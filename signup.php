<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

if (current_user()) redirect('/');

$errors = [];
$geo = geolocate_ip(client_ip());   // location auto-detected from IP (editable below)
$old = [
    'username' => '',
    'email'    => '',
    'city'     => $geo['city'] ?? '',
    'country'  => $geo['country'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['username'] = $username = trim((string)($_POST['username'] ?? ''));
    $old['email']    = $email    = strtolower(trim((string)($_POST['email'] ?? '')));
    $old['city']     = trim((string)($_POST['city'] ?? ''));
    $old['country']  = trim((string)($_POST['country'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    $pass2 = (string)($_POST['password2'] ?? '');

    if (!csrf_check())                                   $errors[] = 'Session expired — please try again.';
    if (!recaptcha2_ok())                                $errors[] = 'Please tick the \'I\'m not a robot\' box.';
    if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username))
        $errors[] = 'Username: 3-30 characters, letters/numbers/._- only.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))      $errors[] = 'Please enter a valid email address.';
    if (strlen($pass) < 8)                               $errors[] = 'Password must be at least 8 characters.';
    if ($pass !== $pass2)                                $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $st = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $st->execute([$username, $email]);
        if ($st->fetch()) $errors[] = 'That username or email is already registered.';
    }

    if (!$errors) {
        $st = db()->prepare(
            'INSERT INTO users (username, email, pass_hash, is_verified, city, country)
             VALUES (?, ?, ?, 0, ?, ?)'
        );
        $st->execute([$username, $email, password_hash($pass, PASSWORD_DEFAULT),
                      $old['city'] ?: null, $old['country'] ?: null]);
        $userId = (int)db()->lastInsertId();
        // verification email still goes out, but confirming it is optional
        send_verification_email(['id' => $userId, 'username' => $username, 'email' => $email]);
        login_user($userId);
        flash_set('ok', 'Welcome to ' . SITE_NAME . ', ' . $username . '! Your account is ready. '
            . 'We\'ve also sent you an email so you can confirm your address (optional).');
        redirect('/');
    }
}

$page_title = 'Sign up | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'signup';
$head_extra = '    <script src="https://www.google.com/recaptcha/api.js" async defer></script>';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <h1>Create your account</h1>
                    <?php echo flash_html(); ?>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-card cn-form-card">
                        <div id="g_id_onload"
                             data-client_id="<?php echo e(GOOGLE_CLIENT_ID); ?>"
                             data-login_uri="<?php echo e(SITE_URL); ?>/google-auth"
                             data-auto_prompt="false"></div>
                        <div class="g_id_signin" data-type="standard" data-text="signup_with"
                             data-size="large" data-shape="rectangular"></div>
                        <script src="https://accounts.google.com/gsi/client" async defer></script>

                        <p class="cn-or">— or sign up with email —</p>

                        <form method="post" action="/signup" id="signup-form" class="cn-form">
                            <?php echo csrf_field(); ?>
                            <label>Username
                                <input type="text" name="username" required maxlength="30"
                                       value="<?php echo e($old['username']); ?>"></label>
                            <label>Email
                                <input type="email" name="email" required maxlength="190"
                                       value="<?php echo e($old['email']); ?>"></label>
                            <div class="cn-form-row">
                                <label>City
                                    <input type="text" name="city" maxlength="100"
                                           value="<?php echo e($old['city']); ?>"></label>
                                <label>Country
                                    <input type="text" name="country" maxlength="100"
                                           value="<?php echo e($old['country']); ?>"></label>
                            </div>
                            <p class="cn-muted">Your location was detected from your IP address —
                                correct it if it's wrong.</p>
                            <label>Password
                                <input type="password" name="password" required minlength="8"></label>
                            <label>Repeat password
                                <input type="password" name="password2" required minlength="8"></label>
                            <div class="g-recaptcha" data-sitekey="<?php echo e(RECAPTCHA2_SITE_KEY); ?>"></div>
                            <button type="submit" class="button blue">Sign up</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>

<?php
include __DIR__ . '/includes/footer.php';
