<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Design Services — help designing community apps, places & programs</title>
<meta name="description" content="Help designing things that grow community: a nursing home connection app, community and activity apps, neighbourhood programs, matching systems. Thirty years of thinking about belonging, applied to your project.">
<link rel="canonical" href="https://chooseneighbors.com/design-services">
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
  .brand{font-weight:600;font-size:21px;letter-spacing:.2px;color:var(--ink);text-decoration:none;display:flex;align-items:baseline;gap:11px;flex-shrink:0}
  .brand b{font-weight:900}
  .brand span{font-style:italic;font-weight:400;font-size:14px;color:var(--ink-faint)}
  .topnav{display:flex;gap:22px;font-size:14.5px}
  .topnav a{color:var(--ink-soft);text-decoration:none}
  .topnav a:hover{color:var(--terra)}
  @media(max-width:860px){.topnav{display:none}}

  .hero{padding:60px 0 26px}
  .eyebrow{font-weight:600;text-transform:uppercase;letter-spacing:.28em;font-size:12.5px;color:var(--terra);margin-bottom:26px;display:flex;align-items:center;gap:14px}
  .eyebrow::before{content:"";width:34px;height:2px;background:var(--terra);display:inline-block}
  h1{font-weight:600;font-size:clamp(38px,7vw,84px);line-height:.95;letter-spacing:-.03em;max-width:15ch}
  h1 em{font-style:italic;font-weight:500;color:var(--terra-deep)}
  .dek{font-size:clamp(19px,2.4vw,24px);line-height:1.5;color:var(--ink-soft);font-weight:300;max-width:56ch;margin-top:26px}
  .dek b{font-weight:500;color:var(--ink)}

  .section-label{font-weight:600;text-transform:uppercase;letter-spacing:.18em;font-size:12.5px;color:var(--terra);margin:56px 0 20px;display:flex;align-items:center;gap:14px}
  .section-label::before{content:"";width:34px;height:2px;background:var(--terra)}

  .svc-grid{display:grid;grid-template-columns:1fr;gap:16px}
  @media(min-width:720px){.svc-grid{grid-template-columns:1fr 1fr}}
  .svc{background:var(--paper-2);border:1px solid var(--line);border-radius:10px;padding:24px 26px}
  .svc h3{font-weight:600;font-size:20px;line-height:1.15;margin-bottom:8px;color:var(--ink)}
  .svc p{font-size:15.5px;color:var(--ink-soft);line-height:1.55;margin:0}

  .how{max-width:var(--measure)}
  .how p{color:var(--ink-soft);font-size:18px;margin-bottom:14px}
  .how p strong{color:var(--ink);font-weight:600}
  .how a{color:var(--terra-deep);text-decoration:none;border-bottom:1px solid var(--line)}
  .how a:hover{color:var(--terra);border-color:var(--terra)}

  .honesty{max-width:var(--measure);background:var(--paper-2);border-left:3px solid var(--gold);padding:20px 26px;border-radius:0 6px 6px 0;margin:26px 0 6px}
  .honesty h4{font-weight:600;font-size:12.5px;text-transform:uppercase;letter-spacing:.14em;color:var(--gold);margin-bottom:8px}
  .honesty p{font-size:15.5px;color:var(--ink-soft);line-height:1.55;margin:0}

  .lasting{margin:56px 0 6px;background:var(--ink);color:var(--paper);border-radius:12px;padding:42px 40px;position:relative;overflow:hidden}
  .lasting::after{content:"";position:absolute;right:-40px;bottom:-60px;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(179,82,56,.5),transparent 70%)}
  .lasting .kick{font-weight:600;text-transform:uppercase;letter-spacing:.2em;font-size:12px;color:var(--gold);margin-bottom:14px}
  .lasting h2{font-weight:600;font-size:clamp(26px,3.6vw,36px);line-height:1.06;letter-spacing:-.015em;max-width:24ch;margin-bottom:14px}
  .lasting p{color:#e4d8c6;max-width:58ch;font-size:17px;line-height:1.55;margin-bottom:22px}
  .lasting a{display:inline-block;font-weight:600;font-size:15px;color:var(--paper);text-decoration:none;border:1px solid rgba(244,236,224,.4);border-radius:30px;padding:11px 22px;transition:.25s;position:relative;z-index:1;margin:0 10px 10px 0}
  .lasting a:hover{background:var(--terra);border-color:var(--terra)}

  footer{border-top:1px solid var(--line);margin-top:54px;padding:36px 0 20px}
  .fmeta{font-style:italic;font-size:13.5px;color:var(--ink-faint);max-width:56ch}
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
      <a href="/support">Contact</a>
    </div>
  </nav>

  <header class="hero">
    <p class="eyebrow">Design services</p>
    <h1>I'll help you design <em>things that grow community.</em></h1>
    <p class="dek">Apps, places, and programs where connection is built in rather than bolted on. I've spent <b>thirty years</b> thinking about how people end up isolated and what actually fixes it — and I'd like to put that to work on <b>your</b> project.</p>
  </header>

  <p class="section-label">Things I can help design</p>
  <div class="svc-grid">
    <div class="svc" style="grid-column:1/-1;border-left:3px solid var(--terra)">
      <h3>The dynamic map model — a living map for your place</h3>
      <p>The model this site is built on: people ask to live (or be) near their people through a matching app, space is deliberately kept open so groups can grow, anyone can move at any time, and a nearest-neighbours system lays the place out by real bonds instead of fixed plots. <a href="/the-fragments.html" style="color:var(--terra-deep)">Every fragment of it is proven</a> — the assembly is the design work, and AI now makes it practical. I'll help you apply it to a village, a building, a campus, or a program.</p>
    </div>
    <div class="svc">
      <h3>Matching &amp; directory systems</h3>
      <p>Systems that connect people by real bonds and real needs — like this site's <a href="/find-a-community" style="color:var(--terra-deep)">AI-populated community directory</a> and the <a href="/belonging/choose-your-neighbours.html" style="color:var(--terra-deep)">living map</a> concept. If your organisation needs people matched to people, places, or services, I've designed and shipped this.</p>
    </div>
    <div class="svc">
      <h3>Community &amp; activity apps</h3>
      <p>"What's happening near me that I can just walk into?" — matching people to activities, groups, and each other without the friction of scheduling everyone in advance. The thinking behind this site's <a href="/belonging/things-to-do.html" style="color:var(--terra-deep)">always-something-to-do</a> design, applied to your place.</p>
    </div>
    <div class="svc">
      <h3>Places &amp; programs</h3>
      <p>Retirement villages, neighbourhood houses, councils, and housing projects that want community to actually happen — not as an activities calendar, but designed into how the place works day to day. Drawing on the <a href="/belonging/index.html" style="color:var(--terra-deep)">Belonging design series</a>.</p>
    </div>
    <div class="svc">
      <h3>A nursing home &amp; aged care app</h3>
      <p>Residents are some of the most isolated people anywhere, and the research is blunt: connection is preventive health. An app that shows who's around, makes visits and activities easy to join without booking, keeps family in the loop, and gives residents real choices about their daily company — designed around the resident, not the roster.</p>
    </div>
  </div>

  <p class="section-label">How it works</p>
  <div class="how">
    <p><strong>Tell me what you're trying to make.</strong> A rough idea is enough — "our residents are lonely and the activities board isn't working" is a perfectly good starting brief. I'll help you turn it into a design: what it does, how people actually use it, what to build first, and what to leave out.</p>
    <p><strong>Cost depends on the project.</strong> If it genuinely grows community — especially for isolated people — I want it to exist, and I'm flexible to the point of free for the right project. Commercial projects, we'll agree something fair.</p>
    <p><strong>What you get:</strong> a worked design you can build from — screens and flows for an app, or the program and layout thinking for a place — plus the reasoning behind every choice, drawing on the <a href="/belonging/why-it-works-on-people.html">evidence about what connection does to people</a>.</p>
  </div>

  <div class="honesty">
    <h4>Honestly, though</h4>
    <p>I'm one person with deep interest and long experience in this one subject — not an agency. If you need a big team and a fixed enterprise contract, I'm the wrong door. If you need someone who has thought about isolation and belonging for three decades to help you design the thing properly before you build it, I'm exactly the right one.</p>
  </div>

  <section class="lasting">
    <p class="kick">Start a conversation</p>
    <h2>Have something in mind — or someone who needs this?</h2>
    <p>Tell me what you're trying to make, in as few or many words as you like. I read everything.</p>
    <a href="/support">Get in touch &rarr;</a>
  </section>

  <footer>
    <p class="fmeta">Design services by the Choose Neighbors project — the same thinking behind this site's living map, community directory, and the Belonging design series.</p>
  </footer>

</div>
</body>
</html>
