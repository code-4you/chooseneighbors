<?php
/** Register your interest — location auto-selected from the visitor's IP. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$me     = current_user();
$geo    = geolocate_ip(client_ip());
$errors = [];
$old    = [
    'name'    => $me['username'] ?? '',
    'email'   => $me['email'] ?? '',
    'city'    => $geo['city'] ?? '',
    'region'  => $geo['region'] ?? '',
    'country' => $geo['country'] ?? '',
    'note'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) {
        $old[$k] = trim((string)($_POST[$k] ?? $v));
    }
    if (!csrf_check())                                        $errors[] = 'Session expired — please try again.';
    if (!recaptcha_ok())                                      $errors[] = 'reCAPTCHA check failed — please try again.';
    if ($old['name'] === '')                                  $errors[] = 'Please enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))    $errors[] = 'Please enter a valid email address.';
    if ($old['country'] === '')                               $errors[] = 'Please enter your country.';

    if (!$errors) {
        $st = db()->prepare(
            'INSERT INTO interests (user_id, name, email, city, region, country, lat, lng, ip, note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $me['id'] ?? null, $old['name'], strtolower($old['email']),
            $old['city'] ?: null, $old['region'] ?: null, $old['country'],
            $geo['lat'] ?? null, $geo['lng'] ?? null, client_ip(), $old['note'] ?: null,
        ]);
        flash_set('ok', 'Thanks — your interest is registered! We\'ll be in touch when something happens near ' . ($old['city'] ?: $old['country']) . '.');
        redirect('/interest');
    }
}

$page_title = 'Register your interest | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'interest';
$head_extra = '    <script src="https://www.google.com/recaptcha/api.js?render=' . RECAPTCHA_SITE_KEY . '"></script>';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <h1>Register your interest</h1>
                    <p>Interested in living where you choose your neighbors? Leave your details and we'll
                        contact you about projects near you. Your location was detected automatically from
                        your IP address — correct it if it's wrong.</p>
                    <?php echo flash_html(); ?>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-card cn-form-card">
                        <form method="post" action="/interest" id="interest-form" class="cn-form">
                            <?php echo csrf_field(); ?>
                            <label>Name
                                <input type="text" name="name" required maxlength="100"
                                       value="<?php echo e($old['name']); ?>"></label>
                            <label>Email
                                <input type="email" name="email" required maxlength="190"
                                       value="<?php echo e($old['email']); ?>"></label>
                            <div class="cn-form-row">
                                <label>City
                                    <input type="text" name="city" maxlength="100"
                                           value="<?php echo e($old['city']); ?>"></label>
                                <label>Region
                                    <input type="text" name="region" maxlength="100"
                                           value="<?php echo e($old['region']); ?>"></label>
                                <label>Country
                                    <input type="text" name="country" required maxlength="100"
                                           value="<?php echo e($old['country']); ?>"></label>
                            </div>
                            <label>Anything you'd like to tell us? (optional)
                                <textarea name="note" rows="4" maxlength="2000"><?php echo e($old['note']); ?></textarea></label>
                            <button type="submit" class="button blue">Register my interest</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>

<?php
$footer_extra = recaptcha_js('interest-form', 'interest');
include __DIR__ . '/includes/footer.php';
