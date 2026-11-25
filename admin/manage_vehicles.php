<?php
// admin/manage_vehicles.php

// 1. HANDLE QUICK-EDIT MODAL FORM SUBMISSIONS FIRST
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf_token.php';

check_admin_auth();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_edit') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $msg = 'error_csrf';
    } else {
        try {
            $pdo->beginTransaction();
            $vehicle_id = (int)$_POST['vehicle_id'];

            // A. Update text data
            $stmt = $pdo->prepare("UPDATE vehicles SET 
                name = :name, brand = :brand, model = :model, year = :year, 
                mileage = :mileage, fuel_type = :fuel_type, transmission = :transmission, 
                engine_capacity = :engine_capacity, horsepower = :horsepower, 
                drive_type = :drive_type, body_type = :body_type, exterior_color = :exterior_color, 
                `condition` = :condition, price = :price, description = :description, 
                features = :features, safety_features = :safety_features, 
                availability_status = :availability_status, is_featured = :is_featured
                WHERE id = :id");

            $stmt->execute([
                'id' => $vehicle_id,
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
                'availability_status' => $_POST['availability_status'],
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0
            ]);

            // B. Handle Image Deletions
            if (!empty($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $img_id) {
                    // Get path to delete from disk
                    $imgStmt = $pdo->prepare("SELECT image_path FROM vehicle_images WHERE id = ? AND vehicle_id = ?");
                    $imgStmt->execute([$img_id, $vehicle_id]);
                    $path = $imgStmt->fetchColumn();

                    if ($path && file_exists(__DIR__ . '/../' . $path)) {
                        unlink(__DIR__ . '/../' . $path);
                    }
                    // Delete from DB
                    $pdo->prepare("DELETE FROM vehicle_images WHERE id = ?")->execute([$img_id]);
                }
            }

            // C. Handle New Image Uploads
            if (!empty($_FILES['new_images']['name'][0])) {
                $target_dir = __DIR__ . '/../assets/images/cars/';
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

                $img_insert = $pdo->prepare("INSERT INTO vehicle_images (vehicle_id, image_path, is_main) VALUES (:vehicle_id, :image_path, 0)");
                
                foreach ($_FILES['new_images']['tmp_name'] as $index => $tmp_name) {
                    if ($_FILES['new_images']['error'][$index] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['new_images']['name'][$index], PATHINFO_EXTENSION);
                        $new_filename = 'car_' . $vehicle_id . '_' . time() . '_' . $index . '.' . $ext;
                        $target_filepath = $target_dir . $new_filename;
                        
                        if (move_uploaded_file($tmp_name, $target_filepath)) {
                            $img_insert->execute([
                                'vehicle_id' => $vehicle_id,
                                'image_path' => 'assets/images/cars/' . $new_filename
                            ]);
                        }
                    }
                }
            }

            // Safety check: Ensure at least one image is set to is_main = 1 if all previous main images were deleted
            $mainCheck = $pdo->prepare("SELECT COUNT(*) FROM vehicle_images WHERE vehicle_id = ? AND is_main = 1");
            $mainCheck->execute([$vehicle_id]);
            if ($mainCheck->fetchColumn() == 0) {
                $pdo->prepare("UPDATE vehicle_images SET is_main = 1 WHERE vehicle_id = ? LIMIT 1")->execute([$vehicle_id]);
            }

            $pdo->commit();
            header("Location: " . BASE_URL . "admin/manage_vehicles.php?msg=updated");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'error_db';
        }
    }
}

// 2. LOAD HEADER AND FETCH DATA
require_once __DIR__ . '/includes/admin_header.php';

// Fetch all vehicles
$stmt = $pdo->query("SELECT * FROM vehicles ORDER BY id DESC");
$vehicles = $stmt->fetchAll();

// Fetch all images and group them by vehicle_id
$imgStmt = $pdo->query("SELECT * FROM vehicle_images ORDER BY is_main DESC, id ASC");
$all_images = $imgStmt->fetchAll();
$images_by_vehicle = [];
foreach ($all_images as $img) {
    $images_by_vehicle[$img['vehicle_id']][] = $img;
}
?>

<style>
/* Page-Specific Responsive Styles */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.page-header h3 {
    margin: 0;
    font-size: 22px;
}

.btn-add {
    background: #16a34a;
    color: white;
    padding: 10px 16px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.2s ease;
    text-align: center;
}

.btn-add:hover {
    background: #15803d;
}

.action-links a {
    font-weight: 600;
    text-decoration: none;
    font-size: 14px;
    padding: 6px 10px;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.link-delete {
    color: #dc2626;
}

.link-delete:hover {
    background: #fef2f2;
}

/* Row Hover & Click Styles */
.clickable-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.clickable-row:hover {
    background-color: #f1f5f9 !important;
}

/* =========================================
   QUICK EDIT MODAL STYLES
   ========================================= */
.edit-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.6);
    z-index: 2000;
    opacity: 0;
    transition: opacity 0.3s ease;
    backdrop-filter: blur(4px);
    overflow-y: auto;
    padding: 20px;
    box-sizing: border-box;
}

.edit-modal-overlay.active {
    display: block;
    opacity: 1;
}

.edit-modal-content {
    background: white;
    max-width: 900px;
    margin: 20px auto;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    transform: translateY(20px);
    transition: transform 0.3s ease;
    overflow: hidden;
}

.edit-modal-overlay.active .edit-modal-content {
    transform: translateY(0);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #0f172a;
}

.close-modal-btn {
    background: none;
    border: none;
    font-size: 24px;
    color: #64748b;
    cursor: pointer;
    transition: color 0.2s;
    padding: 0;
}

.close-modal-btn:hover {
    color: #ef4444;
}

.modal-body {
    padding: 24px;
}

.modal-section-title {
    font-size: 14px;
    text-transform: uppercase;
    color: #64748b;
    font-weight: 700;
    margin: 24px 0 12px 0;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 8px;
}

.modal-section-title:first-child {
    margin-top: 0;
}

.modal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}

.modal-field {
    display: flex;
    flex-direction: column;
}

.modal-field label {
    margin-bottom: 6px;
    font-weight: 600;
    color: #475569;
    font-size: 12px;
    text-transform: uppercase;
}

.modal-field input,
.modal-field select,
.modal-field textarea {
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    width: 100%;
    box-sizing: border-box;
}

.modal-field input:focus,
.modal-field select:focus,
.modal-field textarea:focus {
    outline: none;
    border-color: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
}

.modal-span-all {
    grid-column: 1 / -1;
}

/* Image Gallery Styles */
.modal-gallery-container {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}

.gallery-item {
    position: relative;
    width: 100px;
    height: 75px;
    border-radius: 6px;
    overflow: hidden;
    border: 2px solid transparent;
    transition: all 0.2s ease;
    cursor: pointer;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.gallery-item.pending-delete {
    border-color: #ef4444;
    opacity: 0.5;
    filter: grayscale(100%);
}

.gallery-item::after {
    content: "Click to Delete";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    font-size: 10px;
    text-align: center;
    padding: 3px 0;
    transform: translateY(100%);
    transition: transform 0.2s;
}

.gallery-item:hover::after {
    transform: translateY(0);
}

.gallery-item.pending-delete::after {
    content: "Will be deleted";
    transform: translateY(0);
    background: #ef4444;
}

.modal-footer {
    padding: 20px 24px;
    border-top: 1px solid #e5e7eb;
    background: #f8fafc;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn-cancel {
    padding: 10px 20px;
    background: white;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    color: #475569;
    font-weight: 600;
    cursor: pointer;
}

.btn-update {
    padding: 10px 24px;
    background: #0ea5e9;
    border: none;
    border-radius: 6px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-update:hover {
    background: #0284c7;
}

/* Mobile Adjustments */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .btn-add {
        width: 100%;
        box-sizing: border-box;
    }

    .action-links {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: flex-end;
    }

    .edit-modal-overlay {
        padding: 10px;
    }

    .modal-body {
        padding: 16px;
    }

    .modal-footer {
        flex-direction: column;
    }

    .modal-footer button {
        width: 100%;
    }
}
</style>

<div class="page-header">
    <h3>Active Vehicle Fleet Inventory</h3>
    <a href="add_vehicle.php" class="btn-add">+ Add New Vehicle Record</a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
<div
    style="background: #dcfce7; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;">
    Vehicle details and media successfully updated.
</div>
<?php endif; ?>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Vehicle Details</th>
                <th>VIN / Chassis</th>
                <th>Pricing Structure</th>
                <th>Status</th>
                <th style="text-align: right;">System Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vehicles)): ?>
            <tr>
                <td colspan="6" style="text-align: center; color: #64748b;">No inventory records detected within the
                    database.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($vehicles as $car): 
                // Attach associated images to the car array
                $car['images'] = $images_by_vehicle[$car['id']] ?? [];
                
                // Find main image for table thumbnail
                $main_thumb = null;
                foreach($car['images'] as $img) {
                    if ($img['is_main'] == 1) { $main_thumb = $img['image_path']; break; }
                }
                if (!$main_thumb && !empty($car['images'])) $main_thumb = $car['images'][0]['image_path'];
                
                $thumb_url = (!empty($main_thumb) && file_exists(__DIR__ . '/../' . $main_thumb)) 
                    ? BASE_URL . $main_thumb 
                    : BASE_URL . 'assets/images/placeholder-car.jpg';

                // Serialize to JSON for Modal
                $carJson = htmlspecialchars(json_encode($car), ENT_QUOTES, 'UTF-8');
            ?>
            <tr class="clickable-row" data-vehicle='<?php echo $carJson; ?>'>
                <td style="width: 100px;">
                    <img src="<?php echo $thumb_url; ?>" alt="Car Thumbnail"
                        style="width: 80px; height: 55px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">
                </td>
                <td>
                    <strong><?php echo sanitize($car['brand'] . ' ' . $car['name'] . ' (' . $car['year'] . ')'); ?></strong><br>
                    <span
                        style="font-size: 12px; color: #64748b;"><?php echo sanitize($car['body_type'] . ' | ' . $car['transmission'] . ' | ' . $car['fuel_type']); ?></span>
                </td>
                <td style="font-family: monospace; font-size: 13px; color: #334155;">
                    <?php echo sanitize($car['vin']); ?></td>
                <td>
                    <strong><?php echo format_price($car['price']); ?></strong>
                    <?php if ($car['discount_price']): ?>
                    <br><span
                        style="font-size: 12px; color: #dc2626; text-decoration: line-through;"><?php echo format_price($car['discount_price']); ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge"
                        style="background: <?php echo $car['availability_status'] === 'Available' ? '#dcfce7; color: #166534;' : '#fee2e2; color: #991b1b;'; ?>">
                        <?php echo sanitize($car['availability_status']); ?>
                    </span>
                </td>
                <td style="text-align: right;" class="action-links">
                    <a href="delete_vehicle.php?id=<?php echo $car['id']; ?>" class="link-delete btn-action-stop"
                        onclick="return confirm('Are you sure you want to completely scrub this vehicle record?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="edit-modal-overlay" id="editModal">
    <div class="edit-modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Quick Edit: <span id="modalCarTitle" style="color: #0ea5e9;"></span></h3>
            <button type="button" class="close-modal-btn" id="closeModalBtn"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <?php csrf_input_field(); ?>
            <input type="hidden" name="action" value="quick_edit">
            <input type="hidden" name="vehicle_id" id="modal_vehicle_id">

            <div class="modal-body">

                <h4 class="modal-section-title">Media Management</h4>
                <div class="modal-grid" style="margin-bottom: 20px;">
                    <div class="modal-field modal-span-all">
                        <label>Current Gallery (Click to flag for deletion)</label>
                        <div class="modal-gallery-container" id="modal_image_gallery">
                        </div>
                    </div>
                    <div class="modal-field modal-span-all">
                        <label>Upload Additional Images (.jpg, .png)</label>
                        <input type="file" name="new_images[]" multiple accept="image/*" style="background: #f8fafc;">
                    </div>
                </div>

                <h4 class="modal-section-title">Vehicle Details</h4>
                <div class="modal-grid">
                    <div class="modal-field"><label>Make</label><input type="text" name="brand" id="modal_brand"
                            required></div>
                    <div class="modal-field"><label>Model</label><input type="text" name="model" id="modal_model"
                            required></div>
                    <div class="modal-field"><label>Title/Name</label><input type="text" name="name" id="modal_name"
                            required></div>
                    <div class="modal-field"><label>Year</label><input type="number" name="year" id="modal_year"
                            required></div>
                    <div class="modal-field"><label>Price ($)</label><input type="number" step="0.01" name="price"
                            id="modal_price" required></div>

                    <div class="modal-field">
                        <label>Status</label>
                        <select name="availability_status" id="modal_availability_status">
                            <option value="Available">Available</option>
                            <option value="Reserved">Reserved</option>
                            <option value="Sold">Sold</option>
                        </select>
                    </div>

                    <div class="modal-field"><label>Mileage</label><input type="number" name="mileage"
                            id="modal_mileage"></div>
                    <div class="modal-field">
                        <label>Condition</label>
                        <select name="condition" id="modal_condition">
                            <option value="New">New</option>
                            <option value="Used">Used</option>
                            <option value="Certified Pre-Owned">Certified Pre-Owned</option>
                        </select>
                    </div>

                    <div class="modal-field">
                        <label>Transmission</label>
                        <select name="transmission" id="modal_transmission">
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                            <option value="CVT">CVT</option>
                            <option value="Dual-Clutch">Dual-Clutch</option>
                        </select>
                    </div>
                    <div class="modal-field">
                        <label>Fuel Type</label>
                        <select name="fuel_type" id="modal_fuel_type">
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                            <option value="Plug-in Hybrid">Plug-in Hybrid</option>
                        </select>
                    </div>

                    <div class="modal-field">
                        <label>Body Type</label>
                        <select name="body_type" id="modal_body_type">
                            <option value="Sedan">Sedan</option>
                            <option value="SUV">SUV</option>
                            <option value="Coupe">Coupe</option>
                            <option value="Hatchback">Hatchback</option>
                            <option value="Truck">Truck</option>
                            <option value="Van">Van</option>
                            <option value="Convertible">Convertible</option>
                        </select>
                    </div>

                    <div class="modal-field">
                        <label>Drivetrain</label>
                        <select name="drive_type" id="modal_drive_type">
                            <option value="FWD">FWD</option>
                            <option value="RWD">RWD</option>
                            <option value="AWD">AWD</option>
                            <option value="4WD">4WD</option>
                        </select>
                    </div>

                    <div class="modal-field"><label>Engine</label><input type="text" name="engine_capacity"
                            id="modal_engine_capacity"></div>
                    <div class="modal-field"><label>Horsepower</label><input type="number" name="horsepower"
                            id="modal_horsepower"></div>
                    <div class="modal-field"><label>Color</label><input type="text" name="exterior_color"
                            id="modal_exterior_color"></div>

                    <div class="modal-field" style="flex-direction: row; align-items: center; gap: 10px;">
                        <input type="checkbox" name="is_featured" id="modal_is_featured" value="1" style="width: auto;">
                        <label for="modal_is_featured" style="margin:0; cursor:pointer;">Feature on Homepage</label>
                    </div>

                    <div class="modal-field modal-span-all">
                        <label>Description</label>
                        <textarea name="description" id="modal_description" rows="3"></textarea>
                    </div>
                    <div class="modal-field modal-span-all">
                        <label>Features</label>
                        <input type="text" name="features" id="modal_features">
                    </div>
                    <div class="modal-field modal-span-all">
                        <label>Safety Features</label>
                        <input type="text" name="safety_features" id="modal_safety_features">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelModalBtn">Cancel</button>
                <button type="submit" class="btn-update">Save Data & Media</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const rows = document.querySelectorAll('.clickable-row');
    const modal = document.getElementById('editModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelModalBtn');
    const deleteLinks = document.querySelectorAll('.btn-action-stop');
    const galleryContainer = document.getElementById('modal_image_gallery');
    const BASE_URL = '<?php echo BASE_URL; ?>';

    deleteLinks.forEach(link => {
        link.addEventListener('click', e => e.stopPropagation());
    });

    rows.forEach(row => {
        row.addEventListener('click', function() {
            const carData = JSON.parse(this.getAttribute('data-vehicle'));

            document.getElementById('modalCarTitle').textContent = carData.brand + ' ' + carData
                .model;
            document.getElementById('modal_vehicle_id').value = carData.id;
            document.getElementById('modal_brand').value = carData.brand || '';
            document.getElementById('modal_model').value = carData.model || '';
            document.getElementById('modal_name').value = carData.name || '';
            document.getElementById('modal_year').value = carData.year || '';
            document.getElementById('modal_price').value = carData.price || '';
            document.getElementById('modal_availability_status').value = carData
                .availability_status || 'Available';
            document.getElementById('modal_mileage').value = carData.mileage || '';
            document.getElementById('modal_condition').value = carData.condition || 'Used';
            document.getElementById('modal_transmission').value = carData.transmission ||
                'Automatic';
            document.getElementById('modal_fuel_type').value = carData.fuel_type || 'Petrol';
            document.getElementById('modal_body_type').value = carData.body_type || 'Sedan';
            document.getElementById('modal_drive_type').value = carData.drive_type || 'FWD';
            document.getElementById('modal_engine_capacity').value = carData.engine_capacity ||
                '';
            document.getElementById('modal_horsepower').value = carData.horsepower || '';
            document.getElementById('modal_exterior_color').value = carData.exterior_color ||
                '';
            document.getElementById('modal_description').value = carData.description || '';
            document.getElementById('modal_features').value = carData.features || '';
            document.getElementById('modal_safety_features').value = carData.safety_features ||
                '';
            document.getElementById('modal_is_featured').checked = (carData.is_featured == 1);

            // Populate Gallery for visual deletion
            galleryContainer.innerHTML = '';
            if (carData.images && carData.images.length > 0) {
                carData.images.forEach(img => {
                    // Create visual wrapper
                    const wrap = document.createElement('div');
                    wrap.className = 'gallery-item';

                    // The visual image
                    const imageEl = document.createElement('img');
                    imageEl.src = BASE_URL + img.image_path;

                    // The hidden checkbox for PHP processing
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'delete_images[]';
                    checkbox.value = img.id;
                    checkbox.style.display = 'none';

                    // Toggle deletion logic on click
                    wrap.addEventListener('click', function() {
                        wrap.classList.toggle('pending-delete');
                        checkbox.checked = !checkbox.checked;
                    });

                    wrap.appendChild(imageEl);
                    wrap.appendChild(checkbox);
                    galleryContainer.appendChild(wrap);
                });
            } else {
                galleryContainer.innerHTML =
                    '<span style="font-size:13px; color:#94a3b8;">No images uploaded yet.</span>';
            }

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>