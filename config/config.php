<?php
// config/config.php

// Ensure session is started globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// System Environment Configuration
define('BASE_URL', 'http://localhost/rj_motorworld/');
define('SITE_NAME', 'LB Autos');
define('TIMEZONE', 'America/New_York');

date_default_timezone_set(TIMEZONE);

// Global Display Settings
define('ITEMS_PER_PAGE', 12);
define('CURRENCY_SYMBOL', '$');

// Autoload core database connection safely
require_once __DIR__ . '/db.php';