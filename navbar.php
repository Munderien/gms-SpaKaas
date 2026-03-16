<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$availableLanguages = ['nl' => 'Nederlands', 'en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français', 'tr' => 'Türkçe'];
$currentLang = $_SESSION['language'] ?? 'nl';

$langFile = __DIR__ . "/pages/vertaling/{$currentLang}.php";
if (!isset($lang) || !is_array($lang)) {
    if (file_exists($langFile)) {
        $lang = include $langFile;
    } else {
        $lang = [];
    }
}

// Determine application root dynamically
$script = $_SERVER['SCRIPT_NAME'];
if (preg_match('#^(.*?/gms-SpaKaas)#', $script, $m)) {
    $base = $m[1];
} else {
    $base = '';
}

$huidigePagina = basename($_SERVER['PHP_SELF']);
?>
<nav class="spakaas-nav">
    <div class="nav-inner">
        <a href="<?= $base ?>/pages/home.php" class="nav-brand">
            SpaKaas
        </a>

// Get user name and profile picture from database if logged in
$gebruikersnaam = 'Gebruiker';
$userInitial = 'U';
$profielfoto = null;

if ($isLoggedIn) {
    include("config.php"); // Use existing connection
    $navStmt = $db->prepare("SELECT naam, profielfoto FROM gebruiker WHERE gebruikerid = ?");
    $navStmt->execute([$_SESSION['gebruikerId']]);
    $navUser = $navStmt->fetch(PDO::FETCH_ASSOC);
    $gebruikersnaam = $navUser ? $navUser['naam'] : 'Gebruiker';
    $profielfoto = $navUser && !empty($navUser['profielfoto']) ? $navUser['profielfoto'] : null;
    $userInitial = $gebruikersnaam ? strtoupper(substr($gebruikersnaam, 0, 1)) : 'U';
    $rol = $_SESSION['rol'] ?? -1;
} else {
    $rol = -1;
}

                <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] == 2 || $_SESSION['rol'] == 3)): ?>
                    <span class="nav-divider"></span>
                    <a href="<?= $base ?>/onderhoud/problemen.php"
                        class="nav-link <?php echo $huidigePagina === 'problemen.php' ? 'active' : ''; ?>">
                        Problemen
                    </a>
                    <a href="<?= $base ?>/onderhoud/rapportage.php"
                        class="nav-link <?php echo $huidigePagina === 'rapportage.php' ? 'active' : ''; ?>">
                        Onderhoudsrapport
                    </a>
                <?php endif; ?>

                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>
                    <span class="nav-divider"></span>
                    <a href="<?= $base ?>/pages/manager/addRole.php"
                        class="nav-link <?php echo $huidigePagina === 'addRole.php' ? 'active' : ''; ?>">
                        Gebruikers
                    </a>
                    <a href="<?= $base ?>/pages/manager/lodge/type_overzicht.php"
                        class="nav-link <?php echo $huidigePagina === 'type_overzicht.php' ? 'active' : ''; ?>">
                        Lodgetypes
                    </a>
                    <a href="<?= $base ?>/pages/manager/lodge/overzicht.php"
                        class="nav-link <?php echo $huidigePagina === 'overzicht.php' ? 'active' : ''; ?>">
                        Lodges
                    </a>
                    <a href="<?= $base ?>/pages/manager/rapportage_lodges.php"
                        class="nav-link <?php echo $huidigePagina === 'rapportage_lodges.php' ? 'active' : ''; ?>">
                        Lodgerapport
                    </a>
                    <a href="<?= $base ?>/pages/manager/rapportage_omzet.php"
                        class="nav-link <?php echo $huidigePagina === 'rapportage_omzet.php' ? 'active' : ''; ?>">
                        Omzetrapport
                    </a>
                    <a href="<?= $base ?>/pages/manager/rapportage_personeel.php"
                        class="nav-link <?php echo $huidigePagina === 'rapportage_personeel.php' ? 'active' : ''; ?>">
                        Personeelrapport
                    </a>
                <?php endif; ?>
        </div>

        <div class="nav-user">
            <?php if (isset($_SESSION['gebruikerId'])): ?>
                <?php if (isset($_SESSION['naam'])): ?>
                    <span class="nav-username"><?php echo htmlspecialchars($_SESSION['naam']); ?></span>
                <?php endif; ?>
                <a href="<?= $base ?>/pages/logout.php" class="nav-btn nav-btn-out">Uitloggen</a>
            <?php else: ?>
                <a href="<?= $base ?>/pages/inlog.php" class="nav-btn nav-btn-in">Inloggen</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
    .spakaas-nav {
        background: linear-gradient(135deg, #1a3c5e 0%, #2980b9 100%);
        box-shadow: 0 2px 12px rgba(0, 0, 0, .2);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .nav-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        height: 54px;
    }

    .nav-brand {
        font-size: 1.15rem;
        font-weight: 700;
        color: #fff;
        text-decoration: none;
        margin-right: 16px;
        letter-spacing: .5px;
        white-space: nowrap;
    }

    .nav-brand:hover {
        opacity: .85;
    }

    .nav-links {
        display: flex;
        align-items: center;
        gap: 2px;
        flex: 1;
        flex-wrap: wrap;
    }

    .nav-link {
        color: rgba(255, 255, 255, .82);
        text-decoration: none;
        padding: 6px 13px;
        border-radius: 6px;
        font-size: .88rem;
        font-weight: 500;
        transition: background .15s, color .15s;
        white-space: nowrap;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, .18);
        color: #fff;
    }

    .nav-link.active {
        background: rgba(255, 255, 255, .22);
        color: #fff;
        font-weight: 600;
    }

    .nav-divider {
        width: 1px;
        height: 20px;
        background: rgba(255, 255, 255, .3);
        margin: 0 8px;
    }

    .nav-user {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
        white-space: nowrap;
    }

    .nav-username {
        font-size: .82rem;
        color: rgba(255, 255, 255, .75);
    }

    .nav-btn {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: .82rem;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s;
    }

    .nav-btn-out {
        background: rgba(231, 76, 60, .8);
        color: #fff;
    }

    .nav-btn-out:hover {
        background: #e74c3c;
    }

    .nav-btn-in {
        background: rgba(255, 255, 255, .9);
        color: #1a3c5e;
    }

    .spakaas-nav .user-avatar {
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

    .spakaas-nav .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .spakaas-nav .user-avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ffeb3b 0%, #ff9800 100%);
    }
</style>

<nav class="spakaas-nav">
    <div class="nav-inner">
        <a href="<?= $base ?>/pages/home.php" class="nav-brand">Spa<span>Kaas</span></a>

        <div class="nav-links">
            <a href="<?= $base ?>/pages/home.php"
                class="nav-link <?= $huidigePagina === 'home.php' ? 'active' : '' ?>"><?= $lang['nav_home'] ?></a>

            <?php if ($isLoggedIn): ?>
                <a href="<?= $base ?>/pages/lodges.php"
                    class="nav-link <?= ($huidigePagina === 'lodges.php' || $huidigePagina === 'Lodgepdp.php') ? 'active' : '' ?>"><?= $lang['nav_bookings'] ?></a>
                <a href="<?= $base ?>/pages/edit_user.php"
                    class="nav-link <?= $huidigePagina === 'edit_user.php' ? 'active' : '' ?>"><?= $lang['nav_profile'] ?></a>
            <?php endif; ?>

            <a href="<?= $base ?>/pages/overOns.php"
                class="nav-link <?= $huidigePagina === 'overOns.php' ? 'active' : '' ?>"><?= $lang['nav_about'] ?></a>
            <a href="<?= $base ?>/pages/review.php"
                class="nav-link <?= $huidigePagina === 'review.php' ? 'active' : '' ?>"><?= $lang['nav_reviews'] ?></a>

            <?php if ($isLoggedIn && ($rol == 1 || $rol == 3)): ?>
                <span class="nav-divider"></span>
                <div class="nav-dropdown">
                    <button class="nav-dropdown-toggle <?= in_array($huidigePagina, $baliePages) ? 'active' : '' ?>">Balie
                        <?= $pijl ?></button>
                    <div class="nav-dropdown-menu">
                        <a href="<?= $base ?>/pages/CalenderPage.php"
                            class="<?= $huidigePagina === 'CalenderPage.php' ? 'active' : '' ?>">Agenda</a>
                        <a href="<?= $base ?>/factuur_manager/list.php"
                            class="<?= $huidigePagina === 'list.php' ? 'active' : '' ?>">Facturen</a>
                        <a href="<?= $base ?>/pages/schoonmaak.php"
                            class="<?= $huidigePagina === 'schoonmaak.php' ? 'active' : '' ?>">Schoonmaak</a>
                        <a href="<?= $base ?>/pages/Medewerkers.php"
                            class="<?= $huidigePagina === 'Medewerkers.php' ? 'active' : '' ?>">Medewerkers</a>
                        <a href="<?= $base ?>/balie/uitchecken.php"
                            class="<?= $huidigePagina === 'uitchecken.php' ? 'active' : '' ?>">Uitchecken</a>
                        <a href="<?= $base ?>/balie/lodge_overzicht.php"
                            class="<?= $huidigePagina === 'lodge_overzicht.php' ? 'active' : '' ?>">Beschikbaarheid</a>
                        <a href="<?= $base ?>/pages/werkuren.php"
                            class="<?= $huidigePagina === 'werkuren.php' ? 'active' : '' ?>">Werkuren</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($isLoggedIn && ($rol == 2 || $rol == 3)): ?>
                <span class="nav-divider"></span>
                <div class="nav-dropdown">
                    <button
                        class="nav-dropdown-toggle <?= in_array($huidigePagina, $onderhoudPages) ? 'active' : '' ?>">Onderhoud
                        <?= $pijl ?></button>
                    <div class="nav-dropdown-menu">
                        <a href="<?= $base ?>/pages/onderhoud/onderhoud_taken.php"
                            class="<?= $huidigePagina === 'onderhoud_taken.php' ? 'active' : '' ?>">Taken</a>
                        <a href="<?= $base ?>/onderhoud/problemen.php"
                            class="<?= $huidigePagina === 'problemen.php' ? 'active' : '' ?>">Problemen</a>
                        <a href="<?= $base ?>/onderhoud/rapportage.php"
                            class="<?= $huidigePagina === 'rapportage.php' ? 'active' : '' ?>">Onderhoudsrapport</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($isLoggedIn && $rol == 3): ?>
                <div class="nav-dropdown">
                    <button class="nav-dropdown-toggle <?= in_array($huidigePagina, $beheerPages) ? 'active' : '' ?>">Beheer
                        <?= $pijl ?></button>
                    <div class="nav-dropdown-menu">
                        <a href="<?= $base ?>/pages/manager/addRole.php"
                            class="<?= $huidigePagina === 'addRole.php' ? 'active' : '' ?>">Gebruikers</a>
                        <a href="<?= $base ?>/pages/manager/lodge/overzicht.php"
                            class="<?= $huidigePagina === 'overzicht.php' ? 'active' : '' ?>">Lodges</a>
                        <a href="<?= $base ?>/pages/manager/lodge/type_overzicht.php"
                            class="<?= $huidigePagina === 'type_overzicht.php' ? 'active' : '' ?>">Lodgetypes</a>
                        <a href="<?= $base ?>/pages/manager/rapportage_lodges.php"
                            class="<?= $huidigePagina === 'rapportage_lodges.php' ? 'active' : '' ?>">Lodgerapport</a>
                        <a href="<?= $base ?>/pages/manager/rapportage_omzet.php"
                            class="<?= $huidigePagina === 'rapportage_omzet.php' ? 'active' : '' ?>">Omzetrapport</a>
                        <a href="<?= $base ?>/pages/manager/rapportage_personeel.php"
                            class="<?= $huidigePagina === 'rapportage_personeel.php' ? 'active' : '' ?>">Personeelrapport</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="nav-controls">
            <div class="language-switcher">
                <button class="lang-toggle" onclick="toggleLanguageDropdown(event)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path
                            d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                        </path>
                    </svg>
                    <?= strtoupper($currentLang) ?>
                </button>
                <div class="lang-dropdown" id="langDropdown">
                    <?php foreach ($availableLanguages as $code => $name): ?>
                        <a href="<?= $base ?>/pages/set-language.php?lang=<?= $code ?>"
                            class="lang-option <?= $code === $currentLang ? 'active' : '' ?>">
                            <span><?= strtoupper($code) ?></span>
                            <span><?= $name ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="nav-user">
                <?php if ($isLoggedIn): ?>
                    <div class="user-avatar">
                        <?php if ($profielfoto): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($profielfoto); ?>"
                                alt="<?php echo htmlspecialchars($gebruikersnaam); ?>">
                        <?php else: ?>
                            <div class="user-avatar-initial"><?php echo $userInitial; ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="nav-username"><?php echo htmlspecialchars(ucfirst($gebruikersnaam)); ?></span>
                    <a href="<?= $base ?>/pages/logout.php"
                        class="nav-btn nav-btn-out"><?= $lang['nav_logout'] ?? 'Uitloggen' ?></a>
                <?php else: ?>
                    <a href="<?= $base ?>/pages/inlog.php"
                        class="nav-btn nav-btn-in"><?= $lang['nav_login'] ?? 'Inloggen' ?></a>
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
    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('langDropdown');
        const switcher = document.querySelector('.language-switcher');

        if (!switcher.contains(event.target)) {
            dropdown.classList.remove('active');
        }
    });

    // Close dropdown when clicking a language option
    document.querySelectorAll('.lang-option').forEach(option => {
        option.addEventListener('click', function () {
            document.getElementById('langDropdown').classList.remove('active');
        });
    });
</script>
