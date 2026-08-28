<?php
/** Create a property / community listing (images hosted on ImgBB). */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';

$me = require_login();
$errors = [];
$old = ['type' => 'property', 'title' => '', 'description' => '', 'location' => '', 'price' => ''];

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
            'INSERT INTO listings (user_id, type, title, description, location, price)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$me['id'], $old['type'], $old['title'], $old['description'],
                    $old['location'], $old['price'] ?: null]);
        $listingId = (int)$db->lastInsertId();

        // upload up to 7 images to ImgBB
        $uploaded = 0;
        if (!empty($_FILES['images']['tmp_name']) && is_array($_FILES['images']['tmp_name'])) {
            foreach (array_slice($_FILES['images']['tmp_name'], 0, 7, true) as $i => $tmp) {
                if (($_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
                $img = imgbb_upload($tmp);
                if ($img) {
                    $db->prepare('INSERT INTO listing_images (listing_id, url, thumb_url, delete_url) VALUES (?, ?, ?, ?)')
                       ->execute([$listingId, $img['url'], $img['thumb'], $img['delete_url']]);
                    $uploaded++;
                }
            }
        }

        $msg = 'Your listing is live!';
        if (!$uploaded && !empty(array_filter($_FILES['images']['name'] ?? []))) {
            $msg .= IMGBB_API_KEY === ''
                ? ' (Images were skipped: ImgBB API key is not configured yet.)'
                : ' (Some images could not be uploaded.)';
        }
        flash_set('ok', $msg);
        redirect('/listing?id=' . $listingId);
    }
}

$page_title = 'Create a listing | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'create';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <h1>Create a listing</h1>
                    <p>List a property, or a whole community project, where people can choose their
                        neighbors. Photos are uploaded to free ImgBB hosting.</p>
                    <?php echo flash_html(); ?>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-card cn-form-card">
                        <form method="post" action="/create" enctype="multipart/form-data" class="cn-form">
                            <?php echo csrf_field(); ?>
                            <label>Type
                                <select name="type">
                                    <option value="property"  <?php if ($old['type'] === 'property')  echo 'selected'; ?>>Property (house / flat / land)</option>
                                    <option value="community" <?php if ($old['type'] === 'community') echo 'selected'; ?>>Community (a whole project / neighborhood)</option>
                                </select></label>
                            <label>Title
                                <input type="text" name="title" required maxlength="190"
                                       placeholder="e.g. 3-bedroom house in a friends-first street"
                                       value="<?php echo e($old['title']); ?>"></label>
                            <label>Location
                                <input type="text" name="location" required maxlength="190"
                                       placeholder="City, Country" value="<?php echo e($old['location']); ?>"></label>
                            <label>Price (optional, free text)
                                <input type="text" name="price" maxlength="100"
                                       placeholder="e.g. €600/month or For sale: €150,000"
                                       value="<?php echo e($old['price']); ?>"></label>
                            <label>Description
                                <textarea name="description" rows="8" required maxlength="10000"
                                          placeholder="Describe the property or community, and how neighbors are chosen…"><?php echo e($old['description']); ?></textarea></label>
                            <label>Photos (up to 7, JPG/PNG/GIF/WebP, max 10&nbsp;MB each)
                                <input type="file" name="images[]" accept="image/*" multiple></label>
                            <button type="submit" class="button blue">Publish listing</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
