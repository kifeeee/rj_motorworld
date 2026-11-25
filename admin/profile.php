<?php
// admin/profile.php
require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../includes/csrf_token.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Process Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security Token mismatch.';
    } else {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($username) || empty($email) || empty($full_name)) {
            $error = 'Username, Email, and Full Name are required.';
        } else {
            // Check if username or email is already taken by ANOTHER user
            $check = $pdo->prepare("SELECT id FROM users WHERE (username = :u OR email = :e) AND id != :id");
            $check->execute(['u' => $username, 'e' => $email, 'id' => $user_id]);
            
            if ($check->fetch()) {
                $error = 'That username or email is already in use by another account.';
            } else {
                // Determine if we are updating the password
                if (!empty($new_password)) {
                    if ($new_password !== $confirm_password) {
                        $error = 'New passwords do not match.';
                    } else {
                        // Update with new password
                        $hashed_pass = password_hash($new_password, PASSWORD_BCRYPT);
                        $update = $pdo->prepare("UPDATE users SET username = :u, email = :e, full_name = :fn, password = :p WHERE id = :id");
                        $update->execute(['u' => $username, 'e' => $email, 'fn' => $full_name, 'p' => $hashed_pass, 'id' => $user_id]);
                        $success = 'Profile and password successfully updated.';
                    }
                } else {
                    // Update WITHOUT changing password
                    $update = $pdo->prepare("UPDATE users SET username = :u, email = :e, full_name = :fn WHERE id = :id");
                    $update->execute(['u' => $username, 'e' => $email, 'fn' => $full_name, 'id' => $user_id]);
                    $success = 'Profile details successfully updated.';
                }

                // If successful, update the active Session variables so the sidebar refreshes instantly
                if (empty($error)) {
                    $_SESSION['username'] = $username;
                    $_SESSION['user_fullname'] = $full_name;
                }
            }
        }
    }
}

// Fetch current user data to populate the form
$stmt = $pdo->prepare("SELECT username, email, full_name FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$my_profile = $stmt->fetch();
?>

<div style="max-width: 600px; margin: 0 auto;">
    <h3>My Profile Settings</h3>

    <?php if (!empty($error)): ?>
    <div
        style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius:6px; margin-bottom:20px; border: 1px solid #fca5a5;">
        <?php echo sanitize($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
    <div
        style="background: #dcfce7; color: #166534; padding: 15px; border-radius:6px; margin-bottom:20px; border: 1px solid #bbf7d0;">
        <?php echo sanitize($success); ?></div>
    <?php endif; ?>

    <form action="" method="POST"
        style="background: white; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <?php csrf_input_field(); ?>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 14px;">Full
                Name</label>
            <input type="text" name="full_name" value="<?php echo sanitize($my_profile['full_name']); ?>" required
                style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label
                    style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 14px;">System
                    Username</label>
                <input type="text" name="username" value="<?php echo sanitize($my_profile['username']); ?>" required
                    style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            <div>
                <label
                    style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 14px;">Email
                    Address</label>
                <input type="email" name="email" value="<?php echo sanitize($my_profile['email']); ?>" required
                    style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        <h4 style="margin-top: 0; color: #0f172a;">Change Password</h4>
        <p style="font-size: 13px; color: #64748b; margin-top: -10px; margin-bottom: 20px;">Leave these fields blank if
            you do not want to change your password.</p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div>

                <label
                    style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 14px;">New
                    Password</label>
                <input type="password" name="new_password"
                    style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            <div>
                <label
                    style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 14px;">Confirm
                    New Password</label>
                <input type="password" name="confirm_password"
                    style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
        </div>

        <button type="submit"
            style="background: #0f172a; color: white; border: none; padding: 14px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; font-size: 15px; transition: background 0.2s;">Save
            Profile Changes</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>