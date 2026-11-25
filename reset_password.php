<?php
// reset_password.php
require_once __DIR__ . '/config/db.php';

// Generate a brand new, clean bcrypt hash for 'admin123'
$new_hash = password_hash('admin123', PASSWORD_BCRYPT);

try {
    // Force update the admin user in your database
    $stmt = $pdo->prepare("UPDATE users SET password = :pass, status = 'Active' WHERE username = 'admin'");
    $stmt->execute(['pass' => $new_hash]);
    
    echo "<div style='font-family:sans-serif; padding:20px; background:#dcfce7; color:#166534; border-radius:6px; max-width:500px; margin:40px auto; border:1px solid #bbf7d0;'>";
    echo "<h3>🚀 Success!</h3>";
    echo "The database record for '<strong>admin</strong>' has been updated with a fresh secure hash.<br><br>";
    echo "Go back to <a href='admin/login.php'><strong>Admin Login</strong></a> and use password <strong>admin123</strong>.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage();
}
?>