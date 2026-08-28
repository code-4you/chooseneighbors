<?php
/** Groups: browse, create, join / leave. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/ui.php';

$me = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$me) { flash_set('error', 'Please log in first.'); redirect('/login'); }
    if (!csrf_check()) { flash_set('error', 'Session expired — please try again.'); redirect('/groups'); }

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'create') {
        $name = trim((string)($_POST['name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        $loc  = trim((string)($_POST['location'] ?? ''));
        if (mb_strlen($name) < 3)  $errors[] = 'Group name: at least 3 characters.';
        if (!$errors) {
            $db = db();
            $db->prepare('INSERT INTO community_groups (owner_id, name, description, location) VALUES (?, ?, ?, ?)')
               ->execute([$me['id'], $name, $desc ?: null, $loc ?: null]);
            $gid = (int)$db->lastInsertId();
            $db->prepare('INSERT INTO group_members (group_id, user_id) VALUES (?, ?)')->execute([$gid, $me['id']]);
            flash_set('ok', 'Group created!');
            redirect('/group?id=' . $gid);
        }
    }

    if ($action === 'join' || $action === 'leave') {
        $gid = (int)($_POST['group_id'] ?? 0);
        $st = db()->prepare('SELECT id, owner_id FROM community_groups WHERE id = ?');
        $st->execute([$gid]);
        if ($g = $st->fetch()) {
            if ($action === 'join') {
                db()->prepare('INSERT IGNORE INTO group_members (group_id, user_id) VALUES (?, ?)')
                    ->execute([$gid, $me['id']]);
                flash_set('ok', 'You joined the group.');
            } elseif ((int)$g['owner_id'] !== (int)$me['id']) {
                db()->prepare('DELETE FROM group_members WHERE group_id = ? AND user_id = ?')
                    ->execute([$gid, $me['id']]);
                flash_set('ok', 'You left the group.');
            } else {
                flash_set('error', 'Owners cannot leave their own group.');
            }
        }
        redirect((string)($_POST['return_to'] ?? '/groups'));
    }
}

$rows = db()->query(
    'SELECT g.*, COALESCE(NULLIF(u.display_name,\'\'), u.username) AS owner_name,
            (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.id) AS member_count
       FROM community_groups g JOIN users u ON u.id = g.owner_id
      ORDER BY g.created_at DESC LIMIT 100'
)->fetchAll();

$myGroups = [];
if ($me) {
    $st = db()->prepare('SELECT group_id FROM group_members WHERE user_id = ?');
    $st->execute([$me['id']]);
    $myGroups = array_column($st->fetchAll(), 'group_id');
}

$page_title = 'Groups | Choose Neighbors';
$body_class = 'about has-bottom-footer';
$page_id    = 'groups';
include __DIR__ . '/includes/header.php';
?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <section class="standard-page">
                    <h1>Groups</h1>
                    <p>Create or join a group to plan a community, a street, or a building together.</p>
                    <?php echo flash_html(); ?>
                    <?php if ($errors): ?>
                        <div class="cn-flash cn-flash-error">
                            <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($me): ?>
                        <div class="cn-card cn-form-card">
                            <h3>Start a new group</h3>
                            <form method="post" action="/groups" class="cn-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="create">
                                <div class="cn-form-row">
                                    <label>Name
                                        <input type="text" name="name" required maxlength="120"
                                               placeholder="e.g. Lisbon co-housing 2026"></label>
                                    <label>Location (optional)
                                        <input type="text" name="location" maxlength="190"
                                               placeholder="City, Country"></label>
                                </div>
                                <label>Description (optional)
                                    <textarea name="description" rows="3" maxlength="5000"
                                              placeholder="What is this group about?"></textarea></label>
                                <button type="submit" class="button blue">Create group</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p class="cn-muted"><a href="/login">Log in</a> or <a href="/signup">sign up</a>
                            to create or join groups.</p>
                    <?php endif; ?>

                    <?php if (!$rows): ?>
                        <p>No groups yet.</p>
                    <?php else: ?>
                        <div class="cn-grid cn-grid-users">
                            <?php foreach ($rows as $g): ?>
                                <div class="cn-card cn-group-card">
                                    <h4><a href="/group?id=<?php echo (int)$g['id']; ?>"><?php echo e($g['name']); ?></a></h4>
                                    <?php if ($g['location']): ?>
                                        <p class="cn-loc"><i class="fa fa-map-marker"></i> <?php echo e($g['location']); ?></p>
                                    <?php endif; ?>
                                    <p class="cn-muted"><?php echo (int)$g['member_count']; ?> member<?php echo $g['member_count'] == 1 ? '' : 's'; ?>
                                        · by <?php echo e($g['owner_name']); ?>
                                        · <?php echo e(time_ago($g['created_at'])); ?></p>
                                    <?php if ($me): ?>
                                        <form method="post" action="/groups" class="cn-inline-form">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="group_id" value="<?php echo (int)$g['id']; ?>">
                                            <input type="hidden" name="return_to" value="/groups">
                                            <?php if (in_array($g['id'], $myGroups)): ?>
                                                <input type="hidden" name="action" value="leave">
                                                <button type="submit" class="button">Leave</button>
                                            <?php else: ?>
                                                <input type="hidden" name="action" value="join">
                                                <button type="submit" class="button green">Join</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
