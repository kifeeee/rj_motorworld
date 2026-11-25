<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the active user is fully authenticated as an administrator.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Enforces admin panel authentication. Redirects users to login if they lack a session.
 */
function check_admin_auth() {
    if (!is_logged_in()) {
        // Find configuration path context cleanly
        header("Location: " . BASE_URL . "admin/login.php");
        exit();
    }
}

/**
 * Checks if the logged-in user matches a specific security clearance tier.
 */
function has_role($allowed_roles = []) {
    if (!is_logged_in() || !in_array($_SESSION['user_role'], $allowed_roles)) {
        header("Location: " . BASE_URL . "admin/index.php?error=unauthorized");
        exit();
    }
}