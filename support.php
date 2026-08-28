<?php
/** Support: open a ticket — new tickets are emailed to ADMIN_EMAIL. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$me = current_user();
$errors = [];
$old = [
    'name'    => $me['username'] ?? '',
    'email'   => $me['email'] ?? '',
    'subject' => '',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) {
        $old[$k] = trim((string)($_POST[$k] ?? $v));
    }
    if (!csrf_check())                                     $errors[] = 'Session expired — please try again.';
    if (!recaptcha_ok())                                   $errors[] = 'reCAPTCHA check failed — please try again.';
    if ($old['name'] === '')                               $errors[] = 'Please enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (mb_strlen($old['subject']) < 3)                    $errors[] = 'Subject: at least 3 characters.';
    if (mb_strlen($old['message']) < 10)                   $errors[] = 'Message: at least 10 characters.';

    // rate limit: max 3 tickets per hour per email/IP
    if (!$errors) {
        $st = db()->prepare(
            'SELECT COUNT(*) FROM support_tickets
              WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
        $st->execute([strtolower($old['email'])]);
        if ((int)$st->fetchColumn() >= 3) $errors[] = 'Too many tickets — please wait a while.';
    }

    if (!$errors) {
        $token = bin2hex(random_bytes(16));
        db()->prepare(
            'INSERT INTO support_tickets (token, user_id, name, email, subject, message)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$token, $me['id'] ?? null, $old['name'], strtolower($old['email']),
                    $old['subject'], $old['message']]);
        $ticketId  = (int)db()->lastInsertId();
        $ticketUrl = SITE_URL . '/ticket?t=' . $token;

        // 1) notify admin
        send_mail(
            ADMIN_EMAIL,
            "[Ticket #$ticketId] " . $old['subject'],
            mail_template(
                "New support ticket #$ticketId",
                '<p><strong>From:</strong> ' . e($old['name']) . ' &lt;' . e($old['email']) . '&gt;'
                . ($me ? ' (user: ' . e($me['username']) . ')' : ' (guest)') . '</p>'
                . '<p><strong>Subject:</strong> ' . e($old['subject']) . '</p>'
                . '<p style="white-space:pre-wrap;border-left:3px solid #2e9fff;padding-left:12px">'
                . nl2br(e($old['message'])) . '</p>',
                'Open ticket & reply',
                $ticketUrl
            )
        );

        // 2) confirmation to the requester (with their secret ticket link)
        send_mail(
            $old['email'],
            "We got your ticket #$ticketId — " . SITE_NAME,
            mail_template(
                "Ticket #$ticketId received",
                '<p>Hi ' . e($old['name']) . ',</p>'
                . '<p>Thanks for contacting ' . e(SITE_NAME) . '. We\'ve received your ticket'
                . ' <strong>&ldquo;' . e($old['subject']) . '&rdquo;</strong> and will reply as soon as possible.</p>'
                . '<p>You can follow the conversation and add more details any time via your ticket link:</p>',
                'View my ticket',
                $ticketUrl
            )
        );

        flash_set('ok', "Ticket #$ticketId created — we've emailed you a link to follow it.");
        redirect('/ticket?t=' . $token);
    }
}

/* tickets of the logged-in user */
$myTickets = [];
if ($me) {
    $st = db()->prepare(
        'SELECT id, token, subject, status, updated_at FROM support_tickets
          WHERE user_id = ? OR email = ? ORDER BY updated_at DESC LIMIT 20'
    );
    $st->execute([$me['id'], $me['email']]);
    $myTickets = $st->fetchAll();
}

$page_title = 'Support | Choose Neighbors';
$body_class = 'remind-page has-bottom-footer';
$page_id    = 'support';
$head_extra = '    <script src="https://www.google.com/recaptcha/api.js?render=' . RECAPTCHA_SITE_KEY . '"></script>';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">

                <section class="standard-page">
                    <h1>Support</h1>
                    <p>We are glad to hear from you! Open a ticket below and we'll reply by email.</p>
                    <p class="cn-muted">You can also reach us by phone or WhatsApp:
                        <a href="tel:+61401824126">+61 401 824 126</a> ·
                        <a href="https://wa.me/61401824126" rel="noopener" target="_blank">WhatsApp</a></p>
                    <?php echo flash_html(); ?>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-card cn-form-card">
                        <form method="post" action="/support" id="ticket-form" class="cn-form">
                            <?php echo csrf_field(); ?>
                            <div class="cn-form-row">
                                <label>Name
                                    <input type="text" name="name" required maxlength="100"
                                           value="<?php echo e($old['name']); ?>"></label>
                                <label>Email
                                    <input type="email" name="email" required maxlength="190"
                                           value="<?php echo e($old['email']); ?>"></label>
                            </div>
                            <label>Subject
                                <input type="text" name="subject" required maxlength="190"
                                       value="<?php echo e($old['subject']); ?>"></label>
                            <label>What are you contacting us about?
                                <textarea name="message" rows="6" required maxlength="5000"><?php echo e($old['message']); ?></textarea></label>
                            <button type="submit" class="button blue">Open ticket</button>
                        </form>
                        <p class="cn-muted">You'll get an email with a private link to follow your ticket
                            and read our reply.</p>
                    </div>

                    <?php if ($myTickets): ?>
                        <h3>Your tickets</h3>
                        <div class="cn-ticket-list">
                            <?php foreach ($myTickets as $t): ?>
                                <a class="cn-ticket-row" href="/ticket?t=<?php echo e($t['token']); ?>">
                                    <span class="cn-ticket-status cn-ticket-<?php echo e($t['status']); ?>"><?php echo e($t['status']); ?></span>
                                    <strong>#<?php echo (int)$t['id']; ?></strong>
                                    <?php echo e($t['subject']); ?>
                                    <span class="cn-muted"><?php echo e(time_ago($t['updated_at'])); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            </div>
        </div>
    </div>

<?php
$footer_extra = recaptcha_js('ticket-form', 'support');
include __DIR__ . '/includes/footer.php';
