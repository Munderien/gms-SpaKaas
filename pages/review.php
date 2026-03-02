<?php
session_start();

$dbHost = "localhost";
$dbName = "dms-spakaas";
$dbUser = "root";
$dbPass = "";

$currentUserId = isset($_SESSION['gebruikerId']) ? (int) $_SESSION['gebruikerId'] : null;
include("config.php");
$stmt = $db->prepare("SELECT naam FROM gebruiker WHERE gebruikerid = ?");
$stmt->execute([$_SESSION['gebruikerId']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$naam = $user ? $user['naam'] : 'Gast';
try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Databaseverbinding mislukt: " . $e->getMessage());
}

$errorMessage = "";
$editReview = null;

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $pdo->prepare("
        SELECT reviewid, rating, opmerking
        FROM review
        WHERE reviewid = :id AND gebruikerid = :uid
    ");
    $stmt->execute([
        ':id' => $editId,
        ':uid' => $currentUserId
    ]);
    $editReview = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$editReview) {
        $errorMessage = "Review niet gevonden of niet van jou om te bewerken.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_review"])) {
    $reviewId = (int) $_POST["reviewid"];

    $stmt = $pdo->prepare("
        DELETE FROM review
        WHERE reviewid = :reviewid
          AND gebruikerid = :gebruikerid
    ");
    $stmt->execute([
        ':reviewid' => $reviewId,
        ':gebruikerid' => $currentUserId
    ]);

    $_SESSION['success'] = $stmt->rowCount()
        ? "Review succesvol verwijderd."
        : "Review niet gevonden of niet van jou om te verwijderen.";

    header("Location: review.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $isUpdate = !empty($_POST["reviewid"]);
    $rating = (float) ($_POST["rating"] ?? 0);
    $message = trim($_POST["message"] ?? "");

    if ($rating < 1 || $rating > 5) {
        $errorMessage = "Beoordeling moet tussen 1 en 5 liggen.";
    } elseif ($message === "") {
        $errorMessage = "Bericht is verplicht.";
    } else {
        $illustrationData = null;
        $hasNewFile = false;

        if (isset($_FILES["illustration"]) && $_FILES["illustration"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $hasNewFile = true;

            if ($_FILES["illustration"]["error"] !== UPLOAD_ERR_OK) {
                $errorMessage = "Fout bij uploaden van bestand.";
            } else {
                $tmp = $_FILES["illustration"]["tmp_name"];
                $max = 5 * 1024 * 1024;

                if (filesize($tmp) > $max) {
                    $errorMessage = "Afbeelding is groter dan 5MB.";
                } else {
                    $info = @getimagesize($tmp);
                    $allowed = ["image/jpeg", "image/png", "image/gif"];

                    if (!$info || !in_array($info["mime"], $allowed, true)) {
                        $errorMessage = "Alleen JPG, PNG of GIF toegestaan.";
                    } else {
                        $illustrationData = file_get_contents($tmp);
                    }
                }
            }
        }

        if ($errorMessage === "") {
            if ($isUpdate) {
                $stmt = $pdo->prepare("
                    UPDATE review
                    SET datum = NOW(),
                        rating = :rating,
                        opmerking = :opmerking,
                        illustratie = COALESCE(:illustratie, illustratie)
                    WHERE reviewid = :reviewid
                      AND gebruikerid = :gebruikerid
                ");
                $stmt->bindValue(":reviewid", (int) $_POST["reviewid"], PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO review (gebruikerid, datum, rating, opmerking, illustratie)
                    VALUES (:gebruikerid, NOW(), :rating, :opmerking, :illustratie)
                ");
            }

            $stmt->bindValue(":gebruikerid", $currentUserId, PDO::PARAM_INT);
            $stmt->bindValue(":rating", number_format($rating, 1, ".", ""), PDO::PARAM_STR);
            $stmt->bindValue(":opmerking", $message, PDO::PARAM_STR);

            if ($hasNewFile) {
                $stmt->bindValue(":illustratie", $illustrationData, PDO::PARAM_LOB);
            } else {
                $stmt->bindValue(":illustratie", null, PDO::PARAM_NULL);
            }

            $stmt->execute();

            $_SESSION['success'] = $isUpdate
                ? "Review succesvol bijgewerkt!"
                : "Review succesvol verzonden!";

            header("Location: review.php");
            exit;
        }
    }
}

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
    <title>Reviews</title>
    <link rel="stylesheet" href="../Style/reviewStyle.css">
</head>

<body>
    <?php require_once __DIR__ . '/navbar.php'; ?>

    <h1><?= $editReview ? "Review bewerken" : "Review plaatsen" ?></h1>

    <?php
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">'
            . htmlspecialchars($_SESSION['success']) .
            '</div>';
        unset($_SESSION['success']);
    }
    if ($errorMessage) {
        echo '<div class="alert alert-error">'
            . htmlspecialchars($errorMessage) .
            '</div>';
    }
    ?>

    <div class="form-card">
        <form method="post" enctype="multipart/form-data">
            <?php if ($editReview): ?>
                <input type="hidden" name="reviewid" value="<?= (int) $editReview['reviewid'] ?>">
            <?php endif; ?>

            <label>Beoordeling (1-5)</label>
            <input type="number" name="rating" min="1" max="5" step="0.1" required
                value="<?= htmlspecialchars($editReview['rating'] ?? '') ?>">

            <label>Bericht</label>
            <textarea name="message" rows="4"
                required><?= htmlspecialchars($editReview['opmerking'] ?? '') ?></textarea>

            <label>Illustratie (optioneel)</label>
            <input type="file" name="illustration">

            <?php if ($editReview): ?>
                <button type="submit">Review bijwerken</button>
                <button type="submit" name="delete_review" value="1" onclick="return confirm('Deze review verwijderen?');"
                    style="background:#dc3545;color:#fff;margin-left:8px;">
                    Review verwijderen
                </button>
            <?php else: ?>
                <button type="submit">Review plaatsen</button>
            <?php endif; ?>
        </form>
    </div>

    <h2>Recente reviews</h2>

    <?php foreach ($reviews as $r): ?>
        <div class="review-card">
            <div class="rating">Beoordeling: <?= htmlspecialchars($r['rating']) ?>/5</div>
            <div><?= nl2br(htmlspecialchars($r['opmerking'])) ?></div>

            <?php if (!empty($r['illustratie'])):
                $info = @getimagesizefromstring($r['illustratie']);
                $mime = $info['mime'] ?? 'image/jpeg';
                ?>
                <img src="data:<?= $mime ?>;base64,<?= base64_encode($r['illustratie']) ?>">
            <?php endif; ?>

            <small><?= htmlspecialchars($r['datum']) ?></small>

            <?php if ((int) $r['gebruikerid'] === $currentUserId): ?>
                <div><a href="?edit=<?= (int) $r['reviewid'] ?>">Bewerken</a></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</body>

</html>