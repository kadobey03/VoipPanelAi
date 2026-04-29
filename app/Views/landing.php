<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VoIP Panel — Akıllı Çağrı Merkezi Yönetimi</title>
  <meta name="description" content="Profesyonel VoIP çağrı merkezi yönetim paneli. Gerçek zamanlı izleme, detaylı raporlama ve çoklu grup yönetimi.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --c-bg:       #030712;
      --c-bg2:      #0f172a;
      --c-card:     #0f172a;
      --c-border:   rgba(255,255,255,0.07);
      --c-text:     #f1f5f9;
      --c-muted:    #94a3b8;
      --c-accent:   #6366f1;
      --c-accent2:  #8b5cf6;
      --c-green:    #10b981;
      --c-cyan:     #06b6d4;
      --grad-hero:  linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--c-bg);
      color: var(--c-text);
      overflow-x: hidden;
      line-height: 1.6;
    }

    /* ── SCROLLBAR ─────────────────────────────────────────── */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: var(--c-bg); }
    ::-webkit-scrollbar-thumb { background: var(--c-accent); border-radius: 3px; }

    /* ── NOISE OVERLAY ─────────────────────────────────────── */
    body::before {
      content: '';
      position: fixed; inset: 0; z-index: 0; pointer-events: none;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
      opacity: .4;
    }

    /* ── UTILITY ───────────────────────────────────────────── */
    .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; position: relative; z-index: 1; }
    .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }

    /* ── NAV ───────────────────────────────────────────────── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      padding: 16px 0;
      transition: background .3s, backdrop-filter .3s, border .3s;
    }
    nav.scrolled {
      background: rgba(3,7,18,.85);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--c-border);
    }
    .nav-inner {
      display: flex; align-items: center; justify-content: space-between;
    }
    .nav-logo {
      display: flex; align-items: center; gap: 10px; text-decoration: none;
    }
    .nav-logo-icon {
      width: 36px; height: 36px; border-radius: 10px;
      background: var(--grad-hero);
      display: flex; align-items: center; justify-content: center;
      font-size: 16px; color: #fff;
      box-shadow: 0 0 20px rgba(99,102,241,.5);
    }
    .nav-logo-text { font-weight: 800; font-size: 18px; color: #fff; }
    .nav-logo-text span { color: #a5b4fc; }
    .nav-links { display: flex; align-items: center; gap: 8px; }
    .nav-link {
      padding: 8px 16px; border-radius: 8px; text-decoration: none;
      color: var(--c-muted); font-size: 14px; font-weight: 500;
      transition: color .2s, background .2s;
    }
    .nav-link:hover { color: #fff; background: rgba(255,255,255,.06); }
    .nav-cta {
      padding: 9px 20px; border-radius: 10px; text-decoration: none;
      background: var(--grad-hero); color: #fff; font-size: 14px; font-weight: 600;
      box-shadow: 0 0 20px rgba(99,102,241,.4);
      transition: transform .2s, box-shadow .2s;
    }
    .nav-cta:hover { transform: translateY(-2px); box-shadow: 0 0 30px rgba(99,102,241,.6); }

    /* ── HERO ──────────────────────────────────────────────── */
    .hero {
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
      padding: 120px 0 80px;
      position: relative; overflow: hidden;
    }

    /* Animated gradient orbs */
    .orb {
      position: absolute; border-radius: 50%;
      filter: blur(80px); pointer-events: none;
      animation: orbFloat 8s ease-in-out infinite;
    }
    .orb-1 { width: 600px; height: 600px; background: rgba(99,102,241,.18); top: -100px; left: -200px; animation-delay: 0s; }
    .orb-2 { width: 500px; height: 500px; background: rgba(139,92,246,.15); top: 50%; right: -150px; animation-delay: -3s; }
    .orb-3 { width: 400px; height: 400px; background: rgba(6,182,212,.12); bottom: -100px; left: 30%; animation-delay: -6s; }

    @keyframes orbFloat {
      0%, 100% { transform: translate(0, 0) scale(1); }
      33% { transform: translate(30px, -30px) scale(1.05); }
      66% { transform: translate(-20px, 20px) scale(.95); }
    }

    /* Grid dots background */
    .hero::after {
      content: '';
      position: absolute; inset: 0; pointer-events: none;
      background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
      background-size: 32px 32px;
      mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black, transparent);
    }

    .hero-content { text-align: center; position: relative; z-index: 2; }

    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 6px 16px; border-radius: 999px;
      background: rgba(99,102,241,.15); border: 1px solid rgba(99,102,241,.3);
      font-size: 12px; font-weight: 600; color: #a5b4fc;
      margin-bottom: 28px;
      animation: fadeInDown .6s ease both;
    }
    .hero-badge-dot {
      width: 6px; height: 6px; border-radius: 50%;
      background: #a5b4fc;
      animation: pulse 2s ease-in-out infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.4)} }

    .hero-title {
      font-size: clamp(2.5rem, 7vw, 5.5rem);
      font-weight: 900; line-height: 1.05;
      letter-spacing: -2px;
      margin-bottom: 24px;
      animation: fadeInUp .7s ease .1s both;
    }
    .hero-title .gradient-text {
      background: var(--grad-hero);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-sub {
      font-size: clamp(1rem, 2vw, 1.25rem);
      color: var(--c-muted); max-width: 600px; margin: 0 auto 40px;
      font-weight: 400; line-height: 1.7;
      animation: fadeInUp .7s ease .2s both;
    }

    .hero-actions {
      display: flex; align-items: center; justify-content: center;
      gap: 12px; flex-wrap: wrap;
      animation: fadeInUp .7s ease .3s both;
    }

    .btn-primary {
      display: inline-flex; align-items: center; gap: 10px;
      padding: 14px 32px; border-radius: 14px; text-decoration: none;
      background: var(--grad-hero); color: #fff;
      font-size: 15px; font-weight: 700;
      box-shadow: 0 0 40px rgba(99,102,241,.4);
      transition: transform .25s, box-shadow .25s;
      position: relative; overflow: hidden;
    }
    .btn-primary::after {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,.15), transparent);
      opacity: 0; transition: opacity .25s;
    }
    .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 0 60px rgba(99,102,241,.6); }
    .btn-primary:hover::after { opacity: 1; }

    .btn-ghost {
      display: inline-flex; align-items: center; gap: 10px;
      padding: 14px 32px; border-radius: 14px; text-decoration: none;
      background: rgba(255,255,255,.06); color: #fff;
      border: 1px solid rgba(255,255,255,.12);
      font-size: 15px; font-weight: 600;
      transition: background .25s, border-color .25s, transform .25s;
    }
    .btn-ghost:hover { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.25); transform: translateY(-3px); }

    /* Hero stats bar */
    .hero-stats {
      display: flex; align-items: center; justify-content: center;
      gap: 40px; flex-wrap: wrap;
      margin-top: 64px;
      animation: fadeInUp .7s ease .5s both;
    }
    .hero-stat { text-align: center; }
    .hero-stat-num {
      font-size: 2rem; font-weight: 800;
      background: var(--grad-hero);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .hero-stat-label { font-size: 12px; color: var(--c-muted); font-weight: 500; margin-top: 2px; }
    .hero-stat-divider { width: 1px; height: 40px; background: var(--c-border); }

    /* ── DASHBOARD PREVIEW ─────────────────────────────────── */
    .preview-section {
      padding: 40px 0 100px;
      position: relative;
    }
    .preview-wrapper {
      position: relative; border-radius: 20px; overflow: hidden;
      border: 1px solid rgba(255,255,255,.08);
      box-shadow: 0 40px 100px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.04);
      animation: fadeInUp .8s ease .4s both;
    }
    .preview-glow {
      position: absolute; top: -100px; left: 50%; transform: translateX(-50%);
      width: 600px; height: 200px;
      background: radial-gradient(ellipse, rgba(99,102,241,.3), transparent 70%);
      pointer-events: none;
    }
    .preview-bar {
      background: rgba(255,255,255,.04);
      border-bottom: 1px solid rgba(255,255,255,.06);
      padding: 12px 16px;
      display: flex; align-items: center; gap: 8px;
    }
    .preview-dot { width: 10px; height: 10px; border-radius: 50%; }
    .preview-url {
      flex: 1; margin: 0 12px;
      background: rgba(255,255,255,.05); border-radius: 6px;
      padding: 4px 12px; font-size: 12px; color: var(--c-muted);
      text-align: center;
    }
    .preview-body {
      background: #0f172a;
      padding: 24px;
      display: grid; grid-template-columns: 200px 1fr; gap: 16px;
      min-height: 360px;
    }
    .preview-sidebar {
      background: rgba(255,255,255,.03); border-radius: 12px; padding: 16px;
      display: flex; flex-direction: column; gap: 8px;
    }
    .preview-nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 12px; border-radius: 8px;
      font-size: 12px; color: var(--c-muted);
      transition: background .2s, color .2s;
    }
    .preview-nav-item.active { background: rgba(99,102,241,.2); color: #a5b4fc; }
    .preview-nav-item i { width: 16px; text-align: center; font-size: 11px; }
    .preview-main { display: flex; flex-direction: column; gap: 12px; }
    .preview-cards { display: grid; grid-template-columns: repeat(4,1fr); gap: 10px; }
    .preview-card {
      border-radius: 10px; padding: 14px;
      animation: countUp 1.5s ease both;
    }
    .preview-card-num { font-size: 18px; font-weight: 800; color: #fff; }
    .preview-card-label { font-size: 10px; color: rgba(255,255,255,.6); margin-top: 2px; }
    .preview-chart {
      background: rgba(255,255,255,.03); border-radius: 10px; padding: 14px; flex: 1;
      display: flex; align-items: flex-end; gap: 6px; min-height: 120px;
    }
    .preview-bar-item {
      flex: 1; border-radius: 4px 4px 0 0;
      background: linear-gradient(180deg, rgba(99,102,241,.8), rgba(99,102,241,.3));
      transform-origin: bottom;
      animation: growBar .8s ease both;
    }
    @keyframes growBar { from { transform: scaleY(0); opacity: 0; } to { transform: scaleY(1); opacity: 1; } }

    /* ── FEATURES ──────────────────────────────────────────── */
    .section {
      padding: 100px 0;
      position: relative;
    }
    .section-label {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 5px 14px; border-radius: 999px;
      background: rgba(99,102,241,.12); border: 1px solid rgba(99,102,241,.25);
      font-size: 12px; font-weight: 600; color: #a5b4fc;
      margin-bottom: 20px;
    }
    .section-title {
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 800; line-height: 1.15;
      letter-spacing: -1px; margin-bottom: 16px;
    }
    .section-sub { font-size: 1.1rem; color: var(--c-muted); max-width: 540px; line-height: 1.7; }

    .features-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 20px; margin-top: 64px;
    }
    .feature-card {
      background: var(--c-card);
      border: 1px solid var(--c-border);
      border-radius: 20px; padding: 32px;
      transition: border-color .3s, transform .3s, box-shadow .3s;
      position: relative; overflow: hidden;
      cursor: default;
    }
    .feature-card::before {
      content: '';
      position: absolute; inset: 0; border-radius: 20px;
      background: var(--grad-hero); opacity: 0;
      transition: opacity .3s;
    }
    .feature-card:hover { transform: translateY(-6px); border-color: rgba(99,102,241,.4); box-shadow: 0 20px 60px rgba(0,0,0,.4), 0 0 0 1px rgba(99,102,241,.2); }
    .feature-card:hover::before { opacity: .03; }

    .feature-icon {
      width: 52px; height: 52px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; margin-bottom: 20px;
      position: relative;
    }
    .feature-icon::after {
      content: '';
      position: absolute; inset: 0; border-radius: 14px;
      background: inherit; filter: blur(16px); opacity: .4; z-index: -1;
    }
    .feature-title { font-size: 18px; font-weight: 700; margin-bottom: 10px; }
    .feature-desc { font-size: 14px; color: var(--c-muted); line-height: 1.7; }

    /* ── HOW IT WORKS ──────────────────────────────────────── */
    .steps-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 24px; margin-top: 64px; position: relative;
    }
    .steps-grid::before {
      content: '';
      position: absolute; top: 40px; left: 10%; right: 10%; height: 1px;
      background: linear-gradient(90deg, transparent, var(--c-accent), transparent);
      pointer-events: none;
    }
    .step-card {
      text-align: center; padding: 32px 20px;
      background: var(--c-card); border: 1px solid var(--c-border);
      border-radius: 20px; position: relative;
      transition: transform .3s, border-color .3s;
    }
    .step-card:hover { transform: translateY(-4px); border-color: rgba(99,102,241,.4); }
    .step-num {
      width: 56px; height: 56px; border-radius: 50%; margin: 0 auto 20px;
      background: var(--grad-hero);
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; font-weight: 900; color: #fff;
      box-shadow: 0 0 30px rgba(99,102,241,.4);
    }
    .step-title { font-size: 16px; font-weight: 700; margin-bottom: 10px; }
    .step-desc { font-size: 13px; color: var(--c-muted); line-height: 1.6; }

    /* ── STATS SECTION ─────────────────────────────────────── */
    .stats-section {
      padding: 100px 0; position: relative;
      background: linear-gradient(180deg, transparent, rgba(99,102,241,.04), transparent);
    }
    .stats-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 24px;
    }
    .stat-card {
      text-align: center; padding: 40px 24px;
      background: var(--c-card); border: 1px solid var(--c-border);
      border-radius: 20px;
      transition: transform .3s, border-color .3s;
    }
    .stat-card:hover { transform: translateY(-4px); border-color: rgba(99,102,241,.3); }
    .stat-num {
      font-size: 3rem; font-weight: 900; line-height: 1;
      background: var(--grad-hero);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 8px;
    }
    .stat-label { font-size: 14px; color: var(--c-muted); font-weight: 500; }

    /* ── TESTIMONIALS ──────────────────────────────────────── */
    .testimonials-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px; margin-top: 64px;
    }
    .testimonial-card {
      background: var(--c-card); border: 1px solid var(--c-border);
      border-radius: 20px; padding: 28px;
      transition: transform .3s, border-color .3s;
    }
    .testimonial-card:hover { transform: translateY(-4px); border-color: rgba(99,102,241,.3); }
    .testimonial-stars { color: #fbbf24; font-size: 14px; margin-bottom: 16px; letter-spacing: 2px; }
    .testimonial-text { font-size: 15px; color: #cbd5e1; line-height: 1.7; margin-bottom: 20px; font-style: italic; }
    .testimonial-author { display: flex; align-items: center; gap: 12px; }
    .testimonial-avatar {
      width: 42px; height: 42px; border-radius: 50%;
      background: var(--grad-hero);
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 16px; color: #fff; flex-shrink: 0;
    }
    .testimonial-name { font-weight: 600; font-size: 14px; }
    .testimonial-role { font-size: 12px; color: var(--c-muted); }

    /* ── PRICING ───────────────────────────────────────────── */
    .pricing-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 24px; margin-top: 64px; align-items: center;
    }
    .pricing-card {
      background: var(--c-card); border: 1px solid var(--c-border);
      border-radius: 24px; padding: 36px;
      transition: transform .3s, box-shadow .3s;
    }
    .pricing-card:hover { transform: translateY(-6px); }
    .pricing-card.popular {
      background: linear-gradient(160deg, rgba(99,102,241,.15), rgba(139,92,246,.08));
      border-color: rgba(99,102,241,.5);
      box-shadow: 0 0 60px rgba(99,102,241,.2);
      position: relative; transform: scale(1.04);
    }
    .pricing-card.popular:hover { transform: scale(1.04) translateY(-6px); }
    .popular-badge {
      position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
      padding: 4px 16px; border-radius: 999px;
      background: var(--grad-hero); color: #fff;
      font-size: 11px; font-weight: 700; white-space: nowrap;
    }
    .pricing-name { font-size: 14px; font-weight: 600; color: var(--c-muted); margin-bottom: 12px; }
    .pricing-price { font-size: 3rem; font-weight: 900; margin-bottom: 4px; }
    .pricing-price span { font-size: 18px; font-weight: 500; color: var(--c-muted); }
    .pricing-desc { font-size: 13px; color: var(--c-muted); margin-bottom: 28px; }
    .pricing-features { list-style: none; display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; }
    .pricing-features li { display: flex; align-items: center; gap: 10px; font-size: 14px; }
    .pricing-features li i { color: var(--c-green); font-size: 13px; flex-shrink: 0; }
    .pricing-features li.no i { color: #475569; }
    .pricing-features li.no { color: #475569; }
    .btn-pricing {
      display: block; text-align: center; text-decoration: none;
      padding: 13px 24px; border-radius: 12px;
      font-size: 14px; font-weight: 700;
      transition: transform .2s, box-shadow .2s;
    }
    .btn-pricing.primary {
      background: var(--grad-hero); color: #fff;
      box-shadow: 0 0 30px rgba(99,102,241,.4);
    }
    .btn-pricing.primary:hover { transform: translateY(-2px); box-shadow: 0 0 50px rgba(99,102,241,.6); }
    .btn-pricing.outline {
      background: transparent; color: var(--c-muted);
      border: 1px solid var(--c-border);
    }
    .btn-pricing.outline:hover { border-color: rgba(99,102,241,.4); color: #fff; }

    /* ── CTA SECTION ───────────────────────────────────────── */
    .cta-section {
      padding: 100px 0;
      text-align: center;
    }
    .cta-box {
      background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(139,92,246,.1));
      border: 1px solid rgba(99,102,241,.3);
      border-radius: 32px; padding: 80px 40px;
      position: relative; overflow: hidden;
    }
    .cta-box::before {
      content: '';
      position: absolute; top: -50%; left: 50%; transform: translateX(-50%);
      width: 600px; height: 400px;
      background: radial-gradient(ellipse, rgba(99,102,241,.2), transparent 70%);
      pointer-events: none;
    }
    .cta-title {
      font-size: clamp(2rem, 4vw, 3.5rem);
      font-weight: 900; letter-spacing: -1.5px;
      margin-bottom: 16px; position: relative;
    }
    .cta-sub { font-size: 1.1rem; color: var(--c-muted); margin-bottom: 40px; position: relative; }

    /* ── FOOTER ────────────────────────────────────────────── */
    footer {
      border-top: 1px solid var(--c-border);
      padding: 40px 0;
      position: relative; z-index: 1;
    }
    .footer-inner {
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 16px;
    }
    .footer-copy { font-size: 13px; color: var(--c-muted); }
    .footer-links { display: flex; gap: 24px; }
    .footer-link { font-size: 13px; color: var(--c-muted); text-decoration: none; transition: color .2s; }
    .footer-link:hover { color: #fff; }

    /* ── ANIMATIONS ────────────────────────────────────────── */
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes countUp {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .reveal {
      opacity: 0; transform: translateY(40px);
      transition: opacity .7s ease, transform .7s ease;
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: .1s; }
    .reveal-delay-2 { transition-delay: .2s; }
    .reveal-delay-3 { transition-delay: .3s; }
    .reveal-delay-4 { transition-delay: .4s; }

    /* ── FLOATING ELEMENTS ─────────────────────────────────── */
    .float-el {
      position: absolute; pointer-events: none;
      animation: floatUpDown 4s ease-in-out infinite;
    }
    @keyframes floatUpDown {
      0%,100% { transform: translateY(0); }
      50%      { transform: translateY(-14px); }
    }

    /* ── RESPONSIVE ────────────────────────────────────────── */
    @media (max-width: 768px) {
      .preview-body { grid-template-columns: 1fr; }
      .preview-sidebar { display: none; }
      .preview-cards { grid-template-columns: repeat(2,1fr); }
      .steps-grid::before { display: none; }
      .pricing-card.popular { transform: scale(1); }
      .pricing-card.popular:hover { transform: translateY(-6px); }
      .nav-links { gap: 4px; }
      .nav-link { display: none; }
    }
  </style>
</head>
<body>

<!-- ── NAV ────────────────────────────────────────────────────────── -->
<nav id="navbar">
  <div class="container">
    <div class="nav-inner">
      <a href="#" class="nav-logo">
        <div class="nav-logo-icon"><i class="fa-solid fa-wave-square"></i></div>
        <span class="nav-logo-text">VoIP<span>Panel</span></span>
      </a>
      <div class="nav-links">
        <a href="#features" class="nav-link">Özellikler</a>
        <a href="#how" class="nav-link">Nasıl Çalışır</a>
        <a href="#pricing" class="nav-link">Fiyatlar</a>
        <a href="<?= \App\Helpers\Url::to('/login') ?>" class="nav-link">Giriş Yap</a>
        <a href="<?= \App\Helpers\Url::to('/register') ?>" class="nav-cta">Ücretsiz Başla</a>
      </div>
    </div>
  </div>
</nav>

<!-- ── HERO ───────────────────────────────────────────────────────── -->
<section class="hero">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <div class="container">
    <div class="hero-content">
      <div class="hero-badge">
        <div class="hero-badge-dot"></div>
        Profesyonel VoIP Yönetim Platformu
      </div>

      <h1 class="hero-title">
        Çağrı Merkezinizi<br>
        <span class="gradient-text">Tam Kontrol Altına</span><br>
        Alın
      </h1>

      <p class="hero-sub">
        Gerçek zamanlı çağrı izleme, otomatik faturalandırma ve gelişmiş raporlama ile
        çağrı merkezinizi bir üst seviyeye taşıyın.
      </p>

      <div class="hero-actions">
        <a href="<?= \App\Helpers\Url::to('/register') ?>" class="btn-primary">
          <i class="fa-solid fa-rocket"></i>
          Hemen Başla — Ücretsiz
          <i class="fa-solid fa-arrow-right"></i>
        </a>
        <a href="<?= \App\Helpers\Url::to('/login') ?>" class="btn-ghost">
          <i class="fa-solid fa-right-to-bracket"></i>
          Giriş Yap
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat">
          <div class="hero-stat-num" data-count="99.9">0</div>
          <div class="hero-stat-label">% Uptime Garantisi</div>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <div class="hero-stat-num" data-count="500" data-suffix="K+">0</div>
          <div class="hero-stat-label">Günlük İşlenen Çağrı</div>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <div class="hero-stat-num" data-count="150" data-suffix="+">0</div>
          <div class="hero-stat-label">Aktif Müşteri</div>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <div class="hero-stat-num" data-count="24" data-suffix="/7">0</div>
          <div class="hero-stat-label">Teknik Destek</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── DASHBOARD PREVIEW ───────────────────────────────────────────── -->
<div class="container preview-section">
  <div class="preview-glow"></div>
  <div class="preview-wrapper reveal">
    <div class="preview-bar">
      <div class="preview-dot" style="background:#ef4444"></div>
      <div class="preview-dot" style="background:#f59e0b"></div>
      <div class="preview-dot" style="background:#22c55e"></div>
      <div class="preview-url">crm.yourcompany.com/VoipPanelAi</div>
    </div>
    <div class="preview-body">
      <div class="preview-sidebar">
        <div style="font-size:10px;font-weight:700;color:#475569;margin-bottom:8px;padding:0 4px">MENÜ</div>
        <div class="preview-nav-item active"><i class="fa-solid fa-grid-2"></i> Dashboard</div>
        <div class="preview-nav-item"><i class="fa-solid fa-phone"></i> Çağrılar</div>
        <div class="preview-nav-item"><i class="fa-solid fa-headset"></i> Agentler</div>
        <div class="preview-nav-item"><i class="fa-solid fa-users"></i> Kullanıcılar</div>
        <div class="preview-nav-item"><i class="fa-solid fa-layer-group"></i> Gruplar</div>
        <div class="preview-nav-item"><i class="fa-solid fa-chart-bar"></i> Raporlar</div>
        <div class="preview-nav-item"><i class="fa-solid fa-wallet"></i> Bakiye</div>
        <div class="preview-nav-item"><i class="fa-solid fa-cog"></i> Ayarlar</div>
      </div>
      <div class="preview-main">
        <div class="preview-cards">
          <div class="preview-card" style="background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(99,102,241,.1));border:1px solid rgba(99,102,241,.3)">
            <div class="preview-card-num">$4,821</div>
            <div class="preview-card-label">API Bakiye</div>
          </div>
          <div class="preview-card" style="background:linear-gradient(135deg,rgba(16,185,129,.3),rgba(16,185,129,.1));border:1px solid rgba(16,185,129,.3)">
            <div class="preview-card-num">1,284</div>
            <div class="preview-card-label">Bugün Çağrı</div>
          </div>
          <div class="preview-card" style="background:linear-gradient(135deg,rgba(245,158,11,.3),rgba(245,158,11,.1));border:1px solid rgba(245,158,11,.3)">
            <div class="preview-card-num">$892</div>
            <div class="preview-card-label">Haftalık Kâr</div>
          </div>
          <div class="preview-card" style="background:linear-gradient(135deg,rgba(139,92,246,.3),rgba(139,92,246,.1));border:1px solid rgba(139,92,246,.3)">
            <div class="preview-card-num">47</div>
            <div class="preview-card-label">Aktif Agent</div>
          </div>
        </div>
        <div class="preview-chart">
          <?php
          $heights = [40, 55, 45, 70, 60, 85, 65, 90, 75, 95, 80, 70, 88, 100];
          foreach ($heights as $i => $h):
          ?>
          <div class="preview-bar-item" style="height:<?= $h ?>%;animation-delay:<?= $i * 0.06 ?>s"></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── FEATURES ───────────────────────────────────────────────────── -->
<section class="section" id="features">
  <div class="container">
    <div class="reveal">
      <div class="section-label"><i class="fa-solid fa-sparkles"></i> Özellikler</div>
      <h2 class="section-title">Her şey tek platformda,<br><span class="gradient-text">eksiksiz kontrol</span></h2>
      <p class="section-sub">Çağrı merkezinizi yönetmek için ihtiyaç duyduğunuz tüm araçlar, sezgisel bir arayüzde.</p>
    </div>

    <div class="features-grid">
      <?php
      $features = [
        ['bg'=>'linear-gradient(135deg,#6366f1,#8b5cf6)', 'icon'=>'fa-chart-line',      'title'=>'Gerçek Zamanlı Analitik',     'desc'=>'Çağrı istatistiklerini anlık olarak izleyin. Cevap oranları, konuşma süreleri ve maliyet analizlerini dakika dakika takip edin.'],
        ['bg'=>'linear-gradient(135deg,#10b981,#06b6d4)', 'icon'=>'fa-layer-group',      'title'=>'Çoklu Grup Yönetimi',         'desc'=>'Sınırsız grup oluşturun, her gruba bağımsız bakiye ve kota tanımlayın. Farklı ekipleri tek panel üzerinden yönetin.'],
        ['bg'=>'linear-gradient(135deg,#f59e0b,#ef4444)', 'icon'=>'fa-bolt',             'title'=>'Otomatik Faturalandırma',     'desc'=>'Saniye bazında faturalandırma, margin yönetimi ve otomatik bakiye uyarıları ile finansal kontrolü elinizde tutun.'],
        ['bg'=>'linear-gradient(135deg,#06b6d4,#3b82f6)', 'icon'=>'fa-headset',          'title'=>'Agent Yönetimi',              'desc'=>'Agentleri kolayca ekleyin, aktif/pasif yapın ve abonelik planları ile maliyetleri optimize edin.'],
        ['bg'=>'linear-gradient(135deg,#8b5cf6,#ec4899)', 'icon'=>'fa-bell',             'title'=>'Telegram Bildirimleri',       'desc'=>'Bakiye düştüğünde, ödeme geldiğinde veya kritik anlarda anında Telegram bildirimi alın.'],
        ['bg'=>'linear-gradient(135deg,#10b981,#22c55e)', 'icon'=>'fa-shield-halved',    'title'=>'Güvenli Ödeme Sistemi',       'desc'=>'USDT/TRC20 kripto ödeme desteği, güvenli bakiye yükleme ve tam işlem geçmişi ile paranız güvende.'],
        ['bg'=>'linear-gradient(135deg,#f59e0b,#fbbf24)', 'icon'=>'fa-table-list',       'title'=>'Detaylı CDR Raporları',       'desc'=>'Tüm çağrı kayıtlarını filtreleyin, sıralayın ve CSV olarak indirin. Tarih, agent, grup ve duruma göre detaylı sorgular.'],
        ['bg'=>'linear-gradient(135deg,#6366f1,#06b6d4)', 'icon'=>'fa-globe',            'title'=>'Çok Dilli Destek',            'desc'=>'Türkçe, İngilizce ve Rusça dil desteği. Kullanıcılarınız kendi dillerinde çalışabilir.'],
        ['bg'=>'linear-gradient(135deg,#ec4899,#f43f5e)', 'icon'=>'fa-mobile-screen',    'title'=>'Mobil Uyumlu Tasarım',        'desc'=>'Her cihazda mükemmel çalışan responsive tasarım. Telefon, tablet ve masaüstünde sorunsuz deneyim.'],
      ];
      foreach ($features as $i => $f):
        $delay = ['','reveal-delay-1','reveal-delay-2'][$i%3];
      ?>
      <div class="feature-card reveal <?= $delay ?>">
        <div class="feature-icon" style="background:<?= $f['bg'] ?>">
          <i class="fa-solid <?= $f['icon'] ?>" style="color:#fff;font-size:22px"></i>
        </div>
        <div class="feature-title"><?= $f['title'] ?></div>
        <div class="feature-desc"><?= $f['desc'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── HOW IT WORKS ───────────────────────────────────────────────── -->
<section class="section" id="how" style="background:linear-gradient(180deg,transparent,rgba(99,102,241,.04),transparent)">
  <div class="container">
    <div class="reveal" style="text-align:center">
      <div class="section-label" style="display:inline-flex"><i class="fa-solid fa-map"></i> Nasıl Çalışır</div>
      <h2 class="section-title" style="margin-top:8px">Dakikalar içinde <span class="gradient-text">hazır</span></h2>
      <p class="section-sub" style="margin:0 auto">Karmaşık kurulum gerektirmez. Hesabınızı açın ve hemen kullanmaya başlayın.</p>
    </div>

    <div class="steps-grid">
      <?php
      $steps = [
        ['Hesap Oluşturun', 'Hızlı kayıt formu ile dakikalar içinde hesabınızı açın.', 'fa-user-plus'],
        ['API Bağlantısı', 'VoIP sağlayıcınızın API bilgilerini girin, sistemi entegre edin.', 'fa-plug'],
        ['Grup & Agent Tanımı', 'Ekiplerinizi gruplar halinde organize edin, agentleri ekleyin.', 'fa-users-gear'],
        ['Yayına Geçin', 'Her şey hazır! Çağrılarınızı gerçek zamanlı izlemeye başlayın.', 'fa-rocket'],
      ];
      foreach ($steps as $i => $s):
      ?>
      <div class="step-card reveal reveal-delay-<?= $i+1 ?>">
        <div class="step-num"><?= $i+1 ?></div>
        <div style="font-size:24px;margin-bottom:12px;color:#a5b4fc"><i class="fa-solid <?= $s[2] ?>"></i></div>
        <div class="step-title"><?= $s[0] ?></div>
        <div class="step-desc"><?= $s[1] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── STATS ──────────────────────────────────────────────────────── -->
<section class="stats-section">
  <div class="container">
    <div class="stats-grid">
      <?php
      $stats = [
        ['99.9%', 'Uptime SLA', 'fa-server'],
        ['< 100ms', 'Ortalama Gecikme', 'fa-bolt'],
        ['∞', 'Eş Zamanlı Çağrı', 'fa-phone'],
        ['7/24', 'Teknik Destek', 'fa-headset'],
        ['256-bit', 'SSL Şifreleme', 'fa-shield'],
        ['GDPR', 'Uyumlu', 'fa-file-shield'],
      ];
      foreach ($stats as $i => $s):
      ?>
      <div class="stat-card reveal reveal-delay-<?= ($i%4)+1 ?>">
        <div style="font-size:28px;color:#6366f1;margin-bottom:12px"><i class="fa-solid <?= $s[2] ?>"></i></div>
        <div class="stat-num"><?= $s[0] ?></div>
        <div class="stat-label"><?= $s[1] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── TESTIMONIALS ───────────────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="reveal" style="text-align:center">
      <div class="section-label" style="display:inline-flex"><i class="fa-solid fa-star"></i> Referanslar</div>
      <h2 class="section-title" style="margin-top:8px">Müşterilerimiz <span class="gradient-text">ne diyor?</span></h2>
    </div>
    <div class="testimonials-grid">
      <?php
      $testimonials = [
        ['⭐⭐⭐⭐⭐', 'Panel sayesinde çağrı maliyetlerimizi %30 düşürdük. Gerçek zamanlı raporlama müthiş, her şeyi anlık görüyoruz.', 'A', 'Ahmet Y.', 'Çağrı Merkezi Müdürü'],
        ['⭐⭐⭐⭐⭐', 'Çoklu grup yönetimi özelliği harika. Farklı departmanlarımızı tek panelden yönetiyoruz, iş akışımız çok kolaylaştı.', 'M', 'Mehmet K.', 'IT Direktörü'],
        ['⭐⭐⭐⭐⭐', 'Telegram bildirimleri sayesinde bakiye bitmeden haberdar oluyoruz. Kesinti yaşamıyoruz artık.', 'F', 'Fatma Ş.', 'Operasyon Sorumlusu'],
      ];
      foreach ($testimonials as $t):
      ?>
      <div class="testimonial-card reveal">
        <div class="testimonial-stars"><?= $t[0] ?></div>
        <div class="testimonial-text">"<?= $t[1] ?>"</div>
        <div class="testimonial-author">
          <div class="testimonial-avatar"><?= $t[2] ?></div>
          <div>
            <div class="testimonial-name"><?= $t[3] ?></div>
            <div class="testimonial-role"><?= $t[4] ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── PRICING ────────────────────────────────────────────────────── -->
<section class="section" id="pricing" style="background:linear-gradient(180deg,transparent,rgba(99,102,241,.04),transparent)">
  <div class="container">
    <div class="reveal" style="text-align:center">
      <div class="section-label" style="display:inline-flex"><i class="fa-solid fa-tag"></i> Fiyatlandırma</div>
      <h2 class="section-title" style="margin-top:8px">İhtiyacınıza uygun <span class="gradient-text">plan seçin</span></h2>
      <p class="section-sub" style="margin:0 auto">Tüm planlar 14 gün ücretsiz deneme içerir. Kredi kartı gerekmez.</p>
    </div>
    <div class="pricing-grid">
      <!-- Starter -->
      <div class="pricing-card reveal reveal-delay-1">
        <div class="pricing-name">STARTER</div>
        <div class="pricing-price">$49<span>/ay</span></div>
        <div class="pricing-desc">Küçük ekipler için ideal başlangıç</div>
        <ul class="pricing-features">
          <li><i class="fa-solid fa-check"></i> 5 Agent</li>
          <li><i class="fa-solid fa-check"></i> 2 Grup</li>
          <li><i class="fa-solid fa-check"></i> CDR Raporları</li>
          <li><i class="fa-solid fa-check"></i> Email Destek</li>
          <li class="no"><i class="fa-solid fa-xmark"></i> Telegram Bildirim</li>
          <li class="no"><i class="fa-solid fa-xmark"></i> API Entegrasyon</li>
        </ul>
        <a href="<?= \App\Helpers\Url::to('/register') ?>" class="btn-pricing outline">Başla</a>
      </div>

      <!-- Pro (Popular) -->
      <div class="pricing-card popular reveal reveal-delay-2">
        <div class="popular-badge">🔥 En Popüler</div>
        <div class="pricing-name">PROFESSIONAL</div>
        <div class="pricing-price">$149<span>/ay</span></div>
        <div class="pricing-desc">Büyüyen işletmeler için tam paket</div>
        <ul class="pricing-features">
          <li><i class="fa-solid fa-check"></i> 25 Agent</li>
          <li><i class="fa-solid fa-check"></i> Sınırsız Grup</li>
          <li><i class="fa-solid fa-check"></i> Gelişmiş CDR Raporları</li>
          <li><i class="fa-solid fa-check"></i> Telegram Bildirim</li>
          <li><i class="fa-solid fa-check"></i> API Entegrasyon</li>
          <li><i class="fa-solid fa-check"></i> Kripto Ödeme</li>
        </ul>
        <a href="<?= \App\Helpers\Url::to('/register') ?>" class="btn-pricing primary">Hemen Başla</a>
      </div>

      <!-- Enterprise -->
      <div class="pricing-card reveal reveal-delay-3">
        <div class="pricing-name">ENTERPRISE</div>
        <div class="pricing-price">Özel<span> fiyat</span></div>
        <div class="pricing-desc">Kurumsal müşteriler için özel çözüm</div>
        <ul class="pricing-features">
          <li><i class="fa-solid fa-check"></i> Sınırsız Agent</li>
          <li><i class="fa-solid fa-check"></i> Sınırsız Grup</li>
          <li><i class="fa-solid fa-check"></i> White-label Seçeneği</li>
          <li><i class="fa-solid fa-check"></i> Özel Entegrasyonlar</li>
          <li><i class="fa-solid fa-check"></i> SLA Garantisi</li>
          <li><i class="fa-solid fa-check"></i> 7/24 Öncelikli Destek</li>
        </ul>
        <a href="mailto:info@example.com" class="btn-pricing outline">Bize Ulaşın</a>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ────────────────────────────────────────────────────────── -->
<section class="cta-section">
  <div class="container">
    <div class="cta-box reveal">
      <h2 class="cta-title">
        Çağrı merkezinizi bugün<br>
        <span class="gradient-text">dönüştürmeye başlayın</span>
      </h2>
      <p class="cta-sub">Dakikalar içinde kurulum yapın, anında sonuç görün. 14 gün ücretsiz deneyin.</p>
      <div style="display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;position:relative">
        <a href="<?= \App\Helpers\Url::to('/register') ?>" class="btn-primary" style="font-size:16px;padding:16px 40px">
          <i class="fa-solid fa-rocket"></i>
          Ücretsiz Hesap Aç
          <i class="fa-solid fa-arrow-right"></i>
        </a>
        <a href="<?= \App\Helpers\Url::to('/login') ?>" class="btn-ghost" style="font-size:16px;padding:16px 40px">
          <i class="fa-solid fa-right-to-bracket"></i>
          Giriş Yap
        </a>
      </div>
      <p style="margin-top:20px;font-size:13px;color:#475569;position:relative">
        <i class="fa-solid fa-lock"></i> Kredi kartı gerekmez &nbsp;·&nbsp;
        <i class="fa-solid fa-check"></i> Anında kurulum &nbsp;·&nbsp;
        <i class="fa-solid fa-shield"></i> SSL şifreli
      </p>
    </div>
  </div>
</section>

<!-- ── FOOTER ─────────────────────────────────────────────────────── -->
<footer>
  <div class="container">
    <div class="footer-inner">
      <div>
        <a href="#" class="nav-logo" style="margin-bottom:8px;display:inline-flex">
          <div class="nav-logo-icon"><i class="fa-solid fa-wave-square"></i></div>
          <span class="nav-logo-text">VoIP<span>Panel</span></span>
        </a>
        <div class="footer-copy" style="margin-top:8px">© <?= date('Y') ?> VoIPPanel. Tüm hakları saklıdır.</div>
      </div>
      <div class="footer-links">
        <a href="#features" class="footer-link">Özellikler</a>
        <a href="#pricing" class="footer-link">Fiyatlar</a>
        <a href="<?= \App\Helpers\Url::to('/login') ?>" class="footer-link">Giriş</a>
        <a href="<?= \App\Helpers\Url::to('/register') ?>" class="footer-link">Kayıt</a>
      </div>
    </div>
  </div>
</footer>

<script>
// ── Navbar scroll effect ────────────────────────────────────────────
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });

// ── Reveal on scroll ────────────────────────────────────────────────
const revealEls = document.querySelectorAll('.reveal');
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
revealEls.forEach(el => observer.observe(el));

// ── Counter animation ───────────────────────────────────────────────
function animateCounters() {
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseFloat(el.dataset.count);
    const suffix = el.dataset.suffix || '';
    const isDecimal = target % 1 !== 0;
    let start = 0;
    const duration = 2000;
    const step = 16;
    const increment = target / (duration / step);
    const timer = setInterval(() => {
      start = Math.min(start + increment, target);
      el.textContent = (isDecimal ? start.toFixed(1) : Math.floor(start)) + suffix;
      if (start >= target) clearInterval(timer);
    }, step);
  });
}

// Trigger counters when hero is visible
const heroObs = new IntersectionObserver((entries) => {
  if (entries[0].isIntersecting) { animateCounters(); heroObs.disconnect(); }
}, { threshold: 0.3 });
const heroStats = document.querySelector('.hero-stats');
if (heroStats) heroObs.observe(heroStats);

// ── Smooth anchor scroll ────────────────────────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  });
});

// ── Parallax orbs (subtle) ──────────────────────────────────────────
document.addEventListener('mousemove', e => {
  const x = (e.clientX / window.innerWidth - 0.5) * 20;
  const y = (e.clientY / window.innerHeight - 0.5) * 20;
  document.querySelectorAll('.orb').forEach((orb, i) => {
    const factor = (i + 1) * 0.3;
    orb.style.transform = `translate(${x * factor}px, ${y * factor}px)`;
  });
}, { passive: true });
</script>

</body>
</html>