<?php
// blog.php
require_once __DIR__ . '/includes/header.php';

$posts = [];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;
$total_pages = 1;

try {
    // Attempt to fetch dynamic posts from a 'blog_posts' table
    // Assuming columns: id, title, excerpt, image_path, category, created_at, status
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
    if ($count_stmt) {
        $total_posts = $count_stmt->fetchColumn();
        $total_pages = ceil($total_posts / $limit);
        
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();
    }
} catch (Exception $e) {
    // Table might not exist yet; we'll catch the error silently and let the fallback UI display the demo content.
    $posts = [];
}
?>

<style>
/* ──────────────────────────────────────────
   BLOG / JOURNAL PAGE  —  Luxury Layout
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

/* ── HERO SECTION ── */
.blog-hero {
    background: var(--obsidian);
    color: var(--white);
    padding: 100px 5%;
    text-align: center;
    position: relative;
    border-bottom: 2px solid var(--gold-deep);
}

.blog-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
    opacity: 0.03;
    pointer-events: none;
}

.blog-hero-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--gold-mid);
    margin-bottom: 16px;
    position: relative;
    z-index: 2;
}

.blog-hero h1 {
    font-family: 'Playfair Display', serif;
    margin: 0 0 16px 0;
    font-size: clamp(36px, 5vw, 56px);
    font-weight: 500;
    letter-spacing: -0.01em;
    position: relative;
    z-index: 2;
}

.blog-hero h1 em {
    font-style: italic;
    color: var(--gold-light);
}

.blog-hero p {
    color: rgba(255, 255, 255, 0.65);
    max-width: 650px;
    margin: 0 auto;
    line-height: 1.7;
    font-size: 16px;
    font-weight: 300;
    position: relative;
    z-index: 2;
}

/* ── LAYOUT & GRID ── */
.blog-layout {
    max-width: 1300px;
    margin: 0 auto;
    padding: 80px 5% 100px;
}

.blog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
}

/* ── BLOG CARD ── */
.blog-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 2px;
    transition: transform 0.35s var(--ease), box-shadow 0.35s var(--ease);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
}

.blog-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--gold-deep), var(--gold-light), var(--gold-deep));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s var(--ease);
    z-index: 3;
}

.blog-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.06);
}

.blog-card:hover::after {
    transform: scaleX(1);
}

.blog-img {
    height: 240px;
    overflow: hidden;
    background: var(--ivory);
    position: relative;
}

.blog-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s var(--ease), filter 0.7s var(--ease);
}

.blog-card:hover .blog-img img {
    transform: scale(1.05);
    filter: brightness(1.05);
}

/* Overlay gradient on image */
.blog-img::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40%;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.15), transparent);
    z-index: 1;
    pointer-events: none;
}

.blog-content {
    padding: 30px 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
    position: relative;
    z-index: 2;
}

.blog-meta {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--gold-deep);
    margin-bottom: 14px;
}

.blog-title {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 500;
    color: var(--ink);
    line-height: 1.35;
    margin: 0 0 16px 0;
    letter-spacing: -0.01em;
}

.blog-excerpt {
    font-size: 14px;
    font-weight: 300;
    color: var(--dim);
    line-height: 1.7;
    margin: 0 0 24px 0;
    flex: 1;
}

.blog-link {
    margin-top: auto;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--ink);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: gap 0.2s, color 0.2s;
    text-decoration: none;
}

.blog-link i {
    color: var(--gold-deep);
    transition: transform 0.2s var(--ease);
}

.blog-link:hover {
    gap: 14px;
    color: var(--gold-deep);
}

/* ── PAGINATION ── */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: 60px;
    flex-wrap: wrap;
}

.pagination a,
.pagination span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 10px;
    border: 1px solid var(--border);
    border-radius: 2px;
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    background: #fff;
    transition: all 0.2s var(--ease);
    text-decoration: none;
}

.pagination a:hover {
    border-color: var(--gold);
    color: var(--gold-deep);
}

.pagination .pg-active {
    background: var(--ink);
    color: #fff;
    border-color: var(--ink);
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .blog-layout {
        padding: 50px 5% 70px;
    }

    .blog-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="blog-hero">
    <div class="blog-hero-eyebrow">Insights & News</div>
    <h1>RJ Motorworld <em>Journal</em></h1>
    <p>Stay updated with the latest automotive trends, dealership news, expert buying guides, and insider knowledge from
        our master technicians.</p>
</section>

<div class="blog-layout">
    <div class="blog-grid">

        <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): 
                $img_src = (!empty($post['image_path']) && file_exists(__DIR__ . '/' . $post['image_path']))
                    ? BASE_URL . $post['image_path']
                    : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=800&q=80'; // Fallback image
            ?>
        <article class="blog-card">
            <a href="<?php echo BASE_URL; ?>blog-post.php?id=<?php echo $post['id']; ?>"
                style="position: absolute; inset: 0; z-index: 5;"
                aria-label="Read <?php echo sanitize($post['title']); ?>"></a>
            <div class="blog-img">
                <img src="<?php echo $img_src; ?>" alt="<?php echo sanitize($post['title']); ?>" loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">
                    <?php echo date('M d, Y', strtotime($post['created_at'])); ?> &bull;
                    <?php echo sanitize($post['category']); ?>
                </div>
                <h3 class="blog-title"><?php echo sanitize($post['title']); ?></h3>
                <p class="blog-excerpt">
                    <?php 
                            // Truncate excerpt if it's too long
                            $excerpt = sanitize($post['excerpt']);
                            echo strlen($excerpt) > 120 ? substr($excerpt, 0, 120) . '...' : $excerpt;
                        ?>
                </p>
                <div class="blog-link">Read Article <i class="fa-solid fa-arrow-right"></i></div>
            </div>
        </article>
        <?php endforeach; ?>

        <?php else: ?>
        <article class="blog-card">
            <a href="<?php echo BASE_URL; ?>blog-post.php?id=1" style="position: absolute; inset: 0; z-index: 5;"></a>
            <div class="blog-img">
                <img src="https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=800&q=80"
                    alt="Future of Luxury SUVs" loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">May 24, 2026 &bull; Buying Guide</div>
                <h3 class="blog-title">The Future of Luxury SUVs: What to Expect This Year</h3>
                <p class="blog-excerpt">Explore the latest advancements in comfort, technology, and performance shaping
                    the next generation of premium sport utility vehicles at RJ Motorworld.</p>
                <div class="blog-link">Read Article <i class="fa-solid fa-arrow-right"></i></div>
            </div>
        </article>

        <article class="blog-card">
            <a href="<?php echo BASE_URL; ?>blog-post.php?id=2" style="position: absolute; inset: 0; z-index: 5;"></a>
            <div class="blog-img">
                <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=800&q=80" alt="Car Financing"
                    loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">May 18, 2026 &bull; Finance</div>
                <h3 class="blog-title">How to Structure the Perfect Finance Deal</h3>
                <p class="blog-excerpt">Navigating auto loans doesn't have to be complex. Our in-house financial experts
                    break down how to secure the best rates and tailored terms.</p>
                <div class="blog-link">Read Article <i class="fa-solid fa-arrow-right"></i></div>
            </div>
        </article>

        <article class="blog-card">
            <a href="<?php echo BASE_URL; ?>blog-post.php?id=3" style="position: absolute; inset: 0; z-index: 5;"></a>
            <div class="blog-img">
                <img src="https://images.unsplash.com/photo-1632733711679-529326f6db12?w=800&q=80"
                    alt="Vehicle Maintenance" loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">May 12, 2026 &bull; Maintenance</div>
                <h3 class="blog-title">5 Essential Maintenance Checks for Pre-Owned Vehicles</h3>
                <p class="blog-excerpt">Maintain the value and longevity of your investment with these simple yet
                    critical checks recommended by our 150-point certified technicians.</p>
                <div class="blog-link">Read Article <i class="fa-solid fa-arrow-right"></i></div>
            </div>
        </article>

        <article class="blog-card">
            <a href="<?php echo BASE_URL; ?>blog-post.php?id=4" style="position: absolute; inset: 0; z-index: 5;"></a>
            <div class="blog-img">
                <img src="https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=800&q=80"
                    alt="Electric Vehicles" loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">May 05, 2026 &bull; EV Transition</div>
                <h3 class="blog-title">Transitioning to Electric: The Real-World Range of Modern EVs</h3>
                <p class="blog-excerpt">Range anxiety is becoming a thing of the past. We take a look at the actual
                    driving range and charging infrastructure available today.</p>
                <div class="blog-link">Read Article <i class="fa-solid fa-arrow-right"></i></div>
            </div>
        </article>

        <article class="blog-card">
            <a href="<?php echo BASE_URL; ?>blog-post.php?id=5" style="position: absolute; inset: 0; z-index: 5;"></a>
            <div class="blog-img">
                <img src="https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800&q=80"
                    alt="Vehicle Trade-In" loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">April 28, 2026 &bull; Selling</div>
                <h3 class="blog-title">Maximizing Your Trade-In Value Before Visiting the Dealership</h3>
                <p class="blog-excerpt">Simple tips and tricks you can perform at home to ensure you get top dollar when
                    appraising your current vehicle with us.</p>
                <div class="blog-link">Read Article <i class="fa-solid fa-arrow-right"></i></div>
            </div>
        </article>

        <article class="blog-card">
            <a href="<?php echo BASE_URL; ?>blog-post.php?id=6" style="position: absolute; inset: 0; z-index: 5;"></a>
            <div class="blog-img">
                <img src="https://images.unsplash.com/photo-1542362567-b07e54358753?w=800&q=80" alt="Sports Cars"
                    loading="lazy">
            </div>
            <div class="blog-content">
                <div class="blog-meta">April 15, 2026 &bull; Performance</div>
                <h3 class="blog-title">The Return of the Driver's Car: Manual Transmissions in 2026</h3>
                <p class="blog-excerpt">Despite the automation trend, manufacturers are keeping the enthusiast spirit
                    alive. Explore the best manual transmission vehicles in our fleet.</p>
                <div class="blog-link">Read Article <i class="fa-solid fa-arrow-right"></i></div>
            </div>
        </article>
        <?php endif; ?>

    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="blog.php?page=<?php echo $page-1; ?>">
            <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
        </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="blog.php?page=<?php echo $i; ?>" class="<?php echo $page === $i ? 'pg-active' : ''; ?>">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
        <a href="blog.php?page=<?php echo $page+1; ?>">
            <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>