<?php
// includes/footer.php

// Fetch all necessary details from the site settings
$company_email = get_site_setting($pdo, 'company_email', 'info@rjmotorworld.com');
$office_address = get_site_setting($pdo, 'office_address', '123 Dealer Drive');
$site_name_footer = get_site_setting($pdo, 'company_name', 'RJ Motorworld');
$company_phone = get_site_setting($pdo, 'company_phone', 'Call Us Today'); 
$site_logo_footer = get_site_setting($pdo, 'site_logo', '');
$business_hours = get_site_setting($pdo, 'business_hours', 'Mon - Sat: 9:00 AM - 7:00 PM');
$whatsapp_number = get_site_setting($pdo, 'whatsapp_number', '');

// Social Media Links
$facebook_link = get_site_setting($pdo, 'facebook_link', '');
$instagram_link = get_site_setting($pdo, 'instagram_link', '');
$x_link = get_site_setting($pdo, 'x_link', '');
$tiktok_link = get_site_setting($pdo, 'tiktok_link', '');
$youtube_link = get_site_setting($pdo, 'youtube_link', '');
?>
<style>
/* ═══════════════════════════════════════════════════════
   RJ MOTORWORLD — Premium Footer CSS (High Contrast)
═══════════════════════════════════════════════════════ */

.site-footer {
    background-color: #000000;
    /* Absolute black for a sharp break from the body */
    color: var(--silver, #b8b8cc);
    padding: 80px 7% 40px;
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    border-top: 2px solid var(--gold-deep, #8a6830);
    /* Stronger demarcation line */
    position: relative;
    z-index: 10;
    box-shadow: 0 -15px 40px rgba(0, 0, 0, 0.7);
    /* Pulls the footer visually under the body */
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 50px;
    margin-bottom: 60px;
}

.footer-col h4 {
    font-family: 'Playfair Display', serif;
    color: var(--white, #fdfdfd);
    font-size: 20px;
    font-weight: 500;
    margin-top: 0;
    margin-bottom: 24px;
    letter-spacing: 0.02em;
}

/* Footer logo — enlarged 3× (50px → 150px), layout unchanged */
.footer-logo img {
    max-height: 150px;
    width: auto;
    margin-bottom: 20px;
    display: block;
}

/* Fallback if no image logo is present */
.footer-logo-text {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 600;
    color: var(--gold-mid, #d4aa68);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 20px;
    display: block;
    text-decoration: none;
}

.footer-col p {
    margin: 8px 0;
    line-height: 1.8;
    font-weight: 300;
    color: var(--mist, #7a7a96);
}

/* Contact List Styling */
.contact-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.contact-list li {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 16px;
    font-weight: 300;
    line-height: 1.6;
}

.contact-list i {
    color: var(--gold, #c4974a);
    margin-top: 4px;
    width: 16px;
    text-align: center;
    font-size: 14px;
}

.contact-list a {
    transition: color 0.2s ease;
}

.contact-list a:hover {
    color: var(--gold-light, #e8c880);
}

/* Quick Links */
.footer-links {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.footer-links a {
    color: var(--silver, #b8b8cc);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-weight: 300;
}

/* Custom animated arrow for links */
.footer-links a::before {
    content: "";
    display: block;
    width: 6px;
    height: 1px;
    background-color: var(--gold-mid, #d4aa68);
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.3s;
}

.footer-links a:hover {
    color: var(--white, #fdfdfd);
    transform: translateX(6px);
}

.footer-links a:hover::before {
    width: 14px;
    background-color: var(--gold-light, #e8c880);
}

/* Social Media Circles */
.social-links {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 30px;
}

.social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.03);
    /* Slight fill to pop against pure black */
    border: 1px solid rgba(196, 151, 74, 0.35);
    /* gold-line */
    border-radius: 50%;
    color: var(--silver, #b8b8cc);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 15px;
}

.social-links a:hover {
    background: var(--gold, #c4974a);
    color: var(--black, #050507);
    border-color: var(--gold, #c4974a);
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(196, 151, 74, 0.25);
}

/* Bottom Bar */
.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    /* Slightly more visible on pure black */
    padding-top: 30px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    color: var(--mist, #7a7a96);
}

.footer-bottom-links {
    display: flex;
    gap: 24px;
}

.footer-bottom-links a {
    color: var(--mist, #7a7a96);
    transition: color 0.2s;
}

.footer-bottom-links a:hover {
    color: var(--gold, #c4974a);
}

/* Responsive */
@media (max-width: 768px) {
    .site-footer {
        padding: 60px 6% 30px;
    }

    .footer-bottom {
        flex-direction: column;
        text-align: center;
        justify-content: center;
        gap: 20px;
    }
}
</style>

<footer class="site-footer">
    <div class="footer-grid">

        <div class="footer-col">
            <?php if (!empty($site_logo_footer) && file_exists(__DIR__ . '/../' . $site_logo_footer)): ?>
            <a href="<?php echo BASE_URL; ?>" class="footer-logo">
                <img src="<?php echo BASE_URL . sanitize($site_logo_footer); ?>"
                    alt="<?php echo sanitize($site_name_footer); ?>">
            </a>
            <?php else: ?>
            <a href="<?php echo BASE_URL; ?>" class="footer-logo-text">
                <?php echo sanitize($site_name_footer); ?>
            </a>
            <?php endif; ?>

            <p>Premium pre-owned and new vehicles. We ensure quality, transparency, and a seamless buying experience
                from start to finish.</p>

            <div class="social-links">
                <?php if (!empty($facebook_link)): ?>
                <a href="<?php echo sanitize($facebook_link); ?>" target="_blank" title="Facebook"><i
                        class="fa-brands fa-facebook-f"></i></a>
                <?php endif; ?>

                <?php if (!empty($x_link)): ?>
                <a href="<?php echo sanitize($x_link); ?>" target="_blank" title="X (Twitter)"><i
                        class="fa-brands fa-x-twitter"></i></a>
                <?php endif; ?>

                <?php if (!empty($instagram_link)): ?>
                <a href="<?php echo sanitize($instagram_link); ?>" target="_blank" title="Instagram"><i
                        class="fa-brands fa-instagram"></i></a>
                <?php endif; ?>

                <?php if (!empty($tiktok_link)): ?>
                <a href="<?php echo sanitize($tiktok_link); ?>" target="_blank" title="TikTok"><i
                        class="fa-brands fa-tiktok"></i></a>
                <?php endif; ?>

                <?php if (!empty($youtube_link)): ?>
                <a href="<?php echo sanitize($youtube_link); ?>" target="_blank" title="YouTube"><i
                        class="fa-brands fa-youtube"></i></a>
                <?php endif; ?>

                <?php if (!empty($whatsapp_number)): ?>
                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp_number); ?>" target="_blank"
                    title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer-col">
            <h4>Quick Links</h4>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>vehicles.php">Search Inventory</a>
                <a href="<?php echo BASE_URL; ?>financing.php">Apply for Financing</a>
                <a href="<?php echo BASE_URL; ?>sell_trade.php">Value Your Trade</a>
                <a href="<?php echo BASE_URL; ?>contact.php">Contact Us</a>
                <a href="<?php echo BASE_URL; ?>about.php">About Dealership</a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Contact Details</h4>
            <ul class="contact-list">
                <?php if (!empty($office_address)): ?>
                <li><i class="fa-solid fa-location-dot"></i> <span><?php echo sanitize($office_address); ?></span></li>
                <?php endif; ?>

                <?php if (!empty($company_phone)): ?>
                <li><i class="fa-solid fa-phone"></i> <span><a
                            href="tel:<?php echo preg_replace('/[^0-9+]/', '', $company_phone); ?>"
                            style="color: inherit; text-decoration: none;"><?php echo sanitize($company_phone); ?></a></span>
                </li>
                <?php endif; ?>

                <?php if (!empty($company_email)): ?>
                <li><i class="fa-solid fa-envelope"></i> <span><a href="mailto:<?php echo sanitize($company_email); ?>"
                            style="color: inherit; text-decoration: none;"><?php echo sanitize($company_email); ?></a></span>
                </li>
                <?php endif; ?>

                <?php if (!empty($business_hours)): ?>
                <li><i class="fa-solid fa-clock"></i> <span><?php echo sanitize($business_hours); ?></span></li>
                <?php endif; ?>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <div>&copy; <?php echo date('Y'); ?> <?php echo sanitize($site_name_footer); ?>. All rights reserved.</div>
        <div class="footer-bottom-links">
            <a href="<?php echo BASE_URL; ?>privacy_policy.php">Privacy Policy</a>
            <a href="<?php echo BASE_URL; ?>terms.php">Terms of Service</a>
        </div>
    </div>
</footer>

</body>

</html>