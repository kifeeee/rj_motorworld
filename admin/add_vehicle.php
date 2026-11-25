<?php
// admin/add_vehicle.php

// 1. PROCESS LOGIC FIRST TO PREVENT "HEADERS ALREADY SENT" ERRORS
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf_token.php';

// Enforce security
check_admin_auth();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO vehicles (
                vin, name, brand, model, year, mileage, fuel_type, transmission, 
                engine_capacity, horsepower, drive_type, body_type, exterior_color, 
                interior_color, `condition`, price, discount_price, description, 
                features, safety_features, availability_status, is_featured
            ) VALUES (
                :vin, :name, :brand, :model, :year, :mileage, :fuel_type, :transmission, 
                :engine_capacity, :horsepower, :drive_type, :body_type, :exterior_color, 
                :interior_color, :condition, :price, :discount_price, :description, 
                :features, :safety_features, :availability_status, :is_featured
            )");

            $stmt->execute([
                // Hardcoded defaults for the fields we removed from the UI
                'vin' => 'N/A',
                'interior_color' => 'N/A',
                'discount_price' => null,
                'availability_status' => 'Available', 

                // Captured from the new simplified form
                'name' => trim($_POST['name']),
                'brand' => trim($_POST['brand']),
                'model' => trim($_POST['model']),
                'year' => (int)$_POST['year'],
                'mileage' => (int)$_POST['mileage'],
                'fuel_type' => $_POST['fuel_type'],
                'transmission' => $_POST['transmission'],
                'engine_capacity' => trim($_POST['engine_capacity']),
                'horsepower' => (int)$_POST['horsepower'],
                'drive_type' => $_POST['drive_type'],
                'body_type' => $_POST['body_type'],
                'exterior_color' => trim($_POST['exterior_color']),
                'condition' => $_POST['condition'],
                'price' => (float)$_POST['price'],
                'description' => trim($_POST['description']),
                'features' => trim($_POST['features']),
                'safety_features' => trim($_POST['safety_features']),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0
            ]);

            $vehicle_id = $pdo->lastInsertId();

            // Handle Multiple Image Uploads
            if (!empty($_FILES['vehicle_images']['name'][0])) {
                $target_dir = __DIR__ . '/../assets/images/cars/';
                
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }

                $img_stmt = $pdo->prepare("INSERT INTO vehicle_images (vehicle_id, image_path, is_main) VALUES (:vehicle_id, :image_path, :is_main)");
                
                $is_first_image = true; // Flag to set the very first upload as the main thumbnail
                
                // Loop through each uploaded file in the array
                foreach ($_FILES['vehicle_images']['tmp_name'] as $index => $tmp_name) {
                    if ($_FILES['vehicle_images']['error'][$index] === UPLOAD_ERR_OK) {
                        
                        $file_extension = pathinfo($_FILES['vehicle_images']['name'][$index], PATHINFO_EXTENSION);
                        // Add the $index to the filename to ensure unique names if multiple files are uploaded at the same exact second
                        $new_filename = 'car_' . $vehicle_id . '_' . time() . '_' . $index . '.' . $file_extension;
                        $target_filepath = $target_dir . $new_filename;
                        $database_relative_path = 'assets/images/cars/' . $new_filename;

                        if (move_uploaded_file($tmp_name, $target_filepath)) {
                            $img_stmt->execute([
                                'vehicle_id' => $vehicle_id,
                                'image_path' => $database_relative_path,
                                'is_main' => $is_first_image ? 1 : 0
                            ]);
                            // After the first image is inserted, set this flag to false so the rest are gallery images
                            $is_first_image = false;
                        }
                    }
                }
            }

            $pdo->commit();
            header("Location: " . BASE_URL . "admin/manage_vehicles.php?msg=added");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to save vehicle to database: ' . $e->getMessage();
        }
    }
}

// 2. LOAD HTML AND HEADER AFTER PROCESSING
require_once __DIR__ . '/includes/admin_header.php';
?>

<style>
/* Clean, Modern, User-Friendly UI */
.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.form-header h3 {
    margin: 0;
    font-size: 22px;
}

.form-section {
    background: white;
    padding: 30px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.section-title {
    margin-top: 0;
    margin-bottom: 20px;
    color: #0f172a;
    font-size: 18px;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 10px;
}

.form-grid {
    display: grid;
    /* This ensures native responsiveness */
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.form-field {
    display: flex;
    flex-direction: column;
}

.form-field label {
    margin-bottom: 6px;
    font-weight: 600;
    color: #475569;
    font-size: 13px;
}

.form-field input[type="text"],
.form-field input[type="number"],
.form-field select,
.form-field textarea,
.form-field input[type="file"] {
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
    font-family: inherit;
    width: 100%;
    box-sizing: border-box;
    /* Crucial to prevent horizontal overflow */
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    outline: none;
    border-color: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
}

.span-all {
    grid-column: 1 / -1;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.action-bar {
    background: white;
    padding: 20px 30px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin-top: 10px;
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
    border: none;
    padding: 14px 32px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
    transition: background 0.2s;
    text-align: center;
}

.btn-save:hover {
    background: #334155;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    border: 1px solid #fca5a5;
    font-weight: 500;
}

.file-hint {
    font-size: 12px;
    color: #64748b;
    margin-top: 4px;
}

/* =========================================
   MEDIA QUERIES FOR MOBILE REFINEMENTS
   ========================================= */
@media (max-width: 768px) {
    .form-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .form-header h3 {
        font-size: 20px;
    }

    .form-section {
        padding: 16px;
        /* Reduce padding on small screens */
    }

    .checkbox-group {
        align-items: flex-start;
        /* Better text wrapping for checkbox label */
    }

    .checkbox-group label {
        font-size: 14px !important;
        line-height: 1.4;
    }

    .action-bar {
        padding: 15px;
        bottom: 0;
        /* Attach to bottom of screen on mobile */
        border-radius: 0;
        border-left: none;
        border-right: none;
        border-bottom: none;
        margin-left: -16px;
        /* Negate main viewport padding */
        margin-right: -16px;
        width: 100vw;
    }

    .btn-save {
        width: 100%;
        /* Full width button on mobile */
        padding: 14px;
    }
}
</style>

<div class="form-header">
    <h3>Add New Vehicle</h3>
    <a href="manage_vehicles.php" style="color: #64748b; text-decoration: none; font-size: 14px;">&larr; Cancel</a>
</div>

<?php if (!empty($error)): ?>
<div class="alert-error">⚠️ <?php echo sanitize($error); ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <?php csrf_input_field(); ?>

    <div class="form-section">
        <h4 class="section-title">1. Basic Information</h4>
        <div class="form-grid">
            <div class="form-field">
                <label>Vehicle Name / Title</label>
                <input type="text" name="name" required placeholder="e.g. Civic Type R">
            </div>
            <div class="form-field">
                <label>Make / Brand</label>
                <input type="text" name="brand" required placeholder="e.g. Honda">
            </div>
            <div class="form-field">
                <label>Model</label>
                <input type="text" name="model" required placeholder="e.g. Civic">
            </div>
            <div class="form-field">
                <label>Year</label>
                <input type="number" name="year" required min="1900" max="2100" value="<?php echo date('Y'); ?>">
            </div>
            <div class="form-field">
                <label>Condition</label>
                <select name="condition">
                    <option value="New">New</option>
                    <option value="Used">Used</option>
                    <option value="Certified Pre-Owned">Certified Pre-Owned</option>
                </select>
            </div>
            <div class="form-field">
                <label>Price ($)</label>
                <input type="number" step="0.01" name="price" required placeholder="e.g. 25000">
            </div>
        </div>
    </div>

    <div class="form-section">
        <h4 class="section-title">2. Specifications</h4>
        <div class="form-grid">
            <div class="form-field">
                <label>Mileage (km/miles)</label>
                <input type="number" name="mileage" required value="0">
            </div>
            <div class="form-field">
                <label>Fuel Type</label>
                <select name="fuel_type">
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Electric">Electric</option>
                    <option value="Hybrid">Hybrid</option>
                    <option value="Plug-in Hybrid">Plug-in Hybrid</option>
                </select>
            </div>
            <div class="form-field">
                <label>Transmission</label>
                <select name="transmission">
                    <option value="Automatic">Automatic</option>
                    <option value="Manual">Manual</option>
                    <option value="CVT">CVT</option>
                    <option value="Dual-Clutch">Dual-Clutch</option>
                </select>
            </div>
            <div class="form-field">
                <label>Drivetrain</label>
                <select name="drive_type">
                    <option value="FWD">FWD (Front Wheel Drive)</option>
                    <option value="RWD">RWD (Rear Wheel Drive)</option>
                    <option value="AWD">AWD (All Wheel Drive)</option>
                    <option value="4WD">4WD (Four Wheel Drive)</option>
                </select>
            </div>
            <div class="form-field">
                <label>Engine Capacity</label>
                <input type="text" name="engine_capacity" placeholder="e.g. 2.0L or 150kW">
            </div>
            <div class="form-field">
                <label>Horsepower</label>
                <input type="number" name="horsepower" placeholder="e.g. 306">
            </div>
            <div class="form-field">
                <label>Body Style</label>
                <select name="body_type">
                    <option value="Sedan">Sedan</option>
                    <option value="SUV">SUV</option>
                    <option value="Coupe">Coupe</option>
                    <option value="Hatchback">Hatchback</option>
                    <option value="Truck">Truck</option>
                    <option value="Van">Van</option>
                    <option value="Convertible">Convertible</option>
                </select>
            </div>
            <div class="form-field">
                <label>Exterior Color</label>
                <input type="text" name="exterior_color" required placeholder="e.g. Pearl White">
            </div>
        </div>
    </div>

    <div class="form-section">
        <h4 class="section-title">3. Details & Media</h4>
        <div class="form-grid">
            <div class="form-field span-all">
                <label>Vehicle Image Gallery (.jpg, .png)</label>
                <input type="file" name="vehicle_images[]" accept="image/*" multiple required
                    style="background: #f8fafc;">
                <span class="file-hint">Hold down CTRL (Windows) or CMD (Mac) to select multiple images at once. The
                    first image selected will be the main thumbnail.</span>
            </div>

            <div class="form-field span-all">
                <label>Vehicle Description</label>
                <textarea name="description" rows="5" placeholder="Write a brief overview of the vehicle..."></textarea>
            </div>

            <div class="form-field span-all">
                <label>Features (Comma-separated)</label>
                <input type="text" name="features" placeholder="e.g. Heated Seats, Apple CarPlay, Sunroof">
            </div>

            <div class="form-field span-all">
                <label>Safety Features (Comma-separated)</label>
                <input type="text" name="safety_features"
                    placeholder="e.g. Backup Camera, Blind Spot Monitor, 6 Airbags">
            </div>

            <div class="span-all checkbox-group">
                <input type="checkbox" name="is_featured" id="is_featured" value="1"
                    style="width: 20px; height: 20px; cursor: pointer; flex-shrink: 0;">
                <label style="margin: 0; font-size: 15px; cursor: pointer;" for="is_featured">
                    <strong>Feature on Homepage:</strong> Display this vehicle prominently on the main website.
                </label>
            </div>
        </div>
    </div>

    <div class="action-bar">
        <button type="submit" class="btn-save">Save Vehicle to Inventory</button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>