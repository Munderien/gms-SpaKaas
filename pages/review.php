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

// Load language file
$langFile = __DIR__ . "/vertaling/{$currentLang}.php";

if (file_exists($langFile)) {
    $lang = require_once($langFile);
} else {
    die("Error: Language file not found at {$langFile}");
}

$dbHost = "localhost";
$dbName = "dms-spakaas";
$dbUser = "root";
$dbPass = "";

$isLoggedIn = isset($_SESSION['gebruikerId']);
$currentUserId = $isLoggedIn ? (int) $_SESSION['gebruikerId'] : null;

include("config.php");

// Only fetch user name if logged in
$naam = $lang['guest'];
if ($isLoggedIn) {
    $stmt = $db->prepare("SELECT naam FROM gebruiker WHERE gebruikerid = ?");
    $stmt->execute([$_SESSION['gebruikerId']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $naam = $user ? $user['naam'] : $lang['guest'];
}

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
    die($lang['review_error_db'] . ": " . $e->getMessage());
}

$errorMessage = "";
$editReview = null;

// Only allow edit if logged in
if ($isLoggedIn && isset($_GET['edit'])) {
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
        $errorMessage = $lang['review_error_not_found'];
    }
} elseif (!$isLoggedIn && isset($_GET['edit'])) {
    // Redirect to login if trying to edit while not logged in
    header("Location: inlog.php");
    exit;
}

// Only allow delete if logged in
if ($isLoggedIn && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_review"])) {
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
        ? $lang['review_success_delete']
        : $lang['review_error_not_found_delete'];

    header("Location: review.php");
    exit;
}

// Only allow posting/updating if logged in
if ($isLoggedIn && $_SERVER["REQUEST_METHOD"] === "POST") {
    $isUpdate = !empty($_POST["reviewid"]);
    $rating = (float) ($_POST["rating"] ?? 0);
    $message = trim($_POST["message"] ?? "");

    if ($rating < 1 || $rating > 5) {
        $errorMessage = $lang['review_error_rating'];
    } elseif ($message === "") {
        $errorMessage = $lang['review_error_message_empty'];
    } else {
        $illustrationData = null;
        $hasNewFile = false;

        if (isset($_FILES["illustration"]) && $_FILES["illustration"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $hasNewFile = true;

            if ($_FILES["illustration"]["error"] !== UPLOAD_ERR_OK) {
                $errorMessage = $lang['review_error_upload'];
            } else {
                $tmp = $_FILES["illustration"]["tmp_name"];
                $max = 5 * 1024 * 1024;

                if (filesize($tmp) > $max) {
                    $errorMessage = $lang['review_error_file_size'];
                } else {
                    $info = @getimagesize($tmp);
                    $allowed = ["image/jpeg", "image/png", "image/gif"];

                    if (!$info || !in_array($info["mime"], $allowed, true)) {
                        $errorMessage = $lang['review_error_file_type'];
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
                ? $lang['review_success_update']
                : $lang['review_success_new'];

            header("Location: review.php");
            exit;
        }
    }
}

// Fetch all reviews (always accessible)
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
    <title><?= $lang['review_page_title'] ?></title>
    <link rel="stylesheet" href="../Style/reviewStyle.css">
    <style>
        body {
            padding-top: 70px;
        }

        .login-prompt {
            background-color: #e8f4f8;
            border-left: 4px solid #3fa8a8;
            color: #0f4c5c;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }

        .login-prompt a {
            color: #3fa8a8;
            text-decoration: none;
            font-weight: 600;
            margin: 0 5px;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }

        .form-card {
            max-width: 600px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #0f4c5c;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        h2 {
            color: #0f4c5c;
            margin-top: 50px;
            margin-bottom: 30px;
            text-align: center;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }

        .alert-error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        .review-card {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #3fa8a8;
        }

        .review-card img {
            max-width: 100%;
            border-radius: 8px;
            margin: 15px 0;
        }

        .rating {
            font-weight: bold;
            color: #0f4c5c;
            margin-bottom: 10px;
        }

        .review-card small {
            color: #999;
            display: block;
            margin-top: 10px;
        }

        .review-card a {
            color: #3fa8a8;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
            display: inline-block;
        }

        .review-card a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../navbar.php'; ?>

    <?php if ($isLoggedIn): ?>
        <h1><?= $editReview ? $lang['review_form_title_edit'] : $lang['review_form_title_new'] ?></h1>

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

                <label><strong><?= $lang['review_form_rating'] ?></strong></label>
                <input type="number" name="rating" min="1" max="5" step="0.1" required
                    value="<?= htmlspecialchars($editReview['rating'] ?? '') ?>">

                <label><strong><?= $lang['review_form_message'] ?></strong></label>
                <textarea name="message" rows="4"
                    required><?= htmlspecialchars($editReview['opmerking'] ?? '') ?></textarea>

                <label><strong><?= $lang['review_form_illustration'] ?></strong></label>
                <input type="file" name="illustration" accept="image/*">

                <?php if ($editReview): ?>
                    <button type="submit" style="background: linear-gradient(135deg, #3fa8a8 0%, #0f4c5c 100%); color: white; margin-top: 15px;"><?= $lang['review_form_update'] ?></button>
                    <button type="submit" name="delete_review" value="1" onclick="return confirm('<?= $lang['review_form_delete_confirm'] ?>');"
                        style="background:#dc3545;color:#fff;margin-left:8px;margin-top: 15px;">
                        <?= $lang['review_form_delete'] ?>
                    </button>
                <?php else: ?>
                    <button type="submit" style="background: linear-gradient(135deg, #3fa8a8 0%, #0f4c5c 100%); color: white; margin-top: 15px;"><?= $lang['review_form_submit'] ?></button>
                <?php endif; ?>
            </form>
        </div>
    <?php else: ?>
        <div class="login-prompt">
            <strong><?= $lang['review_login_prompt'] ?></strong><br>
            <?= $lang['review_login_required'] ?>
            <a href="inlog.php"><?= $lang['review_login_here'] ?></a> <?php echo $lang['review_create_account']; ?>
        </div>
    <?php endif; ?>

    <h2><?= $lang['review_heading_recent'] ?></h2>

    <?php foreach ($reviews as $r): ?>
        <div class="review-card">
            <div class="rating"><?= $lang['review_rating_label'] ?>: <?= htmlspecialchars($r['rating']) ?><?= $lang['out_of_5'] ?></div>
            <div><?= nl2br(htmlspecialchars($r['opmerking'])) ?></div>

            <?php if (!empty($r['illustratie'])):
                $info = @getimagesizefromstring($r['illustratie']);
                $mime = $info['mime'] ?? 'image/jpeg';
                ?>
                <img src="data:<?= $mime ?>;base64,<?= base64_encode($r['illustratie']) ?>" alt="Review afbeelding">
            <?php endif; ?>

            <small><?= htmlspecialchars($r['datum']) ?></small>

            <?php if ($isLoggedIn && (int) $r['gebruikerid'] === $currentUserId): ?>
                <div><a href="?edit=<?= (int) $r['reviewid'] ?>"><?= $lang['review_edit_link'] ?></a></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</body>

</html>