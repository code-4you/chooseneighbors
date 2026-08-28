<?php
/** Edit your own group (owner only). */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$me = require_login();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$st = db()->prepare('SELECT * FROM community_groups WHERE id = ?');
$st->execute([$id]);
$g = $st->fetch();
if (!$g || (int)$g['owner_id'] !== (int)$me['id']) {
    flash_set('error', 'You can only edit groups you created.');
    redirect('/groups');
}

$errors = [];
$old = [
    'name'        => $g['name'],
    'description' => (string)($g['description'] ?? ''),
    'location'    => (string)($g['location'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_check()) {
        db()->prepare('DELETE FROM community_groups WHERE id = ? AND owner_id = ?')->execute([$id, $me['id']]);
        db()->prepare("DELETE FROM comments WHERE target_type = 'grp' AND target_id = ?")->execute([$id]);
        flash_set('ok', 'Group deleted.');
        redirect('/groups');
    }
    flash_set('error', 'Session expired — please try again.');
    redirect('/group-edit?id=' . $id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) {
        $old[$k] = trim((string)($_POST[$k] ?? $v));
    }
    if (!csrf_check())                $errors[] = 'Session expired — please try again.';
    if (mb_strlen($old['name']) < 3)  $errors[] = 'Group name: at least 3 characters.';
    if (!$errors) {
        db()->prepare('UPDATE community_groups SET name = ?, description = ?, location = ? WHERE id = ?')
            ->execute([$old['name'], $old['description'] ?: null, $old['location'] ?: null, $id]);
        flash_set('ok', 'Group updated.');
        redirect('/group?id=' . $id);
    }
}

$page_title = 'Edit group | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'group-edit';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <p><a href="/group?id=<?php echo $id; ?>">&larr; Back to group</a></p>
                    <h1>Edit group</h1>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-card cn-form-card">
                        <form method="post" action="/group-edit" class="cn-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <label>Name
                                <input type="text" name="name" required maxlength="120"
                                       value="<?php echo e($old['name']); ?>"></label>
                            <label>Location (optional)
                                <input type="text" name="location" maxlength="190"
                                       value="<?php echo e($old['location']); ?>"></label>
                            <label>Description (optional)
                                <textarea name="description" rows="4" maxlength="5000"><?php echo e($old['description']); ?></textarea></label>
                            <button type="submit" class="button blue">Save group</button>
                        </form>
                    </div>

                    <div class="cn-card cn-form-card cn-danger">
                        <h3>Delete group</h3>
                        <p class="cn-muted">Removes the group, its members list and its discussion. Cannot be undone.</p>
                        <form method="post" action="/group-edit" class="cn-inline-form"
                              onsubmit="return confirm('Really delete this group? This cannot be undone.');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="button cn-btn-danger">Delete group</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
