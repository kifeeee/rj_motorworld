<?php
// about.php
require_once __DIR__ . '/includes/header.php';

// Fetch dynamic site settings if needed
$co_name = get_site_setting($pdo, 'company_name', 'RJ Motorworld');
?>

<style>
.about-hero {
    background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/images/about-hero-bg.jpg') center/cover;
    padding: 80px 5%;
    color: white;
    text-align: center;
}

.about-hero h1 {
    margin: 0 0 15px 0;
    font-size: 42px;
    font-weight: 800;
    letter-spacing: -1px;
}

.about-hero p {
    color: #cbd5e1;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
    font-size: 18px;
}

.about-container {
    max-width: 1200px;
    margin: 60px auto;
    padding: 0 5%;
}

/* Two-column layout for the main story */
.story-section {
    display: flex;
    gap: 50px;
    align-items: center;
    margin-bottom: 80px;
}

.story-content {
    flex: 1;
}

.story-content h2 {
    color: var(--primary);
    font-size: 32px;
    margin-top: 0;
    margin-bottom: 20px;
}

.story-content p {
    color: #475569;
    line-height: 1.8;
    font-size: 16px;
    margin-bottom: 20px;
}

.story-image {
    flex: 1;
    background: #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.story-image img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
}

/* Core Values Grid */
.values-section {
    text-align: center;
    background: white;
    padding: 60px 5%;
    border-radius: 8px;
    border: 1px solid var(--border);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}

.values-section h2 {
    color: var(--primary);
    font-size: 32px;
    margin-top: 0;
    margin-bottom: 40px;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.value-card {
    padding: 20px;
}

.value-icon {
    font-size: 40px;
    margin-bottom: 15px;
}

.value-card h3 {
    color: var(--primary);
    margin: 0 0 10px 0;
    font-size: 20px;
}

.value-card p {
    color: #64748b;
    line-height: 1.6;
    margin: 0;
    font-size: 15px;
}

/* Call to Action Banner */
.cta-banner {
    background: var(--primary);
    color: white;
    text-align: center;
    padding: 50px 5%;
    margin-top: 80px;
    border-radius: 8px;
}

.cta-banner h2 {
    margin: 0 0 15px 0;
    font-size: 28px;
}

.cta-banner p {
    color: #cbd5e1;
    margin-bottom: 30px;
    font-size: 16px;
}

.btn-cta {
    background: var(--secondary);
    color: var(--primary);
    padding: 14px 28px;
    font-size: 16px;
    font-weight: 700;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    transition: background 0.2s;
}

.btn-cta:hover {
    background: #0ea5e9;
    color: white;
}

@media (max-width: 992px) {
    .story-section {
        flex-direction: column;
    }
}
</style>

<div class="about-hero">
    <h1>About <?php echo sanitize($co_name); ?></h1>
    <p>Driven by passion, defined by quality, and dedicated to providing an unparalleled automotive experience.</p>
</div>

<div class="about-container">
    <!-- Our Story Section -->
    <div class="story-section">
        <div class="story-content">
            <h2>Our Journey</h2>
            <p>Founded on the belief that buying a car should be exciting and transparent,
                <strong><?php echo sanitize($co_name); ?></strong> has grown into a premier automotive destination. We
                started with a simple mission: to connect drivers with vehicles they truly love, without the traditional
                dealership friction.
            </p>
            <p>Over the years, we have built a carefully curated fleet of high-quality new and certified pre-owned
                vehicles. Every car on our lot undergoes a rigorous inspection process to ensure it meets our strict
                standards for safety, performance, and reliability.</p>
            <p>Whether you are looking for a fuel-efficient commuter, a spacious family SUV, or a high-performance
                sports car, our experienced team is here to guide you every step of the way.</p>
        </div>
        <div class="story-image">
            <!-- Ensure you place a nice dealership or showroom image in your assets folder -->
            <img src="<?php echo BASE_URL; ?>assets/images/showroom-about.jpg"
                alt="<?php echo sanitize($co_name); ?> Dealership Showroom"
                onerror="this.src='<?php echo BASE_URL; ?>assets/images/placeholder-car.jpg'">
        </div>
    </div>

    <!-- Core Values Section -->
    <div class="values-section">
        <h2>What Drives Us</h2>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">🤝</div>
                <h3>Radical Transparency</h3>
                <p>No hidden fees, no high-pressure tactics, and no surprises. We provide upfront pricing and
                    comprehensive vehicle histories.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🏆</div>
                <h3>Uncompromising Quality</h3>
                <p>We hand-select our inventory. If a vehicle doesn't pass our exhaustive multi-point inspection, it
                    doesn't make it to our showroom.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">⭐</div>
                <h3>Customer First</h3>
                <p>Your satisfaction is our primary metric of success. We are dedicated to building long-term
                    relationships, not just making a quick sale.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🔧</div>
                <h3>Expert Support</h3>
                <p>Our knowledgeable sales and financing staff are industry experts, ready to help you navigate your
                    purchase with confidence.</p>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="cta-banner">
        <h2>Ready to Find Your Next Vehicle?</h2>
        <p>Explore our premium inventory online, or visit our showroom to experience the difference for yourself.</p>
        <a href="<?php echo BASE_URL; ?>vehicles.php" class="btn-cta">Browse Our Inventory</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>