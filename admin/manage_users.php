<?php
// admin/manage_users.php
require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../includes/csrf_token.php';

$error = '';
$success = '';

// Check if the current user is a SuperAdmin
$is_superadmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'SuperAdmin');

// Catch messages from delete_user.php
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $success = 'Staff account successfully removed from the system.';
}
if (isset($_GET['error']) && $_GET['error'] === 'self_delete') {
    $error = 'Security constraint: You cannot delete your own active session account.';
}
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = 'Access Denied: You do not have permission to delete users.';
}

// Process New Account Deployment (BACKEND SECURITY CHECK)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (!$is_superadmin) {
        $error = 'Access Denied: Only SuperAdmins can provision new profiles.';
    } elseif (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security Token mismatch.';
    } else {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $password = trim($_POST['password']);
        $role = $_POST['role'];

        if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
            $error = 'All fields are required.';
        } else {
            // Check for duplicates
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
            $stmt->execute(['u' => $username, 'e' => $email]);
            if ($stmt->fetch()) {
                $error = 'Conflict: Username or Email already in use.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $ins = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role, status) VALUES (:u, :p, :e, :fn, :r, 'Active')");
                $ins->execute([
                    'u' => $username, 'p' => $hashed_password, 'e' => $email, 'fn' => $full_name, 'r' => $role
                ]);
                $success = 'Staff account provisioned and activated.';
            }
        }
    }
}

// Fetch Active Users Database Stack
$users = $pdo->query("SELECT id, username, email, full_name, role, status, created_at FROM users ORDER BY id ASC")->fetchAll();
?>

<style>
/* Page-Specific Layout Styles */
.users-layout {
    display: grid;
    /* If they aren't a superadmin, we just show a 1-column layout for the table */
    grid-template-columns: <?php echo $is_superadmin ? '1fr 2fr': '1fr';
    ?>;
    gap: 30px;
    align-items: start;
}

.provision-form,
.restricted-notice {
    background: white;
    padding: 24px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.restricted-notice {
    text-align: center;
    background: #f8fafc;
}

.form-group {
    margin-bottom: 12px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 6px;
    color: #475569;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    box-sizing: border-box;
    font-family: inherit;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
}

.btn-deploy {
    width: 100%;
    background: #0f172a;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 8px;
}

.btn-deploy:hover {
    background: #1e293b;
}

.btn-remove {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-remove:hover {
    background: #fecaca;
    border-color: #f87171;
}

/* Mobile Adjustments */
@media (max-width: 768px) {
    .users-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .provision-form,
    .restricted-notice {
        padding: 16px;
    }
}
</style>

<h3>Staff Account Allocation Operations</h3>

<?php if (!empty($error)): ?>
<div
    style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius:6px; margin-bottom:20px; box-sizing: border-box;">
    <?php echo sanitize($error); ?>
</div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<div
    style="background: #dcfce7; color: #166534; padding: 12px; border-radius:6px; margin-bottom:20px; box-sizing: border-box;">
    <?php echo sanitize($success); ?>
</div>
<?php endif; ?>

<div class="users-layout">

    <?php if ($is_superadmin): ?>
    <form action="" method="POST" class="provision-form">
        <?php csrf_input_field(); ?>
        <h4 style="margin-top: 0; color: #0f172a; margin-bottom: 20px;">Provision New Profile</h4>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Access Role</label>
            <select name="role">
                <option value="Sales">Sales Personnel</option>
                <option value="Manager">Manager</option>
                <option value="SuperAdmin">SuperAdmin</option>
            </select>
        </div>
        <button type="submit" name="add_user" class="btn-deploy">Deploy User Profile</button>
    </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Profile Info</th>
                    <th>Role Tier</th>
                    <th>Registered</th>
                    <?php if ($is_superadmin): ?>
                    <th style="text-align: right;">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <strong><?php echo sanitize($u['full_name']); ?></strong> <br>
                        <span style="font-size:12px; color:#64748b;">@<?php echo sanitize($u['username']); ?> |
                            <?php echo sanitize($u['email']); ?></span>
                    </td>
                    <td>
                        <span
                            style="font-weight: 500; font-size:13px; color: <?php echo $u['role'] === 'SuperAdmin' ? '#0284c7' : '#475569'; ?>">
                            <?php echo sanitize($u['role']); ?>
                        </span>
                    </td>
                    <td style="font-size: 12px; color: #64748b;">
                        <?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>

                    <?php if ($is_superadmin): ?>
                    <td style="text-align: right;">
                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                        <form action="delete_user.php" method="POST"
                            onsubmit="return confirm('Permanently remove this user?');" style="display:inline;">
                            <?php csrf_input_field(); ?>
                            <input type="hidden" name="delete_id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn-remove">Remove</button>
                        </form>
                        <?php else: ?>
                        <span style="font-size: 12px; color: #94a3b8; font-style: italic;">(You)</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>