<?php
/**
 * View a support ticket via its secret token link.
 * - The requester replies here (also gets email updates when staff answer).
 * - If the logged-in user's email is ADMIN_EMAIL, replies are marked as staff
 *   and the requester is notified by email.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$token = (string)($_GET['t'] ?? $_POST['t'] ?? '');
$st = db()->prepare('SELECT * FROM support_tickets WHERE token = ?');
$st->execute([$token]);
$t = $st->fetch();

if (!$t) {
    flash_set('error', 'Ticket not found — check the link from your email.');
    redirect('/support');
}

$me      = current_user();
$isStaff = $me && strtolower($me['email']) === strtolower(ADMIN_EMAIL);
$ticketUrl = SITE_URL . '/ticket?t=' . $t['token'];
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? 'reply');

    if (!csrf_check()) {
        $errors[] = 'Session expired — please try again.';
    } elseif ($action === 'close') {
        db()->prepare("UPDATE support_tickets SET status = 'closed', updated_at = NOW() WHERE id = ?")
            ->execute([$t['id']]);
        flash_set('ok', 'Ticket closed. You can re-open it by replying.');
        redirect('/ticket?t=' . $t['token']);
    } else {
        $body = trim((string)($_POST['body'] ?? ''));
        if (mb_strlen($body) < 2 || mb_strlen($body) > 5000) {
            $errors[] = 'Reply must be between 2 and 5000 characters.';
        } else {
            db()->prepare('INSERT INTO support_ticket_replies (ticket_id, is_staff, body) VALUES (?, ?, ?)')
                ->execute([$t['id'], $isStaff ? 1 : 0, $body]);
            $newStatus = $isStaff ? 'answered' : 'open';
            db()->prepare('UPDATE support_tickets SET status = ?, updated_at = NOW() WHERE id = ?')
                ->execute([$newStatus, $t['id']]);

            if ($isStaff) {
                // notify the requester
                send_mail(
                    $t['email'],
                    '[Ticket #' . $t['id'] . '] Reply from ' . SITE_NAME . ' support',
                    mail_template(
                        'Reply to your ticket #' . $t['id'],
                        '<p>Hi ' . e($t['name']) . ',</p>'
                        . '<p>Our support replied to <strong>&ldquo;' . e($t['subject']) . '&rdquo;</strong>:</p>'
                        . '<p style="white-space:pre-wrap;border-left:3px solid #2e9fff;padding-left:12px">'
                        . nl2br(e($body)) . '</p>',
                        'View / reply',
                        $ticketUrl
                    )
                );
            } else {
                // notify admin
                send_mail(
                    ADMIN_EMAIL,
                    '[Ticket #' . $t['id'] . '] New reply: ' . $t['subject'],
                    mail_template(
                        'New reply on ticket #' . $t['id'],
                        '<p><strong>' . e($t['name']) . '</strong> &lt;' . e($t['email']) . '&gt; replied:</p>'
                        . '<p style="white-space:pre-wrap;border-left:3px solid #2e9fff;padding-left:12px">'
                        . nl2br(e($body)) . '</p>',
                        'Open ticket & reply',
                        $ticketUrl
                    )
                );
            }
            flash_set('ok', 'Reply posted.');
            redirect('/ticket?t=' . $t['token']);
        }
    }
}

/* refresh + load thread */
$st = db()->prepare('SELECT * FROM support_tickets WHERE id = ?');
$st->execute([$t['id']]);
$t = $st->fetch();

$st = db()->prepare('SELECT * FROM support_ticket_replies WHERE ticket_id = ? ORDER BY id');
$st->execute([$t['id']]);
$replies = $st->fetchAll();

$page_title = 'Ticket #' . $t['id'] . ' | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'ticket';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <p><a href="/support">&larr; Support</a></p>
                    <span class="cn-ticket-status cn-ticket-<?php echo e($t['status']); ?>"><?php echo e($t['status']); ?></span>
                    <h1>Ticket #<?php echo (int)$t['id']; ?>: <?php echo e($t['subject']); ?></h1>
                    <p class="cn-muted">Opened by <?php echo e($t['name']); ?>
                        · <?php echo e(time_ago($t['created_at'])); ?>
                        <?php if ($isStaff): ?> · <strong>staff view</strong>
                            (<?php echo e($t['email']); ?>)<?php endif; ?></p>
                    <?php echo flash_html(); ?>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-ticket-thread">
                        <div class="cn-ticket-msg">
                            <p class="cn-muted"><strong><?php echo e($t['name']); ?></strong>
                                · <?php echo e(time_ago($t['created_at'])); ?></p>
                            <p><?php echo nl2br(e($t['message'])); ?></p>
                        </div>
                        <?php foreach ($replies as $r): ?>
                            <div class="cn-ticket-msg <?php echo $r['is_staff'] ? 'cn-ticket-staff' : ''; ?>">
                                <p class="cn-muted"><strong><?php
                                    echo $r['is_staff'] ? e(SITE_NAME) . ' support' : e($t['name']);
                                ?></strong> · <?php echo e(time_ago($r['created_at'])); ?></p>
                                <p><?php echo nl2br(e($r['body'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cn-card cn-form-card">
                        <h3><?php echo $t['status'] === 'closed' ? 'Re-open with a reply' : 'Reply'; ?></h3>
                        <form method="post" action="/ticket" class="cn-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="t" value="<?php echo e($t['token']); ?>">
                            <label>Your message
                                <textarea name="body" rows="5" maxlength="5000" required></textarea></label>
                            <button type="submit" class="button blue">Send reply</button>
                        </form>
                        <?php if ($t['status'] !== 'closed'): ?>
                            <form method="post" action="/ticket" class="cn-inline-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="t" value="<?php echo e($t['token']); ?>">
                                <input type="hidden" name="action" value="close">
                                <button type="submit" class="button">Close ticket</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
