<?php
// contact.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/csrf_token.php';

$success_msg = '';
$error_msg = '';

// Check if user came from a specific vehicle page
$target_vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : null;
$target_vehicle_name = '';

if ($target_vehicle_id) {
    $stmt = $pdo->prepare("SELECT year, brand, model FROM vehicles WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $target_vehicle_id]);
    $car = $stmt->fetch();
    if ($car) {
        $target_vehicle_name = $car['year'] . ' ' . $car['brand'] . ' ' . $car['model'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security token mismatch. Form submission rejected.';
    } else {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $message = trim($_POST['message']);
        $v_id = !empty($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;

        if (empty($first_name) || empty($last_name) || empty($email) || empty($message)) {
            $error_msg = 'Please complete all required fields.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO leads (vehicle_id, lead_type, first_name, last_name, email, phone, message, status) 
                                       VALUES (:vid, 'General Inquiry', :fname, :lname, :email, :phone, :msg, 'New')");
                $stmt->execute([
                    'vid' => $v_id,
                    'fname' => $first_name,
                    'lname' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'msg' => $message
                ]);
                $success_msg = 'Thank you for reaching out! A member of our team will get back to you shortly.';
            } catch (Exception $e) {
                $error_msg = 'Database error submitting message. Please try again.';
            }
        }
    }
}

// Fetch dynamic contact info from database settings
$co_email = get_site_setting($pdo, 'company_email', 'info@rjmotorworld.com');
$co_phone = get_site_setting($pdo, 'company_phone', '(555) 123-4567');
$co_address = get_site_setting($pdo, 'office_address', '123 Dealer Drive, Motor City, MI');
$business_hours = get_site_setting($pdo, 'business_hours', 'Mon - Sat: 9:00 AM - 7:00 PM');
?>

<style>
/* ──────────────────────────────────────────
   CONTACT PAGE  —  Luxury Layout
────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap');

:root {
    --obsidian: #0a0a0e;
    --ink: #10101a;
    --dim: #4a4a60;
    --mist: #7a7a96;
    --silver: #b8b8cc;
    --ivory: #f8f4ed;
    --white: #fdfdfd;
    --gold-deep: #8a6830;
    --gold: #c4974a;
    --gold-light: #e8c880;
    --border: #e2dcd0;
    --ease: cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    background-color: var(--ivory);
}

.contact-wrapper {
    font-family: 'Outfit', sans-serif;
    padding: 80px 5% 100px;
    max-width: 1300px;
    margin: 0 auto;
}

.contact-header {
    text-align: center;
    margin-bottom: 60px;
}

.contact-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(36px, 5vw, 52px);
    color: var(--ink);
    margin: 0 0 16px 0;
    font-weight: 500;
    letter-spacing: -0.01em;
}

.contact-header h1 em {
    font-style: italic;
    color: var(--gold-deep);
}

.contact-header p {
    color: var(--dim);
    font-size: 16px;
    font-weight: 300;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.7;
}

.contact-container {
    display: flex;
    gap: 60px;
    align-items: flex-start;
}

/* ── LEFT: CONTACT INFO CARDS ── */
.contact-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-card {
    background: #fff;
    padding: 35px 30px;
    border-radius: 2px;
    border: 1px solid var(--border);
    display: flex;
    gap: 20px;
    align-items: flex-start;
    transition: transform 0.3s var(--ease), box-shadow 0.3s var(--ease);
}

.info-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.04);
}

.info-icon {
    width: 50px;
    height: 50px;
    border: 1px solid rgba(196, 151, 74, 0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gold);
    font-size: 18px;
    flex-shrink: 0;
    background: rgba(196, 151, 74, 0.05);
}

.info-content h3 {
    font-family: 'Playfair Display', serif;
    margin: 0 0 8px 0;
    color: var(--ink);
    font-size: 20px;
    font-weight: 600;
}

.info-content p {
    margin: 0;
    color: var(--dim);
    font-size: 14.5px;
    line-height: 1.6;
    font-weight: 300;
}

.info-content a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}

.info-content a:hover {
    color: var(--gold-deep);
}

/* ── RIGHT: CONTACT FORM ── */
.contact-form-wrapper {
    flex: 1.4;
    background: #fff;
    padding: 50px;
    border-radius: 2px;
    border: 1px solid var(--border);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.03);
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 12px;
    color: var(--dim);
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.form-group label span {
    color: var(--red);
}

.form-control {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid var(--border);
    border-radius: 2px;
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    color: var(--ink);
    background: var(--ivory);
    box-sizing: border-box;
    transition: border-color 0.3s var(--ease), background 0.3s var(--ease);
}

.form-control:focus {
    outline: none;
    border-color: var(--gold);
    background: #fff;
}

.form-control::placeholder {
    color: var(--mist);
    font-weight: 300;
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.btn-submit {
    background: var(--obsidian);
    color: var(--white);
    border: 1px solid var(--obsidian);
    padding: 18px;
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    border-radius: 2px;
    cursor: pointer;
    transition: all 0.3s var(--ease);
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.btn-submit:hover {
    background: var(--gold);
    border-color: var(--gold);
    color: var(--obsidian);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(196, 151, 74, 0.2);
}

/* ── ALERTS & NOTICES ── */
.alert-success {
    background: rgba(196, 151, 74, 0.05);
    color: var(--ink);
    padding: 30px;
    border-radius: 2px;
    border: 1px solid var(--gold);
    text-align: center;
}

.alert-success h3 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    margin: 0 0 10px 0;
    color: var(--gold-deep);
}

.alert-error {
    background: #fdf2f2;
    color: var(--red);
    padding: 16px 20px;
    border-radius: 2px;
    margin-bottom: 30px;
    font-weight: 500;
    font-size: 14px;
    border-left: 3px solid var(--red);
}

.vehicle-context-notice {
    background: var(--ivory);
    padding: 16px 20px;
    border-left: 3px solid var(--gold);
    margin-bottom: 30px;
    font-size: 13.5px;
    color: var(--ink);
    display: flex;
    align-items: center;
    gap: 12px;
}

.vehicle-context-notice i {
    color: var(--gold-deep);
    font-size: 16px;
}

/* ── RESPONSIVE ── */
@media (max-width: 992px) {
    .contact-container {
        flex-direction: column-reverse;
    }

    .contact-info {
        width: 100%;
        flex-direction: row;
        flex-wrap: wrap;
    }

    .info-card {
        flex: 1;
        min-width: 250px;
    }
}

@media (max-width: 768px) {
    .contact-wrapper {
        padding: 40px 5% 60px;
    }

    .grid-2 {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .contact-form-wrapper {
        padding: 30px 20px;
    }

    .info-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}
</style>

<div class="contact-wrapper">
    <div class="contact-header">
        <h1>Connect with <em>RJ Motorworld</em></h1>
        <p>Whether you are inquiring about a specific masterpiece in our showroom, exploring tailored financing options,
            or discussing a premium trade-in, our advisory team is at your disposal.</p>
    </div>

    <div class="contact-container">

        <div class="contact-info">
            <div class="info-card">
                <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                <div class="info-content">
                    <h3>Showroom Location</h3>
                    <p><?php echo sanitize($co_address); ?></p>
                </div>
            </div>

            <div class="info-card">
                <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                <div class="info-content">
                    <h3>Sales & Concierge</h3>
                    <p><a
                            href="tel:<?php echo preg_replace('/[^0-9+]/', '', $co_phone); ?>"><?php echo sanitize($co_phone); ?></a>
                    </p>
                    <p
                        style="font-size: 12px; margin-top: 6px; color: var(--gold-deep); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">
                        <?php echo sanitize($business_hours); ?></p>
                </div>
            </div>

            <div class="info-card">
                <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                <div class="info-content">
                    <h3>Digital Correspondence</h3>
                    <p><a href="mailto:<?php echo sanitize($co_email); ?>"><?php echo sanitize($co_email); ?></a></p>
                </div>
            </div>
        </div>

        <div class="contact-form-wrapper">
            <?php if ($success_msg): ?>
            <div class="alert-success">
                <h3>Inquiry Received</h3>
                <p><?php echo $success_msg; ?></p>
            </div>
            <?php else: ?>

            <?php if ($error_msg): ?>
            <div class="alert-error"><i class="fa-solid fa-circle-exclamation" style="margin-right: 8px;"></i>
                <?php echo sanitize($error_msg); ?></div>
            <?php endif; ?>

            <?php if ($target_vehicle_name): ?>
            <div class="vehicle-context-notice">
                <i class="fa-solid fa-car-side"></i>
                <div>
                    <strong>Subject Vehicle:</strong> <?php echo sanitize($target_vehicle_name); ?>
                </div>
            </div>
            <?php endif; ?>

            <form action="" method="POST">
                <?php csrf_input_field(); ?>
                <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($target_vehicle_id); ?>">

                <div class="grid-2">
                    <div class="form-group">
                        <label>First Name <span>*</span></label>
                        <input type="text" name="first_name" class="form-control" placeholder="Enter your first name"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Last Name <span>*</span></label>
                        <input type="text" name="last_name" class="form-control" placeholder="Enter your last name"
                            required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Email Address <span>*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Optional contact number">
                    </div>
                </div>

                <div class="form-group">
                    <label>How may we assist you? <span>*</span></label>
                    <textarea name="message" class="form-control" required
                        placeholder="Please provide details regarding your inquiry..."></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    Send Inquiry <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>