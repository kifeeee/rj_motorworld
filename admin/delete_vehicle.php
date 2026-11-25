<?php
// admin/delete_vehicle.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Enforce active administrator validation check
check_admin_auth();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. Trace structural assets on filesystem storage to clean up physical images
    $stmt = $pdo->prepare("SELECT image_path FROM vehicle_images WHERE vehicle_id = :id");
    $stmt->execute(['id' => $id]);
    $images = $stmt->fetchAll();

    foreach ($images as $img) {
        $physical_file_path = __DIR__ . '/../' . $img['image_path'];
        if (file_exists($physical_file_path) && is_file($physical_file_path)) {
            unlink($physical_file_path); // Remove asset cleanly from server disk
        }
    }

    // 2. Cascade execution logic deletes the parent matching index records cleanly from your database
    $delete_stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = :id");
    $delete_stmt->execute(['id' => $id]);
}

header("Location: " . BASE_URL . "admin/manage_vehicles.php?msg=deleted");
exit();