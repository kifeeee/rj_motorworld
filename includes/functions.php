<?php
// includes/functions.php

require_once __DIR__ . '/../config/config.php';

/**
 * Sanitize user input variables for basic output display protection (XSS Defense)
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency outputs uniformly across the site layout
 */
function format_price($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Format mileage numbers accurately
 */
function format_mileage($miles) {
    return number_format($miles) . " mi";
}

/**
 * Retrieve dynamic system metrics from site_settings table fallback mechanisms
 */
function get_site_setting($pdo, $key, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = :key LIMIT 1");
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Returns the primary display photo string or a generic fallback image if none exists
 */
function get_vehicle_thumbnail($pdo, $vehicle_id) {
    $stmt = $pdo->prepare("SELECT image_path FROM vehicle_images WHERE vehicle_id = :id AND is_main = 1 LIMIT 1");
    $stmt->execute(['id' => $vehicle_id]);
    $img = $stmt->fetch();
    
    if ($img && file_exists(__DIR__ . '/../' . $img['image_path'])) {
        return BASE_URL . $img['image_path'];
    }
    return BASE_URL . 'assets/images/placeholder-car.jpg'; // Ensure you keep a placeholder in assets
}