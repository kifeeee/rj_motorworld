<?php
// sell_trade.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/csrf_token.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security token mismatch. Form submission rejected.';
    } else {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        
        // Compile trade-in details into a structured text block
        $trade_year = trim($_POST['trade_year']);
        $trade_make = trim($_POST['trade_make']);
        $trade_model = trim($_POST['trade_model']);
        $trade_mileage = trim($_POST['trade_mileage']);
        $trade_condition = trim($_POST['trade_condition']);
        $trade_vin = trim($_POST['trade_vin']);
        
        $trade_details = "Year: $trade_year | Make: $trade_make | Model: $trade_model | Mileage: $trade_mileage | Cond: $trade_condition | VIN: $trade_vin";

        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($trade_make) || empty($trade_model)) {
            $error_msg = 'Please complete all required fields.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO leads (lead_type, first_name, last_name, email, phone, trade_in_details, status) 
                                       VALUES ('Trade-In Valuation', :fname, :lname, :email, :phone, :trade, 'New')");
                $stmt->execute([
                    'fname' => $first_name,
                    'lname' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'trade' => $trade_details
                ]);
                $success_msg = 'Your vehicle information has been successfully submitted! Our appraisers will review the details and contact you with an estimated offer within 24 hours.';
            } catch (Exception $e) {
                $error_msg = 'Database error submitting request. Please try again.';
            }
        }
    }
}
?>

<style>
/* ──────────────────────────────────────────
   SELL/TRADE PAGE  —  Luxury Layout
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
    --red: #c0392b;
    --ease: cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    background-color: var(--ivory);
    font-family: 'Outfit', sans-serif;
}

.trade-hero {
    background: var(--obsidian);
    color: var(--white);
    padding: 100px 5%;
    text-align: center;
    position: relative;
    border-bottom: 2px solid var(--gold-deep);
}

/* Subtle background texture for hero */
.trade-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
    opacity: 0.03;
    pointer-events: none;
}

.trade-hero h1 {
    font-family: 'Playfair Display', serif;
    margin: 0 0 16px 0;
    font-size: clamp(36px, 5vw, 56px);
    font-weight: 500;
    letter-spacing: -0.01em;
    position: relative;
    z-index: 2;
}

.trade-hero h1 em {
    font-style: italic;
    color: var(--gold-light);
}

.trade-hero p {
    color: rgba(255, 255, 255, 0.65);
    max-width: 650px;
    margin: 0 auto;
    line-height: 1.7;
    font-size: 16px;
    font-weight: 300;
    position: relative;
    z-index: 2;
}

.form-container {
    max-width: 900px;
    margin: -40px auto 80px auto;
    background: #fff;
    padding: 50px;
    border-radius: 2px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--border);
    position: relative;
    z-index: 10;
}

.section-label {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    color: var(--ink);
    font-weight: 500;
    border-bottom: 1px solid var(--border);
    padding-bottom: 16px;
    margin-bottom: 24px;
    margin-top: 40px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-label i {
    color: var(--gold);
    font-size: 18px;
}

.section-label:first-child {
    margin-top: 0;
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

select.form-control {
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' viewBox='0 0 12 7'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23999' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    cursor: pointer;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
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
    margin-top: 30px;
}

.btn-submit:hover {
    background: var(--gold);
    border-color: var(--gold);
    color: var(--obsidian);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(196, 151, 74, 0.2);
}

.btn-secondary {
    background: transparent;
    color: var(--obsidian);
    border: 1px solid var(--obsidian);
}

.btn-secondary:hover {
    background: var(--obsidian);
    color: var(--white);
    box-shadow: none;
}

.alert-success {
    background: rgba(196, 151, 74, 0.05);
    color: var(--ink);
    padding: 40px 30px;
    border-radius: 2px;
    border: 1px solid var(--gold);
    text-align: center;
    margin-bottom: 20px;
}

.alert-success h3 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    margin: 0 0 15px 0;
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

@media (max-width: 768px) {

    .grid-2,
    .grid-3 {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .form-container {
        padding: 30px 20px;
        margin-top: -20px;
    }

    .trade-hero {
        padding: 60px 5% 80px;
    }
}
</style>

<div class="trade-hero">
    <h1>Value Your Trade or <em>Sell to Us</em></h1>
    <p>Skip the hassle of private sales. Get a competitive, instant cash offer or apply the value directly toward your
        next vehicle from our curated inventory.</p>
</div>

<div class="form-container">
    <?php if ($success_msg): ?>
    <div class="alert-success">
        <h3 style="margin-top:0;">Appraisal Request Received</h3>
        <p><?php echo $success_msg; ?></p>
    </div>
    <div style="text-align: center; margin-top: 30px; max-width: 400px; margin-left: auto; margin-right: auto;">
        <a href="vehicles.php" class="btn-submit btn-secondary" style="text-decoration:none;">
            Browse Our Showroom <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
    <?php else: ?>

    <?php if ($error_msg): ?>
    <div class="alert-error"><i class="fa-solid fa-circle-exclamation" style="margin-right: 8px;"></i>
        <?php echo sanitize($error_msg); ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <?php csrf_input_field(); ?>

        <div class="section-label"><i class="fa-regular fa-id-card"></i> 1. Contact Information</div>
        <div class="grid-2">
            <div class="form-group">
                <label>First Name <span>*</span></label>
                <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
            </div>
            <div class="form-group">
                <label>Last Name <span>*</span></label>
                <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
            </div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Email Address <span>*</span></label>
                <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>Phone Number <span>*</span></label>
                <input type="tel" name="phone" class="form-control" placeholder="Contact number" required>
            </div>
        </div>

        <div class="section-label"><i class="fa-solid fa-car-side"></i> 2. Vehicle Specifications</div>
        <div class="grid-3">
            <div class="form-group">
                <label>Year <span>*</span></label>
                <input type="number" name="trade_year" class="form-control" required placeholder="e.g. 2018" min="1990"
                    max="<?php echo date('Y')+1; ?>">
            </div>
            <div class="form-group">
                <label>Make/Brand <span>*</span></label>
                <input type="text" name="trade_make" class="form-control" required placeholder="e.g. Mercedes-Benz">
            </div>
            <div class="form-group">
                <label>Model <span>*</span></label>
                <input type="text" name="trade_model" class="form-control" required placeholder="e.g. E-Class">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Current Mileage <span>*</span></label>
                <input type="number" name="trade_mileage" class="form-control" required placeholder="e.g. 45000">
            </div>
            <div class="form-group">
                <label>Vehicle Condition <span>*</span></label>
                <select name="trade_condition" class="form-control" required>
                    <option value="">Select Condition...</option>
                    <option value="Excellent">Excellent (Looks and drives like new)</option>
                    <option value="Good">Good (Minor wear and tear, mechanically sound)</option>
                    <option value="Fair">Fair (Needs some mechanical or cosmetic repair)</option>
                    <option value="Poor">Poor (Major repairs needed)</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>VIN (Vehicle Identification Number) - <i>Optional for faster processing</i></label>
            <input type="text" name="trade_vin" class="form-control" maxlength="17"
                placeholder="17-Digit alphanumeric VIN">
        </div>

        <button type="submit" class="btn-submit">
            Submit for Appraisal <i class="fa-solid fa-arrow-right"></i>
        </button>
    </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>