<?php
// privacy_policy.php
require_once __DIR__ . '/includes/header.php';

// Fetch dynamic contact info for the policy
$co_email = get_site_setting($pdo, 'company_email', 'privacy@rjmotorworld.com');
$co_phone = get_site_setting($pdo, 'company_phone', '(555) 123-4567');
$co_address = get_site_setting($pdo, 'office_address', '123 Dealer Drive, Motor City, MI');
?>

<style>
/* ──────────────────────────────────────────
   LEGAL PAGES (PRIVACY)  —  Luxury Layout
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
    <div class="legal-hero-eyebrow">Commitment to Security</div>
    <h1>Privacy Policy</h1>
    <p>LAST UPDATED: JUNE 1, 2026</p>
</div>

<div class="legal-wrapper">
    <div class="legal-content">

        <h2>1. Introduction</h2>
        <p>At <strong>RJ Motorworld</strong> ("we," "our," or "us"), we recognize that your privacy is of the utmost
            importance. This Privacy Policy describes how we collect, use, process, and disclose your personal
            information, in conjunction with your access to and use of our premium automotive website and physical
            dealership services.</p>
        <p>By using our website, submitting inquiries, applying for financing, or interacting with our digital showroom,
            you agree to the collection and use of information in accordance with this policy.</p>

        <h2>2. Information We Collect</h2>
        <p>We collect information to provide a bespoke, highly tailored automotive experience. The types of data we
            gather include:</p>

        <h3>Personal Identification Information</h3>
        <ul>
            <li>Name, email address, physical address, and telephone number.</li>
            <li>Government-issued identification (e.g., driver's license) when booking test drives or purchasing a
                vehicle.</li>
        </ul>

        <h3>Financial and Transactional Data</h3>
        <ul>
            <li>Income details, employment history, and credit scores submitted via our secure financing application.
            </li>
            <li>Payment details for vehicle deposits, purchases, or service billing.</li>
        </ul>

        <h3>Vehicle Data</h3>
        <ul>
            <li>Information regarding vehicles you currently own, including VIN, make, model, and mileage, primarily
                collected when requesting a trade-in appraisal.</li>
            <li>Preferences and configurations for vehicles you are looking to purchase.</li>
        </ul>

        <h3>Technical and Usage Data</h3>
        <ul>
            <li>IP addresses, browser types, device identifiers, and operating systems.</li>
            <li>Information about how you interact with our website, including pages visited, time spent, and vehicles
                viewed.</li>
        </ul>

        <h2>3. How We Use Your Information</h2>
        <p>The personal information we collect is strictly utilized to facilitate your requests and enhance our service
            offerings:</p>
        <ul>
            <li><strong>To Process Transactions:</strong> Handling vehicle purchases, trade-ins, and financing
                applications securely.</li>
            <li><strong>To Communicate:</strong> Responding to your inquiries, scheduling test drives, and sending
                important updates regarding your vehicle or account.</li>
            <li><strong>To Improve Our Services:</strong> Analyzing site usage to refine our digital showroom and tailor
                our inventory to client demand.</li>
            <li><strong>For Marketing:</strong> With your explicit consent, delivering exclusive invitations to
                unveiling events, promotions, and curated vehicle alerts.</li>
        </ul>

        <h2>4. Information Sharing and Disclosure</h2>
        <p>We do not sell, rent, or trade your personal information. We only share your data with trusted third parties
            under the following conditions:</p>
        <ul>
            <li><strong>Financial Institutions:</strong> To secure lending and financing options on your behalf when you
                submit a credit application.</li>
            <li><strong>Service Providers:</strong> Third-party vendors who assist us in operating our website,
                conducting our business, or serving our users (e.g., CRM providers, secure hosting, email delivery).
            </li>
            <li><strong>Legal Obligations:</strong> When required by law, subpoena, or other legal processes to protect
                our rights, your safety, or the safety of others.</li>
        </ul>

        <h2>5. Data Security</h2>
        <p>We implement state-of-the-art security protocols to maintain the safety of your personal information. All
            sensitive data, including financial applications, is transmitted via Secure Socket Layer (SSL) technology
            and encrypted within our secure databases. Access to this information is strictly limited to authorized
            personnel who are required to keep the information confidential.</p>

        <h2>6. Your Rights and Choices</h2>
        <p>You have the right to access, correct, or delete your personal data. Furthermore, you may:</p>
        <ul>
            <li>Opt-out of marketing communications at any time by utilizing the "unsubscribe" link provided in our
                emails.</li>
            <li>Request a copy of the personal information we hold about you.</li>
            <li>Request the deletion of your data, subject to certain legal and transactional retention requirements.
            </li>
        </ul>

        <h2>7. Cookies and Tracking Technologies</h2>
        <p>Our website utilizes cookies to elevate your browsing experience, remember your preferences, and track
            analytics. You can set your browser to refuse all or some browser cookies, or to alert you when websites set
            or access cookies. Please note that disabling cookies may affect the functionality of certain parts of our
            digital showroom.</p>

        <h2>8. Changes to This Privacy Policy</h2>
        <p>We may update our Privacy Policy periodically to reflect changes in our practices or legal obligations. We
            will notify you of any material changes by posting the new Privacy Policy on this page and updating the
            "Last Updated" date at the top.</p>

        <div class="contact-box">
            <h4>Contact Our Privacy Team</h4>
            <p>If you have any questions, concerns, or requests regarding this Privacy Policy or your personal data,
                please contact our administrative team:</p>
            <p><strong>Email:</strong> <a
                    href="mailto:<?php echo sanitize($co_email); ?>"><?php echo sanitize($co_email); ?></a></p>
            <p><strong>Phone:</strong> <?php echo sanitize($co_phone); ?></p>
            <p><strong>Address:</strong> <?php echo sanitize($co_address); ?></p>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>