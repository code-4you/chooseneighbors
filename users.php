<?php
/** All registered users. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/ui.php';

$page  = max(1, (int)($_GET['page'] ?? 1));
$per   = 40;
$total = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$rows  = db()->query(
    'SELECT id, COALESCE(NULLIF(display_name,\'\'), username) AS username, avatar_url, city, country, created_at
       FROM users
      ORDER BY created_at DESC
      LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per)
)->fetchAll();

$page_title = 'People | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'users';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <h1>People (<?php echo $total; ?>)</h1>
                    <p>Everyone building communities on Choose Neighbors — newest first.
                        Say hello with the site chat!</p>
                    <?php echo flash_html(); ?>

                    <?php if (!$rows): ?>
                        <p>No users yet — <a href="/signup">be the first to sign up</a>!</p>
                    <?php else: ?>
                        <div class="cn-grid cn-grid-users">
                            <?php foreach ($rows as $u) echo user_card($u); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($total > $per): ?>
                        <p class="cn-toolbar">
                            <?php for ($p = 1; $p <= ceil($total / $per); $p++): ?>
                                <a class="button <?php echo $p === $page ? 'blue' : ''; ?>"
                                   href="/users?page=<?php echo $p; ?>"><?php echo $p; ?></a>
                            <?php endfor; ?>
                        </p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
