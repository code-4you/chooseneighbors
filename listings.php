<?php
/** All property / community listings. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/ui.php';

$type  = $_GET['type'] ?? '';
$where = in_array($type, ['property', 'community'], true) ? 'WHERE l.type = ' . db()->quote($type) : '';
$page  = max(1, (int)($_GET['page'] ?? 1));
$per   = 24;

$total = (int)db()->query("SELECT COUNT(*) FROM listings l $where")->fetchColumn();
$rows  = db()->query(
    "SELECT l.*, COALESCE(NULLIF(u.display_name,''), u.username) AS username,
            (SELECT thumb_url FROM listing_images li WHERE li.listing_id = l.id ORDER BY li.id LIMIT 1) AS thumb_url,
            (SELECT url       FROM listing_images li WHERE li.listing_id = l.id ORDER BY li.id LIMIT 1) AS image_url
       FROM listings l JOIN users u ON u.id = l.user_id
      $where
      ORDER BY l.created_at DESC
      LIMIT $per OFFSET " . (($page - 1) * $per)
)->fetchAll();

$page_title = 'Listings | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'listings';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <h1>Listings (<?php echo $total; ?>)</h1>
                    <?php echo flash_html(); ?>
                    <p class="cn-toolbar">
                        <a class="button <?php echo $type === '' ? 'blue' : ''; ?>" href="/listings">All</a>
                        <a class="button <?php echo $type === 'property' ? 'blue' : ''; ?>" href="/listings?type=property">Properties</a>
                        <a class="button <?php echo $type === 'community' ? 'blue' : ''; ?>" href="/listings?type=community">Communities</a>
                        <a class="button green" href="/create"><i class="fa fa-plus"></i> Create listing</a>
                    </p>

                    <?php if (!$rows): ?>
                        <p>No listings yet — <a href="/create">be the first to create one</a>!</p>
                    <?php else: ?>
                        <div class="cn-grid">
                            <?php foreach ($rows as $l) echo listing_card($l); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($total > $per): ?>
                        <p class="cn-toolbar">
                            <?php for ($p = 1; $p <= ceil($total / $per); $p++): ?>
                                <a class="button <?php echo $p === $page ? 'blue' : ''; ?>"
                                   href="/listings?<?php echo $type ? 'type=' . e($type) . '&' : ''; ?>page=<?php echo $p; ?>"><?php echo $p; ?></a>
                            <?php endfor; ?>
                        </p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
