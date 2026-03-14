<?php
// Cookie consent is stored in a functional cookie (no analytics/tracking, just remembers the choice)
// This file should be included BEFORE any other cookie is set
$cookieConsentGiven  = isset($_COOKIE['cookieConsent']) && $_COOKIE['cookieConsent'] === 'accepted';
$cookieConsentDeclined = isset($_COOKIE['cookieConsent']) && $_COOKIE['cookieConsent'] === 'declined';
$cookieConsentDecided = isset($_COOKIE['cookieConsent']);
?>
<link rel="stylesheet" href="../Style/cookieConsent.css">
<?php if (!$cookieConsentDecided): ?>
<div id="cookie-banner" class="cookie-banner" role="dialog" aria-label="Cookie toestemming">
    <div class="cookie-banner-inner">
        <div class="cookie-banner-text">
            <span class="cookie-icon">🍪</span>
            <div>
                <strong>Wij gebruiken cookies</strong>
                <p>We slaan je recent bekeken lodges op, alleen zonder inlog. Om jouw ervaring te verbeteren. Wil je dit toestaan?</p>
            </div>
        </div>
        <div class="cookie-banner-actions">
            <button class="cookie-btn cookie-btn-accept" onclick="setCookieConsent('accepted')">Alles accepteren</button>
            <button class="cookie-btn cookie-btn-decline" onclick="setCookieConsent('declined')">Weigeren</button>
        </div>
    </div>
</div>

<script>
    function setCookieConsent(choice) {
        // Store for 365 days
        const expires = new Date();
        expires.setFullYear(expires.getFullYear() + 1);
        document.cookie = 'cookieConsent=' + choice + '; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';

        document.getElementById('cookie-banner').style.display = 'none';

        // If declined, remove any previously set recentLodges cookie
        if (choice === 'declined') {
            document.cookie = 'recentLodges=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        }

        window.location.reload();
    }
</script>
<?php endif; ?>
