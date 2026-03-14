<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Language configuration
$availableLanguages = ['nl' => 'Nederlands', 'en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français', 'tr' => 'Türkçe'];
$currentLang = $_SESSION['language'] ?? 'nl';

// Validate language exists
if (!in_array($currentLang, array_keys($availableLanguages))) {
    $currentLang = 'nl';
    $_SESSION['language'] = $currentLang;
}

// Load language file - Fixed path
$langFile = __DIR__ . "/vertaling/{$currentLang}.php";
$lang = file_exists($langFile) ? require $langFile : [];

// Determine application root dynamically
$script = $_SERVER['SCRIPT_NAME'];
if (preg_match('#^(.*?/gms-SpaKaas)#', $script, $m)) {
    $base = $m[1];
} else {
    $base = '';
}

$huidigePagina = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['gebruikerId']);

// Get user name and profile picture from database if logged in
$gebruikersnaam = 'Gebruiker';
$userInitial = 'U';
$profielfoto = null;

if ($isLoggedIn) {
    include("config.php");
    $stmt = $db->prepare("SELECT naam, profielfoto FROM gebruiker WHERE gebruikerid = ?");
    $stmt->execute([$_SESSION['gebruikerId']]);
    $navUser = $stmt->fetch(PDO::FETCH_ASSOC);
    $gebruikersnaam = $navUser ? $navUser['naam'] : 'Gebruiker';
    $profielfoto = $navUser && !empty($navUser['profielfoto']) ? $navUser['profielfoto'] : null;
    $userInitial = strtoupper(substr($gebruikersnaam, 0, 1));
}
?>

<style>
    .spakaas-nav {
        background: linear-gradient(90deg, #0f4c5c 0%, #3d8f8f 100%);
        box-shadow: 0 6px 30px rgba(15, 76, 92, 0.15);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        width: 100%;
    }

    .nav-inner {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 40px;
        display: flex;
        align-items: center;
        gap: 8px;
        height: 70px;
    }

    .nav-brand {
        font-size: 1.6rem;
        font-weight: 800;
        color: #fff;
        text-decoration: none;
        margin-right: 16px;
        letter-spacing: 2px;
        white-space: nowrap;
    }

    .nav-brand span {
        background: linear-gradient(135deg, #ffeb3b 0%, #ff9800 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .nav-brand:hover {
        opacity: 0.9;
    }

    .nav-links {
        display: flex;
        align-items: center;
        gap: 5px;
        flex: 1;
        flex-wrap: wrap;
    }

    .nav-link {
        color: rgba(255, 255, 255, 0.82);
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s ease;
        white-space: nowrap;
        position: relative;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 16px;
        right: 16px;
        height: 2px;
        background: linear-gradient(90deg, #ffeb3b 0%, #ff9800 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
    }

    .nav-link:hover::after {
        transform: scaleX(1);
    }

    .nav-link.active {
        background: rgba(255, 255, 255, 0.2);
        color: #ffeb3b;
        font-weight: 600;
    }

    .nav-link.active::after {
        transform: scaleX(1);
    }

    .nav-divider {
        width: 1px;
        height: 24px;
        background: rgba(255, 255, 255, 0.3);
        margin: 0 8px;
    }

    /* Language Dropdown Styles */
    .nav-controls {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-left: auto;
    }

    .language-switcher {
        position: relative;
    }

    .lang-toggle {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
        padding: 8px 14px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .lang-toggle:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.4);
    }

    .lang-toggle svg {
        width: 16px;
        height: 16px;
    }

    .lang-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: linear-gradient(135deg, #0f4c5c 0%, #1a5f6f 100%);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        min-width: 180px;
        margin-top: 8px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        display: none;
        z-index: 2000;
        overflow: hidden;
    }

    .lang-dropdown.active {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .lang-option {
        color: rgba(255, 255, 255, 0.8);
        padding: 12px 16px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }

    .lang-option:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .lang-option.active {
        background: rgba(255, 235, 59, 0.2);
        color: #ffeb3b;
        border-left-color: #ffeb3b;
        font-weight: 600;
    }

    .lang-flag {
        font-size: 1.1rem;
    }

    .nav-user {
        display: flex;
        align-items: center;
        gap: 12px;
        white-space: nowrap;
        background: rgba(255, 255, 255, 0.15);
        padding: 8px 18px 8px 10px;
        border-radius: 25px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #ffeb3b 0%, #ff9800 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0f4c5c;
        font-weight: 700;
        font-size: 1.05em;
        flex-shrink: 0;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .user-avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ffeb3b 0%, #ff9800 100%);
    }

    .nav-username {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 600;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nav-btn {
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .nav-btn-out {
        background: rgba(231, 76, 60, 0.85);
        color: #fff;
    }

    .nav-btn-out:hover {
        background: #e74c3c;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
    }

    .nav-btn-in {
        background: linear-gradient(135deg, #3fa8a8 0%, #0f4c5c 100%);
        color: #fff;
    }

    .nav-btn-in:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(63, 168, 168, 0.3);
    }

    /* Add padding to body to account for fixed navbar */
    html {
        scroll-behavior: smooth;
    }

    body {
        padding-top: 70px;
    }

    @media (max-width: 1024px) {
        .nav-inner {
            padding: 0 25px;
            gap: 5px;
        }

        .nav-link {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .nav-links {
            gap: 2px;
        }
    }

    @media (max-width: 768px) {
        .spakaas-nav {
            height: 60px;
        }

        .nav-inner {
            height: 60px;
            padding: 0 15px;
        }

        .nav-brand {
            font-size: 1.2rem;
            letter-spacing: 1px;
        }

        .nav-link {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .nav-links {
            gap: 2px;
        }

        .nav-username {
            display: none;
        }

        .nav-user {
            padding: 6px 10px;
            gap: 8px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.9em;
        }

        .nav-btn {
            padding: 6px 12px;
            font-size: 0.75rem;
        }

        .lang-toggle {
            padding: 6px 10px;
            font-size: 0.75rem;
        }

        .lang-dropdown {
            min-width: 160px;
        }

        .lang-option {
            padding: 10px 12px;
            font-size: 0.85rem;
        }
    }
</style>

<nav class="spakaas-nav">
    <div class="nav-inner">
        <a href="<?= $base ?>/pages/home.php" class="nav-brand">
            Spa<span>Kaas</span>
        </a>

        <div class="nav-links">
            <a href="<?= $base ?>/pages/home.php"
                class="nav-link <?php echo $huidigePagina === 'home.php' ? 'active' : ''; ?>">
                <?= $lang['nav_home'] ?? 'Home' ?>
            </a>
            
            <!-- Only show these if logged in -->
            <?php if ($isLoggedIn): ?>
                <a href="<?= $base ?>/pages/lodges.php"
                    class="nav-link <?php echo $huidigePagina === 'lodges.php' ? 'active' : ''; ?>">
                    <?= $lang['nav_bookings'] ?? 'Boekingen' ?>
                </a>
                <a href="<?= $base ?>/pages/edit_user.php"
                    class="nav-link <?php echo $huidigePagina === 'edit_user.php' ? 'active' : ''; ?>">
                    <?= $lang['nav_profile'] ?? 'Profiel' ?>
                </a>
            <?php endif; ?>

            <a href="<?= $base ?>/pages/overOns.php"
                class="nav-link <?php echo $huidigePagina === 'overOns.php' ? 'active' : ''; ?>">
                <?= $lang['nav_about'] ?? 'Over ons' ?>
            </a>

            <a href="<?= $base ?>/pages/review.php"
                class="nav-link <?php echo $huidigePagina === 'review.php' ? 'active' : ''; ?>">
                <?= $lang['nav_reviews'] ?? 'Reviews' ?>
            </a>
        </div>

        <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] != 0)): ?>
            <span class="nav-divider"></span>
            <a href="<?= $base ?>/pages/onderhoud/onderhoud_taken.php"
                class="nav-link <?php echo $huidigePagina === 'onderhoud_taken.php' ? 'active' : ''; ?>">
                <?= $lang['nav_employee'] ?? 'Medewerkerpagina' ?>
            </a>
        <?php endif; ?>

        <div class="nav-controls">
            <!-- Language Switcher -->
            <div class="language-switcher">
                <button class="lang-toggle" onclick="toggleLanguageDropdown(event)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                    <?= strtoupper($currentLang) ?>
                </button>
                <div class="lang-dropdown" id="langDropdown">
                    <?php foreach ($availableLanguages as $code => $name): ?>
                        <a href="<?= $base ?>/pages/set-language.php?lang=<?= $code ?>" 
                           class="lang-option <?= $code === $currentLang ? 'active' : '' ?>">
                            <span class="lang-flag"><?= strtoupper($code) ?></span>
                            <span><?= $name ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="nav-user">
                <?php if ($isLoggedIn): ?>
                    <div class="user-avatar">
                        <?php if ($profielfoto): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($profielfoto); ?>" alt="<?php echo htmlspecialchars($gebruikersnaam); ?>">
                        <?php else: ?>
                            <div class="user-avatar-initial"><?php echo $userInitial; ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="nav-username"><?php echo htmlspecialchars(ucfirst($gebruikersnaam)); ?></span>
                    <a href="<?= $base ?>/pages/logout.php" class="nav-btn nav-btn-out"><?= $lang['nav_logout'] ?? 'Uitloggen' ?></a>
                <?php else: ?>
                    <a href="<?= $base ?>/pages/inlog.php" class="nav-btn nav-btn-in"><?= $lang['nav_login'] ?? 'Inloggen' ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleLanguageDropdown(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('langDropdown');
        dropdown.classList.toggle('active');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('langDropdown');
        const switcher = document.querySelector('.language-switcher');
        
        if (!switcher.contains(event.target)) {
            dropdown.classList.remove('active');
        }
    });

    // Close dropdown when clicking a language option
    document.querySelectorAll('.lang-option').forEach(option => {
        option.addEventListener('click', function() {
            document.getElementById('langDropdown').classList.remove('active');
        });
    });
</script>