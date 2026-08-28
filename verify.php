<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$token  = (string)($_GET['token'] ?? '');
$userId = $token !== '' ? consume_token($token, 'verify') : null;

if ($userId) {
    db()->prepare('UPDATE users SET is_verified = 1 WHERE id = ?')->execute([$userId]);
    login_user($userId);
    flash_set('ok', 'Email confirmed — thanks!');
    redirect('/');
}

flash_set('error', 'That confirmation link is invalid or has expired.');
redirect('/login');
