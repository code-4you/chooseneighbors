<?php
/**
 * Chat JSON API.
 *   POST action=send   c=<conversation_id> body=<text>
 *   GET  action=poll   c=<conversation_id> after=<last_message_id>
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/inc/helpers.php';

header('Content-Type: application/json; charset=UTF-8');

$me = current_user();
if (!$me) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'not_logged_in']);
    exit;
}

$db     = db();
$action = (string)($_REQUEST['action'] ?? '');
$convId = (int)($_REQUEST['c'] ?? 0);

/** Confirm the conversation belongs to me; returns row or null. */
$st = $db->prepare('SELECT * FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)');
$st->execute([$convId, $me['id'], $me['id']]);
$conv = $st->fetch();
if (!$conv) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'conversation_not_found']);
    exit;
}
$otherId = (int)($conv['user1_id'] == $me['id'] ? $conv['user2_id'] : $conv['user1_id']);

if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(csrf_token(), (string)($_POST['authenticity_token'] ?? ''))) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'csrf']);
        exit;
    }
    $body = trim((string)($_POST['body'] ?? ''));
    if ($body === '' || mb_strlen($body) > 3000) {
        echo json_encode(['ok' => false, 'error' => 'bad_body']);
        exit;
    }
    $db->prepare('INSERT INTO messages (conversation_id, sender_id, body) VALUES (?, ?, ?)')
       ->execute([$convId, $me['id'], $body]);
    $msgId = (int)$db->lastInsertId();
    $db->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = ?')->execute([$convId]);

    // notify recipient by email (once per unread conversation)
    $st = $db->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([$otherId]);
    if ($recipient = $st->fetch()) {
        chat_notify($convId, $me, $recipient);
    }

    echo json_encode(['ok' => true, 'id' => $msgId]);
    exit;
}

if ($action === 'poll') {
    $after = (int)($_GET['after'] ?? 0);
    $st = $db->prepare(
        'SELECT m.id, m.sender_id, m.body, m.created_at FROM messages m
          WHERE m.conversation_id = ? AND m.id > ? ORDER BY m.id LIMIT 200'
    );
    $st->execute([$convId, $after]);
    $msgs = $st->fetchAll();

    // mark incoming as read + allow future email notifications
    $db->prepare('UPDATE messages SET read_at = NOW()
                   WHERE conversation_id = ? AND sender_id <> ? AND read_at IS NULL')
       ->execute([$convId, $me['id']]);
    chat_notify_reset($convId, (int)$me['id']);

    echo json_encode(['ok' => true, 'me' => (int)$me['id'], 'messages' => array_map(
        fn($m) => [
            'id'   => (int)$m['id'],
            'mine' => (int)$m['sender_id'] === (int)$me['id'],
            'body' => $m['body'],
            'time' => date('H:i', strtotime($m['created_at'])),
        ], $msgs)]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'bad_action']);
