<?php
// vehicles.php
require_once __DIR__ . '/includes/header.php';

// Fetch WhatsApp number for the contact button
$whatsapp_number = get_site_setting($pdo, 'whatsapp_number', '254700000000'); // Fallback number if not set
$clean_wa = preg_replace('/[^0-9]/', '', $whatsapp_number);

/* ══════════════════════════════════════════
   HELPERS
══════════════════════════════════════════ */

// Read a filter as a list. Accepts ?brand=BMW (old links) and ?brand[]=BMW&brand[]=Audi
function vp_list(string $key): array
{
    $v = $_GET[$key] ?? [];
    if (!is_array($v)) {
        $v = ($v === '') ? [] : [$v];
    }
    $out = [];
    foreach ($v as $item) {
        if (is_scalar($item)) {
            $item = trim((string)$item);
            if ($item !== '') {
                $out[] = $item;
            }
        }
    }
    return array_values(array_unique($out));
}

function vp_int(string $key): ?int
{
    if (isset($_GET[$key]) && is_scalar($_GET[$key]) && (int)$_GET[$key] > 0) {
        return (int)$_GET[$key];
    }
    return null;
}

function vp_url(array $q): string
{
    unset($q['page']);
    $q = array_filter($q, fn($v) => $v !== '' && $v !== [] && $v !== null);
    $s = http_build_query($q);
    return 'vehicles.php' . ($s ? '?' . $s : '');
}

// URL with one value (or several whole keys) removed — powers the removable chips
function vp_without(string $key, ?string $val = null): string
{
    $q = $_GET;
    if ($val !== null && isset($q[$key]) && is_array($q[$key])) {
        $q[$key] = array_values(array_diff($q[$key], [$val]));
    } else {
        unset($q[$key]);
    }
    return vp_url($q);
}

function vp_drop(array $keys): string
{
    $q = $_GET;
    foreach ($keys as $k) {
        unset($q[$k]);
    }
    return vp_url($q);
}

// Hidden inputs that carry every other query parameter through a form
function vp_hidden(array $data, array $skip = [], string $prefix = ''): string
{
    $html = '';
    foreach ($data as $k => $v) {
        if ($prefix === '' && in_array($k, $skip, true)) {
            continue;
        }
        $name = $prefix === '' ? $k : $prefix . '[' . $k . ']';
        if (is_array($v)) {
            $html .= vp_hidden($v, $skip, $name);
        } elseif (is_scalar($v)) {
            $html .= '<input type="hidden" name="' . htmlspecialchars((string)$name) . '" value="'
                . htmlspecialchars((string)$v) . '">';
        }
    }
    return $html;
}

// Option counts for a filter column (column names are whitelisted)
function vp_facet(PDO $pdo, string $key): array
{
    $cols = [
        'condition'    => '`condition`',
        'brand'        => 'brand',
        'body_type'    => 'body_type',
        'fuel_type'    => 'fuel_type',
        'transmission' => 'transmission',
        'drive_type'   => 'drive_type',
    ];
    if (!isset($cols[$key])) {
        return [];
    }
    $c = $cols[$key];
    try {
        return $pdo->query(
            "SELECT $c AS val, COUNT(*) AS cnt FROM vehicles
             WHERE availability_status = 'Available' AND $c IS NOT NULL AND $c <> ''
             GROUP BY $c ORDER BY $c"
        )->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Throwable $e) {
        return [];
    }
}

function vp_body_icon(string $type): string
{
    $map = [
        'sedan'       => 'fa-car-side',
        'suv'         => 'fa-truck-monster',
        'coupe'       => 'fa-car-rear',
        'hatchback'   => 'fa-car',
        'truck'       => 'fa-truck-pickup',
        'pickup'      => 'fa-truck-pickup',
        'van'         => 'fa-van-shuttle',
        'convertible' => 'fa-sun',
        'wagon'       => 'fa-car-side',
    ];
    return $map[strtolower($type)] ?? 'fa-car-side';
}

function vp_group_open(string $title, int $badge, bool $open): void
{
    echo '<details class="fg"' . ($open ? ' open' : '') . '><summary><span>' . $title . '</span>'
        . ($badge ? '<b class="fg-badge">' . $badge . '</b>' : '') . '</summary><div class="fg-body">';
}

function vp_group_close(): void
{
    echo '</div></details>';
}

function vp_pills(string $name, array $facet, array $selected): void
{
    foreach ($facet as $val => $cnt) {
        $val = (string)$val; ?>
<label class="pill">
    <input type="checkbox" name="<?php echo $name; ?>[]" value="<?php echo sanitize($val); ?>"
        <?php echo in_array($val, $selected, true) ? 'checked' : ''; ?>>
    <span><?php echo sanitize($val); ?><em><?php echo (int)$cnt; ?></em></span>
</label>
<?php }
}

/* ══════════════════════════════════════════
   READ FILTERS
══════════════════════════════════════════ */
$f_condition = vp_list('condition');
$f_brand     = vp_list('brand');
$f_body      = vp_list('body_type');
$f_fuel      = vp_list('fuel_type');
$f_trans     = vp_list('transmission');
$f_drive     = vp_list('drive_type');
$f_min       = vp_int('min_price');
$f_max       = vp_int('max_price');
$f_year_min  = vp_int('year_min');
$f_year_max  = vp_int('year_max');
$f_mileage   = vp_int('mileage');
$f_deal      = !empty($_GET['deal']);
$f_q         = (isset($_GET['q']) && is_string($_GET['q'])) ? trim($_GET['q']) : '';

$where_clauses = ["v.availability_status = 'Available'"];
$params = [];

$in_filters = [
    'condition'    => ['v.`condition`', $f_condition],
    'brand'        => ['v.brand',        $f_brand],
    'body_type'    => ['v.body_type',    $f_body],
    'fuel_type'    => ['v.fuel_type',    $f_fuel],
    'transmission' => ['v.transmission', $f_trans],
    'drive_type'   => ['v.drive_type',   $f_drive],
];
foreach ($in_filters as $key => [$col, $vals]) {
    if ($vals) {
        $ph = [];
        foreach ($vals as $i => $val) {
            $name = ":{$key}_{$i}";
            $ph[] = $name;
            $params[$name] = $val;
        }
        $where_clauses[] = "$col IN (" . implode(',', $ph) . ")";
    }
}

if ($f_min) {
    $where_clauses[] = "COALESCE(v.discount_price, v.price) >= :min_price";
    $params[':min_price'] = $f_min;
}
if ($f_max) {
    $where_clauses[] = "COALESCE(v.discount_price, v.price) <= :max_price";
    $params[':max_price'] = $f_max;
}
if ($f_year_min) {
    $where_clauses[] = "v.year >= :year_min";
    $params[':year_min'] = $f_year_min;
}
if ($f_year_max) {
    $where_clauses[] = "v.year <= :year_max";
    $params[':year_max'] = $f_year_max;
}
if ($f_mileage) {
    $where_clauses[] = "v.mileage <= :max_mileage";
    $params[':max_mileage'] = $f_mileage;
}
if ($f_deal) {
    $where_clauses[] = "v.discount_price IS NOT NULL AND v.discount_price > 0";
}
if ($f_q !== '') {
    $words = array_slice(preg_split('/\s+/', $f_q), 0, 4);
    foreach ($words as $i => $w) {
        $where_clauses[] = "CONCAT_WS(' ', v.year, v.brand, v.name) LIKE :q_$i";
        $params[":q_$i"] = '%' . addcslashes($w, '%_\\') . '%';
    }
}

$where_sql = implode(' AND ', $where_clauses);

/* ══════════════════════════════════════════
   PAGINATION + SORT
══════════════════════════════════════════ */
$allowed_per_page = [12, 24, 48, 96];
$limit = (isset($_GET['per_page']) && is_scalar($_GET['per_page']) && in_array((int)$_GET['per_page'], $allowed_per_page, true))
    ? (int)$_GET['per_page']
    : 24;

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles v WHERE $where_sql");
$count_stmt->execute($params);
$total_vehicles = (int)$count_stmt->fetchColumn();
$total_pages    = (int)ceil($total_vehicles / $limit);

$page   = max(1, (int)($_GET['page'] ?? 1));
$page   = min($page, max(1, $total_pages));
$offset = ($page - 1) * $limit;

$sort_options = [
    'newest'      => 'v.created_at DESC',
    'price_asc'   => 'COALESCE(v.discount_price,v.price) ASC',
    'price_desc'  => 'COALESCE(v.discount_price,v.price) DESC',
    'year_desc'   => 'v.year DESC',
    'mileage_asc' => 'v.mileage ASC',
];
$sort_key  = (isset($_GET['sort']) && is_string($_GET['sort']) && array_key_exists($_GET['sort'], $sort_options))
    ? $_GET['sort'] : 'newest';
$order_sql = $sort_options[$sort_key];

$query = "SELECT v.*, vi.image_path
          FROM vehicles v
          LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_main = 1
          WHERE $where_sql
          ORDER BY $order_sql
          LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$vehicles = $stmt->fetchAll();

$showing_from = $total_vehicles ? $offset + 1 : 0;
$showing_to   = min($offset + $limit, $total_vehicles);

/* ══════════════════════════════════════════
   FILTER OPTIONS (with counts) + BOUNDS
══════════════════════════════════════════ */
$fac = [];
foreach (['condition', 'brand', 'body_type', 'fuel_type', 'transmission', 'drive_type'] as $k) {
    $fac[$k] = vp_facet($pdo, $k);
}
$total_available = array_sum($fac['brand']);

$bounds = ['pmin' => 0, 'pmax' => 0, 'ymin' => 0, 'ymax' => 0];
try {
    $b = $pdo->query(
        "SELECT MIN(COALESCE(discount_price, price)) AS pmin, MAX(COALESCE(discount_price, price)) AS pmax,
                MIN(year) AS ymin, MAX(year) AS ymax
         FROM vehicles WHERE availability_status = 'Available'"
    )->fetch();
    if ($b) {
        $bounds = $b;
    }
} catch (Throwable $e) {
}

$pmax_raw = (int)($bounds['pmax'] ?? 0);
$price_step = max(1000, (int)pow(10, max(0, strlen((string)max($pmax_raw, 1)) - 3)));
$price_lo = (int)(floor(((int)($bounds['pmin'] ?? 0)) / $price_step) * $price_step);
$price_hi = (int)(ceil($pmax_raw / $price_step) * $price_step);
$show_slider = $price_hi > $price_lo;

$year_hi = (int)($bounds['ymax'] ?? 0) ?: (int)date('Y');
$year_lo = (int)($bounds['ymin'] ?? 0) ?: ($year_hi - 20);

$slider_min = $f_min ? max($price_lo, min($f_min, $price_hi)) : $price_lo;
$slider_max = $f_max ? max($price_lo, min($f_max, $price_hi)) : $price_hi;

/* ══════════════════════════════════════════
   ACTIVE FILTER CHIPS
══════════════════════════════════════════ */
$chips = [];
foreach (['condition' => $f_condition, 'brand' => $f_brand, 'body_type' => $f_body, 'fuel_type' => $f_fuel,
          'transmission' => $f_trans, 'drive_type' => $f_drive] as $k => $vals) {
    foreach ($vals as $v) {
        $chips[] = [$v, vp_without($k, $v)];
    }
}
if ($f_q !== '') {
    $chips[] = ['“' . $f_q . '”', vp_drop(['q'])];
}
if ($f_min || $f_max) {
    $label = 'KSh ' . ($f_min ? number_format($f_min) : '0') . ' – ' . ($f_max ? number_format($f_max) : 'Any');
    $chips[] = [$label, vp_drop(['min_price', 'max_price'])];
}
if ($f_year_min || $f_year_max) {
    $label = ($f_year_min ?: 'Any') . ' – ' . ($f_year_max ?: 'Any');
    $chips[] = ['Year ' . $label, vp_drop(['year_min', 'year_max'])];
}
if ($f_mileage) {
    $chips[] = ['Under ' . number_format($f_mileage) . ' km', vp_drop(['mileage'])];
}
if ($f_deal) {
    $chips[] = ['Discounted', vp_drop(['deal'])];
}

$active_count = count($chips);
$has_filters  = $active_count > 0;
$clear_all    = vp_url(array_intersect_key($_GET, ['sort' => 1, 'per_page' => 1]));

$brand_open = true;
?>

<style>
/* ──────────────────────────────────────────
   VEHICLES PAGE  —  LB Autos Showroom
   Light gallery layout · Forest & Copper accents
────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500&family=Manrope:wght@300;400;500;600;700;800&display=swap');

:root {
    --v-forest: #0f1a15;
    --v-forest-mid: #1e3229;
    --v-cream: #f6f1e7;
    --v-paper: #fbf8f1;
    --v-line: #e4dac7;
    --v-ink: #211d17;
    --v-stone: #6b6154;
    --v-taupe: #9a8974;
    --v-copper: #b87333;
    --v-copper-light: #d4935a;
    --v-copper-pale: #e8bf94;
    --v-copper-deep: #8a5220;
    --v-copper-glow: rgba(184, 115, 51, 0.12);
    --v-ember: #c94f2b;
    --v-ease: cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    background: var(--v-cream);
}

/* ── PAGE BANNER ── */
.shop-hero {
    position: relative;
    background: var(--v-forest);
    padding: 46px 6% 42px;
    overflow: hidden;
    border-bottom: 1px solid rgba(212, 147, 90, 0.25);
}

.shop-hero::before {
    content: '';
    position: absolute;
    right: -10%;
    top: -120%;
    width: 60%;
    height: 340%;
    background: radial-gradient(circle, rgba(184, 115, 51, 0.2), transparent 60%);
    pointer-events: none;
}

.shop-hero__in {
    position: relative;
    max-width: 1700px;
    margin: 0 auto;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}

.crumbs {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Manrope', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: rgba(246, 241, 231, 0.45);
    margin-bottom: 16px;
}

.crumbs a:hover {
    color: var(--v-copper-light);
}

.crumbs i {
    font-size: 8px;
    color: var(--v-copper);
}

.crumbs span {
    color: var(--v-copper-light);
}

.shop-hero h1 {
    font-family: 'Fraunces', serif;
    font-weight: 400;
    font-size: clamp(34px, 4.2vw, 54px);
    letter-spacing: -0.025em;
    line-height: 1;
    color: var(--v-cream);
}

.shop-hero h1 em {
    font-style: italic;
    font-weight: 500;
    color: var(--v-copper-light);
}

.shop-hero__stat {
    font-family: 'Manrope', sans-serif;
    font-size: 13px;
    color: rgba(246, 241, 231, 0.55);
    line-height: 1.6;
    text-align: right;
}

.shop-hero__stat strong {
    display: block;
    font-family: 'Fraunces', serif;
    font-size: 34px;
    font-weight: 500;
    color: var(--v-copper-light);
    line-height: 1;
}

/* ── LAYOUT ── */
.inventory-layout {
    display: flex;
    gap: 28px;
    align-items: flex-start;
    max-width: 1760px;
    margin: 0 auto;
    padding: 32px 3% 0;
    min-height: 80vh;
}

/* ══════════════════════════════
   FILTER SIDEBAR
══════════════════════════════ */
.filter-sidebar {
    width: 300px;
    flex-shrink: 0;
    position: sticky;
    top: 88px;
    height: calc(100vh - 112px);
    max-height: 900px;
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid var(--v-line);
    border-top: 3px solid var(--v-copper);
    border-radius: 8px;
    box-shadow: 0 18px 50px rgba(33, 29, 23, 0.06);
    overflow: hidden;
    z-index: 100;
}

.fs-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 22px 16px;
    background: linear-gradient(180deg, var(--v-paper), #fff);
    border-bottom: 1px solid var(--v-line);
}

.fs-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Fraunces', serif;
    font-size: 22px;
    font-weight: 500;
    letter-spacing: -0.015em;
    color: var(--v-ink);
}

.fs-title .fs-count {
    min-width: 22px;
    height: 22px;
    padding: 0 7px;
    border-radius: 11px;
    background: var(--v-copper);
    color: #fff;
    font-family: 'Manrope', sans-serif;
    font-size: 11px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.filter-clear {
    font-family: 'Manrope', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    color: var(--v-copper-deep);
    border-bottom: 1px solid var(--copper-line, rgba(184, 115, 51, 0.35));
    padding-bottom: 1px;
    transition: color 0.2s;
}

.filter-clear:hover {
    color: var(--v-copper);
}

.close-filter-btn {
    display: none;
    background: none;
    border: none;
    font-size: 18px;
    color: var(--v-ink);
    cursor: pointer;
    padding: 4px;
}

.filter-actions {
    display: flex;
    align-items: center;
    gap: 14px;
}

#filterForm {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
}

.fs-body {
    flex: 1;
    overflow-y: auto;
    padding: 18px 22px 6px;
    scrollbar-width: thin;
    scrollbar-color: var(--v-line) transparent;
}

.fs-body::-webkit-scrollbar {
    width: 5px;
}

.fs-body::-webkit-scrollbar-thumb {
    background: var(--v-line);
    border-radius: 3px;
}

/* Keyword search */
.fs-search {
    position: relative;
    margin-bottom: 14px;
}

.fs-search i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    color: var(--v-copper);
    pointer-events: none;
}

.fs-search input {
    width: 100%;
    padding: 13px 14px 13px 38px;
    border: 1px solid var(--v-line);
    border-radius: 6px;
    background: var(--v-paper);
    font-family: 'Manrope', sans-serif;
    font-size: 13px;
    color: var(--v-ink);
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}

.fs-search input:focus {
    outline: none;
    background: #fff;
    border-color: var(--v-copper);
    box-shadow: 0 0 0 3px var(--v-copper-glow);
}

.fs-search input::placeholder {
    color: var(--v-taupe);
}

/* Toggle switch */
.switch {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    margin-bottom: 8px;
    border: 1px dashed var(--v-copper-pale);
    border-radius: 6px;
    background: var(--v-copper-glow);
    font-family: 'Manrope', sans-serif;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--v-ink);
    cursor: pointer;
}

.switch small {
    display: block;
    font-size: 10.5px;
    font-weight: 500;
    color: var(--v-stone);
    margin-top: 2px;
}

.switch input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.switch__track {
    position: relative;
    width: 40px;
    height: 22px;
    flex-shrink: 0;
    border-radius: 11px;
    background: #d6cbb6;
    transition: background 0.25s var(--v-ease);
}

.switch__track::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
    transition: transform 0.25s var(--v-ease);
}

.switch input:checked+.switch__track {
    background: linear-gradient(135deg, var(--v-copper), var(--v-copper-light));
}

.switch input:checked+.switch__track::after {
    transform: translateX(18px);
}

.switch input:focus-visible+.switch__track {
    outline: 2px solid var(--v-copper);
    outline-offset: 2px;
}

/* Accordion groups */
.fg {
    border-bottom: 1px solid var(--v-line);
}

.fg:last-child {
    border-bottom: none;
}

.fg summary {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 0;
    cursor: pointer;
    font-family: 'Manrope', sans-serif;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--v-ink);
    user-select: none;
}

.fg summary::-webkit-details-marker {
    display: none;
}

.fg summary span {
    flex: 1;
}

.fg summary::after {
    content: '';
    width: 7px;
    height: 7px;
    border-right: 1.5px solid var(--v-taupe);
    border-bottom: 1.5px solid var(--v-taupe);
    transform: rotate(45deg) translateY(-2px);
    transition: transform 0.3s var(--v-ease);
}

.fg[open] summary::after {
    transform: rotate(-135deg) translateY(-2px);
}

.fg summary:hover {
    color: var(--v-copper-deep);
}

.fg-badge {
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 9px;
    background: var(--v-copper);
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.fg-body {
    padding: 0 0 18px;
}

/* Pills */
.pill-row {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.pill {
    position: relative;
    cursor: pointer;
}

.pill input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.pill span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 13px;
    border: 1px solid var(--v-line);
    border-radius: 999px;
    background: #fff;
    font-family: 'Manrope', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: var(--v-stone);
    transition: all 0.2s var(--v-ease);
}

.pill span em {
    font-style: normal;
    font-size: 10px;
    font-weight: 700;
    color: var(--v-taupe);
    transition: color 0.2s;
}

.pill:hover span {
    border-color: var(--v-copper-light);
    color: var(--v-ink);
}

.pill input:checked+span {
    background: var(--v-forest);
    border-color: var(--v-forest);
    color: var(--v-cream);
    box-shadow: 0 4px 12px rgba(15, 26, 21, 0.2);
}

.pill input:checked+span em {
    color: var(--v-copper-light);
}

.pill input:focus-visible+span {
    outline: 2px solid var(--v-copper);
    outline-offset: 2px;
}

/* Brand checklist */
.list-search {
    width: 100%;
    padding: 9px 12px;
    margin-bottom: 8px;
    border: 1px solid var(--v-line);
    border-radius: 5px;
    font-family: 'Manrope', sans-serif;
    font-size: 12px;
    color: var(--v-ink);
    background: var(--v-paper);
}

.list-search:focus {
    outline: none;
    border-color: var(--v-copper);
}

.opt-list {
    max-height: 230px;
    overflow-y: auto;
    padding-right: 4px;
    scrollbar-width: thin;
    scrollbar-color: var(--v-line) transparent;
}

.opt-list::-webkit-scrollbar {
    width: 4px;
}

.opt-list::-webkit-scrollbar-thumb {
    background: var(--v-line);
    border-radius: 2px;
}

.opt {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 8px 6px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.15s;
    font-family: 'Manrope', sans-serif;
    font-size: 13px;
    color: var(--v-stone);
}

.opt:hover {
    background: var(--v-paper);
    color: var(--v-ink);
}

.opt input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.opt__box {
    position: relative;
    width: 17px;
    height: 17px;
    flex-shrink: 0;
    border: 1.5px solid #cdbb9c;
    border-radius: 4px;
    background: #fff;
    transition: all 0.2s var(--v-ease);
}

.opt__box::after {
    content: '';
    position: absolute;
    left: 4.5px;
    top: 1.5px;
    width: 4px;
    height: 8px;
    border-right: 2px solid #fff;
    border-bottom: 2px solid #fff;
    transform: rotate(45deg) scale(0);
    transition: transform 0.2s var(--v-ease);
}

.opt input:checked~.opt__box {
    background: var(--v-copper);
    border-color: var(--v-copper);
}

.opt input:checked~.opt__box::after {
    transform: rotate(45deg) scale(1);
}

.opt input:checked~.opt__name {
    color: var(--v-ink);
    font-weight: 700;
}

.opt input:focus-visible~.opt__box {
    outline: 2px solid var(--v-copper);
    outline-offset: 2px;
}

.opt__name {
    flex: 1;
}

.opt__count {
    font-size: 11px;
    font-weight: 700;
    color: var(--v-taupe);
    background: var(--v-cream);
    padding: 2px 8px;
    border-radius: 10px;
}

/* Body-type tiles */
.tile-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.tile {
    position: relative;
    cursor: pointer;
}

.tile input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.tile span {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    padding: 12px 4px 10px;
    border: 1px solid var(--v-line);
    border-radius: 6px;
    background: #fff;
    font-family: 'Manrope', sans-serif;
    transition: all 0.2s var(--v-ease);
}

.tile i {
    font-size: 17px;
    color: var(--v-taupe);
    transition: color 0.2s, transform 0.3s var(--v-ease);
}

.tile b {
    font-size: 11px;
    font-weight: 700;
    color: var(--v-ink);
}

.tile em {
    font-style: normal;
    font-size: 10px;
    font-weight: 600;
    color: var(--v-taupe);
}

.tile:hover span {
    border-color: var(--v-copper-light);
}

.tile:hover i {
    transform: translateY(-2px);
}

.tile input:checked+span {
    border-color: var(--v-copper);
    background: var(--v-copper-glow);
    box-shadow: 0 0 0 1px var(--v-copper);
}

.tile input:checked+span i {
    color: var(--v-copper);
}

.tile input:focus-visible+span {
    outline: 2px solid var(--v-copper);
    outline-offset: 2px;
}

/* Price slider */
.range {
    position: relative;
    height: 28px;
    margin: 4px 6px 2px;
}

.range__track {
    position: absolute;
    top: 12px;
    left: 0;
    right: 0;
    height: 4px;
    border-radius: 4px;
    background: var(--v-line);
}

.range__fill {
    position: absolute;
    top: 0;
    bottom: 0;
    border-radius: 4px;
    background: linear-gradient(90deg, var(--v-copper-deep), var(--v-copper-light));
}

.range input[type=range] {
    position: absolute;
    left: -6px;
    top: 0;
    width: calc(100% + 12px);
    height: 28px;
    margin: 0;
    background: none;
    pointer-events: none;
    -webkit-appearance: none;
    appearance: none;
}

.range input[type=range]::-webkit-slider-runnable-track {
    background: transparent;
}

.range input[type=range]::-moz-range-track {
    background: transparent;
}

.range input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    pointer-events: auto;
    width: 18px;
    height: 18px;
    margin-top: 5px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid var(--v-copper);
    box-shadow: 0 2px 8px rgba(138, 82, 32, 0.35);
    cursor: grab;
}

.range input[type=range]::-moz-range-thumb {
    pointer-events: auto;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid var(--v-copper);
    box-shadow: 0 2px 8px rgba(138, 82, 32, 0.35);
    cursor: grab;
}

.range__readout {
    display: flex;
    justify-content: space-between;
    margin: 0 2px 14px;
    font-family: 'Fraunces', serif;
    font-size: 15px;
    font-weight: 500;
    color: var(--v-ink);
}

.range__readout span:last-child {
    text-align: right;
}

.price-range {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.money {
    position: relative;
}

.money span {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    font-family: 'Manrope', sans-serif;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.08em;
    color: var(--v-copper-deep);
    pointer-events: none;
}

.money input,
.fg-body select {
    width: 100%;
    padding: 11px 10px 11px 40px;
    border: 1px solid var(--v-line);
    border-radius: 5px;
    background: #fff;
    font-family: 'Manrope', sans-serif;
    font-size: 13px;
    color: var(--v-ink);
    transition: border-color 0.2s, box-shadow 0.2s;
    -moz-appearance: textfield;
}

.money input::-webkit-outer-spin-button,
.money input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.money input:focus,
.fg-body select:focus {
    outline: none;
    border-color: var(--v-copper);
    box-shadow: 0 0 0 3px var(--v-copper-glow);
}

.money input::placeholder {
    color: var(--v-taupe);
}

.chip-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 16px;
}

.chip-btn {
    padding: 6px 11px;
    border: 1px solid var(--v-line);
    border-radius: 999px;
    background: var(--v-paper);
    font-family: 'Manrope', sans-serif;
    font-size: 11px;
    font-weight: 700;
    color: var(--v-stone);
    cursor: pointer;
    transition: all 0.2s var(--v-ease);
}

.chip-btn:hover {
    border-color: var(--v-copper);
    color: var(--v-copper-deep);
}

.chip-btn.on {
    background: var(--v-copper-glow);
    border-color: var(--v-copper);
    color: var(--v-copper-deep);
}

/* Year selects */
.year-range {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.year-range select {
    padding: 11px 30px 11px 12px;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' viewBox='0 0 12 7'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23b87333' stroke-width='1.6' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 11px center;
}

.field-label {
    display: block;
    font-family: 'Manrope', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--v-taupe);
    margin-bottom: 6px;
}

/* Sidebar footer */
.fs-foot {
    padding: 14px 22px 18px;
    border-top: 1px solid var(--v-line);
    background: linear-gradient(0deg, var(--v-paper), #fff);
    display: grid;
    gap: 10px;
}

.btn-filter {
    position: relative;
    overflow: hidden;
    width: 100%;
    background: linear-gradient(135deg, var(--v-copper) 0%, var(--v-copper-light) 100%);
    color: var(--v-forest);
    padding: 15px;
    border: none;
    border-radius: 5px;
    font-family: 'Manrope', sans-serif;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 10px 26px rgba(184, 115, 51, 0.28);
    transition: transform 0.25s var(--v-ease), box-shadow 0.25s, filter 0.25s;
}

.btn-filter::after {
    content: '';
    position: absolute;
    top: 0;
    left: -60%;
    width: 40%;
    height: 100%;
    background: linear-gradient(100deg, transparent, rgba(255, 255, 255, 0.45), transparent);
    transform: skewX(-20deg);
    transition: left 0.7s var(--v-ease);
}

.btn-filter:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 34px rgba(184, 115, 51, 0.4);
    filter: brightness(1.05);
}

.btn-filter:hover::after {
    left: 130%;
}

.btn-reset {
    text-align: center;
    font-family: 'Manrope', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: var(--v-stone);
}

.btn-reset:hover {
    color: var(--v-copper-deep);
}

/* ══════════════════════════════
   RESULTS AREA
══════════════════════════════ */
.inventory-results {
    flex: 1;
    min-width: 0;
    padding-bottom: 70px;
}

.results-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 18px;
    padding: 14px 18px;
    background: #fff;
    border: 1px solid var(--v-line);
    border-radius: 8px;
    box-shadow: 0 6px 24px rgba(33, 29, 23, 0.04);
}

.rt-left {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.results-count {
    font-family: 'Manrope', sans-serif;
    font-size: 13px;
    color: var(--v-stone);
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 4px 8px;
}

.results-count strong {
    font-family: 'Fraunces', serif;
    font-size: 26px;
    font-weight: 500;
    color: var(--v-ink);
    line-height: 1;
}

.results-count small {
    font-size: 11.5px;
    color: var(--v-taupe);
    font-weight: 500;
}

.mobile-filter-btn {
    display: none;
}

.rt-right {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-left: auto;
}

#viewForm {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.sel {
    display: flex;
    align-items: center;
    gap: 9px;
    font-family: 'Manrope', sans-serif;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--v-taupe);
}

.sel select {
    padding: 9px 30px 9px 12px;
    border: 1px solid var(--v-line);
    border-radius: 5px;
    font-family: 'Manrope', sans-serif;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0;
    text-transform: none;
    color: var(--v-ink);
    background: var(--v-paper);
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' viewBox='0 0 12 7'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23b87333' stroke-width='1.6' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
}

.sel select:focus {
    outline: none;
    border-color: var(--v-copper);
}

.view-toggle {
    display: flex;
    border: 1px solid var(--v-line);
    border-radius: 5px;
    overflow: hidden;
    background: var(--v-paper);
}

.view-toggle button {
    width: 38px;
    height: 36px;
    border: none;
    background: transparent;
    color: var(--v-taupe);
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.view-toggle button+button {
    border-left: 1px solid var(--v-line);
}

.view-toggle button:hover {
    color: var(--v-copper-deep);
}

.view-toggle button.on {
    background: var(--v-forest);
    color: var(--v-copper-light);
}

/* Active chips */
.chips-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-bottom: 18px;
}

.chips-row__label {
    font-family: 'Manrope', sans-serif;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--v-taupe);
    margin-right: 4px;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px 6px 13px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid var(--v-copper-pale);
    font-family: 'Manrope', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: var(--v-ink);
    transition: all 0.2s var(--v-ease);
}

.filter-tag i {
    width: 18px;
    height: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--v-copper-glow);
    color: var(--v-copper-deep);
    font-size: 9px;
    transition: all 0.2s;
}

.filter-tag:hover {
    border-color: var(--v-ember);
}

.filter-tag:hover i {
    background: var(--v-ember);
    color: #fff;
}

.chips-clear {
    margin-left: 6px;
    font-family: 'Manrope', sans-serif;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    color: var(--v-copper-deep);
    border-bottom: 1px solid rgba(184, 115, 51, 0.35);
}

/* ── CAR GRID ── */
.car-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 18px;
}

.car-grid.view-compact {
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 12px;
}

.car-grid.view-list {
    grid-template-columns: 1fr;
    gap: 14px;
}

/* ── CAR CARD ── */
.car-card {
    background: #fff;
    position: relative;
    border: 1px solid var(--v-line);
    border-radius: 6px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.35s var(--v-ease), transform 0.35s var(--v-ease);
    animation: cardIn 0.5s var(--v-ease) both;
    animation-delay: calc(var(--i, 0) * 25ms);
}

.car-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--v-copper-deep), var(--v-copper-light), var(--v-copper-deep));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s var(--v-ease);
    z-index: 4;
}

.car-card:hover {
    box-shadow: 0 28px 60px rgba(33, 29, 23, 0.1), 0 0 0 1px rgba(184, 115, 51, 0.22);
    transform: translateY(-5px);
    z-index: 2;
}

.car-card:hover::after {
    transform: scaleX(1);
}

.card-link-overlay {
    position: absolute;
    inset: 0;
    z-index: 5;
}

.car-img-wrapper {
    position: relative;
    aspect-ratio: 16 / 11;
    background: var(--v-cream);
    overflow: hidden;
}

.car-img-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s var(--v-ease), filter 0.7s var(--v-ease);
}

.car-card:hover .car-img-wrapper img {
    transform: scale(1.06);
    filter: brightness(1.05);
}

.car-img-wrapper::after {
    content: '';
    position: absolute;
    inset: auto 0 0 0;
    height: 45%;
    background: linear-gradient(to top, rgba(15, 26, 21, 0.35), transparent);
    pointer-events: none;
}

.condition-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    font-family: 'Manrope', sans-serif;
    font-size: 8.5px;
    font-weight: 800;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    padding: 5px 11px;
    border-radius: 2px;
    z-index: 3;
    pointer-events: none;
}

.condition-badge.new {
    background: var(--v-copper);
    color: var(--v-forest);
}

.condition-badge.used {
    background: rgba(255, 255, 255, 0.92);
    color: var(--v-ink);
    border: 1px solid rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(6px);
}

.condition-badge.cpo {
    background: var(--v-forest);
    color: var(--v-copper-light);
    border: 1px solid var(--v-copper);
}

.discount-ribbon {
    position: absolute;
    top: 12px;
    right: 12px;
    background: var(--v-ember);
    color: #fff;
    font-family: 'Manrope', sans-serif;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.1em;
    padding: 5px 9px;
    border-radius: 2px;
    z-index: 3;
    pointer-events: none;
    box-shadow: 0 4px 12px rgba(201, 79, 43, 0.35);
}

.tag-fresh {
    position: absolute;
    left: 12px;
    bottom: 10px;
    z-index: 3;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'Manrope', sans-serif;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #fff;
    pointer-events: none;
}

.tag-fresh::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #6fcf97;
    box-shadow: 0 0 8px #6fcf97;
}

.car-details {
    padding: 20px 20px 18px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.car-price-row {
    display: flex;
    align-items: baseline;
    gap: 10px;
    margin-bottom: 6px;
    flex-wrap: wrap;
}

.car-price {
    font-family: 'Fraunces', serif;
    font-size: 24px;
    font-weight: 600;
    color: var(--v-ink);
    line-height: 1;
    letter-spacing: -0.015em;
}

.car-price-old {
    font-family: 'Manrope', sans-serif;
    font-size: 11.5px;
    color: var(--v-taupe);
    text-decoration: line-through;
    font-weight: 400;
}

.car-title {
    font-family: 'Manrope', sans-serif;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--v-stone);
    margin: 0 0 16px;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    line-height: 1.45;
    min-height: 36px;
}

.car-specs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 12px;
    padding-bottom: 16px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--v-line);
}

.spec-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'Manrope', sans-serif;
    font-size: 11.5px;
    font-weight: 500;
    color: var(--v-stone);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.spec-item i {
    color: var(--v-copper-deep);
    font-size: 10.5px;
    width: 13px;
    flex-shrink: 0;
    opacity: 0.85;
}

.car-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
    position: relative;
    z-index: 10;
}

.btn-view {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 12px;
    border: 1px solid var(--v-line);
    border-radius: 4px;
    font-family: 'Manrope', sans-serif;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--v-ink);
    background: #fff;
    transition: background 0.25s, border-color 0.25s, color 0.25s;
}

.btn-view .t-short {
    display: none;
}

.btn-view i {
    transition: transform 0.2s var(--v-ease);
}

.btn-view:hover {
    background: var(--v-forest);
    border-color: var(--v-forest);
    color: var(--v-cream);
}

.btn-view:hover i {
    transform: translateX(4px);
}

.btn-whatsapp {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border: 1px solid var(--v-line);
    border-radius: 4px;
    background: #fff;
    color: var(--v-ink);
    font-size: 18px;
    transition: all 0.25s var(--v-ease);
    flex-shrink: 0;
}

.btn-whatsapp:hover {
    background: #25D366;
    border-color: #25D366;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(37, 211, 102, 0.3);
}

/* Compact view */
.view-compact .car-details {
    padding: 14px 14px 14px;
}

.view-compact .car-price {
    font-size: 19px;
}

.view-compact .car-price-old {
    font-size: 10.5px;
}

.view-compact .car-title {
    font-size: 11px;
    min-height: 0;
    margin-bottom: 10px;
    letter-spacing: 0.04em;
}

.view-compact .car-specs {
    gap: 6px 8px;
    padding-bottom: 10px;
    margin-bottom: 10px;
}

.view-compact .spec-item {
    font-size: 10.5px;
    gap: 6px;
}

.view-compact .btn-view {
    padding: 9px 8px;
    font-size: 9.5px;
    letter-spacing: 0.1em;
}

.view-compact .btn-view .t-full {
    display: none;
}

.view-compact .btn-view .t-short {
    display: inline;
}

.view-compact .btn-whatsapp {
    width: 36px;
    height: 36px;
    font-size: 16px;
}

.view-compact .condition-badge,
.view-compact .discount-ribbon {
    top: 8px;
    font-size: 8px;
    padding: 4px 8px;
}

.view-compact .condition-badge {
    left: 8px;
}

.view-compact .discount-ribbon {
    right: 8px;
}

.view-compact .tag-fresh {
    left: 8px;
    bottom: 7px;
    font-size: 8px;
}

/* List view */
.view-list .car-card {
    flex-direction: row;
}

.view-list .car-img-wrapper {
    width: 320px;
    flex-shrink: 0;
    aspect-ratio: auto;
    min-height: 220px;
}

.view-list .car-details {
    padding: 24px 28px;
    justify-content: center;
}

.view-list .car-price {
    font-size: 28px;
}

.view-list .car-title {
    min-height: 0;
    font-size: 14px;
    margin-bottom: 18px;
}

.view-list .car-specs {
    grid-template-columns: repeat(4, auto);
    justify-content: start;
    gap: 10px 28px;
}

.view-list .car-actions {
    margin-top: 0;
    max-width: 320px;
}

/* Empty state */
.empty-state {
    grid-column: 1 / -1;
    background: #fff;
    padding: 80px 20px;
    text-align: center;
    border: 1px solid var(--v-line);
    border-radius: 8px;
}

.empty-state i {
    font-size: 36px;
    color: var(--v-copper-pale);
    margin-bottom: 20px;
    display: block;
}

.empty-state h3 {
    font-family: 'Fraunces', serif;
    font-size: 28px;
    font-weight: 500;
    color: var(--v-ink);
    margin-bottom: 10px;
}

.empty-state p {
    font-family: 'Manrope', sans-serif;
    font-size: 14px;
    color: var(--v-stone);
    margin-bottom: 24px;
}

.empty-state a {
    display: inline-block;
    padding: 13px 28px;
    border-radius: 4px;
    background: var(--v-forest);
    color: var(--v-cream);
    font-family: 'Manrope', sans-serif;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    transition: background 0.25s;
}

.empty-state a:hover {
    background: var(--v-copper);
    color: var(--v-forest);
}

/* ── PAGINATION ── */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: 46px;
    flex-wrap: wrap;
}

.pagination a,
.pagination span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 42px;
    height: 42px;
    padding: 0 10px;
    border: 1px solid var(--v-line);
    border-radius: 5px;
    font-family: 'Manrope', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: var(--v-ink);
    background: #fff;
    transition: all 0.2s var(--v-ease);
}

.pagination a:hover {
    border-color: var(--v-copper);
    color: var(--v-copper-deep);
}

.pagination .pg-active {
    background: var(--v-forest);
    color: var(--v-copper-light);
    border-color: var(--v-forest);
}

.pagination .pg-ellipsis {
    border: none;
    background: transparent;
    min-width: 24px;
    padding: 0;
    color: var(--v-taupe);
}

.pagination-note {
    text-align: center;
    margin-top: 14px;
    font-family: 'Manrope', sans-serif;
    font-size: 12px;
    color: var(--v-taupe);
}

.filter-overlay {
    display: none;
}

@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateY(14px);
    }

    to {
        opacity: 1;
        transform: none;
    }
}

/* ──────────────────────────────────────────
   WIDE SCREENS — squeeze in even more cars
────────────────────────────────────────── */
@media (min-width: 1500px) {
    .car-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    }
}

/* ──────────────────────────────────────────
   TABLET & MOBILE
────────────────────────────────────────── */
@media (max-width: 1100px) {
    .view-list .car-img-wrapper {
        width: 240px;
    }

    .view-list .car-specs {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 900px) {
    .shop-hero {
        padding: 34px 6% 30px;
    }

    .shop-hero__stat {
        text-align: left;
    }

    .inventory-layout {
        padding: 20px 4% 0;
    }

    .inventory-results {
        width: 100%;
    }

    .mobile-filter-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--v-forest);
        color: var(--v-cream);
        border: none;
        padding: 11px 16px;
        border-radius: 5px;
        font-family: 'Manrope', sans-serif;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(15, 26, 21, 0.2);
    }

    .mobile-filter-btn i {
        color: var(--v-copper-light);
    }

    .mobile-filter-btn b {
        min-width: 18px;
        height: 18px;
        border-radius: 9px;
        background: var(--v-copper);
        color: #fff;
        font-size: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Sidebar becomes a slide-in drawer */
    .filter-sidebar {
        position: fixed;
        top: 0;
        left: -105%;
        width: 88%;
        max-width: 380px;
        height: 100vh;
        height: 100dvh;
        max-height: none;
        z-index: 1200;
        border-radius: 0;
        border: none;
        transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 20px 0 50px rgba(0, 0, 0, 0.45);
    }

    .filter-sidebar.open {
        left: 0;
    }

    .close-filter-btn {
        display: block;
    }

    .filter-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(10, 16, 13, 0.7);
        z-index: 1150;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s var(--v-ease), visibility 0.3s;
        backdrop-filter: blur(4px);
    }

    .filter-overlay.open {
        opacity: 1;
        visibility: visible;
    }
}

/* Two compact columns on phones */
@media (max-width: 650px) {
    .view-toggle {
        display: none;
    }

    .results-toolbar {
        padding: 12px;
    }

    .rt-right {
        width: 100%;
        margin-left: 0;
    }

    #viewForm {
        width: 100%;
        justify-content: space-between;
    }

    .car-grid,
    .car-grid.view-compact,
    .car-grid.view-list {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .car-grid .car-card,
    .car-grid.view-list .car-card {
        flex-direction: column;
    }

    .car-grid.view-list .car-img-wrapper {
        width: 100%;
        min-height: 0;
        aspect-ratio: 16 / 11;
    }

    .car-grid .car-details,
    .car-grid.view-list .car-details {
        padding: 12px 11px 11px;
    }

    .car-grid .car-price {
        font-size: 17px;
    }

    .car-grid .car-price-old {
        font-size: 10px;
    }

    .car-grid .car-title,
    .car-grid.view-list .car-title {
        font-size: 10.5px;
        min-height: 30px;
        margin-bottom: 10px;
    }

    .car-grid .car-specs,
    .car-grid.view-list .car-specs {
        grid-template-columns: 1fr;
        gap: 5px;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }

    .car-grid .spec-item {
        font-size: 10.5px;
    }

    .car-grid .btn-view {
        padding: 9px 6px;
        font-size: 9.5px;
    }

    .car-grid .btn-view .t-full {
        display: none;
    }

    .car-grid .btn-view .t-short {
        display: inline;
    }

    .car-grid .btn-view i {
        display: none;
    }

    .car-grid .btn-whatsapp {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }

    .car-grid.view-list .car-actions {
        max-width: none;
    }

    .car-grid .condition-badge {
        font-size: 7.5px;
        padding: 4px 7px;
        top: 8px;
        left: 8px;
    }

    .car-grid .discount-ribbon {
        font-size: 8px;
        padding: 4px 6px;
        top: 8px;
        right: 8px;
    }

    .car-grid .tag-fresh {
        display: none;
    }
}

@media (max-width: 360px) {

    .car-grid,
    .car-grid.view-compact,
    .car-grid.view-list {
        grid-template-columns: 1fr;
    }
}

@media (prefers-reduced-motion: reduce) {

    *,
    *::before,
    *::after {
        animation: none !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<!-- ── PAGE BANNER ── -->
<section class="shop-hero">
    <div class="shop-hero__in">
        <div>
            <nav class="crumbs" aria-label="Breadcrumb">
                <a href="<?php echo BASE_URL; ?>index.php">Home</a>
                <i class="fa-solid fa-chevron-right"></i>
                <span>Showroom</span>
            </nav>
            <h1>The <em>Showroom</em></h1>
        </div>
        <div class="shop-hero__stat">
            <strong><?php echo number_format($total_available); ?></strong>
            motorcars available now
        </div>
    </div>
</section>

<div class="filter-overlay" id="filterOverlay"></div>

<div class="inventory-layout">

    <!-- ══════════ FILTER SIDEBAR ══════════ -->
    <aside class="filter-sidebar" id="filterSidebar" aria-label="Filter vehicles">
        <div class="fs-head">
            <div class="fs-title">
                Refine
                <?php if ($has_filters): ?><span class="fs-count"><?php echo $active_count; ?></span><?php endif; ?>
            </div>
            <div class="filter-actions">
                <?php if ($has_filters): ?>
                <a href="<?php echo $clear_all; ?>" class="filter-clear">Clear all</a>
                <?php endif; ?>
                <button type="button" class="close-filter-btn" id="closeFilterBtn" aria-label="Close filters">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <form action="vehicles.php" method="GET" id="filterForm">
            <?php if (!empty($_GET['sort']) && is_string($_GET['sort'])): ?>
            <input type="hidden" name="sort" value="<?php echo sanitize($_GET['sort']); ?>">
            <?php endif; ?>
            <?php if (!empty($_GET['per_page']) && is_scalar($_GET['per_page'])): ?>
            <input type="hidden" name="per_page" value="<?php echo (int)$_GET['per_page']; ?>">
            <?php endif; ?>

            <div class="fs-body">

                <!-- Keyword -->
                <div class="fs-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="q" placeholder="Search make, model or year"
                        value="<?php echo sanitize($f_q); ?>" autocomplete="off">
                </div>

                <!-- Deals -->
                <label class="switch">
                    <span>Discounted only<small>Cars with a reduced price</small></span>
                    <input type="checkbox" name="deal" value="1" <?php echo $f_deal ? 'checked' : ''; ?>>
                    <span class="switch__track"></span>
                </label>

                <!-- Condition -->
                <?php if ($fac['condition']):
                    vp_group_open('Condition', count($f_condition), true); ?>
                <div class="pill-row"><?php vp_pills('condition', $fac['condition'], $f_condition); ?></div>
                <?php vp_group_close(); endif; ?>

                <!-- Price -->
                <?php vp_group_open('Price Range', ($f_min || $f_max) ? 1 : 0, true); ?>
                <div class="chip-row" id="pricePresets">
                    <button type="button" class="chip-btn" data-min="" data-max="1500000">Under 1.5M</button>
                    <button type="button" class="chip-btn" data-min="1500000" data-max="3000000">1.5 – 3M</button>
                    <button type="button" class="chip-btn" data-min="3000000" data-max="6000000">3 – 6M</button>
                    <button type="button" class="chip-btn" data-min="6000000" data-max="10000000">6 – 10M</button>
                    <button type="button" class="chip-btn" data-min="10000000" data-max="">10M +</button>
                </div>
                <?php if ($show_slider): ?>
                <div class="range" id="priceRange" data-lo="<?php echo $price_lo; ?>"
                    data-hi="<?php echo $price_hi; ?>">
                    <div class="range__track"><span class="range__fill" id="rangeFill"></span></div>
                    <input type="range" id="rMin" min="<?php echo $price_lo; ?>" max="<?php echo $price_hi; ?>"
                        step="<?php echo $price_step; ?>" value="<?php echo $slider_min; ?>" aria-label="Minimum price">
                    <input type="range" id="rMax" min="<?php echo $price_lo; ?>" max="<?php echo $price_hi; ?>"
                        step="<?php echo $price_step; ?>" value="<?php echo $slider_max; ?>" aria-label="Maximum price">
                </div>
                <div class="range__readout"><span id="rMinOut"></span><span id="rMaxOut"></span></div>
                <?php endif; ?>
                <div class="price-range">
                    <div class="money"><span>KSh</span>
                        <input type="number" name="min_price" id="minPrice" min="0" placeholder="Min"
                            value="<?php echo $f_min ?: ''; ?>">
                    </div>
                    <div class="money"><span>KSh</span>
                        <input type="number" name="max_price" id="maxPrice" min="0" placeholder="Max"
                            value="<?php echo $f_max ?: ''; ?>">
                    </div>
                </div>
                <?php vp_group_close(); ?>

                <!-- Brand -->
                <?php if ($fac['brand']):
                    vp_group_open('Brand / Make', count($f_brand), true);
                    if (count($fac['brand']) > 8): ?>
                <input type="search" class="list-search" id="brandSearch" placeholder="Find a brand…"
                    autocomplete="off">
                <?php endif; ?>
                <div class="opt-list" id="brandList">
                    <?php foreach ($fac['brand'] as $name => $cnt): $name = (string)$name; ?>
                    <label class="opt" data-name="<?php echo strtolower(sanitize($name)); ?>">
                        <input type="checkbox" name="brand[]" value="<?php echo sanitize($name); ?>"
                            <?php echo in_array($name, $f_brand, true) ? 'checked' : ''; ?>>
                        <span class="opt__box"></span>
                        <span class="opt__name"><?php echo sanitize($name); ?></span>
                        <span class="opt__count"><?php echo (int)$cnt; ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php vp_group_close(); endif; ?>

                <!-- Body type -->
                <?php if ($fac['body_type']):
                    vp_group_open('Body Type', count($f_body), true); ?>
                <div class="tile-grid">
                    <?php foreach ($fac['body_type'] as $bt => $cnt): $bt = (string)$bt; ?>
                    <label class="tile">
                        <input type="checkbox" name="body_type[]" value="<?php echo sanitize($bt); ?>"
                            <?php echo in_array($bt, $f_body, true) ? 'checked' : ''; ?>>
                        <span>
                            <i class="fa-solid <?php echo vp_body_icon($bt); ?>"></i>
                            <b><?php echo sanitize($bt); ?></b>
                            <em><?php echo (int)$cnt; ?></em>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php vp_group_close(); endif; ?>

                <!-- Year -->
                <?php vp_group_open('Year', ($f_year_min || $f_year_max) ? 1 : 0, (bool)($f_year_min || $f_year_max)); ?>
                <div class="year-range">
                    <div>
                        <span class="field-label">From</span>
                        <select name="year_min">
                            <option value="">Any</option>
                            <?php for ($y = $year_hi; $y >= $year_lo; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $f_year_min === $y ? 'selected' : ''; ?>>
                                <?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <span class="field-label">To</span>
                        <select name="year_max">
                            <option value="">Any</option>
                            <?php for ($y = $year_hi; $y >= $year_lo; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $f_year_max === $y ? 'selected' : ''; ?>>
                                <?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <?php vp_group_close(); ?>

                <!-- Mileage -->
                <?php vp_group_open('Mileage', $f_mileage ? 1 : 0, (bool)$f_mileage); ?>
                <div class="pill-row">
                    <?php foreach ([0 => 'Any', 10000 => '≤ 10,000 km', 50000 => '≤ 50,000 km', 100000 => '≤ 100,000 km', 150000 => '≤ 150,000 km'] as $mv => $ml): ?>
                    <label class="pill">
                        <input type="radio" name="mileage" value="<?php echo $mv ?: ''; ?>"
                            <?php echo ((int)$f_mileage === $mv) ? 'checked' : ''; ?>>
                        <span><?php echo $ml; ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php vp_group_close(); ?>

                <!-- Transmission -->
                <?php if ($fac['transmission']):
                    vp_group_open('Transmission', count($f_trans), (bool)$f_trans); ?>
                <div class="pill-row"><?php vp_pills('transmission', $fac['transmission'], $f_trans); ?></div>
                <?php vp_group_close(); endif; ?>

                <!-- Fuel -->
                <?php if ($fac['fuel_type']):
                    vp_group_open('Fuel Type', count($f_fuel), (bool)$f_fuel); ?>
                <div class="pill-row"><?php vp_pills('fuel_type', $fac['fuel_type'], $f_fuel); ?></div>
                <?php vp_group_close(); endif; ?>

                <!-- Drive -->
                <?php if ($fac['drive_type']):
                    vp_group_open('Drive Type', count($f_drive), (bool)$f_drive); ?>
                <div class="pill-row"><?php vp_pills('drive_type', $fac['drive_type'], $f_drive); ?></div>
                <?php vp_group_close(); endif; ?>

            </div>

            <div class="fs-foot">
                <button type="submit" class="btn-filter">
                    <i class="fa-solid fa-sliders"></i> Apply Filters
                </button>
                <?php if ($has_filters): ?>
                <a href="<?php echo $clear_all; ?>" class="btn-reset">Reset all filters</a>
                <?php endif; ?>
            </div>
        </form>
    </aside>

    <!-- ══════════ RESULTS ══════════ -->
    <main class="inventory-results">

        <div class="results-toolbar">
            <div class="rt-left">
                <button type="button" class="mobile-filter-btn" id="mobileFilterBtn">
                    <i class="fa-solid fa-sliders"></i> Filters
                    <?php if ($has_filters): ?><b><?php echo $active_count; ?></b><?php endif; ?>
                </button>
                <div class="results-count">
                    <strong><?php echo number_format($total_vehicles); ?></strong>
                    vehicle<?php echo $total_vehicles !== 1 ? 's' : ''; ?> found
                    <?php if ($total_vehicles): ?>
                    <small>Showing <?php echo $showing_from; ?>–<?php echo $showing_to; ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rt-right">
                <form method="GET" id="viewForm">
                    <?php echo vp_hidden($_GET, ['sort', 'per_page', 'page']); ?>
                    <label class="sel"><span>Sort</span>
                        <select name="sort" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort_key === 'newest' ? 'selected' : ''; ?>>Newest
                            </option>
                            <option value="price_asc" <?php echo $sort_key === 'price_asc' ? 'selected' : ''; ?>>Price:
                                Low → High</option>
                            <option value="price_desc" <?php echo $sort_key === 'price_desc' ? 'selected' : ''; ?>>
                                Price: High → Low</option>
                            <option value="year_desc" <?php echo $sort_key === 'year_desc' ? 'selected' : ''; ?>>Year:
                                Newest</option>
                            <option value="mileage_asc" <?php echo $sort_key === 'mileage_asc' ? 'selected' : ''; ?>>
                                Mileage: Lowest</option>
                        </select>
                    </label>
                    <label class="sel"><span>Show</span>
                        <select name="per_page" onchange="this.form.submit()">
                            <?php foreach ($allowed_per_page as $pp): ?>
                            <option value="<?php echo $pp; ?>" <?php echo $limit === $pp ? 'selected' : ''; ?>>
                                <?php echo $pp; ?> cars</option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </form>

                <div class="view-toggle" role="group" aria-label="Layout density">
                    <button type="button" data-view="grid" class="on" aria-label="Comfortable grid" title="Grid">
                        <i class="fa-solid fa-table-cells-large"></i></button>
                    <button type="button" data-view="compact" aria-label="Compact grid" title="Compact">
                        <i class="fa-solid fa-table-cells"></i></button>
                    <button type="button" data-view="list" aria-label="List" title="List">
                        <i class="fa-solid fa-list"></i></button>
                </div>
            </div>
        </div>

        <?php if ($has_filters): ?>
        <div class="chips-row">
            <span class="chips-row__label">Active</span>
            <?php foreach ($chips as [$label, $remove_url]): ?>
            <a href="<?php echo $remove_url; ?>" class="filter-tag" title="Remove filter">
                <?php echo sanitize($label); ?> <i class="fa-solid fa-xmark"></i>
            </a>
            <?php endforeach; ?>
            <a href="<?php echo $clear_all; ?>" class="chips-clear">Clear all</a>
        </div>
        <?php endif; ?>

        <div class="car-grid" id="carGrid">
            <?php if (empty($vehicles)): ?>
            <div class="empty-state">
                <i class="fa-regular fa-car-side"></i>
                <h3>No vehicles match your search</h3>
                <p>Try removing a filter or two to see more of the collection.</p>
                <a href="<?php echo $clear_all; ?>">Clear all filters</a>
            </div>
            <?php else: ?>
            <?php foreach ($vehicles as $idx => $car):
                $img_src = (!empty($car['image_path']) && file_exists(__DIR__ . '/' . $car['image_path']))
                    ? BASE_URL . $car['image_path']
                    : BASE_URL . 'assets/images/placeholder-car.jpg';
                $badge_class = match (strtolower((string)$car['condition'])) {
                    'new'                        => 'new',
                    'certified pre-owned', 'cpo' => 'cpo',
                    default                      => 'used'
                };
                $has_discount  = !empty($car['discount_price']);
                $display_price = $has_discount ? $car['discount_price'] : $car['price'];
                $is_fresh = !empty($car['created_at']) && strtotime($car['created_at']) > strtotime('-14 days');

                $vehicle_name = sanitize($car['year'] . ' ' . $car['brand'] . ' ' . $car['name']);
                $vehicle_raw  = $car['year'] . ' ' . $car['brand'] . ' ' . $car['name'];
                $wa_msg = urlencode("Hello {$site_name}, I am interested in the {$vehicle_raw} (Ref ID: {$car['id']}) listed for KSh " . number_format($display_price) . ". Is it still available?");
                $wa_link = "https://wa.me/{$clean_wa}?text={$wa_msg}";
            ?>
            <div class="car-card" style="--i:<?php echo min($idx, 12); ?>">
                <a href="<?php echo BASE_URL; ?>vehicle_detail.php?id=<?php echo $car['id']; ?>"
                    class="card-link-overlay" aria-label="View details for <?php echo $vehicle_name; ?>"></a>

                <div class="car-img-wrapper">
                    <span class="condition-badge <?php echo $badge_class; ?>">
                        <?php echo sanitize($car['condition']); ?>
                    </span>
                    <?php if ($has_discount):
                        $pct = round((1 - $car['discount_price'] / $car['price']) * 100);
                    ?>
                    <span class="discount-ribbon">–<?php echo $pct; ?>%</span>
                    <?php endif; ?>
                    <?php if ($is_fresh): ?><span class="tag-fresh">Just in</span><?php endif; ?>
                    <img src="<?php echo $img_src; ?>" alt="<?php echo $vehicle_name; ?>" loading="lazy">
                </div>

                <div class="car-details">
                    <div class="car-price-row">
                        <div class="car-price">KSh <?php echo number_format($display_price); ?></div>
                        <?php if ($has_discount): ?>
                        <div class="car-price-old">KSh <?php echo number_format($car['price']); ?></div>
                        <?php endif; ?>
                    </div>

                    <h3 class="car-title"><?php echo $vehicle_name; ?></h3>

                    <div class="car-specs">
                        <div class="spec-item">
                            <i class="fa-solid fa-gauge-high"></i>
                            <?php echo format_mileage($car['mileage']); ?>
                        </div>
                        <div class="spec-item">
                            <i class="fa-solid fa-gears"></i>
                            <?php echo sanitize($car['transmission']); ?>
                        </div>
                        <div class="spec-item">
                            <i class="fa-solid fa-gas-pump"></i>
                            <?php echo sanitize($car['fuel_type']); ?>
                        </div>
                        <div class="spec-item">
                            <i class="fa-solid fa-car-side"></i>
                            <?php echo sanitize($car['drive_type']); ?>
                        </div>
                    </div>

                    <div class="car-actions">
                        <a href="<?php echo BASE_URL; ?>vehicle_detail.php?id=<?php echo $car['id']; ?>"
                            class="btn-view">
                            <span class="t-full">View Details</span>
                            <span class="t-short">Details</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <a href="<?php echo $wa_link; ?>" class="btn-whatsapp" target="_blank" rel="noopener"
                            aria-label="Inquire via WhatsApp">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1):
            $q = $_GET;
            unset($q['page']);
            $base_qs = http_build_query($q);
            $base_qs = $base_qs ? $base_qs . '&' : '';
        ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="vehicles.php?<?php echo $base_qs; ?>page=<?php echo $page - 1; ?>" aria-label="Previous page">
                <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
            </a>
            <?php endif; ?>

            <?php
            // Smart pagination — show first, last, and window around current
            for ($i = 1; $i <= $total_pages; $i++):
                if ($i === 1 || $i === $total_pages || abs($i - $page) <= 1):
            ?>
            <a href="vehicles.php?<?php echo $base_qs; ?>page=<?php echo $i; ?>"
                class="<?php echo $page === $i ? 'pg-active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php
                elseif (abs($i - $page) === 2):
            ?>
            <span class="pg-ellipsis">…</span>
            <?php
                endif;
            endfor;
            ?>

            <?php if ($page < $total_pages): ?>
            <a href="vehicles.php?<?php echo $base_qs; ?>page=<?php echo $page + 1; ?>" aria-label="Next page">
                <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
            </a>
            <?php endif; ?>
        </div>
        <div class="pagination-note">Page <?php echo $page; ?> of <?php echo $total_pages; ?></div>
        <?php endif; ?>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Mobile filter drawer ── */
    const filterBtn = document.getElementById('mobileFilterBtn');
    const closeBtn = document.getElementById('closeFilterBtn');
    const sidebar = document.getElementById('filterSidebar');
    const overlay = document.getElementById('filterOverlay');

    function setDrawer(open) {
        sidebar.classList.toggle('open', open);
        overlay.classList.toggle('open', open);
        document.body.style.overflow = open ? 'hidden' : '';
    }
    if (filterBtn) filterBtn.addEventListener('click', () => setDrawer(true));
    if (closeBtn) closeBtn.addEventListener('click', () => setDrawer(false));
    if (overlay) overlay.addEventListener('click', () => setDrawer(false));
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') setDrawer(false);
    });

    /* ── Layout density: grid / compact / list (remembered) ── */
    const grid = document.getElementById('carGrid');
    const toggles = document.querySelectorAll('.view-toggle button');

    function setView(v) {
        grid.classList.remove('view-compact', 'view-list');
        if (v === 'compact') grid.classList.add('view-compact');
        if (v === 'list') grid.classList.add('view-list');
        toggles.forEach(b => b.classList.toggle('on', b.dataset.view === v));
        try {
            localStorage.setItem('lb_view', v);
        } catch (e) {}
    }
    let saved = 'grid';
    try {
        saved = localStorage.getItem('lb_view') || 'grid';
    } catch (e) {}
    setView(['grid', 'compact', 'list'].includes(saved) ? saved : 'grid');
    toggles.forEach(b => b.addEventListener('click', () => setView(b.dataset.view)));

    /* ── Brand list search ── */
    const brandSearch = document.getElementById('brandSearch');
    if (brandSearch) {
        brandSearch.addEventListener('input', () => {
            const term = brandSearch.value.trim().toLowerCase();
            document.querySelectorAll('#brandList .opt').forEach(o => {
                o.style.display = o.dataset.name.includes(term) ? '' : 'none';
            });
        });
    }

    /* ── Price slider + inputs + quick ranges ── */
    const minIn = document.getElementById('minPrice');
    const maxIn = document.getElementById('maxPrice');
    const rMin = document.getElementById('rMin');
    const rMax = document.getElementById('rMax');
    const fill = document.getElementById('rangeFill');
    const minOut = document.getElementById('rMinOut');
    const maxOut = document.getElementById('rMaxOut');
    const presets = document.querySelectorAll('#pricePresets .chip-btn');
    const fmt = new Intl.NumberFormat('en', {
        notation: 'compact',
        maximumFractionDigits: 1
    });

    function paintPresets() {
        const a = minIn.value || '',
            b = maxIn.value || '';
        presets.forEach(p => p.classList.toggle('on', p.dataset.min === a && p.dataset.max === b));
    }

    function paint() {
        if (!rMin) {
            paintPresets();
            return;
        }
        const lo = +rMin.min,
            hi = +rMin.max;
        const a = +rMin.value,
            b = +rMax.value;
        fill.style.left = ((a - lo) / (hi - lo) * 100) + '%';
        fill.style.right = (100 - (b - lo) / (hi - lo) * 100) + '%';
        minOut.textContent = 'KSh ' + fmt.format(a);
        maxOut.textContent = (b >= hi ? 'KSh ' + fmt.format(b) + '+' : 'KSh ' + fmt.format(b));
        paintPresets();
    }

    if (rMin && rMax) {
        const lo = +rMin.min,
            hi = +rMin.max;
        rMin.addEventListener('input', () => {
            if (+rMin.value > +rMax.value) rMin.value = rMax.value;
            minIn.value = (+rMin.value <= lo) ? '' : rMin.value;
            paint();
        });
        rMax.addEventListener('input', () => {
            if (+rMax.value < +rMin.value) rMax.value = rMin.value;
            maxIn.value = (+rMax.value >= hi) ? '' : rMax.value;
            paint();
        });
        [minIn, maxIn].forEach(inp => inp.addEventListener('input', () => {
            const a = minIn.value === '' ? lo : Math.max(lo, Math.min(+minIn.value, hi));
            const b = maxIn.value === '' ? hi : Math.max(lo, Math.min(+maxIn.value, hi));
            rMin.value = Math.min(a, b);
            rMax.value = Math.max(a, b);
            paint();
        }));
    } else {
        [minIn, maxIn].forEach(inp => inp.addEventListener('input', paintPresets));
    }

    presets.forEach(p => p.addEventListener('click', () => {
        const same = p.classList.contains('on');
        minIn.value = same ? '' : p.dataset.min;
        maxIn.value = same ? '' : p.dataset.max;
        if (rMin) {
            const lo = +rMin.min,
                hi = +rMin.max;
            rMin.value = minIn.value === '' ? lo : Math.max(lo, Math.min(+minIn.value, hi));
            rMax.value = maxIn.value === '' ? hi : Math.max(lo, Math.min(+maxIn.value, hi));
        }
        paint();
    }));

    paint();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>