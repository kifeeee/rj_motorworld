<?php
// search_results.php
require_once __DIR__ . '/includes/header.php';

// 1. Initialize Pagination and Query Variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = ITEMS_PER_PAGE; 
$offset = ($page - 1) * $limit;

$where_clauses = ["v.availability_status = 'Available'"];
$params = [];

// 2. Dynamically Build the Advanced SQL Query based on GET inputs
if (!empty($_GET['brand'])) {
    $where_clauses[] = "v.brand = :brand";
    $params['brand'] = $_GET['brand'];
}
if (!empty($_GET['body_type'])) {
    $where_clauses[] = "v.body_type = :body_type";
    $params['body_type'] = $_GET['body_type'];
}
if (!empty($_GET['min_price'])) {
    $where_clauses[] = "(v.discount_price IS NOT NULL AND v.discount_price >= :min_price OR v.discount_price IS NULL AND v.price >= :min_price)";
    $params['min_price'] = (float)$_GET['min_price'];
}
if (!empty($_GET['max_price'])) {
    $where_clauses[] = "(v.discount_price IS NOT NULL AND v.discount_price <= :max_price OR v.discount_price IS NULL AND v.price <= :max_price)";
    $params['max_price'] = (float)$_GET['max_price'];
}
if (!empty($_GET['min_year'])) {
    $where_clauses[] = "v.year >= :min_year";
    $params['min_year'] = (int)$_GET['min_year'];
}
if (!empty($_GET['max_year'])) {
    $where_clauses[] = "v.year <= :max_year";
    $params['max_year'] = (int)$_GET['max_year'];
}
if (!empty($_GET['transmission'])) {
    $where_clauses[] = "v.transmission = :transmission";
    $params['transmission'] = $_GET['transmission'];
}

$where_sql = implode(' AND ', $where_clauses);

// 3. Count Total Matching Records for Pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles v WHERE $where_sql");
$count_stmt->execute($params);
$total_vehicles = $count_stmt->fetchColumn();
$total_pages = ceil($total_vehicles / $limit);

// 4. Fetch the specific page of data with primary images
$query = "SELECT v.*, vi.image_path 
          FROM vehicles v 
          LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_main = 1 
          WHERE $where_sql 
          ORDER BY v.price ASC 
          LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$vehicles = $stmt->fetchAll();

// 5. Fetch dropdown data for the search form
$brands = $pdo->query("SELECT DISTINCT brand FROM vehicles WHERE availability_status = 'Available' ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);
$body_types = $pdo->query("SELECT DISTINCT body_type FROM vehicles WHERE availability_status = 'Available' ORDER BY body_type")->fetchAll(PDO::FETCH_COLUMN);
?>

<style>
.search-header {
    background: var(--primary);
    color: white;
    padding: 40px 5%;
    text-align: center;
}

.search-header h1 {
    margin: 0;
    font-size: 32px;
}

.advanced-search-box {
    background: white;
    max-width: 1000px;
    margin: -30px auto 40px auto;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--border);
    position: relative;
    z-index: 10;
}

.search-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}

.filter-group select,
.filter-group input {
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    font-size: 14px;
}

.range-group {
    display: flex;
    gap: 10px;
}

.range-group input {
    width: 100%;
}

.btn-search {
    background: var(--secondary);
    color: var(--primary);
    padding: 12px;
    border: none;
    border-radius: 4px;
    font-weight: 700;
    cursor: pointer;
    height: 42px;
    transition: background 0.2s;
}

.btn-search:hover {
    background: #0ea5e9;
    color: white;
}

/* Grid & Cards (Reused from vehicles.php for consistency) */
.results-container {
    padding: 0 5% 60px 5%;
}

.results-info {
    margin-bottom: 20px;
    font-size: 16px;
    color: #475569;
    font-weight: 500;
    border-bottom: 2px solid var(--border);
    padding-bottom: 10px;
}

.car-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.car-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border);
    transition: transform 0.2s;
}

.car-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
}

.car-img-wrapper {
    position: relative;
    height: 200px;
    background: #e2e8f0;
}

.car-img-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.condition-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--primary);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.car-details {
    padding: 15px;
}

.car-price {
    font-size: 22px;
    font-weight: 800;
    color: var(--accent);
    margin-bottom: 5px;
}

.car-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--primary);
    margin: 0 0 15px 0;
    height: 40px;
    overflow: hidden;
}

.car-specs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    font-size: 12px;
    color: #475569;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border);
}

.btn-view {
    display: block;
    text-align: center;
    background: var(--light);
    color: var(--primary);
    padding: 10px;
    border-radius: 4px;
    font-weight: 600;
    border: 1px solid var(--border);
}

.btn-view:hover {
    background: var(--primary);
    color: white;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 40px;
}

.pagination a {
    padding: 8px 16px;
    border: 1px solid var(--border);
    border-radius: 4px;
    text-decoration: none;
    color: var(--primary);
    font-weight: 600;
}

.pagination .active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}
</style>

<div class="search-header">
    <h1>Advanced Vehicle Search</h1>
</div>

<!-- Advanced Search Form -->
<div class="advanced-search-box">
    <form action="search_results.php" method="GET">
        <div class="search-grid">
            <div class="filter-group">
                <label>Make / Brand</label>
                <select name="brand">
                    <option value="">All Brands</option>
                    <?php foreach ($brands as $b): ?>
                    <option value="<?php echo sanitize($b); ?>"
                        <?php echo ($_GET['brand'] ?? '') === $b ? 'selected' : ''; ?>><?php echo sanitize($b); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Body Style</label>
                <select name="body_type">
                    <option value="">All Styles</option>
                    <?php foreach ($body_types as $bt): ?>
                    <option value="<?php echo sanitize($bt); ?>"
                        <?php echo ($_GET['body_type'] ?? '') === $bt ? 'selected' : ''; ?>><?php echo sanitize($bt); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Price Range ($)</label>
                <div class="range-group">
                    <input type="number" name="min_price" placeholder="Min"
                        value="<?php echo sanitize($_GET['min_price'] ?? ''); ?>" min="0" step="500">
                    <input type="number" name="max_price" placeholder="Max"
                        value="<?php echo sanitize($_GET['max_price'] ?? ''); ?>" min="0" step="500">
                </div>
            </div>

            <div class="filter-group">
                <label>Year Range</label>
                <div class="range-group">
                    <input type="number" name="min_year" placeholder="From"
                        value="<?php echo sanitize($_GET['min_year'] ?? ''); ?>" min="1990"
                        max="<?php echo date('Y')+1; ?>">
                    <input type="number" name="max_year" placeholder="To"
                        value="<?php echo sanitize($_GET['max_year'] ?? ''); ?>" min="1990"
                        max="<?php echo date('Y')+1; ?>">
                </div>
            </div>

            <div class="filter-group">
                <button type="submit" class="btn-search">Search Inventory</button>
            </div>
        </div>
        <div style="text-align: right; margin-top: 15px;">
            <a href="search_results.php" style="font-size: 13px; color: #64748b; text-decoration: underline;">Reset All
                Filters</a>
        </div>
    </form>
</div>

<!-- Search Results Array -->
<div class="results-container">
    <div class="results-info">
        Found <?php echo $total_vehicles; ?> vehicle(s) matching your criteria.
    </div>

    <div class="car-grid">
        <?php if (empty($vehicles)): ?>
        <div
            style="grid-column: 1 / -1; padding: 60px; text-align: center; background: white; border: 1px dashed #cbd5e1; border-radius: 8px;">
            <h3 style="color: var(--primary); margin-top: 0;">No Match Found</h3>
            <p style="color: #64748b;">We couldn't find any vehicles matching those exact parameters. Try widening your
                price range or year criteria.</p>
        </div>
        <?php else: ?>
        <?php foreach ($vehicles as $car): ?>
        <div class="car-card">
            <div class="car-img-wrapper">
                <span class="condition-badge"><?php echo sanitize($car['condition']); ?></span>
                <?php 
                            $img_src = (!empty($car['image_path']) && file_exists(__DIR__ . '/' . $car['image_path'])) 
                                       ? BASE_URL . $car['image_path'] 
                                       : BASE_URL . 'assets/images/placeholder-car.jpg'; 
                        ?>
                <img src="<?php echo $img_src; ?>" alt="<?php echo sanitize($car['name']); ?>" loading="lazy">
            </div>
            <div class="car-details">
                <div class="car-price">
                    <?php echo format_price($car['discount_price'] ?: $car['price']); ?>
                </div>
                <h3 class="car-title"><?php echo sanitize($car['year'] . ' ' . $car['brand'] . ' ' . $car['name']); ?>
                </h3>
                <div class="car-specs">
                    <div>⏱️ <?php echo format_mileage($car['mileage']); ?></div>
                    <div>⚙️ <?php echo sanitize($car['transmission']); ?></div>
                    <div>⛽ <?php echo sanitize($car['fuel_type']); ?></div>
                    <div>🚗 <?php echo sanitize($car['drive_type']); ?></div>
                </div>
                <a href="<?php echo BASE_URL; ?>vehicle_detail.php?id=<?php echo $car['id']; ?>" class="btn-view">View
                    Details</a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php 
                    $query_args = $_GET;
                    $query_args['page'] = $i;
                    $link = 'search_results.php?' . http_build_query($query_args);
                ?>
        <a href="<?php echo $link; ?>" class="<?php echo $page === $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>