<?php
// admin/edit_vehicle.php

// 1. LOAD CORE CONFIGURATION FIRST
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf_token.php';

// Enforce security
check_admin_auth();

$id = (int)($_GET['id'] ?? 0);
$error = '';

// 2. PROCESS FORM SUBMISSION & REDIRECTS BEFORE ANY HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        try {
            // STEP A: Delete specific existing images if requested
            if (!empty($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $del_img_id) {
                    $del_img_id = (int)$del_img_id;
                    
                    $stmt = $pdo->prepare("SELECT image_path, is_main FROM vehicle_images WHERE id = :img_id AND vehicle_id = :vid");
                    $stmt->execute(['img_id' => $del_img_id, 'vid' => $id]);
                    $del_target = $stmt->fetch();

                    if ($del_target) {
                        $physical_path = __DIR__ . '/../' . $del_target['image_path'];
                        if (file_exists($physical_path)) { unlink($physical_path); }
                        $pdo->prepare("DELETE FROM vehicle_images WHERE id = :img_id")->execute(['img_id' => $del_img_id]);
                        
                        // Promote a new main image if the old one was deleted
                        if ($del_target['is_main'] == 1) {
                            $pdo->prepare("UPDATE vehicle_images SET is_main = 1 WHERE vehicle_id = :vid LIMIT 1")->execute(['vid' => $id]);
                        }
                    }
                }
            }

            // STEP B: Update Main Image Selection
            // (We do this after deletion so we don't accidentally set a deleted image as main)
            if (!empty($_POST['main_image_id'])) {
                $new_main_id = (int)$_POST['main_image_id'];
                
                // Verify the image exists and belongs to this car
                $check = $pdo->prepare("SELECT id FROM vehicle_images WHERE id = ? AND vehicle_id = ?");
                $check->execute([$new_main_id, $id]);
                if ($check->fetch()) {
                    // Reset all to 0, then set the chosen one to 1
                    $pdo->prepare("UPDATE vehicle_images SET is_main = 0 WHERE vehicle_id = ?")->execute([$id]);
                    $pdo->prepare("UPDATE vehicle_images SET is_main = 1 WHERE id = ?")->execute([$new_main_id]);
                }
            }

            // STEP C: Update core text fields
            $update_stmt = $pdo->prepare("UPDATE vehicles SET 
                name = :name, brand = :brand, model = :model, year = :year, 
                mileage = :mileage, fuel_type = :fuel_type, transmission = :transmission, 
                engine_capacity = :engine_capacity, horsepower = :horsepower, drive_type = :drive_type, 
                body_type = :body_type, exterior_color = :exterior_color, 
                `condition` = :condition, price = :price, 
                description = :description, features = :features, safety_features = :safety_features, 
                is_featured = :is_featured 
                WHERE id = :id");

            $update_stmt->execute([
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
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'id' => $id
            ]);

            // STEP D: Handle appending new Multiple Image Uploads
            if (!empty($_FILES['vehicle_images']['name'][0])) {
                $target_dir = __DIR__ . '/../assets/images/cars/';
                if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }

                $main_check = $pdo->prepare("SELECT id FROM vehicle_images WHERE vehicle_id = :id AND is_main = 1");
                $main_check->execute(['id' => $id]);
                $has_main = $main_check->fetch() ? true : false;
                
                $img_stmt = $pdo->prepare("INSERT INTO vehicle_images (vehicle_id, image_path, is_main) VALUES (:id, :path, :is_main)");
                
                foreach ($_FILES['vehicle_images']['tmp_name'] as $index => $tmp_name) {
                    if ($_FILES['vehicle_images']['error'][$index] === UPLOAD_ERR_OK) {
                        
                        $file_extension = pathinfo($_FILES['vehicle_images']['name'][$index], PATHINFO_EXTENSION);
                        $new_filename = 'car_' . $id . '_' . time() . '_' . $index . '.' . $file_extension;
                        $target_filepath = $target_dir . $new_filename;
                        $database_relative_path = 'assets/images/cars/' . $new_filename;

                        if (move_uploaded_file($tmp_name, $target_filepath)) {
                            $img_stmt->execute([
                                'id' => $id,
                                'path' => $database_relative_path,
                                'is_main' => !$has_main ? 1 : 0
                            ]);
                            $has_main = true; 
                        }
                    }
                }
            }

            header("Location: " . BASE_URL . "admin/manage_vehicles.php?msg=updated");
            exit();

        } catch (Exception $e) {
            $error = 'Failed to update vehicle: ' . $e->getMessage();
        }
    }
}

// 3. FETCH DATA FOR THE FORM
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$car = $stmt->fetch();

$img_stmt = $pdo->prepare("SELECT * FROM vehicle_images WHERE vehicle_id = :id ORDER BY is_main DESC, id ASC");
$img_stmt->execute(['id' => $id]);
$existing_images = $img_stmt->fetchAll();

// 4. LOAD THE HTML HEADER AND CSS
require_once __DIR__ . '/includes/admin_header.php';

if (!$car) {
    echo "<div style='padding:40px;'><h3>Inventory record missing or deleted.</h3></div>";
    require_once __DIR__ . '/includes/admin_footer.php';
    exit();
}
?>

<style>
/* Clean, Modern, User-Friendly UI */
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

/* Image Gallery Styles */
.current-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 15px;
    background: #f8fafc;
    padding: 20px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    margin-bottom: 15px;
}

.gallery-item {
    position: relative;
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid #cbd5e1;
    background: white;
    display: flex;
    flex-direction: column;
}

.gallery-item img {
    width: 100%;
    height: 130px;
    object-fit: cover;
    border-bottom: 1px solid #cbd5e1;
}

.main-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #38bdf8;
    color: #0f172a;
    font-size: 10px;
    font-weight: 800;
    padding: 4px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

/* Control Panel under each image */
.gallery-controls {
    display: flex;
    flex-direction: column;
}

.control-row {
    padding: 8px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid #f1f5f9;
}

.control-row:last-child {
    border-bottom: none;
}

.control-row:hover {
    background: #f8fafc;
}

.control-row.delete-row {
    color: #dc2626;
}

.control-row.delete-row:hover {
    background: #fee2e2;
}

.control-row input {
    margin: 0;
    cursor: pointer;
}

/* Live Upload Preview */
#upload-preview-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.preview-item {
    position: relative;
    border-radius: 6px;
    overflow: hidden;
    border: 2px solid #38bdf8;
    background: white;
    aspect-ratio: 4/3;
}

.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-preview-btn {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    background: rgba(220, 38, 38, 0.9);
    color: white;
    text-align: center;
    font-size: 11px;
    font-weight: bold;
    padding: 6px 0;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s;
    text-transform: uppercase;
}

.preview-item:hover .remove-preview-btn {
    opacity: 1;
}
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h3 style="margin: 0;">Edit Vehicle</h3>
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
                <input type="text" name="name" value="<?php echo sanitize($car['name']); ?>" required>
            </div>
            <div class="form-field">
                <label>Make / Brand</label>
                <input type="text" name="brand" value="<?php echo sanitize($car['brand']); ?>" required>
            </div>
            <div class="form-field">
                <label>Model</label>
                <input type="text" name="model" value="<?php echo sanitize($car['model']); ?>" required>
            </div>
            <div class="form-field">
                <label>Year</label>
                <input type="number" name="year" value="<?php echo (int)$car['year']; ?>" required min="1900"
                    max="2100">
            </div>
            <div class="form-field">
                <label>Condition</label>
                <select name="condition">
                    <option value="New" <?php echo $car['condition'] === 'New' ? 'selected' : ''; ?>>New</option>
                    <option value="Used" <?php echo $car['condition'] === 'Used' ? 'selected' : ''; ?>>Used</option>
                    <option value="Certified Pre-Owned"
                        <?php echo $car['condition'] === 'Certified Pre-Owned' ? 'selected' : ''; ?>>Certified Pre-Owned
                    </option>
                </select>
            </div>
            <div class="form-field">
                <label>Price ($)</label>
                <input type="number" step="0.01" name="price" value="<?php echo (float)$car['price']; ?>" required>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h4 class="section-title">2. Specifications</h4>
        <div class="form-grid">
            <div class="form-field">
                <label>Mileage (km/miles)</label>
                <input type="number" name="mileage" value="<?php echo (int)$car['mileage']; ?>" required>
            </div>
            <div class="form-field">
                <label>Fuel Type</label>
                <select name="fuel_type">
                    <?php foreach(['Gasoline', 'Diesel', 'Electric', 'Hybrid', 'Plug-in Hybrid'] as $ft): ?>
                    <option value="<?php echo $ft; ?>" <?php echo $car['fuel_type'] === $ft ? 'selected' : ''; ?>>
                        <?php echo $ft; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label>Transmission</label>
                <select name="transmission">
                    <?php foreach(['Automatic', 'Manual', 'CVT', 'Dual-Clutch'] as $tm): ?>
                    <option value="<?php echo $tm; ?>" <?php echo $car['transmission'] === $tm ? 'selected' : ''; ?>>
                        <?php echo $tm; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label>Drivetrain</label>
                <select name="drive_type">
                    <?php foreach(['FWD', 'RWD', 'AWD', '4WD'] as $dt): ?>
                    <option value="<?php echo $dt; ?>" <?php echo $car['drive_type'] === $dt ? 'selected' : ''; ?>>
                        <?php echo $dt; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label>Engine Capacity</label>
                <input type="text" name="engine_capacity" value="<?php echo sanitize($car['engine_capacity']); ?>">
            </div>
            <div class="form-field">
                <label>Horsepower</label>
                <input type="number" name="horsepower" value="<?php echo (int)$car['horsepower']; ?>">
            </div>
            <div class="form-field">
                <label>Body Style</label>
                <select name="body_type">
                    <?php foreach(['Sedan', 'SUV', 'Coupe', 'Hatchback', 'Truck', 'Van', 'Convertible'] as $bt): ?>
                    <option value="<?php echo $bt; ?>" <?php echo $car['body_type'] === $bt ? 'selected' : ''; ?>>
                        <?php echo $bt; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label>Exterior Color</label>
                <input type="text" name="exterior_color" value="<?php echo sanitize($car['exterior_color']); ?>"
                    required>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h4 class="section-title">3. Details & Media</h4>
        <div class="form-grid">

            <div class="form-field span-all">
                <label>Current Image Gallery</label>
                <span class="file-hint" style="margin-bottom: 8px; display: block;">Select the radio button to set the
                    cover photo. Check the box to permanently delete an image.</span>
                <?php if (!empty($existing_images)): ?>
                <div class="current-gallery">
                    <?php foreach ($existing_images as $img): ?>
                    <div class="gallery-item">
                        <?php if ($img['is_main']): ?>
                        <span class="main-badge">Main</span>
                        <?php endif; ?>
                        <img src="<?php echo BASE_URL . sanitize($img['image_path']); ?>" alt="Vehicle Image">

                        <div class="gallery-controls">
                            <label class="control-row">
                                <input type="radio" name="main_image_id" value="<?php echo $img['id']; ?>"
                                    <?php echo $img['is_main'] ? 'checked' : ''; ?>>
                                Set as Main Image
                            </label>
                            <label class="control-row delete-row">
                                <input type="checkbox" name="delete_images[]" value="<?php echo $img['id']; ?>">
                                <i class="fa-solid fa-trash"></i> Delete Image
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div
                    style="padding: 15px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; margin-bottom: 15px; color: #64748b; font-size: 13px;">
                    No images currently attached to this vehicle.
                </div>
                <?php endif; ?>
            </div>

            <div class="form-field span-all">
                <label>Upload Additional Images (.jpg, .png)</label>
                <input type="file" id="vehicle_images_input" name="vehicle_images[]" accept="image/*" multiple
                    style="background: #f8fafc;">
                <span class="file-hint">Hold down CTRL (Windows) or CMD (Mac) to select multiple files. Selected images
                    will appear below before saving.</span>

                <div id="upload-preview-container"></div>
            </div>

            <div class="form-field span-all">
                <label>Vehicle Description</label>
                <textarea name="description" rows="5"><?php echo sanitize($car['description']); ?></textarea>
            </div>

            <div class="form-field span-all">
                <label>Features (Comma-separated)</label>
                <input type="text" name="features" value="<?php echo sanitize($car['features']); ?>">
            </div>

            <div class="form-field span-all">
                <label>Safety Features (Comma-separated)</label>
                <input type="text" name="safety_features" value="<?php echo sanitize($car['safety_features']); ?>">
            </div>

            <div class="span-all checkbox-group">
                <input type="checkbox" name="is_featured" id="is_featured" value="1"
                    <?php echo $car['is_featured'] ? 'checked' : ''; ?>
                    style="width: 20px; height: 20px; cursor: pointer;">
                <label style="margin: 0; font-size: 15px; cursor: pointer;" for="is_featured">
                    <strong>Feature on Homepage:</strong> Display this vehicle prominently on the main website.
                </label>
            </div>
        </div>
    </div>

    <div
        style="background: white; padding: 20px 30px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 10px; display: flex; justify-content: flex-end; position: sticky; bottom: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <button type="submit" class="btn-save">Save Changes to Vehicle</button>
    </div>
</form>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const fileInput = document.getElementById('vehicle_images_input');
    const previewContainer = document.getElementById('upload-preview-container');

    // We maintain an array of actual files so we can add/remove them dynamically
    let selectedFilesArray = [];

    fileInput.addEventListener('change', function(e) {
        // Add newly selected files to our tracking array
        const files = Array.from(this.files);
        files.forEach(file => {
            selectedFilesArray.push(file);
        });

        updateFileInputAndRenderPreview();
    });

    // Make removeFile globally available to the onclick handlers
    window.removeFile = function(index) {
        selectedFilesArray.splice(index, 1);
        updateFileInputAndRenderPreview();
    };

    function updateFileInputAndRenderPreview() {
        // 1. Rebuild the actual file input using DataTransfer so the backend receives the correct files
        const dataTransfer = new DataTransfer();
        selectedFilesArray.forEach(file => {
            dataTransfer.items.add(file);
        });
        fileInput.files = dataTransfer.files;

        // 2. Clear current previews
        previewContainer.innerHTML = '';

        // 3. Render fresh previews
        selectedFilesArray.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <div class="remove-preview-btn" onclick="removeFile(${index})">Click to Remove</div>
                    <img src="${e.target.result}" alt="Preview">
                `;
                previewContainer.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>