<?php
// includes/loader.php

$loader_site_name = get_site_setting($pdo, 'company_name', 'LB Motors');
$loader_logo      = get_site_setting($pdo, 'site_logo', '');

$loader_logo_url = (!empty($loader_logo) && file_exists(__DIR__ . '/../' . $loader_logo))
    ? BASE_URL . sanitize($loader_logo)
    : '';
?>
<div id="premium-loader">

    <canvas id="loaderCanvas"></canvas>

    <div class="loader-scene">

        <div class="orbit orbit--outer">
            <div class="orbit__gem"></div>
        </div>

        <div class="orbit orbit--mid">
            <div class="orbit__gem"></div>
        </div>

        <div class="loader-core">
            <div class="core__glow"></div>

            <?php if ($loader_logo_url): ?>
            <div class="core__logo">
                <img src="<?php echo $loader_logo_url; ?>" alt="<?php echo sanitize($loader_site_name); ?>">
            </div>
            <?php else: ?>
            <div class="core__monogram">
                <span class="mono mono--first">L</span><span class="mono mono--second">B</span>
            </div>
            <?php endif; ?>

            <div class="core__rule">
                <span></span>
                <i class="fa-solid fa-diamond"></i>
                <span></span>
            </div>

            <div class="core__wordmark">Motors</div>
        </div>

        <svg class="progress-arc" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <circle class="arc-track" cx="100" cy="100" r="90" />
            <circle class="arc-fill" cx="100" cy="100" r="90" id="arcFill" />
        </svg>

    </div>

    <div class="loader-tagline">
        <span class="tagline__line"></span>
        Premium Auto Dealership
        <span class="tagline__line"></span>
    </div>

</div>

<style>
/* ════════════════════════════════════════
   PREMIUM LOADER  —  LB Motors
   Aesthetic: Dark luxury · art-deco orbital
════════════════════════════════════════ */

@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=DM+Sans:wght@300;400;600&display=swap');

#premium-loader {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: #060606;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 28px;
    transition: opacity 0.9s cubic-bezier(0.4, 0, 0.2, 1),
        visibility 0.9s cubic-bezier(0.4, 0, 0.2, 1);
}

#premium-loader.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

/* Particle canvas sits behind everything */
#loaderCanvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0.4;
}

/* ── SCENE WRAPPER ── */
.loader-scene {
    position: relative;
    width: 220px;
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ── ORBIT RINGS ── */
.orbit {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 1px solid rgba(192, 154, 90, 0.18);
}

/* Spinning gem dot */
.orbit__gem {
    position: absolute;
    top: -4px;
    left: 50%;
    transform: translateX(-50%);
    width: 8px;
    height: 8px;
    background: #c09a5a;
    border-radius: 50%;
    box-shadow: 0 0 10px 3px rgba(192, 154, 90, 0.6);
}

.orbit--outer {
    animation: orbitSpin 4s linear infinite;
}

.orbit--mid {
    inset: 20px;
    border-color: rgba(192, 154, 90, 0.1);
    animation: orbitSpin 6s linear infinite reverse;
}

.orbit--mid .orbit__gem {
    width: 5px;
    height: 5px;
    background: #d4b47a;
    box-shadow: 0 0 8px 2px rgba(212, 180, 122, 0.5);
}

@keyframes orbitSpin {
    to {
        transform: rotate(360deg);
    }
}

/* ── CORE BADGE ── */
.loader-core {
    position: relative;
    z-index: 2;
    width: 130px;
    height: 130px;
    background: linear-gradient(160deg, #161616 0%, #0d0d0d 100%);
    border-radius: 50%;
    border: 1px solid rgba(192, 154, 90, 0.3);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-shadow:
        0 0 0 1px rgba(192, 154, 90, 0.08),
        0 0 40px rgba(192, 154, 90, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.04);
    overflow: hidden;
    /* keep logo inside the circle */
}

/* Radial gold glow behind logo */
.core__glow {
    position: absolute;
    inset: 20%;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(192, 154, 90, 0.14) 0%, transparent 70%);
    animation: glowPulse 3s ease-in-out infinite;
}

@keyframes glowPulse {

    0%,
    100% {
        transform: scale(1);
        opacity: 0.7;
    }

    50% {
        transform: scale(1.3);
        opacity: 1;
    }
}

/* ── LOGO IMAGE (centered) ── */
.core__logo {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 78%;
    height: 78%;
}

.core__logo img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    filter: drop-shadow(0 0 10px rgba(192, 154, 90, 0.35));
}

/* ── MONOGRAM FALLBACK ── */
.core__monogram {
    display: flex;
    align-items: baseline;
    line-height: 1;
    position: relative;
    z-index: 1;
}

.mono {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 700;
    line-height: 1;
    display: inline-block;
}

/* L — larger, gold */
.mono--first {
    font-size: 52px;
    color: #c09a5a;
    letter-spacing: -0.02em;
    text-shadow: 0 0 30px rgba(192, 154, 90, 0.5);
    animation: letterShimmer 3s ease-in-out infinite;
}

/* B — slightly smaller, white, italic counterpart */
.mono--second {
    font-size: 44px;
    font-style: italic;
    color: rgba(255, 255, 255, 0.9);
    margin-left: -2px;
    margin-bottom: 2px;
}

@keyframes letterShimmer {

    0%,
    100% {
        text-shadow: 0 0 20px rgba(192, 154, 90, 0.4);
    }

    50% {
        text-shadow: 0 0 40px rgba(192, 154, 90, 0.8), 0 0 6px rgba(212, 180, 122, 0.6);
    }
}

/* ── DECORATIVE RULE ── */
.core__rule {
    display: flex;
    align-items: center;
    gap: 5px;
    position: relative;
    z-index: 1;
}

.core__rule span {
    display: block;
    width: 22px;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(192, 154, 90, 0.6));
}

.core__rule span:last-child {
    background: linear-gradient(90deg, rgba(192, 154, 90, 0.6), transparent);
}

.core__rule i {
    color: #c09a5a;
    font-size: 5px;
    animation: diamondSpin 4s linear infinite;
}

@keyframes diamondSpin {
    to {
        transform: rotate(360deg);
    }
}

/* ── WORDMARK ── */
.core__wordmark {
    font-family: 'DM Sans', sans-serif;
    font-size: 7px;
    font-weight: 600;
    letter-spacing: 0.26em;
    text-transform: uppercase;
    color: rgba(192, 154, 90, 0.55);
    position: relative;
    z-index: 1;
    margin-top: 2px;
}

/* ── PROGRESS ARC ── */
.progress-arc {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.arc-track {
    fill: none;
    stroke: rgba(255, 255, 255, 0.04);
    stroke-width: 1;
}

.arc-fill {
    fill: none;
    stroke: url(#arcGradient);
    stroke-width: 1.5;
    stroke-linecap: round;
    stroke-dasharray: 565.5;
    /* 2π × 90 */
    stroke-dashoffset: 565.5;
    transition: stroke-dashoffset 0.05s linear;
    filter: drop-shadow(0 0 4px rgba(192, 154, 90, 0.7));
}

/* ── TAGLINE ── */
.loader-tagline {
    display: flex;
    align-items: center;
    gap: 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 9px;
    font-weight: 500;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.2);
    animation: taglineFade 2s ease-in-out infinite alternate;
}

.tagline__line {
    display: block;
    width: 32px;
    height: 1px;
    background: rgba(192, 154, 90, 0.25);
}

@keyframes taglineFade {
    from {
        opacity: 0.5;
    }

    to {
        opacity: 1;
    }
}
</style>

<svg width="0" height="0" style="position:absolute">
    <defs>
        <linearGradient id="arcGradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#c09a5a" stop-opacity="0.2" />
            <stop offset="50%" stop-color="#d4b47a" stop-opacity="1" />
            <stop offset="100%" stop-color="#c09a5a" stop-opacity="0.2" />
        </linearGradient>
    </defs>
</svg>

<script>
(function() {
    const loader = document.getElementById('premium-loader');
    const arcFill = document.getElementById('arcFill');
    const canvas = document.getElementById('loaderCanvas');
    const ctx = canvas ? canvas.getContext('2d') : null;

    if (!loader) return;

    /* ── PARTICLE FIELD ── */
    if (ctx) {
        let W, H, particles = [];

        function resize() {
            W = canvas.width = window.innerWidth;
            H = canvas.height = window.innerHeight;
        }

        function makeParticle() {
            return {
                x: Math.random() * W,
                y: Math.random() * H,
                r: Math.random() * 1.2 + 0.2,
                a: Math.random() * Math.PI * 2,
                speed: Math.random() * 0.3 + 0.1,
                opacity: Math.random() * 0.5 + 0.1,
            };
        }

        resize();
        for (let i = 0; i < 80; i++) particles.push(makeParticle());
        window.addEventListener('resize', resize);

        function drawParticles() {
            ctx.clearRect(0, 0, W, H);
            particles.forEach(p => {
                p.a += p.speed * 0.01;
                p.x += Math.cos(p.a) * p.speed * 0.4;
                p.y += Math.sin(p.a) * p.speed * 0.4;

                // Wrap edges
                if (p.x < 0) p.x = W;
                if (p.x > W) p.x = 0;
                if (p.y < 0) p.y = H;
                if (p.y > H) p.y = 0;

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(192,154,90,${p.opacity})`;
                ctx.fill();
            });
            if (!loader.classList.contains('hidden')) requestAnimationFrame(drawParticles);
        }

        drawParticles();
    }

    /* ── PROGRESS ARC ── */
    const circumference = 2 * Math.PI * 90; // ~565.5
    let progress = 0;
    let rafId;

    function animateArc() {
        if (progress < 100) {
            // Fast start, eased at end
            const increment = progress < 70 ? 1.4 : 0.5;
            progress = Math.min(progress + increment, 100);
            const offset = circumference - (progress / 100) * circumference;
            if (arcFill) arcFill.style.strokeDashoffset = offset;
            rafId = requestAnimationFrame(animateArc);
        }
    }

    animateArc();

    /* ── HIDE ON LOAD ── */
    function hideLoader() {
        cancelAnimationFrame(rafId);
        // Complete the arc first, then fade
        if (arcFill) arcFill.style.strokeDashoffset = 0;
        setTimeout(() => loader.classList.add('hidden'), 200);
    }

    if (document.readyState === 'complete') {
        hideLoader();
    } else {
        window.addEventListener('load', hideLoader);
    }

    // Hard fallback — never block the user
    setTimeout(hideLoader, 6000);
})();
</script>