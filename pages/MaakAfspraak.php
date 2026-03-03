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

$isCustomer = isset($_SESSION['rol']) && (int)$_SESSION['rol'] === 0;

$message = '';
// get all klanten
$gebruikers = [];
$result = false;
if ($isCustomer) {
    // Als gebruiker krijg je alleen de ingelogde gebruiker
    if (isset($_SESSION['gebruikerId'])) {
        $result = $conn->query("SELECT gebruikerid, naam FROM gebruiker WHERE gebruikerid = " . intval($_SESSION['gebruikerId']));
    }
} else {
    // Anders alle gebruikers 
    $result = $conn->query("SELECT gebruikerid, naam FROM gebruiker ORDER BY naam ASC");
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
               LEFT JOIN lodgetype lt ON l.typeid = lt.lodgetypeid
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
    $beginTime = $conn->real_escape_string($_POST['starttijd']);
    $endTime = $conn->real_escape_string($_POST['eindtijd']);
    $status = 'Vrij';
    $desc = $conn->real_escape_string($_POST['toelichting']);
    $aantalmensen = $conn->real_escape_string($_POST['aantalmensen']);
    if ($isCustomer) {
        $userId = intval($_SESSION['gebruikerId'] ?? 0);
    } else {
        $userId = intval($_POST['gebruikerid'] ?? 0);
    }
    $lodgeId = intval($_POST['lodgeid']);

    $today = date('Y-m-d');
    if ($userId <= 0) {
        $message = "Selecteer een gebruiker.";
    } elseif ($beginTime < $today) {
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
            $sql = "INSERT INTO afspraak (gebruikerid, lodgeid, starttijd, eindtijd,
            status, toelichting, aantalmensen)
                    VALUES ('$userId', '$lodgeId', '$beginTime', '$endTime', 
                    '$status', '$desc', '$aantalmensen')";
            if ($conn->query($sql) === TRUE) {
                $message = "Afspraak succesvol toegevoegd en iedereen is gekoppeld!";
                require_once __DIR__ . '/email/emailService.php';

                $loggedInUser = null;
                if (isset($_SESSION['gebruikerId'])) {
                    $userQuery = "SELECT gebruikerid, naam, email FROM gebruiker WHERE gebruikerid = " . intval($_SESSION['gebruikerId']);
                    $userResult = $conn->query($userQuery);
                    if ($userResult && $userResult->num_rows > 0) {
                        $loggedInUser = $userResult->fetch_assoc();
                    }
                }

                try {
                    $emailService = new EmailService();
                    $emailService->sendEmail(
                        $loggedInUser['email'],
                        'Bevestiging boeking',
                        'Beste ' . $loggedInUser['naam'] . '

Uw afspraak is succesvol aangemaakt.

Details:
- Naam: ' . $titel . '
- Starttijd: ' . $beginTime . '
- Eindtijd: ' . $endTime . '
- Toelichting: ' . $desc . '
- Aantal mensen: ' . $aantalmensen . '

Wij zien u graag op de afgesproken datum en wensen u alvast een fijne tijd toe!'
                    );
                } catch (Exception $e) {
                    echo 'Error: ' . htmlspecialchars($e->getMessage());
                }
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

            // krijgt lodgeid van lodges.php
            const params = new URLSearchParams(window.location.search);
            const urlLodgeId = params.get('lodgeid');

            // Only populate gebruiker dropdown if it's actually a select element (not hidden input)
            if (gebruikerSelect && gebruikerSelect.tagName === 'SELECT') {
                gebruikers.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g.gebruikerid;
                    opt.textContent = g.naam;
                    gebruikerSelect.appendChild(opt);
                });
            }

            lodges.forEach(l => {
                const opt = document.createElement('option');
                opt.value = l.lodgeid;
                opt.textContent = l.lodgetype_naam || l.lodgeid;
                lodgeSelect.appendChild(opt);
            });

            // als er een lodgeid is geselecteerd voordat je de pagina opent, dan wordt die automatisch gekozen
            if (urlLodgeId) {
                lodgeSelect.value = urlLodgeId;
            }
        });
    </script>
</head>

<body>
    <div class="form-grid">
        <div class="popup-overlay" id="planneritem-popup">
            <div class="popup-panel" id="main-panel">
                <h1>Nieuwe afspraak toevoegen test</h1>
                <!-- Debug info -->
                <div style="background: #f0f0f0; padding: 10px; margin-bottom: 15px; border-radius: 5px; font-size: 12px;">
                    <strong>Debug:</strong> 
                    rol = <?php echo isset($_SESSION['rol']) ? $_SESSION['rol'] : 'NOT SET'; ?>, 
                    gebruikerId = <?php echo isset($_SESSION['gebruikerId']) ? $_SESSION['gebruikerId'] : 'NOT SET'; ?>,
                    isCustomer = <?php echo $isCustomer ? 'true' : 'false'; ?>,
                    gebruikers count = <?php echo count($gebruikers); ?>
                </div>
                <?php if ($message): ?>
                    <p class="<?php echo (str_contains($message, 'succesvol')) ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </p>
                <?php endif; ?>
                <span class="popup-close" title="Close" onclick="window.location.href='Lodges.php'">
                    &times;
                </span>
                <form method="post">
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
                    <?php if (!$isCustomer): ?>
                        <div class="popup-field">
                            <label for="gebruikerSelect">Select gebruiker:</label>
                            <select id="gebruikerSelect" name="gebruikerid" required>
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" id="gebruikerSelect" name="gebruikerid"
                            value="<?php echo intval($_SESSION['gebruikerId'] ?? 0); ?>">
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