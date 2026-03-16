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
    header('Location: inlog.php');
    exit;
}

$rol = (int)($_SESSION['rol'] ?? 0);

$message = '';
// get all klanten
$gebruikers = [];
$result = false;
if ($rol === 0) {
    // Klant: alleen eigen account
    $result = $conn->query("SELECT gebruikerid, naam FROM gebruiker WHERE gebruikerid = " . intval($_SESSION['gebruikerId']));
} else {
    // Medewerker/manager: alle gebruikers
    $result = $conn->query("SELECT gebruikerid, naam FROM gebruiker ORDER BY naam ASC");
}
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $gebruikers[] = $row;
    }
}

// get all lodge types
$lodgeTypes = [];
$lodgeTypeResult = $conn->query("SELECT lodgetypeid, naam, beschrijving, capaciteit, prijs FROM lodgetype ORDER BY naam ASC");
if ($lodgeTypeResult && $lodgeTypeResult->num_rows > 0) {
    while ($row = $lodgeTypeResult->fetch_assoc()) {
        $lodgeTypes[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $beginTime = $conn->real_escape_string($_POST['starttijd']);
    $endTime   = $conn->real_escape_string($_POST['eindtijd']);
    $status    = 'Vrij';
    $desc      = $conn->real_escape_string($_POST['toelichting']);
    $aantalmensen = intval($_POST['aantalmensen'] ?? 0);

    if ($rol === 0) {
        $userId = intval($_SESSION['gebruikerId'] ?? 0);
    } else {
        $userId = intval($_POST['gebruikerid'] ?? 0);
    }
    $lodgeTypeId = intval($_POST['lodgetypeid'] ?? 0);

    $capaciteit = 0;
    if ($lodgeTypeId > 0) {
        $capStmt = $conn->prepare("SELECT capaciteit FROM lodgetype WHERE lodgetypeid = ? LIMIT 1");
        if ($capStmt) {
            $capStmt->bind_param('i', $lodgeTypeId);
            $capStmt->execute();
            $capResult = $capStmt->get_result();
            if ($capResult && $capResult->num_rows > 0) {
                $capRow     = $capResult->fetch_assoc();
                $capaciteit = (int)($capRow['capaciteit'] ?? 0);
            }
            $capStmt->close();
        }
    }

    $today = date('Y-m-d');
    if ($userId <= 0) {
        $message = "Selecteer een gebruiker.";
    } elseif ($lodgeTypeId <= 0) {
        $message = "Selecteer een lodgetype.";
    } elseif ($aantalmensen <= 0) {
        $message = "Aantal mensen moet groter zijn dan 0.";
    } elseif ($capaciteit <= 0) {
        $message = "Ongeldig lodgetype of capaciteit niet gevonden.";
    } elseif ($aantalmensen > $capaciteit) {
        $message = "Je kan niet meer dan $capaciteit personen boeken voor dit lodgetype.";
    } elseif ($beginTime < $today) {
        $message = "Datum mag niet in het verleden liggen.";
    } elseif ($endTime <= $beginTime) {
        $message = "Eindtijd moet later zijn dan begintijd.";
    } else {
        $availableLodgeQuery = "SELECT l.lodgeid
                                FROM lodge l
                                WHERE l.typeid = $lodgeTypeId
                                AND NOT EXISTS (
                                    SELECT 1 FROM afspraak a
                                    WHERE a.lodgeid = l.lodgeid
                                    AND (
                                        (a.starttijd <= '$beginTime' AND a.eindtijd > '$beginTime')
                                        OR (a.starttijd < '$endTime'  AND a.eindtijd >= '$endTime')
                                        OR (a.starttijd >= '$beginTime' AND a.eindtijd <= '$endTime')
                                    )
                                )
                                ORDER BY l.lodgeid ASC LIMIT 1";

        $availableLodgeResult = $conn->query($availableLodgeQuery);

        if (!$availableLodgeResult || $availableLodgeResult->num_rows === 0) {
            $message = "Geen beschikbare lodge gevonden voor dit lodgetype in deze periode.";
        } else {
            $availableLodge = $availableLodgeResult->fetch_assoc();
            $lodgeId        = (int)$availableLodge['lodgeid'];

            $sql = "INSERT INTO afspraak (gebruikerid, lodgeid, starttijd, eindtijd, status, toelichting, aantalmensen)
                    VALUES ('$userId','$lodgeId','$beginTime','$endTime','$status','$desc','$aantalmensen')";

            if ($conn->query($sql) === TRUE) {
                $conn->query("UPDATE lodge SET status = 'bezet' WHERE lodgeid = $lodgeId");
                require_once __DIR__ . '/email/emailService.php';

                $loggedInUser = null;
                $userQuery    = "SELECT gebruikerid, naam, email FROM gebruiker WHERE gebruikerid = " . intval($_SESSION['gebruikerId']);
                $userResult   = $conn->query($userQuery);
                if ($userResult && $userResult->num_rows > 0) {
                    $loggedInUser = $userResult->fetch_assoc();
                }

                try {
                    $emailService = new EmailService();
                    $emailService->sendEmail(
                        $loggedInUser['email'],
                        'Bevestiging boeking',
                        'Beste ' . $loggedInUser['naam'] . "\n\nUw afspraak is succesvol aangemaakt.\n\nDetails:\n- Starttijd: $beginTime\n- Eindtijd: $endTime\n- Toelichting: $desc\n- Aantal mensen: $aantalmensen\n\nWij zien u graag op de afgesproken datum en wensen u alvast een fijne tijd toe!"
                    );
                } catch (Exception $e) {
                    echo 'Error: ' . htmlspecialchars($e->getMessage());
                }

                // Store appointment details in session and redirect to success page
                $_SESSION['appointmentDetails'] = [
                    'starttijd' => $beginTime,
                    'eindtijd' => $endTime,
                    'toelichting' => $desc,
                    'aantalmensen' => $aantalmensen
                ];
                header('Location: AfspraakSucces.php');
                exit;
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
            const lodgeTypes     = <?php echo json_encode($lodgeTypes); ?>;
            const lodgeTypeSelect = document.getElementById('lodgeTypeSelect');
            const lodgeTypeInfo = document.getElementById('lodgeTypeInfo');
            const lodgeInfoName = document.getElementById('lodgeInfoName');
            const lodgeInfoCapacity = document.getElementById('lodgeInfoCapacity');
            const lodgeInfoPrice = document.getElementById('lodgeInfoPrice');
            const lodgeInfoDescription = document.getElementById('lodgeInfoDescription');
            const aantalMensenInput = document.getElementById('aantalmensen');

            // Pre-select lodgetypeid if passed via URL (e.g. from Lodges.php)
            const urlLodgeTypeId = new URLSearchParams(window.location.search).get('lodgetypeid');

            lodgeTypes.forEach(l => {
                const opt = document.createElement('option');
                opt.value = l.lodgetypeid;
                opt.textContent = l.naam || l.lodgetypeid;
                lodgeTypeSelect.appendChild(opt);
            });

            const renderSelectedLodgeInfo = () => {
                const selectedId = lodgeTypeSelect.value;
                const selectedLodgeType = lodgeTypes.find(l => String(l.lodgetypeid) === String(selectedId));

                if (!selectedLodgeType) {
                    lodgeTypeInfo.style.display = 'none';
                    if (aantalMensenInput) {
                        aantalMensenInput.removeAttribute('max');
                    }
                    return;
                }

                lodgeInfoName.textContent = selectedLodgeType.naam || '-';
                lodgeInfoCapacity.textContent = selectedLodgeType.capaciteit || '-';
                lodgeInfoPrice.textContent = selectedLodgeType.prijs ? '€' + selectedLodgeType.prijs : '-';
                lodgeInfoDescription.textContent = selectedLodgeType.beschrijving || 'Geen beschrijving beschikbaar.';

                if (aantalMensenInput && selectedLodgeType.capaciteit) {
                    aantalMensenInput.max = parseInt(selectedLodgeType.capaciteit, 10);
                }

                lodgeTypeInfo.style.display = 'block';
            };

            if (urlLodgeTypeId) {
                lodgeTypeSelect.value = urlLodgeTypeId;
            }

            lodgeTypeSelect.addEventListener('change', renderSelectedLodgeInfo);
            renderSelectedLodgeInfo();
        });
    </script>
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="form-grid">
        <div class="popup-overlay" id="planneritem-popup">
            <div class="popup-panel" id="main-panel">
                <h1>Nieuwe afspraak toevoegen</h1>

                <?php if ($message): ?>
                    <p class="<?php echo str_contains($message, 'succesvol') ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </p>
                <?php endif; ?>

                <span class="popup-close" title="Close" onclick="window.location.href='Lodges.php'">&times;</span>

                <form method="post">
                    <div class="popup-field">
                        <label for="starttijd">Begintijd:</label><br>
                        <input min="<?php echo date('Y-m-d'); ?>" type="date" id="starttijd" name="starttijd" required>
                    </div>
                    <div class="popup-field">
                        <label for="eindtijd">Eindtijd:</label><br>
                        <input min="<?php echo date('Y-m-d'); ?>" type="date" id="eindtijd" name="eindtijd" required>
                    </div>
                    <div class="popup-field">
                        <label for="toelichting">Beschrijving:</label><br>
                        <input type="text" id="toelichting" name="toelichting" required>
                    </div>

                    <?php if ($rol !== 0): ?>
                        <!-- Medewerker/manager: dropdown met alle gebruikers -->
                        <div class="popup-field">
                            <label for="gebruikerSelect">Select gebruiker:</label>
                            <select id="gebruikerSelect" name="gebruikerid" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($gebruikers as $g): ?>
                                    <option value="<?= (int)$g['gebruikerid'] ?>"><?= htmlspecialchars($g['naam']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <!-- Klant: eigen account als hidden input -->
                        <input type="hidden" name="gebruikerid" value="<?= intval($_SESSION['gebruikerId'] ?? 0) ?>">
                    <?php endif; ?>

                    <div class="popup-field">
                        <label for="lodgeTypeSelect">Select Lodgetype:</label>
                        <select id="lodgeTypeSelect" name="lodgetypeid" required>
                            <option value="">-- Select Lodgetype --</option>
                        </select>
                    </div>

                    <div id="lodgeTypeInfo" class="lodge-info-card" style="display:none;">
                        <div class="lodge-info-title">Geselecteerde lodge informatie</div>
                        <div class="lodge-info-row"><strong>Naam:</strong> <span id="lodgeInfoName">-</span></div>
                        <div class="lodge-info-row"><strong>Capaciteit:</strong> <span id="lodgeInfoCapacity">-</span> personen</div>
                        <div class="lodge-info-row"><strong>Prijs:</strong> <span id="lodgeInfoPrice">-</span></div>
                        <div class="lodge-info-row"><strong>Beschrijving:</strong> <span id="lodgeInfoDescription">-</span></div>
                    </div>

                    <div class="popup-field">
                        <label for="aantalmensen">Aantal mensen:</label><br>
                        <input type="number" id="aantalmensen" name="aantalmensen" min="1" required><br><br>
                    </div>

                    <input type="submit" value="Toevoegen">
                </form>
            </div>
        </div>
    </div>
</body>
</html>
