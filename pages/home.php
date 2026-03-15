<?php
session_start();

// Language configuration
$availableLanguages = ['nl', 'en', 'de', 'fr', 'tr'];
$currentLang = $_SESSION['language'] ?? 'nl';

// Validate language exists
if (!in_array($currentLang, $availableLanguages)) {
    $currentLang = 'nl';
    $_SESSION['language'] = $currentLang;
}

// Load language file - Use require instead of require_once
$langFile = __DIR__ . "/vertaling/{$currentLang}.php";

if (file_exists($langFile)) {
    $lang = require($langFile);  // Changed from require_once to require
} else {
    die("Error: Language file not found at {$langFile}");
}

// Ensure $lang is an array
if (!is_array($lang)) {
    $lang = [];  // Fallback to empty array instead of dying
}

// Fetch user data only if logged in
$naam = $lang['welcome'] ?? 'Welcome';
$isLoggedIn = isset($_SESSION['gebruikerId']);

if ($isLoggedIn) {
    include("config.php");
    $stmt = $db->prepare("SELECT naam FROM gebruiker WHERE gebruikerid = ?");
    $stmt->execute([$_SESSION['gebruikerId']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $naam = $user ? $user['naam'] : ($lang['guest'] ?? 'Guest');
}

// Setup for reviews
$currentUserId = $isLoggedIn ? (int)$_SESSION['gebruikerId'] : 0;

try {
    include("config.php");
    $pdo = $db;
} catch (PDOException $e) {
    die(($lang['db_connection_failed'] ?? 'Database connection failed') . ": " . $e->getMessage());
}

// Fetch all reviews
$stmt = $pdo->query("
    SELECT reviewid, gebruikerid, datum, rating, opmerking, illustratie
    FROM review
    ORDER BY datum DESC
");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Luxe Spa Resort</title>
    <link rel="stylesheet" href="../Style/home.css">
    <link rel="stylesheet" href="../Style/reviewStyle.css">
</head>

<body>
    <?php require_once __DIR__ . '/navbarKlant.php'; ?>

    <main class="home-container">
        <!-- Welcome Card -->
        <section class="welcome-card">
            <div class="welcome-header">
                <h1>
                    <?php 
                    if ($isLoggedIn && $naam !== $lang['guest']) {
                        echo $lang['welcome_back'] . ", <span class='user-name'>" . htmlspecialchars(ucfirst($naam)) . "</span>!";
                    } else {
                        echo "<span class='user-name'>" . $lang['welcome_guest'] . "</span>";
                    }
                    ?>
                </h1>
                <p class="welcome-subtitle"><?= $lang['welcome_subtitle'] ?></p>
            </div>

            <div class="welcome-content">
                <p class="welcome-intro">
                    <?= $lang['welcome_intro'] ?>
                </p>

                <div class="features-grid">
                    <div class="feature-item">
                        <h3><?= $lang['premium_treatments'] ?></h3>
                        <p><?= $lang['premium_treatments_desc'] ?></p>
                    </div>
                    <div class="feature-item">
                        <h3><?= $lang['easy_booking'] ?></h3>
                        <p><?= $lang['easy_booking_desc'] ?></p>
                    </div>
                    <div class="feature-item">
                        <h3><?= $lang['special_offers'] ?></h3>
                        <p><?= $lang['special_offers_desc'] ?></p>
                    </div>
                    <div class="feature-item">
                        <h3><?= $lang['vip_status'] ?></h3>
                        <p><?= $lang['vip_status_desc'] ?></p>
                    </div>
                </div>

                <div class="quick-stats">
                    <div class="stat">
                        <span class="stat-number"><?= $lang['availability_24_7'] ?></span>
                        <span class="stat-label"><?= $lang['availability_label'] ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?= $lang['great_reviews'] ?></span>
                        <span class="stat-label"><?= $lang['reviews_label'] ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hero Image -->
        <section class="hero-section">
            <div class="hero-wrapper">
                <img src="../afbeelding/spa resort bali.jpg" alt="Spa Resort Bali" class="hero-image">
                <div class="hero-overlay">
                    <div class="overlay-badge"></div>
                </div>
                <div class="hero-decoration">
                    <div class="deco-circle deco-1"></div>
                    <div class="deco-circle deco-2"></div>
                    <div class="deco-circle deco-3"></div>
                </div>
            </div>
        </section>
    </main>

    <h2><?= $lang['recent_reviews'] ?></h2>

    <?php if ($isLoggedIn): ?>
    <div class="reviews-button-container">
        <a href="review.php" class="btn-reviews"><?= $lang['create_review'] ?></a>
    </div>
    <?php else: ?>
    <div class="reviews-button-container">
        <p style="color: #666; font-style: italic;">
            <a href="inlog.php" style="color: #3fa8a8; text-decoration: none; font-weight: 600;"><?= $lang['login_link'] ?></a> <?= $lang['login_to_review'] ?>
        </p>
    </div>
    <?php endif; ?>

    <?php foreach ($reviews as $r): ?>
        <div class="review-card">
            <div class="rating"><?= $lang['rating'] ?>:
                <?= htmlspecialchars($r['rating']) ?><?= $lang['out_of_5'] ?>
            </div>
            <div>
                <?= nl2br(htmlspecialchars($r['opmerking'])) ?>
            </div>

            <?php if (!empty($r['illustratie'])):
                $info = @getimagesizefromstring($r['illustratie']);
                $mime = $info['mime'] ?? 'image/jpeg';
                ?>
                <img src="data:<?= $mime ?>;base64,<?= base64_encode($r['illustratie']) ?>">
            <?php endif; ?>

            <small>
                <?= htmlspecialchars($r['datum']) ?>
            </small>

            <?php if ($isLoggedIn && (int) $r['gebruikerid'] === $currentUserId): ?>
                <div><a href="review.php?edit=<?= (int) $r['reviewid'] ?>"><?= $lang['edit'] ?></a></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php require_once __DIR__ . '/FavoriteLodges.php'; ?>
    <?php require_once __DIR__ . '/RecentLodges.php'; ?>
</body>

</html>