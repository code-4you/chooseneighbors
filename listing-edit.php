<?php
/** Edit your own listing: fields, add photos, remove photos. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$me = require_login();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$st = db()->prepare('SELECT * FROM listings WHERE id = ?');
$st->execute([$id]);
$l = $st->fetch();
if (!$l || (int)$l['user_id'] !== (int)$me['id']) {
    flash_set('error', 'You can only edit your own listings.');
    redirect('/listings');
}

$errors = [];
$old = [
    'type'        => $l['type'],
    'title'       => $l['title'],
    'description' => $l['description'],
    'location'    => $l['location'],
    'price'       => (string)($l['price'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_check()) {
        db()->prepare('DELETE FROM listings WHERE id = ? AND user_id = ?')->execute([$id, $me['id']]);
        db()->prepare("DELETE FROM comments WHERE target_type = 'listing' AND target_id = ?")->execute([$id]);
        flash_set('ok', 'Listing deleted.');
        redirect('/listings');
    }
    flash_set('error', 'Session expired — please try again.');
    redirect('/listing-edit?id=' . $id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) {
        $old[$k] = trim((string)($_POST[$k] ?? $v));
    }
    if (!csrf_check())                                     $errors[] = 'Session expired — please try again.';
    if (!in_array($old['type'], ['property', 'community'], true)) $old['type'] = 'property';
    if (mb_strlen($old['title']) < 5)                      $errors[] = 'Title: at least 5 characters.';
    if (mb_strlen($old['description']) < 20)               $errors[] = 'Description: at least 20 characters.';
    if ($old['location'] === '')                           $errors[] = 'Please enter a location.';

    if (!$errors) {
        $db = db();
        $db->prepare(
            'UPDATE listings SET type = ?, title = ?, description = ?, location = ?, price = ? WHERE id = ?'
        )->execute([$old['type'], $old['title'], $old['description'],
                    $old['location'], $old['price'] ?: null, $id]);

        // remove selected photos
        foreach ((array)($_POST['remove_images'] ?? []) as $imgId) {
            $db->prepare('DELETE FROM listing_images WHERE id = ? AND listing_id = ?')
               ->execute([(int)$imgId, $id]);
        }

        // add new photos (7 max in total)
        $count = (int)$db->query('SELECT COUNT(*) FROM listing_images WHERE listing_id = ' . $id)->fetchColumn();
        if (!empty($_FILES['images']['tmp_name']) && is_array($_FILES['images']['tmp_name'])) {
            foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                if ($count >= 7) break;
                if (($_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
                $img = imgbb_upload($tmp);
                if ($img) {
                    $db->prepare('INSERT INTO listing_images (listing_id, url, thumb_url, delete_url) VALUES (?, ?, ?, ?)')
                       ->execute([$id, $img['url'], $img['thumb'], $img['delete_url']]);
                    $count++;
                }
            }
        }

        flash_set('ok', 'Listing updated.');
        redirect('/listing?id=' . $id);
    }
}

$st = db()->prepare('SELECT * FROM listing_images WHERE listing_id = ? ORDER BY id');
$st->execute([$id]);
$images = $st->fetchAll();

$page_title = 'Edit listing | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'listing-edit';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <p><a href="/listing?id=<?php echo $id; ?>">&larr; Back to listing</a></p>
                    <h1>Edit listing</h1>
                    <?php echo flash_html(); ?>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-card cn-form-card">
                        <form method="post" action="/listing-edit" enctype="multipart/form-data" class="cn-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <label>Type
                                <select name="type">
                                    <option value="property"  <?php if ($old['type'] === 'property')  echo 'selected'; ?>>Property (house / flat / land)</option>
                                    <option value="community" <?php if ($old['type'] === 'community') echo 'selected'; ?>>Community (a whole project / neighborhood)</option>
                                </select></label>
                            <label>Title
                                <input type="text" name="title" required maxlength="190"
                                       value="<?php echo e($old['title']); ?>"></label>
                            <label>Location
                                <input type="text" name="location" required maxlength="190"
                                       value="<?php echo e($old['location']); ?>"></label>
                            <label>Price (optional, free text)
                                <input type="text" name="price" maxlength="100"
                                       value="<?php echo e($old['price']); ?>"></label>
                            <label>Description
                                <textarea name="description" rows="8" required maxlength="10000"><?php echo e($old['description']); ?></textarea></label>

                            <?php if ($images): ?>
                                <h3>Current photos <span class="cn-muted">(tick to remove)</span></h3>
                                <div class="cn-gallery cn-gallery-edit">
                                    <?php foreach ($images as $img): ?>
                                        <label class="cn-photo-remove">
                                            <img src="<?php echo e($img['thumb_url'] ?: $img['url']); ?>" alt="">
                                            <span><input type="checkbox" name="remove_images[]"
                                                         value="<?php echo (int)$img['id']; ?>"> remove</span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <label>Add photos (max 7 in total)
                                <input type="file" name="images[]" accept="image/*" multiple></label>
                            <button type="submit" class="button blue">Save changes</button>
                        </form>
                    </div>

                    <div class="cn-card cn-form-card cn-danger">
                        <h3>Delete listing</h3>
                        <p class="cn-muted">Removes this listing, its photos and its comments. Cannot be undone.</p>
                        <form method="post" action="/listing-edit" class="cn-inline-form"
                              onsubmit="return confirm('Really delete this listing? This cannot be undone.');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="button cn-btn-danger">Delete listing</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
