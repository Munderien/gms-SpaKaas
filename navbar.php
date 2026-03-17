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

$script = $_SERVER['SCRIPT_NAME'];
if (preg_match('#^(.*?/gms-SpaKaas)#', $script, $m)) {
    $base = $m[1];
} else {
    $base = '';
}

$huidigePagina = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['gebruikerId']);

$gebruikersnaam = 'Gebruiker';
$userInitial = 'U';
$profielfoto = null;

if ($isLoggedIn) {
    include("config.php"); 
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

$baliePages = ['CalenderPage.php', 'list.php', 'schoonmaak.php', 'Medewerkers.php', 'uitchecken.php', 'lodge_overzicht.php'];
$onderhoudPages = ['onderhoud_taken.php', 'problemen.php', 'rapportage.php'];
$beheerPages = ['addRole.php', 'type_overzicht.php', 'type_toevoegen.php', 'type_bewerken.php', 'overzicht.php', 'lodge_toevoegen.php', 'koppel_afspraak.php', 'rapportage_lodges.php', 'rapportage_omzet.php', 'rapportage_personeel.php', 'werkuren.php'];
$pijl = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>';
?>

<style>
    .spakaas-nav,
    .spakaas-nav * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .spakaas-nav {
        background: linear-gradient(90deg, #0f4c5c, #3d8f8f) !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1000 !important;
        width: 100% !important;
        height: 70px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    body {
        padding-top: 70px !important;
    }

    .spakaas-nav .nav-inner {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 30px;
        display: flex;
        align-items: center;
        gap: 8px;
        height: 70px;
    }

    .spakaas-nav .nav-brand {
        font-size: 1.5rem;
        font-weight: 800;
        color: #fff;
        text-decoration: none;
        margin-right: 15px;
        letter-spacing: 2px;
        border: none;
    }

    .spakaas-nav .nav-brand span {
        background: linear-gradient(135deg, #ffeb3b 0%, #ff9800 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .spakaas-nav .nav-brand:hover {
        opacity: 0.85;
    }

    .spakaas-nav .nav-links {
        display: flex;
        align-items: center;
        gap: 4px;
        flex: 1;
        flex-wrap: wrap;
    }

    .spakaas-nav .nav-link {
        color: rgba(255, 255, 255, 0.85) !important;
        text-decoration: none !important;
        padding: 8px 14px !important;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        background: none;
        border: none;
        margin: 0;
    }

    .spakaas-nav .nav-link:hover {
        background: rgba(255, 255, 255, 0.15) !important;
        color: #fff !important;
    }

    .spakaas-nav .nav-link.active {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffeb3b !important;
        font-weight: 600;
    }

    .spakaas-nav .nav-divider {
        width: 1px;
        height: 24px;
        background: rgba(255, 255, 255, 0.3);
        margin: 0 6px;
        display: inline-block;
    }

    .spakaas-nav .nav-dropdown {
        position: relative;
    }

    .spakaas-nav .nav-dropdown-toggle {
        color: rgba(255, 255, 255, 0.85) !important;
        background: none !important;
        border: none !important;
        padding: 8px 14px !important;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        font-family: inherit;
        margin: 0;
        width: auto;
    }

    .spakaas-nav .nav-dropdown-toggle:hover {
        background: rgba(255, 255, 255, 0.15) !important;
        color: #fff !important;
    }

    .spakaas-nav .nav-dropdown-toggle.active {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffeb3b !important;
        font-weight: 600;
    }

    .spakaas-nav .nav-dropdown-toggle svg {
        width: 12px;
        height: 12px;
    }

    .spakaas-nav .nav-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        background: #0f4c5c !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        min-width: 200px;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        display: none;
        z-index: 2000;
        overflow: hidden;
    }

    .spakaas-nav .nav-dropdown::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        height: 10px;
    }

    .spakaas-nav .nav-dropdown:hover .nav-dropdown-menu {
        display: block;
    }

    .spakaas-nav .nav-dropdown-menu a {
        color: rgba(255, 255, 255, 0.85) !important;
        padding: 11px 16px !important;
        text-decoration: none !important;
        display: block !important;
        font-size: 0.88rem;
        border-left: 3px solid transparent;
        margin: 0 !important;
        background: none;
    }

    .spakaas-nav .nav-dropdown-menu a:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
    }

    .spakaas-nav .nav-dropdown-menu a.active {
        background: rgba(255, 235, 59, 0.15) !important;
        color: #ffeb3b !important;
        border-left-color: #ffeb3b;
        font-weight: 600;
    }

    .spakaas-nav .nav-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-left: auto;
    }

    .spakaas-nav .language-switcher {
        position: relative;
    }

    .spakaas-nav .lang-toggle {
        background: rgba(255, 255, 255, 0.15) !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
        padding: 6px 12px !important;
        border-radius: 20px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        width: auto;
    }

    .spakaas-nav .lang-toggle:hover {
        background: rgba(255, 255, 255, 0.25) !important;
    }

    .spakaas-nav .lang-toggle svg {
        width: 16px;
        height: 16px;
    }

    .spakaas-nav .lang-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: #0f4c5c !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        min-width: 170px;
        margin-top: 6px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        display: none;
        z-index: 2000;
        overflow: hidden;
        padding: 0 !important;
    }

    .spakaas-nav .lang-dropdown.active {
        display: block;
    }

    .spakaas-nav .lang-option {
        color: rgba(255, 255, 255, 0.85) !important;
        padding: 10px 14px !important;
        text-decoration: none !important;
        display: flex !important;
        align-items: center;
        gap: 8px;
    }

    .spakaas-nav .lang-option:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
    }

    .spakaas-nav .lang-option.active {
        background: rgba(255, 235, 59, 0.15) !important;
        color: #ffeb3b !important;
        font-weight: 600;
    }

    .spakaas-nav .nav-user {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.15);
        padding: 6px 16px 6px 12px;
        border-radius: 25px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .spakaas-nav .user-initial {
        width: 32px;
        height: 32px;
        background: #ffeb3b;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0f4c5c;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .spakaas-nav .nav-username {
        font-size: 0.88rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 600;
    }

    .spakaas-nav .nav-btn {
        padding: 7px 16px !important;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none !important;
        border: none;
        margin: 0;
        display: inline-block;
        color: #fff !important;
    }

    .spakaas-nav .nav-btn-out {
        background: #e74c3c !important;
    }

    .spakaas-nav .nav-btn-out:hover {
        background: #c0392b !important;
    }

    .spakaas-nav .nav-btn-in {
        background: #3fa8a8 !important;
    }

    .spakaas-nav .nav-btn-in:hover {
        background: #2d8a8a !important;
    }

    @media (max-width: 768px) {
        .spakaas-nav {
            height: 60px !important;
        }

        .spakaas-nav .nav-inner {
            height: 60px;
            padding: 0 12px;
        }

        .spakaas-nav .nav-brand {
            font-size: 1.1rem;
        }

        .spakaas-nav .nav-link,
        .spakaas-nav .nav-dropdown-toggle {
            padding: 5px 8px !important;
            font-size: 0.78rem;
        }

        .spakaas-nav .nav-username {
            display: none;
        }

        .spakaas-nav .nav-btn {
            padding: 5px 10px !important;
            font-size: 0.75rem;
        }

        .spakaas-nav .lang-toggle {
            padding: 5px 8px !important;
            font-size: 0.75rem;
        }

        body {
            padding-top: 60px !important;
        }
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

            <?php if ($isLoggedIn && $rol == 0): ?>
                <?php $agendaActive = in_array($huidigePagina, ['MijnAfspraken.php'], true); ?>
                <a href="<?= $base ?>/pages/MijnAfspraken.php"
                    class="nav-link <?= $agendaActive ? 'active' : '' ?>">Agenda</a>
            <?php endif; ?>
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
                        <a href="<?= $base ?>/pages/werkuren.php"
                            class="<?= $huidigePagina === 'werkuren.php' ? 'active' : '' ?>">Werkuren</a>
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
        document.getElementById('langDropdown').classList.toggle('active');
    }
    document.addEventListener('click', function (event) {
        var switcher = document.querySelector('.language-switcher');
        if (switcher && !switcher.contains(event.target)) {
            document.getElementById('langDropdown').classList.remove('active');
        }
    });
</script>