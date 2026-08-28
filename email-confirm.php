<?php
/** Confirm an email address change (link sent to the NEW address). */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$token  = (string)($_GET['token'] ?? '');
$userId = $token !== '' ? consume_token($token, 'email_change') : null;

if ($userId) {
    $st = db()->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([$userId]);
    $u = $st->fetch();
    if ($u && !empty($u['pending_email'])) {
        // re-check uniqueness at confirmation time
        $st = db()->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $st->execute([$u['pending_email'], $userId]);
        if ($st->fetch()) {
            db()->prepare('UPDATE users SET pending_email = NULL WHERE id = ?')->execute([$userId]);
            flash_set('error', 'That email was taken by another account in the meantime.');
            redirect('/profile');
        }
        db()->prepare('UPDATE users SET email = pending_email, pending_email = NULL, is_verified = 1 WHERE id = ?')
            ->execute([$userId]);
        login_user($userId);
        flash_set('ok', 'Email address updated.');
        redirect('/profile');
    }
}

flash_set('error', 'That confirmation link is invalid or has expired. Request the change again from your profile.');
redirect('/');
