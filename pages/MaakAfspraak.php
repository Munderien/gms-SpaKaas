<?php
session_start();
$host = 'localhost';
$db   = 'dms-spakaas';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$_SESSION['rol'];


$message = '';
// get all klanten
$gebruikers = [];
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 0) {
    // Als gebruiker krijg je alleen de ingelogde gebruiker
    $result = $conn->query("SELECT gebruikerid, naam FROM gebruiker WHERE gebruikerid = " . intval($_SESSION['gebruikerid']));
} else {
    // Anders alle gebruikers 
    $result = $conn->query("SELECT gebruikerid, naam FROM gebruiker where rol = 0 ORDER BY naam ASC");
}
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $gebruikers[] = $row;
    }
}

// get all lodges
$lodges = [];
$lodgeQuery = "SELECT l.lodgeid, lt.naam as lodgetype_naam 
               FROM lodge l
               LEFT JOIN lodgetype lt ON l.lodgetypeid = lt.typeid
               ORDER BY l.lodgeid ASC";
$lodgeResult = $conn->query($lodgeQuery);
if ($lodgeResult && $lodgeResult->num_rows > 0) {
    while ($row = $lodgeResult->fetch_assoc()) {
        $lodges[] = $row;
    }
}

$lodgeTypeName = '';
if (!empty($item['lodgeid'])) {
    $ltQuery = "SELECT lt.naam FROM lodge l
               LEFT JOIN lodgetype lt ON l.lodgetypeid = lt.typeid
               WHERE l.lodgeid = " . intval($item['lodgeid']);
    $ltResult = $conn->query($ltQuery);
    if ($ltResult && $ltResult->num_rows > 0) {
        $ltRow = $ltResult->fetch_assoc();
        $lodgeTypeName = $ltRow['naam'] ?? $item['lodgeid'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel     = $conn->real_escape_string($_POST['titel']);
    $beginTime = $conn->real_escape_string($_POST['starttijd']);
    $endTime   = $conn->real_escape_string($_POST['eindtijd']);
    $status = 'Afwachten';
    $desc      = $conn->real_escape_string($_POST['toelichting']);
    $aantalmensen = $conn->real_escape_string($_POST['aantalmensen']);
    $userId    = intval($_POST['gebruikerid']);
    $lodgeId = intval($_POST['lodgeid']);

    $today = date('Y-m-d');
    if ($beginTime < $today) {
        $message = "Datum mag niet in het verleden liggen.";
    } elseif ($endTime <= $beginTime) {
        $message = "Eindtijd moet later zijn dan begintijd.";
    } else {
        // Kijkt of er een afspraak is op de lodge die overlapped met dezelfde datum
        $conflictQuery = "SELECT COUNT(*) as count FROM afspraak 
                         WHERE lodgeid = $lodgeId 
                         AND ((starttijd <= '$beginTime' AND eindtijd > '$beginTime')
                              OR (starttijd < '$endTime' AND eindtijd >= '$endTime')
                              OR (starttijd >= '$beginTime' AND eindtijd <= '$endTime'))";
        $conflictResult = $conn->query($conflictQuery);
        $conflictRow = $conflictResult->fetch_assoc();

        if ($conflictRow['count'] > 0) {
            $message = "Deze lodge heeft al een afspraak in deze periode.";
        } else {
            $sql = "INSERT INTO afspraak (gebruikerid, lodgeid, titel, starttijd, eindtijd,
            status, toelichting, aantalmensen)
                    VALUES ('$userId', '$lodgeId', '$titel', '$beginTime', '$endTime', 
                    '$status', '$desc', '$aantalmensen')";
            if ($conn->query($sql) === TRUE) {
                $message = "Afspraak succesvol toegevoegd en iedereen is gekoppeld!";
            } else {
                $message = "Fout bij toevoegen: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Afspraak toevoegen</title>
    <link rel="stylesheet" href="../Style/MaakAfspraak.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const gebruikers = <?php echo json_encode($gebruikers); ?>;
            const lodges = <?php echo json_encode($lodges); ?>;

            const gebruikerSelect = document.getElementById('gebruikerSelect');
            const lodgeSelect = document.getElementById('lodgeSelect');

            gebruikers.forEach(g => {
                const opt = document.createElement('option');
                opt.value = g.gebruikerid;
                opt.textContent = g.naam;
                gebruikerSelect.appendChild(opt);
            });

            lodges.forEach(l => {
                const opt = document.createElement('option');
                opt.value = l.lodgeid;
                opt.textContent = l.lodgetype_naam || l.lodgeid;
                lodgeSelect.appendChild(opt);
            });
        });
    </script>
</head>

<body>
    <div class="form-grid">
        <div class="popup-overlay" id="planneritem-popup">
            <div class="popup-panel" id="main-panel">
                <h1>Nieuwe afspraak toevoegen</h1>
                <?php if ($message): ?>
                    <p class="<?php echo (str_contains($message, 'succesvol')) ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </p>
                <?php endif; ?>
                <span class="popup-close" title="Close"
                    onclick="window.location.href='home.php'">
                    &times;
                </span>
                <form method="post">
                    <div class="popup-field">
                        <label for="titel">Titel:</label><br>
                        <input type="text" id="titel" name="titel" required>
                    </div>
                    <div class="popup-field">
                        <label for="starttijd">Begintijd:</label><br>
                        <input type="date" id="starttijd" name="starttijd" required>
                    </div>
                    <div class="popup-field">
                        <label for="eindtijd">Eindtijd:</label><br>
                        <input type="date" id="eindtijd" name="eindtijd" required>
                    </div>
                    <div class="popup-field">
                        <label for="toelichting">Beschrijving:</label><br>
                        <input type="text" id="toelichting" name="toelichting" required>
                    </div>

                    <!--Als je ingelogd ben als gebruiker, laat de hidden inout type zien, anders de dropdown met alle gebruikers -->
                    <?php if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 0): ?>
                        <div class="popup-field">
                            <label for="gebruikerSelect">Select gebruiker:</label>
                            <select id="gebruikerSelect" name="gebruikerid" required>
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" id="gebruikerSelect" name="gebruikerid" value="<?php echo intval($_SESSION['gebruikerid']); ?>">
                    <?php endif; ?>

                    <div class="popup-field">
                        <label for="lodgeSelect">Select Lodge:</label>
                        <select id="lodgeSelect" name="lodgeid" required>
                            <option value="">-- Select Lodge --</option>
                        </select>
                    </div>

                    <div class="popup-field">
                        <label for="aantalmensen">aantal mensen:</label><br>
                        <input type="number" id="aantalmensen" name="aantalmensen" required><br><br>
                    </div>

                    <input type="submit" value="Toevoegen">
                </form>
            </div>
        </div>
    </div>
</body>

</html>