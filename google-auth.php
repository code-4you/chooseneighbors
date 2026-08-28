<?php
/**
 * Google Sign-In (Google Identity Services).
 * The GIS button POSTs a `credential` (ID token JWT) here; we verify it
 * server-side against Google's tokeninfo endpoint, then log in / create the user.
 * Google accounts count as email-verified.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$credential = (string)($_POST['credential'] ?? '');
if ($credential === '') {
    flash_set('error', 'Google sign-in failed (no credential).');
    redirect('/login');
}

// GIS double-submit-cookie CSRF check
$csrfBody   = (string)($_POST['g_csrf_token'] ?? '');
$csrfCookie = (string)($_COOKIE['g_csrf_token'] ?? '');
if ($csrfBody === '' || $csrfBody !== $csrfCookie) {
    flash_set('error', 'Google sign-in failed (CSRF check).');
    redirect('/login');
}

// Verify the ID token with Google
$resp = http_get('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential));
$info = json_decode((string)$resp, true);

$valid = is_array($info)
    && ($info['aud'] ?? '') === GOOGLE_CLIENT_ID
    && in_array($info['iss'] ?? '', ['https://accounts.google.com', 'accounts.google.com'], true)
    && (int)($info['exp'] ?? 0) > time()
    && !empty($info['sub'])
    && !empty($info['email']);

if (!$valid) {
    flash_set('error', 'Google sign-in could not be verified. Please try again.');
    redirect('/login');
}

$googleId = (string)$info['sub'];
$email    = strtolower((string)$info['email']);
$name     = (string)($info['name'] ?? '');
$picture  = (string)($info['picture'] ?? '');

$db = db();

// 1) existing Google account
$st = $db->prepare('SELECT * FROM users WHERE google_id = ?');
$st->execute([$googleId]);
$user = $st->fetch();

// 2) existing email account -> link Google to it
if (!$user) {
    $st = $db->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([$email]);
    $user = $st->fetch();
    if ($user) {
        $db->prepare('UPDATE users SET google_id = ?, is_verified = 1, avatar_url = COALESCE(avatar_url, ?) WHERE id = ?')
           ->execute([$googleId, $picture ?: null, $user['id']]);
    }
}

// 3) brand new user
if (!$user) {
    $base = preg_replace('/[^A-Za-z0-9_.-]/', '', $name !== '' ? str_replace(' ', '', $name) : explode('@', $email)[0]);
    $base = substr($base !== '' ? $base : 'user', 0, 24);
    $username = $base;
    for ($i = 1; ; $i++) {
        $st = $db->prepare('SELECT id FROM users WHERE username = ?');
        $st->execute([$username]);
        if (!$st->fetch()) break;
        $username = $base . $i;
    }
    $geo = geolocate_ip(client_ip());
    $db->prepare(
        'INSERT INTO users (username, display_name, email, pass_hash, google_id, avatar_url, is_verified, city, country)
         VALUES (?, ?, ?, NULL, ?, ?, 1, ?, ?)'
    )->execute([$username, mb_substr($name, 0, 80) ?: null, $email, $googleId, $picture ?: null, $geo['city'] ?? null, $geo['country'] ?? null]);
    $st = $db->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([(int)$db->lastInsertId()]);
    $user = $st->fetch();
}

/* backfill display name from Google for accounts that don't have one yet */
if ($name !== '' && trim((string)($user['display_name'] ?? '')) === '') {
    $db->prepare('UPDATE users SET display_name = ? WHERE id = ?')
       ->execute([mb_substr($name, 0, 80), $user['id']]);
    $user['display_name'] = mb_substr($name, 0, 80);
}

login_user((int)$user['id']);
flash_set('ok', 'Signed in with Google — welcome, '
    . (trim((string)($user['display_name'] ?? '')) !== '' ? $user['display_name'] : $user['username']) . '!');
redirect('/');
