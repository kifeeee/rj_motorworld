<?php
// terms.php
require_once __DIR__ . '/includes/header.php';

// Fetch dynamic contact info for the terms
$co_email = get_site_setting($pdo, 'company_email', 'legal@rjmotorworld.com');
$co_phone = get_site_setting($pdo, 'company_phone', '(555) 123-4567');
$co_address = get_site_setting($pdo, 'office_address', '123 Dealer Drive, Motor City, MI');
?>

<style>
/* ──────────────────────────────────────────
   LEGAL PAGES (TERMS)  —  Luxury Layout
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

.legal-hero {
    background: var(--obsidian);
    color: var(--white);
    padding: 100px 5%;
    text-align: center;
    position: relative;
    border-bottom: 2px solid var(--gold-deep);
}

.legal-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
    opacity: 0.03;
    pointer-events: none;
}

.legal-hero-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--gold-mid);
    margin-bottom: 16px;
    position: relative;
    z-index: 2;
}

.legal-hero h1 {
    font-family: 'Playfair Display', serif;
    margin: 0 0 16px 0;
    font-size: clamp(36px, 5vw, 56px);
    font-weight: 500;
    letter-spacing: -0.01em;
    position: relative;
    z-index: 2;
}

.legal-hero p {
    color: rgba(255, 255, 255, 0.65);
    font-size: 14px;
    font-weight: 300;
    letter-spacing: 0.05em;
    position: relative;
    z-index: 2;
}

.legal-wrapper {
    max-width: 850px;
    margin: 0 auto;
    padding: 80px 5% 100px;
}

.legal-content {
    background: #fff;
    padding: 60px;
    border-radius: 2px;
    border: 1px solid var(--border);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.02);
}

.legal-content h2 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    color: var(--ink);
    font-weight: 500;
    margin: 40px 0 20px 0;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border);
}

.legal-content h2:first-child {
    margin-top: 0;
}

.legal-content h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 18px;
    color: var(--ink);
    font-weight: 600;
    margin: 30px 0 15px 0;
}

.legal-content p {
    font-size: 15.5px;
    line-height: 1.8;
    color: var(--dim);
    font-weight: 300;
    margin-bottom: 20px;
}

.legal-content ul {
    margin-bottom: 24px;
    padding-left: 20px;
}

.legal-content li {
    font-size: 15.5px;
    line-height: 1.8;
    color: var(--dim);
    font-weight: 300;
    margin-bottom: 10px;
}

.legal-content li::marker {
    color: var(--gold-deep);
}

.legal-content a {
    color: var(--gold-deep);
    text-decoration: none;
    transition: color 0.2s;
    font-weight: 500;
}

.legal-content a:hover {
    color: var(--gold);
    text-decoration: underline;
}

.contact-box {
    background: var(--ivory);
    padding: 30px;
    border-left: 3px solid var(--gold);
    margin-top: 40px;
}

.contact-box h4 {
    margin: 0 0 15px 0;
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    color: var(--ink);
}

.contact-box p {
    margin-bottom: 8px;
    font-size: 14px;
}

.contact-box p:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .legal-content {
        padding: 40px 25px;
    }

    .legal-hero {
        padding: 80px 5% 60px;
    }

    .legal-content h2 {
        font-size: 24px;
    }
}
</style>

<div class="legal-hero">
    <div class="legal-hero-eyebrow">Legal Agreement</div>
    <h1>Terms of Service</h1>
    <p>LAST UPDATED: JUNE 1, 2026</p>
</div>

<div class="legal-wrapper">
    <div class="legal-content">

        <h2>1. Agreement to Terms</h2>
        <p>By accessing, browsing, or using the <strong>RJ Motorworld</strong> website and our associated digital
            platforms, you agree to be bound by these Terms of Service. If you do not agree with any part of these
            terms, you must not access or use our website. These terms apply to all visitors, users, clients, and others
            who access the service.</p>

        <h2>2. Vehicle Inventory and Pricing</h2>
        <p>Our commitment is to provide accurate, up-to-date information regarding our premium vehicle inventory.
            However, please note the following:</p>
        <ul>
            <li><strong>Pricing Adjustments:</strong> All vehicle prices, specifications, and availability are subject
                to change without prior notice. Listed prices typically exclude taxes, title, registration, and
                documentary fees.</li>
            <li><strong>Typographical Errors:</strong> In the event a vehicle is listed at an incorrect price due to a
                typographical error or an error in pricing information received from our suppliers, RJ Motorworld
                reserves the right to refuse or cancel any orders placed for the vehicle listed at the incorrect price.
            </li>
            <li><strong>Availability:</strong> Inventory is dynamic. A vehicle shown online may have recently been sold.
                We recommend contacting our concierge team directly to verify the availability of a specific masterpiece
                before visiting our physical showroom.</li>
        </ul>

        <h2>3. Trade-In Appraisals and Financing</h2>
        <p>Features on our site allowing for trade-in valuations and financing pre-approvals are provided as estimating
            tools.</p>
        <ul>
            <li><strong>Valuations:</strong> Any trade-in value generated online is a preliminary estimate based on the
                data you provide. A final, binding offer will only be made after a physical, 150-point inspection by our
                certified appraisers at the dealership.</li>
            <li><strong>Financing:</strong> Submitting a financing application does not guarantee loan approval. Rates
                and terms are ultimately determined by our network of third-party lenders based on your complete
                financial profile and credit history.</li>
        </ul>

        <h2>4. User Conduct</h2>
        <p>When interacting with the RJ Motorworld platform, you agree not to:</p>
        <ul>
            <li>Provide false, deceptive, or misleading personal or financial information.</li>
            <li>Attempt to bypass, compromise, or hack any security protocols, CAPTCHAs, or authentication measures.
            </li>
            <li>Utilize automated scripts, spiders, or scrapers to extract data, vehicle images, or pricing structures
                from our website.</li>
        </ul>

        <h2>5. Intellectual Property</h2>
        <p>All content presented on this website—including but not limited to dealership branding, vehicle photography,
            text, graphics, logos, and software—is the exclusive property of RJ Motorworld or its content suppliers and
            is protected by intellectual property laws. Unauthorized reproduction, distribution, or commercial
            exploitation of this content is strictly prohibited.</p>

        <h2>6. Disclaimer of Warranties</h2>
        <p>The RJ Motorworld website is provided on an "AS IS" and "AS AVAILABLE" basis. While we strive for excellence,
            we make no representations or warranties of any kind, express or implied, as to the operation of our site or
            the accuracy of the information, content, or materials included.</p>
        <p><em>Note: This disclaimer applies solely to the website. Vehicles purchased from RJ Motorworld carry their
                own specific manufacturer or dealership warranties, which will be provided in your official purchase
                agreement.</em></p>

        <h2>7. Limitation of Liability</h2>
        <p>To the fullest extent permitted by applicable law, RJ Motorworld shall not be liable for any indirect,
            incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether
            incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses, resulting
            from your access to or use of our digital platforms.</p>

        <h2>8. Governing Law</h2>
        <p>These Terms shall be governed and construed in accordance with the laws of the jurisdiction in which RJ
            Motorworld is headquartered, without regard to its conflict of law provisions.</p>

        <div class="contact-box">
            <h4>Questions About Our Terms?</h4>
            <p>If you require clarification on any of the stipulations outlined above, please direct your inquiries to
                our administrative and legal team:</p>
            <p><strong>Email:</strong> <a
                    href="mailto:<?php echo sanitize($co_email); ?>"><?php echo sanitize($co_email); ?></a></p>
            <p><strong>Phone:</strong> <?php echo sanitize($co_phone); ?></p>
            <p><strong>Address:</strong> <?php echo sanitize($co_address); ?></p>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>