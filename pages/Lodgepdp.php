<?php
session_start();
$host = 'localhost';
$db = 'dms-spakaas';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$lodgetypeId = isset($_GET['lodgetypeid']) ? (int) $_GET['lodgetypeid'] : 0;
if ($lodgetypeId <= 0) {
    die('Ongeldig lodgetype.');
}

// Get specific lodge type
$stmt = $conn->prepare("SELECT lodgetypeid, naam, beschrijving, capaciteit, prijs FROM lodgetype WHERE lodgetypeid = ? LIMIT 1");
if (!$stmt) {
    die("Query prepare failed: " . $conn->error);
}

$stmt->bind_param('i', $lodgetypeId);
$stmt->execute();
$result = $stmt->get_result();
$lodgeType = $result ? $result->fetch_assoc() : null;

if (!$lodgeType) {
    die('Lodgetype niet gevonden.');
}

// Track recently viewed lodge types in cookie (only if consent given)
$cookieConsentGiven = isset($_COOKIE['cookieConsent']) && $_COOKIE['cookieConsent'] === 'accepted';
$recentLodges = [];
if ($cookieConsentGiven && isset($_COOKIE['recentLodges'])) {
    $decoded = json_decode($_COOKIE['recentLodges'], true);
    if (is_array($decoded)) {
        $recentLodges = array_map('intval', $decoded);
    }
}

// Move current lodgetype to front and keep max 5 unique items
$recentLodges = array_values(array_diff($recentLodges, [$lodgetypeId]));
array_unshift($recentLodges, $lodgetypeId);
$recentLodges = array_slice($recentLodges, 0, 5);

if ($cookieConsentGiven) {
    setcookie('recentLodges', json_encode($recentLodges), time() + (14 * 24 * 60 * 60), '/');
}

// Track this view if user is logged in
if (isset($_SESSION['gebruikerId'])) {
    $date = date("Y-m-d H:i:s");
    $userId = (int) $_SESSION['gebruikerId'];

    // Keep one row per lodgetype per user (no duplicates)
    $deleteView = $conn->prepare("DELETE FROM viewed WHERE gebruikerid = ? AND lodgetypeid = ?");
    if ($deleteView) {
        $deleteView->bind_param('ii', $userId, $lodgetypeId);
        $deleteView->execute();
        $deleteView->close();
    }

    $stmtView = $conn->prepare("INSERT INTO viewed (gebruikerid, lodgetypeid, viewed_at) VALUES (?, ?, ?)");
    if ($stmtView) {
        $stmtView->bind_param('iis', $userId, $lodgetypeId, $date);
        $stmtView->execute();
        $stmtView->close();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lodgetype details</title>
    <link rel="stylesheet" href="../Style/Lodges.css">
</head>

<body>
    <?php include '../navbar.php'; ?>

    <div class="lodges-container">
        <h1><?php echo htmlspecialchars($lodgeType['naam']); ?></h1>
        <div class="lodge-card expanded">
            <div class="lodge-card-content">
                <p class="description"><?php echo htmlspecialchars($lodgeType['beschrijving']); ?></p>
                <div class="lodge-card-footer">
                    <span class="price">€<?php echo htmlspecialchars($lodgeType['prijs']); ?></span>
                </div>
            </div>
            <div class="lodge-card-details" style="max-height:none; opacity:1; margin-top:12px;">
                <div class="details-content">
                    <div class="detail-item">
                        <label>Capaciteit:</label>
                        <p><?php echo htmlspecialchars($lodgeType['capaciteit']); ?> personen</p>
                    </div>
                    <div class="detail-item">
                        <label>Lodgetype ID:</label>
                        <p><?php echo htmlspecialchars($lodgeType['lodgetypeid']); ?></p>
                    </div>
                </div>
                <button class="close-details"
                    onclick="window.location.href='MaakAfspraak.php?lodgetypeid=<?php echo (int) $lodgeType['lodgetypeid']; ?>'">Maak
                    afspraak</button>
            </div>
        </div>
    </div>
</body>

</html>