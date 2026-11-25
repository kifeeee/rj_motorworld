<?php
// index.php
require_once __DIR__ . '/includes/header.php';

$query = "SELECT v.*, vi.image_path 
          FROM vehicles v 
          LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_main = 1 
          WHERE v.is_featured = 1 AND v.availability_status = 'Available' 
          ORDER BY v.created_at DESC LIMIT 6";
$featured_vehicles = $pdo->query($query)->fetchAll();
?>

<style>
/* ─────────────────────────────────────────
   INDEX PAGE — Luxury Automotive Aesthetic
   Palette: obsidian · gold · pearl
   Fonts:   Cormorant Garamond + DM Sans
───────────────────────────────────────── */

:root {
    --obsidian: #080808;
    --ink: #111111;
    --pitch: #181818;
    --steel: #252525;
    --dim: #666;
    --mist: #999;
    --silver: #c8c8c8;
    --pearl: #f0ede8;
    --white: #fafafa;
    --gold: #c09a5a;
    --gold-light: #d4b47a;
    --gold-dim: rgba(192, 154, 90, 0.15);
    --red: #b91c1c;
    --ease: cubic-bezier(0.4, 0, 0.2, 1);
}

/* ── HERO ── */
.hero {
    position: relative;
    min-height: 92vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
    background: var(--obsidian);
    padding: 120px 5% 100px;
}

/* ── HERO SLIDESHOW ── */
.hero__slides {
    position: absolute;
    inset: 0;
    z-index: 0;
}

.hero__slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transform: scale(1.06);
    transition: opacity 1.6s cubic-bezier(0.4, 0, 0.2, 1),
        transform 8s cubic-bezier(0.2, 0, 0, 1);
}

.hero__slide.active {
    opacity: 0.48;
    transform: scale(1.0);
}

/* Individual slide images — Unsplash free CDN */
.hero__slide:nth-child(1) {
    background-image: url('https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=1800&q=80');
    /* Lamborghini Aventador */
}

.hero__slide:nth-child(2) {
    background-image: url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1800&q=80');
    /* Ferrari */
}

.hero__slide:nth-child(3) {
    background-image: url('https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?w=1800&q=80');
    /* Bugatti / supercar */
}

.hero__slide:nth-child(4) {
    background-image: url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1800&q=80');
    /* Lamborghini side */
}

.hero__slide:nth-child(5) {
    background-image: url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1800&q=80&fit=crop&crop=entropy');
    /* Exotic sports car */
}

/* Slide label — car name bottom-left */
.hero__slide-label {
    position: absolute;
    bottom: 32px;
    left: 5%;
    z-index: 3;
    display: flex;
    align-items: center;
    gap: 10px;
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.6s 0.4s var(--ease), transform 0.6s 0.4s var(--ease);
}

.hero__slide-label.visible {
    opacity: 1;
    transform: none;
}

.hero__slide-label span {
    font-family: 'DM Sans', sans-serif;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.5);
}

.hero__slide-label strong {
    font-family: 'Cormorant Garamond', serif;
    font-size: 15px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.85);
    letter-spacing: 0.04em;
}

/* Dot navigation */
.hero__dots {
    position: absolute;
    bottom: 38px;
    right: 5%;
    z-index: 3;
    display: flex;
    gap: 8px;
    align-items: center;
}

.hero__dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    cursor: pointer;
    transition: background 0.3s, width 0.3s var(--ease);
    border: none;
    padding: 0;
}

.hero__dot.active {
    background: var(--gold);
    width: 20px;
    border-radius: 3px;
}

/* Gradient vignette over the image */
.hero__overlay {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 80% 60% at 50% 40%, transparent 0%, rgba(8, 8, 8, 0.7) 100%),
        linear-gradient(to bottom, rgba(8, 8, 8, 0.3) 0%, rgba(8, 8, 8, 0.85) 100%);
}

/* Decorative thin gold rule */
.hero__rule {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, var(--gold) 30%, var(--gold) 70%, transparent 100%);
    opacity: 0.6;
}

.hero__content {
    position: relative;
    z-index: 2;
    max-width: 860px;
}

.hero__eyebrow {
    font-family: 'DM Sans', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    animation: fadeUp 0.8s var(--ease) both;
}

.hero__eyebrow::before,
.hero__eyebrow::after {
    content: '';
    display: block;
    width: 40px;
    height: 1px;
    background: var(--gold);
    opacity: 0.5;
}

.hero h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(42px, 7vw, 88px);
    font-weight: 600;
    line-height: 1.0;
    color: #fff;
    letter-spacing: -0.01em;
    margin-bottom: 24px;
    animation: fadeUp 0.9s 0.1s var(--ease) both;
}

.hero h1 em {
    font-style: italic;
    color: var(--gold-light);
}

.hero__sub {
    font-family: 'DM Sans', sans-serif;
    font-size: 16px;
    color: rgba(255, 255, 255, 0.55);
    line-height: 1.7;
    max-width: 520px;
    margin: 0 auto 52px;
    font-weight: 300;
    letter-spacing: 0.01em;
    animation: fadeUp 0.9s 0.2s var(--ease) both;
}

/* ── SEARCH BAR ── */
.search-bar {
    background: rgba(255, 255, 255, 0.04);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 10px 10px 10px 10px;
    border-radius: 6px;
    display: flex;
    gap: 8px;
    max-width: 720px;
    margin: 0 auto;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(192, 154, 90, 0.12);
    animation: fadeUp 0.9s 0.3s var(--ease) both;
}

.search-bar select,
.search-bar input {
    flex: 1;
    padding: 13px 16px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    color: #fff;
    letter-spacing: 0.02em;
    appearance: none;
    -webkit-appearance: none;
    transition: border-color 0.2s var(--ease), background 0.2s;
}

.search-bar select option {
    background: #1a1a1a;
    color: #fff;
}

.search-bar select:focus,
.search-bar input:focus {
    outline: none;
    border-color: var(--gold);
    background: rgba(255, 255, 255, 0.1);
}

.search-bar select::placeholder,
.search-bar input::placeholder {
    color: rgba(255, 255, 255, 0.35);
}

.search-btn {
    background: var(--gold);
    color: var(--obsidian);
    padding: 13px 28px;
    border: none;
    border-radius: 4px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.2s var(--ease), transform 0.15s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-btn:hover {
    background: var(--gold-light);
    transform: translateY(-1px);
}

/* Hero stats strip */
.hero__stats {
    position: relative;
    z-index: 2;
    display: flex;
    gap: 48px;
    justify-content: center;
    margin-top: 56px;
    animation: fadeUp 0.9s 0.4s var(--ease) both;
}

.stat {
    text-align: center;
}

.stat__num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 34px;
    font-weight: 700;
    color: var(--gold-light);
    line-height: 1;
}

.stat__label {
    font-size: 10.5px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.35);
    margin-top: 4px;
}

.stat__sep {
    width: 1px;
    background: rgba(255, 255, 255, 0.1);
    align-self: stretch;
}

/* ── SECTION HEADER ── */
.section-header {
    padding: 90px 5% 50px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
}

.section-header__left {}

.section-eyebrow {
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-eyebrow::before {
    content: '';
    display: block;
    width: 28px;
    height: 1px;
    background: var(--gold);
}

.section-header h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(28px, 4vw, 46px);
    font-weight: 600;
    color: var(--ink);
    letter-spacing: -0.01em;
    line-height: 1.1;
    margin: 0;
}

.section-header p {
    font-size: 14.5px;
    color: var(--mist);
    margin-top: 8px;
    font-weight: 300;
    max-width: 360px;
}

.section-header__link {
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--gold);
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    padding-bottom: 4px;
    border-bottom: 1px solid rgba(192, 154, 90, 0.3);
    transition: gap 0.2s var(--ease), border-color 0.2s;
}

.section-header__link:hover {
    gap: 14px;
    border-color: var(--gold);
}

/* ── CAR GRID ── */
.car-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 2px;
    padding: 0 5% 80px;
}

/* ── CAR CARD ── */
.car-card {
    background: #fff;
    position: relative;
    overflow: hidden;
    border: 1px solid #ece8e2;
    transition: box-shadow 0.3s var(--ease), transform 0.3s var(--ease);
}

.car-card::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--gold), var(--gold-light));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.35s var(--ease);
    z-index: 2;
}

.car-card:hover {
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
    z-index: 1;
}

.car-card:hover::before {
    transform: scaleX(1);
}

/* Image */
.car-img-wrapper {
    position: relative;
    height: 230px;
    background: var(--pearl);
    overflow: hidden;
}

.car-img-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s var(--ease);
}

.car-card:hover .car-img-wrapper img {
    transform: scale(1.04);
}

/* Condition badge */
.condition-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    padding: 5px 11px;
    border-radius: 2px;
    z-index: 1;
}

.condition-badge.new {
    background: var(--gold);
    color: var(--obsidian);
}

.condition-badge.used {
    background: rgba(0, 0, 0, 0.72);
    color: var(--silver);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
}

/* Discount ribbon */
.discount-ribbon {
    position: absolute;
    top: 14px;
    right: 14px;
    background: var(--red);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    padding: 4px 9px;
    border-radius: 2px;
    z-index: 1;
}

/* Card body */
.car-details {
    padding: 22px 22px 20px;
}

.car-price-row {
    display: flex;
    align-items: baseline;
    gap: 10px;
    margin-bottom: 6px;
}

.car-price {
    font-family: 'Cormorant Garamond', serif;
    font-size: 30px;
    font-weight: 700;
    color: var(--ink);
    line-height: 1;
}

.car-price-old {
    font-size: 13px;
    color: var(--mist);
    text-decoration: line-through;
    font-weight: 400;
}

.car-title {
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: #222;
    margin: 0 0 18px;
    letter-spacing: 0.01em;
}

/* Specs */
.car-specs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 14px;
    padding-bottom: 18px;
    margin-bottom: 18px;
    border-bottom: 1px solid #ece8e2;
}

.spec-item {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12.5px;
    color: #555;
    font-weight: 400;
}

.spec-item i {
    color: var(--gold);
    font-size: 11px;
    width: 14px;
    flex-shrink: 0;
}

/* CTA */
.btn-view {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 13px 18px;
    border: 1px solid #ece8e2;
    border-radius: 3px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--ink);
    transition: background 0.2s var(--ease), border-color 0.2s, color 0.2s;
}

.btn-view i {
    font-size: 11px;
    transition: transform 0.2s var(--ease);
}

.btn-view:hover {
    background: var(--ink);
    border-color: var(--ink);
    color: #fff;
}

.btn-view:hover i {
    transform: translateX(4px);
}

/* ── EMPTY STATE ── */
.empty-state {
    grid-column: 1 / -1;
    padding: 80px 20px;
    text-align: center;
}

.empty-state p {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    color: var(--mist);
}

/* ── VIEW ALL CTA ── */
.view-all-wrap {
    text-align: center;
    padding: 0 5% 100px;
}

.btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 40px;
    border: 1px solid var(--ink);
    border-radius: 3px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--ink);
    transition: background 0.25s var(--ease), color 0.25s;
}

.btn-outline i {
    font-size: 11px;
    transition: transform 0.2s var(--ease);
}

.btn-outline:hover {
    background: var(--ink);
    color: #fff;
}

.btn-outline:hover i {
    transform: translateX(5px);
}

/* ── WHY US STRIP ── */
.why-strip {
    background: var(--obsidian);
    padding: 70px 5%;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px 24px;
    border-top: 1px solid #1a1a1a;
    border-bottom: 1px solid #1a1a1a;
}

.why-item {
    text-align: center;
}

.why-icon {
    width: 48px;
    height: 48px;
    border: 1px solid rgba(192, 154, 90, 0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
}

.why-icon i {
    color: var(--gold);
    font-size: 17px;
}

.why-item h4 {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #fff;
    margin-bottom: 8px;
}

.why-item p {
    font-size: 13px;
    color: var(--dim);
    line-height: 1.65;
    font-weight: 300;
}

/* ── KEYFRAMES ── */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(22px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ── SCROLL REVEAL ── */
.reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s var(--ease), transform 0.7s var(--ease);
}

.reveal.visible {
    opacity: 1;
    transform: none;
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .hero {
        min-height: 80vh;
        padding: 100px 5% 70px;
    }

    .hero__stats {
        gap: 24px;
    }

    .search-bar {
        flex-direction: column;
    }

    .car-grid {
        grid-template-columns: 1fr;
        padding: 0 4% 60px;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .why-strip {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .hero__stats {
        flex-direction: column;
        gap: 16px;
    }

    .stat__sep {
        display: none;
    }

    .why-strip {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- ════════════════════════════════════
     HERO
════════════════════════════════════ -->
<section class="hero">

    <!-- Slideshow layers -->
    <div class="hero__slides">
        <div class="hero__slide active"></div>
        <div class="hero__slide"></div>
        <div class="hero__slide"></div>
        <div class="hero__slide"></div>
        <div class="hero__slide"></div>
    </div>

    <div class="hero__overlay"></div>
    <div class="hero__rule"></div>

    <!-- Car name label (bottom-left) -->
    <div class="hero__slide-label" id="heroLabel">
        <span>Now Showing</span>
        <strong id="heroLabelText">Lamborghini Aventador</strong>
    </div>

    <!-- Dot navigation (bottom-right) -->
    <div class="hero__dots" id="heroDots">
        <button class="hero__dot active" data-index="0"></button>
        <button class="hero__dot" data-index="1"></button>
        <button class="hero__dot" data-index="2"></button>
        <button class="hero__dot" data-index="3"></button>
        <button class="hero__dot" data-index="4"></button>
    </div>

    <div class="hero__content">
        <div class="hero__eyebrow">Premium Auto Dealership</div>

        <h1>Find Your <em>Perfect</em><br>Drive</h1>

        <p class="hero__sub">
            Curated new and pre-owned vehicles with transparent pricing
            and white-glove service from first look to final handover.
        </p>

        <form class="search-bar" action="vehicles.php" method="GET">
            <select name="condition">
                <option value="">Any Condition</option>
                <option value="New">New</option>
                <option value="Used">Used</option>
            </select>
            <select name="body_type">
                <option value="">Any Body Type</option>
                <option value="SUV">SUV</option>
                <option value="Sedan">Sedan</option>
                <option value="Truck">Truck</option>
                <option value="Coupe">Coupe</option>
            </select>
            <button type="submit" class="search-btn">
                <i class="fa-solid fa-magnifying-glass"></i>
                Search Fleet
            </button>
        </form>
    </div>

    <div class="hero__stats">
        <div class="stat">
            <div class="stat__num">300+</div>
            <div class="stat__label">Vehicles in Stock</div>
        </div>
        <div class="stat__sep"></div>
        <div class="stat">
            <div class="stat__num">5★</div>
            <div class="stat__label">Customer Rating</div>
        </div>
        <div class="stat__sep"></div>
        <div class="stat">
            <div class="stat__num">15+</div>
            <div class="stat__label">Years in Business</div>
        </div>
        <div class="stat__sep"></div>
        <div class="stat">
            <div class="stat__num">100%</div>
            <div class="stat__label">Transparent Pricing</div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════
     WHY US STRIP
════════════════════════════════════ -->
<div class="why-strip">
    <div class="why-item reveal">
        <div class="why-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h4>Certified Vehicles</h4>
        <p>Every car undergoes a rigorous 150-point inspection before it reaches our floor.</p>
    </div>
    <div class="why-item reveal">
        <div class="why-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
        <h4>Flexible Financing</h4>
        <p>Competitive rates, same-day approvals, and terms tailored to your budget.</p>
    </div>
    <div class="why-item reveal">
        <div class="why-icon"><i class="fa-solid fa-rotate-left"></i></div>
        <h4>Trade-In Ready</h4>
        <p>Get a fair market valuation for your current vehicle in minutes.</p>
    </div>
    <div class="why-item reveal">
        <div class="why-icon"><i class="fa-solid fa-headset"></i></div>
        <h4>Lifetime Support</h4>
        <p>Our relationship doesn't end at the sale — we're here for the road ahead.</p>
    </div>
</div>


<!-- ════════════════════════════════════
     FEATURED INVENTORY
════════════════════════════════════ -->
<div class="section-header reveal">
    <div class="section-header__left">
        <div class="section-eyebrow">Hand-Selected</div>
        <h2>Featured Inventory</h2>
        <p>Exceptional vehicles chosen for outstanding value, condition, and character.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>vehicles.php" class="section-header__link">
        Browse All <i class="fa-solid fa-arrow-right"></i>
    </a>
</div>

<div class="car-grid">
    <?php if (empty($featured_vehicles)): ?>
    <div class="empty-state">
        <p>Currently curating our featured showroom — check back shortly.</p>
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
    <div class="car-card reveal" style="animation-delay: <?php echo $i * 0.07; ?>s">

        <div class="car-img-wrapper">
            <span class="condition-badge <?php echo $badge_class; ?>">
                <?php echo sanitize($car['condition']); ?>
            </span>
            <?php if ($has_discount):
                $pct = round((1 - $car['discount_price'] / $car['price']) * 100);
            ?>
            <span class="discount-ribbon">–<?php echo $pct; ?>%</span>
            <?php endif; ?>
            <img src="<?php echo $img_src; ?>"
                alt="<?php echo sanitize($car['year'] . ' ' . $car['brand'] . ' ' . $car['name']); ?>" loading="lazy">
        </div>

        <div class="car-details">
            <div class="car-price-row">
                <div class="car-price"><?php echo format_price($display_price); ?></div>
                <?php if ($has_discount): ?>
                <div class="car-price-old"><?php echo format_price($car['price']); ?></div>
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
    <a href="<?php echo BASE_URL; ?>vehicles.php" class="btn-outline">
        View Complete Inventory
        <i class="fa-solid fa-arrow-right"></i>
    </a>
</div>

<!-- SCRIPTS CONSOLIDATED HERE -->
<script>
// ── HERO SLIDESHOW ──
(function() {
    const slides = document.querySelectorAll('.hero__slide');
    const dots = document.querySelectorAll('.hero__dot');
    const label = document.getElementById('heroLabel');
    const labelText = document.getElementById('heroLabelText');

    const cars = [
        'Lamborghini Aventador',
        'Ferrari 488',
        'Bugatti Chiron',
        'Lamborghini Huracán',
        'McLaren 720S'
    ];

    let current = 0;
    let timer;

    function goTo(index) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        label.classList.remove('visible');

        current = index;

        slides[current].classList.add('active');
        dots[current].classList.add('active');
        labelText.textContent = cars[current];

        // slight delay so the slide fade starts first
        setTimeout(() => label.classList.add('visible'), 200);
    }

    function next() {
        goTo((current + 1) % slides.length);
    }

    function start() {
        timer = setInterval(next, 5000);
    }

    // Dot clicks
    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            clearInterval(timer);
            goTo(parseInt(dot.dataset.index));
            start();
        });
    });

    // Init
    label.classList.add('visible');
    start();
})();

// ── SCROLL REVEAL (INTERSECTION OBSERVER) ──
(function() {
    const els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
        els.forEach(el => el.classList.add('visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                io.unobserve(e.target);
            }
        });
    }, {
        threshold: 0.12
    });
    els.forEach(el => io.observe(el));
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>