<?php
// index.php
require_once __DIR__ . '/includes/header.php';

$query = "SELECT v.*, vi.image_path 
          FROM vehicles v 
          LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_main = 1 
          WHERE v.is_featured = 1 AND v.availability_status = 'Available' 
          ORDER BY v.created_at DESC LIMIT 6";
$featured_vehicles = $pdo->query($query)->fetchAll();

// Brands for the hero search panel (fails quietly if the column/table differs)
$search_brands = [];
try {
    $search_brands = $pdo->query(
        "SELECT DISTINCT brand FROM vehicles
         WHERE availability_status = 'Available' AND brand <> ''
         ORDER BY brand"
    )->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $search_brands = [];
}
?>

<style>
/* ═══════════════════════════════════════════════════════
   LB AUTOS — "SOVEREIGN"
   Theme: Forest & Copper — Warm Heritage Luxury
   Fonts: Fraunces + Manrope
   A private atelier aesthetic — warm, tactile, gallery-like
═══════════════════════════════════════════════════════ */

@import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,500&family=Manrope:wght@300;400;500;600;700;800&display=swap');

:root {
    /* Core — forest depth & warm neutrals */
    --forest-deep: #0f1a15;
    --forest: #16251e;
    --forest-mid: #1e3229;
    --forest-soft: #2a4436;
    --moss: #3d5c4a;
    --sage: #6b8774;
    --sage-light: #9db3a4;

    /* Warm neutrals — gallery walls */
    --cream: #f6f1e7;
    --parchment: #ede4d3;
    --linen: #e0d4bd;
    --sand: #cdbb9c;
    --taupe: #9a8974;
    --stone: #6b6154;
    --espresso: #3d362d;
    --ink: #211d17;

    /* Copper spectrum — burnished metal */
    --copper: #b87333;
    --copper-light: #d4935a;
    --copper-pale: #e8bf94;
    --copper-deep: #8a5220;
    --copper-glow: rgba(184, 115, 51, 0.14);
    --copper-line: rgba(184, 115, 51, 0.32);

    /* Signal */
    --ember: #c94f2b;
    --verdant: #5a8f6b;

    /* Easing */
    --ease: cubic-bezier(0.4, 0, 0.2, 1);
    --ease-out: cubic-bezier(0, 0, 0.2, 1);
    --spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    --smooth: cubic-bezier(0.65, 0, 0.35, 1);
}

*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

a {
    text-decoration: none;
    color: inherit;
}

/* ══════════════════════════════
   GLOBAL POLISH (NEW)
══════════════════════════════ */
html {
    scroll-behavior: smooth;
}

::selection {
    background: var(--copper);
    color: var(--forest-deep);
}

:focus-visible {
    outline: 2px solid var(--copper-light);
    outline-offset: 3px;
    border-radius: 2px;
}

/* Slim copper scrollbar (WebKit + Firefox) */
html {
    scrollbar-width: thin;
    scrollbar-color: var(--copper) var(--forest-deep);
}

::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: var(--forest-deep);
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(var(--copper-deep), var(--copper));
    border-radius: 10px;
    border: 2px solid var(--forest-deep);
}

/* Reading progress bar */
.scroll-progress {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    width: 100%;
    transform-origin: left;
    transform: scaleX(0);
    z-index: 9999;
    background: linear-gradient(90deg, var(--copper-deep), var(--copper-light), var(--copper-pale));
    box-shadow: 0 0 14px rgba(212, 147, 90, 0.6);
    pointer-events: none;
}

/* Shared section eyebrow */
.section-eyebrow {
    font-family: 'Manrope', sans-serif;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.42em;
    text-transform: uppercase;
    color: var(--copper-deep);
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
}

.section-eyebrow::before {
    content: '';
    display: block;
    width: 38px;
    height: 1px;
    background: currentColor;
}

.section-eyebrow.center {
    justify-content: center;
}

.section-eyebrow.center::after {
    content: '';
    display: block;
    width: 38px;
    height: 1px;
    background: currentColor;
}

.why-header .section-eyebrow {
    color: var(--copper-light);
}

/* Back to top */
.to-top {
    position: fixed;
    left: 26px;
    bottom: 26px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 1px solid var(--copper-line);
    background: rgba(15, 26, 21, 0.88);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: var(--copper-light);
    font-size: 13px;
    cursor: pointer;
    z-index: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: translateY(16px);
    pointer-events: none;
    transition: opacity 0.35s var(--ease), transform 0.35s var(--ease), background 0.3s, color 0.3s;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
}

.to-top.show {
    opacity: 1;
    transform: none;
    pointer-events: auto;
}

.to-top:hover {
    background: var(--copper);
    color: var(--forest-deep);
}

/* ══════════════════════════════
   HERO — WARM & CINEMATIC
══════════════════════════════ */
.hero {
    position: relative;
    height: 100vh;
    min-height: 780px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--forest-deep);
}

/* ── SLIDES ── */
.hero__slides {
    position: absolute;
    inset: -24px;
    z-index: 0;
    transform: translate3d(var(--px, 0px), var(--py, 0px), 0);
    transition: transform 0.9s var(--ease-out);
}

.hero__slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center 55%;
    opacity: 0;
    transform: scale(1.15);
    transition:
        opacity 2.2s cubic-bezier(0.4, 0, 0.2, 1),
        transform 14s cubic-bezier(0.1, 0, 0.1, 1);
    will-change: opacity, transform;
    filter: saturate(0.85) contrast(1.05);
}

.hero__slide.active {
    opacity: 1;
    transform: scale(1.0);
}

.hero__slide:nth-child(1) {
    background-image: url('https://images.unsplash.com/photo-1631295868223-63265b40d9e4?w=2400&q=90');
}

.hero__slide:nth-child(2) {
    background-image: url('https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=2400&q=90');
    background-position: center 50%;
}

.hero__slide:nth-child(3) {
    background-image: url('https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?w=2400&q=90');
}

.hero__slide:nth-child(4) {
    background-image: url('https://images.unsplash.com/photo-1621135802920-133df287f89c?w=2400&q=90');
    background-position: center 45%;
}

.hero__slide:nth-child(5) {
    background-image: url('https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=2400&q=90');
    background-position: center 60%;
}

/* ── WARM MULTI-LAYER OVERLAY ── */
.hero__overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
    background:
        linear-gradient(112deg, rgba(15, 26, 21, 0.96) 0%, rgba(15, 26, 21, 0.65) 40%, rgba(15, 26, 21, 0.15) 100%),
        linear-gradient(to bottom, rgba(15, 26, 21, 0.35) 0%, transparent 25%, transparent 50%, rgba(15, 26, 21, 0.98) 100%);
}

.hero__vignette {
    position: absolute;
    inset: 0;
    z-index: 2;
    background: radial-gradient(ellipse at 68% 45%, transparent 0%, rgba(15, 26, 21, 0.5) 100%);
    pointer-events: none;
}

.hero__grain {
    position: absolute;
    inset: 0;
    z-index: 2;
    opacity: 0.035;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
    background-size: 180px;
    pointer-events: none;
    mix-blend-mode: overlay;
}

/* Floating copper dust (NEW) */
.hero__dust {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 3;
    pointer-events: none;
}

/* Warm light leak (NEW) */
.hero__glow {
    position: absolute;
    z-index: 2;
    width: 70vw;
    height: 70vw;
    max-width: 960px;
    max-height: 960px;
    left: -18vw;
    top: -24vw;
    background: radial-gradient(circle, rgba(184, 115, 51, 0.22) 0%, transparent 62%);
    filter: blur(30px);
    pointer-events: none;
    animation: glowDrift 16s ease-in-out infinite alternate;
}

/* Copper hairlines */
.hero__top-rule,
.hero__bottom-rule {
    position: absolute;
    left: 0;
    right: 0;
    height: 1px;
    z-index: 10;
    background: linear-gradient(90deg, transparent 0%, var(--copper-deep) 18%, var(--copper-light) 50%, var(--copper-deep) 82%, transparent 100%);
}

.hero__top-rule {
    top: 0;
    opacity: 0.9;
}

.hero__bottom-rule {
    bottom: 0;
    opacity: 0.45;
}

/* ── CONTENT ── */
.hero__content {
    position: relative;
    z-index: 5;
    width: 100%;
    max-width: 1380px;
    padding: 0 7% 70px;
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    align-items: center;
    gap: 72px;
}

.hero__badge {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    font-family: 'Manrope', sans-serif;
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 0.42em;
    text-transform: uppercase;
    color: var(--copper-light);
    margin-bottom: 34px;
    opacity: 0;
    animation: slideRight 1s 0.15s var(--ease-out) forwards;
}

.hero__badge-line {
    width: 48px;
    height: 1px;
    background: var(--copper-line);
}

.hero__badge-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--copper);
    box-shadow: 0 0 12px var(--copper);
    animation: pulseDot 2.6s ease-in-out infinite;
}

.hero h1 {
    font-family: 'Fraunces', serif;
    font-size: clamp(54px, 6.4vw, 96px);
    font-weight: 400;
    color: var(--cream);
    line-height: 0.98;
    letter-spacing: -0.025em;
    margin-bottom: 32px;
    opacity: 0;
    animation: slideRight 1s 0.3s var(--ease-out) forwards;
}

/* Burnished-metal sheen on the accent line (NEW) */
.hero h1 em,
.cta-title em {
    font-style: italic;
    font-weight: 500;
    background: linear-gradient(100deg, var(--copper) 0%, var(--copper-pale) 38%, var(--copper-light) 55%, var(--copper) 100%);
    background-size: 220% auto;
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    -webkit-text-fill-color: transparent;
    animation: sheen 7s linear infinite;
    filter: drop-shadow(0 0 36px rgba(184, 115, 51, 0.35));
}

.hero h1 em {
    display: block;
}

.hero h1 .thin {
    font-weight: 300;
    color: rgba(246, 241, 231, 0.88);
}

.hero__sub {
    font-family: 'Manrope', sans-serif;
    font-size: 14.5px;
    font-weight: 300;
    color: rgba(246, 241, 231, 0.5);
    line-height: 1.9;
    max-width: 460px;
    margin-bottom: 50px;
    letter-spacing: 0.015em;
    opacity: 0;
    animation: slideRight 1s 0.45s var(--ease-out) forwards;
}

/* ── CTA BUTTONS ── */
.hero__ctas {
    display: flex;
    align-items: center;
    gap: 22px;
    opacity: 0;
    animation: slideRight 1s 0.6s var(--ease-out) forwards;
}

.btn-primary {
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, var(--copper) 0%, var(--copper-light) 100%);
    color: var(--forest-deep);
    font-family: 'Manrope', sans-serif;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    padding: 19px 42px;
    border-radius: 3px;
    border: none;
    cursor: pointer;
    transition: box-shadow 0.3s var(--ease), transform 0.25s var(--ease), filter 0.3s;
    box-shadow: 0 12px 44px rgba(184, 115, 51, 0.35);
}

/* Light sweep across primary buttons (NEW) */
.btn-primary::after,
.search-submit::after {
    content: '';
    position: absolute;
    top: 0;
    left: -60%;
    width: 40%;
    height: 100%;
    background: linear-gradient(100deg, transparent, rgba(255, 255, 255, 0.45), transparent);
    transform: skewX(-20deg);
    transition: left 0.7s var(--ease);
    pointer-events: none;
}

.btn-primary:hover::after,
.search-submit:hover::after {
    left: 130%;
}

.btn-primary:hover {
    box-shadow: 0 18px 60px rgba(184, 115, 51, 0.5);
    transform: translateY(-2px);
    filter: brightness(1.08);
}

.btn-primary i {
    transition: transform 0.25s var(--ease);
}

.btn-primary:hover i {
    transform: translateX(5px);
}

.btn-ghost {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: transparent;
    color: rgba(246, 241, 231, 0.62);
    font-family: 'Manrope', sans-serif;
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    padding: 18px 32px;
    border: 1px solid rgba(246, 241, 231, 0.18);
    border-radius: 3px;
    transition: border-color 0.3s, color 0.3s, background 0.3s;
}

.btn-ghost:hover {
    border-color: var(--copper-line);
    color: var(--copper-light);
    background: rgba(184, 115, 51, 0.08);
}

/* ── HERO RIGHT: SEARCH PANEL ── */
.hero__right {
    opacity: 0;
    animation: fadeUp 1.2s 0.6s var(--ease-out) forwards;
}

.search-panel {
    background: rgba(22, 37, 30, 0.82);
    backdrop-filter: blur(36px);
    -webkit-backdrop-filter: blur(36px);
    border: 1px solid rgba(246, 241, 231, 0.08);
    border-top: 1px solid var(--copper-deep);
    padding: 42px;
    border-radius: 5px;
    box-shadow:
        0 55px 110px rgba(0, 0, 0, 0.7),
        inset 0 1px 0 rgba(246, 241, 231, 0.05);
    position: relative;
}

.search-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--copper-light), transparent);
    opacity: 0.7;
}

.search-panel__title {
    font-family: 'Fraunces', serif;
    font-size: 27px;
    font-weight: 500;
    color: var(--cream);
    margin-bottom: 7px;
    letter-spacing: -0.015em;
}

.search-panel__sub {
    font-family: 'Manrope', sans-serif;
    font-size: 10.5px;
    color: var(--sage);
    margin-bottom: 32px;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.search-panel form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-field label {
    font-family: 'Manrope', sans-serif;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.26em;
    text-transform: uppercase;
    color: var(--copper-light);
}

.form-field select,
.form-field input {
    background: rgba(246, 241, 231, 0.05);
    border: 1px solid rgba(246, 241, 231, 0.1);
    border-radius: 3px;
    padding: 14px 16px;
    font-family: 'Manrope', sans-serif;
    font-size: 12.5px;
    font-weight: 400;
    color: var(--cream);
    appearance: none;
    -webkit-appearance: none;
    transition: border-color 0.25s, background 0.25s;
    letter-spacing: 0.03em;
}

/* Chevron for custom selects (NEW) */
.form-field select {
    padding-right: 38px;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' fill='none' stroke='%23d4935a' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
}

.form-field select option {
    background: #1e3229;
    color: var(--cream);
}

.form-field select:focus,
.form-field input:focus {
    outline: none;
    border-color: var(--copper);
    background-color: rgba(184, 115, 51, 0.08);
}

.form-field input::placeholder {
    color: rgba(246, 241, 231, 0.28);
}

.search-submit {
    position: relative;
    overflow: hidden;
    margin-top: 10px;
    width: 100%;
    background: linear-gradient(135deg, var(--copper) 0%, var(--copper-light) 100%);
    color: var(--forest-deep);
    font-family: 'Manrope', sans-serif;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    padding: 17px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    transition: box-shadow 0.3s, transform 0.25s, filter 0.3s;
    box-shadow: 0 10px 32px rgba(184, 115, 51, 0.3);
}

.search-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 44px rgba(184, 115, 51, 0.45);
    filter: brightness(1.08);
}

/* ── BOTTOM BAR: STATS + SLIDE NAV ── */
.hero__footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 6;
    display: flex;
    align-items: stretch;
    border-top: 1px solid rgba(246, 241, 231, 0.06);
    opacity: 0;
    animation: fadeUp 1s 0.9s var(--ease-out) forwards;
}

.hero__stat {
    flex: 1;
    padding: 26px 0;
    text-align: center;
    border-right: 1px solid rgba(246, 241, 231, 0.05);
    background: rgba(15, 26, 21, 0.78);
    backdrop-filter: blur(14px);
    transition: background 0.3s;
}

.hero__stat:last-child {
    border-right: none;
}

.hero__stat:hover {
    background: rgba(184, 115, 51, 0.07);
}

.stat__num {
    font-family: 'Fraunces', serif;
    font-size: 30px;
    font-weight: 600;
    color: var(--copper-light);
    line-height: 1;
    letter-spacing: -0.015em;
    font-variant-numeric: tabular-nums;
}

.stat__label {
    font-family: 'Manrope', sans-serif;
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: rgba(246, 241, 231, 0.32);
    margin-top: 7px;
}

/* Slide navigation */
.hero__nav {
    display: flex;
    align-items: center;
    gap: 0;
    background: rgba(15, 26, 21, 0.78);
    backdrop-filter: blur(14px);
}

.hero__nav-btn {
    width: 56px;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    border-left: 1px solid rgba(246, 241, 231, 0.05);
    color: rgba(246, 241, 231, 0.38);
    font-size: 12px;
    cursor: pointer;
    transition: color 0.25s, background 0.25s;
}

.hero__nav-btn:hover {
    color: var(--copper-light);
    background: rgba(184, 115, 51, 0.08);
}

.hero__counter {
    padding: 0 24px;
    font-family: 'Fraunces', serif;
    font-size: 14px;
    color: rgba(246, 241, 231, 0.32);
    letter-spacing: 0.14em;
    white-space: nowrap;
    border-left: 1px solid rgba(246, 241, 231, 0.05);
    display: flex;
    align-items: center;
    gap: 6px;
}

.hero__counter span {
    color: var(--copper-light);
    font-weight: 600;
}

/* ── CAR LABEL BADGE ── */
.hero__car-label {
    position: absolute;
    top: 52px;
    right: 7%;
    z-index: 6;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
    opacity: 0;
    transform: translateY(-8px);
    transition: opacity 0.6s 0.4s, transform 0.6s 0.4s var(--ease);
}

.hero__car-label.visible {
    opacity: 1;
    transform: none;
}

.car-label__tag {
    font-family: 'Manrope', sans-serif;
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--copper);
}

.car-label__name {
    font-family: 'Fraunces', serif;
    font-size: 19px;
    font-weight: 500;
    color: rgba(246, 241, 231, 0.88);
    letter-spacing: 0.01em;
}

/* ── PROGRESS BAR ── */
.hero__progress {
    position: absolute;
    bottom: 74px;
    left: 7%;
    z-index: 6;
    width: 210px;
    height: 1px;
    background: rgba(246, 241, 231, 0.12);
}

.hero__progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--copper-deep), var(--copper-light));
    width: 0%;
    transition: width 5s linear;
}

.hero__progress-fill.running {
    width: 100%;
}

/* Scroll cue (NEW) */
.hero__scroll {
    position: absolute;
    right: 7%;
    bottom: 120px;
    z-index: 6;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    font-family: 'Manrope', sans-serif;
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: rgba(246, 241, 231, 0.4);
    writing-mode: vertical-rl;
    opacity: 0;
    animation: fadeUp 1s 1.3s var(--ease-out) forwards;
}

.hero__scroll-line {
    display: block;
    width: 1px;
    height: 56px;
    background: rgba(246, 241, 231, 0.14);
    position: relative;
    overflow: hidden;
}

.hero__scroll-line::after {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 40%;
    background: linear-gradient(var(--copper-light), transparent);
    animation: scrollCue 2.2s ease-in-out infinite;
}


/* ══════════════════════════════
   FEATURED INVENTORY — GALLERY
══════════════════════════════ */
.inventory-section {
    background: var(--cream);
    padding: 120px 0 100px;
    position: relative;
}

.inventory-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--copper-line), transparent);
}

.inventory-header {
    padding: 0 7% 72px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
}

.inventory-header h2 {
    font-family: 'Fraunces', serif;
    font-size: clamp(38px, 4.6vw, 62px);
    font-weight: 400;
    color: var(--ink);
    letter-spacing: -0.025em;
    line-height: 1.05;
}

.inventory-header h2 em {
    font-style: italic;
    font-weight: 500;
    color: var(--copper-deep);
}

.inventory-header p {
    font-family: 'Manrope', sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: var(--stone);
    margin-top: 14px;
    max-width: 380px;
    line-height: 1.85;
    letter-spacing: 0.01em;
}

.header-link {
    font-family: 'Manrope', sans-serif;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--copper-deep);
    display: flex;
    align-items: center;
    gap: 12px;
    white-space: nowrap;
    padding-bottom: 9px;
    border-bottom: 1px solid var(--copper-line);
    transition: gap 0.25s, border-color 0.25s;
}

.header-link:hover {
    gap: 18px;
    border-color: var(--copper-deep);
}

/* ── CAR GRID ── */
.car-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 26px;
    padding: 0 7% 84px;
}

/* ── CAR CARD ── */
.car-card {
    position: relative;
    overflow: hidden;
    background: #fff;
    border: 1px solid rgba(33, 29, 23, 0.07);
    border-radius: 4px;
    transition: transform 0.42s var(--ease), box-shadow 0.42s;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 12px rgba(33, 29, 23, 0.03);
}

.car-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--copper-deep), var(--copper-light), var(--copper-deep));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.5s var(--ease);
    z-index: 3;
}

/* Cursor-following copper spotlight (NEW) */
.car-card::after {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 2;
    opacity: 0;
    transition: opacity 0.4s;
    background: radial-gradient(420px circle at var(--mx, 50%) var(--my, 50%), rgba(184, 115, 51, 0.1), transparent 60%);
}

.car-card:hover {
    transform: translateY(-9px);
    box-shadow: 0 44px 88px rgba(33, 29, 23, 0.11), 0 0 0 1px rgba(184, 115, 51, 0.18);
    z-index: 2;
}

.car-card:hover::before {
    transform: scaleX(1);
}

.car-card:hover::after {
    opacity: 1;
}

/* Image */
.car-img-wrapper {
    position: relative;
    height: 240px;
    overflow: hidden;
    background: var(--parchment);
}

.car-img-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.85s var(--ease), filter 0.85s var(--ease);
}

.car-card:hover .car-img-wrapper img {
    transform: scale(1.07);
    filter: brightness(1.05) saturate(1.05);
}

.car-img-wrapper::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 55%;
    background: linear-gradient(to top, rgba(15, 26, 21, 0.4), transparent);
    z-index: 1;
    pointer-events: none;
}

/* Diagonal light sweep over the photo (NEW) */
.car-img-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: -80%;
    width: 50%;
    height: 100%;
    z-index: 2;
    background: linear-gradient(100deg, transparent, rgba(255, 255, 255, 0.26), transparent);
    transform: skewX(-18deg);
    transition: left 0.95s var(--ease);
    pointer-events: none;
}

.car-card:hover .car-img-wrapper::before {
    left: 130%;
}

/* Lot number, gallery-label style (NEW) */
.car-lot {
    position: absolute;
    bottom: 14px;
    right: 16px;
    z-index: 2;
    font-family: 'Fraunces', serif;
    font-style: italic;
    font-size: 13px;
    letter-spacing: 0.08em;
    color: rgba(246, 241, 231, 0.9);
}

/* Condition badge */
.condition-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    font-family: 'Manrope', sans-serif;
    font-size: 8.5px;
    font-weight: 800;
    letter-spacing: 0.26em;
    text-transform: uppercase;
    padding: 7px 15px;
    border-radius: 2px;
    z-index: 2;
}

.condition-badge.new {
    background: var(--copper);
    color: var(--forest-deep);
}

.condition-badge.used {
    background: rgba(246, 241, 231, 0.94);
    color: var(--espresso);
    border: 1px solid rgba(33, 29, 23, 0.1);
    backdrop-filter: blur(8px);
}

.discount-ribbon {
    position: absolute;
    top: 16px;
    right: 16px;
    background: var(--ember);
    color: #fff;
    font-family: 'Manrope', sans-serif;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.14em;
    padding: 7px 12px;
    border-radius: 2px;
    z-index: 2;
    box-shadow: 0 4px 14px rgba(201, 79, 43, 0.4);
}

/* Card body */
.car-details {
    padding: 28px 28px 26px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.car-price-row {
    display: flex;
    align-items: baseline;
    gap: 12px;
    margin-bottom: 9px;
}

.car-price {
    font-family: 'Fraunces', serif;
    font-size: 31px;
    font-weight: 600;
    color: var(--ink);
    line-height: 1;
    letter-spacing: -0.015em;
}

.car-price-old {
    font-family: 'Manrope', sans-serif;
    font-size: 12px;
    color: var(--taupe);
    text-decoration: line-through;
    font-weight: 400;
}

.car-title {
    font-family: 'Manrope', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--espresso);
    margin-bottom: 24px;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

/* Specs */
.car-specs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 11px 18px;
    padding-bottom: 24px;
    margin-bottom: 24px;
    border-bottom: 1px solid rgba(33, 29, 23, 0.08);
}

.spec-item {
    display: flex;
    align-items: center;
    gap: 9px;
    font-family: 'Manrope', sans-serif;
    font-size: 11px;
    font-weight: 500;
    color: var(--stone);
    letter-spacing: 0.03em;
}

.spec-item i {
    color: var(--copper-deep);
    font-size: 10px;
    width: 13px;
    flex-shrink: 0;
    opacity: 0.9;
}

/* CTA */
.btn-view {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 22px;
    border: 1px solid rgba(33, 29, 23, 0.1);
    border-radius: 3px;
    font-family: 'Manrope', sans-serif;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ink);
    transition: background 0.3s, border-color 0.3s, color 0.3s;
    margin-top: auto;
}

.btn-view i {
    transition: transform 0.25s var(--ease);
}

.btn-view:hover {
    background: var(--forest);
    border-color: var(--forest);
    color: var(--cream);
}

.btn-view:hover i {
    transform: translateX(5px);
}

/* Empty state */
.empty-state {
    grid-column: 1 / -1;
    padding: 80px 20px;
    text-align: center;
}

.empty-state p {
    font-family: 'Fraunces', serif;
    font-size: 25px;
    color: var(--stone);
    font-style: italic;
}

/* View all */
.view-all-wrap {
    text-align: center;
    padding: 10px 7% 20px;
}

.btn-outline-gold {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    padding: 19px 50px;
    border: 1px solid var(--copper-line);
    border-radius: 3px;
    font-family: 'Manrope', sans-serif;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--copper-deep);
    transition: background 0.3s, border-color 0.3s, color 0.3s, box-shadow 0.3s;
}

.btn-outline-gold i {
    transition: transform 0.25s var(--ease);
}

.btn-outline-gold:hover {
    background: linear-gradient(135deg, var(--copper), var(--copper-light));
    border-color: var(--copper);
    color: var(--forest-deep);
    box-shadow: 0 12px 40px rgba(184, 115, 51, 0.4);
}

.btn-outline-gold:hover i {
    transform: translateX(6px);
}


/* ══════════════════════════════
   MARQUEE STRIP
══════════════════════════════ */
.marquee-strip {
    background: var(--forest);
    border-top: 1px solid var(--forest-soft);
    border-bottom: 1px solid var(--forest-soft);
    padding: 18px 0;
    overflow: hidden;
    position: relative;
}

.marquee-strip::before,
.marquee-strip::after {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 110px;
    z-index: 2;
    pointer-events: none;
}

.marquee-strip::before {
    left: 0;
    background: linear-gradient(90deg, var(--forest), transparent);
}

.marquee-strip::after {
    right: 0;
    background: linear-gradient(-90deg, var(--forest), transparent);
}

.marquee-track {
    display: flex;
    gap: 0;
    animation: marquee 38s linear infinite;
    width: max-content;
}

.marquee-strip:hover .marquee-track {
    animation-play-state: paused;
}

.marquee-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 0 50px;
    border-right: 1px solid var(--forest-soft);
    white-space: nowrap;
}

.marquee-item i {
    color: var(--copper-light);
    font-size: 12px;
}

.marquee-item span {
    font-family: 'Manrope', sans-serif;
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: var(--sage-light);
}

@keyframes marquee {
    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(-50%);
    }
}


/* ══════════════════════════════
   MARQUES — BRAND WALL (NEW)
══════════════════════════════ */
.marques-section {
    background: var(--cream);
    padding: 120px 7% 130px;
    text-align: center;
    position: relative;
}

.marques-section h2 {
    font-family: 'Fraunces', serif;
    font-size: clamp(36px, 4.4vw, 58px);
    font-weight: 400;
    color: var(--ink);
    letter-spacing: -0.025em;
    line-height: 1.06;
    margin-bottom: 18px;
}

.marques-section h2 em {
    font-style: italic;
    font-weight: 500;
    color: var(--copper-deep);
}

.marques-section__sub {
    font-family: 'Manrope', sans-serif;
    font-size: 13px;
    color: var(--stone);
    line-height: 1.85;
    max-width: 440px;
    margin: 0 auto 70px;
}

.marques-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    border-top: 1px solid rgba(33, 29, 23, 0.09);
    border-left: 1px solid rgba(33, 29, 23, 0.09);
    max-width: 1200px;
    margin: 0 auto;
}

.marque {
    position: relative;
    padding: 50px 18px 44px;
    border-right: 1px solid rgba(33, 29, 23, 0.09);
    border-bottom: 1px solid rgba(33, 29, 23, 0.09);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    overflow: hidden;
    transition: background 0.4s var(--ease);
}

.marque::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--copper-deep), var(--copper-light));
    transform: translateX(-50%);
    transition: width 0.45s var(--ease);
}

.marque__name {
    font-family: 'Fraunces', serif;
    font-style: italic;
    font-weight: 400;
    font-size: clamp(20px, 2vw, 27px);
    color: var(--espresso);
    letter-spacing: -0.01em;
    transition: color 0.35s, letter-spacing 0.45s var(--ease);
}

.marque__origin {
    font-family: 'Manrope', sans-serif;
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: var(--taupe);
}

.marque:hover {
    background: #fff;
}

.marque:hover::after {
    width: 100%;
}

.marque:hover .marque__name {
    color: var(--copper-deep);
    letter-spacing: 0.02em;
}


/* ══════════════════════════════
   WHY US — PARALLAX
══════════════════════════════ */
.why-section {
    background-color: var(--forest-deep);
    background-image:
        linear-gradient(rgba(15, 26, 21, 0.93), rgba(15, 26, 21, 0.98)),
        url('https://images.unsplash.com/photo-1614200187524-dc4b892acf16?w=2000&q=90');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    padding: 130px 7%;
    position: relative;
    overflow: hidden;
}

.why-section::before {
    content: 'LB';
    position: absolute;
    top: -50px;
    right: -30px;
    font-family: 'Fraunces', serif;
    font-size: clamp(150px, 22vw, 300px);
    font-weight: 700;
    color: rgba(184, 115, 51, 0.035);
    pointer-events: none;
    letter-spacing: -0.08em;
    line-height: 1;
    z-index: 0;
    font-style: italic;
}

.why-header,
.why-grid {
    position: relative;
    z-index: 2;
}

.why-header {
    margin-bottom: 74px;
}

.why-header h2 {
    font-family: 'Fraunces', serif;
    font-size: clamp(40px, 5vw, 66px);
    font-weight: 400;
    color: var(--cream);
    letter-spacing: -0.025em;
    line-height: 1.02;
    max-width: 500px;
}

.why-header h2 em {
    font-style: italic;
    font-weight: 500;
    color: var(--copper-light);
}

.why-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2px;
}

.why-card {
    background: rgba(30, 50, 41, 0.55);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    padding: 50px 38px;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(246, 241, 231, 0.05);
    transition: transform 0.35s var(--ease), box-shadow 0.35s, background 0.35s;
}

/* Cursor-following spotlight (NEW) */
.why-card::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.4s;
    background: radial-gradient(360px circle at var(--mx, 50%) var(--my, 50%), rgba(212, 147, 90, 0.13), transparent 60%);
}

.why-card:hover::before {
    opacity: 1;
}

.why-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--copper-deep), var(--copper-light));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.45s var(--ease);
}

.why-card:hover {
    transform: translateY(-9px);
    box-shadow: 0 34px 76px rgba(0, 0, 0, 0.75);
    background: rgba(30, 50, 41, 0.82);
    z-index: 1;
}

.why-card:hover::after {
    transform: scaleX(1);
}

.why-card__num {
    font-family: 'Fraunces', serif;
    font-size: 76px;
    font-weight: 300;
    color: rgba(246, 241, 231, 0.05);
    line-height: 1;
    margin-bottom: 22px;
    letter-spacing: -0.05em;
    font-style: italic;
}

.why-card__icon {
    width: 52px;
    height: 52px;
    border: 1px solid var(--copper-line);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 28px;
    background: linear-gradient(135deg, rgba(184, 115, 51, 0.1), transparent);
    transition: border-color 0.3s, background 0.3s, transform 0.5s var(--spring);
}

.why-card:hover .why-card__icon {
    border-color: var(--copper);
    background: linear-gradient(135deg, rgba(184, 115, 51, 0.18), transparent);
    transform: rotate(-8deg) scale(1.08);
}

.why-card__icon i {
    color: var(--copper-light);
    font-size: 16px;
}

.why-card h4 {
    font-family: 'Manrope', sans-serif;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--cream);
    margin-bottom: 15px;
}

.why-card p {
    font-family: 'Manrope', sans-serif;
    font-size: 12.5px;
    font-weight: 400;
    color: var(--sage-light);
    line-height: 1.85;
}


/* ══════════════════════════════
   PROCESS — THE ACQUISITION (NEW)
══════════════════════════════ */
.process-section {
    background: var(--parchment);
    padding: 120px 7% 130px;
    position: relative;
}

.process-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--copper-line), transparent);
}

.process-header {
    text-align: center;
    max-width: 640px;
    margin: 0 auto 84px;
}

.process-header h2 {
    font-family: 'Fraunces', serif;
    font-size: clamp(36px, 4.4vw, 58px);
    font-weight: 400;
    color: var(--ink);
    letter-spacing: -0.025em;
    line-height: 1.06;
}

.process-header h2 em {
    font-style: italic;
    font-weight: 500;
    color: var(--copper-deep);
}

.process-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 40px;
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
}

/* Dashed copper path linking the steps */
.process-grid::before {
    content: '';
    position: absolute;
    top: 34px;
    left: 12.5%;
    right: 12.5%;
    height: 1px;
    background: repeating-linear-gradient(90deg, var(--copper-line) 0 7px, transparent 7px 14px);
}

.process-step {
    text-align: center;
    position: relative;
}

.process-step__node {
    position: relative;
    z-index: 1;
    width: 68px;
    height: 68px;
    margin: 0 auto 30px;
    border-radius: 50%;
    background: var(--cream);
    border: 1px solid var(--copper-line);
    box-shadow: 0 0 0 9px var(--parchment);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Fraunces', serif;
    font-style: italic;
    font-size: 24px;
    color: var(--copper-deep);
    transition: background 0.4s var(--ease), color 0.4s, transform 0.5s var(--spring), border-color 0.4s;
}

.process-step:hover .process-step__node {
    background: linear-gradient(135deg, var(--copper), var(--copper-light));
    color: var(--forest-deep);
    border-color: var(--copper);
    transform: scale(1.1);
}

.process-step h4 {
    font-family: 'Fraunces', serif;
    font-size: 22px;
    font-weight: 500;
    color: var(--ink);
    letter-spacing: -0.01em;
    margin-bottom: 12px;
}

.process-step p {
    font-family: 'Manrope', sans-serif;
    font-size: 12.5px;
    color: var(--stone);
    line-height: 1.85;
    max-width: 250px;
    margin: 0 auto;
}


/* ══════════════════════════════
   TESTIMONIAL BAND
══════════════════════════════ */
.testimonial-band {
    background: var(--forest-mid);
    border-top: 1px solid rgba(246, 241, 231, 0.05);
    padding: 100px 7%;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2px;
}

.testimonial-card {
    background: rgba(246, 241, 231, 0.025);
    border: 1px solid rgba(246, 241, 231, 0.05);
    padding: 44px 38px;
    position: relative;
    overflow: hidden;
    transition: background 0.3s;
}

/* Oversized quote mark (NEW) */
.testimonial-card::before {
    content: '\201C';
    position: absolute;
    top: 6px;
    right: 28px;
    font-family: 'Fraunces', serif;
    font-style: italic;
    font-size: 130px;
    line-height: 1;
    color: rgba(184, 115, 51, 0.16);
    pointer-events: none;
    transition: color 0.4s, transform 0.5s var(--ease);
}

.testimonial-card:hover {
    background: rgba(246, 241, 231, 0.05);
}

.testimonial-card:hover::before {
    color: rgba(212, 147, 90, 0.3);
    transform: translateY(-4px);
}

.testimonial-card__stars {
    display: flex;
    gap: 5px;
    margin-bottom: 22px;
}

.testimonial-card__stars i {
    color: var(--copper-light);
    font-size: 11px;
}

.testimonial-card__quote {
    font-family: 'Fraunces', serif;
    font-size: 18px;
    font-weight: 400;
    font-style: italic;
    color: rgba(246, 241, 231, 0.72);
    line-height: 1.72;
    margin-bottom: 30px;
    position: relative;
}

.testimonial-card__author {
    display: flex;
    align-items: center;
    gap: 15px;
}

.testimonial-card__avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--copper-deep), var(--copper-light));
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Fraunces', serif;
    font-size: 19px;
    color: var(--forest-deep);
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(184, 115, 51, 0.18);
}

.testimonial-card__name {
    font-family: 'Manrope', sans-serif;
    font-size: 12px;
    font-weight: 700;
    color: var(--sage-light);
    letter-spacing: 0.05em;
}

.testimonial-card__vehicle {
    font-family: 'Manrope', sans-serif;
    font-size: 10px;
    font-weight: 400;
    color: var(--copper-light);
    margin-top: 3px;
    letter-spacing: 0.07em;
}

/* ══════════════════════════════
   JOURNAL / BLOG SECTION
══════════════════════════════ */
.blog-section {
    background: var(--parchment);
    padding: 120px 0 140px;
}

.blog-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    padding: 0 7%;
}

.blog-card {
    background: var(--cream);
    border: 1px solid rgba(33, 29, 23, 0.06);
    border-radius: 4px;
    transition: transform 0.42s var(--ease), box-shadow 0.42s;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.blog-card:hover {
    transform: translateY(-9px);
    box-shadow: 0 44px 88px rgba(33, 29, 23, 0.09);
}

.blog-img {
    height: 270px;
    overflow: hidden;
    position: relative;
}

/* Warm tint that fades on hover (NEW) */
.blog-img::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(138, 82, 32, 0.28), transparent 60%);
    transition: opacity 0.5s;
    pointer-events: none;
}

.blog-card:hover .blog-img::after {
    opacity: 0;
}

.blog-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.85s var(--ease);
}

.blog-card:hover .blog-img img {
    transform: scale(1.06);
}

.blog-content {
    padding: 36px 30px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.blog-meta {
    font-family: 'Manrope', sans-serif;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.26em;
    text-transform: uppercase;
    color: var(--copper-deep);
    margin-bottom: 15px;
}

.blog-title {
    font-family: 'Fraunces', serif;
    font-size: 25px;
    font-weight: 500;
    color: var(--ink);
    line-height: 1.26;
    margin-bottom: 17px;
    letter-spacing: -0.015em;
}

.blog-excerpt {
    font-family: 'Manrope', sans-serif;
    font-size: 12.5px;
    font-weight: 400;
    color: var(--stone);
    line-height: 1.82;
    margin-bottom: 28px;
}

.blog-link {
    margin-top: auto;
    font-family: 'Manrope', sans-serif;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--ink);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: gap 0.25s, color 0.25s;
}

.blog-link i {
    color: var(--copper-deep);
}

.blog-link:hover {
    gap: 16px;
    color: var(--copper-deep);
}

/* ══════════════════════════════
   CALL TO ACTION
══════════════════════════════ */
.cta-section {
    position: relative;
    padding: 170px 7%;
    background: var(--forest-deep);
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    z-index: 3;
    background: linear-gradient(90deg, transparent, var(--copper-light), transparent);
    opacity: 0.7;
}

.cta-bg {
    position: absolute;
    inset: 0;
    background-image: url('https://images.unsplash.com/photo-1563720225384-986167c8702c?w=2000&q=90');
    background-size: cover;
    background-position: center;
    opacity: 0.22;
    z-index: 0;
    filter: saturate(0.7);
}

.cta-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, var(--forest-deep) 0%, rgba(15, 26, 21, 0.35) 100%);
    z-index: 1;
}

/* Concentric copper rings behind the heading (NEW) */
.cta-rings {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 760px;
    height: 760px;
    transform: translate(-50%, -50%);
    z-index: 1;
    pointer-events: none;
    border-radius: 50%;
    border: 1px solid rgba(184, 115, 51, 0.16);
    box-shadow:
        0 0 0 90px rgba(184, 115, 51, 0.03),
        0 0 0 91px rgba(184, 115, 51, 0.11),
        0 0 0 190px rgba(184, 115, 51, 0.02),
        0 0 0 191px rgba(184, 115, 51, 0.07);
    animation: ringBreathe 9s ease-in-out infinite;
}

.cta-content {
    position: relative;
    z-index: 2;
    max-width: 780px;
}

.cta-eyebrow {
    font-family: 'Manrope', sans-serif;
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.42em;
    text-transform: uppercase;
    color: var(--copper-light);
    margin-bottom: 26px;
}

.cta-title {
    font-family: 'Fraunces', serif;
    font-size: clamp(44px, 5.6vw, 68px);
    font-weight: 400;
    color: var(--cream);
    line-height: 1.02;
    margin-bottom: 30px;
    letter-spacing: -0.025em;
}

.cta-text {
    font-family: 'Manrope', sans-serif;
    font-size: 14.5px;
    font-weight: 400;
    color: rgba(246, 241, 231, 0.55);
    line-height: 1.85;
    margin-bottom: 48px;
    letter-spacing: 0.02em;
}

.cta-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 18px;
}

/* ══════════════════════════════
   ANIMATIONS & REVEAL
══════════════════════════════ */
@keyframes slideRight {
    from {
        opacity: 0;
        transform: translateX(-32px);
    }

    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(28px);
    }

    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes sheen {
    from {
        background-position: 0% center;
    }

    to {
        background-position: 220% center;
    }
}

@keyframes pulseDot {

    0%,
    100% {
        box-shadow: 0 0 6px var(--copper);
        transform: scale(1);
    }

    50% {
        box-shadow: 0 0 18px var(--copper-light);
        transform: scale(1.35);
    }
}

@keyframes glowDrift {
    from {
        transform: translate(0, 0) scale(1);
    }

    to {
        transform: translate(8vw, 5vw) scale(1.12);
    }
}

@keyframes scrollCue {
    0% {
        transform: translateY(-110%);
    }

    100% {
        transform: translateY(260%);
    }
}

@keyframes ringBreathe {

    0%,
    100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.9;
    }

    50% {
        transform: translate(-50%, -50%) scale(1.05);
        opacity: 0.55;
    }
}

.reveal {
    opacity: 0;
    transform: translateY(32px);
    transition: opacity 0.9s var(--ease), transform 0.9s var(--ease);
}

.reveal.visible {
    opacity: 1;
    transform: none;
}

/* ══════════════════════════════
   RESPONSIVE
══════════════════════════════ */
@media (max-width: 1100px) {
    .hero__content {
        grid-template-columns: 1fr;
    }

    .hero__right {
        display: none;
    }

    .hero__left {
        text-align: center;
    }

    .hero__badge {
        justify-content: center;
    }

    .hero__sub {
        margin-left: auto;
        margin-right: auto;
    }

    .hero__ctas {
        justify-content: center;
    }

    .hero__scroll {
        display: none;
    }

    .why-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .car-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .blog-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .marques-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .process-grid {
        grid-template-columns: repeat(2, 1fr);
        row-gap: 64px;
    }

    .process-grid::before {
        display: none;
    }

    .testimonial-band {
        grid-template-columns: 1fr;
        gap: 2px;
    }
}

@media (max-width: 768px) {
    .hero__content {
        padding: 0 6%;
    }

    .hero h1 {
        font-size: 50px;
    }

    .car-grid {
        grid-template-columns: 1fr;
        padding: 0 5% 50px;
    }

    .blog-grid {
        grid-template-columns: 1fr;
    }

    .inventory-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .hero__footer {
        display: none;
    }

    .hero__progress {
        display: none;
    }

    .why-grid {
        grid-template-columns: 1fr;
    }

    .why-section {
        padding: 90px 6%;
        background-attachment: scroll;
    }

    .why-section::before {
        font-size: 130px;
        top: -20px;
    }

    .marques-section,
    .process-section {
        padding: 90px 6%;
    }

    .process-grid {
        grid-template-columns: 1fr;
    }

    .cta-rings {
        width: 460px;
        height: 460px;
    }

    .cta-buttons {
        flex-direction: column;
        width: 100%;
    }

    .cta-buttons .btn-primary,
    .cta-buttons .btn-ghost {
        width: 100%;
        justify-content: center;
    }

    .to-top {
        left: 16px;
        bottom: 16px;
    }
}

@media (max-width: 480px) {
    .marques-grid {
        grid-template-columns: 1fr 1fr;
    }

    .marque {
        padding: 36px 10px 32px;
    }
}

/* Respect reduced-motion preferences (NEW) */
@media (prefers-reduced-motion: reduce) {

    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }

    .reveal {
        opacity: 1;
        transform: none;
    }

    .marquee-track {
        animation: none;
    }

    html {
        scroll-behavior: auto;
    }
}
</style>

<div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>

<!-- ════════════════════════════════════
     HERO
════════════════════════════════════ -->
<section class="hero">

    <div class="hero__slides" id="heroSlides">
        <div class="hero__slide active"></div>
        <div class="hero__slide"></div>
        <div class="hero__slide"></div>
        <div class="hero__slide"></div>
        <div class="hero__slide"></div>
    </div>

    <div class="hero__overlay"></div>
    <div class="hero__glow"></div>
    <div class="hero__vignette"></div>
    <div class="hero__grain"></div>
    <canvas class="hero__dust" id="heroDust" aria-hidden="true"></canvas>
    <div class="hero__top-rule"></div>
    <div class="hero__bottom-rule"></div>

    <div class="hero__car-label" id="heroCarLabel">
        <span class="car-label__tag">Now Showcasing</span>
        <span class="car-label__name" id="heroCarName">Rolls-Royce Ghost</span>
    </div>

    <div class="hero__progress">
        <div class="hero__progress-fill" id="heroProgress"></div>
    </div>

    <a href="#featured" class="hero__scroll" aria-label="Scroll to featured vehicles">
        <span>Scroll</span>
        <span class="hero__scroll-line"></span>
    </a>

    <div class="hero__content">
        <div class="hero__left">
            <div class="hero__badge">
                <span class="hero__badge-dot"></span>
                <span class="hero__badge-line"></span>
                Private Automotive Atelier
            </div>

            <h1>
                <span class="thin">Where Mastery</span>
                <em>Meets Motion</em>
            </h1>

            <p class="hero__sub">
                A curated atelier of the world's most exceptional automobiles — assembled for those who
                understand that true luxury is not acquired, but inherited through discerning taste.
                Every vehicle, a testament to craftsmanship.
            </p>

            <div class="hero__ctas">
                <a href="<?php echo BASE_URL; ?>vehicles.php" class="btn-primary">
                    Enter the Atelier
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="<?php echo BASE_URL; ?>contact.php" class="btn-ghost">
                    <i class="fa-regular fa-calendar"></i>
                    Private Viewing
                </a>
            </div>
        </div>

        <!-- Quick search panel -->
        <div class="hero__right">
            <div class="search-panel">
                <h2 class="search-panel__title">Find Your Motorcar</h2>
                <p class="search-panel__sub">Refine the collection</p>

                <form action="<?php echo BASE_URL; ?>vehicles.php" method="get">
                    <div class="form-row">
                        <div class="form-field">
                            <label for="sp-brand">Marque</label>
                            <select id="sp-brand" name="brand">
                                <option value="">Any marque</option>
                                <?php foreach ($search_brands as $b): ?>
                                <option value="<?php echo sanitize($b); ?>"><?php echo sanitize($b); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="sp-condition">Condition</label>
                            <select id="sp-condition" name="condition">
                                <option value="">Any</option>
                                <option value="New">New</option>
                                <option value="Used">Pre-Owned</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="sp-price">Maximum Budget</label>
                        <select id="sp-price" name="max_price">
                            <option value="">No limit</option>
                            <option value="1500000">Up to KSh 1.5M</option>
                            <option value="3000000">Up to KSh 3M</option>
                            <option value="5000000">Up to KSh 5M</option>
                            <option value="10000000">Up to KSh 10M</option>
                            <option value="20000000">Up to KSh 20M</option>
                        </select>
                    </div>

                    <button type="submit" class="search-submit">
                        Search Collection
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="hero__footer">
        <div class="hero__stat">
            <div class="stat__num" data-count="300" data-suffix="+">300+</div>
            <div class="stat__label">Curated Vehicles</div>
        </div>
        <div class="hero__stat">
            <div class="stat__num" data-count="5" data-suffix="★">5★</div>
            <div class="stat__label">Client Rating</div>
        </div>
        <div class="hero__stat">
            <div class="stat__num" data-count="5" data-suffix="+">5+</div>
            <div class="stat__label">Years in Atelier</div>
        </div>
        <div class="hero__stat">
            <div class="stat__num" data-count="100" data-suffix="%">100%</div>
            <div class="stat__label">Transparent</div>
        </div>
        <div class="hero__nav">
            <button class="hero__nav-btn" id="heroPrev" aria-label="Previous">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div class="hero__counter">
                <span id="heroCurrent">01</span> / 05
            </div>
            <button class="hero__nav-btn" id="heroNext" aria-label="Next">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     FEATURED INVENTORY
════════════════════════════════════ -->
<section class="inventory-section" id="featured">

    <div class="inventory-header reveal">
        <div class="inventory-header__left">
            <div class="section-eyebrow">Hand-Selected</div>
            <h2>Featured <em>vehicles</em></h2>
            <p>Exceptional machines chosen for outstanding provenance, condition, and character.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>vehicles.php" class="header-link">
            Browse All <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="car-grid">
        <?php if (empty($featured_vehicles)): ?>
        <div class="empty-state">
            <p>Currently curating our featured collection — check back shortly.</p>
        </div>
        <?php else: ?>
        <?php foreach ($featured_vehicles as $i => $car):
            $img_src = (!empty($car['image_path']) && file_exists(__DIR__ . '/' . $car['image_path']))
                ? BASE_URL . $car['image_path']
                : BASE_URL . 'assets/images/placeholder-car.jpg';
            $badge_class = (strtolower($car['condition']) === 'new') ? 'new' : 'used';
            $has_discount = !empty($car['discount_price']);
            $display_price = $has_discount ? $car['discount_price'] : $car['price'];
        ?>
        <div class="car-card reveal" style="transition-delay: <?php echo $i * 0.08; ?>s">

            <div class="car-img-wrapper">
                <span class="condition-badge <?php echo $badge_class; ?>">
                    <?php echo sanitize($car['condition']); ?>
                </span>
                <?php if ($has_discount):
                    $pct = round((1 - $car['discount_price'] / $car['price']) * 100);
                ?>
                <span class="discount-ribbon">–<?php echo $pct; ?>%</span>
                <?php endif; ?>
                <span class="car-lot">No. <?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></span>
                <img src="<?php echo $img_src; ?>"
                    alt="<?php echo sanitize($car['year'] . ' ' . $car['brand'] . ' ' . $car['name']); ?>"
                    loading="lazy">
            </div>

            <div class="car-details">
                <div class="car-price-row">
                    <div class="car-price">KSh <?php echo number_format($display_price); ?></div>
                    <?php if ($has_discount): ?>
                    <div class="car-price-old">KSh <?php echo number_format($car['price']); ?></div>
                    <?php endif; ?>
                </div>

                <h3 class="car-title">
                    <?php echo sanitize($car['year'] . ' ' . $car['brand'] . ' ' . $car['name']); ?>
                </h3>

                <div class="car-specs">
                    <div class="spec-item">
                        <i class="fa-solid fa-gauge-high"></i>
                        <?php echo format_mileage($car['mileage']); ?>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-gears"></i>
                        <?php echo sanitize($car['transmission']); ?>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-gas-pump"></i>
                        <?php echo sanitize($car['fuel_type']); ?>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-car-side"></i>
                        <?php echo sanitize($car['drive_type']); ?>
                    </div>
                </div>

                <a href="<?php echo BASE_URL; ?>vehicle_detail.php?id=<?php echo $car['id']; ?>" class="btn-view">
                    View Details
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="view-all-wrap reveal">
        <a href="<?php echo BASE_URL; ?>vehicles.php" class="btn-outline-gold">
            View Complete Collection
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
</section>


<!-- ════════════════════════════════════
     MARQUEE STRIP
════════════════════════════════════ -->
<div class="marquee-strip">
    <div class="marquee-track">
        <?php
        $marquee_items = [
            ['fa-shield-halved',       '150-Point Certified'],
            ['fa-hand-holding-dollar', 'Flexible Financing'],
            ['fa-rotate-left',         'Trade-In Ready'],
            ['fa-headset',             'Lifetime Support'],
            ['fa-car-side',            '300+ Vehicles'],
            ['fa-star',                '5-Star Rated'],
            ['fa-file-contract',       'Transparent Pricing'],
            ['fa-clock',               'Same-Day Approval'],
        ];
        $doubled = array_merge($marquee_items, $marquee_items);
        foreach ($doubled as $item):
        ?>
        <div class="marquee-item">
            <i class="fa-solid <?php echo $item[0]; ?>"></i>
            <span><?php echo $item[1]; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>


<!-- ════════════════════════════════════
     MARQUES — BRAND WALL
════════════════════════════════════ -->
<section class="marques-section">
    <div class="reveal">
        <div class="section-eyebrow center">The Houses We Represent</div>
        <h2>Marques of <em>distinction</em></h2>
        <p class="marques-section__sub">
            From Goodwood to Maranello, every name on our floor carries a lineage worth owning.
        </p>
    </div>

    <div class="marques-grid reveal">
        <?php
        $marques = [
            ['Rolls-Royce',   'Goodwood'],
            ['Bentley',       'Crewe'],
            ['Mercedes-Benz', 'Stuttgart'],
            ['Porsche',       'Zuffenhausen'],
            ['Range Rover',   'Solihull'],
            ['BMW',           'Munich'],
            ['Ferrari',       'Maranello'],
            ['Lexus',         'Nagoya'],
        ];
        foreach ($marques as $m):
        ?>
        <a href="<?php echo BASE_URL; ?>vehicles.php?brand=<?php echo urlencode($m[0]); ?>" class="marque">
            <span class="marque__name"><?php echo $m[0]; ?></span>
            <span class="marque__origin"><?php echo $m[1]; ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</section>


<!-- ════════════════════════════════════
     WHY US — PARALLAX
════════════════════════════════════ -->
<section class="why-section">
    <div class="why-header reveal">
        <div class="section-eyebrow">The Difference</div>
        <h2>Why the <em>Discerning</em> Choose LB</h2>
    </div>

    <div class="why-grid">
        <div class="why-card reveal">
            <div class="why-card__num">01</div>
            <div class="why-card__icon"><i class="fa-solid fa-shield-halved"></i></div>
            <h4>Certified Vehicles</h4>
            <p>Every motorcar undergoes a rigorous 150-point inspection before it reaches our atelier floor —
                your confidence, guaranteed.</p>
        </div>
        <div class="why-card reveal">
            <div class="why-card__num">02</div>
            <div class="why-card__icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <h4>Flexible Financing</h4>
            <p>Competitive rates, same-day approvals, and repayment terms tailored precisely to your financial
                circumstances.</p>
        </div>
        <div class="why-card reveal">
            <div class="why-card__num">03</div>
            <div class="why-card__icon"><i class="fa-solid fa-rotate-left"></i></div>
            <h4>Trade-In Ready</h4>
            <p>Receive a fair, transparent market valuation for your current vehicle — settled in minutes, not
                days.</p>
        </div>
        <div class="why-card reveal">
            <div class="why-card__num">04</div>
            <div class="why-card__icon"><i class="fa-solid fa-headset"></i></div>
            <h4>Lifetime Support</h4>
            <p>Our relationship doesn't end at the sale. We are here for every mile of the journey that
                follows.</p>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     PROCESS — THE ACQUISITION
════════════════════════════════════ -->
<section class="process-section">
    <div class="process-header reveal">
        <div class="section-eyebrow center">How It Works</div>
        <h2>From first call to <em>first drive</em></h2>
    </div>

    <div class="process-grid">
        <div class="process-step reveal">
            <div class="process-step__node">1</div>
            <h4>Consultation</h4>
            <p>Tell us what you're looking for. An advisor builds a shortlist around your taste and budget.</p>
        </div>
        <div class="process-step reveal" style="transition-delay:0.1s">
            <div class="process-step__node">2</div>
            <h4>Private Viewing</h4>
            <p>Inspect your shortlisted cars in person, take a test drive, and review the 150-point report.</p>
        </div>
        <div class="process-step reveal" style="transition-delay:0.2s">
            <div class="process-step__node">3</div>
            <h4>Finance &amp; Trade-In</h4>
            <p>Get a same-day financing decision and a fair valuation on your current vehicle.</p>
        </div>
        <div class="process-step reveal" style="transition-delay:0.3s">
            <div class="process-step__node">4</div>
            <h4>Handover</h4>
            <p>Collect your car detailed and ready, with all paperwork complete and support on call.</p>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     TESTIMONIALS
════════════════════════════════════ -->
<section class="testimonial-band">
    <div class="testimonial-card reveal">
        <div class="testimonial-card__stars">
            <?php for ($s=0;$s<5;$s++) echo '<i class="fa-solid fa-star"></i>'; ?>
        </div>
        <p class="testimonial-card__quote">
            "The entire experience was seamless — from first enquiry to collecting the keys.
            The team's knowledge and patience made all the difference."
        </p>
        <div class="testimonial-card__author">
            <div class="testimonial-card__avatar">J</div>
            <div>
                <div class="testimonial-card__name">James Mwangi</div>
                <div class="testimonial-card__vehicle">2023 BMW X5 M-Sport</div>
            </div>
        </div>
    </div>

    <div class="testimonial-card reveal" style="transition-delay:0.1s">
        <div class="testimonial-card__stars">
            <?php for ($s=0;$s<5;$s++) echo '<i class="fa-solid fa-star"></i>'; ?>
        </div>
        <p class="testimonial-card__quote">
            "Got an honest trade-in valuation and walked away with a deal that genuinely
            exceeded my expectations. Wouldn't go anywhere else."
        </p>
        <div class="testimonial-card__author">
            <div class="testimonial-card__avatar">A</div>
            <div>
                <div class="testimonial-card__name">Aisha Kamau</div>
                <div class="testimonial-card__vehicle">2022 Mercedes GLE 350</div>
            </div>
        </div>
    </div>

    <div class="testimonial-card reveal" style="transition-delay:0.2s">
        <div class="testimonial-card__stars">
            <?php for ($s=0;$s<5;$s++) echo '<i class="fa-solid fa-star"></i>'; ?>
        </div>
        <p class="testimonial-card__quote">
            "Financing was approved same-day, the paperwork was painless, and the car was
            in perfect condition. Truly white-glove service."
        </p>
        <div class="testimonial-card__author">
            <div class="testimonial-card__avatar">D</div>
            <div>
                <div class="testimonial-card__name">David Ochieng</div>
                <div class="testimonial-card__vehicle">2024 Porsche Cayenne</div>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     JOURNAL / BLOG 
════════════════════════════════════ -->
<section class="blog-section">
    <div class="inventory-header reveal">
        <div class="inventory-header__left">
            <div class="section-eyebrow">Insights & News</div>
            <h2>The LB <em>Journal</em></h2>
            <p>Reflections on motoring, market intelligence, and expert buying guidance from our atelier.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>blog.php" class="header-link">
            View All Posts <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="blog-grid">
        <div class="blog-card reveal">
            <div class="blog-img">
                <img src="https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=800&q=80"
                    alt="Future of Luxury SUVs" loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">May 24, 2026 • Buying Guide</div>
                <h3 class="blog-title">The Future of Luxury SUVs: What to Expect This Year</h3>
                <p class="blog-excerpt">Explore the latest advancements in comfort, technology, and performance shaping
                    the next generation of premium sport utility vehicles.</p>
                <a href="<?php echo BASE_URL; ?>blog-post.php?id=1" class="blog-link">Read Article <i
                        class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <div class="blog-card reveal" style="transition-delay: 0.1s">
            <div class="blog-img">
                <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=800&q=80" alt="Car Financing"
                    loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">May 18, 2026 • Finance</div>
                <h3 class="blog-title">How to Structure the Perfect Finance Deal</h3>
                <p class="blog-excerpt">Navigating auto loans doesn't have to be complex. Our in-house financial experts
                    break down how to secure the best rates and tailored terms.</p>
                <a href="<?php echo BASE_URL; ?>blog-post.php?id=2" class="blog-link">Read Article <i
                        class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <div class="blog-card reveal" style="transition-delay: 0.2s">
            <div class="blog-img">
                <img src="https://images.unsplash.com/photo-1632733711679-529326f6db12?w=800&q=80"
                    alt="Vehicle Maintenance" loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">May 12, 2026 • Maintenance</div>
                <h3 class="blog-title">5 Essential Maintenance Checks for Pre-Owned Vehicles</h3>
                <p class="blog-excerpt">Maintain the value and longevity of your investment with these simple yet
                    critical checks recommended by our 150-point certified technicians.</p>
                <a href="<?php echo BASE_URL; ?>blog-post.php?id=3" class="blog-link">Read Article <i
                        class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     CALL TO ACTION
════════════════════════════════════ -->
<section class="cta-section reveal">
    <div class="cta-bg"></div>
    <div class="cta-overlay"></div>
    <div class="cta-rings" aria-hidden="true"></div>
    <div class="cta-content">
        <div class="cta-eyebrow">Your Journey Begins Here</div>
        <h2 class="cta-title">Ready to Experience <em>Excellence?</em></h2>
        <p class="cta-text">Whether you're looking to acquire a new masterpiece, find a certified pre-owned gem, or
            request an instant valuation for your current vehicle, the LB Autos atelier is exclusively at your
            service.</p>
        <div class="cta-buttons">
            <a href="<?php echo BASE_URL; ?>vehicles.php" class="btn-primary">
                Explore Showroom
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn-ghost">
                <i class="fa-solid fa-phone"></i>
                Speak to an Advisor
            </a>
        </div>
    </div>
</section>

<button class="to-top" id="toTop" aria-label="Back to top">
    <i class="fa-solid fa-arrow-up"></i>
</button>


<!-- ════════════════════════════════════
     SCRIPTS
════════════════════════════════════ -->
<script>
const LB_REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// ── HERO SLIDESHOW ──
(function() {
    const slides = document.querySelectorAll('.hero__slide');
    const label = document.getElementById('heroCarLabel');
    const nameEl = document.getElementById('heroCarName');
    const counter = document.getElementById('heroCurrent');
    const progress = document.getElementById('heroProgress');
    const prevBtn = document.getElementById('heroPrev');
    const nextBtn = document.getElementById('heroNext');

    const cars = [
        'Rolls-Royce Ghost',
        'Mercedes-Maybach S-Class',
        'Bentley Continental GT',
        'Range Rover Autobiography',
        'Ferrari Roma'
    ];

    let current = 0;
    let timer;

    function goTo(idx) {
        slides[current].classList.remove('active');
        label.classList.remove('visible');

        progress.classList.remove('running');
        progress.style.transition = 'none';
        progress.style.width = '0%';

        current = ((idx % slides.length) + slides.length) % slides.length;
        slides[current].classList.add('active');

        nameEl.textContent = cars[current];
        counter.textContent = String(current + 1).padStart(2, '0');

        setTimeout(() => {
            label.classList.add('visible');
            progress.style.transition = 'width 5s linear';
            progress.classList.add('running');
            progress.style.width = '100%';
        }, 80);
    }

    function next() {
        goTo(current + 1);
    }

    function prev() {
        goTo(current - 1);
    }

    function start() {
        timer = setInterval(next, 5500);
    }

    function reset() {
        clearInterval(timer);
        start();
    }

    nextBtn.addEventListener('click', () => {
        reset();
        next();
    });
    prevBtn.addEventListener('click', () => {
        reset();
        prev();
    });

    label.classList.add('visible');
    setTimeout(() => {
        progress.style.transition = 'width 5s linear';
        progress.classList.add('running');
        progress.style.width = '100%';
    }, 100);
    start();
})();

// ── SCROLL REVEAL ──
(function() {
    const els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
        els.forEach(el => el.classList.add('visible'));
        return;
    }
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                io.unobserve(e.target);
            }
        });
    }, {
        threshold: 0.1
    });
    els.forEach(el => io.observe(el));
})();

// ── SCROLL PROGRESS + BACK TO TOP ──
(function() {
    const bar = document.getElementById('scrollProgress');
    const toTop = document.getElementById('toTop');
    let ticking = false;

    function update() {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        const y = window.scrollY || window.pageYOffset;
        bar.style.transform = 'scaleX(' + (max > 0 ? Math.min(y / max, 1) : 0) + ')';
        toTop.classList.toggle('show', y > 700);
        ticking = false;
    }

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(update);
            ticking = true;
        }
    }, {
        passive: true
    });

    toTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: LB_REDUCED_MOTION ? 'auto' : 'smooth'
        });
    });
    update();
})();

// ── HERO DUST PARTICLES ──
(function() {
    const canvas = document.getElementById('heroDust');
    if (!canvas || LB_REDUCED_MOTION) return;
    const ctx = canvas.getContext('2d');
    const hero = canvas.parentElement;
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    let w = 0,
        h = 0,
        particles = [],
        running = true,
        raf;

    function make(initial) {
        return {
            x: Math.random() * w,
            y: initial ? Math.random() * h : h + 10,
            r: Math.random() * 1.5 + 0.4,
            vy: Math.random() * 0.35 + 0.12,
            vx: (Math.random() - 0.5) * 0.15,
            a: Math.random() * 0.5 + 0.15,
            t: Math.random() * Math.PI * 2
        };
    }

    function resize() {
        const r = hero.getBoundingClientRect();
        w = r.width;
        h = r.height;
        canvas.width = w * dpr;
        canvas.height = h * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        const count = Math.round(Math.min(60, w / 22));
        particles = Array.from({
            length: count
        }, () => make(true));
    }

    function frame() {
        if (!running) return;
        ctx.clearRect(0, 0, w, h);
        for (const p of particles) {
            p.t += 0.01;
            p.y -= p.vy;
            p.x += p.vx + Math.sin(p.t) * 0.12;
            if (p.y < -10) Object.assign(p, make(false));
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(212,147,90,' + p.a + ')';
            ctx.fill();
        }
        raf = requestAnimationFrame(frame);
    }

    resize();
    window.addEventListener('resize', resize);

    // Only animate while the hero is on screen
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(entries => {
            running = entries[0].isIntersecting;
            if (running) {
                cancelAnimationFrame(raf);
                frame();
            }
        }).observe(hero);
    }
    frame();
})();

// ── HERO MOUSE PARALLAX ──
(function() {
    const hero = document.querySelector('.hero');
    const slides = document.getElementById('heroSlides');
    if (!hero || !slides || LB_REDUCED_MOTION || !window.matchMedia('(hover: hover)').matches) return;

    hero.addEventListener('mousemove', e => {
        const r = hero.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - 0.5;
        const y = (e.clientY - r.top) / r.height - 0.5;
        slides.style.setProperty('--px', (x * -18).toFixed(1) + 'px');
        slides.style.setProperty('--py', (y * -12).toFixed(1) + 'px');
    });
    hero.addEventListener('mouseleave', () => {
        slides.style.setProperty('--px', '0px');
        slides.style.setProperty('--py', '0px');
    });
})();

// ── CURSOR SPOTLIGHT ON CARDS ──
(function() {
    if (!window.matchMedia('(hover: hover)').matches) return;
    document.querySelectorAll('.car-card, .why-card').forEach(card => {
        card.addEventListener('mousemove', e => {
            const r = card.getBoundingClientRect();
            card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
            card.style.setProperty('--my', (e.clientY - r.top) + 'px');
        });
    });
})();

// ── HERO STATS COUNT-UP ──
(function() {
    const nums = document.querySelectorAll('.stat__num[data-count]');
    if (!nums.length || LB_REDUCED_MOTION || !('IntersectionObserver' in window)) return;

    function run(el) {
        const target = parseInt(el.dataset.count, 10);
        const suffix = el.dataset.suffix || '';
        const duration = 1600;
        const start = performance.now();

        function tick(now) {
            const t = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - t, 3);
            el.textContent = Math.round(target * eased) + suffix;
            if (t < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                // wait for the footer's fade-in before counting
                setTimeout(() => run(e.target), 700);
                io.unobserve(e.target);
            }
        });
    }, {
        threshold: 0.4
    });
    nums.forEach(n => io.observe(n));
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>