<?php
// admin/site_settings.php

// 1. LOAD CORE CONFIGURATION FIRST (NO HTML OUTPUT HERE)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf_token.php';

// Enforce security
check_admin_auth();

// 2. FLASH MESSAGE HANDLING (Displays exactly once, then deletes itself)
$success_msg = '';
$error_msg = '';

if (isset($_SESSION['flash_success'])) {
    $success_msg = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']); // Delete it so it doesn't show again
}
if (isset($_SESSION['flash_error'])) {
    $error_msg = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']); // Delete it so it doesn't show again
}

// 3. PROCESS FORM SUBMISSION & REDIRECTS BEFORE ANY HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $local_error = '';
    
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        
        // Handle Logo Deletion
        if (isset($_POST['delete_logo']) && $_POST['delete_logo'] === 'yes') {
            $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'site_logo' LIMIT 1");
            $stmt->execute();
            $old_logo = $stmt->fetchColumn();

            if ($old_logo && file_exists(__DIR__ . '/../' . $old_logo)) {
                unlink(__DIR__ . '/../' . $old_logo);
            }
            $pdo->prepare("DELETE FROM site_settings WHERE setting_key = 'site_logo'")->execute();
            $_SESSION['flash_success'] = 'Site logo has been removed. ';
        }

        // Handle Text Updates
        $allowed_keys = [
            'company_name', 'company_email', 'company_phone', 'whatsapp_number', 
            'office_address', 'business_hours', 'facebook_link', 'instagram_link', 
            'currency_symbol', 'x_link', 'tiktok_link', 'youtube_link'
        ];
        
        $update_stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                                      VALUES (:key, :value) 
                                      ON DUPLICATE KEY UPDATE setting_value = :value2");
        
        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $val = trim($_POST[$key]);
                $update_stmt->execute([
                    'key' => $key,
                    'value' => $val,
                    'value2' => $val
                ]);
            }
        }

        // Handle Logo Image Upload
        if (!empty($_FILES['site_logo']['name'])) {
            $target_dir = __DIR__ . '/../assets/images/';
            
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'svg', 'webp'];

            if (in_array($file_extension, $allowed_exts)) {
                $new_filename = 'brand_logo_' . time() . '.' . $file_extension;
                $target_filepath = $target_dir . $new_filename;
                $database_relative_path = 'assets/images/' . $new_filename;

                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target_filepath)) {
                    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'site_logo' LIMIT 1");
                    $stmt->execute();
                    $existing_logo = $stmt->fetchColumn();
                    if ($existing_logo && file_exists(__DIR__ . '/../' . $existing_logo)) {
                        unlink(__DIR__ . '/../' . $existing_logo);
                    }

                    $update_stmt->execute([
                        'key' => 'site_logo',
                        'value' => $database_relative_path,
                        'value2' => $database_relative_path
                    ]);
                } else {
                    $local_error = "Failed to move uploaded logo file to the server folder.";
                }
            } else {
                $local_error = "Invalid logo format. Only JPG, PNG, SVG, and WEBP are allowed.";
            }
        }

        if (empty($local_error)) {
            // Append success message if we didn't already set one (like from deleting the logo)
            if (!isset($_SESSION['flash_success'])) {
                $_SESSION['flash_success'] = 'Settings successfully updated.';
            }
        } else {
            $_SESSION['flash_error'] = $local_error;
        }
    } else {
        $_SESSION['flash_error'] = 'Security token mismatch. Update rejected.';
    }

    // AUTOMATIC BACKGROUND REDIRECT: This prevents the message from staying permanently!
    header("Location: site_settings.php");
    exit();
}

// 4. FETCH CURRENT SETTINGS DATA
$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
$settings = [];
foreach ($settings_raw as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// 5. NOW IT IS SAFE TO LOAD THE HTML HEADER AND CSS
require_once __DIR__ . '/includes/admin_header.php';
?>

<style>
/* Base Layout */
.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    align-items: start;
}

.settings-panel {
    background: white;
    padding: 30px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.settings-panel h4 {
    margin-top: 0;
    color: #0f172a;
    font-size: 16px;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 16px;
}

.form-group label {
    font-weight: 600;
    color: #475569;
    font-size: 13px;
}

/* Ensure inputs never break out of their containers */
.form-group input,
.form-group textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.logo-preview {
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
    margin-bottom: 15px;
}

.logo-preview img {
    max-width: 100%;
    max-height: 100px;
    object-fit: contain;
}

/* Sticky Action Bar */
.action-bar {
    background: white;
    padding: 20px 30px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
    position: sticky;
    bottom: 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    z-index: 100;
}

.btn-save {
    background: #0f172a;
    color: white;
    padding: 14px 28px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 15px;
    width: auto;
    transition: background 0.2s;
    text-align: center;
}

.btn-save:hover {
    background: #334155;
}

/* Mobile Media Queries */
@media (max-width: 900px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .settings-panel {
        padding: 20px;
        /* Reduce bulk on mobile */
    }

    .contact-grid {
        grid-template-columns: 1fr;
        /* Stack email and phone */
    }

    .action-bar {
        padding: 15px;
        bottom: 0;
        border-radius: 0;
        border-left: none;
        border-right: none;
        border-bottom: none;
        margin-left: -16px;
        /* Counteracts main-viewport padding if present */
        margin-right: -16px;
        width: 100vw;
        justify-content: center;
    }

    .btn-save {
        width: 100%;
        /* Full width button on small screens */
    }
}
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h3 style="margin: 0; font-size: 24px; color: #1e293b;">General Settings</h3>
</div>

<?php if (!empty($success_msg)): ?>
<div
    style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size:14px; font-weight: 500; border: 1px solid #bbf7d0;">
    ✅ <?php echo sanitize($success_msg); ?>
</div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
<div
    style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size:14px; font-weight: 500; border: 1px solid #fca5a5;">
    ⚠️ <?php echo sanitize($error_msg); ?>
</div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <?php csrf_input_field(); ?>

    <div class="settings-grid">

        <div class="settings-panel">
            <h4>Brand Identity & Assets</h4>

            <div class="form-group">
                <label>Current Website Logo</label>
                <div class="logo-preview">
                    <?php if (!empty($settings['site_logo']) && file_exists(__DIR__ . '/../' . $settings['site_logo'])): ?>
                    <img src="<?php echo BASE_URL . $settings['site_logo']; ?>" alt="Site Logo">
                    <div style="margin-top: 10px;">
                        <input type="checkbox" name="delete_logo" value="yes" id="delete_logo">
                        <label for="delete_logo" style="color: #dc2626; font-size: 13px; cursor: pointer;">Delete
                            current logo</label>
                    </div>
                    <?php else: ?>
                    <span style="color: #94a3b8; font-size: 13px;">No logo uploaded. System will use text name.</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Upload New Logo (.png, .svg, .webp transparent recommended)</label>
                <input type="file" name="site_logo" accept="image/png, image/jpeg, image/svg+xml, image/webp"
                    style="background: #f8fafc;">
            </div>

            <div class="form-group">
                <label>Dealership Name</label>
                <input type="text" name="company_name" value="<?php echo sanitize($settings['company_name'] ?? ''); ?>"
                    required>
            </div>

            <div class="form-group" style="max-width: 150px;">
                <label>Currency Symbol</label>
                <input type="text" name="currency_symbol"
                    value="<?php echo sanitize($settings['currency_symbol'] ?? '$'); ?>" maxlength="3"
                    style="text-align: center;">
            </div>
        </div>

        <div class="settings-panel">
            <h4>Contact Details & Operations</h4>

            <div class="contact-grid">
                <div class="form-group">
                    <label>Public Email Address</label>
                    <input type="email" name="company_email"
                        value="<?php echo sanitize($settings['company_email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Primary Telephone</label>
                    <input type="text" name="company_phone"
                        value="<?php echo sanitize($settings['company_phone'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Physical Showroom Address</label>
                <textarea name="office_address"
                    rows="2"><?php echo sanitize($settings['office_address'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Operating Hours</label>
                <input type="text" name="business_hours"
                    value="<?php echo sanitize($settings['business_hours'] ?? 'Mon - Sat: 9:00 AM - 7:00 PM'); ?>">
            </div>

            <div class="form-group">
                <label>WhatsApp Link Number (Format: 15552345678)</label>
                <input type="text" name="whatsapp_number"
                    value="<?php echo sanitize($settings['whatsapp_number'] ?? ''); ?>">
            </div>

            <h4 style="margin-top: 30px;">Social Media Links</h4>

            <div class="form-group">
                <label>Facebook Page URL</label>
                <input type="url" name="facebook_link" value="<?php echo sanitize($settings['facebook_link'] ?? ''); ?>"
                    placeholder="https://facebook.com/yourpage">
            </div>

            <div class="form-group">
                <label>Instagram Profile URL</label>
                <input type="url" name="instagram_link"
                    value="<?php echo sanitize($settings['instagram_link'] ?? ''); ?>"
                    placeholder="https://instagram.com/yourprofile">
            </div>

            <div class="form-group">
                <label>X (Twitter) Profile URL</label>
                <input type="url" name="x_link" value="<?php echo sanitize($settings['x_link'] ?? ''); ?>"
                    placeholder="https://x.com/yourprofile">
            </div>

            <div class="form-group">
                <label>TikTok Profile URL</label>
                <input type="url" name="tiktok_link" value="<?php echo sanitize($settings['tiktok_link'] ?? ''); ?>"
                    placeholder="https://tiktok.com/@yourprofile">
            </div>

            <div class="form-group">
                <label>YouTube Channel URL</label>
                <input type="url" name="youtube_link" value="<?php echo sanitize($settings['youtube_link'] ?? ''); ?>"
                    placeholder="https://youtube.com/@yourchannel">
            </div>
        </div>

    </div>

    <div class="action-bar">
        <button type="submit" class="btn-save">Save Settings</button>
    </div>

</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>