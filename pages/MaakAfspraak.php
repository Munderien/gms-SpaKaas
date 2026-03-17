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
    $lang = require($langFile);
} else {
    die("Error: Language file not found at {$langFile}");
}

// Ensure $lang is an array
if (!is_array($lang)) {
    $lang = [];
}

$host = 'localhost';
$db = 'dms-spakaas';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die($lang['appointment_connection_failed'] . " " . $conn->connect_error);
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
        $message = $lang['appointment_error_no_user'];
    } elseif ($lodgeTypeId <= 0) {
        $message = $lang['appointment_error_no_lodgetype'];
    } elseif ($aantalmensen <= 0) {
        $message = $lang['appointment_error_invalid_people_count'];
    } elseif ($capaciteit <= 0) {
        $message = $lang['appointment_error_invalid_capacity'];
    } elseif ($aantalmensen > $capaciteit) {
        $message = str_replace(':capacity', $capaciteit, $lang['appointment_error_capacity_exceeded']);
    } elseif ($beginTime < $today) {
        $message = $lang['appointment_error_past_date'];
    } elseif ($endTime <= $beginTime) {
        $message = $lang['appointment_error_invalid_time'];
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
            $message = $lang['appointment_error_no_availability'];
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
                    $emailMessage = str_replace(
                        [':name', ':startTime', ':endTime', ':description', ':numberOfPeople'],
                        [$loggedInUser['naam'], $beginTime, $endTime, $desc, $aantalmensen],
                        $lang['appointment_success_message']
                    );
                    $emailService->sendEmail(
                        $loggedInUser['email'],
                        $lang['appointment_success_title'],
                        $emailMessage
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
                $message = $lang['appointment_error_db'] . " " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $lang['appointment_title'] ?> - Luxe Spa Resort</title>
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
                <h1><?= $lang['appointment_title'] ?></h1>

                <?php if ($message): ?>
                    <p class="<?php echo str_contains($message, $lang['appointment_error_no_user'] ?? '') || str_contains($message, $lang['appointment_error_no_lodgetype'] ?? '') || strpos($message, $lang['appointment_error_db'] ?? '') !== false ? 'error-message' : 'success-message'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                <?php endif; ?>

                <span class="popup-close" title="Close" onclick="window.location.href='Lodges.php'">&times;</span>

                <form method="post">
                    <div class="popup-field">
                        <label for="starttijd"><?= $lang['appointment_start_time'] ?></label><br>
                        <input min="<?php echo date('Y-m-d'); ?>" type="date" id="starttijd" name="starttijd" required>
                    </div>
                    <div class="popup-field">
                        <label for="eindtijd"><?= $lang['appointment_end_time'] ?></label><br>
                        <input min="<?php echo date('Y-m-d'); ?>" type="date" id="eindtijd" name="eindtijd" required>
                    </div>
                    <div class="popup-field">
                        <label for="toelichting"><?= $lang['appointment_description'] ?></label><br>
                        <input type="text" id="toelichting" name="toelichting" required>
                    </div>

                    <?php if ($rol !== 0): ?>
                        <!-- Medewerker/manager: dropdown met alle gebruikers -->
                        <div class="popup-field">
                            <label for="gebruikerSelect"><?= $lang['appointment_select_user'] ?></label>
                            <select id="gebruikerSelect" name="gebruikerid" required>
                                <option value=""><?= $lang['appointment_select_users'] ?></option>
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
                        <label for="lodgeTypeSelect"><?= $lang['appointment_select_lodgetype'] ?></label>
                        <select id="lodgeTypeSelect" name="lodgetypeid" required>
                            <option value=""><?= $lang['appointment_select_lodgetype_placeholder'] ?></option>
                        </select>
                    </div>

                    <div id="lodgeTypeInfo" class="lodge-info-card" style="display:none;">
                        <div class="lodge-info-title"><?= $lang['appointment_lodge_info_title'] ?></div>
                        <div class="lodge-info-row"><strong><?= $lang['appointment_lodge_info_name'] ?></strong> <span id="lodgeInfoName">-</span></div>
                        <div class="lodge-info-row"><strong><?= $lang['appointment_lodge_info_capacity'] ?></strong> <span id="lodgeInfoCapacity">-</span> <?= $lang['lodge_pdp_capacity_unit'] ?></div>
                        <div class="lodge-info-row"><strong><?= $lang['appointment_lodge_info_price'] ?></strong> <span id="lodgeInfoPrice">-</span></div>
                        <div class="lodge-info-row"><strong><?= $lang['appointment_lodge_info_description'] ?></strong> <span id="lodgeInfoDescription">-</span></div>
                    </div>

                    <div class="popup-field">
                        <label for="aantalmensen"><?= $lang['appointment_number_of_people'] ?></label><br>
                        <input type="number" id="aantalmensen" name="aantalmensen" min="1" required><br><br>
                    </div>

                    <input type="submit" value="<?= $lang['appointment_submit_btn'] ?>">
                </form>
            </div>
        </div>
    </div>
</body>
</html>