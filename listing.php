<?php
/** Single listing page with photo gallery and comments. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/ui.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare(
    'SELECT l.*, COALESCE(NULLIF(u.display_name,\'\'), u.username) AS username, u.id AS owner_id, u.avatar_url
       FROM listings l JOIN users u ON u.id = l.user_id WHERE l.id = ?'
);
$st->execute([$id]);
$l = $st->fetch();

if (!$l) {
    flash_set('error', 'Listing not found.');
    redirect('/listings');
}

$st = db()->prepare('SELECT * FROM listing_images WHERE listing_id = ? ORDER BY id');
$st->execute([$id]);
$images = $st->fetchAll();

$me = current_user();

$page_title = $l['title'] . ' | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'listing';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <?php echo flash_html(); ?>
                    <p><a href="/listings">&larr; All listings</a></p>
                    <span class="cn-badge cn-badge-<?php echo e($l['type']); ?>">
                        <?php echo $l['type'] === 'community' ? 'Community' : 'Property'; ?></span>
                    <h1><?php echo e($l['title']); ?></h1>
                    <p class="cn-loc"><i class="fa fa-map-marker"></i> <?php echo e($l['location']); ?>
                        <?php if ($l['price']): ?> · <strong><?php echo e($l['price']); ?></strong><?php endif; ?></p>
                    <p class="cn-muted">Listed by <strong><?php echo e($l['username']); ?></strong>
                        · <?php echo e(time_ago($l['created_at'])); ?>
                        <?php if ($me && (int)$me['id'] !== (int)$l['owner_id']): ?>
                            · <a href="/messages?to=<?php echo (int)$l['owner_id']; ?>">
                                <i class="fa fa-comments"></i> Message <?php echo e($l['username']); ?></a>
                        <?php endif; ?>
                        <?php if ($me && (int)$me['id'] === (int)$l['owner_id']): ?>
                            · <a href="/listing-edit?id=<?php echo $id; ?>"><i class="fa fa-pencil"></i> Edit listing</a>
                        <?php endif; ?></p>

                    <?php if ($images): ?>
                        <div class="cn-gallery">
                            <?php foreach ($images as $img): ?>
                                <a href="<?php echo e($img['url']); ?>" target="_blank" rel="noopener">
                                    <img src="<?php echo e($img['thumb_url'] ?: $img['url']); ?>"
                                         alt="<?php echo e($l['title']); ?>" loading="lazy"></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cn-listing-desc"><?php echo nl2br(e($l['description'])); ?></div>
                </section>

                <section class="standard-page">
                    <h1>Comments</h1>
                    <?php echo comments_section('listing', $id, $me, '/listing?id=' . $id); ?>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
