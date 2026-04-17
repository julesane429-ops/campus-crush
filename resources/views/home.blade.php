<!doctype html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@include('components.pwa-meta')
<title>Campus Crush — Rencontres Universitaires au Sénégal</title>
<meta name="description" content="L'appli de rencontres exclusivement conçue pour les étudiants sénégalais. Swipe, match et discute avec des étudiants de ton campus.">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&family=Playfair+Display:ital,wght@1,900&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lenis@1.1.14/dist/lenis.min.js"></script>

<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  :root {
    --cc-rose: #ff5e6c;
    --cc-orange: #ff8a5c;
    --cc-yellow: #ffc145;
    --cc-pink: #ff2d6b;
    --cc-violet: #a855f7;
    --cc-dark: #0c0a1a;
    --cc-dark-2: #1a1145;
    --cc-green: #2d9d57;
    --bg-tint-1: rgba(255, 94, 108, 0.08);
    --bg-tint-2: rgba(168, 85, 247, 0.05);
  }

  html, body {
    font-family: 'Sora', sans-serif;
    background: var(--cc-dark);
    color: #fff;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
  }

  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(ellipse at 20% 20%, var(--bg-tint-1) 0%, transparent 50%),
      radial-gradient(ellipse at 80% 80%, var(--bg-tint-2) 0%, transparent 50%);
    z-index: 0;
    pointer-events: none;
    transition: background 1.5s ease;
  }

  body.tint-rose { --bg-tint-1: rgba(255, 94, 108, 0.15); --bg-tint-2: rgba(255, 138, 92, 0.08); }
  body.tint-violet { --bg-tint-1: rgba(168, 85, 247, 0.12); --bg-tint-2: rgba(255, 45, 107, 0.08); }
  body.tint-yellow { --bg-tint-1: rgba(255, 193, 69, 0.1); --bg-tint-2: rgba(255, 138, 92, 0.08); }
  body.tint-green { --bg-tint-1: rgba(45, 157, 87, 0.08); --bg-tint-2: rgba(255, 193, 69, 0.08); }

  html.lenis, html.lenis body { height: auto; }
  .lenis.lenis-smooth { scroll-behavior: auto !important; }

  /* ==== LOADER ==== */
  .loader {
    position: fixed; inset: 0;
    background: var(--cc-dark);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 2rem;
    transition: opacity 0.5s;
  }
  .loader.hidden { opacity: 0; pointer-events: none; }
  .loader-logo {
    width: 72px; height: 72px;
    border-radius: 18px;
    background: linear-gradient(135deg, var(--cc-rose), var(--cc-orange), var(--cc-yellow));
    display: flex;
    align-items: center;
    justify-content: center;
    animation: loaderPulse 1.2s ease-in-out infinite;
  }
  @keyframes loaderPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
  }

  /* ==== CANVAS HEARTS ==== */
  #hearts-canvas {
    position: fixed;
    inset: 0;
    z-index: 1;
    pointer-events: none;
  }
  .layer { position: relative; z-index: 2; }

  /* ==== NAV ==== */
  .nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    padding: 1.25rem 2rem;
    z-index: 100;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.3s, backdrop-filter 0.3s;
  }
  .nav.scrolled {
    background: rgba(12, 10, 26, 0.75);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255,255,255,0.05);
  }
  .nav-logo {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-weight: 800;
    font-size: 1.1rem;
    text-decoration: none;
  }
  .nav-logo-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--cc-rose), var(--cc-yellow));
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .gradient-text {
    background: linear-gradient(135deg, var(--cc-rose), var(--cc-orange), var(--cc-yellow));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    background-size: 200% 200%;
    animation: shimmer 3s ease-in-out infinite;
  }
  @keyframes shimmer {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
  }
  .italic-accent {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-weight: 900;
  }
  .nav-actions { display: flex; gap: 0.75rem; align-items: center; }
  .nav-link {
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.3s;
  }
  .nav-link:hover { color: #fff; }
  .nav-install {
    display: none;
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
    border-radius: 100px;
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.3s;
  }
  .nav-install:hover { background: rgba(255,255,255,0.05); color: white; }
  @media (min-width: 768px) { .nav-install { display: inline-flex; align-items: center; gap: 0.4rem; } }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 100px;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
    border: none;
    font-family: inherit;
  }
  .btn-primary {
    background: linear-gradient(135deg, var(--cc-rose), var(--cc-orange));
    color: white;
    box-shadow: 0 10px 30px rgba(255, 94, 108, 0.3);
  }
  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 40px rgba(255, 94, 108, 0.5);
  }
  .btn-ghost {
    background: rgba(255,255,255,0.04);
    color: white;
    border: 1px solid rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
  }
  .btn-ghost:hover {
    background: rgba(255,255,255,0.08);
    border-color: rgba(255,255,255,0.2);
  }

  /* ==== HERO ==== */
  .hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 6rem 2rem 3rem;
  }
  .hero-container {
    max-width: 1400px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 4rem;
    align-items: center;
    width: 100%;
  }
  @media (max-width: 968px) {
    .hero-container { grid-template-columns: 1fr; text-align: center; }
  }
  .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 100px;
    font-family: 'Space Mono', monospace;
    font-size: 0.7rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.6);
    margin-bottom: 1.5rem;
  }
  .hero-badge::before {
    content: '';
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #2ecc71;
    animation: pulse 2s ease-in-out infinite;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7); }
    50% { opacity: 0.7; box-shadow: 0 0 0 10px rgba(46, 204, 113, 0); }
  }
  .hero-title {
    font-size: clamp(2.8rem, 7vw, 5.5rem);
    font-weight: 900;
    line-height: 0.95;
    letter-spacing: -0.03em;
    margin-bottom: 1.5rem;
  }
  .hero-title .word {
    display: inline-block;
    overflow: hidden;
    padding-bottom: 0.12em;
    vertical-align: bottom;
  }
  .hero-title .word span {
    display: inline-block;
    transform: translateY(110%);
  }
  .hero-desc {
    font-size: 1.15rem;
    color: rgba(255,255,255,0.55);
    max-width: 500px;
    margin-bottom: 2.5rem;
    line-height: 1.6;
  }
  @media (max-width: 968px) {
    .hero-desc { margin-left: auto; margin-right: auto; }
  }
  .hero-cta {
    display: flex;
    gap: 1rem;
    margin-bottom: 3rem;
    flex-wrap: wrap;
  }
  @media (max-width: 968px) {
    .hero-cta { justify-content: center; }
  }
  .hero-social {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
  }
  @media (max-width: 968px) {
    .hero-social { justify-content: center; }
  }
  .avatar-stack { display: flex; }
  .avatar-stack > div {
    width: 40px; height: 40px;
    border-radius: 50%;
    border: 2.5px solid var(--cc-dark);
    margin-left: -10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8rem;
    color: white;
  }
  .avatar-stack > div:first-child { margin-left: 0; }
  .avatar-stack > div:nth-child(1) { background: linear-gradient(135deg, #ff5e6c, #ff8a5c); }
  .avatar-stack > div:nth-child(2) { background: linear-gradient(135deg, #a855f7, #ff5e6c); }
  .avatar-stack > div:nth-child(3) { background: linear-gradient(135deg, #ffc145, #ff8a5c); }
  .avatar-stack > div:nth-child(4) { background: linear-gradient(135deg, #2d9d57, #ffc145); }
  .avatar-stack > div:nth-child(5) { background: linear-gradient(135deg, #ff2d6b, #a855f7); }
  .social-text {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.8);
    font-weight: 600;
  }
  .social-stars { display: flex; gap: 2px; margin-top: 3px; }
  .social-stars span { color: var(--cc-yellow); font-size: 0.8rem; }

  /* Badges hero */
  .hero-badges {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
  }
  @media (max-width: 968px) {
    .hero-badges { justify-content: center; }
  }
  .hero-badges span {
    font-size: 0.7rem;
    padding: 0.35rem 0.85rem;
    border-radius: 100px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.6);
  }

  /* ==== PHONE 3D ==== */
  .phone-stage {
    position: relative;
    perspective: 1400px;
    height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .phone-wrap {
    position: relative;
    width: 320px;
    height: 560px;
    transform-style: preserve-3d;
    transition: transform 0.2s ease-out;
  }
  .phone-card {
    position: absolute;
    top: 50%; left: 50%;
    width: 280px;
    height: 520px;
    border-radius: 40px;
    overflow: hidden;
    transform-style: preserve-3d;
  }
  .phone-card.main {
    transform: translate(-50%, -50%);
    z-index: 3;
    box-shadow: 0 30px 80px rgba(255, 94, 108, 0.4),
                0 15px 40px rgba(0, 0, 0, 0.5);
  }
  .phone-card.back-left {
    transform: translate(-50%, -50%) translateX(-85px) translateZ(-80px) rotate(-12deg) scale(0.9);
    z-index: 1;
    opacity: 0.55;
    filter: blur(2px);
    box-shadow: 0 20px 50px rgba(168, 85, 247, 0.3);
  }
  .phone-card.back-right {
    transform: translate(-50%, -50%) translateX(75px) translateZ(-40px) rotate(8deg) scale(0.93);
    z-index: 2;
    opacity: 0.75;
    filter: blur(1px);
    box-shadow: 0 20px 50px rgba(255, 193, 69, 0.3);
  }
  .phone-screen {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1a1145 0%, #0f1a3a 100%);
    position: relative;
  }
  .profile-photo {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 65%;
    background: linear-gradient(135deg, #ff8a5c 0%, #ff5e6c 40%, #a855f7 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8rem;
  }
  .profile-info {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 1.5rem;
    background: linear-gradient(to top, rgba(12, 10, 26, 0.98) 50%, transparent);
  }
  .profile-name { font-size: 1.4rem; font-weight: 800; margin-bottom: 0.3rem; }
  .profile-meta { font-size: 0.75rem; color: rgba(255,255,255,0.6); margin-bottom: 0.75rem; }
  .profile-tags { display: flex; gap: 0.4rem; flex-wrap: wrap; }
  .profile-tag {
    font-size: 0.65rem;
    padding: 0.25rem 0.6rem;
    border-radius: 100px;
    font-weight: 600;
  }
  .tag-rose { background: rgba(255, 94, 108, 0.2); color: #ff9aa3; }
  .tag-violet { background: rgba(168, 85, 247, 0.2); color: #c084fc; }
  .tag-yellow { background: rgba(255, 193, 69, 0.2); color: #fcd34d; }
  .like-stamp {
    position: absolute;
    top: 30px;
    right: 20px;
    transform: rotate(12deg) scale(0.5);
    border: 3px solid #4ade80;
    color: #4ade80;
    padding: 0.4rem 1rem;
    border-radius: 8px;
    font-weight: 900;
    font-size: 1.1rem;
    letter-spacing: 2px;
    z-index: 3;
    opacity: 0;
    transition: opacity 0.2s, transform 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
  }
  .like-stamp.show { opacity: 1; transform: rotate(12deg) scale(1); }
  .phone-actions {
    position: absolute;
    bottom: -40px;
    left: 0; right: 0;
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    z-index: 4;
  }
  .phone-action {
    width: 54px; height: 54px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    cursor: pointer;
    transition: transform 0.3s;
    backdrop-filter: blur(20px);
  }
  .action-pass {
    background: rgba(239, 68, 68, 0.15);
    border: 1.5px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
  }
  .action-like {
    background: linear-gradient(135deg, var(--cc-rose), var(--cc-orange));
    box-shadow: 0 10px 30px rgba(255, 94, 108, 0.5);
    color: white;
  }
  .phone-action:hover { transform: scale(1.15); }
  .senegal-flag {
    position: absolute;
    top: 8%;
    left: -35px;
    width: 56px; height: 38px;
    background: linear-gradient(to right,
      #00853F 0%, #00853F 33.33%,
      #FDEF42 33.33%, #FDEF42 66.66%,
      #E31B23 66.66%, #E31B23 100%);
    border-radius: 4px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    z-index: 5;
    transform: rotate(-10deg);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .senegal-flag::after { content: '★'; color: #00853F; font-size: 1rem; }

  /* ==== RÉVÉLATION TEXTE ==== */
  .reveal-section {
    padding: 15vh 2rem;
    min-height: 100vh;
    display: flex;
    align-items: center;
  }
  .reveal-text {
    max-width: 1100px;
    margin: 0 auto;
    font-size: clamp(1.8rem, 4.5vw, 3.8rem);
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: -0.02em;
  }
  .reveal-text .word-reveal {
    display: inline-block;
    opacity: 0.15;
    margin-right: 0.2em;
    transition: opacity 0.2s;
    will-change: opacity;
  }
  .reveal-text .word-reveal.accent {
    background: linear-gradient(135deg, var(--cc-rose), var(--cc-orange), var(--cc-yellow));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-weight: 900;
  }

  /* ==== HORIZONTAL SCROLL ==== */
  .horizontal-section {
    height: 100vh;
    overflow: hidden;
  }
  .horizontal-track {
    display: flex;
    height: 100%;
    align-items: center;
    padding: 0 5vw;
    gap: 2.5rem;
    will-change: transform;
  }
  .h-title-panel {
    flex-shrink: 0;
    width: 45vw;
    min-width: 320px;
    padding-right: 2rem;
  }
  .h-title-panel .eyebrow {
    font-family: 'Space Mono', monospace;
    font-size: 0.75rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--cc-rose);
    margin-bottom: 1rem;
  }
  .h-title-panel h2 {
    font-size: clamp(2.5rem, 6vw, 5rem);
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.03em;
    margin-bottom: 1.5rem;
  }
  .h-title-panel p {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.5);
    max-width: 500px;
    line-height: 1.6;
  }
  .step-card {
    flex-shrink: 0;
    width: 380px;
    height: 480px;
    border-radius: 32px;
    padding: 3rem 2.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    border: 1px solid rgba(255,255,255,0.08);
  }
  .step-card.c-1 { background: linear-gradient(135deg, rgba(255, 94, 108, 0.18), rgba(255, 138, 92, 0.08)); }
  .step-card.c-2 { background: linear-gradient(135deg, rgba(168, 85, 247, 0.18), rgba(255, 94, 108, 0.08)); }
  .step-card.c-3 { background: linear-gradient(135deg, rgba(255, 193, 69, 0.18), rgba(255, 138, 92, 0.08)); }
  .step-number {
    font-family: 'Space Mono', monospace;
    font-size: 0.75rem;
    letter-spacing: 0.3em;
    color: rgba(255,255,255,0.4);
  }
  .step-emoji { font-size: 4rem; }
  .step-content h3 {
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 0.75rem;
    letter-spacing: -0.02em;
  }
  .step-content p {
    color: rgba(255,255,255,0.6);
    font-size: 1rem;
    line-height: 1.5;
  }

  @media (max-width: 640px) {
    .horizontal-track { padding: 0 1.5rem; gap: 1.5rem; }
    .h-title-panel { width: 80vw; min-width: 260px; padding-right: 1rem; }
    .step-card {
      width: 280px;
      height: 400px;
      padding: 2rem 1.75rem;
      border-radius: 24px;
    }
    .step-emoji { font-size: 3rem; }
    .step-content h3 { font-size: 1.4rem; }
    .step-content p { font-size: 0.9rem; }
  }

  /* ==== UNFOLD (cartes qui se déplient) ==== */
  .unfold-section {
    position: relative;
    height: 400vh;
  }
  .unfold-sticky {
    position: sticky;
    top: 0;
    height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 8vh 2rem 4vh;
    overflow: hidden;
    gap: 4vh;
  }
  .unfold-heading {
    text-align: center;
    flex-shrink: 0;
    padding: 0 1rem;
  }
  .unfold-heading .eyebrow {
    font-family: 'Space Mono', monospace;
    font-size: 0.75rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--cc-yellow);
    margin-bottom: 0.75rem;
  }
  .unfold-heading h2 {
    font-size: clamp(1.8rem, 5vw, 4rem);
    font-weight: 900;
    letter-spacing: -0.03em;
    line-height: 1.1;
  }
  .unfold-stage {
    position: relative;
    width: 300px;
    height: 460px;
    flex-shrink: 0;
  }
  .unfold-card {
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.4);
    background: #1a1145;
    will-change: transform, opacity;
  }
  .unfold-card-photo {
    height: 60%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 5.5rem;
  }
  .unfold-card-info { padding: 1.5rem; color: white; }
  .unfold-card-info h4 { font-size: 1.2rem; font-weight: 800; margin-bottom: 0.3rem; }
  .unfold-card-info .meta {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 0.5rem;
  }
  .unfold-card-info .tags { display: flex; gap: 0.3rem; flex-wrap: wrap; }
  .unfold-card-info .tags span {
    font-size: 0.65rem;
    padding: 0.2rem 0.5rem;
    border-radius: 100px;
    font-weight: 600;
    background: rgba(255,255,255,0.1);
  }

  @media (max-width: 640px) {
    .unfold-sticky { padding: 6vh 1rem 3vh; gap: 3vh; }
    .unfold-stage { width: 260px; height: 400px; }
    .unfold-card { border-radius: 24px; }
    .unfold-card-photo { font-size: 4.5rem; }
    .unfold-card-info { padding: 1rem 1.2rem; }
    .unfold-card-info h4 { font-size: 1.05rem; }
  }
  @media (max-width: 380px) {
    .unfold-stage { width: 230px; height: 360px; }
  }

  /* ==== STATS ==== */
  .stats-section { padding: 12vh 2rem; }
  .stats-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 3rem;
  }
  @media (max-width: 768px) {
    .stats-container { grid-template-columns: 1fr; gap: 2rem; }
  }
  .stat-block { text-align: center; }
  .stat-number {
    font-family: 'Space Mono', monospace;
    font-size: clamp(3rem, 7vw, 5rem);
    font-weight: 700;
    background: linear-gradient(135deg, var(--cc-rose), var(--cc-yellow));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    line-height: 1;
    margin-bottom: 0.5rem;
  }
  .stat-label {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: 0.2em;
    font-weight: 500;
  }

  /* ==== MARQUEE ==== */
  .marquee-section {
    padding: 4rem 0;
    border-top: 1px solid rgba(255,255,255,0.05);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    overflow: hidden;
  }
  .marquee-label {
    text-align: center;
    font-family: 'Space Mono', monospace;
    font-size: 0.75rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    margin-bottom: 2rem;
    padding: 0 1rem;
  }
  .marquee-track {
    display: flex;
    gap: 2rem;
    white-space: nowrap;
    animation: marquee 40s linear infinite;
    width: max-content;
  }
  @keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
  }
  .marquee-item {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem 1.5rem;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 100px;
    font-size: 1rem;
    font-weight: 500;
    color: rgba(255,255,255,0.7);
    flex-shrink: 0;
  }
  .marquee-item .dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--cc-rose);
  }
  .marquee-item .city-dim { color: rgba(255,255,255,0.35); }

  /* ==== TESTIMONIALS ==== */
  .testimonials { padding: 12vh 2rem; }
  .testimonials-header {
    text-align: center;
    margin-bottom: 4rem;
  }
  .testimonials-header .eyebrow {
    font-family: 'Space Mono', monospace;
    font-size: 0.75rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--cc-rose);
    margin-bottom: 1rem;
  }
  .testimonials-header h2 {
    font-size: clamp(2.2rem, 5vw, 4rem);
    font-weight: 900;
    letter-spacing: -0.03em;
  }
  .testimonials-grid {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
  }
  @media (max-width: 968px) {
    .testimonials-grid { grid-template-columns: 1fr; }
  }
  .testimonial-card {
    padding: 2rem;
    border-radius: 24px;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    backdrop-filter: blur(10px);
    transition: transform 0.4s, border-color 0.4s;
  }
  .testimonial-card:hover {
    transform: translateY(-6px);
    border-color: rgba(255, 94, 108, 0.3);
  }
  .testimonial-stars { display: flex; gap: 2px; margin-bottom: 1rem; }
  .testimonial-stars span { font-size: 0.9rem; }
  .testimonial-stars .filled { color: var(--cc-yellow); }
  .testimonial-stars .empty { color: rgba(255,255,255,0.1); }
  .testimonial-text {
    font-size: 1rem;
    color: rgba(255,255,255,0.75);
    line-height: 1.6;
    margin-bottom: 1.5rem;
    font-style: italic;
  }
  .testimonial-author {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  .testimonial-avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    font-size: 0.9rem;
    background-size: cover;
    background-position: center;
    flex-shrink: 0;
  }
  .avatar-1 { background: linear-gradient(135deg, var(--cc-rose), var(--cc-orange)); }
  .avatar-2 { background: linear-gradient(135deg, var(--cc-violet), var(--cc-rose)); }
  .avatar-3 { background: linear-gradient(135deg, var(--cc-yellow), var(--cc-orange)); }
  .testimonial-name { font-size: 0.9rem; font-weight: 700; }
  .testimonial-uni { font-size: 0.75rem; color: rgba(255,255,255,0.4); }

  /* ==== CTA ==== */
  .cta-section { padding: 10vh 2rem 12vh; }
  .cta-box {
    max-width: 900px;
    margin: 0 auto;
    padding: 5rem 3rem;
    border-radius: 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg,
      rgba(255, 94, 108, 0.18),
      rgba(168, 85, 247, 0.12),
      rgba(255, 193, 69, 0.1));
    border: 1px solid rgba(255, 94, 108, 0.2);
  }
  .cta-box::before, .cta-box::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.4;
  }
  .cta-box::before {
    width: 300px; height: 300px;
    background: var(--cc-rose);
    top: -100px; right: -100px;
  }
  .cta-box::after {
    width: 250px; height: 250px;
    background: var(--cc-violet);
    bottom: -80px; left: -80px;
  }
  .cta-content { position: relative; z-index: 1; }
  .cta-heart {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    animation: floatHeart 3s ease-in-out infinite;
  }
  @keyframes floatHeart {
    0%, 100% { transform: translateY(0) rotate(-5deg); }
    50% { transform: translateY(-15px) rotate(5deg); }
  }
  .cta-title {
    font-size: clamp(2.2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 1rem;
    letter-spacing: -0.03em;
  }
  .cta-sub {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 0.5rem;
  }
  .cta-micro {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.3);
    margin-bottom: 2.5rem;
  }

  /* ==== FOOTER ==== */
  footer {
    padding: 3rem 2rem;
    border-top: 1px solid rgba(255,255,255,0.05);
  }
  .footer-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
  }
  .footer-links { display: flex; gap: 2rem; }
  .footer-links a {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.4);
    text-decoration: none;
    transition: color 0.3s;
  }
  .footer-links a:hover { color: white; }
  .footer-credit {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.3);
  }

  /* ==== PROGRESS BAR ==== */
  .progress-bar {
    position: fixed;
    top: 0; left: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--cc-rose), var(--cc-yellow));
    width: 0%;
    z-index: 200;
    transform-origin: left;
  }

  /* ==== SECTION INDICATOR ==== */
  .section-indicator {
    position: fixed;
    right: 2rem;
    top: 50%;
    transform: translateY(-50%);
    z-index: 50;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.75rem 0.5rem;
    background: rgba(12, 10, 26, 0.35);
    backdrop-filter: blur(10px);
    border-radius: 100px;
    border: 1px solid rgba(255,255,255,0.05);
  }
  @media (max-width: 768px) {
    .section-indicator {
      right: 0.75rem;
      gap: 0.5rem;
      padding: 0.6rem 0.4rem;
    }
  }
  .indicator-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    transition: all 0.4s;
    cursor: pointer;
    border: none;
    padding: 0;
  }
  .indicator-dot.active {
    background: var(--cc-rose);
    transform: scale(1.5);
    box-shadow: 0 0 15px var(--cc-rose);
  }
  @media (max-width: 768px) {
    .indicator-dot { width: 6px; height: 6px; }
    .indicator-dot.active { transform: scale(1.6); box-shadow: 0 0 10px var(--cc-rose); }
  }
</style>
</head>
<body>

{{-- LOADER --}}
<div class="loader" id="loader">
  <div class="loader-logo">
    <svg width="40" height="40" viewBox="0 0 24 24" fill="white">
      <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
    </svg>
  </div>
</div>

{{-- CANVAS PARTICULES COEURS --}}
<canvas id="hearts-canvas"></canvas>

{{-- PROGRESS BAR --}}
<div class="progress-bar" id="progress-bar"></div>

{{-- SECTION INDICATOR --}}
<div class="section-indicator">
  <button class="indicator-dot active" data-target="hero" aria-label="Hero"></button>
  <button class="indicator-dot" data-target="reveal" aria-label="Présentation"></button>
  <button class="indicator-dot" data-target="horizontal" aria-label="Comment ça marche"></button>
  <button class="indicator-dot" data-target="unfold" aria-label="Profils"></button>
  <button class="indicator-dot" data-target="stats" aria-label="Chiffres"></button>
  @if(isset($featuredReviews) && $featuredReviews->count() > 0)
  <button class="indicator-dot" data-target="testimonials" aria-label="Témoignages"></button>
  @endif
  <button class="indicator-dot" data-target="cta" aria-label="Inscription"></button>
</div>

{{-- NAV --}}
<nav class="nav" id="nav">
  <a href="{{ url('/') }}" class="nav-logo">
    <div class="nav-logo-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
      </svg>
    </div>
    <span class="gradient-text">Campus Crush</span>
  </a>
  <div class="nav-actions">
    <a href="{{ route('login') }}" class="nav-link">Connexion</a>
    <a href="{{ route('register') }}" class="btn btn-primary">S'inscrire</a>
    <a href="/install" class="nav-install">📲 Installer</a>
  </div>
</nav>

<div class="layer">

{{-- ════════════════════════════════════════════
     HERO
════════════════════════════════════════════ --}}
<section class="hero" id="hero">
  <div class="hero-container">
    <div class="hero-text">
      <div class="hero-badge fade-el">🇸🇳 Made for Sénégal</div>
      <h1 class="hero-title">
        <div class="word"><span>Trouve</span></div>
        <div class="word"><span>ton</span></div>
        <div class="word"><span class="gradient-text">crush</span></div>
        <br>
        <div class="word"><span>sur le</span></div>
        <div class="word"><span class="italic-accent gradient-text">campus</span></div>
      </h1>
      <p class="hero-desc fade-el">
        L'appli de rencontres exclusivement conçue pour les étudiants sénégalais. Swipe, match, discute — et trouve l'amour entre deux cours.
      </p>
      <div class="hero-cta fade-el">
        <a href="{{ route('register') }}" class="btn btn-primary">Commencer gratuitement →</a>
        <a href="{{ route('login') }}" class="btn btn-ghost">Se connecter</a>
      </div>
      <div class="hero-social fade-el">
        <div class="avatar-stack">
          <div>AB</div><div>KD</div><div>FL</div><div>MS</div><div>AD</div>
        </div>
        <div>
          <div class="social-text">{{ number_format($stats['users'] ?? 0, 0, ',', ' ') }} étudiants inscrits</div>
          <div class="social-stars">
            <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
          </div>
        </div>
      </div>
      <div class="hero-badges fade-el">
        <span>✓ Gratuit</span>
        <span>✓ Sécurisé</span>
        <span>✓ Privé</span>
      </div>
    </div>

    <div class="phone-stage fade-el" id="phone-stage">
      <div class="phone-wrap" id="phone-wrap">
        <div class="phone-card back-left">
          <div class="phone-screen">
            <div class="profile-photo" style="background: linear-gradient(135deg, #a855f7, #ff2d6b);">👩🏾‍🎓</div>
            <div class="profile-info">
              <div class="profile-name">Fatou, 22</div>
              <div class="profile-meta">📍 UGB · Droit</div>
            </div>
          </div>
        </div>
        <div class="phone-card back-right">
          <div class="phone-screen">
            <div class="profile-photo" style="background: linear-gradient(135deg, #ffc145, #ff5e6c);">👨🏿‍🎓</div>
            <div class="profile-info">
              <div class="profile-name">Mamadou, 23</div>
              <div class="profile-meta">📍 UCAD · Médecine</div>
            </div>
          </div>
        </div>
        <div class="phone-card main" id="main-card">
          <div class="phone-screen">
            <div class="like-stamp" id="like-stamp">LIKE</div>
            <div class="profile-photo">👩🏾</div>
            <div class="profile-info">
              <div class="profile-name">Aïssatou, 21</div>
              <div class="profile-meta">📍 UCAD · Lettres modernes</div>
              <div class="profile-tags">
                <span class="profile-tag tag-rose">Musique</span>
                <span class="profile-tag tag-violet">Voyage</span>
                <span class="profile-tag tag-yellow">Photo</span>
              </div>
            </div>
          </div>
          <div class="phone-actions">
            <div class="phone-action action-pass">✕</div>
            <div class="phone-action action-like">♥</div>
          </div>
        </div>
        <div class="senegal-flag"></div>
      </div>
    </div>
  </div>
</section>

{{-- ════════════════════════════════════════════
     RÉVÉLATION TEXTE
════════════════════════════════════════════ --}}
<section class="reveal-section" id="reveal">
  <p class="reveal-text" id="reveal-text">
    Plus de {{ number_format($stats['users'] ?? 0, 0, ',', ' ') }} étudiants sénégalais utilisent Campus Crush pour <mark>trouver l'amour</mark>, se faire des amis, ou juste partager un café entre deux cours. Rejoins la communauté qui swipe depuis Dakar jusqu'à Saint-Louis.
  </p>
</section>

{{-- ════════════════════════════════════════════
     SCROLL HORIZONTAL — COMMENT ÇA MARCHE
════════════════════════════════════════════ --}}
<section class="horizontal-section" id="horizontal">
  <div class="horizontal-track" id="horizontal-track">
    <div class="h-title-panel">
      <div class="eyebrow">Comment ça marche</div>
      <h2>Trois étapes,<br>et c'est <span class="gradient-text italic-accent">parti</span>.</h2>
      <p>On a simplifié au maximum. Pas de questionnaire de 20 minutes, juste toi, tes vibes et ton campus.</p>
    </div>

    <div class="step-card c-1">
      <div class="step-number">01 / CRÉER</div>
      <div class="step-emoji">📸</div>
      <div class="step-content">
        <h3>Crée ton profil</h3>
        <p>Photo, filière, bio et tes passions. Deux minutes chrono, on te jure.</p>
      </div>
    </div>

    <div class="step-card c-2">
      <div class="step-number">02 / SWIPE</div>
      <div class="step-emoji">🔥</div>
      <div class="step-content">
        <h3>Swipe & like</h3>
        <p>Découvre les étudiants de ton campus ou d'ailleurs. À droite si ça te plaît.</p>
      </div>
    </div>

    <div class="step-card c-3">
      <div class="step-number">03 / MATCH</div>
      <div class="step-emoji">💬</div>
      <div class="step-content">
        <h3>Match & discute</h3>
        <p>C'est mutuel ? La conversation s'ouvre. À toi de briser la glace.</p>
      </div>
    </div>
  </div>
</section>

{{-- ════════════════════════════════════════════
     CARTES QUI SE DÉPLIENT — PROFILS VARIÉS
════════════════════════════════════════════ --}}
<section class="unfold-section" id="unfold">
  <div class="unfold-sticky">
    <div class="unfold-heading">
      <div class="eyebrow">Des profils variés</div>
      <h2>Rencontre <span class="italic-accent gradient-text">la diversité</span></h2>
    </div>
    <div class="unfold-stage" id="unfold-stage">
      <div class="unfold-card" data-card="0" style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c);">
        <div class="unfold-card-photo">👩🏾‍🎓</div>
        <div class="unfold-card-info">
          <h4>Aïssatou, 21</h4>
          <div class="meta">📍 UCAD · Lettres modernes</div>
          <div class="tags"><span>Musique</span><span>Voyage</span><span>Photo</span></div>
        </div>
      </div>
      <div class="unfold-card" data-card="1" style="background: linear-gradient(135deg, #a855f7, #ff2d6b);">
        <div class="unfold-card-photo">👨🏿‍🎓</div>
        <div class="unfold-card-info">
          <h4>Mamadou, 23</h4>
          <div class="meta">📍 UCAD · Médecine</div>
          <div class="tags"><span>Basket</span><span>Cinéma</span><span>Cuisine</span></div>
        </div>
      </div>
      <div class="unfold-card" data-card="2" style="background: linear-gradient(135deg, #ffc145, #ff8a5c);">
        <div class="unfold-card-photo">👩🏾</div>
        <div class="unfold-card-info">
          <h4>Fatou, 22</h4>
          <div class="meta">📍 UGB · Droit international</div>
          <div class="tags"><span>Lecture</span><span>Théâtre</span><span>Poésie</span></div>
        </div>
      </div>
      <div class="unfold-card" data-card="3" style="background: linear-gradient(135deg, #2d9d57, #ffc145);">
        <div class="unfold-card-photo">👨🏾</div>
        <div class="unfold-card-info">
          <h4>Ibrahima, 20</h4>
          <div class="meta">📍 UT · Ingénierie</div>
          <div class="tags"><span>Tech</span><span>Foot</span><span>Rap</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ════════════════════════════════════════════
     STATS
════════════════════════════════════════════ --}}
<section class="stats-section" id="stats">
  <div class="stats-container">
    <div class="stat-block">
      <div class="stat-number" data-target="{{ $stats['users'] ?? 0 }}">0</div>
      <div class="stat-label">Étudiants inscrits</div>
    </div>
    <div class="stat-block">
      <div class="stat-number" data-target="{{ $stats['matches'] ?? 0 }}">0</div>
      <div class="stat-label">Matchs créés</div>
    </div>
    <div class="stat-block">
      <div class="stat-number" data-target="{{ $stats['univs'] ?? 0 }}">0</div>
      <div class="stat-label">Universités</div>
    </div>
  </div>
</section>

{{-- ════════════════════════════════════════════
     MARQUEE UNIVERSITÉS
════════════════════════════════════════════ --}}
@if(isset($universities) && $universities->count() > 0)
<section class="marquee-section">
  <div class="marquee-label">Étudiants de toutes les universités du Sénégal</div>
  <div class="marquee-track">
    {{-- Double le loop pour que l'animation infinie soit fluide --}}
    @foreach($universities as $univ)
    <div class="marquee-item">
      <span class="dot"></span>
      🎓 {{ $univ->short_name }}@if($univ->city)<span class="city-dim"> · {{ $univ->city }}</span>@endif
    </div>
    @endforeach
    @foreach($universities as $univ)
    <div class="marquee-item">
      <span class="dot"></span>
      🎓 {{ $univ->short_name }}@if($univ->city)<span class="city-dim"> · {{ $univ->city }}</span>@endif
    </div>
    @endforeach
  </div>
</section>
@endif

{{-- ════════════════════════════════════════════
     TESTIMONIALS
════════════════════════════════════════════ --}}
@if(isset($featuredReviews) && $featuredReviews->count() > 0)
<section class="testimonials" id="testimonials">
  <div class="testimonials-header">
    <div class="eyebrow">Témoignages</div>
    <h2>Ce qu'ils en <span class="italic-accent gradient-text">pensent</span></h2>
  </div>
  <div class="testimonials-grid">
    @foreach($featuredReviews as $rev)
    @php
      $photo = $rev->user->profile?->photo_url;
      $initials = strtoupper(mb_substr($rev->user->name, 0, 2));
      $avatarClass = 'avatar-' . (($loop->index % 3) + 1);
    @endphp
    <div class="testimonial-card">
      <div class="testimonial-stars">
        @for($i = 1; $i <= 5; $i++)
          <span class="{{ $i <= $rev->rating ? 'filled' : 'empty' }}">★</span>
        @endfor
      </div>
      <p class="testimonial-text">« {{ Str::limit($rev->comment, 150) }} »</p>
      <div class="testimonial-author">
        @if($photo)
          <div class="testimonial-avatar" style="background-image: url('{{ $photo }}');"></div>
        @else
          <div class="testimonial-avatar {{ $avatarClass }}">{{ $initials }}</div>
        @endif
        <div>
          <div class="testimonial-name">{{ $rev->user->name }}</div>
          <div class="testimonial-uni">{{ $rev->user->profile?->university_name ?? 'Étudiant·e' }}</div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
</section>
@endif

{{-- ════════════════════════════════════════════
     CTA FINAL
════════════════════════════════════════════ --}}
<section class="cta-section" id="cta">
  <div class="cta-box">
    <div class="cta-content">
      <div class="cta-heart">💘</div>
      <h2 class="cta-title">Prêt·e à <span class="italic-accent gradient-text">matcher</span> ?</h2>
      <p class="cta-sub">Rejoins les {{ number_format($stats['users'] ?? 0, 0, ',', ' ') }} étudiants sénégalais sur Campus Crush</p>
      <p class="cta-micro">Premier mois gratuit · Aucune carte bancaire requise</p>
      <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.05rem;">Créer mon compte →</a>
    </div>
  </div>
</section>

{{-- ════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════ --}}
<footer>
  <div class="footer-container">
    <div class="nav-logo">
      <span class="gradient-text">Campus Crush</span>
    </div>
    <div class="footer-links">
      <a href="{{ route('login') }}">Connexion</a>
      <a href="{{ route('register') }}">S'inscrire</a>
      <a href="/install">Installer l'app</a>
    </div>
    <div class="footer-credit">© {{ date('Y') }} · Fait avec ❤️ pour les étudiants du Sénégal</div>
  </div>
</footer>

</div>

<script>
/* ============================================================
   CAMPUS CRUSH — SCRIPT CINÉMATIQUE
   ============================================================ */
const log = (...args) => console.log('[CC]', ...args);
const hasGSAP = typeof gsap !== 'undefined';
const hasScrollTrigger = typeof ScrollTrigger !== 'undefined';
const hasLenis = typeof Lenis !== 'undefined';
log('GSAP:', hasGSAP, '| ScrollTrigger:', hasScrollTrigger, '| Lenis:', hasLenis);

/* 1. LOADER */
window.addEventListener('load', () => {
  setTimeout(() => {
    const loader = document.getElementById('loader');
    if (loader) {
      loader.classList.add('hidden');
      setTimeout(() => loader.style.display = 'none', 500);
    }
  }, 600);
});

/* 2. LENIS */
let lenis = null;
if (hasLenis) {
  try {
    lenis = new Lenis({
      duration: 1.2,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      smoothWheel: true,
    });
    function raf(time) {
      lenis.raf(time);
      requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);
    log('Lenis initialisé ✓');
  } catch (e) {
    console.warn('Lenis a échoué:', e);
    lenis = null;
  }
}

/* 3. GSAP + SCROLLTRIGGER */
if (hasGSAP && hasScrollTrigger) {
  gsap.registerPlugin(ScrollTrigger);
  if (lenis) {
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);
  }
  log('GSAP + ScrollTrigger prêts ✓');
}

/* 4. NAV SCROLL */
(() => {
  const nav = document.getElementById('nav');
  if (!nav) return;
  window.addEventListener('scroll', () => {
    if (window.scrollY > 80) nav.classList.add('scrolled');
    else nav.classList.remove('scrolled');
  });
})();

/* 5. INTRO HERO */
(() => {
  if (!hasGSAP) {
    document.querySelectorAll('.hero-title .word span').forEach(s => s.style.transform = 'translateY(0)');
    document.querySelectorAll('.fade-el').forEach(el => el.style.opacity = '1');
    return;
  }
  try {
    gsap.to('.hero-title .word span', {
      y: 0, duration: 1.1, ease: 'power4.out', stagger: 0.08, delay: 0.3,
    });
    gsap.from('.fade-el', {
      opacity: 0, y: 40, duration: 1, ease: 'power3.out', stagger: 0.12, delay: 0.6,
    });
  } catch (e) { console.warn('Intro erreur:', e); }
})();

/* 6. PHONE 3D INTERACTION */
(() => {
  const stage = document.getElementById('phone-stage');
  const wrap = document.getElementById('phone-wrap');
  if (!stage || !wrap) return;
  let currentX = 0, currentY = 0, targetX = 0, targetY = 0;

  stage.addEventListener('mousemove', (e) => {
    const rect = stage.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width - 0.5;
    const y = (e.clientY - rect.top) / rect.height - 0.5;
    targetX = x * 25;
    targetY = -y * 20;
  });
  stage.addEventListener('mouseleave', () => {
    targetX = 0; targetY = 0;
  });

  function animate() {
    currentX += (targetX - currentX) * 0.1;
    currentY += (targetY - currentY) * 0.1;
    wrap.style.transform = `rotateY(${currentX}deg) rotateX(${currentY}deg)`;
    requestAnimationFrame(animate);
  }
  animate();
})();

/* 7. AUTO-SWIPE DÉMO */
(() => {
  const mainCard = document.getElementById('main-card');
  const likeStamp = document.getElementById('like-stamp');
  if (!mainCard || !hasGSAP) return;
  try {
    gsap.to(mainCard, {
      y: -10, duration: 2.5, ease: 'sine.inOut', yoyo: true, repeat: -1,
    });
    function swipeDemo() {
      likeStamp.classList.add('show');
      gsap.timeline()
        .to(mainCard, { x: 100, rotation: 10, duration: 0.6, ease: 'power2.out' })
        .to(mainCard, { x: 0, rotation: 0, duration: 0.8, ease: 'elastic.out(1, 0.5)' })
        .call(() => likeStamp.classList.remove('show'), [], '-=0.4');
    }
    setTimeout(swipeDemo, 2500);
    setInterval(swipeDemo, 5500);
  } catch (e) { console.warn('Swipe démo erreur:', e); }
})();

/* 8. HEARTS CANVAS */
(() => {
  const canvas = document.getElementById('hearts-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  function resize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }
  resize();
  window.addEventListener('resize', resize);

  const colors = ['#ff5e6c', '#ff8a5c', '#ffc145', '#a855f7', '#ff2d6b'];
  const hearts = [];
  for (let i = 0; i < 30; i++) {
    hearts.push({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      size: Math.random() * 14 + 8,
      speed: Math.random() * 0.6 + 0.3,
      color: colors[Math.floor(Math.random() * colors.length)],
      opacity: Math.random() * 0.35 + 0.15,
      rotation: Math.random() * Math.PI * 2,
      rotSpeed: (Math.random() - 0.5) * 0.02,
      wobble: Math.random() * Math.PI * 2,
      wobbleSpeed: Math.random() * 0.02 + 0.01,
    });
  }
  function drawHeart(x, y, size, color, opacity, rotation) {
    ctx.save();
    ctx.translate(x, y);
    ctx.rotate(rotation);
    ctx.globalAlpha = opacity;
    ctx.fillStyle = color;
    ctx.beginPath();
    const s = size;
    ctx.moveTo(0, -s * 0.3);
    ctx.bezierCurveTo(-s, -s, -s, s * 0.3, 0, s * 0.7);
    ctx.bezierCurveTo(s, s * 0.3, s, -s, 0, -s * 0.3);
    ctx.fill();
    ctx.restore();
  }
  let scrollOffset = 0;
  window.addEventListener('scroll', () => { scrollOffset = window.scrollY * 0.15; });
  function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hearts.forEach(h => {
      h.y -= h.speed;
      h.rotation += h.rotSpeed;
      h.wobble += h.wobbleSpeed;
      if (h.y < -50) {
        h.y = canvas.height + 50;
        h.x = Math.random() * canvas.width;
      }
      const drawX = h.x + Math.sin(h.wobble) * 20;
      const drawY = h.y - (scrollOffset % canvas.height);
      drawHeart(drawX, drawY, h.size, h.color, h.opacity, h.rotation);
    });
    requestAnimationFrame(animate);
  }
  animate();
})();

/* 9. RÉVÉLATION TEXTE */
(() => {
  const target = document.getElementById('reveal-text');
  if (!target || !hasGSAP || !hasScrollTrigger) return;
  try {
    const fullHTML = target.innerHTML;
    const markers = [];
    let processed = fullHTML.replace(/<mark>(.*?)<\/mark>/g, (_, content) => {
      markers.push(content);
      return `__MARK${markers.length - 1}__`;
    });
    const words = processed.trim().split(/\s+/);
    const html = words.map(word => {
      const markMatch = word.match(/__MARK(\d+)__/);
      if (markMatch) {
        const content = markers[parseInt(markMatch[1])];
        const subWords = content.split(/\s+/);
        return subWords.map(w => `<span class="word-reveal accent">${w}</span>`).join(' ');
      }
      return `<span class="word-reveal">${word}</span>`;
    }).join(' ');
    target.innerHTML = html;
    gsap.to('.word-reveal', {
      opacity: 1, ease: 'none', stagger: 0.04,
      scrollTrigger: {
        trigger: '#reveal',
        start: 'top 75%',
        end: 'bottom 60%',
        scrub: 1,
      }
    });
  } catch (e) { console.warn('Text reveal erreur:', e); }
})();

/* 10. SCROLL HORIZONTAL */
(() => {
  const track = document.getElementById('horizontal-track');
  const section = document.getElementById('horizontal');
  if (!track || !section || !hasGSAP || !hasScrollTrigger) return;
  try {
    const getScrollDistance = () => track.scrollWidth - window.innerWidth;
    gsap.to(track, {
      x: () => -getScrollDistance(),
      ease: 'none',
      scrollTrigger: {
        trigger: section,
        start: 'top top',
        end: () => '+=' + getScrollDistance(),
        pin: true,
        scrub: 1,
        invalidateOnRefresh: true,
        anticipatePin: 1,
      }
    });
  } catch (e) { console.warn('Horizontal erreur:', e); }
})();

/* 11. CARTES QUI SE DÉPLIENT */
(() => {
  const section = document.getElementById('unfold');
  const cards = document.querySelectorAll('.unfold-card');
  if (!section || !cards.length || !hasGSAP || !hasScrollTrigger) return;
  try {
    cards.forEach((card, i) => {
      gsap.set(card, {
        scale: i === 0 ? 1 : 0.8,
        rotation: i === 0 ? 0 : (i % 2 === 0 ? -8 : 8),
        y: i === 0 ? 0 : 50 * i,
        x: i === 0 ? 0 : (i % 2 === 0 ? -30 * i : 30 * i),
        opacity: i === 0 ? 1 : 0,
        zIndex: cards.length - i,
      });
    });
    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: section,
        start: 'top top',
        end: 'bottom bottom',
        scrub: 1,
      }
    });
    cards.forEach((card, i) => {
      if (i === 0) return;
      tl.to(cards[i - 1], {
        scale: 0.85,
        x: i % 2 === 0 ? 150 : -150,
        rotation: i % 2 === 0 ? 15 : -15,
        opacity: 0.3,
        duration: 1,
      }, i - 1)
      .to(card, {
        scale: 1, rotation: 0, x: 0, y: 0, opacity: 1,
        duration: 1, ease: 'power2.out',
      }, i - 1);
    });
  } catch (e) { console.warn('Unfold erreur:', e); }
})();

/* 12. STATS COUNTER */
(() => {
  const stats = document.querySelectorAll('.stat-number');
  if (!stats.length || !hasGSAP || !hasScrollTrigger) return;
  try {
    stats.forEach(stat => {
      const target = parseInt(stat.dataset.target) || 0;
      ScrollTrigger.create({
        trigger: stat,
        start: 'top 85%',
        once: true,
        onEnter: () => {
          const obj = { val: 0 };
          gsap.to(obj, {
            val: target, duration: 2, ease: 'power2.out',
            onUpdate: () => {
              stat.textContent = Math.floor(obj.val).toLocaleString('fr-FR');
            }
          });
        }
      });
    });
  } catch (e) { console.warn('Stats erreur:', e); }
})();

/* 13. TESTIMONIALS + CTA FADE */
(() => {
  if (!hasGSAP || !hasScrollTrigger) return;
  try {
    gsap.utils.toArray('.testimonial-card').forEach((card, i) => {
      gsap.from(card, {
        opacity: 0, y: 50, duration: 0.8, delay: i * 0.15,
        scrollTrigger: {
          trigger: card,
          start: 'top 85%',
          toggleActions: 'play none none reverse',
        }
      });
    });
    gsap.from('.cta-box', {
      scale: 0.92, opacity: 0, duration: 1, ease: 'power3.out',
      scrollTrigger: {
        trigger: '.cta-box',
        start: 'top 80%',
        toggleActions: 'play none none reverse',
      }
    });
  } catch (e) { console.warn('Fade erreur:', e); }
})();

/* 14. COULEURS ÉVOLUTIVES */
(() => {
  if (!hasScrollTrigger) return;
  try {
    const tints = [
      { id: 'hero', class: 'tint-rose' },
      { id: 'reveal', class: 'tint-violet' },
      { id: 'horizontal', class: 'tint-rose' },
      { id: 'unfold', class: 'tint-yellow' },
      { id: 'stats', class: 'tint-green' },
      { id: 'testimonials', class: 'tint-violet' },
      { id: 'cta', class: 'tint-rose' },
    ];
    const allClasses = tints.map(t => t.class);
    tints.forEach(({ id, class: cls }) => {
      const section = document.getElementById(id);
      if (!section) return;
      ScrollTrigger.create({
        trigger: section,
        start: 'top 50%',
        end: 'bottom 50%',
        onToggle: (self) => {
          if (self.isActive) {
            allClasses.forEach(c => document.body.classList.remove(c));
            document.body.classList.add(cls);
          }
        }
      });
    });
    document.body.classList.add('tint-rose');
  } catch (e) { console.warn('Tints erreur:', e); }
})();

/* 15. PROGRESS BAR */
(() => {
  const bar = document.getElementById('progress-bar');
  if (!bar) return;
  window.addEventListener('scroll', () => {
    const h = document.documentElement;
    const scrolled = h.scrollTop / (h.scrollHeight - h.clientHeight);
    bar.style.width = (scrolled * 100) + '%';
  });
})();

/* 16. SECTION INDICATOR */
(() => {
  const dots = document.querySelectorAll('.indicator-dot');
  if (!dots.length) return;
  dots.forEach(dot => {
    dot.addEventListener('click', () => {
      const target = document.getElementById(dot.dataset.target);
      if (!target) return;
      if (lenis) lenis.scrollTo(target, { duration: 1.5 });
      else target.scrollIntoView({ behavior: 'smooth' });
    });
  });
  if (hasScrollTrigger) {
    dots.forEach(dot => {
      const section = document.getElementById(dot.dataset.target);
      if (!section) return;
      ScrollTrigger.create({
        trigger: section,
        start: 'top 50%',
        end: 'bottom 50%',
        onToggle: (self) => {
          if (self.isActive) {
            dots.forEach(d => d.classList.remove('active'));
            dot.classList.add('active');
          }
        }
      });
    });
  }
})();

/* 17. RESIZE */
window.addEventListener('resize', () => {
  if (hasScrollTrigger) ScrollTrigger.refresh();
});

log('Tout est initialisé ✓');
</script>

@include('components.pwa-install-banner')
@include('components.promo-popup')
</body>
</html>
