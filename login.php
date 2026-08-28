<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

/* ---------- POST: process a login attempt ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_check()) {
        flash_set('error', 'Session expired — please try again.');
        redirect('/login');
    }

    $id   = trim((string)($_POST['user_username'] ?? ''));   // username OR email
    $pass = (string)($_POST['user_password'] ?? '');

    $st = db()->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
    $st->execute([$id, $id]);
    $user = $st->fetch();

    if (!$user || $user['pass_hash'] === null || !password_verify($pass, $user['pass_hash'])) {
        flash_set('error', 'Incorrect username or password.');
        redirect('/login');
    }

    login_user((int)$user['id']);
    flash_set('ok', 'Welcome back, ' . $user['username'] . '!');
    redirect('/');
}

/* ---------- GET: render the login / signup page ---------- */
$me = current_user();
if ($me) {
    redirect('/');
}

$page_title = 'Sign up or log in';
$page_desc  = 'Sign up for Choose Neighbors or log in to your account — find and create homes and communities where you choose your neighbours.';
$body_class = 'home has-bottom-footer';
$head_extra = "\n    <script src=\"https://www.google.com/recaptcha/api.js?render=" . RECAPTCHA_SITE_KEY . "\"></script>"
            . "\n    <script src=\"https://accounts.google.com/gsi/client\" async defer></script>";
$footer_extra = recaptcha_js('login-form', 'login');
include __DIR__ . '/includes/header.php';
?>

    <div class="content-page">
        <div class="limiter">

            <div class="container-login100" id="login">
                <div class="wrap-login100">

                    <form accept-charset="UTF-8" action="/login" class="custom-form login100-form" id="login-form" method="post">

                        <?php echo csrf_field(); ?>

                        <span class="login100-form-title ">Sign up</span>

                        <a href="/signup" class="button more-3 d-block mt-2"> Create your account<i class="fa fa-arrow-right ml-1"></i></a>

                        <div style="margin-top:40px; clear:both; width:100%">
                            <center><span class="login100-form-title" style="padding-bottom:20px"> OR </span></center>
                        </div>

                        <span class="login100-form-title ">Login</span>

                        <div class="cn-gsi">
                            <div id="g_id_onload"
                                 data-client_id="<?php echo e(GOOGLE_CLIENT_ID); ?>"
                                 data-login_uri="<?php echo e(SITE_URL); ?>/google-auth"
                                 data-auto_prompt="false"></div>
                            <div class="g_id_signin" data-type="standard" data-text="signin_with"
                                 data-size="large" data-shape="rectangular"></div>
                        </div>

                        <?php echo flash_html(); ?>

                        <input id="username" name="user_username" placeholder="Username or email" required="required" size="30" type="text" value="">
                        <input id="password" name="user_password" placeholder="Password" required="required" size="30" type="password" value="">

                        <div class="login-button">
                            <input style="margin-right: 10px" class="submit-button button blue" name="commit" type="submit" value="Log in">
                            <a href="/remind" class="help">Forgot password?</a>
                        </div>

                    </form>

                    <div class="login100-more">
                        <div class="login100_content">
                            <p></p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
