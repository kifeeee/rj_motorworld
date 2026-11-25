<?php
// book_test_drive.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/csrf_token.php';

$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_msg = '';
$error_msg = '';

// Fetch vehicle details to display what they are booking
$stmt = $pdo->prepare("SELECT year, brand, model, vin FROM vehicles WHERE id = :id AND availability_status = 'Available' LIMIT 1");
$stmt->execute(['id' => $vehicle_id]);
$car = $stmt->fetch();

if (!$car && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<div style='padding: 100px 5%; text-align: center;'><h2>Invalid Vehicle Selection.</h2><a href='vehicles.php' class='btn-primary'>Return to Inventory</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security session expired. Please refresh and try again.';
    } else {
        $v_id = (int)$_POST['vehicle_id'];
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $preferred_date = $_POST['preferred_date'];
        $preferred_time = $_POST['preferred_time'];

        if (empty($full_name) || empty($email) || empty($phone) || empty($preferred_date) || empty($preferred_time)) {
            $error_msg = 'Please fill out all contact and scheduling fields.';
        } else {
            try {
                $insert = $pdo->prepare("INSERT INTO test_drives (vehicle_id, full_name, email, phone, preferred_date, preferred_time, status) 
                                         VALUES (:v_id, :name, :email, :phone, :date, :time, 'Pending')");
                $insert->execute([
                    'v_id' => $v_id,
                    'name' => $full_name,
                    'email' => $email,
                    'phone' => $phone,
                    'date' => $preferred_date,
                    'time' => $preferred_time
                ]);
                $success_msg = 'Your test drive request has been received! Our sales team will call you shortly to confirm the appointment.';
            } catch (Exception $e) {
                $error_msg = 'System error processing request. Please call our sales line directly.';
            }
        }
    }
}
?>

<style>
.form-container {
    max-width: 600px;
    margin: 60px auto;
    background: white;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--border);
}

.form-container h2 {
    margin-top: 0;
    color: var(--primary);
}

.car-summary {
    background: var(--light);
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid var(--secondary);
    margin-bottom: 25px;
    font-size: 14px;
    color: #475569;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 14px;
    color: #475569;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 15px;
    box-sizing: border-box;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.btn-submit {
    width: 100%;
    background: var(--primary);
    color: white;
    border: none;
    padding: 14px;
    font-size: 16px;
    font-weight: 700;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 10px;
}

.btn-submit:hover {
    background: var(--secondary);
    color: var(--primary);
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
}
</style>

<div class="form-container">
    <h2>Schedule a Test Drive</h2>

    <?php if ($success_msg): ?>
    <div class="alert-success">✅ <?php echo $success_msg; ?></div>
    <a href="vehicles.php" class="btn-submit"
        style="display:inline-block; text-align:center; text-decoration:none; box-sizing:border-box;">Browse More
        Vehicles</a>
    <?php else: ?>

    <?php if ($error_msg): ?>
    <div class="alert-error">⚠️ <?php echo sanitize($error_msg); ?></div>
    <?php endif; ?>

    <?php if ($car): ?>
    <div class="car-summary">
        <strong>Target Vehicle:</strong>
        <?php echo sanitize($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']); ?><br>
        <span style="font-size: 12px; font-family: monospace;">VIN: <?php echo sanitize($car['vin']); ?></span>
    </div>
    <?php endif; ?>

    <form action="" method="POST">
        <?php csrf_input_field(); ?>
        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">

        <div class="form-group">
            <label>Full Legal Name</label>
            <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="john@example.com">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control" required placeholder="(555) 123-4567">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Preferred Date</label>
                <!-- Restrict dates to tomorrow onwards -->
                <input type="date" name="preferred_date" class="form-control" required
                    min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            </div>
            <div class="form-group">
                <label>Preferred Time Slot</label>
                <select name="preferred_time" class="form-control" required>
                    <option value="">Select a time...</option>
                    <option value="09:00:00">09:00 AM</option>
                    <option value="11:00:00">11:00 AM</option>
                    <option value="13:30:00">01:30 PM</option>
                    <option value="15:30:00">03:30 PM</option>
                    <option value="17:00:00">05:00 PM</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn-submit">Confirm Appointment Request</button>
        <p style="font-size: 12px; color: #64748b; text-align: center; margin-top: 15px;">Please bring a valid driver's
            license to your appointment.</p>
    </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>