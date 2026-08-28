<?php
/** POST endpoint: create a comment on the site wall, a listing, or a group. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$me = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
    flash_set('error', 'Session expired — please try again.');
    redirect('/');
}

$type     = (string)($_POST['target_type'] ?? 'site');
$targetId = (int)($_POST['target_id'] ?? 0);
$body     = trim((string)($_POST['body'] ?? ''));
$returnTo = (string)($_POST['return_to'] ?? '/');
if (!str_starts_with($returnTo, '/')) $returnTo = '/';

if (!in_array($type, ['site', 'listing', 'grp'], true)) $type = 'site';
if ($type === 'site') $targetId = 0;

// target must exist
if ($type === 'listing') {
    $st = db()->prepare('SELECT id FROM listings WHERE id = ?');
    $st->execute([$targetId]);
    if (!$st->fetch()) { flash_set('error', 'Listing not found.'); redirect('/listings'); }
}
if ($type === 'grp') {
    $st = db()->prepare('SELECT id FROM community_groups WHERE id = ?');
    $st->execute([$targetId]);
    if (!$st->fetch()) { flash_set('error', 'Group not found.'); redirect('/groups'); }
}

if (mb_strlen($body) < 2 || mb_strlen($body) > 3000) {
    flash_set('error', 'Comment must be between 2 and 3000 characters.');
    redirect($returnTo);
}

// simple rate limit: max 1 comment per 15 seconds
$st = db()->prepare('SELECT COUNT(*) FROM comments WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 SECOND)');
$st->execute([$me['id']]);
if ((int)$st->fetchColumn() > 0) {
    flash_set('error', 'You are commenting too fast — try again in a few seconds.');
    redirect($returnTo);
}

db()->prepare('INSERT INTO comments (user_id, target_type, target_id, body) VALUES (?, ?, ?, ?)')
    ->execute([$me['id'], $type, $targetId, $body]);

flash_set('ok', 'Comment posted.');
redirect($returnTo);
