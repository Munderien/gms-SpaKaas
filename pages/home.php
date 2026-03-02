<?php
session_start();
if (!isset($_SESSION['gebruikerId'])) {
    header("Location: inlog.php");
    exit();
}

// Fetch user data from database using gebruikerid
include("config.php");
$stmt = $db->prepare("SELECT naam FROM gebruiker WHERE gebruikerid = ?");
$stmt->execute([$_SESSION['gebruikerId']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$naam = $user ? $user['naam'] : 'Gast';

// Setup for reviews
$currentUserId = (int)$_SESSION['gebruikerId'];

try {
    $pdo = $db; // Use existing connection from config.php
} catch (PDOException $e) {
    die("Databaseverbinding mislukt: " . $e->getMessage());
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
<html lang="nl">

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
                <h1>Welkom terug, <span class="user-name"><?php echo htmlspecialchars(ucfirst($naam)); ?></span>!</h1>
                <p class="welcome-subtitle">Geniet van pure ontspanning en luxe</p>
            </div>

            <div class="welcome-content">
                <p class="welcome-intro">
                    Fijn dat je weer terug bent bij onze Luxe Spa Resort. Hier kun je volledig ontsnappen van het
                    dagelijkse leven en jezelf verwennen met de beste behandelingen.
                </p>

                <div class="features-grid">
                    <div class="feature-item">
                        <h3>Premium Behandelingen</h3>
                        <p>Kies uit onze exclusieve wellness-aanbiedingen</p>
                    </div>
                    <div class="feature-item">
                        <h3>Makkelijk Boeken</h3>
                        <p>Reserveer direct jouw favoriete behandeling</p>
                    </div>
                    <div class="feature-item">
                        <h3>Speciale Aanbiedingen</h3>
                        <p>Exclusieve deals voor onze leden</p>
                    </div>
                    <div class="feature-item">
                        <h3>VIP Status</h3>
                        <p>Geniet van extra voordelen en privileges</p>
                    </div>
                </div>

                <div class="quick-stats">
                    <div class="stat">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Beschikbaarheid</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">Geweldige</span>
                        <span class="stat-label">Beoordelingen</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hero Image -->
        <section class="hero-section">
            <img src="../afbeelding/spa resort bali.jpg" alt="Spa Resort Bali" class="hero-image">
        </section>
    </main>

    <h2>Recente reviews</h2>

    <div class="reviews-button-container">
        <a href="review.php" class="btn-reviews">Review maken</a>
    </div>

    <?php foreach ($reviews as $r): ?>
        <div class="review-card">
            <div class="rating">Beoordeling:
                <?= htmlspecialchars($r['rating']) ?>/5
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

            <?php if ((int) $r['gebruikerid'] === $currentUserId): ?>
                <div><a href="review.php?edit=<?= (int) $r['reviewid'] ?>">Bewerken</a></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>

</html>