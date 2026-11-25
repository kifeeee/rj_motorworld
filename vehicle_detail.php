<?php
// vehicle_detail.php
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch core vehicle data
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = :id AND availability_status = 'Available' LIMIT 1");
$stmt->execute(['id' => $id]);
$car = $stmt->fetch();

if (!$car) {
    echo "<div style='padding: 120px 5%; text-align: center; font-family: \"Outfit\", sans-serif; background-color: var(--ivory); min-height: 60vh;'>
            <h2 style='font-family: \"Playfair Display\", serif; font-size: 36px; margin-bottom: 20px; color: var(--ink);'>Vehicle not found or no longer available.</h2>
            <a href='vehicles.php' style='display: inline-flex; align-items: center; gap: 10px; background: var(--gold); color: var(--obsidian); padding: 14px 34px; text-transform: uppercase; font-weight: 600; font-size: 13px; letter-spacing: 0.1em; border-radius: 2px; text-decoration: none; transition: transform 0.2s;' onmouseover='this.style.transform=\"translateY(-2px)\"' onmouseout='this.style.transform=\"none\"'><i class=\"fa-solid fa-arrow-left\"></i> Return to Inventory</a>
          </div>";
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Fetch all gallery images for this vehicle
$img_stmt = $pdo->prepare("SELECT image_path, is_main FROM vehicle_images WHERE vehicle_id = :id ORDER BY is_main DESC, id ASC");
$img_stmt->execute(['id' => $id]);
$images = $img_stmt->fetchAll();

// Fetch Similar Vehicles (Same brand, different ID, available status)
$sim_stmt = $pdo->prepare("
    SELECT v.*, 
           (SELECT image_path FROM vehicle_images vi WHERE vi.vehicle_id = v.id ORDER BY is_main DESC LIMIT 1) as main_image
    FROM vehicles v 
    WHERE v.brand = :brand 
      AND v.id != :id 
      AND v.availability_status = 'Available' 
    ORDER BY v.id DESC 
    LIMIT 3
");
$sim_stmt->execute(['brand' => $car['brand'], 'id' => $id]);
$similar_vehicles = $sim_stmt->fetchAll();

// Formatting helpers for tags
$comfort_features = array_filter(array_map('trim', explode(',', $car['features'])));
$safety_features = array_filter(array_map('trim', explode(',', $car['safety_features'])));

// WhatsApp Link Generation
$whatsapp_number = get_site_setting($pdo, 'whatsapp_number', '254700000000'); 
$clean_wa = preg_replace('/[^0-9]/', '', $whatsapp_number);
$vehicle_name = sanitize($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']);
$display_price = $car['discount_price'] ?: $car['price'];
$wa_msg = urlencode("Hello RJ Motorworld, I am interested in the {$vehicle_name} (Ref ID: {$car['id']}) listed for KSh " . number_format($display_price) . ". Is it still available?");
$wa_link = "https://wa.me/{$clean_wa}?text={$wa_msg}";

?>

<style>
/* ──────────────────────────────────────────
   VEHICLE DETAIL PAGE  —  Edge-to-Edge Luxury
────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap');

:root {
    --obsidian: #080808;
    --ink: #10101a;
    --pitch: #181818;
    --steel: #252525;
    --dim: #4a4a60;
    --mist: #7a7a96;
    --silver: #b8b8cc;
    --pearl: #eeeadf;
    --ivory: #f8f4ed;
    --white: #ffffff;
    --gold-deep: #8a6830;
    --gold: #c4974a;
    --gold-light: #e8c880;
    --red: #a91e1e;
    --border: #e6e2d8;
    --ease: cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    background: var(--ivory);
}

/* Expanded Breadcrumb */
.breadcrumb {
    max-width: 1800px;
    margin: 30px auto 0;
    padding: 0 4%;
    font-family: 'Outfit', sans-serif;
    font-size: 11.5px;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--mist);
}

.breadcrumb a {
    color: var(--ink);
    transition: color 0.2s;
}

.breadcrumb a:hover {
    color: var(--gold);
}

.breadcrumb span {
    margin: 0 8px;
    color: var(--silver);
}

/* Expanded Full-Width Container */
.detail-container {
    display: flex;
    gap: 4%;
    padding: 30px 4% 80px;
    align-items: flex-start;
    max-width: 1800px;
    margin: 0 auto;
    font-family: 'Outfit', sans-serif;
}

/* ── LEFT COLUMN: GALLERY & DESCRIPTION ── */
.detail-left {
    flex: 1 1 65%;
    min-width: 0;
}

.main-image-container {
    width: 100%;
    height: clamp(400px, 70vh, 800px);
    background: var(--pitch);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 24px;
    position: relative;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06);
}

.main-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.4s var(--ease);
}

.thumbnail-track {
    display: flex;
    gap: 14px;
    overflow-x: auto;
    padding-bottom: 15px;
    scrollbar-width: thin;
    scrollbar-color: var(--gold) var(--border);
}

.thumbnail-track::-webkit-scrollbar {
    height: 3px;
}

.thumbnail-track::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
}

.thumbnail-track::-webkit-scrollbar-thumb {
    background: var(--gold);
}

.thumb {
    width: 160px;
    height: 105px;
    border-radius: 4px;
    cursor: pointer;
    opacity: 0.4;
    transition: all 0.3s var(--ease);
    flex-shrink: 0;
    overflow: hidden;
    background: var(--pitch);
    position: relative;
}

.thumb::after {
    content: '';
    position: absolute;
    inset: 0;
    border: 2px solid transparent;
    border-radius: 4px;
    transition: border-color 0.3s;
}

.thumb:hover {
    opacity: 0.8;
}

.thumb.active {
    opacity: 1;
}

.thumb.active::after {
    border-color: var(--gold);
}

.thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.content-box {
    background: var(--white);
    padding: 50px;
    border-radius: 4px;
    margin-top: 40px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
    border: 1px solid rgba(0, 0, 0, 0.03);
}

.content-box h3 {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    margin-top: 0;
    color: var(--ink);
    padding-bottom: 20px;
    margin-bottom: 25px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 15px;
}

.content-box h3::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

.content-box p {
    line-height: 1.85;
    color: var(--dim);
    font-size: 15.5px;
    font-weight: 300;
}

.feature-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.tag {
    background: var(--ivory);
    border: 1px solid var(--border);
    padding: 10px 18px;
    border-radius: 2px;
    font-size: 12px;
    letter-spacing: 0.03em;
    color: var(--ink);
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: border-color 0.2s, background 0.2s;
}

.tag:hover {
    border-color: var(--gold-light);
    background: rgba(196, 151, 74, 0.05);
}

.tag i {
    color: var(--gold-deep);
    font-size: 11px;
}

/* ── RIGHT COLUMN: PRICING, SPECS, ACTIONS ── */
.detail-right {
    flex: 1 1 35%;
    min-width: 400px;
    max-width: 500px;
    position: sticky;
    top: 110px;
}

.pricing-card {
    background: var(--white);
    padding: 50px 40px;
    border-radius: 4px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(0, 0, 0, 0.03);
}

.condition-badge {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    padding: 6px 14px;
    border-radius: 2px;
    display: inline-block;
    margin-bottom: 25px;
}

.condition-badge.new {
    background: var(--gold);
    color: var(--obsidian);
}

.condition-badge.used {
    background: var(--ivory);
    color: var(--ink);
    border: 1px solid var(--border);
}

.condition-badge.cpo {
    background: var(--ink);
    color: var(--gold);
}

.pricing-card h1 {
    font-family: 'Playfair Display', serif;
    margin: 0 0 8px 0;
    font-size: clamp(32px, 3vw, 42px);
    color: var(--ink);
    line-height: 1.15;
    font-weight: 600;
    letter-spacing: -0.01em;
}

.pricing-card .vin {
    font-family: 'Outfit', sans-serif;
    color: var(--mist);
    font-size: 12px;
    margin-bottom: 35px;
    display: block;
    letter-spacing: 0.08em;
}

.price-display {
    font-family: 'Playfair Display', serif;
    font-size: clamp(40px, 3.5vw, 50px);
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 40px;
    line-height: 1;
}

.msrp-display {
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    color: var(--mist);
    text-decoration: line-through;
    margin-top: 12px;
    font-weight: 400;
    letter-spacing: 0.03em;
}

.spec-list {
    display: flex;
    flex-direction: column;
    margin-bottom: 40px;
    border-top: 1px solid var(--border);
}

.spec-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 0;
    border-bottom: 1px solid var(--border);
}

.spec-label {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 11.5px;
    text-transform: uppercase;
    color: var(--mist);
    font-weight: 600;
    letter-spacing: 0.1em;
}

.spec-label i {
    color: var(--gold);
    width: 16px;
    text-align: center;
    font-size: 13px;
}

.spec-value {
    font-size: 14.5px;
    font-weight: 500;
    color: var(--ink);
    text-align: right;
}

/* Action Buttons */
.action-btn {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 20px;
    border-radius: 2px;
    font-weight: 600;
    font-size: 13px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-bottom: 16px;
    cursor: pointer;
    transition: all 0.3s var(--ease);
    box-sizing: border-box;
    text-decoration: none;
}

.btn-test-drive {
    background: var(--gold);
    color: var(--obsidian);
    box-shadow: 0 10px 25px rgba(196, 151, 74, 0.2);
}

.btn-test-drive:hover {
    background: var(--gold-light);
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(196, 151, 74, 0.4);
}

.btn-whatsapp-detail {
    background: #25D366;
    color: #fff;
    box-shadow: 0 10px 25px rgba(37, 211, 102, 0.2);
}

.btn-whatsapp-detail:hover {
    background: #1eb858;
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(37, 211, 102, 0.35);
}

.btn-contact {
    background: transparent;
    color: var(--ink);
    border: 1px solid var(--border);
}

.btn-contact:hover {
    background: var(--ivory);
    border-color: var(--mist);
}

/* ── SIMILAR VEHICLES SECTION ── */
.similar-section {
    max-width: 1800px;
    margin: 0 auto 100px;
    padding: 0 4%;
    font-family: 'Outfit', sans-serif;
}

.similar-section h2 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    color: var(--ink);
    margin-bottom: 40px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.similar-section h2::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

.similar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
}

.similar-card {
    background: var(--white);
    border-radius: 4px;
    overflow: hidden;
    text-decoration: none;
    transition: transform 0.3s var(--ease), box-shadow 0.3s var(--ease);
    border: 1px solid rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
}

.similar-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06);
}

.similar-img-wrapper {
    width: 100%;
    height: 220px;
    background: var(--pitch);
    position: relative;
    overflow: hidden;
}

.similar-img-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s var(--ease);
}

.similar-card:hover .similar-img-wrapper img {
    transform: scale(1.05);
}

.similar-info {
    padding: 25px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.similar-brand {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--gold);
    font-weight: 600;
    margin-bottom: 8px;
}

.similar-title {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    color: var(--ink);
    font-weight: 600;
    margin-bottom: 15px;
    line-height: 1.3;
}

.similar-price {
    font-size: 18px;
    font-weight: 600;
    color: var(--obsidian);
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px solid var(--border);
}

/* Responsive Scaling */
@media (max-width: 1200px) {
    .detail-container {
        flex-direction: column;
        padding: 30px 4% 60px;
    }

    .detail-right {
        width: 100%;
        max-width: none;
        min-width: 0;
        position: static;
    }

    .main-image-container {
        height: 500px;
    }
}

@media (max-width: 768px) {
    .detail-container {
        padding: 20px 5% 50px;
    }

    .breadcrumb {
        padding: 0 5%;
    }

    .main-image-container {
        height: 350px;
    }

    .pricing-card {
        padding: 30px;
    }

    .content-box {
        padding: 30px;
    }

    .spec-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: 14px 0;
    }

    .spec-value {
        text-align: left;
        padding-left: 28px;
    }

    .action-btn {
        padding: 16px;
        font-size: 12px;
    }

    .similar-section h2 {
        font-size: 26px;
    }
}
</style>

<div class="breadcrumb">
    <a href="<?php echo BASE_URL; ?>index.php">Home</a> <span>/</span>
    <a href="<?php echo BASE_URL; ?>vehicles.php">Inventory</a> <span>/</span>
    <?php echo sanitize($car['brand']); ?>
</div>

<div class="detail-container">
    <div class="detail-left">
        <?php if (!empty($images)): ?>
        <div class="main-image-container">
            <img id="mainImage" src="<?php echo BASE_URL . $images[0]['image_path']; ?>" alt="Vehicle Primary Image">
        </div>
        <div class="thumbnail-track">
            <?php foreach ($images as $index => $img): ?>
            <div class="thumb <?php echo $index === 0 ? 'active' : ''; ?>"
                onclick="swapImage('<?php echo BASE_URL . $img['image_path']; ?>', this)">
                <img src="<?php echo BASE_URL . $img['image_path']; ?>" alt="Thumbnail">
            </div>
            <?php endforeach; ?>
        </div>
        <script>
        function swapImage(src, element) {
            const mainImg = document.getElementById('mainImage');
            mainImg.style.opacity = '0.4';

            setTimeout(() => {
                mainImg.src = src;
                mainImg.style.opacity = '1';
            }, 200);

            document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
            element.classList.add('active');
        }
        </script>
        <?php else: ?>
        <div class="main-image-container">
            <img src="<?php echo BASE_URL; ?>assets/images/placeholder-car.jpg" alt="No Image Available">
        </div>
        <?php endif; ?>

        <div class="content-box">
            <h3>Vehicle Overview</h3>
            <p>
                <?php echo nl2br(sanitize($car['description'] ?: 'Detailed specifications and overview for this vehicle are currently being curated by our team. Please contact us for immediate, detailed information.')); ?>
            </p>
        </div>

        <div class="content-box">
            <h3>Comfort & Convenience</h3>
            <div class="feature-tags">
                <?php if (empty($comfort_features)): ?>
                <span style="color: var(--mist); font-style: italic; font-size: 14px; font-weight: 300;">Standard
                    premium equipment applies.</span>
                <?php else: ?>
                <?php foreach ($comfort_features as $feature): ?>
                <span class="tag"><i class="fa-solid fa-check"></i> <?php echo sanitize($feature); ?></span>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <h3 style="margin-top: 50px;">Safety Protocols</h3>
            <div class="feature-tags">
                <?php if (empty($safety_features)): ?>
                <span style="color: var(--mist); font-style: italic; font-size: 14px; font-weight: 300;">Standard
                    premium safety equipment applies.</span>
                <?php else: ?>
                <?php foreach ($safety_features as $feature): ?>
                <span class="tag"><i class="fa-solid fa-shield-halved"></i> <?php echo sanitize($feature); ?></span>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="detail-right">
        <div class="pricing-card">

            <?php 
                $badge_class = match(strtolower($car['condition'])) {
                    'new'               => 'new',
                    'certified pre-owned', 'cpo' => 'cpo',
                    default             => 'used'
                };
            ?>
            <span class="condition-badge <?php echo $badge_class; ?>">
                <?php echo sanitize($car['condition']); ?>
            </span>

            <h1><?php echo $vehicle_name; ?></h1>
            <span class="vin">VIN:
                <?php echo sanitize($car['vin'] !== 'N/A' ? $car['vin'] : 'Available upon request'); ?></span>

            <div class="price-display">
                KSh <?php echo number_format($display_price); ?>
                <?php if ($car['discount_price']): ?>
                <div class="msrp-display">
                    MSRP: KSh <?php echo number_format($car['price']); ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="spec-list">
                <div class="spec-row">
                    <span class="spec-label"><i class="fa-solid fa-gauge-high"></i> Mileage</span>
                    <span class="spec-value"><?php echo format_mileage($car['mileage']); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label"><i class="fa-solid fa-bolt"></i> Engine Capacity</span>
                    <span class="spec-value"><?php echo sanitize($car['engine_capacity'] ?: 'N/A'); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label"><i class="fa-solid fa-gears"></i> Transmission</span>
                    <span class="spec-value"><?php echo sanitize($car['transmission']); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label"><i class="fa-solid fa-car-side"></i> Drivetrain</span>
                    <span class="spec-value"><?php echo sanitize($car['drive_type']); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label"><i class="fa-solid fa-palette"></i> Exterior Color</span>
                    <span class="spec-value"><?php echo sanitize($car['exterior_color']); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label"><i class="fa-solid fa-gas-pump"></i> Fuel Protocol</span>
                    <span class="spec-value"><?php echo sanitize($car['fuel_type']); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label"><i class="fa-solid fa-horse-head"></i> Output</span>
                    <span
                        class="spec-value"><?php echo $car['horsepower'] ? sanitize($car['horsepower']) . ' HP' : 'N/A'; ?></span>
                </div>
            </div>

            <a href="<?php echo BASE_URL; ?>book_test_drive.php?id=<?php echo $car['id']; ?>"
                class="action-btn btn-test-drive">
                <i class="fa-regular fa-calendar-check"></i> Schedule Test Drive
            </a>

            <a href="<?php echo $wa_link; ?>" target="_blank" class="action-btn btn-whatsapp-detail">
                <i class="fa-brands fa-whatsapp" style="font-size: 16px;"></i> Inquire via WhatsApp
            </a>

            <a href="<?php echo BASE_URL; ?>contact.php?vehicle_id=<?php echo $car['id']; ?>"
                class="action-btn btn-contact">
                <i class="fa-regular fa-envelope"></i> Request Dossier
            </a>
        </div>
    </div>
</div>

<!-- SIMILAR VEHICLES SECTION -->
<?php if (!empty($similar_vehicles)): ?>
<div class="similar-section">
    <h2>More From <?php echo sanitize($car['brand']); ?></h2>
    <div class="similar-grid">
        <?php foreach ($similar_vehicles as $sim_car): 
            $sim_name = sanitize($sim_car['year'] . ' ' . $sim_car['brand'] . ' ' . $sim_car['model']);
            $sim_price = $sim_car['discount_price'] ?: $sim_car['price'];
            $sim_img = !empty($sim_car['main_image']) ? BASE_URL . $sim_car['main_image'] : BASE_URL . 'assets/images/placeholder-car.jpg';
        ?>
        <a href="<?php echo BASE_URL; ?>vehicle_detail.php?id=<?php echo $sim_car['id']; ?>" class="similar-card">
            <div class="similar-img-wrapper">
                <img src="<?php echo $sim_img; ?>" alt="<?php echo $sim_name; ?>">
            </div>
            <div class="similar-info">
                <span class="similar-brand"><?php echo sanitize($sim_car['brand']); ?></span>
                <h4 class="similar-title"><?php echo $sim_name; ?></h4>
                <div class="similar-price">KSh <?php echo number_format($sim_price); ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>