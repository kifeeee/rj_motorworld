<?php
// admin/includes/admin_header.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Enforce operational session rules
check_admin_auth();

// Fetch site logo for favicon and header display
$site_logo = get_site_setting($pdo, 'site_logo', '');
$logo_url = (!empty($site_logo) && file_exists(__DIR__ . '/../../' . $site_logo))
    ? BASE_URL . sanitize($site_logo)
    : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | RJ Motorworld</title>

    <?php if ($logo_url): ?>
    <link rel="icon" type="image/png" href="<?php echo $logo_url; ?>">
    <link rel="shortcut icon" href="<?php echo $logo_url; ?>">
    <link rel="apple-touch-icon" href="<?php echo $logo_url; ?>">
    <?php endif; ?>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    /* Premium CSS Reset & Variables */
    :root {
        --bg-body: #f3f4f6;
        --bg-sidebar: #0f172a;
        --card-bg: #ffffff;
        --text-main: #1f2937;
        --text-muted: #6b7280;
        --border-color: #e5e7eb;
        --accent: #38bdf8;
        --accent-hover: #0ea5e9;
    }

    body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        margin: 0;
        background-color: var(--bg-body);
        color: var(--text-main);
        display: flex;
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
        overflow-x: hidden;
        /* Prevents horizontal scrolling issues */
    }

    /* Sidebar Styling */
    .sidebar {
        width: 260px;
        background-color: var(--bg-sidebar);
        color: #f8fafc;
        padding: 24px 16px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #1e293b;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
        flex-shrink: 0;
        position: relative;
        /* Anchor for mobile close button */
        transition: transform 0.3s ease;
        z-index: 1000;
    }

    .sidebar-brand {
        padding: 0 12px 15px 12px;
    }

    .sidebar-brand h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #fff;
        letter-spacing: -0.5px;
    }

    .system-badge {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--accent);
        display: inline-block;
        margin-top: 4px;
        letter-spacing: 0.5px;
    }

    .user-profile-context {
        display: flex;
        align-items: center;
        gap: 12px;
        background-color: #1e293b;
        padding: 12px;
        border-radius: 10px;
        margin: 16px 0 20px 0;
        border: 1px solid #334155;
    }

    .user-avatar {
        width: 38px;
        height: 38px;
        background-color: var(--accent);
        color: #0f172a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .user-name {
        font-size: 14px;
        font-weight: 600;
        color: #f1f5f9;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .user-role-badge {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    .sidebar-divider {
        border: 0;
        border-top: 1px solid #1e293b;
        margin: 15px 0;
    }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .nav-section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
        padding: 14px 12px 8px 12px;
    }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        color: #cbd5e1;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .sidebar-nav a i {
        font-size: 16px;
        width: 20px;
        text-align: center;
        color: #94a3b8;
        transition: color 0.2s ease;
    }

    .sidebar-nav a:hover {
        background-color: #1e293b;
        color: #fff;
    }

    .sidebar-nav a:hover i {
        color: var(--accent);
    }

    .sidebar-nav a.active {
        background-color: rgba(56, 189, 248, 0.1);
        color: var(--accent);
        font-weight: 600;
        border: 1px solid rgba(56, 189, 248, 0.2);
    }

    .sidebar-nav a.active i {
        color: var(--accent);
    }

    .nav-logout {
        color: #fca5a5 !important;
        margin-top: 10px;
    }

    .nav-logout i {
        color: #fca5a5 !important;
    }

    .nav-logout:hover {
        background-color: #991b1b !important;
        color: white !important;
        border-color: transparent !important;
    }

    .nav-logout:hover i {
        color: white !important;
    }

    /* Main Viewport & Layout */
    .main-viewport {
        flex: 1;
        padding: 40px;
        box-sizing: border-box;
        overflow-y: auto;
        min-width: 0;
    }

    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 20px;
        margin-bottom: 30px;
    }

    .header-brand-container {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        color: var(--text-main);
        font-size: 24px;
        cursor: pointer;
        padding: 0;
    }

    .admin-logo {
        height: 38px;
        width: auto;
        object-fit: contain;
    }

    .header-bar h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
    }

    /* Dashboard Metrics Cards */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }

    .metric-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .metric-card h4 {
        margin: 0;
        color: var(--text-muted);
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .metric-card .value {
        font-size: 32px;
        font-weight: 700;
        margin: 12px 0 0 0;
        color: var(--text-main);
    }

    /* Tables */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--card-bg);
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        min-width: 600px;
    }

    .data-table th,
    .data-table td {
        padding: 16px;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
    }

    .data-table th {
        background: #f8fafc;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .data-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Badges */
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-new {
        background: #dcfce7;
        color: #166534;
    }

    .badge-pending {
        background: #fef9c3;
        color: #854d0e;
    }

    .session-badge {
        font-size: 13px;
        color: #059669;
        background: #d1fae5;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        border: 1px solid #a7f3d0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* --- MOBILE RESPONSIVE COMPONENTS --- */

    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .mobile-close-btn {
        display: none;
        position: absolute;
        top: 20px;
        right: 16px;
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 24px;
        cursor: pointer;
        z-index: 1001;
    }

    @media (max-width: 768px) {
        .sidebar {
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            transform: translateX(-100%);
            overflow-y: auto;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        .mobile-menu-btn {
            display: block;
        }

        .mobile-close-btn {
            display: block;
        }

        .main-viewport {
            padding: 16px;
        }

        .header-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }

        .header-bar h2 {
            font-size: 20px;
        }

        /* Compact Analytics: 2 items per row */
        .metrics-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .metric-card {
            padding: 16px 12px;
            border-radius: 8px;
        }

        .metric-card h4 {
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .metric-card .value {
            font-size: 20px;
            margin: 8px 0 0 0;
        }
    }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <?php include_once __DIR__ . '/loader.php'; ?>

    <?php require_once __DIR__ . '/admin_sidebar.php'; ?>

    <div class="main-viewport">
        <div class="header-bar">
            <div class="header-brand-container">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <?php if ($logo_url): ?>
                <img src="<?php echo $logo_url; ?>" alt="Brand Logo" class="admin-logo">
                <?php endif; ?>
                <h2>Admin Dashboard</h2>
            </div>
            <div class="session-badge">
                <i class="fa-solid fa-shield-halved"></i> Secure Session
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const mobileBtn = document.getElementById('mobileMenuBtn');
            const overlay = document.getElementById('sidebarOverlay');
            const sidebar = document.querySelector('.sidebar');

            // Dynamically inject the Close Button into the sidebar 
            // so we don't have to modify admin_sidebar.php
            let closeBtn;
            if (sidebar) {
                closeBtn = document.createElement('button');
                closeBtn.className = 'mobile-close-btn';
                closeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                sidebar.appendChild(closeBtn);
            }

            function toggleMenu() {
                if (sidebar) sidebar.classList.toggle('active');
                if (overlay) overlay.classList.toggle('active');
            }

            if (mobileBtn) mobileBtn.addEventListener('click', toggleMenu);
            if (overlay) overlay.addEventListener('click', toggleMenu);
            if (closeBtn) closeBtn.addEventListener('click', toggleMenu);
        });
        </script>