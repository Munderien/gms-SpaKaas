<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$huidigePagina = basename($_SERVER['PHP_SELF']);
?>
<nav class="spakaas-nav">
    <div class="nav-inner">
        <a href="/dms-spakaas/gms-SpaKaas/home.php" class="nav-brand">
            SpaKaas
        </a>

        <div class="nav-links">
            <a href="/dms-spakaas/gms-SpaKaas/home.php"
                class="nav-link <?php echo $huidigePagina === 'home.php' ? 'active' : ''; ?>">
                Home
            </a>
            <a href="/dms-spakaas/gms-SpaKaas/Calendar.php"
                class="nav-link <?php echo $huidigePagina === 'Calendar.php' ? 'active' : ''; ?>">
                Agenda
            </a>
            <a href="/dms-spakaas/gms-SpaKaas/factuur_manager/list.php"
                class="nav-link <?php echo $huidigePagina === 'list.php' ? 'active' : ''; ?>">
                Facturen
            </a>

            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>
                <span class="nav-divider"></span>
                <a href="/dms-spakaas/gms-SpaKaas/manager/addRole.php"
                    class="nav-link <?php echo $huidigePagina === 'addRole.php' ? 'active' : ''; ?>">
                    Gebruikers
                </a>
                <a href="/dms-spakaas/gms-SpaKaas/manager/lodge/type_overzicht.php"
                    class="nav-link <?php echo $huidigePagina === 'type_overzicht.php' ? 'active' : ''; ?>">
                    Lodgetypes
                </a>
                <a href="/dms-spakaas/gms-SpaKaas/manager/lodge/overzicht.php"
                    class="nav-link <?php echo $huidigePagina === 'overzicht.php' ? 'active' : ''; ?>">
                    Lodges
                </a>
                <a href="/dms-spakaas/gms-SpaKaas/manager/rapportage_lodges.php"
                    class="nav-link <?php echo $huidigePagina === 'rapportage_lodges.php' ? 'active' : ''; ?>">
                    Lodgerapport
                </a>
                <a href="/dms-spakaas/gms-SpaKaas/manager/rapportage_omzet.php"
                    class="nav-link <?php echo $huidigePagina === 'rapportage_omzet.php' ? 'active' : ''; ?>">
                    Omzetrapport
                </a>
                <a href="/dms-spakaas/gms-SpaKaas/manager/rapportage_personeel.php"
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
                <a href="/dms-spakaas/gms-SpaKaas/logout.php" class="nav-btn nav-btn-out">Uitloggen</a>
            <?php else: ?>
                <a href="/dms-spakaas/gms-SpaKaas/inlog/inlog.php" class="nav-btn nav-btn-in">Inloggen</a>
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

    .nav-btn-in:hover {
        background: #fff;
    }
</style>