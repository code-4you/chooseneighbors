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

  .topbar{display:flex;align-items:center;justify-content:space-between;padding:26px 0 22px;border-bottom:1px solid var(--line);gap:20px}
  .brand{font-weight:600;font-size:21px;letter-spacing:.2px;color:var(--ink);text-decoration:none;display:flex;flex-direction:column;align-items:flex-start;gap:1px;line-height:1.25;flex-shrink:0}
  .brand b{font-weight:900}
  .brand span{font-style:italic;font-weight:400;font-size:14px;color:var(--ink-faint)}
  .topnav{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:4px 20px;font-size:14.5px}
  .topnav a{color:var(--ink-soft);text-decoration:none;white-space:nowrap}
  .topnav a:hover{color:var(--terra)}
  @media(max-width:860px){.topnav{display:none}}

  .hero{padding:60px 0 10px}
  .eyebrow{font-weight:600;text-transform:uppercase;letter-spacing:.28em;font-size:12.5px;color:var(--terra);margin-bottom:26px;display:flex;align-items:center;gap:14px}
  .eyebrow::before{content:"";width:34px;height:2px;background:var(--terra);display:inline-block}
  h1{font-weight:600;font-size:clamp(38px,7vw,84px);line-height:.95;letter-spacing:-.03em;max-width:15ch}
  h1 em{font-style:italic;font-weight:500;color:var(--terra-deep)}
  .dek{font-size:clamp(19px,2.4vw,24px);line-height:1.5;color:var(--ink-soft);font-weight:300;max-width:56ch;margin-top:26px}
  .dek b{font-weight:500;color:var(--ink)}

  .section-label{font-weight:600;text-transform:uppercase;letter-spacing:.18em;font-size:12.5px;color:var(--terra);margin:56px 0 20px;display:flex;align-items:center;gap:14px}
  .section-label::before{content:"";width:34px;height:2px;background:var(--terra)}
  .section-label i{font-style:italic;font-weight:400;text-transform:none;letter-spacing:0;color:var(--ink-faint)}

  .body{max-width:var(--measure)}
  .body p{color:var(--ink-soft);font-size:18px;margin-bottom:14px}
  .body p strong,.body li strong{color:var(--ink);font-weight:600}
  .body ul{list-style:none;padding:0;margin:0 0 10px}
  .body ul li{position:relative;padding-left:26px;margin:0 0 12px;color:var(--ink-soft);font-size:18px}
  .body ul li::before{content:"";position:absolute;left:0;top:.72em;width:12px;height:2px;background:var(--terra)}
  .body a{color:var(--terra-deep);text-decoration:none;border-bottom:1px solid var(--line)}
  .body a:hover{color:var(--terra);border-color:var(--terra)}

  .rule{max-width:var(--measure);background:var(--paper-2);border-left:3px solid var(--gold);padding:18px 26px;border-radius:0 6px 6px 0;margin:18px 0 6px;font-size:18px;color:var(--ink)}

  .steps{list-style:none;padding:0;margin:0}
  .steps li{display:grid;grid-template-columns:44px 1fr;gap:16px;align-items:start;background:var(--paper-2);border:1px solid var(--line);border-radius:10px;padding:22px 24px;margin:0 0 14px}
  .steps .n{font-weight:700;font-size:30px;line-height:1;color:var(--terra);padding-top:2px}
  .steps h3{font-weight:600;font-size:20px;line-height:1.15;margin-bottom:6px;color:var(--ink)}
  .steps p{font-size:16px;color:var(--ink-soft);line-height:1.55;margin:0 0 12px}
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

  <nav class="topbar">
    <a class="brand" href="/"><b>Choose Neighbors</b> <span>homes designed around friendship</span></a>
    <div class="topnav">
      <a href="/start-a-community">Start a community</a>
      <a href="/find-a-community">Find a community</a>
      <a href="/interest">Join the list</a>
      <a href="/support">Contact</a>
      <a href="https://buymeacoffee.com/michaelvandiermen" target="_blank" rel="noopener">Buy me a coffee</a>
    </div>
  </nav>

  <header class="hero">
    <p class="eyebrow">The plan</p>
    <h1>People choose <em>their neighbours.</em></h1>
    <p class="dek"><b>That's all &mdash; everything else is supplementary.</b></p>
    <p class="dek">You pay off a room or an apartment, whichever your budget allows. Start with a room if that's what you can afford, and upgrade to an apartment later. Payments are a share of what you earn (about 30%), on the home's original value plus inflation &mdash; not a price that keeps climbing.</p>
  </header>

  <p class="section-label">How moving works</p>
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

  <p class="section-label">Add-ons a community can choose <i>(recommended, optional)</i></p>
  <div class="body">
    <ul>
      <li><strong>&ldquo;Around me&rdquo; work opportunities.</strong> Local work and casual jobs you can do with your friends, the upkeep of the place, a meeting area, a say in how the area runs &mdash; things to do because the people you chose are around.</li>
      <li>More to come.</li>
    </ul>
  </div>
  <div class="rule">The list is up to the people living there.</div>

  <p class="section-label">How it starts</p>
  <ol class="steps">
    <li><span class="n">1</span><div><h3>People join the list</h3><p>and get notified when something is happening near them.</p><a class="btn" href="/interest">Join the list &rarr;</a></div></li>
    <li><span class="n">2</span><div><h3>People do the advertising</h3><p>We make a page they can use.</p><a class="btn2" href="/spread-the-word">Spread the word &rarr;</a></div></li>
    <li><span class="n">3</span><div><h3>My plan, with my funding</h3><p>I'm putting in my own savings and looking for a loan or funding. How much I can borrow is still to be worked out. For what the money could buy, see the list of unbelievable apartment prices in Germany.</p><a class="btn2" href="/germany-apartment-prices">Apartment prices in Germany &rarr;</a></div></li>
    <li><span class="n">4</span><div><h3>An Australian government fund plan</h3><p>A version of the plan for government funding, which we will be putting to Australians.</p><a class="btn2" href="/australia-government-fund-plan">The Australian fund plan &rarr;</a><span class="wip">work in progress</span></div></li>
  </ol>

  <footer>
    <p class="fmeta">Older plans, kept for the record: <a href="/business-plan-2026.html">Business plan 2026</a> &middot; <a href="/belonging/index.html">Belonging &mdash; the community design</a> &middot; <a href="/business-plan.html">The original plan</a> &middot; <a href="/pitch-letter-2023.html">Pitch letter 2023</a> &middot; <a href="/city-experiments.html">The journal</a><br>
    Michael van Diermen &middot; <a href="/support">contact via the site</a> &middot; <a href="/license">License &amp; copyright</a></p>
  </footer>

</div>
</body>
</html>
