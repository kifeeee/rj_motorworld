<?php
// admin/includes/admin_sidebar.php

$current_page = basename($_SERVER['PHP_SELF']);
$user_fullname = $_SESSION['user_fullname'] ?? 'Admin';
$user_role     = $_SESSION['user_role'] ?? 'Staff';
?>
<div class="sidebar">
    <button class="mobile-close-btn" id="closeMenuBtn">
        <i class="fa-solid fa-xmark"></i>
    </button>

    <div class="sidebar-brand">
        <h3>RJ Motorworld</h3>
        <span class="system-badge">Admin Panel</span>
    </div>

    <div class="user-profile-context">
        <div class="user-avatar"><?php echo strtoupper(substr(sanitize($user_fullname), 0, 1)); ?></div>
        <div class="user-info">
            <span class="user-name"><?php echo sanitize($user_fullname); ?></span>
            <span class="user-role-badge"><?php echo sanitize($user_role); ?></span>
        </div>
    </div>

    <hr class="sidebar-divider">

    <nav class="sidebar-nav">
        <a href="<?php echo BASE_URL; ?>admin/index.php"
            class="<?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-line"></i> Dashboard
        </a>

        <div class="nav-section-title">Inventory</div>

        <a href="<?php echo BASE_URL; ?>admin/manage_vehicles.php"
            class="<?php echo in_array($current_page, ['manage_vehicles.php', 'edit_vehicle.php']) ? 'active' : ''; ?>">
            <i class="fa-solid fa-car"></i> All Vehicles
        </a>

        <a href="<?php echo BASE_URL; ?>admin/add_vehicle.php"
            class="<?php echo ($current_page === 'add_vehicle.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-circle-plus"></i> Add New Vehicle
        </a>

        <div class="nav-section-title">Sales</div>

        <a href="<?php echo BASE_URL; ?>admin/manage_leads.php"
            class="<?php echo ($current_page === 'manage_leads.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-inbox"></i> Customer Leads
        </a>

        <div class="nav-section-title">Settings</div>

        <a href="<?php echo BASE_URL; ?>admin/manage_users.php"
            class="<?php echo ($current_page === 'manage_users.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-users"></i> Manage Users
        </a>

        <a href="<?php echo BASE_URL; ?>admin/site_settings.php"
            class="<?php echo ($current_page === 'site_settings.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-sliders"></i> General Settings
        </a>

        <div class="nav-section-title">My Account</div>

        <a href="<?php echo BASE_URL; ?>admin/profile.php"
            class="<?php echo ($current_page === 'profile.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-id-badge"></i> My Profile
        </a>

        <hr class="sidebar-divider" style="margin-top: 20px;">

        <a href="<?php echo BASE_URL; ?>admin/logout.php" class="nav-logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out
        </a>
    </nav>
</div>