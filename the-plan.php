<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>The Plan — people choose their neighbours · Choose Neighbors</title>
<meta name="description" content="The Choose Neighbors plan: people choose their neighbours. Pay off a room or an apartment, whichever fits your budget, and upgrade later. How moving works with a custom AI system, optional add-ons a community can choose, and how it starts.">
<meta name="robots" content="noindex">
<link rel="canonical" href="https://chooseneighbors.com/the-plan">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  :root{--paper:#f4ece0;--paper-2:#efe5d6;--ink:#24201c;--ink-soft:#4a443d;--ink-faint:#7c7468;--terra:#b35238;--terra-deep:#8f3f2b;--sage:#5e6b50;--gold:#c08a3e;--line:#d9cdb9;--measure:64ch}
  *{box-sizing:border-box;margin:0;padding:0}
  html{scroll-behavior:smooth}
  body{background:var(--ink);color:var(--ink);font-family:'Google Sans','Outfit',Arial,sans-serif;font-weight:400;font-size:19px;line-height:1.65;-webkit-font-smoothing:antialiased;position:relative;overflow-x:hidden}
  .wrap{background:#fff;border-radius:14px;width:min(1120px,calc(100% - 28px));margin:22px auto 44px;padding:4px 7vw 46px;box-shadow:0 30px 80px rgba(0,0,0,.35)}


  .hero{padding:30px 0 0}
  .eyebrow{font-weight:600;text-transform:uppercase;letter-spacing:.28em;font-size:12.5px;color:var(--terra);margin-bottom:14px;display:flex;align-items:center;gap:14px}
  .eyebrow::before{content:"";width:34px;height:2px;background:var(--terra);display:inline-block}
  h1{font-weight:600;font-size:min(38px,calc((86vw - 28px) / 15.5));line-height:1.05;letter-spacing:-.02em;white-space:nowrap}
  h1 em{font-style:italic;font-weight:500;color:var(--terra-deep)}
  .dek{font-size:clamp(16px,1.8vw,18px);line-height:1.45;color:var(--ink-soft);font-weight:300;max-width:72ch;margin-top:12px}
  .dek b{font-weight:500;color:var(--ink)}

  .section-label{font-weight:600;text-transform:uppercase;letter-spacing:.18em;font-size:12.5px;color:var(--terra);margin:30px 0 12px;display:flex;align-items:center;gap:14px}
  .section-label::before{content:"";width:34px;height:2px;background:var(--terra)}
  details.fold summary{cursor:pointer;list-style:none;user-select:none}
  details.fold summary::-webkit-details-marker{display:none}
  details.fold summary::after{content:"";width:9px;height:9px;border-right:2px solid var(--terra);border-bottom:2px solid var(--terra);transform:rotate(-45deg) translateY(2px);transition:transform .2s;margin-left:4px}
  details.fold[open] summary::after{transform:rotate(45deg) translateY(-2px)}
  details.fold summary:hover{color:var(--terra-deep)}
  details.fold summary.section-label{margin-bottom:6px}
  details.fold .body{margin-top:0}
  details.fold + .section-label{margin-top:26px}
  details.item{margin:0 0 8px;max-width:var(--measure)}
  details.item summary{cursor:pointer;list-style:none;position:relative;padding-left:26px;font-size:17px;font-weight:600;color:var(--ink);user-select:none;display:block}
  details.item summary::-webkit-details-marker{display:none}
  details.item summary::before{content:"";position:absolute;left:0;top:.72em;width:12px;height:2px;background:var(--terra)}
  details.item summary::after{content:"";display:inline-block;width:8px;height:8px;border-right:2px solid var(--terra);border-bottom:2px solid var(--terra);transform:rotate(-45deg) translateY(1px);transition:transform .2s;margin-left:10px;vertical-align:middle}
  details.item[open] summary::after{transform:rotate(45deg) translateY(-3px)}
  details.item summary:hover{color:var(--terra-deep)}
  details.item .text{padding:6px 0 0 26px}
  details.item .text p{font-size:16px;margin-bottom:6px}
  .section-label i{font-style:italic;font-weight:400;text-transform:none;letter-spacing:0;color:var(--ink-faint)}

  .body{max-width:var(--measure)}
  .body p{color:var(--ink-soft);font-size:17px;margin-bottom:10px}
  .body p strong,.body li strong{color:var(--ink);font-weight:600}
  .body ul{list-style:none;padding:0;margin:0 0 10px}
  .body ul li{position:relative;padding-left:26px;margin:0 0 8px;color:var(--ink-soft);font-size:17px}
  .body ul li::before{content:"";position:absolute;left:0;top:.72em;width:12px;height:2px;background:var(--terra)}
  .body h3{font-weight:600;font-size:17px;color:var(--ink);margin:12px 0 4px}
  .body a{color:var(--terra-deep);text-decoration:none;border-bottom:1px solid var(--line)}
  .body a:hover{color:var(--terra);border-color:var(--terra)}

  .rule{max-width:var(--measure);background:var(--paper-2);border-left:3px solid var(--gold);padding:12px 22px;border-radius:0 6px 6px 0;margin:10px 0 0;font-size:17px;color:var(--ink)}

  .steps{list-style:none;padding:0;margin:0}
  .steps li{display:grid;grid-template-columns:40px 1fr;gap:14px;align-items:start;background:var(--paper-2);border:1px solid var(--line);border-radius:10px;padding:16px 20px;margin:0 0 10px}
  .steps .n{font-weight:700;font-size:26px;line-height:1;color:var(--terra);padding-top:2px}
  .steps h3{font-weight:600;font-size:19px;line-height:1.15;margin-bottom:4px;color:var(--ink)}
  .steps p{font-size:15.5px;color:var(--ink-soft);line-height:1.5;margin:0 0 8px}
  .steps .wip{font-style:italic;font-size:14px;color:var(--ink-faint);margin-left:10px}
  a.btn{display:inline-block;font-weight:600;font-size:15px;color:#fff;background:var(--terra);text-decoration:none;border-radius:30px;padding:10px 20px;transition:.25s}
  a.btn:hover{background:var(--terra-deep)}
  a.btn2{display:inline-block;font-weight:600;font-size:15px;color:var(--terra-deep);text-decoration:none;border:1px solid var(--line);border-radius:30px;padding:10px 18px;transition:.25s}
  a.btn2:hover{border-color:var(--terra);color:var(--terra)}

  footer{border-top:1px solid var(--line);margin-top:54px;padding:36px 0 20px}
  .fmeta{font-style:italic;font-size:13.5px;color:var(--ink-faint);max-width:70ch;line-height:1.7}
  .fmeta a{color:var(--ink-soft);text-decoration:none;border-bottom:1px solid var(--line)}
  .fmeta a:hover{color:var(--terra)}
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
</head>
<body>
<div class="wrap">


  <header class="hero">
    <p class="eyebrow">The plan</p>
    <h1>People choose <em>their neighbours.</em></h1>
    <p class="dek"><b>That's all &mdash; everything else is supplementary.</b></p>
    <p class="dek">You pay off a room or an apartment, whichever your budget allows. Start with a room if that's what you can afford, and upgrade to an apartment later. Payments are a share of what you earn (about 30%), on the home's original value plus inflation &mdash; not a price that keeps climbing.</p>
  </header>

  <details class="fold">
    <summary class="section-label">How moving works</summary>
    <div class="body">
    <p>A custom AI system runs the neighbour map. It does the matching and the paperwork; the people make the choices.</p>
    <ul>
      <li><strong>You tell it who you want to live near</strong> &mdash; people, or a named group-area. It offers you a free room or apartment near them.</li>
      <li><strong>Some homes are always kept free,</strong> so a group of friends can keep growing and anyone can move at any time &mdash; to other people, or to another area.</li>
      <li><strong>A move is a name change on the papers, not a sale.</strong> If the new home is worth more, you pay only the difference.</li>
      <li><strong>Upgrading:</strong> say you want an apartment instead of a room. The system watches for one near your people and offers it to you.</li>
      <li><strong>Block someone</strong> and the system moves you apart. If you share friends, your friends decide.</li>
      <li><strong>Nothing happens without your yes.</strong> Moving is always a choice; anyone can stay where they are.</li>
    </ul>
    </div>
  </details>

  <p class="section-label">Add-ons</p>
  <div class="body">
    <details class="item">
      <summary>&ldquo;Around me&rdquo; work opportunities.</summary>
      <div class="text">
        <p>Local work and casual jobs you can do with your friends, the upkeep of the place, a meeting area, a say in how the area runs &mdash; things to do because the people you chose are around.</p>
        <p><a href="/city-experiments.html#_1bc2mp9dx64w">Read more &rarr;</a></p>
      </div>
    </details>
    <ul>
      <li><a href="/">View more &rarr;</a></li>
    </ul>
  </div>
  <div class="rule">The list is up to the people living there.</div>

  <p class="section-label">How it starts</p>
  <ol class="steps">
    <li><span class="n">1</span><div><h3>People join the list</h3><p>and get notified when something is happening near them.</p><a class="btn" href="/interest">Join the list &rarr;</a></div></li>
    <li><span class="n">2</span><div><h3>Start it yourself</h3><p>Gather your people and begin at the scale you can: a shared house, a street, a building. The guide covers the rest.</p><a class="btn2" href="/start-a-community">Start a community &rarr;</a></div></li>
    <li><span class="n">3</span><div><h3>My plan, with my funding</h3><p>I'm putting in my own savings and looking for a loan or funding. How much I can borrow is still to be worked out. For what the money could buy, see the list of unbelievable apartment prices in Germany.</p><a class="btn2" href="/germany-apartment-prices">Apartment prices in Germany &rarr;</a></div></li>
    <li><span class="n">4</span><div><h3>An Australian government fund plan</h3><p>A version of the plan for government funding, which we will be putting to Australians.</p><a class="btn2" href="/australia-government-fund-plan">The Australian fund plan &rarr;</a><span class="wip">work in progress</span></div></li>
  </ol>

  <details class="fold">
    <summary class="section-label">Why</summary>
    <div class="body">
      <h3>100% home ownership</h3>
      <p>Everyone owns the place they live in, from the first day. A room or an apartment is paid off at what it cost plus inflation, never at a price that keeps climbing, so nobody is left renting forever or waiting on a list. Some homes are always kept free, so there is always somewhere to move to.</p>
      <h3>Isolation</h3>
      <p>More people live alone than ever, and being lonely or isolated harms health on a par with smoking. A 2026 national report found half of Australians are lonely, isolated, or both. The usual answer is a visit, a class or a group you travel to. The default life most people are left with is being alone, without community where they live.</p>
      <h3>Community feeling</h3>
      <p>The feeling people are missing is being part of something with people they like: people who can deal with problems, make things and make things better, right where they live. Living around people you chose, with something to do because they are around, is what gives it. A place to go is a place to live.</p>
      <p><a href="/#why">Read more &rarr;</a></p>
    </div>
  </details>

  <footer>
    <p class="fmeta">Michael van Diermen &middot; <a href="/support">contact via the site</a> &middot; <a href="/license">License &amp; copyright</a></p>
  </footer>

</div>
</body>
</html>
