<?php
/** Shared helpers: auth, CSRF, reCAPTCHA, ImgBB, IP geolocation, chat notify. */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

/* ---------------- output ---------------- */

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function time_ago(string $datetime): string
{
    $d = time() - strtotime($datetime);
    if ($d < 60)      return 'just now';
    if ($d < 3600)    return floor($d / 60) . ' min ago';
    if ($d < 86400)   return floor($d / 3600) . ' h ago';
    if ($d < 2592000) return floor($d / 86400) . ' d ago';
    return date('j M Y', strtotime($datetime));
}

/* ---------------- flash messages ---------------- */

function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function flash_html(): string
{
    $f = flash_get();
    if (!$f) return '';
    $cls = $f['type'] === 'error' ? 'cn-flash cn-flash-error' : 'cn-flash cn-flash-ok';
    return '<div class="' . $cls . '">' . e($f['msg']) . '</div>';
}

/* ---------------- CSRF ---------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="authenticity_token" value="' . csrf_token() . '">';
}

function csrf_check(): bool
{
    return isset($_POST['authenticity_token'])
        && hash_equals(csrf_token(), (string)$_POST['authenticity_token']);
}

/* ---------------- auth ---------------- */

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $st = db()->prepare('SELECT * FROM users WHERE id = ?');
            $st->execute([$_SESSION['user_id']]);
            $user = $st->fetch() ?: null;
            if ($user) {
                db()->prepare('UPDATE users SET last_seen = NOW() WHERE id = ?')->execute([$user['id']]);
            }
        }
    }
    return $user;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        flash_set('error', 'Please log in first.');
        redirect('/#login');
    }
    return $u;
}

function login_user(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

/** Unread chat message count for the header badge. */
function unread_count(?array $u = null): int
{
    $u = $u ?? current_user();
    if (!$u) return 0;
    $st = db()->prepare(
        'SELECT COUNT(*) FROM messages m
           JOIN conversations c ON c.id = m.conversation_id
          WHERE m.read_at IS NULL AND m.sender_id <> ?
            AND (c.user1_id = ? OR c.user2_id = ?)'
    );
    $st->execute([$u['id'], $u['id'], $u['id']]);
    return (int)$st->fetchColumn();
}

/* ---------------- reCAPTCHA v3 ---------------- */

function recaptcha_ok(): bool
{
    $token = $_POST['recaptcha_token'] ?? '';
    if ($token === '') return false;
    $resp = http_post_form('https://www.google.com/recaptcha/api/siteverify', [
        'secret'   => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => client_ip(),
    ]);
    $j = json_decode((string)$resp, true);
    return !empty($j['success']) && (!isset($j['score']) || $j['score'] >= 0.3);
}

/** reCAPTCHA v2 (visible checkbox): verify the g-recaptcha-response field. */
function recaptcha2_ok(): bool
{
    $token = $_POST['g-recaptcha-response'] ?? '';
    if ($token === '') return false;
    $resp = http_post_form('https://www.google.com/recaptcha/api/siteverify', [
        'secret'   => RECAPTCHA2_SECRET_KEY,
        'response' => $token,
        'remoteip' => client_ip(),
    ]);
    $j = json_decode((string)$resp, true);
    return !empty($j['success']);
}

/** JS snippet: run reCAPTCHA v3 on form submit and inject recaptcha_token. */
function recaptcha_js(string $formId, string $action): string
{
    $key = RECAPTCHA_SITE_KEY;
    return <<<HTML
    <script>
    (function () {
        var form = document.getElementById('$formId');
        if (!form) return;
        form.addEventListener('submit', function (ev) {
            if (form.dataset.ready) return;
            ev.preventDefault();
            grecaptcha.ready(function () {
                grecaptcha.execute('$key', {action: '$action'}).then(function (token) {
                    var i = document.createElement('input');
                    i.type = 'hidden'; i.name = 'recaptcha_token'; i.value = token;
                    form.appendChild(i);
                    form.dataset.ready = '1';
                    form.submit();
                });
            });
        });
    })();
    </script>
HTML;
}

/* ---------------- HTTP utils ---------------- */

function http_post_form(string $url, array $fields): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
        ]);
        $out = curl_exec($ch);
        curl_close($ch);
        return $out === false ? null : $out;
    }
    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query($fields),
        'timeout' => 20,
    ]]);
    $out = @file_get_contents($url, false, $ctx);
    return $out === false ? null : $out;
}

function http_get(string $url): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 12]);
        $out = curl_exec($ch);
        curl_close($ch);
        return $out === false ? null : $out;
    }
    $out = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 12]]));
    return $out === false ? null : $out;
}

/* ---------------- ImgBB image hosting ---------------- */

/**
 * Upload one image file to ImgBB. Returns ['url'=>..,'thumb'=>..,'delete_url'=>..] or null.
 */
function imgbb_upload(string $tmpFile): ?array
{
    if (IMGBB_API_KEY === '' || !is_uploaded_file($tmpFile)) return null;

    $info = @getimagesize($tmpFile);
    $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
    if (!$info || !in_array($info[2], $allowed, true)) return null;
    if (filesize($tmpFile) > 10 * 1024 * 1024) return null;

    $resp = http_post_form('https://api.imgbb.com/1/upload?key=' . urlencode(IMGBB_API_KEY), [
        'image' => base64_encode((string)file_get_contents($tmpFile)),
    ]);
    $j = json_decode((string)$resp, true);
    if (empty($j['success']) || empty($j['data']['url'])) return null;
    return [
        'url'        => $j['data']['url'],
        'thumb'      => $j['data']['thumb']['url'] ?? $j['data']['url'],
        'delete_url' => $j['data']['delete_url'] ?? null,
    ];
}

/* ---------------- IP geolocation ---------------- */

function client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

/**
 * Look up city/region/country for an IP (used to pre-select interest location).
 * Tries ipwho.is (https) then ip-api.com. Returns [] on failure.
 */
function geolocate_ip(string $ip): array
{
    if ($ip === '0.0.0.0' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return [];
    }
    $j = json_decode((string)http_get('https://ipwho.is/' . urlencode($ip)), true);
    if (!empty($j['success'])) {
        return [
            'city'    => $j['city']      ?? null,
            'region'  => $j['region']    ?? null,
            'country' => $j['country']   ?? null,
            'lat'     => $j['latitude']  ?? null,
            'lng'     => $j['longitude'] ?? null,
        ];
    }
    $j = json_decode((string)http_get('http://ip-api.com/json/' . urlencode($ip)), true);
    if (($j['status'] ?? '') === 'success') {
        return [
            'city'    => $j['city']       ?? null,
            'region'  => $j['regionName'] ?? null,
            'country' => $j['country']    ?? null,
            'lat'     => $j['lat']        ?? null,
            'lng'     => $j['lon']        ?? null,
        ];
    }
    return [];
}

/* ---------------- tokens ---------------- */

function create_token(int $userId, string $kind, int $ttlHours = 48): string
{
    $token = bin2hex(random_bytes(32));
    $st = db()->prepare(
        'INSERT INTO user_tokens (user_id, token, kind, expires_at)
         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))'
    );
    $st->execute([$userId, $token, $kind, $ttlHours]);
    return $token;
}

function consume_token(string $token, string $kind): ?int
{
    $st = db()->prepare('SELECT id, user_id FROM user_tokens WHERE token = ? AND kind = ? AND expires_at > NOW()');
    $st->execute([$token, $kind]);
    $row = $st->fetch();
    if (!$row) return null;
    db()->prepare('DELETE FROM user_tokens WHERE id = ?')->execute([$row['id']]);
    return (int)$row['user_id'];
}

function send_verification_email(array $user): void
{
    $token = create_token((int)$user['id'], 'verify');
    $url   = SITE_URL . '/verify?token=' . $token;
    $html  = mail_template(
        'Verify your email address',
        '<p>Hi ' . e($user['username']) . ',</p>'
        . '<p>Welcome to ' . e(SITE_NAME) . '! Your account is already active. '
        . 'If you like, confirm your email address below so we know this inbox is really yours '
        . '(this is optional).</p>',
        'Verify my email',
        $url
    );
    send_mail($user['email'], 'Verify your email — ' . SITE_NAME, $html);
}

/* ---------------- chat notification ----------------
   One email per unread conversation: mail the recipient when a new message
   arrives, but not again until they have read the conversation. */

function chat_notify(int $conversationId, array $sender, array $recipient): void
{
    $db = db();
    $db->prepare(
        'INSERT INTO chat_notify_state (conversation_id, user_id, notified_at)
         VALUES (?, ?, NULL)
         ON DUPLICATE KEY UPDATE conversation_id = conversation_id'
    )->execute([$conversationId, $recipient['id']]);

    $st = $db->prepare('SELECT notified_at FROM chat_notify_state WHERE conversation_id = ? AND user_id = ?');
    $st->execute([$conversationId, $recipient['id']]);
    if ($st->fetchColumn() !== null) {
        return; // already notified for this unread conversation
    }

    $html = mail_template(
        'New message from ' . $sender['username'],
        '<p>Hi ' . e($recipient['username']) . ',</p>'
        . '<p><strong>' . e($sender['username']) . '</strong> sent you a message on '
        . e(SITE_NAME) . '. Log in to read and reply.</p>',
        'Read your message',
        SITE_URL . '/messages?c=' . $conversationId
    );
    if (send_mail($recipient['email'], 'New message from ' . $sender['username'] . ' — ' . SITE_NAME, $html)) {
        $db->prepare('UPDATE chat_notify_state SET notified_at = NOW() WHERE conversation_id = ? AND user_id = ?')
           ->execute([$conversationId, $recipient['id']]);
    }
}

/** Called when a user reads a conversation: allow future notifications again. */
function chat_notify_reset(int $conversationId, int $userId): void
{
    db()->prepare('UPDATE chat_notify_state SET notified_at = NULL WHERE conversation_id = ? AND user_id = ?')
        ->execute([$conversationId, $userId]);
}
