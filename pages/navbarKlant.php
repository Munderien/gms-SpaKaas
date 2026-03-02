<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine application root dynamically
$script = $_SERVER['SCRIPT_NAME'];
if (preg_match('#^(.*?/gms-SpaKaas)#', $script, $m)) {
    $base = $m[1];
} else {
    $base = '';
}

$huidigePagina = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['gebruikerId']);

// Get user name from database if logged in
$gebruikersnaam = 'Gebruiker';
$userInitial = 'U';

if ($isLoggedIn) {
    include("config.php");
    $stmt = $db->prepare("SELECT naam FROM gebruiker WHERE gebruikerid = ?");
    $stmt->execute([$_SESSION['gebruikerId']]);
    $navUser = $stmt->fetch(PDO::FETCH_ASSOC);
    $gebruikersnaam = $navUser ? $navUser['naam'] : 'Gebruiker';
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

    .nav-user {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-left: auto;
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
                Home
            </a>
            
            <!-- Only show these if logged in -->
            <?php if ($isLoggedIn): ?>
                <a href="<?= $base ?>/pages/MaakAfspraak.php"
                    class="nav-link <?php echo $huidigePagina === 'MaakAfspraak.php' ? 'active' : ''; ?>">
                    Boekingen
                </a>
                <a href="<?= $base ?>/pages/edit_user.php"
                    class="nav-link <?php echo $huidigePagina === 'edit_user.php' ? 'active' : ''; ?>">
                    Profiel
                </a>
            <?php endif; ?>

            <a href="<?= $base ?>/pages/overOns.php"
                class="nav-link <?php echo $huidigePagina === 'overOns.php' ? 'active' : ''; ?>">
                Over ons
            </a>

            <a href="<?= $base ?>/pages/review.php"
                class="nav-link <?php echo $huidigePagina === 'review.php' ? 'active' : ''; ?>">
                Reviews
            </a>

            <?php if ($isLoggedIn && isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>
                <span class="nav-divider"></span>
                <a href="<?= $base ?>/manager/addRole.php"
                    class="nav-link <?php echo $huidigePagina === 'addRole.php' ? 'active' : ''; ?>">
                    Gebruikers
                </a>
                <a href="<?= $base ?>/manager/rapportage.php"
                    class="nav-link <?php echo $huidigePagina === 'rapportage.php' ? 'active' : ''; ?>">
                    Rapporten
                </a>
            <?php endif; ?>
        </div>

        <div class="nav-user">
            <?php if ($isLoggedIn): ?>
                <div class="user-avatar"><?php echo $userInitial; ?></div>
                <span class="nav-username"><?php echo htmlspecialchars(ucfirst($gebruikersnaam)); ?></span>
                <a href="<?= $base ?>/pages/logout.php" class="nav-btn nav-btn-out">Uitloggen</a>
            <?php else: ?>
                <a href="<?= $base ?>/pages/inlog.php" class="nav-btn nav-btn-in">Inloggen</a>
            <?php endif; ?>
        </div>
    </div>
</nav>