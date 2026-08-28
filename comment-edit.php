<?php
/** Edit your own comment (wall, listing, or group post). */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$me = require_login();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$returnTo = (string)($_GET['return_to'] ?? $_POST['return_to'] ?? '/');
if (!str_starts_with($returnTo, '/')) $returnTo = '/';

$st = db()->prepare('SELECT * FROM comments WHERE id = ?');
$st->execute([$id]);
$c = $st->fetch();
if (!$c || (int)$c['user_id'] !== (int)$me['id']) {
    flash_set('error', 'You can only edit your own posts.');
    redirect($returnTo);
}

$errors = [];
$body = $c['body'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_check()) {
        db()->prepare('DELETE FROM comments WHERE id = ? AND user_id = ?')->execute([$id, $me['id']]);
        flash_set('ok', 'Post deleted.');
    } else {
        flash_set('error', 'Session expired — please try again.');
    }
    redirect($returnTo);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = trim((string)($_POST['body'] ?? ''));
    if (!csrf_check())                                    $errors[] = 'Session expired — please try again.';
    if (mb_strlen($body) < 2 || mb_strlen($body) > 3000)  $errors[] = 'Post must be between 2 and 3000 characters.';
    if (!$errors) {
        db()->prepare('UPDATE comments SET body = ?, edited_at = NOW() WHERE id = ?')
            ->execute([$body, $id]);
        flash_set('ok', 'Post updated.');
        redirect($returnTo);
    }
}

$page_title = 'Edit post | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'comment-edit';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <p><a href="<?php echo e($returnTo); ?>">&larr; Back</a></p>
                    <h1>Edit your post</h1>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-card cn-form-card">
                        <form method="post" action="/comment-edit" class="cn-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="return_to" value="<?php echo e($returnTo); ?>">
                            <label>Post
                                <textarea name="body" rows="5" maxlength="3000" required><?php echo e($body); ?></textarea></label>
                            <button type="submit" class="button blue">Save post</button>
                        </form>
                        <form method="post" action="/comment-edit" class="cn-inline-form"
                              onsubmit="return confirm('Delete this post? This cannot be undone.');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="return_to" value="<?php echo e($returnTo); ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="button cn-btn-danger">Delete post</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
