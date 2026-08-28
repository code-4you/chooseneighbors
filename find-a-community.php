<?php
/**
 * /find-a-community — a directory of places where community is built into
 * where you live, filterable by type and state/region.
 *
 * First visitor from an unpopulated state triggers a one-time Gemini call
 * (GEMINI_API_KEY in config.php); results are cached in the DB forever after.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/db.php';

require_once __DIR__ . '/inc/community-directory.php';

/* ---------- page logic ---------- */
$entries = [];
$regions = [];
$status  = 'done';
$dbError = false;

try {
    fac_ensure_schema();

    /* region: explicit choice via GET, else IP detection */
    if (isset($_GET['region']) && $_GET['region'] !== '') {
        $country = mb_substr(trim((string)($_GET['country'] ?? 'Australia')), 0, 64);
        $region  = mb_substr(trim((string)$_GET['region']), 0, 96);
    } else {
        list($country, $region) = fac_detect_region();
    }

    /* known regions for the switcher */
    $regions = db()->query("SELECT country, region, status FROM community_directory_regions ORDER BY country, region")->fetchAll();

    if ($region !== '') {
        $st = db()->prepare("SELECT * FROM community_directory WHERE country=? AND region=? ORDER BY type, name LIMIT 500");
        $st->execute([$country, $region]);
        $entries = $st->fetchAll();
        if (count($entries) === 0) {
            $status = fac_populate_region($country, $region, $TYPES);
            if ($status === 'done') {
                $st->execute([$country, $region]);
                $entries = $st->fetchAll();
            }
        }
    }
} catch (Throwable $e) {
    $dbError = true;
    $country = $country ?? 'Australia';
    $region  = $region ?? '';
}

$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

/* ---------- country/state dropdown data ---------- */
$REGION_PRESETS = [
    'Australia' => ['New South Wales','Victoria','Queensland','South Australia','Western Australia','Tasmania','Australian Capital Territory','Northern Territory'],
    'United States' => ['Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut','Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada','New Hampshire','New Jersey','New Mexico','New York','North Carolina','North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia','Wisconsin','Wyoming'],
    'Canada' => ['Alberta','British Columbia','Manitoba','New Brunswick','Newfoundland and Labrador','Nova Scotia','Ontario','Prince Edward Island','Quebec','Saskatchewan','Northwest Territories','Nunavut','Yukon'],
    'United Kingdom' => ['England','Scotland','Wales','Northern Ireland'],
    'New Zealand' => ['Auckland','Bay of Plenty','Canterbury','Hawke\'s Bay','Manawatu-Wanganui','Northland','Otago','Southland','Taranaki','Waikato','Wellington'],
];
/* merge regions already in the DB (any country) */
foreach ($regions as $r) {
    if (!isset($REGION_PRESETS[$r['country']])) $REGION_PRESETS[$r['country']] = [];
    if (!in_array($r['region'], $REGION_PRESETS[$r['country']], true)) $REGION_PRESETS[$r['country']][] = $r['region'];
}
foreach ($REGION_PRESETS as &$list) sort($list);
unset($list);
/* current selection must exist in the map so the dropdowns can show it */
if ($country !== '' && !isset($REGION_PRESETS[$country])) $REGION_PRESETS[$country] = [];
if ($region !== '' && !in_array($region, $REGION_PRESETS[$country] ?? [], true)) $REGION_PRESETS[$country][] = $region;

/* ---------- per-state SEO head values ---------- */
if ($region !== '') {
    $pageTitle = 'Find a Community in ' . $region . ' — monasteries, cohousing, co-ops & more';
    $pageDesc  = 'Real communities in ' . $region . ', ' . $country . ' you can join, visit, or move into — monasteries welcoming guests, cohousing, ecovillages, housing co-ops, seniors villages, and community-strong neighbourhoods.';
    $canonicalUrl = 'https://chooseneighbors.com/find-a-community?' . http_build_query(['country' => $country, 'region' => $region]);
} else {
    $pageTitle = 'Find a Community Near You — places where belonging is built in';
    $pageDesc  = 'A directory of communities you can join, visit, or move into — monasteries, cohousing, ecovillages, co-ops, seniors villages, and community-strong neighbourhoods — filtered by type, for your state.';
    $canonicalUrl = 'https://chooseneighbors.com/find-a-community';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $h($pageTitle); ?></title>
<meta name="description" content="<?php echo $h($pageDesc); ?>">
<link rel="canonical" href="<?php echo $h($canonicalUrl); ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  :root{--paper:#f4ece0;--paper-2:#efe5d6;--ink:#24201c;--ink-soft:#4a443d;--ink-faint:#7c7468;--terra:#b35238;--terra-deep:#8f3f2b;--sage:#5e6b50;--gold:#c08a3e;--line:#d9cdb9;--measure:64ch}
  *{box-sizing:border-box;margin:0;padding:0}
  html{scroll-behavior:smooth}
  body{background:var(--paper);color:var(--ink);font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:400;font-size:19px;line-height:1.65;-webkit-font-smoothing:antialiased;position:relative;overflow-x:hidden}
  body::before{content:"";position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.4'/%3E%3C/svg%3E");opacity:.045;pointer-events:none;z-index:9999;mix-blend-mode:multiply}
  .wrap{max-width:1120px;margin:0 auto;padding:0 7vw}
  .topbar{display:flex;align-items:center;justify-content:space-between;padding:26px 0 22px;border-bottom:1px solid var(--line);font-family:'Google Sans','Outfit',Arial,sans-serif;gap:20px}
  .brand{font-weight:600;font-size:21px;letter-spacing:.2px;color:var(--ink);text-decoration:none;display:flex;align-items:baseline;gap:11px;flex-shrink:0}
  .brand b{font-weight:900}
  .brand span{font-family:'Google Sans','Outfit',Arial,sans-serif;font-style:italic;font-weight:400;font-size:14px;color:var(--ink-faint)}
  .topnav{display:flex;gap:22px;font-size:14.5px}
  .topnav a{color:var(--ink-soft);text-decoration:none;transition:color .2s}
  .topnav a:hover{color:var(--terra)}
  @media(max-width:860px){.topnav{display:none}}

  .hero{padding:64px 0 26px}
  .eyebrow{font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;text-transform:uppercase;letter-spacing:.28em;font-size:12.5px;color:var(--terra);margin-bottom:26px;display:flex;align-items:center;gap:14px}
  .eyebrow::before{content:"";width:34px;height:2px;background:var(--terra);display:inline-block}
  h1{font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;font-size:clamp(40px,7.5vw,88px);line-height:.95;letter-spacing:-.03em;max-width:15ch}
  h1 em{font-style:italic;font-weight:500;color:var(--terra-deep)}
  .dek{font-size:clamp(19px,2.4vw,24px);line-height:1.5;color:var(--ink-soft);font-weight:300;max-width:56ch;margin-top:28px}
  .dek b{font-weight:500;color:var(--ink)}

  .controls{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin:38px 0 8px;padding-top:30px;border-top:1px solid var(--line)}
  .regionpick{font-family:'Google Sans','Outfit',Arial,sans-serif;font-size:15px;display:flex;align-items:center;gap:10px;margin-right:auto}
  .regionpick select{font-family:'Google Sans','Outfit',Arial,sans-serif;font-size:15px;color:var(--ink);background:var(--paper-2);border:1px solid var(--line);border-radius:8px;padding:9px 12px;max-width:280px}
  .chip{font-family:'Google Sans','Outfit',Arial,sans-serif;font-size:14px;color:var(--ink-soft);background:var(--paper-2);border:1px solid var(--line);border-radius:30px;padding:8px 16px;cursor:pointer;transition:.2s;user-select:none}
  .chip:hover{border-color:var(--terra);color:var(--terra-deep)}
  .chip.on{background:var(--terra);border-color:var(--terra);color:var(--paper)}

  .notice{max-width:var(--measure);background:var(--paper-2);border-left:3px solid var(--gold);padding:18px 24px;border-radius:0 6px 6px 0;margin:26px 0 6px;font-size:16px;color:var(--ink-soft)}
  .notice b{color:var(--ink)}

  .grid{display:grid;grid-template-columns:1fr;gap:16px;margin:30px 0 10px}
  @media(min-width:720px){.grid{grid-template-columns:1fr 1fr}}
  .card{background:var(--paper-2);border:1px solid var(--line);border-radius:10px;padding:24px 26px 20px;display:flex;flex-direction:column;gap:8px}
  .card .type{font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;text-transform:uppercase;letter-spacing:.14em;font-size:11px;color:var(--terra-deep)}
  .card h3{font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;font-size:21px;line-height:1.1;color:var(--ink)}
  .card .where{font-style:italic;font-size:14.5px;color:var(--ink-faint)}
  .card p{font-size:15.5px;line-height:1.55;color:var(--ink-soft)}
  .card a{font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;font-size:14px;color:var(--terra-deep);text-decoration:none;border-bottom:1px solid var(--line);width:fit-content;margin-top:auto}
  .card a:hover{color:var(--terra);border-color:var(--terra)}
  .empty{max-width:var(--measure);font-size:18px;color:var(--ink-soft);margin:34px 0;font-style:italic}

  .lasting{margin:60px 0 6px;background:var(--ink);color:var(--paper);border-radius:12px;padding:42px 40px;position:relative;overflow:hidden}
  .lasting::after{content:"";position:absolute;right:-40px;bottom:-60px;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(179,82,56,.5),transparent 70%)}
  .lasting .kick{font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;text-transform:uppercase;letter-spacing:.2em;font-size:12px;color:var(--gold);margin-bottom:14px}
  .lasting h2{font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;font-size:clamp(26px,3.6vw,36px);line-height:1.06;letter-spacing:-.015em;max-width:24ch;margin-bottom:14px}
  .lasting p{color:#e4d8c6;max-width:58ch;font-size:17px;line-height:1.55;margin-bottom:22px}
  .lasting a{display:inline-block;font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:600;font-size:15px;color:var(--paper);text-decoration:none;border:1px solid rgba(244,236,224,.4);border-radius:30px;padding:11px 22px;transition:.25s;position:relative;z-index:1;margin:0 10px 10px 0}
  .lasting a:hover{background:var(--terra);border-color:var(--terra)}

  footer{border-top:1px solid var(--line);margin-top:60px;padding:42px 0 68px;font-family:'Google Sans','Outfit',Arial,sans-serif}
  .fnav{display:grid;grid-template-columns:1fr;gap:10px 40px;margin-bottom:28px}
  @media(min-width:560px){.fnav{grid-template-columns:1fr 1fr}}
  @media(min-width:860px){.fnav{grid-template-columns:repeat(3,1fr)}}
  .fnav a{color:var(--ink-soft);text-decoration:none;font-size:15px;padding:4px 0;width:fit-content}
  .fnav a:hover{color:var(--terra)}
  .fmeta{font-family:'Google Sans','Outfit',Arial,sans-serif;font-style:italic;font-size:13.5px;color:var(--ink-faint);max-width:56ch;border-top:1px solid var(--line);padding-top:22px}
</style>
<!-- Global site tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RGETBRPSNS"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-RGETBRPSNS');
</script>
<!-- End, Global site tag (gtag.js) -->
<style>/* white sheet on brown */
body{background:var(--ink)}
.wrap{background:#fff;border-radius:14px;width:min(1120px,calc(100% - 28px));margin:22px auto 44px;padding-top:4px;padding-bottom:46px;box-shadow:0 30px 80px rgba(0,0,0,.35)}
</style>
</head>
<body>
<div class="wrap">

  <nav class="topbar">
    <a class="brand" href="/"><b>Choose Neighbors</b> <span>homes designed around friendship</span></a>
    <div class="topnav">
      <a href="/start-a-community">Start a community</a>
      <a href="/cohousing-vs-coliving.html">Cohousing vs coliving</a>
      <a href="/groups">Groups</a>
      <a href="/interest">Register interest</a>
    </div>
  </nav>

  <header class="hero">
    <p class="eyebrow">A directory of belonging</p>
    <h1>Find a community <em>near you</em></h1>
    <p class="dek">Places where community is built into where you live — not another club to visit, but somewhere the people around you are part of daily life. <b>Monasteries that welcome guests, cohousing, co-ops, ecovillages, seniors villages, and neighbourhoods that actually function.</b> Start where you are.</p>
  </header>

  <div class="controls">
    <form class="regionpick" method="get" action="/find-a-community" id="regionform">
      <label for="country">Showing</label>
      <select name="country" id="country">
        <?php foreach (array_keys($REGION_PRESETS) as $c): ?>
        <option value="<?php echo $h($c); ?>"<?php echo $c === $country ? ' selected' : ''; ?>><?php echo $h($c); ?></option>
        <?php endforeach; ?>
      </select>
      <select name="region" id="region">
        <?php if ($region === ''): ?><option value="" selected>Choose your state…</option><?php endif; ?>
        <?php foreach (($REGION_PRESETS[$country] ?? []) as $s): ?>
        <option value="<?php echo $h($s); ?>"<?php echo $s === $region ? ' selected' : ''; ?>><?php echo $h($s); ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <span class="chip on" data-type="all">All types</span>
    <?php foreach ($TYPES as $key => $label): ?><span class="chip" data-type="<?php echo $h($key); ?>"><?php echo $h($label); ?></span><?php endforeach; ?>
  </div>

  <?php if ($dbError): ?>
    <p class="notice"><b>The directory is warming up.</b> We couldn't reach the database just now — please try again shortly.</p>
  <?php elseif ($region === ''): ?>
    <p class="empty">Choose your state above and we'll gather what's there.</p>
  <?php elseif ($status === 'failed' && count($entries) === 0): ?>
    <p class="notice"><b>We're still compiling <?php echo $h($region); ?>.</b> The first gathering for a state takes a little while — check back in a few minutes.</p>
  <?php elseif ($status === 'pending' && count($entries) === 0): ?>
    <p class="notice"><b>Someone's visit just started the gathering for <?php echo $h($region); ?>.</b> Refresh in a minute or two.</p>
  <?php else: ?>
    <p class="notice"><b>Compiled with AI, checked by nobody yet.</b> These are starting points, gathered for <?php echo $h($region); ?> — real places as far as we know, but details change. Verify with a phone call before you set your heart or drive across the state.</p>
    <div class="grid" id="grid">
      <?php foreach ($entries as $e): ?>
      <div class="card" data-type="<?php echo $h($e['type']); ?>">
        <span class="type"><?php echo $h($TYPES[$e['type']] ?? $e['type']); ?></span>
        <h3><?php echo $h($e['name']); ?></h3>
        <span class="where"><?php echo $h($e['locality']); ?><?php if ($e['council'] !== ''): ?> · <?php echo $h($e['council']); ?><?php endif; ?></span>
        <p><?php echo $h($e['description']); ?></p>
        <?php if ($e['website'] !== ''): ?><a href="<?php echo $h($e['website']); ?>" rel="nofollow noopener" target="_blank">Visit website &rarr;</a><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <section class="lasting">
    <p class="kick">The other path</p>
    <h2>Nothing near you? Start one — smaller than you think.</h2>
    <p>If your state's list is thin, that's not a dead end; it's the reason this project exists. Three friends on one street is already a community. The guide walks you through the whole path.</p>
    <a href="/start-a-community">Start a community — the guide</a>
    <a href="/groups">Create or join a group</a>
    <a href="/interest">Register your interest</a>
  </section>

  <?php
    $doneRegions = array_values(array_filter($regions, function ($r) { return $r['status'] === 'done'; }));
    if (count($doneRegions) > 0):
  ?>
  <section aria-label="Browse by state" style="margin-top:56px">
    <p class="eyebrow">Browse by state</p>
    <div class="fnav" style="font-family:'Google Sans','Outfit',Arial,sans-serif">
      <?php foreach ($doneRegions as $r):
        $u = '/find-a-community?' . http_build_query(['country' => $r['country'], 'region' => $r['region']]); ?>
      <a href="<?php echo $h($u); ?>"><?php echo $h($r['region']); ?>, <?php echo $h($r['country']); ?></a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <footer>
    <div class="fnav">
      <a href="/">Choose Neighbors home</a>
      <a href="/start-a-community">Start a community</a>
      <a href="/cohousing-vs-coliving.html">Cohousing vs coliving</a>
      <a href="/belonging/index.html">The Belonging essays</a>
      <a href="/belonging/why-it-works-on-people.html">Why community works on people</a>
      <a href="/listings">Listings</a>
    </div>
    <p class="fmeta">Each state's list is gathered once by AI when its first visitor arrives, then kept. Know a community that's missing or wrong? <a href="mailto:contact@chooseneighbors.com" style="color:var(--ink-soft)">Tell us</a> — this directory should end up human-checked, entry by entry.</p>
  </footer>

</div>
<script>
  (function(){
    var REGIONS = <?php echo json_encode($REGION_PRESETS, JSON_UNESCAPED_UNICODE); ?>;
    var countrySel = document.getElementById('country');
    var regionSel  = document.getElementById('region');
    var form       = document.getElementById('regionform');
    if (countrySel && regionSel) {
      countrySel.addEventListener('change', function(){
        var list = REGIONS[countrySel.value] || [];
        regionSel.innerHTML = '';
        var ph = document.createElement('option');
        ph.value = ''; ph.textContent = 'Choose your state…'; ph.selected = true;
        regionSel.appendChild(ph);
        list.forEach(function(s){
          var o = document.createElement('option');
          o.value = s; o.textContent = s;
          regionSel.appendChild(o);
        });
      });
      regionSel.addEventListener('change', function(){
        if (regionSel.value !== '') form.submit();
      });
    }
    var chips = document.querySelectorAll('.chip');
    var cards = document.querySelectorAll('#grid .card');
    chips.forEach(function(ch){
      ch.addEventListener('click', function(){
        chips.forEach(function(c){ c.classList.remove('on'); });
        ch.classList.add('on');
        var t = ch.getAttribute('data-type');
        cards.forEach(function(card){
          card.style.display = (t === 'all' || card.getAttribute('data-type') === t) ? '' : 'none';
        });
      });
    });
  })();
</script>
</body>
</html>
<?php
/* post-render: quietly refresh stale states (>= 4 days old) with new Gemini suggestions */
if (!$dbError && $region !== '' && count($entries) > 0) {
    try { fac_refresh_if_stale($country, $region, $TYPES); } catch (Throwable $e) { /* never surface to visitor */ }
}
?>
