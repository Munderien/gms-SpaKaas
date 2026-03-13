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

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: /dms-spakaas/gms-SpaKaas/pages/inlog.php');
    exit;
}

// Kijk hier later naar hier -Marijn
$isCustomer = isset($_SESSION['rol']) && $_SESSION['rol'] != 0;

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

// get all lodge types
$lodgeTypes = [];
$lodgeTypeQuery = "SELECT lodgetypeid, naam
                   FROM lodgetype
                   ORDER BY naam ASC";
$lodgeTypeResult = $conn->query($lodgeTypeQuery);
if ($lodgeTypeResult && $lodgeTypeResult->num_rows > 0) {
    while ($row = $lodgeTypeResult->fetch_assoc()) {
        $lodgeTypes[] = $row;
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
    $lodgeTypeId = intval($_POST['lodgetypeid'] ?? 0);

    $today = date('Y-m-d');
    if ($userId <= 0) {
        $message = "Selecteer een gebruiker.";
    } elseif ($lodgeTypeId <= 0) {
        $message = "Selecteer een lodgetype.";
    } elseif ($beginTime < $today) {
        $message = "Datum mag niet in het verleden liggen.";
    } elseif ($endTime <= $beginTime) {
        $message = "Eindtijd moet later zijn dan begintijd.";
    } else {
        // Zoek een beschikbare lodge binnen het gekozen lodgetype in de geselecteerde periode
        $availableLodgeQuery = "SELECT l.lodgeid
                                FROM lodge l
                                WHERE l.typeid = $lodgeTypeId
                                AND NOT EXISTS (
                                    SELECT 1
                                    FROM afspraak a
                                    WHERE a.lodgeid = l.lodgeid
                                    AND (
                                        (a.starttijd <= '$beginTime' AND a.eindtijd > '$beginTime')
                                        OR (a.starttijd < '$endTime' AND a.eindtijd >= '$endTime')
                                        OR (a.starttijd >= '$beginTime' AND a.eindtijd <= '$endTime')
                                    )
                                )
                                ORDER BY l.lodgeid ASC
                                LIMIT 1";

        $availableLodgeResult = $conn->query($availableLodgeQuery);

        if (!$availableLodgeResult || $availableLodgeResult->num_rows === 0) {
            $message = "Geen beschikbare lodge gevonden voor dit lodgetype in deze periode.";
        } else {
            $availableLodge = $availableLodgeResult->fetch_assoc();
            $lodgeId = (int) $availableLodge['lodgeid'];

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
            const lodgeTypes = <?php echo json_encode($lodgeTypes); ?>;

            const gebruikerSelect = document.getElementById('gebruikerSelect');
            const lodgeTypeSelect = document.getElementById('lodgeTypeSelect');

            // krijgt lodgetypeid van Lodges.php
            const params = new URLSearchParams(window.location.search);
            const urlLodgeTypeId = params.get('lodgetypeid');

            // Only populate gebruiker dropdown if it's actually a select element (not hidden input)
            if (gebruikerSelect && gebruikerSelect.tagName === 'SELECT') {
                gebruikers.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g.gebruikerid;
                    opt.textContent = g.naam;
                    gebruikerSelect.appendChild(opt);
                });
            }

            lodgeTypes.forEach(l => {
                const opt = document.createElement('option');
                opt.value = l.lodgetypeid;
                opt.textContent = l.naam || l.lodgetypeid;
                lodgeTypeSelect.appendChild(opt);
            });

            // als er een lodgetypeid is geselecteerd voordat je de pagina opent, dan wordt die automatisch gekozen
            if (urlLodgeTypeId) {
                lodgeTypeSelect.value = urlLodgeTypeId;
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
                        <label for="lodgeTypeSelect">Select Lodgetype:</label>
                        <select id="lodgeTypeSelect" name="lodgetypeid" required>
                            <option value="">-- Select Lodgetype --</option>
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