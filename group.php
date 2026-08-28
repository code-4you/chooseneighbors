<?php
/** Single group page: members + group discussion. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/ui.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare(
    'SELECT g.*, COALESCE(NULLIF(u.display_name,\'\'), u.username) AS owner_name FROM community_groups g
       JOIN users u ON u.id = g.owner_id WHERE g.id = ?'
);
$st->execute([$id]);
$g = $st->fetch();

if (!$g) {
    flash_set('error', 'Group not found.');
    redirect('/groups');
}

$st = db()->prepare(
    'SELECT u.id, COALESCE(NULLIF(u.display_name,\'\'), u.username) AS username, u.avatar_url, u.city, u.country, gm.joined_at, u.created_at
       FROM group_members gm JOIN users u ON u.id = gm.user_id
      WHERE gm.group_id = ? ORDER BY gm.joined_at'
);
$st->execute([$id]);
$members = $st->fetchAll();

$me       = current_user();
$isMember = $me && in_array((int)$me['id'], array_map('intval', array_column($members, 'id')), true);

$page_title = $g['name'] . ' | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'group';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <?php echo flash_html(); ?>
                    <p><a href="/groups">&larr; All groups</a></p>
                    <h1><?php echo e($g['name']); ?></h1>
                    <?php if ($g['location']): ?>
                        <p class="cn-loc"><i class="fa fa-map-marker"></i> <?php echo e($g['location']); ?></p>
                    <?php endif; ?>
                    <p class="cn-muted">Created by <strong><?php echo e($g['owner_name']); ?></strong>
                        · <?php echo e(time_ago($g['created_at'])); ?>
                        · <?php echo count($members); ?> member<?php echo count($members) === 1 ? '' : 's'; ?></p>
                    <?php if ($g['description']): ?>
                        <div class="cn-listing-desc"><?php echo nl2br(e($g['description'])); ?></div>
                    <?php endif; ?>

                    <?php if ($me && (int)$me['id'] === (int)$g['owner_id']): ?>
                        <p><a class="button" href="/group-edit?id=<?php echo $id; ?>">
                            <i class="fa fa-pencil"></i> Edit group</a></p>
                    <?php endif; ?>

                    <?php if ($me): ?>
                        <form method="post" action="/groups" class="cn-inline-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="group_id" value="<?php echo $id; ?>">
                            <input type="hidden" name="return_to" value="/group?id=<?php echo $id; ?>">
                            <?php if ($isMember): ?>
                                <input type="hidden" name="action" value="leave">
                                <button type="submit" class="button">Leave group</button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="join">
                                <button type="submit" class="button green">Join group</button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>

                    <h3>Members</h3>
                    <div class="cn-grid cn-grid-users">
                        <?php foreach ($members as $m) echo user_card($m); ?>
                    </div>
                </section>

                <section class="standard-page">
                    <h1>Group discussion</h1>
                    <?php echo comments_section('grp', $id, $me, '/group?id=' . $id); ?>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
