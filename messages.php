<?php
/** Site chat: conversation list + message thread (AJAX polling). */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$me = require_login();
$db = db();

/* Start (or find) a conversation via ?to=<user_id> */
if (!empty($_GET['to'])) {
    $otherId = (int)$_GET['to'];
    if ($otherId === (int)$me['id']) redirect('/messages');
    $st = $db->prepare('SELECT id FROM users WHERE id = ?');
    $st->execute([$otherId]);
    if (!$st->fetch()) {
        flash_set('error', 'User not found.');
        redirect('/messages');
    }
    $u1 = min((int)$me['id'], $otherId);
    $u2 = max((int)$me['id'], $otherId);
    $st = $db->prepare('SELECT id FROM conversations WHERE user1_id = ? AND user2_id = ?');
    $st->execute([$u1, $u2]);
    $convId = $st->fetchColumn();
    if (!$convId) {
        $db->prepare('INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)')->execute([$u1, $u2]);
        $convId = (int)$db->lastInsertId();
    }
    redirect('/messages?c=' . $convId);
}

$activeId = (int)($_GET['c'] ?? 0);

/* My conversations with partner + unread count */
$st = $db->prepare(
    'SELECT c.id, c.updated_at,
            u.id AS other_id, COALESCE(NULLIF(u.display_name,\'\'), u.username) AS other_name, u.avatar_url AS other_avatar,
            (SELECT COUNT(*) FROM messages m
              WHERE m.conversation_id = c.id AND m.sender_id <> :me AND m.read_at IS NULL) AS unread,
            (SELECT m.body FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_body
       FROM conversations c
       JOIN users u ON u.id = IF(c.user1_id = :me, c.user2_id, c.user1_id)
      WHERE c.user1_id = :me OR c.user2_id = :me
      ORDER BY c.updated_at DESC'
);
$st->execute(['me' => $me['id']]);
$convs = $st->fetchAll();

$active = null;
foreach ($convs as $c) {
    if ((int)$c['id'] === $activeId) { $active = $c; break; }
}
if (!$active && $convs && $activeId === 0) {
    $active = $convs[0];
    $activeId = (int)$active['id'];
}

$page_title = 'Messages | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'messages';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <h1>Messages</h1>
                    <?php echo flash_html(); ?>

                    <?php if (!$convs): ?>
                        <p>No conversations yet. Find someone on the
                            <a href="/users">people page</a> and click <em>Message</em>.</p>
                    <?php else: ?>
                        <div class="cn-chat">
                            <div class="cn-chat-list">
                                <?php foreach ($convs as $c): ?>
                                    <a class="cn-chat-item <?php echo (int)$c['id'] === $activeId ? 'active' : ''; ?>"
                                       href="/messages?c=<?php echo (int)$c['id']; ?>">
                                        <div class="cn-avatar cn-avatar-sm"><?php
                                            echo $c['other_avatar']
                                                ? '<img src="' . e($c['other_avatar']) . '" alt="" referrerpolicy="no-referrer">'
                                                : '<span class="cn-avatar-letter">' . e(strtoupper(substr($c['other_name'], 0, 1))) . '</span>';
                                        ?></div>
                                        <div class="cn-chat-item-body">
                                            <strong><?php echo e($c['other_name']); ?></strong>
                                            <?php if ((int)$c['unread'] > 0): ?>
                                                <span class="cn-unread-badge"><?php echo (int)$c['unread']; ?></span>
                                            <?php endif; ?>
                                            <p class="cn-muted"><?php echo e(mb_substr((string)$c['last_body'], 0, 40)); ?></p>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($active): ?>
                                <div class="cn-chat-thread">
                                    <div class="cn-chat-head">
                                        Chat with <strong><?php echo e($active['other_name']); ?></strong>
                                    </div>
                                    <div class="cn-chat-msgs" id="chat-msgs"></div>
                                    <form id="chat-form" class="cn-chat-form" autocomplete="off">
                                        <textarea id="chat-input" rows="2" maxlength="3000"
                                                  placeholder="Write a message…" required></textarea>
                                        <button type="submit" class="button blue">Send</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>

<?php
if ($active) {
    $csrf = csrf_token();
    $footer_extra = <<<HTML
    <script>
    (function () {
        var convId  = $activeId;
        var csrf    = '$csrf';
        var lastId  = 0;
        var box     = document.getElementById('chat-msgs');
        var form    = document.getElementById('chat-form');
        var input   = document.getElementById('chat-input');

        function esc(s) {
            var d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }
        function add(m) {
            var div = document.createElement('div');
            div.className = 'cn-msg ' + (m.mine ? 'cn-msg-mine' : 'cn-msg-theirs');
            div.innerHTML = '<span>' + esc(m.body).replace(/\\n/g, '<br>') + '</span>'
                          + '<em>' + m.time + '</em>';
            box.appendChild(div);
        }
        function poll() {
            fetch('/api/chat.php?action=poll&c=' + convId + '&after=' + lastId, {credentials: 'same-origin'})
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (!j.ok) return;
                    var had = j.messages.length > 0;
                    j.messages.forEach(function (m) { add(m); lastId = Math.max(lastId, m.id); });
                    if (had) box.scrollTop = box.scrollHeight;
                })
                .catch(function () {});
        }
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var body = input.value.trim();
            if (!body) return;
            var fd = new FormData();
            fd.append('action', 'send');
            fd.append('c', convId);
            fd.append('body', body);
            fd.append('authenticity_token', csrf);
            fetch('/api/chat.php', {method: 'POST', body: fd, credentials: 'same-origin'})
                .then(function (r) { return r.json(); })
                .then(function (j) { if (j.ok) { input.value = ''; poll(); } });
        });
        input.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' && !ev.shiftKey) {
                ev.preventDefault();
                form.dispatchEvent(new Event('submit', {cancelable: true}));
            }
        });
        poll();
        setInterval(poll, 4000);
    })();
    </script>
HTML;
}
include __DIR__ . '/includes/footer.php';
