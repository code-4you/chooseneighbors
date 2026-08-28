<?php
/**
 * Shared page header.
 * Set before including:
 *   $page_title  - page <title> and og:title (required)
 *   $page_desc   - meta description (optional; falls back to site default)
 *   $body_class  - class attribute for <body> (default: "about has-bottom-footer")
 *   $head_extra  - optional extra HTML for <head> (scripts, og tags)
 */
$page_title = isset($page_title) ? $page_title : 'Choose Neighbors';
$page_desc  = isset($page_desc) ? $page_desc
            : 'Choose Neighbors helps you find, create and join homes and communities where you choose your neighbours — live near friends, design your own neighbourhood, and share daily life.';
$body_class = isset($body_class) ? $body_class : 'about has-bottom-footer';
$head_extra = isset($head_extra) ? $head_extra : '';

/* Brand the title unless the page already includes it */
if (stripos($page_title, 'Choose Neighbors') === false) {
    $page_title .= ' | Choose Neighbors';
}

/* Canonical URL: https, non-www, extensionless */
$canon_path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$canon_path = preg_replace('/\.php$/', '', $canon_path);
if ($canon_path === '/index' || $canon_path === '') $canon_path = '/';
$canonical = 'https://chooseneighbors.com' . $canon_path;

/* Auth-aware nav (fails soft on static pages / before DB setup) */
$nav_user   = null;
$nav_unread = 0;
if (is_file(dirname(__DIR__) . '/config.php')) {
    require_once dirname(__DIR__) . '/config.php';
    require_once dirname(__DIR__) . '/inc/helpers.php';
    try {
        $nav_user   = current_user();
        $nav_unread = $nav_user ? unread_count($nav_user) : 0;
    } catch (Throwable $e) {
        $nav_user = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="google-site-verification" content="" />
    <meta name='yandex-verification' content='' />
    <meta name="msvalidate.01" content="" />
    <meta property="og:site_name" content="Choose Neighbors">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">
    <link href="/img/favicon.png?v=5" rel="shortcut icon" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Oswald:wght@600&display=swap" />
    <link rel="stylesheet" href="/css/styles.css?x=7" type="text/css" media="screen">
<?php echo $head_extra; ?>

<!-- Global site tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RGETBRPSNS">
</script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-RGETBRPSNS');
</script>
<!-- End, Global site tag (gtag.js) -->

</head>
<body class="<?php echo htmlspecialchars($body_class); ?>">

    
        <div id="backdrop" class="sn-backdrop" style="opacity: 0;"></div>

        <div id="sidenav" class="sn-sidenav" style="transform: translate3d(-380px, 0px, 0px);">

            <div class="top-header" id="sn-topbar">
                <div class="container">

                    <div class="d-flex">

                        <div class="burger-icon d-block menu-toggle">
                            <div class="burger-container">
                                <span class="burger-bun-top"></span>
                                <span class="burger-filling"></span>
                                <span class="burger-bun-bot"></span>
                            </div>
                        </div>

                        <a class="logo" href="/">
                            <img class="header-brand-img" src="/img/logo.png?v=4" alt="Choose Neighbors" title="Choose Neighbors">
                        </a>
                    </div>
                </div>
            </div>

            <div class="sidenav-content">

            </div>

        </div>

        <div class="top-header not-authorized">
            <div class="container">

                <div class="d-flex">

                    <div class="burger-icon d-block d-lg-none menu-toggle hidden">
                        <div class="burger-container">
                            <span class="burger-bun-top"></span>
                            <span class="burger-filling"></span>
                            <span class="burger-bun-bot"></span>
                        </div>
                    </div>

                    <a class="logo" href="/">
                        <img class="header-brand-img" src="/img/logo.png?v=4" alt="Choose Neighbors" title="Choose Neighbors">
                    </a>


                    <div class="d-flex align-items-center order-lg-2 ml-auto">

<div class="nav-item">
                                <a href="/interest" class="topbar-button" title="Register your interest">
                                    <span class="new-item d-sm-inline-block">Register your interest</span>
                                </a>
</div>

<div class="nav-item">
                                <a href="/support" class="topbar-button" title="Invest">
                                    <span class="new-item d-sm-inline-block">Invest</span>
                                </a>
</div>

<div class="nav-item d-none d-md-block">
                                <a href="/listings" class="topbar-button" title="Listings">
                                    <span class="new-item d-sm-inline-block">Listings</span>
                                </a>
</div>

<?php if ($nav_user): ?>

                            <div class="nav-item p-0">
                                <a href="/messages" class="topbar-button" title="Messages">
                                    <span class="new-item d-sm-inline-block"><i class="fa fa-comments"></i> Messages<?php
                                        echo $nav_unread > 0 ? ' <span class="cn-unread-badge">' . (int)$nav_unread . '</span>' : '';
                                    ?></span>
                                </a>
                            </div>

                            <div class="nav-item p-0 d-none d-md-block">
                                <a href="/users" class="topbar-button" title="People">
                                    <span class="new-item d-sm-inline-block">People</span>
                                </a>
                            </div>

                            <div class="nav-item p-0 cn-dd">
                                <a href="/profile" class="topbar-button" title="Account" aria-haspopup="true">
                                    <span class="new-item d-sm-inline-block"><?php echo htmlspecialchars(trim((string)($nav_user['display_name'] ?? '')) !== '' ? $nav_user['display_name'] : $nav_user['username']); ?> <i class="fa fa-caret-down"></i></span>
                                </a>
                                <div class="cn-dd-menu">
                                    <a href="/profile"><i class="fa fa-user"></i> Edit profile</a>
                                    <a href="/messages"><i class="fa fa-comments"></i> Messages</a>
                                    <a href="/create"><i class="fa fa-plus"></i> Create listing</a>
                                    <a href="/groups"><i class="fa fa-users"></i> Groups</a>
                                    <a href="/logout"><i class="fa fa-sign-out"></i> Log out</a>
                                </div>
                            </div>

<?php else: ?>

                            <div class="nav-item p-0">
                                <a href="/login" class="topbar-button" title="Log in">
                                    <span class="new-item d-sm-inline-block">Log in</span>
                                </a>
                            </div>

                            <div class="nav-item p-0">
                                <a href="/signup" class="topbar-button" title="Sign up">
                                    <span class="new-item d-sm-inline-block">Sign up</span>
                                </a>
                            </div>

<?php endif; ?>

                    </div>
                    
                </div>
            </div>
        </div>

        
