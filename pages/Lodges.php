<?php
session_start();

// Language configuration
$availableLanguages = ['nl', 'en'];
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

// Database connection
$host = 'localhost';
$db   = 'dms-spakaas';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all lodges with their lodge type details
$lodges = [];
$query = "SELECT l.lodgeid, lt.lodgetypeid, lt.naam, lt.beschrijving, lt.capaciteit, lt.prijs
          FROM lodge l
          INNER JOIN lodgetype lt ON l.typeid = lt.lodgetypeid
          ORDER BY lt.naam ASC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lodges[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="<?= $currentLang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['lodges_title'] ?> - Luxe Spa Resort</title>
    <link rel="stylesheet" href="../Style/Lodges.css">
</head>

<body>
    <?php require_once __DIR__ . '/navbarKlant.php'; ?>

    <div class="lodges-container">
        <h1><?= $lang['lodges_title'] ?></h1>
        <div class="lodges-grid">
            <?php foreach ($lodges as $lodge): ?>
                <div class="lodge-card" onclick="toggleDetails(this)">
                    <div class="lodge-card-header">
                        <h2><?php echo htmlspecialchars($lodge['naam']); ?></h2>
                    </div>
                    <div class="lodge-card-content">
                        <p class="description"><?php echo htmlspecialchars(substr($lodge['beschrijving'], 0, 100)) . '...'; ?></p>
                        <div class="lodge-card-footer">
                            <span class="price"><?= $lang['lodges_currency'] ?><?php echo htmlspecialchars($lodge['prijs']); ?></span>
                        </div>
                    </div>
                    <div class="lodge-card-details">
                        <div class="details-content">
                            <div class="detail-item">
                                <label><?= $lang['lodges_description'] ?>:</label>
                                <p><?php echo htmlspecialchars($lodge['beschrijving']); ?></p>
                            </div>
                            <div class="detail-item">
                                <label><?= $lang['lodges_capacity'] ?>:</label>
                                <p><?php echo htmlspecialchars($lodge['capaciteit']); ?> <?= $lang['lodges_persons'] ?></p>
                            </div>
                            <div class="detail-item">
                                <label><?= $lang['lodges_price'] ?>:</label>
                                <p><?= $lang['lodges_currency'] ?><?php echo htmlspecialchars($lodge['prijs']); ?></p>
                            </div>
                        </div>
                        <button class="close-details" onclick="bookAppointment(<?php echo htmlspecialchars($lodge['lodgeid']); ?>)"><?= $lang['lodges_book_appointment'] ?></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function toggleDetails(card) {
            card.classList.toggle("expanded");
        }

        function bookAppointment(lodgeId) {
            event.stopPropagation();
            window.location.href = 'MaakAfspraak.php?lodgeid=' + lodgeId;
        }
    </script>
</body>

</html>