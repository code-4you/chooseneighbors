<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/ui.php';
require_once __DIR__ . '/inc/community-directory.php';

$me = current_user();

/* Homepage data (fails soft if the database isn't configured yet) */
$latestListings = $newestUsers = $latestGroups = [];
$listingCount = 0;
try {
    $latestListings = db()->query(
        "SELECT l.*, COALESCE(NULLIF(u.display_name,''), u.username) AS username,
                (SELECT thumb_url FROM listing_images li WHERE li.listing_id = l.id ORDER BY li.id LIMIT 1) AS thumb_url,
                (SELECT url       FROM listing_images li WHERE li.listing_id = l.id ORDER BY li.id LIMIT 1) AS image_url
           FROM listings l JOIN users u ON u.id = l.user_id
          ORDER BY l.created_at DESC LIMIT 8"
    )->fetchAll();
    $listingCount = (int)db()->query('SELECT COUNT(*) FROM listings')->fetchColumn();
    $newestUsers = db()->query(
        'SELECT id, COALESCE(NULLIF(display_name,\'\'), username) AS username, avatar_url, city, country, created_at
           FROM users ORDER BY created_at DESC LIMIT 8'
    )->fetchAll();
    $latestGroups = db()->query(
        'SELECT g.*, COALESCE(NULLIF(u.display_name,\'\'), u.username) AS owner_name,
                (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.id) AS member_count
           FROM community_groups g JOIN users u ON u.id = g.owner_id
          ORDER BY g.created_at DESC LIMIT 6'
    )->fetchAll();
} catch (Throwable $e) {
    // DB not ready — homepage still renders
}

/* Communities near you — same detection & generation as /find-a-community */
$cn_region = ''; $cn_country = ''; $cn_list = [];
try {
    fac_ensure_schema();
    list($cn_country, $cn_region) = fac_detect_region();
    if ($cn_region !== '') {
        $cnq = db()->prepare('SELECT * FROM community_directory WHERE country=? AND region=? ORDER BY RAND() LIMIT 8');
        $cnq->execute([$cn_country, $cn_region]);
        $cn_list = $cnq->fetchAll();
        if (count($cn_list) === 0 && fac_populate_region($cn_country, $cn_region, $TYPES) === 'done') {
            $cnq->execute([$cn_country, $cn_region]);
            $cn_list = $cnq->fetchAll();
        }
    }
} catch (Throwable $e) {
    $cn_list = [];
}

$page_title = 'Choose Neighbors — homes where you choose your neighbours';
$page_desc  = 'Choose Neighbors is building affordable homes and communities designed around friendship: live near friends, design your own neighbourhood, and share daily life. Browse listings, join a group, or register your interest.';
$body_class = 'home has-bottom-footer main-page';
$page_id    = 'main';
$head_extra = <<<'HTML'
    <meta property="og:url" content="https://chooseneighbors.com" />
    <meta property="og:image" content="https://chooseneighbors.com/img/panel_logo.png" />
    <meta property="og:description" content="Choose Neighbors" />
HTML;
$head_extra .= "\n    <script src=\"https://www.google.com/recaptcha/api.js?render=" . RECAPTCHA_SITE_KEY . "\"></script>"
             . "\n    <script src=\"https://accounts.google.com/gsi/client\" async defer></script>"
             . "\n    <style>body.home .content-page{background:#fff}</style>";
$footer_extra = '';
include __DIR__ . '/includes/header.php';
?>

    <div class="content-page">

        <div class="limiter">
            
            
            
            
            
            
            
            
            
            
            
            
            
            
<div style="max-width:1100px;margin:30px auto">
  <h1 style="font-size:clamp(24px,4vw,38px);text-align:center;margin:0 0 18px;font-weight:600">Choose your neighbors &mdash; homes designed around friendship</h1>
  <div style="position:relative;width:100%;aspect-ratio:16/9">
    <iframe src="choose-neighbors-film.html" title="Choose Neighbors — the film" allowfullscreen
            style="position:absolute;inset:0;width:100%;height:100%;border:0"></iframe>
  </div>
</div>











            
            

                            <div class="wrap-landing-info-container explore-promo">

                    <div class="wrap-landing-info">
                        
                        
                        
                        
                        
                       
                        
                        
                        
                
<p style=text-align:left;">


</p>
                       
                          
                           </br> </br>
                          <p>
                         There are currently <a style="color:#8f3f2b;text-decoration:underline;font-weight:600" href="/listings"> (<?php echo $listingCount; ?>) listings</a> of accommodation where you can choose your Neighbors.   </br> </br>

                         Create your accommodation <a style="color:#8f3f2b;text-decoration:underline;font-weight:600" href="/create">listing now</a>.
                         </br> </br>

                          
                         
                           <ul> <li style="
    text-align: left;" > Sense of community to support better energy.</li> <li style="
    text-align: left;" >  See your friends more often.</li> <li style="
    text-align: left;" > Organise local events, activities and work. </li> </ul>
                         
                         </p>
                         
                        
                                                                   </div>
                </div>
                
<!-- ===== THE DYNAMIC MAP — core mechanic section ===== -->
<section class="dmap">
  <style>
    .dmap{box-sizing:border-box;max-width:1000px;margin:34px auto;padding:38px 7% 34px;
      background:#f3efe6;border:1px solid #d4ccbd;border-radius:12px;
      font-family:'Google Sans','Outfit',Arial,sans-serif;color:#1e2428;line-height:1.5}
    .dmap *{box-sizing:border-box}
    .dmap .dm-eyebrow{font-weight:600;text-transform:uppercase;letter-spacing:.24em;font-size:11.5px;color:#8f3f2b;margin:0 0 10px;display:flex;align-items:center;gap:12px}
    .dmap .dm-eyebrow::before{content:"";width:28px;height:2px;background:#8f3f2b}
    .dmap h2{font-weight:600;font-size:clamp(23px,3.5vw,32px);line-height:1.05;letter-spacing:-.02em;margin:0 0 14px;color:#24201c}
    .dmap h2 em{font-style:italic;color:#8f3f2b}
    .dmap .dm-intro{font-size:16.5px;color:#4a443d;max-width:64ch;margin:0 0 24px}
    .dmap .dm-grid{display:grid;grid-template-columns:1fr;gap:14px;margin:0 0 22px}
    @media(min-width:640px){.dmap .dm-grid{grid-template-columns:1fr 1fr}}
    .dmap .dm-card{background:#fff;border:1px solid #d4ccbd;border-radius:10px;padding:18px 20px}
    .dmap .dm-card b{display:block;font-weight:600;font-size:16px;margin-bottom:6px;color:#24201c}
    .dmap .dm-card b .n{color:#b35238;font-weight:700;margin-right:8px}
    .dmap .dm-card p{font-size:14.5px;color:#5d564c;line-height:1.5;margin:0}
    .dmap .dm-ai{background:#24201c;color:#e4d8c6;border-radius:10px;padding:20px 24px;font-size:15.5px;line-height:1.55;margin:0 0 18px}
    .dmap .dm-ai b{color:#f4ece0}
    .dmap .dm-ai a{color:#e3a564;text-decoration:none;border-bottom:1px solid rgba(227,165,100,.4)}
    .dmap .dm-links{font-size:15px;font-weight:600}
    .dmap .dm-links a{color:#8f3f2b;text-decoration:none;border-bottom:1px solid #d4ccbd;padding-bottom:1px;margin-right:22px}
    .dmap .dm-links a:hover{color:#b35238;border-color:#b35238}
  </style>
  <p class="dm-eyebrow">The core mechanic</p>
  <h2>Not membership housing. <em>A living map.</em></h2>
  <p class="dm-intro">Co-ops and cohousing fill vacancies as they come, and a board decides who gets in. Choose Neighbors runs on a <strong>dynamic map</strong> instead &mdash; you ask to live near your people, space is deliberately kept open so your group can grow, and anyone can move &mdash; to other people, or other areas &mdash; at any time. Four moving parts:</p>
  <div class="dm-grid">
    <div class="dm-card"><b><span class="n">01</span>You choose, by application</b><p>Through a simple matching app you ask to live near friends &mdash; or people whose ideas and energy you share. Groups can choose other groups.</p></div>
    <div class="dm-card"><b><span class="n">02</span>Room is always kept free</b><p>A share of space is deliberately held open, so when a friend wants to join you &mdash; or you want to move closer to someone &mdash; there's actually somewhere to go. The piece every other model is missing.</p></div>
    <div class="dm-card"><b><span class="n">03</span>You can move, freely</b><p>You own your home and sell back at a fair formula, so moving is never a financial trap. Shift toward new friends, or away from friction, whenever you like.</p></div>
    <div class="dm-card"><b><span class="n">04</span>A map placed by who you know</b><p>A nearest-neighbours system positions people close to those they chose &mdash; the layout reflects real bonds, not the accident of who signed a lease first.</p></div>
  </div>
  <p class="dm-ai"><b>This was always designed as a program &mdash; and AI now makes it practical.</b> The matching, the map, the held-open space, homes swapping as neighbours and budgets change: what once needed a bespoke system, <a href="/aibasedcommunity/personal-agent.html">a personal AI agent</a> and today's models can actually run. As far as we can find, this combination doesn't exist anywhere else &mdash; <a href="/the-fragments.html">here's the evidence file</a>.</p>
  <p class="dm-links"><a href="/belonging/choose-your-neighbours.html">Read the full idea &rarr;</a><a href="/the-fragments.html">The fragments &rarr;</a><a href="/start-a-community">How to start &rarr;</a></p>
</section>
<!-- ===== END DYNAMIC MAP SECTION ===== -->

            <?php if (count($cn_list) > 0): ?>
            <div class="wrap-landing-info-container">
              <div style="display:flex;flex-wrap:wrap;align-items:stretch;gap:32px">
                <div style="flex:1 1 280px;min-height:280px;background:url('/img/3.jpg') center/cover no-repeat;border-radius:10px"></div>
                <div class="wrap-landing-info" style="text-align:left;flex:1.4 1 340px;padding:6px 0;">
                    <h3 style="margin:0 0 4px">Communities near you</h3>
                    <p style="font-size:14px;color:#6b6055;margin:0 0 14px"><?php echo e($cn_region); ?>, <?php echo e($cn_country); ?> &mdash; compiled with AI, verify before visiting &middot; <a href="/find-a-community" style="text-decoration:underline">see all &amp; filter by type &rarr;</a></p>
                    <ul style="list-style:none;padding:0;margin:0 0 10px">
                        <?php foreach ($cn_list as $c): ?>
                        <li style="text-align:left;padding:7px 0;border-bottom:1px solid #e8e2d8">
                            <b><?php if ($c['website'] !== ''): ?><a href="<?php echo e($c['website']); ?>" rel="nofollow noopener" target="_blank"><?php echo e($c['name']); ?></a><?php else: echo e($c['name']); endif; ?></b>
                            <span style="color:#7c7468;font-size:13px"> &mdash; <?php echo e($TYPES[$c['type']] ?? $c['type']); ?><?php if ($c['locality'] !== ''): ?>, <?php echo e($c['locality']); endif; ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <!-- ===== Latest listings (8) ===== -->
            <div class="cn-home-section">
                <h2><i class="fa fa-home"></i> Latest listings</h2>
                <?php if ($latestListings): ?>
                    <div class="cn-grid">
                        <?php foreach ($latestListings as $l) echo listing_card($l); ?>
                    </div>
                <?php else: ?>
                    <p class="cn-empty">No listings yet — be the first!</p>
                <?php endif; ?>
                <p class="cn-toolbar">
                    <a class="button green" href="/create"><i class="fa fa-plus"></i> Create a listing</a>
                    <a class="button" href="/listings">See all <?php echo $listingCount ?: ''; ?> listings</a>
                </p>
            </div>

            <!-- ===== Newest people (8) ===== -->
            <div class="cn-home-section">
                <h2><i class="fa fa-user"></i> New people</h2>
                <?php if ($newestUsers): ?>
                    <div class="cn-grid cn-grid-users">
                        <?php foreach ($newestUsers as $u) echo user_card($u); ?>
                    </div>
                <?php else: ?>
                    <p class="cn-empty">No members yet — <a href="/signup">sign up</a> to be the first.</p>
                <?php endif; ?>
                <p class="cn-toolbar"><a class="button" href="/users">See all people</a></p>
            </div>

            <!-- ===== Community wall (comments) ===== -->
            <div class="cn-home-section" id="wall">
                <h2><i class="fa fa-comment"></i> Community wall</h2>
                <?php
                try {
                    echo comments_section('site', 0, $me, '/#wall');
                } catch (Throwable $e) {
                    echo '<p class="cn-empty">Comments will appear here once the site database is set up.</p>';
                }
                ?>
            </div>

            <!-- ===== Groups ===== -->
            <div class="cn-home-section">
                <h2><i class="fa fa-users"></i> Groups</h2>
                <p>Create or join a group to plan a street, a building, or a whole community together.</p>
                <?php if ($latestGroups): ?>
                    <div class="cn-grid cn-grid-users">
                        <?php foreach ($latestGroups as $g): ?>
                            <div class="cn-card cn-group-card">
                                <h4><a href="/group?id=<?php echo (int)$g['id']; ?>"><?php echo e($g['name']); ?></a></h4>
                                <?php if ($g['location']): ?>
                                    <p class="cn-loc"><i class="fa fa-map-marker"></i> <?php echo e($g['location']); ?></p>
                                <?php endif; ?>
                                <p class="cn-muted"><?php echo (int)$g['member_count']; ?> member<?php echo $g['member_count'] == 1 ? '' : 's'; ?>
                                    · by <?php echo e($g['owner_name']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <p class="cn-toolbar">
                    <a class="button green" href="/groups"><i class="fa fa-plus"></i> Create or join a group</a>
                </p>
            </div>

                                    
                    





 <div class="wrap-landing-info-container explore-promo">

                    <div class="wrap-landing-info"  style="text-align:left;">

                         


</div>
</div>



<!-- =========================================================================
     BELONGING — "WHY THIS MATTERS" EVIDENCE SECTION
     Paste everything between the <section> tags into the <body> of your home page.
     Self-contained (brings its own fonts + styles).
     Link assumes the new pages live in a folder called "belonging" — change
     "belonging/" in the button below if you put them somewhere else.
     ========================================================================= -->

<section class="ev-section">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');
    .ev-section{
      box-sizing:border-box;max-width:1000px;margin:40px auto;padding:50px 7%;
      background:#24201c;border-radius:14px;color:#f4ece0;
      font-family:'Google Sans','Outfit',Arial,sans-serif;line-height:1.6;position:relative;overflow:hidden;
      box-shadow:0 18px 50px rgba(36,32,28,.25);
    }
    .ev-section *{box-sizing:border-box}
    .ev-section::after{content:"";position:absolute;right:-60px;bottom:-90px;width:300px;height:300px;border-radius:50%;
      background:radial-gradient(circle,rgba(179,82,56,.45),transparent 70%);pointer-events:none}
    .ev-section .ev-eyebrow{
      font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;text-transform:uppercase;letter-spacing:.26em;
      font-size:12px;color:#c08a3e;margin:0 0 18px;display:flex;align-items:center;gap:12px;position:relative;z-index:1;
    }
    .ev-section .ev-eyebrow::before{content:"";width:30px;height:2px;background:#c08a3e}
    .ev-section h2{
      font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;font-size:clamp(27px,4.6vw,42px);
      line-height:1.06;letter-spacing:-.02em;margin:0 0 16px;color:#f4ece0;max-width:22ch;position:relative;z-index:1;
    }
    .ev-section h2 em{font-style:italic;color:#e3a564}
    .ev-section .ev-intro{font-size:18px;color:#e4d8c6;margin:0 0 30px;max-width:60ch;position:relative;z-index:1}
    .ev-section .ev-stats{
      display:grid;grid-template-columns:1fr;gap:1px;background:rgba(244,236,224,.18);
      border:1px solid rgba(244,236,224,.18);border-radius:10px;overflow:hidden;margin:0 0 22px;position:relative;z-index:1;
    }
    @media(min-width:560px){.ev-section .ev-stats{grid-template-columns:repeat(3,1fr)}}
    .ev-section .ev-stat{background:#2a2521;padding:24px 22px}
    .ev-section .ev-stat .f{font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;font-size:40px;line-height:1;color:#e3a564;letter-spacing:-.02em}
    .ev-section .ev-stat .l{font-size:15px;line-height:1.4;color:#cdbfa9;margin-top:10px}
    .ev-section .ev-harvard{font-size:17.5px;color:#e4d8c6;max-width:62ch;margin:0 0 10px;position:relative;z-index:1}
    .ev-section .ev-harvard strong{color:#f4ece0;font-weight:500}
    .ev-section .ev-src{font-family:'Google Sans','Outfit',Arial,sans-serif;font-style:italic;font-size:13.5px;color:#9a8f7e;max-width:62ch;margin:0 0 28px;position:relative;z-index:1}
    .ev-section a.ev-btn{
      font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;font-size:15px;color:#24201c;background:#e3a564;
      border-radius:30px;padding:14px 26px;text-decoration:none;transition:background .2s;display:inline-block;position:relative;z-index:1;
    }
    .ev-section a.ev-btn:hover{background:#f4ece0}
    .ev-section a.ev-btn2{
      font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;font-size:15px;color:#f4ece0;
      border:1px solid rgba(244,236,224,.4);border-radius:30px;padding:13px 26px;text-decoration:none;
      transition:.2s;display:inline-block;position:relative;z-index:1;margin-left:10px;
    }
    .ev-section a.ev-btn2:hover{background:#b35238;border-color:#b35238}
    .ev-section .ev-body{background:#2a2521;border:1px solid rgba(244,236,224,.14);border-left:3px solid #5e6b50;
      border-radius:0 10px 10px 0;padding:26px 28px;margin:0 0 24px;position:relative;z-index:1}
    .ev-section .ev-body h3{font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;font-size:20px;margin:0 0 4px;color:#f4ece0}
    .ev-section .ev-body .sub{font-style:italic;font-size:14.5px;color:#9a8f7e;margin:0 0 16px}
    .ev-section .ev-body ul{list-style:none;margin:0;padding:0}
    .ev-section .ev-body li{font-size:15.5px;line-height:1.55;color:#cdbfa9;padding-left:20px;position:relative;margin-bottom:12px}
    .ev-section .ev-body li:last-child{margin-bottom:0}
    .ev-section .ev-body li::before{content:"—";position:absolute;left:0;color:#5e6b50}
    .ev-section .ev-body li strong{color:#f4ece0;font-weight:600}
    .ev-section .ev-body a{color:#e3a564;text-decoration:none;border-bottom:1px solid rgba(227,165,100,.35)}
    .ev-section .ev-body a:hover{border-color:#e3a564}
  </style>

  <p class="ev-eyebrow">Why this matters</p>
  <h2>Belonging isn&rsquo;t soft. <em>It&rsquo;s measurable.</em></h2>
  <p class="ev-intro">The feeling that the people around you affect your health and how long you live turns out to be one of the most robust findings in modern science. The connection a community provides is, quite literally, preventive health.</p>

  <div class="ev-stats">
    <div class="ev-stat"><div class="f">+26%</div><div class="l">higher risk of early death linked to loneliness</div></div>
    <div class="ev-stat"><div class="f">+29%</div><div class="l">higher risk linked to social isolation</div></div>
    <div class="ev-stat"><div class="f">+32%</div><div class="l">higher risk linked to living alone</div></div>
  </div>

  <p class="ev-harvard">The Harvard Study of Adult Development &mdash; running over 85 years &mdash; found that <strong>the quality of our relationships, more than money, fame, or even cholesterol, is the clearest predictor of long-term health and happiness.</strong> The US Surgeon General has placed the toll of weak social connection on a par with smoking up to 15 cigarettes a day.</p>

  <div class="ev-body">
    <h3>The clenched state vs. the relaxed state &mdash; is it real?</h3>
    <p class="sub">Yes. The nervous system has two settings, and who&rsquo;s nearby helps decide which one you live in. This is measured physiology, not metaphor.</p>
    <ul>
      <li><strong>The clench is vascular.</strong> Threat constricts peripheral blood vessels and tenses muscle; safety does the opposite. Biofeedback clinics use hand temperature as their relaxation gauge precisely because calm means blood flowing back out to the skin.</li>
      <li><strong>Isolation is a chronic clench.</strong> Lonely people carry <a href="https://www.sciencedaily.com/releases/2006/03/060328081644.htm">higher vascular resistance and blood pressure even at rest</a> &mdash; up to ~30 points &mdash; <a href="https://pubmed.ncbi.nlm.nih.gov/28903579/">not only under stress</a>. The body without trusted people nearby idles in low-grade vigilance.</li>
      <li><strong>Trusted people turn the threat system down.</strong> <a href="https://www.frontiersin.org/journals/psychology/articles/10.3389/fpsyg.2020.00378/full">Social Baseline Theory</a>: the brain budgets effort assuming trusted others are close &mdash; in the hand-holding experiments, threat activity dropped with a partner&rsquo;s hand. Alone is not neutral; alone is the expensive setting.</li>
      <li><strong>Even imagined closeness registers.</strong> <a href="https://pubmed.ncbi.nlm.nih.gov/20967200/">A photo of a loved one measurably reduces pain</a>, and <a href="https://www.frontiersin.org/journals/human-neuroscience/articles/10.3389/fnhum.2013.00386/full">attachment figures act as safety signals</a> &mdash; controlled against mere distraction.</li>
      <li><strong>Change the street, change the body.</strong> In the <a href="https://opportunityinsights.org/paper/newmto/">Moving to Opportunity</a> randomized trial, families who moved to better neighbourhoods showed less depression and anxiety, half the diabetes rate, and 40% less extreme obesity.</li>
    </ul>
  </div>

  <p class="ev-src">Figures from Holt-Lunstad et al. meta-analyses (3.4M+ people); Harvard Study of Adult Development; US Surgeon General&rsquo;s Advisory (2023); Cacioppo et al.; Coan et al.; Eisenberger et al.; Chetty, Hendren &amp; Katz. Some researchers debate the exact size of the cigarette comparison, while agreeing the underlying risk is real and rising.</p>

  <a class="ev-btn" href="belonging/why-it-works-on-people.html">See the evidence &rarr;</a>
  <a class="ev-btn2" href="/start-a-community">Do something about it &rarr;</a>
</section>

<!-- ================= END EVIDENCE SECTION ================= -->

<br>



































<!-- =========================================================================
     THE NEW MAIN PITCH — paste into index.php directly under the film iframe.
     Self-contained (brings its own fonts + styles). ~3 minute read.
     The donate buttons link to /support until a payment link exists —
     see the README for swapping in PayPal/Ko-fi later.
     ========================================================================= -->
<section class="pitch">
  <style>
    .pitch{
      box-sizing:border-box;max-width:1000px;margin:40px auto;padding:48px 7%;
      background:#f4ece0;border:1px solid #d9cdb9;border-radius:14px;
      font-family:Georgia,"Times New Roman",serif;color:#24201c;line-height:1.6;
      box-shadow:0 18px 50px rgba(36,32,28,.12);
    }
    .pitch *{box-sizing:border-box}
    .pitch .p-eyebrow{
      font-family:"Palatino Linotype",Palatino,"Book Antiqua",Georgia,serif;font-weight:600;text-transform:uppercase;
      letter-spacing:.26em;font-size:12px;color:#b35238;margin:0 0 18px;
      display:flex;align-items:center;gap:12px;
    }
    .pitch .p-eyebrow::before{content:"";width:30px;height:2px;background:#b35238}
    .pitch h2{
      font-family:"Palatino Linotype",Palatino,"Book Antiqua",Georgia,serif;font-weight:600;font-size:clamp(28px,5vw,46px);
      line-height:1.04;letter-spacing:-.02em;margin:0 0 16px;color:#24201c;
    }
    .pitch h2 em{font-style:italic;color:#8f3f2b}
    .pitch h3{
      font-family:"Palatino Linotype",Palatino,"Book Antiqua",Georgia,serif;font-weight:600;font-size:20px;margin:34px 0 12px;color:#24201c;
    }
    .pitch p{font-size:17.5px;color:#4a443d;margin:0 0 16px;max-width:62ch}
    .pitch p strong{color:#24201c}
    .pitch .p-cards{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin:18px 0 6px}
    @media(max-width:700px){.pitch .p-cards{grid-template-columns:1fr}}
    .pitch .p-card{background:#fff9ef;border:1px solid #d9cdb9;border-radius:10px;padding:16px 18px}
    .pitch .p-card b{font-family:"Palatino Linotype",Palatino,"Book Antiqua",Georgia,serif;font-weight:600;font-size:15.5px;display:block;margin-bottom:5px;color:#24201c}
    .pitch .p-card p{font-size:15px;margin:0;color:#4a443d}
    .pitch .p-chips{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0 6px}
    .pitch .p-chips span{
      font-family:"Palatino Linotype",Palatino,"Book Antiqua",Georgia,serif;font-size:13px;color:#4a443d;background:#efe5d6;
      border:1px solid #d9cdb9;border-radius:30px;padding:6px 13px;
    }
    .pitch .p-buttons{display:flex;flex-wrap:wrap;gap:11px;align-items:center;margin:20px 0 4px}
    .pitch a.p-primary{
      font-family:"Palatino Linotype",Palatino,"Book Antiqua",Georgia,serif;font-weight:600;font-size:15px;color:#fff;
      background:#b35238;border-radius:30px;padding:13px 24px;text-decoration:none;display:inline-block;
    }
    .pitch a.p-primary:hover{background:#8f3f2b}
    .pitch a.p-secondary{
      font-family:"Palatino Linotype",Palatino,"Book Antiqua",Georgia,serif;font-weight:600;font-size:15px;color:#8f3f2b;
      border:1px solid #d9cdb9;border-radius:30px;padding:13px 22px;text-decoration:none;display:inline-block;
    }
    .pitch a.p-secondary:hover{border-color:#b35238;color:#b35238}
    .pitch .p-links{font-family:"Palatino Linotype",Palatino,"Book Antiqua",Georgia,serif;font-size:15px;line-height:2.1;margin:8px 0 0}
    .pitch .p-links a{color:#1f5b6a;text-decoration:none;border-bottom:1px solid #d9cdb9;margin-right:18px;white-space:nowrap}
    .pitch .p-links a:hover{color:#2c7a8c;border-color:#2c7a8c}
    .pitch .p-note{
      font-style:italic;font-size:16px;color:#6d6455;border-top:1px solid #d9cdb9;
      padding-top:16px;margin:28px 0 0;max-width:64ch;
    }
  </style>

  <p class="p-eyebrow">The pitch</p>

  <p>I've spent years in casual work, rooms I didn't choose, suburbs where seeing a friend takes a booking two weeks out. That isolation isn't a personal failing — it's how our places are built. So for thirty years I've been designing the fix, and I'm putting my own savings into it:</p>
  <p><strong>A place where you choose your neighbours, own your home at cost instead of renting, and always have something to do</strong> — with an AI helper that runs the boring parts and never rules. People choose their neighbours. That's all. Everything else is supplementary.</p>

  <h3>Why this can work — and why invest</h3>
  <div class="p-cards">
    <div class="p-card"><b>The demand is enormous.</b><p>Housing unaffordability and loneliness are the two fastest-growing problems in the developed world. A place that fixes both doesn't need to advertise — it needs to exist.</p></div>
    <div class="p-card"><b>The model protects the money.</b><p>Land and buildings hold value. Market-rate homes on one side fund the affordable core on the other; buyers repay at ~30% of income; nothing depends on speculation.</p></div>
    <div class="p-card"><b>The work is already done.</b><p>Full designs, business plans, and a working matching app — built over decades, free to use. I'm committing $50,000+ of my own savings and future income, and my labour.</p></div>
  </div>

  <h3>Ways it can start — pick a scale</h3>
  <div class="p-chips">
    <span>The app first — groups form online, then move near each other</span>
    <span>One building — a hostel, hotel or monastery converted to owned rooms</span>
    <span>A caravan park — the cheapest owned homes there are</span>
    <span>A cheap or half-empty town — renovated at our own pace</span>
    <span>One street — homes bought as they come up, held for the group</span>
    <span>New blocks — common utilities, owners finish their own homes</span>
  </div>

  <h3>What you get</h3>
  <div class="p-cards">
    <div class="p-card"><b>As a resident</b><p>A home you own at ~30% of income, neighbours you chose, free homes held for your friends to join, and always something on.</p></div>
    <div class="p-card"><b>As an investor</b><p>Property-backed returns from the market-rate side and construction margin — and first place in line as the model proves and repeats.</p></div>
    <div class="p-card"><b>As a supporter</b><p>Every plan and line of code published open — your name on the piece you funded, and a working example other towns can copy.</p></div>
  </div>

  <h3>Fund a piece of it</h3>
  <p>Donations go to a named piece, not a pot — the first starter room, the community hall kit, the app's next feature — and every dollar is publicly accounted for. Tell us which piece, or ask what's next on the list:</p>
  <div class="p-buttons">
    <a class="p-primary" href="/support">Invest or partner &rarr;</a>
    <a class="p-secondary" href="/support">Donate to a piece</a>
    <a class="p-secondary" href="/signup">Join as a future resident</a>
  </div>


  <p class="p-note">If any of it speaks to you — as a resident, builder, critic, or investor — <a href="/support">say hello</a>.</p>
</section>
<!-- ===================== END NEW MAIN PITCH ===================== -->










<!-- =========================================================================
     AI EXPERIMENTS SECTION (compact)
     Paste everything between the <section> tags into the <body> of your
     chooseneighbors.com home page. Self-contained (brings its own fonts/styles).
     ========================================================================= -->

<section class="aix">
  <style>
    .aix{
      box-sizing:border-box;max-width:1000px;margin:30px auto;padding:38px 7% 34px;
      background:#f3efe6;border:1px solid #d4ccbd;border-radius:12px;
      font-family:'Google Sans','Outfit',Arial,sans-serif;color:#1e2428;line-height:1.5;
    }
    .aix *{box-sizing:border-box}
    .aix .aix-eyebrow{
      font-weight:600;text-transform:uppercase;letter-spacing:.24em;
      font-size:11.5px;color:#2c7a8c;margin:0 0 10px;display:flex;align-items:center;gap:12px;
    }
    .aix .aix-eyebrow::before{content:"";width:28px;height:2px;background:#2c7a8c}
    .aix h2{font-weight:600;font-size:clamp(22px,3.4vw,30px);line-height:1.05;letter-spacing:-.02em;margin:0 0 18px;color:#1e2428}
    .aix h2 em{font-style:italic;color:#1f5b6a}
    .aix .aix-cards{display:grid;grid-template-columns:1fr;gap:14px;margin:0 0 30px}
    @media(min-width:640px){.aix .aix-cards{grid-template-columns:repeat(3,1fr)}}
    .aix .aix-card{display:block;background:#fff;border:1px solid #d4ccbd;border-radius:10px;padding:18px 20px;text-decoration:none;color:#1e2428;transition:border-color .2s,transform .2s}
    .aix .aix-card:hover{border-color:#2c7a8c;transform:translateY(-3px)}
    .aix .aix-card b{display:block;font-weight:600;font-size:16.5px;line-height:1.25;margin-bottom:6px;color:#1f5b6a}
    .aix .aix-card span{font-size:13.5px;color:#5d665f;line-height:1.45}
    .aix .aix-subhead{font-weight:600;text-transform:uppercase;letter-spacing:.18em;font-size:11.5px;color:#8a8375;margin:0 0 12px}
    .aix ul.aix-list{list-style:none;margin:0 0 20px;padding:0}
    .aix ul.aix-list li{padding:9px 0;border-bottom:1px solid #e2dccd}
    .aix ul.aix-list li:last-child{border-bottom:0}
    .aix ul.aix-list a{font-weight:600;font-size:15.5px;color:#1f5b6a;text-decoration:none}
    .aix ul.aix-list a:hover{color:#2c7a8c}
    .aix ul.aix-list .tag{font-size:12px;color:#8a8375;font-weight:400;margin-left:8px}
    .aix .big{font-weight:700;font-size:clamp(24px,4vw,38px);line-height:1.0;letter-spacing:-.02em;color:#1e2428;margin:14px 0 0}
    .aix .big em{font-style:italic;color:#2c7a8c}
  </style>

  <p class="aix-eyebrow">Guides &amp; experiments</p>
  <h2>Start with the guides. <em>Stay for the experiments.</em></h2>

  <div class="aix-cards">
    <a class="aix-card" href="/find-a-community">
      <b>Find a community near you &rarr;</b>
      <span>Monasteries, cohousing, co-ops, ecovillages &mdash; real places in your state, filterable by type.</span>
    </a>
    <a class="aix-card" href="/start-a-community">
      <b>Start a community with your friends &rarr;</b>
      <span>The complete guide: gather your people, pick a scale, sort homes and money, make it last.</span>
    </a>
    <a class="aix-card" href="/cohousing-vs-coliving.html">
      <b>Cohousing vs coliving &rarr;</b>
      <span>The honest map of the ways to live near people you choose &mdash; privacy, cost, ownership, time.</span>
    </a>
  </div>

  <p class="aix-subhead">The plans</p>
  <ul class="aix-list" style="margin-bottom:28px">
    <li><a href="/business-plan-2026.html">The business plan &rarr;</a></li>
    <li><a href="/belonging/index.html">Belonging &mdash; the community design &rarr;</a></li>
    <li><a href="/the-journal.html">The original pitch &amp; the journal &rarr;</a></li>
    <li><a href="/pitch-letter-2023.html">Pitch letter 2023 &rarr;</a></li>
    <li><a href="/pitch-note-2026.html">Pitch note 2026 &mdash; regular social connection, mutual respect &rarr;</a></li>
    <li><a href="/design-services">Design services &mdash; I'll help you design community apps, places &amp; programs &rarr;</a></li>
  </ul>

  <p class="aix-subhead">Experiments &amp; working notes</p>
  <ul class="aix-list">
    <li><a href="/the-fragments.html">The Fragments &mdash; evidence that every piece of the living map already exists &rarr;</a></li>
    <li><a href="/emergence-world.html">AI Test Engines &mdash; how models behave over time &rarr;</a></li>
    <li><a href="/organised-areas.html">When people organised area-based communities around an idea &rarr;</a></li>
    <li><a href="/ai-community-plan/">AI Community &mdash; Test City, the updated plan &rarr;</a></li>
    <li><a href="/aibasedcommunity/index.html">AI Experiment 1 &mdash; analyse a community &rarr;</a></li>
    <li><a href="/aibasedcommunity/personal-agent.html">AI Experiment 2 &mdash; a personal AI agent &rarr;</a></li>
    <li><a href="/ai-fringe-community/index.html">AI Experiment 3 &mdash; fringe possibilities &rarr;</a></li>
    <li><a href="/cheapest-room-insa/index.html">Cheapest room in SA &rarr;</a><span class="tag">early draft</span></li>
    <li><a href="https://www.youtube.com/watch?v=s_LpgQ6sGrI">Time tree &rarr;</a><span class="tag">sketch, unfinished</span></li>
    <li><a href="/organiser-counselling-app.html">Simple Example of an Organizer / Counselling App &rarr;</a><span class="tag">try it live</span></li>
  </ul>

  <p class="big">We are not <em>finished yet.</em></p>
</section>

<!-- ===================== END GUIDES & EXPERIMENTS SECTION ===================== -->

<br>

        </div>

    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
