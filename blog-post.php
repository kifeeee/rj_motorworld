<?php
// blog-post.php
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;

try {
    // Attempt to fetch dynamic post from the 'blog_posts' table
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = :id AND status = 'published' LIMIT 1");
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch();
} catch (Exception $e) {
    // Silently catch missing table errors and proceed to fallback
}

// Fallback static content to ensure the page works beautifully out of the box
if (!$post) {
    $fallback_posts = [
        1 => [
            'title' => 'The Future of Luxury SUVs: What to Expect This Year',
            'category' => 'Buying Guide',
            'created_at' => '2026-05-24',
            'image_path' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=1600&q=80',
            'content' => "
                <p>The luxury SUV segment is undergoing a massive transformation. As we navigate through 2026, manufacturers are pushing the boundaries of what these vehicles can achieve, blending track-ready performance with palatial comfort and cutting-edge autonomous features.</p>
                <h2>The Rise of the 'Hyper-SUV'</h2>
                <p>Gone are the days when choosing an SUV meant compromising on driving dynamics. The newest iterations from top-tier brands feature active roll stabilization, rear-wheel steering, and hybrid powertrains that deliver instant torque. These vehicles defy physics, handling corners with the agility of a sports coupe while weighing upwards of 5,000 pounds.</p>
                <blockquote>\"Modern luxury is no longer just about leather and wood; it's about seamless technological integration and uncompromised capability.\"</blockquote>
                <h2>Sanctuary Cabins</h2>
                <p>Interior design has shifted towards creating a mobile sanctuary. Expect to see widespread adoption of active noise cancellation embedded into the headrests, biometric climate control that adjusts based on passenger body temperature, and augmented reality head-up displays that project navigation paths directly onto the road ahead.</p>
                <p>At RJ Motorworld, we are actively curating our showroom to include these next-generation masterpieces. Whether you are looking for a hybrid daily driver or a V8-powered weekend cruiser, the future of the SUV is already here.</p>
            "
        ],
        2 => [
            'title' => 'How to Structure the Perfect Finance Deal',
            'category' => 'Finance',
            'created_at' => '2026-05-18',
            'image_path' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=1600&q=80',
            'content' => "
                <p>Navigating the financial aspects of acquiring a luxury or premium vehicle can seem daunting, but it doesn't have to be. Structuring the perfect finance deal is about aligning the loan terms with your personal cash flow and the vehicle's depreciation curve.</p>
                <h2>Understanding the Variables</h2>
                <p>Your monthly payment is dictated by three primary factors: the loan principal (purchase price minus down payment and trade-in), the interest rate (APR), and the loan term (duration). While it might be tempting to stretch a loan to 84 months to achieve a lower monthly payment, you end up paying significantly more in interest and run the risk of negative equity.</p>
                <h2>The 20/4/10 Rule</h2>
                <p>A classic, conservative rule of thumb in automotive financing is the 20/4/10 rule:</p>
                <ul>
                    <li>Put down at least <strong>20%</strong> of the purchase price.</li>
                    <li>Finance the vehicle for no more than <strong>4 years</strong> (48 months).</li>
                    <li>Ensure your total automotive expenses (loan, insurance, fuel) do not exceed <strong>10%</strong> of your gross monthly income.</li>
                </ul>
                <p>While premium vehicle purchases often stretch these traditional rules, they remain excellent benchmarks. Our in-house financial advisors at RJ Motorworld specialize in bespoke lending solutions, offering flexible terms that respect your financial architecture.</p>
            "
        ],
        3 => [
            'title' => '5 Essential Maintenance Checks for Pre-Owned Vehicles',
            'category' => 'Maintenance',
            'created_at' => '2026-05-12',
            'image_path' => 'https://images.unsplash.com/photo-1632733711679-529326f6db12?w=1600&q=80',
            'content' => "
                <p>Acquiring a certified pre-owned vehicle is a brilliant way to experience luxury automotive engineering without bearing the brunt of initial depreciation. However, maintaining that engineering requires diligence.</p>
                <h2>1. Fluid Health and Levels</h2>
                <p>Oil is the lifeblood of any internal combustion engine. Check not just the level, but the color and consistency. Transmission fluid, brake fluid, and coolant should also be inspected. If you've purchased from RJ Motorworld, these have already been refreshed during our 150-point inspection.</p>
                <h2>2. Tire Tread and Wear Patterns</h2>
                <p>Uneven tire wear is a massive indicator of underlying issues, usually pointing to poor alignment or failing suspension components. High-performance vehicles require precise alignment to handle correctly and safely.</p>
                <h2>3. Brake Pad and Rotor Thickness</h2>
                <p>Listen for squeaking or grinding. Premium vehicles often use high-carbon or ceramic composite brakes which have unique wear characteristics. Ensure there is ample pad life remaining.</p>
                <h2>4. Battery Health</h2>
                <p>Modern luxury vehicles are heavily dependent on electrical systems. A weak battery can cause a cascade of electronic gremlins that mimic much more severe mechanical failures.</p>
                <h2>5. Cabin and Engine Air Filters</h2>
                <p>Often overlooked, these filters protect both the engine's internals and your lungs. Replacing them is inexpensive and dramatically improves both engine efficiency and interior air quality.</p>
            "
        ]
    ];

    if (isset($fallback_posts[$id])) {
        $post = $fallback_posts[$id];
        $post['is_fallback'] = true;
    }
}

if (!$post) {
    echo "<div style='padding: 120px 5%; text-align: center; font-family: \"Outfit\", sans-serif; background-color: var(--ivory); min-height: 60vh;'>
            <h2 style='font-family: \"Playfair Display\", serif; font-size: 36px; margin-bottom: 20px; color: var(--ink);'>Article not found.</h2>
            <a href='blog.php' style='display: inline-block; background: var(--gold); color: var(--obsidian); padding: 12px 30px; text-transform: uppercase; font-weight: 600; font-size: 13px; letter-spacing: 0.1em; border-radius: 2px; text-decoration: none;'>Return to Journal</a>
          </div>";
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Format data depending on whether it came from DB or fallback
$title = sanitize($post['title']);
$category = sanitize($post['category']);
$date = date('F d, Y', strtotime($post['created_at']));
$content = isset($post['is_fallback']) ? $post['content'] : nl2br(sanitize($post['content']));

$img_src = '';
if (!empty($post['image_path'])) {
    if (filter_var($post['image_path'], FILTER_VALIDATE_URL)) {
        $img_src = $post['image_path']; // Direct URL (from fallback)
    } elseif (file_exists(__DIR__ . '/' . $post['image_path'])) {
        $img_src = BASE_URL . $post['image_path']; // Local file
    }
}
if (empty($img_src)) {
    $img_src = 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=1600&q=80';
}
?>

<style>
/* ──────────────────────────────────────────
   ARTICLE PAGE  —  Luxury Layout
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
    font-family: 'Outfit', sans-serif;
}

/* ── ARTICLE HERO ── */
.article-hero {
    position: relative;
    height: 60vh;
    min-height: 450px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
    text-align: center;
    padding: 60px 5%;
    background-color: var(--obsidian);
}

.article-hero-bg {
    position: absolute;
    inset: 0;
    z-index: 1;
}

.article-hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.4;
}

.article-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(10, 10, 14, 1) 0%, rgba(10, 10, 14, 0.2) 100%);
    z-index: 2;
}

.article-hero-content {
    position: relative;
    z-index: 3;
    max-width: 900px;
    width: 100%;
}

.article-meta {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--gold-mid);
    margin-bottom: 20px;
}

.article-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(32px, 5vw, 56px);
    color: var(--white);
    font-weight: 500;
    line-height: 1.15;
    margin: 0 0 20px 0;
    letter-spacing: -0.01em;
}

/* ── ARTICLE BODY ── */
.article-body-wrapper {
    max-width: 800px;
    margin: 0 auto;
    padding: 80px 5% 100px;
}

.article-content {
    font-family: 'Outfit', sans-serif;
    font-size: 17px;
    line-height: 1.85;
    color: var(--ink);
    font-weight: 300;
}

.article-content h2 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 500;
    color: var(--obsidian);
    margin: 50px 0 20px 0;
    line-height: 1.2;
}

.article-content h3 {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 500;
    color: var(--obsidian);
    margin: 40px 0 15px 0;
}

.article-content p {
    margin: 0 0 24px 0;
}

/* Drop cap for first paragraph */
.article-content>p:first-of-type::first-letter {
    font-family: 'Playfair Display', serif;
    font-size: 80px;
    line-height: 0.8;
    float: left;
    margin: 8px 12px 0 0;
    color: var(--gold-deep);
    font-weight: 400;
}

.article-content blockquote {
    margin: 40px 0;
    padding: 30px 40px;
    background: #fff;
    border-left: 4px solid var(--gold);
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-style: italic;
    line-height: 1.5;
    color: var(--ink);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
}

.article-content ul,
.article-content ol {
    margin: 0 0 24px 0;
    padding-left: 20px;
}

.article-content li {
    margin-bottom: 10px;
    padding-left: 10px;
}

.article-content li::marker {
    color: var(--gold-deep);
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 2px;
    margin: 40px 0;
    border: 1px solid var(--border);
}

/* ── ARTICLE FOOTER & NAVIGATION ── */
.article-footer {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--obsidian);
    text-decoration: none;
    transition: color 0.2s;
}

.back-link i {
    color: var(--gold-deep);
    transition: transform 0.2s var(--ease);
}

.back-link:hover {
    color: var(--gold-deep);
}

.back-link:hover i {
    transform: translateX(-4px);
}

.share-links {
    display: flex;
    gap: 15px;
    align-items: center;
}

.share-links span {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--mist);
}

.share-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink);
    text-decoration: none;
    transition: all 0.2s var(--ease);
}

.share-btn:hover {
    background: var(--gold);
    color: var(--white);
    border-color: var(--gold);
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .article-hero {
        height: auto;
        padding: 120px 5% 60px;
    }

    .article-content>p:first-of-type::first-letter {
        font-size: 60px;
        margin-top: 6px;
    }

    .article-content blockquote {
        padding: 20px 25px;
        font-size: 20px;
    }

    .article-footer {
        flex-direction: column;
        gap: 30px;
        text-align: center;
    }
}
</style>

<article>
    <header class="article-hero">
        <div class="article-hero-bg">
            <img src="<?php echo $img_src; ?>" alt="Article cover image">
        </div>
        <div class="article-hero-overlay"></div>
        <div class="article-hero-content">
            <div class="article-meta"><?php echo $date; ?> &bull; <?php echo $category; ?></div>
            <h1 class="article-title"><?php echo $title; ?></h1>
        </div>
    </header>

    <div class="article-body-wrapper">
        <div class="article-content">
            <?php echo $content; ?>
        </div>

        <footer class="article-footer">
            <a href="<?php echo BASE_URL; ?>blog.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Journal
            </a>

            <div class="share-links">
                <span>Share</span>
                <a href="#" class="share-btn"
                    onclick="window.open('https://twitter.com/intent/tweet?text='+encodeURIComponent(document.title)+'&url='+encodeURIComponent(window.location.href), '_blank'); return false;"
                    aria-label="Share on Twitter">
                    <i class="fa-brands fa-x-twitter"></i>
                </a>
                <a href="#" class="share-btn"
                    onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(window.location.href), '_blank'); return false;"
                    aria-label="Share on Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="#" class="share-btn"
                    onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!'); return false;"
                    aria-label="Copy Link">
                    <i class="fa-solid fa-link"></i>
                </a>
            </div>
        </footer>
    </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>