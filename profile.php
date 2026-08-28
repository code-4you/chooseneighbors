<?php
/** Edit your profile: username, location, avatar, password. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$me = require_login();
$errors = [];
$old = [
    'username'     => $me['username'],
    'display_name' => (string)($me['display_name'] ?? ''),
    'city'         => (string)($me['city'] ?? ''),
    'country'      => (string)($me['country'] ?? ''),
];

/* ----- account deletion (danger zone) ----- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_account') {
    if (!csrf_check()) {
        flash_set('error', 'Session expired — please try again.');
        redirect('/profile');
    }
    $ok = false;
    if ($me['pass_hash'] !== null) {
        $ok = password_verify((string)($_POST['confirm_password'] ?? ''), $me['pass_hash']);
        if (!$ok) flash_set('error', 'Account NOT deleted: password was incorrect.');
    } else {
        $ok = trim((string)($_POST['confirm_username'] ?? '')) === $me['username'];
        if (!$ok) flash_set('error', 'Account NOT deleted: username did not match.');
    }
    if ($ok) {
        // remove comments (by anyone) on this user's listings and groups,
        // then let FK cascades remove everything else
        db()->prepare("DELETE FROM comments WHERE target_type = 'listing'
                          AND target_id IN (SELECT id FROM listings WHERE user_id = ?)")
            ->execute([$me['id']]);
        db()->prepare("DELETE FROM comments WHERE target_type = 'grp'
                          AND target_id IN (SELECT id FROM community_groups WHERE owner_id = ?)")
            ->execute([$me['id']]);
        // cascades remove listings (+images), own comments, group memberships,
        // owned groups, conversations + messages, tokens
        db()->prepare('DELETE FROM users WHERE id = ?')->execute([$me['id']]);
        $_SESSION = [];
        session_destroy();
        redirect('/?deleted=1');
    }
    redirect('/profile');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['username']     = trim((string)($_POST['username'] ?? $old['username']));
    $old['display_name'] = trim((string)($_POST['display_name'] ?? $old['display_name']));
    $newEmail        = strtolower(trim((string)($_POST['email'] ?? $me['email'])));
    $old['city']     = trim((string)($_POST['city'] ?? ''));
    $old['country']  = trim((string)($_POST['country'] ?? ''));
    $newPass  = (string)($_POST['new_password'] ?? '');
    $newPass2 = (string)($_POST['new_password2'] ?? '');
    $curPass  = (string)($_POST['current_password'] ?? '');

    if (!csrf_check()) $errors[] = 'Session expired — please try again.';

    if (mb_strlen($old['display_name']) > 80) {
        $errors[] = 'Display name: 80 characters max.';
    }

    if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $old['username'])) {
        $errors[] = 'Username: 3-30 characters, letters/numbers/._- only.';
    } elseif ($old['username'] !== $me['username']) {
        $st = db()->prepare('SELECT id FROM users WHERE username = ? AND id <> ?');
        $st->execute([$old['username'], $me['id']]);
        if ($st->fetch()) $errors[] = 'That username is already taken.';
    }

    // optional password change
    $passHash = null;
    if ($newPass !== '' || $newPass2 !== '') {
        if (strlen($newPass) < 8)      $errors[] = 'New password must be at least 8 characters.';
        if ($newPass !== $newPass2)    $errors[] = 'New passwords do not match.';
        // if the account already has a password, require the current one
        if ($me['pass_hash'] !== null && !password_verify($curPass, $me['pass_hash'])) {
            $errors[] = 'Current password is incorrect.';
        }
        if (!$errors) $passHash = password_hash($newPass, PASSWORD_DEFAULT);
    }

    // optional avatar upload (hosted on ImgBB)
    $avatarUrl = null;
    if (!empty($_FILES['avatar']['tmp_name']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $img = imgbb_upload($_FILES['avatar']['tmp_name']);
        if ($img) {
            $avatarUrl = $img['thumb'] ?: $img['url'];
        } else {
            $errors[] = IMGBB_API_KEY === ''
                ? 'Avatar was skipped: ImgBB API key is not configured yet.'
                : 'Avatar upload failed — please try a JPG/PNG under 10 MB.';
        }
    }

    // email change: confirm via link sent to the NEW address
    $emailChangeRequested = false;
    if (!$errors && $newEmail !== strtolower($me['email'])) {
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            $st = db()->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
            $st->execute([$newEmail, $me['id']]);
            if ($st->fetch()) $errors[] = 'That email is already used by another account.';
        }
        if (!$errors) $emailChangeRequested = true;
    }

    if (!$errors) {
        $sql    = 'UPDATE users SET username = ?, display_name = ?, city = ?, country = ?';
        $params = [$old['username'], $old['display_name'] ?: null, $old['city'] ?: null, $old['country'] ?: null];
        if ($passHash !== null)  { $sql .= ', pass_hash = ?';  $params[] = $passHash; }
        if ($avatarUrl !== null) { $sql .= ', avatar_url = ?'; $params[] = $avatarUrl; }
        $sql .= ' WHERE id = ?';
        $params[] = $me['id'];
        db()->prepare($sql)->execute($params);

        if ($emailChangeRequested) {
            db()->prepare('UPDATE users SET pending_email = ? WHERE id = ?')
                ->execute([$newEmail, $me['id']]);
            $token = create_token((int)$me['id'], 'email_change', 24);
            send_mail($newEmail, 'Confirm your new email — ' . SITE_NAME, mail_template(
                'Confirm your new email address',
                '<p>Hi ' . e($old['username']) . ',</p>'
                . '<p>You asked to change your ' . e(SITE_NAME) . ' account email to this address. '
                . 'Click below to confirm. Until then, your old address stays active.</p>',
                'Confirm new email',
                SITE_URL . '/email-confirm?token=' . $token
            ));
            flash_set('ok', 'Profile updated. To finish changing your email, click the '
                . 'confirmation link we just sent to ' . $newEmail . '.');
        } else {
            flash_set('ok', 'Profile updated.');
        }
        redirect('/profile');
    }
}

$page_title = 'Edit profile | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'profile';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <h1>Edit profile</h1>
                    <?php echo flash_html(); ?>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-card cn-form-card">
                        <div class="cn-profile-head">
                            <div class="cn-avatar"><?php
                                echo $me['avatar_url']
                                    ? '<img src="' . e($me['avatar_url']) . '" alt="" referrerpolicy="no-referrer">'
                                    : '<span class="cn-avatar-letter">' . e(strtoupper(substr($me['username'], 0, 1))) . '</span>';
                            ?></div>
                            <div>
                                <strong><?php echo e(trim((string)($me['display_name'] ?? '')) !== '' ? $me['display_name'] : $me['username']); ?></strong>
                                <p class="cn-muted"><?php echo e($me['email']); ?>
                                    · joined <?php echo e(time_ago($me['created_at'])); ?><?php
                                    if ($me['google_id']) echo ' · Google account'; ?></p>
                            </div>
                        </div>

                        <form method="post" action="/profile" enctype="multipart/form-data" class="cn-form">
                            <?php echo csrf_field(); ?>
                            <label>Display name <span class="cn-muted">(shown on the site — your full name is fine)</span>
                                <input type="text" name="display_name" maxlength="80"
                                       value="<?php echo e($old['display_name']); ?>"
                                       placeholder="e.g. Michael van Diermen"></label>
                            <label>Username <span class="cn-muted">(your unique handle for logging in)</span>
                                <input type="text" name="username" required maxlength="30"
                                       value="<?php echo e($old['username']); ?>"></label>
                            <label>Email
                                <input type="email" name="email" required maxlength="190"
                                       value="<?php echo e($me['email']); ?>"></label>
                            <?php if (!empty($me['pending_email'])): ?>
                                <p class="cn-muted">Pending change to
                                    <strong><?php echo e($me['pending_email']); ?></strong> —
                                    waiting for you to click the confirmation link we emailed there.</p>
                            <?php endif; ?>
                            <div class="cn-form-row">
                                <label>City
                                    <input type="text" name="city" maxlength="100"
                                           value="<?php echo e($old['city']); ?>"></label>
                                <label>Country
                                    <input type="text" name="country" maxlength="100"
                                           value="<?php echo e($old['country']); ?>"></label>
                            </div>
                            <label>Profile photo (JPG/PNG, max 10&nbsp;MB)
                                <input type="file" name="avatar" accept="image/*"></label>

                            <h3>Change password <span class="cn-muted">(optional)</span></h3>
                            <?php if ($me['pass_hash'] !== null): ?>
                                <label>Current password
                                    <input type="password" name="current_password" autocomplete="current-password"></label>
                            <?php else: ?>
                                <p class="cn-muted">You signed up with Google — set a password here to also
                                    log in with username + password.</p>
                            <?php endif; ?>
                            <div class="cn-form-row">
                                <label>New password
                                    <input type="password" name="new_password" minlength="8" autocomplete="new-password"></label>
                                <label>Repeat new password
                                    <input type="password" name="new_password2" minlength="8" autocomplete="new-password"></label>
                            </div>

                            <button type="submit" class="button blue">Save profile</button>
                        </form>
                    </div>

                    <div class="cn-card cn-form-card cn-danger">
                        <h3>Delete account</h3>
                        <p class="cn-muted">This permanently removes your account, listings, photos,
                            posts, groups you created, and messages. This cannot be undone.</p>
                        <form method="post" action="/profile" class="cn-form"
                              onsubmit="return confirm('Really delete your account and everything in it? This cannot be undone.');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="delete_account">
                            <?php if ($me['pass_hash'] !== null): ?>
                                <label>Type your password to confirm
                                    <input type="password" name="confirm_password" required
                                           autocomplete="current-password"></label>
                            <?php else: ?>
                                <label>Type your username (<?php echo e($me['username']); ?>) to confirm
                                    <input type="text" name="confirm_username" required></label>
                            <?php endif; ?>
                            <button type="submit" class="button cn-btn-danger">Delete my account</button>
                        </form>
                    </div>

                    <h3>Your content</h3>
                    <p class="cn-muted">You can edit your listings from their pages, your groups from the
                        group page, and your comments via the <em>edit</em> link next to them.</p>
                    <?php
                    $st = db()->prepare('SELECT id, title, created_at FROM listings WHERE user_id = ? ORDER BY created_at DESC');
                    $st->execute([$me['id']]);
                    $mine = $st->fetchAll();
                    if ($mine) {
                        echo '<ul>';
                        foreach ($mine as $l) {
                            echo '<li><a href="/listing?id=' . (int)$l['id'] . '">' . e($l['title']) . '</a>'
                               . ' — <a href="/listing-edit?id=' . (int)$l['id'] . '">edit</a></li>';
                        }
                        echo '</ul>';
                    }
                    $st = db()->prepare('SELECT id, name FROM community_groups WHERE owner_id = ? ORDER BY created_at DESC');
                    $st->execute([$me['id']]);
                    $mg = $st->fetchAll();
                    if ($mg) {
                        echo '<ul>';
                        foreach ($mg as $g) {
                            echo '<li>Group: <a href="/group?id=' . (int)$g['id'] . '">' . e($g['name']) . '</a>'
                               . ' — <a href="/group-edit?id=' . (int)$g['id'] . '">edit</a></li>';
                        }
                        echo '</ul>';
                    }
                    ?>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
