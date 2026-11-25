<?php

// admin/login.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf_token.php';

// Fetch the logo at the top so it can be used for the favicon and the page body
$site_logo = get_site_setting($pdo, 'site_logo', '');
$logo_url = (!empty($site_logo) && file_exists(__DIR__ . '/../' . $site_logo)) 
    ? BASE_URL . sanitize($site_logo) 
    : '';

// Redirect immediately if already authorized
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "admin/index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($token)) {
        $error = 'Security token mismatch. Direct form tampering detected.';
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (empty($username) || empty($password)) {
            $error = 'Please fill out all authorization fields.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND status = 'Active' LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['username']      = $user['username'];
                $_SESSION['user_role']     = $user['role'];
                $_SESSION['user_fullname'] = $user['full_name'];
                header("Location: " . BASE_URL . "admin/index.php");
                exit();
            } else {
                $error = 'Invalid administrative credentials or deactivated account.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | RJ Motorworld</title>

    <?php if ($logo_url): ?>
    <link rel="icon" type="image/png" href="<?php echo $logo_url; ?>">
    <link rel="shortcut icon" href="<?php echo $logo_url; ?>">
    <link rel="apple-touch-icon" href="<?php echo $logo_url; ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    :root {
        --gold: #c9a84c;
        --gold-light: #e2c97e;
        --gold-dim: rgba(201, 168, 76, 0.15);
        --dark: #050608;
        --panel: rgba(8, 10, 14, 0.82);
        --border: rgba(201, 168, 76, 0.25);
        --text: #e8e2d6;
        --muted: #8a8070;
    }

    /* ─── LOADER ─────────────────────────────────────────── */
    #loader {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: #050608;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 28px;
        transition: opacity 0.6s ease, visibility 0.6s ease;
    }

    #loader.hidden {
        opacity: 0;
        visibility: hidden;
    }

    .loader-logo {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        font-weight: 600;
        letter-spacing: 0.25em;
        color: var(--gold);
        opacity: 0;
        animation: fadeUp 0.8s 0.3s forwards;
    }

    .loader-logo span {
        color: #fff;
    }

    .loader-track {
        width: 200px;
        height: 1px;
        background: rgba(255, 255, 255, 0.08);
        position: relative;
        overflow: hidden;
    }

    .loader-bar {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, var(--gold), transparent);
        animation: sweep 1.4s 0.5s ease-in-out forwards;
    }

    .loader-label {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.65rem;
        letter-spacing: 0.35em;
        color: var(--muted);
        text-transform: uppercase;
        opacity: 0;
        animation: fadeUp 0.6s 0.6s forwards;
    }

    @keyframes sweep {
        0% {
            left: -100%
        }

        100% {
            left: 100%
        }
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ─── BACKGROUND SLIDESHOW ───────────────────────────── */
    body {
        font-family: 'Montserrat', sans-serif;
        background: var(--dark);
        min-height: 100vh;
        min-height: 100dvh;
        /* Dynamic viewport height fix for mobile browsers */
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .bg-slides {
        position: fixed;
        inset: 0;
        z-index: 0;
    }

    .bg-slide {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transition: opacity 1.8s ease;
        transform: scale(1.06);
        animation: slowZoom 10s linear infinite alternate;
    }

    .bg-slide.active {
        opacity: 1;
    }

    /* Executive car images via Unsplash */
    .bg-slide:nth-child(1) {
        background-image: url('https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=1600&q=80');
    }

    .bg-slide:nth-child(2) {
        background-image: url('https://images.unsplash.com/photo-1555215695-3004980ad54e?w=1600&q=80');
    }

    .bg-slide:nth-child(3) {
        background-image: url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1600&q=80');
    }

    .bg-slide:nth-child(4) {
        background-image: url('https://images.unsplash.com/photo-1526726538690-5cbf956ae2fd?w=1600&q=80');
    }

    @keyframes slowZoom {
        from {
            transform: scale(1.0);
        }

        to {
            transform: scale(1.08);
        }
    }

    .bg-overlay {
        position: fixed;
        inset: 0;
        z-index: 1;
        background:
            linear-gradient(to right, rgba(5, 6, 8, 0.92) 40%, rgba(5, 6, 8, 0.4) 100%),
            linear-gradient(to top, rgba(5, 6, 8, 0.85) 0%, transparent 60%);
    }

    /* ─── LAYOUT ──────────────────────────────────────────── */
    .page-wrap {
        position: relative;
        z-index: 2;
        display: flex;
        width: min(1100px, 95vw);
        min-height: 100vh;
        min-height: 100dvh;
        /* Dynamic viewport height fix */
        align-items: center;
        gap: 60px;
    }

    /* left brand column */
    .brand-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 24px;
        opacity: 0;
        animation: fadeUp 1s 1.8s forwards;
    }

    .logo-mark {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .logo-emblem {
        width: 54px;
        height: 54px;
        border: 1.5px solid var(--gold);
        border-radius: 50%;
        display: grid;
        place-items: center;
        position: relative;
        overflow: hidden;
    }

    .logo-emblem img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Fallback if no logo image */
    .logo-emblem::before {
        content: '';
        position: absolute;
        inset: 5px;
        border: 1px solid rgba(201, 168, 76, 0.3);
        border-radius: 50%;
    }

    .logo-emblem svg {
        width: 26px;
        height: 26px;
        fill: var(--gold);
    }

    .logo-text {
        display: flex;
        flex-direction: column;
    }

    .logo-text .rj {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.9rem;
        font-weight: 600;
        color: #fff;
        letter-spacing: 0.1em;
        line-height: 1;
    }

    .logo-text .sub {
        font-size: 0.6rem;
        letter-spacing: 0.45em;
        color: var(--gold);
        text-transform: uppercase;
        margin-top: 3px;
    }

    .brand-tagline {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2.8rem;
        font-weight: 300;
        color: #fff;
        line-height: 1.15;
        max-width: 360px;
    }

    .brand-tagline em {
        font-style: normal;
        color: var(--gold);
    }

    .brand-rule {
        width: 60px;
        height: 1px;
        background: linear-gradient(90deg, var(--gold), transparent);
    }

    .brand-desc {
        font-size: 0.78rem;
        letter-spacing: 0.05em;
        color: var(--muted);
        line-height: 1.8;
        max-width: 320px;
    }

    /* slide dots */
    .slide-dots {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .dot {
        width: 24px;
        height: 2px;
        background: rgba(255, 255, 255, 0.2);
        cursor: pointer;
        transition: all 0.3s;
        border-radius: 2px;
    }

    .dot.active {
        background: var(--gold);
        width: 40px;
    }

    /* ─── LOGIN CARD ─────────────────────────────────────── */
    .login-col {
        width: 380px;
        flex-shrink: 0;
        opacity: 0;
        animation: fadeUp 1s 2s forwards;
    }

    .login-card {
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: 2px;
        padding: 44px 40px 40px;
        backdrop-filter: blur(24px) saturate(140%);
        position: relative;
        overflow: hidden;
    }

    /* corner accent */
    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        background: linear-gradient(225deg, rgba(201, 168, 76, 0.12) 0%, transparent 60%);
    }

    .login-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 1px;
        background: linear-gradient(90deg, var(--gold), transparent);
    }

    .card-header {
        margin-bottom: 32px;
    }

    .card-header .eyebrow {
        font-size: 0.6rem;
        letter-spacing: 0.4em;
        color: var(--gold);
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .card-header h2 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.7rem;
        font-weight: 400;
        color: #fff;
        letter-spacing: 0.04em;
    }

    .form-group {
        margin-bottom: 22px;
    }

    .form-group label {
        display: block;
        font-size: 0.63rem;
        letter-spacing: 0.3em;
        color: var(--muted);
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-wrap svg.icon {
        position: absolute;
        left: 14px;
        width: 15px;
        height: 15px;
        stroke: var(--muted);
        fill: none;
        stroke-width: 1.5;
        transition: stroke 0.3s;
        pointer-events: none;
        z-index: 2;
    }

    .form-control {
        width: 100%;
        padding: 13px 14px 13px 40px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        color: var(--text);
        font-family: 'Montserrat', sans-serif;
        font-size: 0.88rem;
        letter-spacing: 0.02em;
        outline: none;
        transition: border-color 0.3s, background 0.3s, box-shadow 0.3s;
        z-index: 1;
    }

    .form-control.password-input {
        padding-right: 40px;
        /* Make room for toggle */
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.18);
    }

    .form-control:focus {
        border-color: var(--gold);
        background: rgba(201, 168, 76, 0.04);
        box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.07);
    }

    .form-control:focus~svg.icon,
    .input-wrap:focus-within svg.icon {
        stroke: var(--gold);
    }

    .toggle-password {
        position: absolute;
        right: 14px;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toggle-password svg {
        width: 16px;
        height: 16px;
        stroke: var(--muted);
        fill: none;
        stroke-width: 1.5;
        transition: stroke 0.3s;
    }

    .toggle-password:hover svg,
    .toggle-password:focus svg {
        stroke: var(--gold);
    }

    .btn-submit {
        width: 100%;
        margin-top: 8px;
        padding: 14px;
        background: linear-gradient(135deg, #b8922a, #e2c97e, #b8922a);
        background-size: 200% 100%;
        background-position: 0% 50%;
        color: #0a0800;
        border: none;
        border-radius: 2px;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.35em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background-position 0.5s, opacity 0.3s, transform 0.15s;
        position: relative;
        overflow: hidden;
    }

    .btn-submit:hover {
        background-position: 100% 50%;
    }

    .btn-submit:active {
        transform: scale(0.98);
    }

    .btn-submit::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 60%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: skewX(-20deg);
        transition: left 0.6s;
    }

    .btn-submit:hover::after {
        left: 150%;
    }

    .alert-danger {
        background: rgba(153, 27, 27, 0.18);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #fca5a5;
        padding: 11px 14px;
        border-radius: 2px;
        margin-bottom: 22px;
        font-size: 0.76rem;
        line-height: 1.5;
    }

    .card-footer {
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-footer .secure-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.6rem;
        letter-spacing: 0.2em;
        color: var(--muted);
        text-transform: uppercase;
    }

    .card-footer .secure-badge svg {
        width: 12px;
        height: 12px;
        stroke: var(--gold);
        fill: none;
        stroke-width: 2;
    }

    .card-footer .version {
        font-size: 0.58rem;
        letter-spacing: 0.1em;
        color: rgba(255, 255, 255, 0.15);
    }

    /* ─── RESPONSIVE ─────────────────────────────────────── */
    @media (max-width: 768px) {
        body {
            /* On mobile, ensure the dark background overlay is very opaque so inputs stand out */
            background: #000;
        }

        .bg-overlay {
            background: rgba(5, 6, 8, 0.85);
            /* Darker overlay for mobile */
        }

        .page-wrap {
            flex-direction: column;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }

        .brand-col {
            flex: none;
            /* FIX: Prevents the column from pushing content to the bottom */
            align-items: center;
            text-align: center;
            gap: 15px;
        }

        /* Simplify header for mobile to emphasize login */
        .brand-tagline,
        .brand-rule,
        .brand-desc,
        .slide-dots {
            display: none;
        }

        .logo-emblem {
            width: 40px;
            height: 40px;
        }

        .logo-text .rj {
            font-size: 1.4rem;
        }

        .logo-text .sub {
            font-size: 0.5rem;
        }

        .login-col {
            width: 100%;
            max-width: 400px;
        }

        .login-card {
            padding: 30px 20px;
            /* Enhance contrast for mobile visibility */
            background: rgba(10, 12, 16, 0.95);
            border-color: rgba(201, 168, 76, 0.4);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8);
        }

        .card-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .card-header h2 {
            font-size: 1.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            /* slightly lighter inputs on mobile */
            border-color: rgba(255, 255, 255, 0.2);
            padding: 15px 15px 15px 45px;
            font-size: 1rem;
            /* Larger text for mobile tap targets */
        }

        .form-group label {
            font-size: 0.7rem;
        }

        .btn-submit {
            padding: 16px;
            font-size: 0.8rem;
        }
    }
    </style>
</head>

<body>

    <div id="loader">
        <div class="loader-logo">RJ <span>MOTORWORLD</span></div>
        <div class="loader-track">
            <div class="loader-bar"></div>
        </div>
        <div class="loader-label">Initialising Secure Portal</div>
    </div>

    <div class="bg-slides">
        <div class="bg-slide active"></div>
        <div class="bg-slide"></div>
        <div class="bg-slide"></div>
        <div class="bg-slide"></div>
    </div>
    <div class="bg-overlay"></div>

    <div class="page-wrap">

        <div class="brand-col">
            <div class="logo-mark">
                <div class="logo-emblem">
                    <?php if ($logo_url): ?>
                    <img src="<?php echo $logo_url; ?>" alt="Logo">
                    <?php else: ?>
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="none" stroke="#c9a84c" stroke-width="1.5" />
                        <circle cx="12" cy="12" r="2.5" fill="#c9a84c" />
                        <line x1="12" y1="9.5" x2="12" y2="2" stroke="#c9a84c" stroke-width="1.5" />
                        <line x1="9.83" y1="10.5" x2="4.5" y2="17" stroke="#c9a84c" stroke-width="1.5" />
                        <line x1="14.17" y1="10.5" x2="19.5" y2="17" stroke="#c9a84c" stroke-width="1.5" />
                    </svg>
                    <?php endif; ?>
                </div>
                <div class="logo-text">
                    <span class="rj">RJ Motorworld</span>
                    <span class="sub">Executive Automotive Group</span>
                </div>
            </div>

            <div class="brand-tagline">
                Where <em>Luxury</em><br>Meets the Open Road
            </div>

            <div class="brand-rule"></div>

            <p class="brand-desc">
                Restricted administrative access.<br>
                Authorised personnel only. All sessions are
                logged and monitored for security compliance.
            </p>

            <div class="slide-dots">
                <div class="dot active" data-slide="0"></div>
                <div class="dot" data-slide="1"></div>
                <div class="dot" data-slide="2"></div>
                <div class="dot" data-slide="3"></div>
            </div>
        </div>

        <div class="login-col">
            <div class="login-card">

                <div class="card-header">
                    <p class="eyebrow">Admin Portal</p>
                    <h2>Secure Sign-In</h2>
                </div>

                <?php if (!empty($error)): ?>
                <div class="alert-danger"><?php echo sanitize($error); ?></div>
                <?php endif; ?>

                <form action="" method="POST" autocomplete="off" id="loginForm">
                    <?php csrf_input_field(); ?>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <input type="text" id="username" name="username" class="form-control" required autofocus
                                placeholder="admin_user">
                            <svg class="icon" viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                            </svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input type="password" id="password" name="password" class="form-control password-input"
                                required placeholder="••••••••••">
                            <svg class="icon" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <button type="button" class="toggle-password" id="togglePassword"
                                aria-label="Toggle password visibility">
                                <svg id="eyeIcon" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        Authenticate Session
                    </button>
                </form>

                <div class="card-footer">
                    <div class="secure-badge">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2L4 6v6c0 5.2 3.4 10 8 12 4.6-2 8-6.8 8-12V6l-8-4z" />
                        </svg>
                        256-bit SSL Secured
                    </div>
                    <span class="version">v3.2.1</span>
                </div>

            </div>
        </div>

    </div>
    <script>
    // ── Loader ───────────────────────────────
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.getElementById('loader').classList.add('hidden');
        }, 2000);
    });

    // ── Slideshow ────────────────────────────
    const slides = document.querySelectorAll('.bg-slide');
    const dots = document.querySelectorAll('.dot');
    let current = 0;

    function goTo(n) {
        slides[current].classList.remove('active');
        if (dots[current]) dots[current].classList.remove('active');
        current = n;
        slides[current].classList.add('active');
        if (dots[current]) dots[current].classList.add('active');
    }

    if (dots.length > 0) {
        dots.forEach(d => d.addEventListener('click', () => goTo(+d.dataset.slide)));
    }
    setInterval(() => goTo((current + 1) % slides.length), 6000);

    // ── Password Toggle ──────────────────────
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    // SVG paths for Eye (visible) and Eye-Off (hidden)
    const iconEye =
        `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>`;
    const iconEyeOff =
        `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>`;

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        if (type === 'text') {
            eyeIcon.innerHTML = iconEyeOff;
        } else {
            eyeIcon.innerHTML = iconEye;
        }
    });

    // ── Submit state ─────────────────────────
    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.textContent = 'Verifying…';
        btn.style.opacity = '0.7';
        btn.disabled = true;
    });
    </script>

</body>

</html>