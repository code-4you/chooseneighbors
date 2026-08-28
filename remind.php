<?php
/** Forgot password: request a reset link, or set a new password with a token. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$mode  = 'request';
$token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
if ($token !== '') $mode = 'reset';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === 'request') {
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    if (!csrf_check()) {
        $errors[] = 'Session expired — please try again.';
    } elseif (!recaptcha_ok()) {
        $errors[] = 'reCAPTCHA check failed — please try again.';
    } else {
        $st = db()->prepare('SELECT * FROM users WHERE email = ? AND pass_hash IS NOT NULL');
        $st->execute([$email]);
        if ($user = $st->fetch()) {
            $t = create_token((int)$user['id'], 'reset', 4);
            $html = mail_template(
                'Reset your password',
                '<p>Hi ' . e($user['username']) . ',</p><p>Click below to choose a new password. '
                . 'The link is valid for 4 hours. If you didn\'t request this, ignore this email.</p>',
                'Reset password',
                SITE_URL . '/remind?token=' . $t
            );
            send_mail($user['email'], 'Reset your password — ' . SITE_NAME, $html);
        }
        // same response whether or not the email exists
        flash_set('ok', 'If that email is registered, a reset link is on its way.');
        redirect('/remind');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === 'reset') {
    $pass  = (string)($_POST['password'] ?? '');
    $pass2 = (string)($_POST['password2'] ?? '');
    if (!csrf_check())        $errors[] = 'Session expired — please try again.';
    if (strlen($pass) < 8)    $errors[] = 'Password must be at least 8 characters.';
    if ($pass !== $pass2)     $errors[] = 'Passwords do not match.';
    if (!$errors) {
        $userId = consume_token($token, 'reset');
        if (!$userId) {
            flash_set('error', 'That reset link is invalid or has expired.');
            redirect('/remind');
        }
        db()->prepare('UPDATE users SET pass_hash = ?, is_verified = 1 WHERE id = ?')
            ->execute([password_hash($pass, PASSWORD_DEFAULT), $userId]);
        login_user($userId);
        flash_set('ok', 'Password updated — you are logged in.');
        redirect('/');
    }
}

$page_title = 'Forgot password | Choose Neighbors';
$body_class = 'remind-page has-bottom-footer';
$page_id    = 'remind';
$head_extra = '    <script src="https://www.google.com/recaptcha/api.js?render=' . RECAPTCHA_SITE_KEY . '"></script>';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <h1><?php echo $mode === 'reset' ? 'Choose a new password' : 'Forgot your password?'; ?></h1>
                    <?php echo flash_html(); ?>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-card cn-form-card">
                        <?php if ($mode === 'reset'): ?>
                            <form method="post" action="/remind" class="cn-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="token" value="<?php echo e($token); ?>">
                                <label>New password
                                    <input type="password" name="password" required minlength="8"></label>
                                <label>Repeat new password
                                    <input type="password" name="password2" required minlength="8"></label>
                                <button type="submit" class="button blue">Set new password</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="/remind" id="remind-form" class="cn-form">
                                <?php echo csrf_field(); ?>
                                <label>Your account email
                                    <input type="email" name="email" required maxlength="190"></label>
                                <button type="submit" class="button blue">Email me a reset link</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>

<?php
$footer_extra = $mode === 'request' ? recaptcha_js('remind-form', 'remind') : '';
include __DIR__ . '/includes/footer.php';
