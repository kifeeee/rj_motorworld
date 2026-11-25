<?php
// admin/delete_user.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // Required for check_admin_auth()
require_once __DIR__ . '/../includes/csrf_token.php';

// 1. Enforce basic operational session rules
check_admin_auth();

// 2. STRICT BACKEND RBAC: Reject anyone who is not a SuperAdmin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'SuperAdmin') {
    header("Location: " . BASE_URL . "admin/manage_users.php?error=unauthorized");
    exit();
}

// 3. Process the deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Security token mismatch.');
    }

    $delete_id = (int)$_POST['delete_id'];

    // Safeguard: Prevent deleting yourself
    if ($delete_id === $_SESSION['user_id']) {
        header("Location: " . BASE_URL . "admin/manage_users.php?error=self_delete");
        exit();
    }

    // Execute deletion safely
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $delete_id]);

    header("Location: " . BASE_URL . "admin/manage_users.php?msg=deleted");
    exit();
} else {
    // If someone tries to just visit the URL directly via GET
    header("Location: " . BASE_URL . "admin/manage_users.php");
    exit();
}