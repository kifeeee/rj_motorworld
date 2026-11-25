<?php

// includes/header.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

$site_name      = get_site_setting($pdo, 'company_name',   'LB Autos');
$company_phone  = get_site_setting($pdo, 'company_phone',  'Call Us Today');
$site_logo      = get_site_setting($pdo, 'site_logo',      '');
$business_hours = get_site_setting($pdo, 'business_hours', 'Mon – Sat: 9:00 AM – 7:00 PM');

$logo_url = (!empty($site_logo) && file_exists(__DIR__ . '/../' . $site_logo))
    ? BASE_URL . sanitize($site_logo)
    : '';

// ── Active page detection (detail pages highlight their parent link) ──
$current_page = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$page_parent  = [
    'blog-post.php'      => 'blog.php',
    'vehicle_detail.php' => 'vehicles.php',
];
if (isset($page_parent[$current_page])) {
    $current_page = $page_parent[$current_page];
}

$nav_items = [
    ['index.php',      'Home'],
    ['vehicles.php',   'Showroom'],
    ['financing.php',  'Financing'],
    ['sell_trade.php', 'Sell / Trade'],
    ['blog.php',       'Journal'],
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a120e">
    <title><?php echo sanitize($site_name); ?> | Private Automotive Atelier</title>

    <?php if ($logo_url): ?>
    <link rel="icon" type="image/png" href="<?php echo $logo_url; ?>">
    <link rel="shortcut icon" href="<?php echo $logo_url; ?>">
    <link rel="apple-touch-icon" href="<?php echo $logo_url; ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500&family=Manrope:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    :root {
        /* Sovereign Luxury Palette */
        --forest-deep: #0a120e;
        --forest-dark: #0f1a15;
        --forest-mid: #182820;
        --forest-soft: #23382d;

        --gold-metallic: linear-gradient(135deg, #d4af37 0%, #edd581 50%, #aa7c11 100%);
        --copper-metallic: linear-gradient(135deg, #c88346 0%, #f1be8b 50%, #9e5b22 100%);
        --copper-light: #e2a76f;
        --copper-bright: #f5cb98;
        --copper-subtle: rgba(200, 131, 70, 0.18);

        --cream: #f9f6f0;
        --sage-light: #b2c4b8;
        --sage-muted: #7f9988;

        --ease-luxury: cubic-bezier(0.16, 1, 0.3, 1);
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    html {
        scroll-behavior: smooth;
        /* keeps #anchors from hiding under the sticky navbar */
        scroll-padding-top: 96px;
    }

    body {
        font-family: 'Manrope', sans-serif;
        background: var(--forest-deep);
        color: var(--cream);
    }

    body.menu-open {
        overflow: hidden;
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    ::selection {
        background: #c88346;
        color: #0a120e;
    }

    .navbar a:focus-visible,
    .top-bar a:focus-visible,
    .nav-toggle:focus-visible {
        outline: 2px solid var(--copper-light);
        outline-offset: 3px;
        border-radius: 3px;
    }

    /* ══════════════════════════════════════
       1. TOP UTILITY BAR
    ══════════════════════════════════════ */
    .top-bar {
        background: #060b09;
        border-bottom: 1px solid rgba(226, 167, 111, 0.15);
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 6%;
        font-size: 11px;
        letter-spacing: 0.08em;
        color: var(--sage-muted);
        position: relative;
        z-index: 1001;
    }

    /* Metallic top edge with a slow travelling highlight */
    .top-bar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg,
                rgba(200, 131, 70, 0.15) 0%,
                rgba(200, 131, 70, 0.4) 25%,
                var(--copper-bright) 50%,
                rgba(200, 131, 70, 0.4) 75%,
                rgba(200, 131, 70, 0.15) 100%);
        background-size: 200% 100%;
        animation: edgeShimmer 9s linear infinite;
    }

    .top-bar__group {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .top-bar__item {
        display: flex;
        align-items: center;
        gap: 7px;
        transition: color 0.3s var(--ease-luxury);
    }

    .top-bar__item i {
        color: var(--copper-light);
        font-size: 11px;
    }

    .top-bar__phone,
    .top-bar__link {
        color: var(--sage-light);
        font-weight: 600;
        transition: color 0.3s var(--ease-luxury);
    }

    .top-bar__phone:hover,
    .top-bar__link:hover {
        color: var(--copper-bright);
    }

    .top-bar__sep {
        width: 1px;
        height: 12px;
        background: rgba(255, 255, 255, 0.1);
    }

    /* ══════════════════════════════════════
       2. MAIN NAVBAR & GLASSMORPHISM
    ══════════════════════════════════════ */
    .navbar {
        position: sticky;
        top: 0;
        z-index: 1000;
        height: 82px;
        background: rgba(10, 18, 14, 0.82);
        backdrop-filter: blur(16px) saturate(1.2);
        -webkit-backdrop-filter: blur(16px) saturate(1.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 6%;
        border-bottom: 1px solid rgba(226, 167, 111, 0.12);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        transition: height 0.4s var(--ease-luxury), background 0.4s var(--ease-luxury), box-shadow 0.4s var(--ease-luxury);
    }

    /* Copper hairline along the bottom edge that glides left to right */
    .navbar::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: -1px;
        height: 1px;
        pointer-events: none;
        background: linear-gradient(90deg,
                transparent 0%,
                rgba(200, 131, 70, 0.55) 30%,
                var(--copper-bright) 50%,
                rgba(200, 131, 70, 0.55) 70%,
                transparent 100%);
        background-size: 220% 100%;
        animation: edgeShimmer 7s linear infinite;
        opacity: 0.7;
    }

    /* Compact "scrolled" state, toggled by JS */
    .navbar.scrolled {
        height: 68px;
        background: rgba(8, 14, 11, 0.94);
        box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55);
    }

    /* ══════════════════════════════════════
       3. BRAND / LOGO
    ══════════════════════════════════════ */
    .brand {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        position: relative;
        z-index: 2;
    }

    .brand img {
        height: 110px;
        width: auto;
        display: block;
        margin-top: 10px;
        /* Slight drop offset for rich overflow */
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
        transition: transform 0.4s var(--ease-luxury), height 0.4s var(--ease-luxury), opacity 0.3s ease;
    }

    .navbar.scrolled .brand img {
        height: 88px;
    }

    .brand:hover img {
        transform: scale(1.03);
    }

    /* Typography Fallback */
    .brand__text {
        font-family: 'Fraunces', serif;
        font-size: 26px;
        font-weight: 500;
        letter-spacing: 0.01em;
        background: linear-gradient(100deg, #c88346 0%, #f5cb98 40%, #e2a76f 60%, #c88346 100%);
        background-size: 220% auto;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1.1;
        animation: textSheen 8s linear infinite;
    }

    .brand__sub {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Manrope', sans-serif;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: 0.35em;
        text-transform: uppercase;
        color: var(--sage-muted);
        margin-top: 6px;
    }

    .brand__sub::before {
        content: '';
        width: 22px;
        height: 1px;
        background: var(--copper-light);
        opacity: 0.7;
        transition: width 0.45s var(--ease-luxury);
    }

    .brand:hover .brand__sub::before {
        width: 38px;
    }

    /* ══════════════════════════════════════
       4. NAVIGATION LINKS
    ══════════════════════════════════════ */
    .nav-links {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .nav-links a {
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--sage-light);
        padding: 10px 16px;
        border-radius: 4px;
        position: relative;
        transition: all 0.3s var(--ease-luxury);
    }

    /* Subtle link hover pill */
    .nav-links a:not(.nav-cta):hover {
        color: var(--cream);
        background: rgba(226, 167, 111, 0.08);
    }

    .nav-links a:not(.nav-cta)::after {
        content: '';
        position: absolute;
        bottom: 6px;
        left: 50%;
        width: 0%;
        height: 1.5px;
        background: var(--copper-light);
        transition: all 0.3s var(--ease-luxury);
        transform: translateX(-50%);
        border-radius: 2px;
    }

    .nav-links a:not(.nav-cta):hover::after {
        width: 40%;
    }

    /* Current page: persistent underline with a glowing dot */
    .nav-links a.active:not(.nav-cta) {
        color: var(--copper-bright);
    }

    .nav-links a.active:not(.nav-cta)::after {
        width: 40%;
        background: linear-gradient(90deg, transparent, var(--copper-bright), transparent);
        height: 1.5px;
    }

    .nav-links a.active:not(.nav-cta)::before {
        content: '';
        position: absolute;
        top: 6px;
        left: 50%;
        width: 4px;
        height: 4px;
        margin-left: -2px;
        border-radius: 50%;
        background: var(--copper-light);
        box-shadow: 0 0 10px var(--copper-light);
    }

    /* Metallic CTA Button */
    .nav-cta {
        background: var(--copper-metallic) !important;
        color: #0d120f !important;
        font-weight: 800 !important;
        letter-spacing: 0.14em !important;
        padding: 12px 26px !important;
        border-radius: 2px !important;
        margin-left: 12px;
        box-shadow: 0 4px 20px rgba(200, 131, 70, 0.25);
        position: relative;
        overflow: hidden;
        transition: all 0.3s var(--ease-luxury) !important;
        animation: ctaGlow 4.5s ease-in-out infinite;
    }

    .nav-cta::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.6s var(--ease-luxury);
    }

    .nav-cta:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(200, 131, 70, 0.45);
        animation: none;
    }

    .nav-cta:hover::before {
        left: 100%;
    }

    /* ══════════════════════════════════════
       5. MOBILE TOGGLE & RESPONSIVE
    ══════════════════════════════════════ */
    .nav-toggle {
        display: none;
        flex-direction: column;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        background: none;
        border: none;
        padding: 8px;
        z-index: 10;
    }

    .nav-toggle span {
        display: block;
        width: 24px;
        height: 1.5px;
        background: var(--cream);
        transition: transform 0.4s var(--ease-luxury), opacity 0.25s, background 0.3s;
    }

    .nav-toggle:hover span {
        background: var(--copper-bright);
    }

    /* Hamburger morphs into a close icon */
    .nav-toggle.open span:nth-child(1) {
        transform: translateY(7.5px) rotate(45deg);
    }

    .nav-toggle.open span:nth-child(2) {
        opacity: 0;
        transform: scaleX(0.2);
    }

    .nav-toggle.open span:nth-child(3) {
        transform: translateY(-7.5px) rotate(-45deg);
    }

    /* Dim backdrop behind the open mobile menu */
    .nav-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(4, 8, 6, 0.6);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.35s var(--ease-luxury), visibility 0.35s;
        z-index: 999;
    }

    .nav-backdrop.show {
        opacity: 1;
        visibility: visible;
    }

    @media (max-width: 992px) {
        .top-bar {
            display: none;
        }

        .nav-toggle {
            display: flex;
        }

        .nav-links {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(10, 18, 14, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            flex-direction: column;
            padding: 24px;
            gap: 8px;
            border-bottom: 1px solid rgba(226, 167, 111, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s var(--ease-luxury);
        }

        .nav-links.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .nav-links a {
            width: 100%;
            text-align: center;
            padding: 14px;
            font-size: 13px;
        }

        /* Links glide in one after another */
        .nav-links.open a {
            animation: navItemIn 0.55s var(--ease-luxury) both;
            animation-delay: calc(var(--i, 0) * 60ms + 80ms);
        }

        /* On mobile the active page gets a side accent instead of an underline */
        .nav-links a.active:not(.nav-cta) {
            background: rgba(226, 167, 111, 0.07);
        }

        .nav-links a.active:not(.nav-cta)::before {
            top: 50%;
            left: 22px;
            margin-top: -2px;
        }

        .nav-links a.active:not(.nav-cta)::after {
            display: none;
        }

        .nav-cta {
            margin-left: 0;
            margin-top: 10px;
        }
    }

    @media (max-width: 576px) {
        .navbar {
            height: 72px;
        }

        .navbar.scrolled {
            height: 62px;
        }

        .brand img {
            height: 85px;
        }

        .navbar.scrolled .brand img {
            height: 70px;
        }

        .brand__text {
            font-size: 22px;
        }
    }

    /* ══════════════════════════════════════
       6. KEYFRAMES
    ══════════════════════════════════════ */
    @keyframes edgeShimmer {
        from {
            background-position: 0% 0;
        }

        to {
            background-position: -200% 0;
        }
    }

    @keyframes textSheen {
        from {
            background-position: 0% center;
        }

        to {
            background-position: 220% center;
        }
    }

    @keyframes ctaGlow {

        0%,
        100% {
            box-shadow: 0 4px 20px rgba(200, 131, 70, 0.25);
        }

        50% {
            box-shadow: 0 4px 34px rgba(226, 167, 111, 0.5), 0 0 0 5px rgba(200, 131, 70, 0.08);
        }
    }

    @keyframes navItemIn {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {
            animation: none !important;
            transition-duration: 0.01ms !important;
        }

        html {
            scroll-behavior: auto;
        }
    }
    </style>
</head>

<body>
    <?php include_once __DIR__ . '/loader.php'; ?>

    <!-- Top Utility Bar -->
    <div class="top-bar">
        <div class="top-bar__group">
            <div class="top-bar__item">
                <i class="fa-regular fa-clock"></i>
                <span><?php echo sanitize($business_hours); ?></span>
            </div>
        </div>
        <div class="top-bar__group">
            <div class="top-bar__item">
                <i class="fa-solid fa-phone-flip"></i>
                <a class="top-bar__phone" href="tel:<?php echo preg_replace('/[^0-9+]/', '', $company_phone); ?>">
                    <?php echo sanitize($company_phone); ?>
                </a>
            </div>
            <div class="top-bar__sep"></div>
            <div class="top-bar__item">
                <i class="fa-solid fa-location-dot"></i>
                <a class="top-bar__link" href="<?php echo BASE_URL; ?>contact.php">Find Us</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar" id="navbar">

        <a href="<?php echo BASE_URL; ?>index.php" class="brand" aria-label="Home">
            <?php if ($logo_url): ?>
            <img src="<?php echo $logo_url; ?>" alt="<?php echo sanitize($site_name); ?>">
            <?php else: ?>
            <div>
                <div class="brand__text"><?php echo sanitize($site_name); ?></div>
                <div class="brand__sub">Private Automotive Atelier</div>
            </div>
            <?php endif; ?>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false"
            aria-controls="navLinks">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="nav-links" id="navLinks">
            <?php foreach ($nav_items as $n => $item): ?>
            <a href="<?php echo BASE_URL . $item[0]; ?>" style="--i:<?php echo $n; ?>"
                class="<?php echo ($current_page === $item[0]) ? 'active' : ''; ?>"
                <?php echo ($current_page === $item[0]) ? 'aria-current="page"' : ''; ?>>
                <?php echo $item[1]; ?>
            </a>
            <?php endforeach; ?>
            <a href="<?php echo BASE_URL; ?>contact.php" class="nav-cta"
                style="--i:<?php echo count($nav_items); ?>">Inquire</a>
        </div>

    </nav>

    <div class="nav-backdrop" id="navBackdrop"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.getElementById('navbar');
        const navToggle = document.getElementById('navToggle');
        const navLinks = document.getElementById('navLinks');
        const backdrop = document.getElementById('navBackdrop');

        // ── Mobile menu ──
        function setMenu(open) {
            navLinks.classList.toggle('open', open);
            navToggle.classList.toggle('open', open);
            backdrop.classList.toggle('show', open);
            document.body.classList.toggle('menu-open', open);
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        if (navToggle && navLinks) {
            navToggle.addEventListener('click', function() {
                setMenu(!navLinks.classList.contains('open'));
            });
            backdrop.addEventListener('click', function() {
                setMenu(false);
            });
            navLinks.querySelectorAll('a').forEach(function(a) {
                a.addEventListener('click', function() {
                    setMenu(false);
                });
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') setMenu(false);
            });
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) setMenu(false);
            });
        }

        // ── Compact navbar once the page scrolls ──
        let ticking = false;

        function onScroll() {
            navbar.classList.toggle('scrolled', window.scrollY > 40);
            ticking = false;
        }
        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(onScroll);
                ticking = true;
            }
        }, {
            passive: true
        });
        onScroll();
    });
    </script>
    <?php /* <body> stays open on purpose: page content follows, and footer.php should close </body></html>. */ ?>