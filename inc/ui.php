<?php
/** Small shared UI partials (listing cards, user cards, comment sections). */

/** One listing card (expects listing row joined with username + first image). */
function listing_card(array $l): string
{
    $img = $l['thumb_url'] ?: ($l['image_url'] ?? '');
    $imgHtml = $img
        ? '<img src="' . e($img) . '" alt="' . e($l['title']) . '" loading="lazy">'
        : '<div class="cn-noimg"><i class="fa fa-home"></i></div>';
    $badge = $l['type'] === 'community' ? 'Community' : 'Property';
    return '<a class="cn-card cn-listing-card" href="/listing?id=' . (int)$l['id'] . '">'
        . '<div class="cn-listing-img">' . $imgHtml . '</div>'
        . '<div class="cn-listing-body">'
        . '<span class="cn-badge cn-badge-' . e($l['type']) . '">' . $badge . '</span>'
        . '<h4>' . e($l['title']) . '</h4>'
        . '<p class="cn-loc"><i class="fa fa-map-marker"></i> ' . e($l['location']) . '</p>'
        . ($l['price'] ? '<p class="cn-price">' . e($l['price']) . '</p>' : '')
        . '<p class="cn-muted">by ' . e($l['username']) . ' · ' . e(time_ago($l['created_at'])) . '</p>'
        . '</div></a>';
}

/** One user card. */
function user_card(array $u): string
{
    $avatar = $u['avatar_url']
        ? '<img src="' . e($u['avatar_url']) . '" alt="" loading="lazy" referrerpolicy="no-referrer">'
        : '<span class="cn-avatar-letter">' . e(strtoupper(substr($u['username'], 0, 1))) . '</span>';
    $loc = trim(($u['city'] ? $u['city'] . ', ' : '') . ($u['country'] ?? ''));
    return '<div class="cn-card cn-user-card">'
        . '<div class="cn-avatar">' . $avatar . '</div>'
        . '<div class="cn-user-body">'
        . '<h4>' . e($u['username']) . '</h4>'
        . ($loc !== '' ? '<p class="cn-loc"><i class="fa fa-map-marker"></i> ' . e($loc) . '</p>' : '')
        . '<p class="cn-muted">joined ' . e(time_ago($u['created_at'])) . '</p>'
        . '<a class="cn-msg-link" href="/messages?to=' . (int)$u['id'] . '"><i class="fa fa-comments"></i> Message</a>'
        . '</div></div>';
}

/** Fetch comments for a target. */
function get_comments(string $type, int $id, int $limit = 50): array
{
    $st = db()->prepare(
        'SELECT c.*, COALESCE(NULLIF(u.display_name,\'\'), u.username) AS username, u.avatar_url FROM comments c
           JOIN users u ON u.id = c.user_id
          WHERE c.target_type = ? AND c.target_id = ?
          ORDER BY c.created_at DESC LIMIT ' . (int)$limit
    );
    $st->execute([$type, $id]);
    return $st->fetchAll();
}

/** Comment list + form for a target ('site' 0, 'listing' id, 'grp' id). */
function comments_section(string $type, int $id, ?array $me, string $returnTo): string
{
    $out = '<div class="cn-comments">';
    if ($me) {
        $out .= '<form method="post" action="/comment-post" class="cn-form cn-comment-form">'
            . csrf_field()
            . '<input type="hidden" name="target_type" value="' . e($type) . '">'
            . '<input type="hidden" name="target_id" value="' . (int)$id . '">'
            . '<input type="hidden" name="return_to" value="' . e($returnTo) . '">'
            . '<textarea name="body" rows="3" maxlength="3000" required placeholder="Write a comment…"></textarea>'
            . '<button type="submit" class="button blue">Post comment</button></form>';
    } else {
        $out .= '<p class="cn-muted"><a href="/#login">Log in</a> or <a href="/signup">sign up</a> to comment.</p>';
    }
    foreach (get_comments($type, $id) as $c) {
        $avatar = $c['avatar_url']
            ? '<img src="' . e($c['avatar_url']) . '" alt="" referrerpolicy="no-referrer">'
            : '<span class="cn-avatar-letter">' . e(strtoupper(substr($c['username'], 0, 1))) . '</span>';
        $edited = !empty($c['edited_at']) ? ' <span class="cn-muted">(edited)</span>' : '';
        $editLink = ($me && (int)$me['id'] === (int)$c['user_id'])
            ? ' · <a class="cn-edit-link" href="/comment-edit?id=' . (int)$c['id']
              . '&return_to=' . urlencode($returnTo) . '">edit</a>'
            : '';
        $out .= '<div class="cn-comment">'
            . '<div class="cn-avatar cn-avatar-sm">' . $avatar . '</div>'
            . '<div class="cn-comment-body">'
            . '<strong>' . e($c['username']) . '</strong> '
            . '<span class="cn-muted">' . e(time_ago($c['created_at'])) . '</span>'
            . $edited . $editLink
            . '<p>' . nl2br(e($c['body'])) . '</p>'
            . '</div></div>';
    }
    return $out . '</div>';
}
