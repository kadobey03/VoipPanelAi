<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/LangHelper.php';
if (isset($_POST['lang']) && in_array($_POST['lang'], ['tr','en','ru'])) {
    LangHelper::setLang($_POST['lang']);
    header('Location: ' . $_SERVER['PHP_SELF']); exit;
}
$currentLang = LangHelper::getCurrentLang();
?><!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PapaM VoIP — Profesyonel Çağrı Merkezi Yönetimi</title>
<meta name="description" content="Gelişmiş VoIP çağrı merkezi yönetim paneli. Gerçek zamanlı izleme, detaylı raporlama, çoklu grup yönetimi.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#030712;--bg2:#0f172a;--card:#0f172a;
  --border:rgba(255,255,255,.07);
  --text:#f1f5f9;--muted:#94a3b8;
  --accent:#7c3aed;--accent2:#6366f1;--cyan:#06b6d4;
  --green:#10b981;
  --grad:linear-gradient(135deg,#7c3aed 0%,#6366f1 50%,#06b6d4 100%);
}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);overflow-x:hidden;line-height:1.6}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--bg)}::-webkit-scrollbar-thumb{background:var(--accent);border-radius:3px}

/* ── NAV */
nav{position:fixed;top:0;left:0;right:0;z-index:100;padding:14px 0;transition:all .3s}
nav.scrolled{background:rgba(3,7,18,.9);backdrop-filter:blur(24px);border-bottom:1px solid var(--border)}
.nav-inner{max-width:1200px;margin:0 auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.logo-icon{width:38px;height:38px;border-radius:11px;background:var(--grad);display:flex;align-items:center;justify-content:center;font-size:16px;color:#fff;box-shadow:0 0 24px rgba(124,58,237,.5);flex-shrink:0}
.logo-text{font-size:18px;font-weight:800;color:#fff;white-space:nowrap}
.logo-text span{background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.nav-links{display:flex;align-items:center;gap:4px}
.nav-link{padding:7px 14px;border-radius:8px;text-decoration:none;color:var(--muted);font-size:13px;font-weight:500;transition:all .2s}
.nav-link:hover{color:#fff;background:rgba(255,255,255,.06)}
.lang-form{display:inline}
.lang-btn{background:rgba(255,255,255,.08);border:1px solid var(--border);color:var(--muted);padding:6px 12px;border-radius:8px;font-size:12px;cursor:pointer;transition:all .2s;font-family:inherit}
.lang-btn:hover{background:rgba(255,255,255,.14);color:#fff}
.lang-btn.active{background:rgba(124,58,237,.3);border-color:rgba(124,58,237,.5);color:#c4b5fd}
.btn-nav-cta{padding:9px 20px;border-radius:10px;text-decoration:none;background:var(--grad);color:#fff;font-size:13px;font-weight:700;box-shadow:0 0 20px rgba(124,58,237,.4);transition:all .2s;white-space:nowrap}
.btn-nav-cta:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(124,58,237,.6)}
.nav-right{display:flex;align-items:center;gap:8px}

/* ── HERO */
.hero{min-height:100vh;display:flex;align-items:center;padding:120px 0 80px;position:relative;overflow:hidden}
.hero-bg{position:absolute;inset:0;pointer-events:none}
.orb{position:absolute;border-radius:50%;filter:blur(80px);animation:orbFloat 9s ease-in-out infinite}
.orb1{width:700px;height:700px;background:rgba(124,58,237,.16);top:-150px;left:-200px;animation-delay:0s}
.orb2{width:550px;height:550px;background:rgba(99,102,241,.13);top:40%;right:-180px;animation-delay:-4s}
.orb3{width:450px;height:450px;background:rgba(6,182,212,.1);bottom:-120px;left:35%;animation-delay:-7s}
@keyframes orbFloat{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(25px,-25px) scale(1.04)}66%{transform:translate(-18px,18px) scale(.96)}}
.grid-dots{position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.035) 1px,transparent 1px);background-size:30px 30px;mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black,transparent)}

.hero-inner{max-width:1200px;margin:0 auto;padding:0 24px;position:relative;z-index:2;text-align:center}

.badge{display:inline-flex;align-items:center;gap:8px;padding:6px 16px;border-radius:999px;background:rgba(124,58,237,.15);border:1px solid rgba(124,58,237,.3);font-size:12px;font-weight:600;color:#c4b5fd;margin-bottom:28px;animation:fadeUp .6s ease both}
.badge-dot{width:6px;height:6px;border-radius:50%;background:#c4b5fd;animation:pulse 2s ease-in-out infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(1.5)}}

.hero-title{font-size:clamp(2.8rem,7vw,5.5rem);font-weight:900;line-height:1.04;letter-spacing:-2.5px;margin-bottom:24px;animation:fadeUp .7s ease .1s both}
.grad-text{background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

.hero-sub{font-size:clamp(1rem,2vw,1.2rem);color:var(--muted);max-width:580px;margin:0 auto 40px;line-height:1.75;animation:fadeUp .7s ease .2s both}

.hero-btns{display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;animation:fadeUp .7s ease .3s both}
.btn-primary{display:inline-flex;align-items:center;gap:10px;padding:15px 34px;border-radius:14px;text-decoration:none;background:var(--grad);color:#fff;font-size:15px;font-weight:700;box-shadow:0 0 40px rgba(124,58,237,.45);transition:all .25s;position:relative;overflow:hidden}
.btn-primary::before{content:'';position:absolute;inset:0;background:rgba(255,255,255,.12);opacity:0;transition:opacity .25s}
.btn-primary:hover{transform:translateY(-3px);box-shadow:0 0 60px rgba(124,58,237,.65)}
.btn-primary:hover::before{opacity:1}
.btn-ghost{display:inline-flex;align-items:center;gap:10px;padding:15px 34px;border-radius:14px;text-decoration:none;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:#fff;font-size:15px;font-weight:600;transition:all .25s}
.btn-ghost:hover{background:rgba(255,255,255,.11);border-color:rgba(255,255,255,.25);transform:translateY(-3px)}

.tg-link{display:inline-flex;align-items:center;gap:8px;margin-top:20px;color:var(--cyan);font-size:14px;font-weight:500;text-decoration:none;transition:color .2s;animation:fadeUp .7s ease .35s both}
.tg-link:hover{color:#fff}

/* Stats */
.hero-stats{display:flex;align-items:center;justify-content:center;gap:40px;flex-wrap:wrap;margin-top:70px;padding-top:40px;border-top:1px solid var(--border);animation:fadeUp .7s ease .5s both}
.stat{text-align:center}
.stat-num{font-size:2.2rem;font-weight:900;background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1}
.stat-label{font-size:12px;color:var(--muted);margin-top:4px;font-weight:500}
.stat-div{width:1px;height:40px;background:var(--border)}

/* ── PREVIEW */
.preview-section{padding:20px 0 80px;position:relative}
.preview-wrap{max-width:1100px;margin:0 auto;padding:0 24px}
.preview-glow{position:absolute;top:-60px;left:50%;transform:translateX(-50%);width:700px;height:180px;background:radial-gradient(ellipse,rgba(124,58,237,.25),transparent 70%);pointer-events:none}
.preview-box{border-radius:20px;overflow:hidden;border:1px solid rgba(255,255,255,.08);box-shadow:0 40px 100px rgba(0,0,0,.65),0 0 0 1px rgba(255,255,255,.04)}
.preview-topbar{background:rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.06);padding:11px 16px;display:flex;align-items:center;gap:8px}
.pdot{width:10px;height:10px;border-radius:50%}
.purl{flex:1;margin:0 12px;background:rgba(255,255,255,.05);border-radius:6px;padding:4px 12px;font-size:11px;color:var(--muted);text-align:center;font-family:monospace}
.preview-content{background:#0f172a;padding:20px;display:grid;grid-template-columns:180px 1fr;gap:14px;min-height:340px}
.p-sidebar{background:rgba(255,255,255,.03);border-radius:10px;padding:14px;display:flex;flex-direction:column;gap:6px}
.p-nav{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:7px;font-size:11px;color:var(--muted);transition:all .2s}
.p-nav.on{background:rgba(124,58,237,.2);color:#c4b5fd}
.p-nav i{width:14px;text-align:center;font-size:10px}
.p-main{display:flex;flex-direction:column;gap:12px}
.p-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.p-card{border-radius:10px;padding:14px;animation:fadeUp .5s ease both}
.p-card-n{font-size:19px;font-weight:800;color:#fff}
.p-card-l{font-size:10px;color:rgba(255,255,255,.55);margin-top:2px}
.p-chart{background:rgba(255,255,255,.03);border-radius:10px;padding:14px;flex:1;display:flex;align-items:flex-end;gap:5px;min-height:110px}
.p-bar{flex:1;border-radius:4px 4px 0 0;background:linear-gradient(180deg,rgba(124,58,237,.85),rgba(124,58,237,.25));transform-origin:bottom;animation:barGrow .9s ease both}
@keyframes barGrow{from{transform:scaleY(0);opacity:0}to{transform:scaleY(1);opacity:1}}

/* ── SECTION BASE */
.section{padding:90px 0;position:relative}
.container{max-width:1200px;margin:0 auto;padding:0 24px}
.sec-badge{display:inline-flex;align-items:center;gap:8px;padding:5px 14px;border-radius:999px;background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.25);font-size:12px;font-weight:600;color:#c4b5fd;margin-bottom:18px}
.sec-title{font-size:clamp(1.9rem,3.5vw,2.8rem);font-weight:800;line-height:1.15;letter-spacing:-1px;margin-bottom:14px}
.sec-sub{font-size:1.05rem;color:var(--muted);max-width:520px;line-height:1.75}

/* ── FEATURES */
.feat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:18px;margin-top:56px}
.feat-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:28px;transition:all .3s;position:relative;overflow:hidden;cursor:default}
.feat-card::after{content:'';position:absolute;inset:0;border-radius:20px;background:var(--grad);opacity:0;transition:opacity .3s}
.feat-card:hover{transform:translateY(-6px);border-color:rgba(124,58,237,.45);box-shadow:0 20px 60px rgba(0,0,0,.4),0 0 0 1px rgba(124,58,237,.2)}
.feat-card:hover::after{opacity:.03}
.feat-icon{width:50px;height:50px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:18px;position:relative;z-index:1}
.feat-icon::after{content:'';position:absolute;inset:0;border-radius:13px;background:inherit;filter:blur(14px);opacity:.35;z-index:-1}
.feat-title{font-size:17px;font-weight:700;margin-bottom:9px;position:relative;z-index:1}
.feat-desc{font-size:13px;color:var(--muted);line-height:1.7;position:relative;z-index:1}

/* ── STEPS */
.steps-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:20px;margin-top:56px;position:relative}
.steps-grid::before{content:'';position:absolute;top:36px;left:12%;right:12%;height:1px;background:linear-gradient(90deg,transparent,var(--accent),transparent);pointer-events:none}
.step-card{text-align:center;padding:30px 18px;background:var(--card);border:1px solid var(--border);border-radius:18px;transition:all .3s}
.step-card:hover{transform:translateY(-5px);border-color:rgba(124,58,237,.4)}
.step-num{width:54px;height:54px;border-radius:50%;margin:0 auto 18px;background:var(--grad);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:900;color:#fff;box-shadow:0 0 28px rgba(124,58,237,.4)}
.step-icon{font-size:22px;color:#c4b5fd;margin-bottom:12px}
.step-title{font-size:15px;font-weight:700;margin-bottom:8px}
.step-desc{font-size:13px;color:var(--muted);line-height:1.6}

/* ── STATS SECTION */
.stats-bg{padding:80px 0;background:linear-gradient(180deg,transparent,rgba(124,58,237,.04),transparent)}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:20px}
.stat-card{text-align:center;padding:36px 20px;background:var(--card);border:1px solid var(--border);border-radius:18px;transition:all .3s}
.stat-card:hover{transform:translateY(-4px);border-color:rgba(124,58,237,.35)}
.stat-card-icon{font-size:26px;color:var(--accent2);margin-bottom:12px}
.stat-card-num{font-size:2.6rem;font-weight:900;background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;margin-bottom:6px}
.stat-card-label{font-size:13px;color:var(--muted);font-weight:500}

/* ── TESTIMONIALS */
.testi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:18px;margin-top:56px}
.testi-card{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:26px;transition:all .3s}
.testi-card:hover{transform:translateY(-4px);border-color:rgba(124,58,237,.3)}
.testi-stars{color:#fbbf24;font-size:14px;letter-spacing:2px;margin-bottom:14px}
.testi-text{font-size:14px;color:#cbd5e1;line-height:1.75;margin-bottom:18px;font-style:italic}
.testi-author{display:flex;align-items:center;gap:11px}
.testi-avatar{width:40px;height:40px;border-radius:50%;background:var(--grad);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;flex-shrink:0}
.testi-name{font-weight:600;font-size:14px}
.testi-role{font-size:12px;color:var(--muted)}

/* ── PRICING */
.pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:22px;margin-top:56px;align-items:center}
.p-card-wrap{background:var(--card);border:1px solid var(--border);border-radius:22px;padding:34px;transition:all .3s}
.p-card-wrap:hover{transform:translateY(-5px)}
.p-card-wrap.pop{background:linear-gradient(160deg,rgba(124,58,237,.15),rgba(99,102,241,.08));border-color:rgba(124,58,237,.5);box-shadow:0 0 60px rgba(124,58,237,.2);position:relative;transform:scale(1.04)}
.p-card-wrap.pop:hover{transform:scale(1.04) translateY(-5px)}
.pop-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);padding:4px 16px;border-radius:999px;background:var(--grad);color:#fff;font-size:11px;font-weight:700;white-space:nowrap}
.p-name{font-size:13px;font-weight:600;color:var(--muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px}
.p-price{font-size:2.8rem;font-weight:900;margin-bottom:6px;line-height:1}
.p-price-sub{font-size:16px;font-weight:400;color:var(--muted)}
.p-desc{font-size:13px;color:var(--muted);margin-bottom:24px}
.p-features{list-style:none;display:flex;flex-direction:column;gap:11px;margin-bottom:26px}
.p-features li{display:flex;align-items:center;gap:9px;font-size:13px}
.p-features li i.y{color:var(--green)}
.p-features li i.n{color:#334155}
.p-features li.no{color:#475569}
.btn-p{display:block;text-align:center;text-decoration:none;padding:13px 24px;border-radius:12px;font-size:14px;font-weight:700;transition:all .2s}
.btn-p.prim{background:var(--grad);color:#fff;box-shadow:0 0 28px rgba(124,58,237,.4)}
.btn-p.prim:hover{transform:translateY(-2px);box-shadow:0 0 45px rgba(124,58,237,.6)}
.btn-p.out{background:transparent;color:var(--muted);border:1px solid var(--border)}
.btn-p.out:hover{border-color:rgba(124,58,237,.4);color:#fff}

/* ── CONTACT */
.contact-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;margin-top:40px}
.contact-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:22px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:all .3s}
.contact-card:hover{transform:translateY(-4px);border-color:rgba(6,182,212,.4);box-shadow:0 10px 40px rgba(0,0,0,.3)}
.contact-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.contact-label{font-size:12px;color:var(--muted);margin-bottom:3px}
.contact-val{font-size:15px;font-weight:600;color:var(--cyan)}

/* ── CTA */
.cta-section{padding:90px 0;text-align:center}
.cta-box{background:linear-gradient(135deg,rgba(124,58,237,.14),rgba(99,102,241,.08));border:1px solid rgba(124,58,237,.3);border-radius:28px;padding:72px 36px;position:relative;overflow:hidden}
.cta-box::before{content:'';position:absolute;top:-60px;left:50%;transform:translateX(-50%);width:600px;height:350px;background:radial-gradient(ellipse,rgba(124,58,237,.18),transparent 70%);pointer-events:none}
.cta-title{font-size:clamp(1.8rem,3.5vw,3rem);font-weight:900;letter-spacing:-1.5px;margin-bottom:14px;position:relative}
.cta-sub{font-size:1.05rem;color:var(--muted);margin-bottom:36px;position:relative}
.cta-btns{display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;position:relative}
.cta-meta{margin-top:20px;font-size:12px;color:#475569;position:relative}
.cta-meta i{margin-right:3px}

/* ── FOOTER */
footer{border-top:1px solid var(--border);padding:36px 0}
.footer-inner{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px}
.footer-copy{font-size:13px;color:var(--muted)}
.footer-links{display:flex;gap:20px}
.footer-link{font-size:13px;color:var(--muted);text-decoration:none;transition:color .2s}
.footer-link:hover{color:#fff}

/* ── ANIMATIONS */
@keyframes fadeUp{from{opacity:0;transform:translateY(32px)}to{opacity:1;transform:translateY(0)}}
.reveal{opacity:0;transform:translateY(36px);transition:opacity .7s ease,transform .7s ease}
.reveal.vis{opacity:1;transform:translateY(0)}
.d1{transition-delay:.1s}.d2{transition-delay:.2s}.d3{transition-delay:.3s}.d4{transition-delay:.4s}

/* ── RESPONSIVE */
@media(max-width:768px){
  .preview-content{grid-template-columns:1fr}
  .p-sidebar{display:none}
  .p-cards{grid-template-columns:repeat(2,1fr)}
  .steps-grid::before{display:none}
  .p-card-wrap.pop{transform:scale(1)}
  .p-card-wrap.pop:hover{transform:translateY(-5px)}
  .nav-links .nav-link{display:none}
  .hero-stats{gap:20px}
  .stat-div{display:none}
}
</style>
</head>
<body>

<!-- NAV -->
<nav id="nav">
  <div class="nav-inner">
    <a href="#" class="logo">
      <div class="logo-icon"><i class="fa-solid fa-wave-square"></i></div>
      <span class="logo-text">PapaM <span>VoIP</span></span>
    </a>
    <div class="nav-links">
      <a href="#features" class="nav-link">Özellikler</a>
      <a href="#how" class="nav-link">Nasıl Çalışır</a>
      <a href="#pricing" class="nav-link">Fiyatlar</a>
    </div>
    <div class="nav-right">
      <!-- Lang switcher -->
      <?php foreach(['tr'=>'🇹🇷','en'=>'🇬🇧','ru'=>'🇷🇺'] as $code=>$flag): ?>
      <form class="lang-form" method="POST"><input type="hidden" name="lang" value="<?= $code ?>">
        <button type="submit" class="lang-btn <?= $currentLang===$code?'active':'' ?>"><?= $flag ?> <?= strtoupper($code) ?></button>
      </form>
      <?php endforeach; ?>
      <a href="/VoipPanelAi/" class="nav-link" style="border:1px solid var(--border)">Giriş</a>
      <a href="/VoipPanelAi/register" class="btn-nav-cta"><i class="fa-solid fa-rocket"></i> Başla</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg">
    <div class="orb orb1"></div>
    <div class="orb orb2"></div>
    <div class="orb orb3"></div>
    <div class="grid-dots"></div>
  </div>
  <div class="hero-inner">
    <div class="badge"><div class="badge-dot"></div> Profesyonel VoIP Yönetim Platformu</div>

    <h1 class="hero-title">
      Çağrı Merkezinizi<br>
      <span class="grad-text">Tam Kontrol Altına</span><br>
      Alın
    </h1>

    <p class="hero-sub">
      Gerçek zamanlı çağrı izleme, otomatik faturalandırma ve gelişmiş
      raporlama ile çağrı merkezinizi bir üst seviyeye taşıyın.
    </p>

    <div class="hero-btns">
      <a href="/VoipPanelAi/register" class="btn-primary">
        <i class="fa-solid fa-rocket"></i>Ücretsiz Başla<i class="fa-solid fa-arrow-right"></i>
      </a>
      <a href="/VoipPanelAi/" class="btn-ghost">
        <i class="fa-solid fa-right-to-bracket"></i>Giriş Yap
      </a>
    </div>

    <a href="https://t.me/lionmw" target="_blank" class="tg-link">
      <i class="fa-brands fa-telegram fa-lg"></i>Sorularınız için Telegram'dan ulaşın — @lionmw
    </a>

    <div class="hero-stats">
      <div class="stat"><div class="stat-num" data-target="99.9" data-dec="1">0</div><div class="stat-label">% Uptime</div></div>
      <div class="stat-div"></div>
      <div class="stat"><div class="stat-num" data-target="500" data-suf="K+">0</div><div class="stat-label">Günlük Çağrı</div></div>
      <div class="stat-div"></div>
      <div class="stat"><div class="stat-num" data-target="150" data-suf="+">0</div><div class="stat-label">Aktif Müşteri</div></div>
      <div class="stat-div"></div>
      <div class="stat"><div class="stat-num" data-target="24" data-suf="/7">0</div><div class="stat-label">Teknik Destek</div></div>
    </div>
  </div>
</section>

<!-- PREVIEW -->
<div class="preview-section">
  <div class="preview-glow"></div>
  <div class="preview-wrap">
    <div class="preview-box reveal">
      <div class="preview-topbar">
        <div class="pdot" style="background:#ef4444"></div>
        <div class="pdot" style="background:#f59e0b"></div>
        <div class="pdot" style="background:#22c55e"></div>
        <div class="purl">crm.akkocbilisim.com/VoipPanelAi</div>
      </div>
      <div class="preview-content">
        <div class="p-sidebar">
          <div style="font-size:9px;font-weight:700;color:#334155;margin-bottom:6px;padding:0 2px">MENÜ</div>
          <div class="p-nav on"><i class="fa-solid fa-grid-2"></i>Dashboard</div>
          <div class="p-nav"><i class="fa-solid fa-phone"></i>Çağrılar</div>
          <div class="p-nav"><i class="fa-solid fa-headset"></i>Agentler</div>
          <div class="p-nav"><i class="fa-solid fa-users"></i>Kullanıcılar</div>
          <div class="p-nav"><i class="fa-solid fa-layer-group"></i>Gruplar</div>
          <div class="p-nav"><i class="fa-solid fa-chart-bar"></i>Raporlar</div>
          <div class="p-nav"><i class="fa-solid fa-wallet"></i>Bakiye</div>
          <div class="p-nav"><i class="fa-solid fa-cog"></i>Ayarlar</div>
        </div>
        <div class="p-main">
          <div class="p-cards">
            <div class="p-card" style="background:linear-gradient(135deg,rgba(124,58,237,.3),rgba(124,58,237,.1));border:1px solid rgba(124,58,237,.3);animation-delay:.1s">
              <div class="p-card-n">$4,821</div><div class="p-card-l">API Bakiye</div>
            </div>
            <div class="p-card" style="background:linear-gradient(135deg,rgba(16,185,129,.3),rgba(16,185,129,.1));border:1px solid rgba(16,185,129,.3);animation-delay:.2s">
              <div class="p-card-n">1,284</div><div class="p-card-l">Bugün Çağrı</div>
            </div>
            <div class="p-card" style="background:linear-gradient(135deg,rgba(245,158,11,.3),rgba(245,158,11,.1));border:1px solid rgba(245,158,11,.3);animation-delay:.3s">
              <div class="p-card-n">$892</div><div class="p-card-l">Haftalık Kâr</div>
            </div>
            <div class="p-card" style="background:linear-gradient(135deg,rgba(6,182,212,.3),rgba(6,182,212,.1));border:1px solid rgba(6,182,212,.3);animation-delay:.4s">
              <div class="p-card-n">47</div><div class="p-card-l">Aktif Agent</div>
            </div>
          </div>
          <div class="p-chart">
            <?php
            $bars=[38,52,44,68,58,82,63,88,72,94,78,68,85,100];
            foreach($bars as $i=>$h):
            ?><div class="p-bar" style="height:<?=$h?>%;animation-delay:<?=$i*.05?>s"></div><?php endforeach;?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- FEATURES -->
<section class="section" id="features">
  <div class="container">
    <div class="reveal" style="max-width:600px">
      <div class="sec-badge"><i class="fa-solid fa-sparkles"></i> Özellikler</div>
      <h2 class="sec-title">Her şey tek platformda,<br><span class="grad-text">eksiksiz kontrol</span></h2>
      <p class="sec-sub">Çağrı merkezinizi yönetmek için ihtiyaç duyduğunuz tüm araçlar.</p>
    </div>
    <div class="feat-grid">
      <?php
      $feats=[
        ['linear-gradient(135deg,#7c3aed,#6366f1)','fa-chart-line','Gerçek Zamanlı Analitik','Çağrı istatistiklerini anlık izleyin. Cevap oranları, süreler ve maliyet analizlerini dakika dakika takip edin.'],
        ['linear-gradient(135deg,#10b981,#06b6d4)','fa-layer-group','Çoklu Grup Yönetimi','Sınırsız grup oluşturun, her gruba bağımsız bakiye ve kota tanımlayın. Farklı ekipleri tek panel üzerinden yönetin.'],
        ['linear-gradient(135deg,#f59e0b,#ef4444)','fa-bolt','Otomatik Faturalandırma','Saniye bazında faturalandırma, margin yönetimi ve otomatik bakiye uyarıları ile finansal kontrolü elinizde tutun.'],
        ['linear-gradient(135deg,#06b6d4,#3b82f6)','fa-headset','Agent Yönetimi','Agentleri ekleyin, aktif/pasif yapın ve abonelik planları ile maliyetleri optimize edin.'],
        ['linear-gradient(135deg,#7c3aed,#ec4899)','fa-brands fa-telegram','Telegram Bildirimleri','Bakiye düştüğünde veya kritik anlarda anında Telegram bildirimi alın. @lionmw'],
        ['linear-gradient(135deg,#10b981,#22c55e)','fa-shield-halved','Güvenli Ödeme Sistemi','USDT/TRC20 kripto ödeme desteği, güvenli bakiye yükleme ve tam işlem geçmişi.'],
        ['linear-gradient(135deg,#f59e0b,#fbbf24)','fa-table-list','Detaylı CDR Raporları','Tüm çağrı kayıtlarını filtreleyin, sıralayın ve CSV olarak indirin. Tarih, agent, grup filtresi.'],
        ['linear-gradient(135deg,#6366f1,#06b6d4)','fa-globe','Çok Dilli Destek','Türkçe, İngilizce ve Rusça dil desteği. Kullanıcılarınız kendi dillerinde çalışabilir.'],
        ['linear-gradient(135deg,#ec4899,#f43f5e)','fa-mobile-screen','Mobil Uyumlu Tasarım','Her cihazda mükemmel çalışan responsive tasarım. Telefon, tablet ve masaüstünde sorunsuz.'],
      ];
      foreach($feats as $i=>[$bg,$icon,$title,$desc]):
        $d=['','d1','d2'][$i%3];
      ?>
      <div class="feat-card reveal <?=$d?>">
        <div class="feat-icon" style="background:<?=$bg?>"><i class="fa-solid <?=$icon?>" style="color:#fff;font-size:20px"></i></div>
        <div class="feat-title"><?=$title?></div>
        <div class="feat-desc"><?=$desc?></div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" id="how" style="background:linear-gradient(180deg,transparent,rgba(124,58,237,.04),transparent)">
  <div class="container">
    <div class="reveal" style="text-align:center">
      <div class="sec-badge" style="display:inline-flex"><i class="fa-solid fa-map"></i> Nasıl Çalışır</div>
      <h2 class="sec-title" style="margin-top:10px">Dakikalar içinde <span class="grad-text">hazır</span></h2>
      <p class="sec-sub" style="margin:0 auto">Karmaşık kurulum gerektirmez. Hesabınızı açın ve hemen başlayın.</p>
    </div>
    <div class="steps-grid">
      <?php
      $steps=[
        ['Hesap Oluşturun','Hızlı kayıt formu ile dakikalar içinde hesabınızı açın.','fa-user-plus'],
        ['API Bağlantısı','VoIP sağlayıcınızın API bilgilerini girin, sistemi entegre edin.','fa-plug'],
        ['Grup & Agent','Ekiplerinizi gruplar halinde organize edin, agentleri ekleyin.','fa-users-gear'],
        ['Yayına Geçin','Her şey hazır! Çağrılarınızı gerçek zamanlı izlemeye başlayın.','fa-rocket'],
      ];
      foreach($steps as $i=>[$t,$d,$ic]):
      ?>
      <div class="step-card reveal d<?=$i+1?>">
        <div class="step-num"><?=$i+1?></div>
        <div class="step-icon"><i class="fa-solid <?=$ic?>"></i></div>
        <div class="step-title"><?=$t?></div>
        <div class="step-desc"><?=$d?></div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="stats-bg">
  <div class="container">
    <div class="stats-grid">
      <?php
      $sc=[['99.9%','Uptime SLA','fa-server'],['< 100ms','Gecikme','fa-bolt'],['∞','Eş Zamanlı Çağrı','fa-phone'],['7/24','Teknik Destek','fa-headset'],['256-bit','SSL Şifreleme','fa-shield'],['GDPR','Uyumlu','fa-file-shield']];
      foreach($sc as $i=>[$n,$l,$ic]):
        $d=['','d1','d2','d3','d4','d1'][$i];
      ?>
      <div class="stat-card reveal <?=$d?>">
        <div class="stat-card-icon"><i class="fa-solid <?=$ic?>"></i></div>
        <div class="stat-card-num"><?=$n?></div>
        <div class="stat-card-label"><?=$l?></div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</div>

<!-- TESTIMONIALS -->
<section class="section">
  <div class="container">
    <div class="reveal" style="text-align:center">
      <div class="sec-badge" style="display:inline-flex"><i class="fa-solid fa-star"></i> Referanslar</div>
      <h2 class="sec-title" style="margin-top:10px">Müşterilerimiz <span class="grad-text">ne diyor?</span></h2>
    </div>
    <div class="testi-grid">
      <?php
      $tt=[
        ['Panel sayesinde çağrı maliyetlerimizi %30 düşürdük. Gerçek zamanlı raporlama müthiş, her şeyi anlık görüyoruz.','A','Ahmet Y.','Çağrı Merkezi Müdürü'],
        ['Çoklu grup yönetimi özelliği harika. Farklı departmanlarımızı tek panelden yönetiyoruz.','M','Mehmet K.','IT Direktörü'],
        ['Telegram bildirimleri sayesinde bakiye bitmeden haberdar oluyoruz. Kesinti yaşamıyoruz artık.','F','Fatma Ş.','Operasyon Sorumlusu'],
      ];
      foreach($tt as [$text,$av,$name,$role]):
      ?>
      <div class="testi-card reveal">
        <div class="testi-stars">★★★★★</div>
        <div class="testi-text">"<?=$text?>"</div>
        <div class="testi-author">
          <div class="testi-avatar"><?=$av?></div>
          <div><div class="testi-name"><?=$name?></div><div class="testi-role"><?=$role?></div></div>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- PRICING -->
<section class="section" id="pricing" style="background:linear-gradient(180deg,transparent,rgba(124,58,237,.04),transparent)">
  <div class="container">
    <div class="reveal" style="text-align:center">
      <div class="sec-badge" style="display:inline-flex"><i class="fa-solid fa-tag"></i> Fiyatlandırma</div>
      <h2 class="sec-title" style="margin-top:10px">İhtiyacınıza uygun <span class="grad-text">plan seçin</span></h2>
      <p class="sec-sub" style="margin:0 auto">Tüm planlar 14 gün ücretsiz deneme içerir. Kredi kartı gerekmez.</p>
    </div>
    <div class="pricing-grid">
      <div class="p-card-wrap reveal d1">
        <div class="p-name">STARTER</div>
        <div class="p-price">$49<span class="p-price-sub">/ay</span></div>
        <div class="p-desc">Küçük ekipler için ideal başlangıç</div>
        <ul class="p-features">
          <li><i class="fa-solid fa-check y"></i>5 Agent</li>
          <li><i class="fa-solid fa-check y"></i>2 Grup</li>
          <li><i class="fa-solid fa-check y"></i>CDR Raporları</li>
          <li><i class="fa-solid fa-check y"></i>Email Destek</li>
          <li class="no"><i class="fa-solid fa-xmark n"></i>Telegram Bildirim</li>
          <li class="no"><i class="fa-solid fa-xmark n"></i>API Entegrasyon</li>
        </ul>
        <a href="https://t.me/lionmw" target="_blank" class="btn-p out">Teklif Al</a>
      </div>
      <div class="p-card-wrap pop reveal d2">
        <div class="pop-badge">🔥 En Popüler</div>
        <div class="p-name">PROFESSIONAL</div>
        <div class="p-price">$149<span class="p-price-sub">/ay</span></div>
        <div class="p-desc">Büyüyen işletmeler için tam paket</div>
        <ul class="p-features">
          <li><i class="fa-solid fa-check y"></i>25 Agent</li>
          <li><i class="fa-solid fa-check y"></i>Sınırsız Grup</li>
          <li><i class="fa-solid fa-check y"></i>Gelişmiş CDR Raporları</li>
          <li><i class="fa-solid fa-check y"></i>Telegram Bildirim</li>
          <li><i class="fa-solid fa-check y"></i>API Entegrasyon</li>
          <li><i class="fa-solid fa-check y"></i>Kripto Ödeme (USDT)</li>
        </ul>
        <a href="https://t.me/lionmw" target="_blank" class="btn-p prim">Demo Talep Et</a>
      </div>
      <div class="p-card-wrap reveal d3">
        <div class="p-name">ENTERPRISE</div>
        <div class="p-price">Özel<span class="p-price-sub"> fiyat</span></div>
        <div class="p-desc">Kurumsal müşteriler için özel çözüm</div>
        <ul class="p-features">
          <li><i class="fa-solid fa-check y"></i>Sınırsız Agent</li>
          <li><i class="fa-solid fa-check y"></i>Sınırsız Grup</li>
          <li><i class="fa-solid fa-check y"></i>White-label Seçeneği</li>
          <li><i class="fa-solid fa-check y"></i>Özel Entegrasyonlar</li>
          <li><i class="fa-solid fa-check y"></i>SLA Garantisi</li>
          <li><i class="fa-solid fa-check y"></i>7/24 Öncelikli Destek</li>
        </ul>
        <a href="https://t.me/lionmw" target="_blank" class="btn-p out">Bize Ulaşın</a>
      </div>
    </div>

    <!-- Contact cards -->
    <div class="reveal" style="margin-top:56px;text-align:center">
      <h3 style="font-size:20px;font-weight:700;margin-bottom:8px">İletişim</h3>
      <p style="font-size:14px;color:var(--muted);margin-bottom:0">Sorularınız için Telegram üzerinden ulaşın</p>
    </div>
    <div class="contact-grid">
      <a href="https://t.me/lionmw" target="_blank" class="contact-card reveal d1">
        <div class="contact-icon" style="background:linear-gradient(135deg,#0088cc,#06b6d4)"><i class="fa-brands fa-telegram" style="color:#fff"></i></div>
        <div><div class="contact-label">Satış & Genel</div><div class="contact-val">@lionmw</div></div>
      </a>
      <a href="https://t.me/Itsupportemre" target="_blank" class="contact-card reveal d2">
        <div class="contact-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i class="fa-brands fa-telegram" style="color:#fff"></i></div>
        <div><div class="contact-label">Teknik Destek</div><div class="contact-val">@Itsupportemre</div></div>
      </a>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="container">
    <div class="cta-box reveal">
      <h2 class="cta-title">Bugün başlayın,<br><span class="grad-text">farkı hemen görün</span></h2>
      <p class="cta-sub">Dakikalar içinde kurulum yapın, anında sonuç görün. 14 gün ücretsiz deneyin.</p>
      <div class="cta-btns">
        <a href="/VoipPanelAi/register" class="btn-primary" style="font-size:15px;padding:15px 38px">
          <i class="fa-solid fa-rocket"></i>Ücretsiz Hesap Aç<i class="fa-solid fa-arrow-right"></i>
        </a>
        <a href="/VoipPanelAi/" class="btn-ghost" style="font-size:15px;padding:15px 38px">
          <i class="fa-solid fa-right-to-bracket"></i>Giriş Yap
        </a>
      </div>
      <p class="cta-meta">
        <i class="fa-solid fa-lock"></i> Kredi kartı gerekmez &nbsp;·&nbsp;
        <i class="fa-solid fa-check"></i> Anında kurulum &nbsp;·&nbsp;
        <i class="fa-solid fa-shield"></i> SSL şifreli
      </p>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="footer-inner">
      <div>
        <a href="#" class="logo" style="margin-bottom:8px;display:inline-flex">
          <div class="logo-icon"><i class="fa-solid fa-wave-square"></i></div>
          <span class="logo-text">PapaM <span>VoIP</span></span>
        </a>
        <div class="footer-copy" style="margin-top:6px">© <?=date('Y')?> PapaM VoIP. Tüm hakları saklıdır.</div>
      </div>
      <div class="footer-links">
        <a href="#features" class="footer-link">Özellikler</a>
        <a href="#pricing" class="footer-link">Fiyatlar</a>
        <a href="/VoipPanelAi/" class="footer-link">Giriş</a>
        <a href="/VoipPanelAi/register" class="footer-link">Kayıt</a>
        <a href="https://t.me/lionmw" target="_blank" class="footer-link">Telegram</a>
      </div>
    </div>
  </div>
</footer>

<script>
// Nav scroll
window.addEventListener('scroll',()=>document.getElementById('nav').classList.toggle('scrolled',scrollY>40),{passive:true});

// Reveal
const obs=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting)e.target.classList.add('vis')}),{threshold:.1,rootMargin:'0px 0px -40px 0px'});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));

// Counter
function runCounters(){
  document.querySelectorAll('[data-target]').forEach(el=>{
    const target=parseFloat(el.dataset.target);
    const dec=parseInt(el.dataset.dec||0);
    const suf=el.dataset.suf||'';
    let v=0,dur=1800,step=16,inc=target/(dur/step);
    const t=setInterval(()=>{
      v=Math.min(v+inc,target);
      el.textContent=(dec?v.toFixed(dec):Math.floor(v))+suf;
      if(v>=target)clearInterval(t);
    },step);
  });
}
const heroObs=new IntersectionObserver(es=>{if(es[0].isIntersecting){runCounters();heroObs.disconnect();}},{threshold:.3});
const hs=document.querySelector('.hero-stats');
if(hs)heroObs.observe(hs);

// Smooth anchors
document.querySelectorAll('a[href^="#"]').forEach(a=>a.addEventListener('click',e=>{
  const t=document.querySelector(a.getAttribute('href'));
  if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth',block:'start'});}
}));

// Parallax orbs
document.addEventListener('mousemove',e=>{
  const x=(e.clientX/innerWidth-.5)*18,y=(e.clientY/innerHeight-.5)*18;
  document.querySelectorAll('.orb').forEach((o,i)=>{
    const f=(i+1)*.28;
    o.style.transform=`translate(${x*f}px,${y*f}px)`;
  });
},{passive:true});

// Animated gradient text shine
(function(){
  let deg=135;
  setInterval(()=>{
    deg=(deg+.3)%360;
    document.querySelectorAll('.grad-text').forEach(el=>{
      el.style.backgroundImage=`linear-gradient(${deg}deg,#7c3aed,#6366f1,#06b6d4)`;
    });
  },30);
})();
</script>
</body>
</html>