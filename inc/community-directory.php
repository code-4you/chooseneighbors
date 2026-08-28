<?php
/**
 * Shared community-directory helpers: types, schema, IP-based region detection,
 * one-time Gemini population, and the 4-day refresh. Used by find-a-community.php
 * and the homepage 'Communities near you' block.
 */

/* ---------- community types (the filters) ---------- */
$TYPES = [
    'monastery'     => 'Monasteries & religious stays',
    'cohousing'     => 'Cohousing',
    'ecovillage'    => 'Ecovillages',
    'coop'          => 'Housing co-operatives',
    'coliving'      => 'Coliving & share houses',
    'intentional'   => 'Intentional communities',
    'care'          => 'Live-in care communities',
    'seniors'       => 'Seniors villages',
    'neighbourhood' => 'Community-strong neighbourhoods & councils',
];

/* ---------- schema (idempotent) ---------- */
function fac_ensure_schema(): void {
    db()->exec("CREATE TABLE IF NOT EXISTS community_directory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        country VARCHAR(64) NOT NULL,
        region VARCHAR(96) NOT NULL,
        type VARCHAR(32) NOT NULL,
        name VARCHAR(160) NOT NULL,
        locality VARCHAR(120) NOT NULL DEFAULT '',
        council VARCHAR(120) NOT NULL DEFAULT '',
        description TEXT,
        website VARCHAR(255) NOT NULL DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_region (country, region),
        KEY idx_type (type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    db()->exec("CREATE TABLE IF NOT EXISTS community_directory_regions (
        country VARCHAR(64) NOT NULL,
        region VARCHAR(96) NOT NULL,
        status ENUM('pending','done','failed') NOT NULL DEFAULT 'pending',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (country, region)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/* ---------- geo: state from visitor IP ---------- */
function fac_detect_region(): array {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $ip = trim(explode(',', $ip)[0]);
    if ($ip === '' || $ip === '127.0.0.1' || strpos($ip, '10.') === 0 || strpos($ip, '192.168.') === 0) {
        return ['Australia', ''];
    }
    $ch = curl_init('http://ip-api.com/json/' . urlencode($ip) . '?fields=status,country,regionName');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4, CURLOPT_CONNECTTIMEOUT => 3]);
    $out = curl_exec($ch);
    curl_close($ch);
    $j = $out ? json_decode($out, true) : null;
    if (is_array($j) && ($j['status'] ?? '') === 'success' && !empty($j['regionName'])) {
        return [$j['country'] ?: 'Australia', $j['regionName']];
    }
    return ['Australia', ''];
}

/* ---------- populate a region via Gemini (once, cached) ---------- */
function fac_populate_region(string $country, string $region, array $TYPES): string {
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') return 'failed';

    /* claim the region so concurrent visitors don't double-call */
    $st = db()->prepare("SELECT status, (updated_at > NOW() - INTERVAL 60 SECOND) AS recent,
                                (updated_at > NOW() - INTERVAL 120 SECOND) AS recent_pending
                           FROM community_directory_regions WHERE country=? AND region=?");
    $st->execute([$country, $region]);
    $row = $st->fetch();
    if ($row) {
        if ($row['status'] === 'done') return 'done';
        /* a pending claim older than 2 min is a crashed attempt — reclaim it */
        if ($row['status'] === 'pending' && $row['recent_pending']) return 'pending';
        if ($row['status'] === 'failed' && $row['recent']) return 'failed';
        db()->prepare("UPDATE community_directory_regions SET status='pending' WHERE country=? AND region=?")->execute([$country, $region]);
    } else {
        try {
            db()->prepare("INSERT INTO community_directory_regions (country, region, status) VALUES (?,?, 'pending')")->execute([$country, $region]);
        } catch (Throwable $e) { return 'pending'; /* another visitor claimed it */ }
    }

    $typeList = '';
    foreach ($TYPES as $key => $label) $typeList .= "- \"$key\" = $label\n";

    $prompt = "List real, currently existing residential communities and community-living options in "
        . $region . ", " . $country . " that a person feeling isolated could realistically join, visit, or move into. "
        . "These are places where community is built into where you LIVE (not clubs or classes). "
        . "Include a spread across these types (use the type key exactly):\n" . $typeList
        . "\nFor monasteries include ones that welcome lay guests or residents. "
        . "For \"neighbourhood\" include specific council areas / suburbs known for strong community programs, neighbourhood houses, or community centres — councils differ a lot and that matters. "
        . "Return 12-18 entries as a strict JSON array, each object exactly: "
        . "{\"name\":string, \"type\":string(one of the keys), \"locality\":string(town/suburb), \"council\":string(local council/LGA, or \"\"), \"description\":string(2 warm factual sentences: what it is and who it welcomes), \"website\":string(official URL if well known, else \"\")}. "
        . "Only include places you are confident actually exist. JSON array only, no other text.";

    $body = json_encode([
        'contents' => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => [
            'temperature' => 0.2,
            'responseMimeType' => 'application/json',
            'thinkingConfig' => ['thinkingBudget' => 0],
        ],
    ]);

    $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 55,
        CURLOPT_CONNECTTIMEOUT => 8,
    ]);
    $out = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    if ($out === false || $httpCode !== 200) {
        error_log('find-a-community: Gemini call failed for ' . $region . ' — HTTP ' . $httpCode
            . ($curlErr ? ' curl: ' . $curlErr : '') . ' body: ' . substr((string)$out, 0, 300));
    }

    $entries = null;
    if ($out) {
        $j = json_decode($out, true);
        $text = $j['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text !== '') {
            $text = preg_replace('/^```(json)?|```$/m', '', trim($text));
            $entries = json_decode(trim($text), true);
        }
    }

    if (!is_array($entries) || count($entries) === 0) {
        db()->prepare("UPDATE community_directory_regions SET status='failed' WHERE country=? AND region=?")->execute([$country, $region]);
        return 'failed';
    }

    $ins = db()->prepare("INSERT INTO community_directory (country, region, type, name, locality, council, description, website) VALUES (?,?,?,?,?,?,?,?)");
    $n = 0;
    foreach ($entries as $e) {
        if (!is_array($e) || empty($e['name']) || empty($e['type']) || !isset($TYPES[$e['type']])) continue;
        $site = (string)($e['website'] ?? '');
        if ($site !== '' && !preg_match('#^https?://#i', $site)) $site = 'https://' . $site;
        $ins->execute([
            $country, $region, $e['type'],
            mb_substr((string)$e['name'], 0, 160),
            mb_substr((string)($e['locality'] ?? ''), 0, 120),
            mb_substr((string)($e['council'] ?? ''), 0, 120),
            (string)($e['description'] ?? ''),
            mb_substr($site, 0, 255),
        ]);
        $n++;
    }
    db()->prepare("UPDATE community_directory_regions SET status=? WHERE country=? AND region=?")
        ->execute([$n > 0 ? 'done' : 'failed', $country, $region]);
    return $n > 0 ? 'done' : 'failed';
}

/* ---------- periodic refresh: ask Gemini for NEW entries, max once per state per 4 days ---------- */
function fac_refresh_if_stale(string $country, string $region, array $TYPES): void {
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') return;

    /* atomic claim: only one visitor wins the refresh, and at most every 4 days */
    $up = db()->prepare("UPDATE community_directory_regions SET updated_at = NOW()
                          WHERE country=? AND region=? AND status='done'
                            AND updated_at < NOW() - INTERVAL 4 DAY");
    $up->execute([$country, $region]);
    if ($up->rowCount() !== 1) return;

    /* detach from the visitor's request so they never wait on this */
    if (function_exists('litespeed_finish_request')) { @litespeed_finish_request(); }
    elseif (function_exists('fastcgi_finish_request')) { @fastcgi_finish_request(); }
    ignore_user_abort(true);
    @set_time_limit(120);

    $st = db()->prepare("SELECT name FROM community_directory WHERE country=? AND region=?");
    $st->execute([$country, $region]);
    $existing = array_column($st->fetchAll(), 'name');

    $typeList = '';
    foreach ($TYPES as $key => $label) $typeList .= "- \"$key\" = $label\n";

    $prompt = "We already list these residential communities for " . $region . ", " . $country . ":\n"
        . implode("\n", array_map(function ($n) { return '- ' . $n; }, $existing))
        . "\n\nSuggest up to 6 ADDITIONAL real, currently existing residential communities or community-living options in "
        . $region . ", " . $country . " that are NOT in the list above (no duplicates, no renames of listed places). "
        . "Places where community is built into where you live. Types (use the key exactly):\n" . $typeList
        . "\nOnly include places you are confident actually exist. If you know of no genuinely new ones, return []. "
        . "Strict JSON array, each object exactly: "
        . "{\"name\":string, \"type\":string(one of the keys), \"locality\":string, \"council\":string, \"description\":string(2 warm factual sentences), \"website\":string(official URL if well known, else \"\")}. "
        . "JSON only.";

    $body = json_encode([
        'contents' => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => [
            'temperature' => 0.2,
            'responseMimeType' => 'application/json',
            'thinkingConfig' => ['thinkingBudget' => 0],
        ],
    ]);

    $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 55,
        CURLOPT_CONNECTTIMEOUT => 8,
    ]);
    $out = curl_exec($ch);
    curl_close($ch);
    if (!$out) return;

    $j = json_decode($out, true);
    $text = $j['candidates'][0]['content']['parts'][0]['text'] ?? '';
    if ($text === '') return;
    $text = preg_replace('/^```(json)?|```$/m', '', trim($text));
    $new = json_decode(trim($text), true);
    if (!is_array($new)) return;

    $dupe = db()->prepare("SELECT COUNT(*) FROM community_directory WHERE country=? AND region=? AND LOWER(name)=LOWER(?)");
    $ins  = db()->prepare("INSERT INTO community_directory (country, region, type, name, locality, council, description, website) VALUES (?,?,?,?,?,?,?,?)");
    $added = 0;
    foreach ($new as $e) {
        if (!is_array($e) || empty($e['name']) || empty($e['type']) || !isset($TYPES[$e['type']])) continue;
        $dupe->execute([$country, $region, (string)$e['name']]);
        if ((int)$dupe->fetchColumn() > 0) continue;
        $site = (string)($e['website'] ?? '');
        if ($site !== '' && !preg_match('#^https?://#i', $site)) $site = 'https://' . $site;
        $ins->execute([
            $country, $region, $e['type'],
            mb_substr((string)$e['name'], 0, 160),
            mb_substr((string)($e['locality'] ?? ''), 0, 120),
            mb_substr((string)($e['council'] ?? ''), 0, 120),
            (string)($e['description'] ?? ''),
            mb_substr($site, 0, 255),
        ]);
        $added++;
    }
    if ($added > 0) error_log('find-a-community: refresh added ' . $added . ' entries for ' . $region . ', ' . $country);
}

