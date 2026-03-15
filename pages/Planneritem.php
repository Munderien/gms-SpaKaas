<?php
session_start();
try {
    $mysqli = new mysqli("localhost", "root", "", "dms-spakaas");
    if ($mysqli->connect_errno) {
        die("Failed to connect to MySQL: " . $mysqli->connect_error);
    }
} catch (Exception $e) {
    die("DB Connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: inlog.php');
    exit;
}

$message = '';
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $afspraakId = intval($_POST['afspraakId'] ?? 0);
    
    // Load current appointment data first to check if it can be edited
    $checkQuery = "SELECT starttijd FROM afspraak WHERE afspraakId = $afspraakId LIMIT 1";
    $checkResult = $mysqli->query($checkQuery);
    $currentItem = $checkResult ? $checkResult->fetch_assoc() : null;
    
    $beginTime = $mysqli->real_escape_string($_POST['starttijd'] ?? '');
    $endTime   = $mysqli->real_escape_string($_POST['eindtijd'] ?? '');
    $status = $mysqli->real_escape_string($_POST['status'] ?? '');
    $desc      = $mysqli->real_escape_string($_POST['toelichting'] ?? '');
    $aantalmensen = $mysqli->real_escape_string($_POST['aantalmensen'] ?? '');
    $lodgeTypeId = intval($_POST['lodgetypeid'] ?? 0);

    $today = date('Y-m-d');

    // Alleen niet aanpassen client sided? (voor nu nog niet, kan nog worden aangepast) -Marijn
    if (!$currentItem) {
        $message = "Afspraak niet gevonden.";
    } elseif ($currentItem['starttijd'] < $today) {
        $message = "Mag niks aanpassen als de afspraak al is begonnen of voorbij is";
    }
    elseif ($beginTime < $today) {
        $message = "Datum mag niet in het verleden liggen.";
    } elseif ($endTime <= $beginTime) {
        $message = "Eindtijd moet later zijn dan begintijd.";
    } elseif ($lodgeTypeId <= 0) {
        $message = "Selecteer een lodgetype.";
    } else {
        // Zoek een beschikbare lodge binnen het gekozen lodgetype in de geselecteerde periode
        $availableLodgeQuery = "SELECT l.lodgeid
                                FROM lodge l
                                WHERE l.typeid = $lodgeTypeId
                                AND NOT EXISTS (
                                    SELECT 1
                                    FROM afspraak a
                                    WHERE a.lodgeid = l.lodgeid
                                    AND a.afspraakId != $afspraakId
                                    AND (
                                        (a.starttijd <= '$beginTime' AND a.eindtijd > '$beginTime')
                                        OR (a.starttijd < '$endTime' AND a.eindtijd >= '$endTime')
                                        OR (a.starttijd >= '$beginTime' AND a.eindtijd <= '$endTime')
                                    )
                                )
                                ORDER BY l.lodgeid ASC
                                LIMIT 1";

        $availableLodgeResult = $mysqli->query($availableLodgeQuery);

        if (!$availableLodgeResult || $availableLodgeResult->num_rows === 0) {
            $message = "Geen beschikbare lodge gevonden voor dit lodgetype in deze periode.";
        } else {
            $availableLodge = $availableLodgeResult->fetch_assoc();
            $lodgeId = (int) $availableLodge['lodgeid'];

            $update = "UPDATE afspraak 
                   SET lodgeid = '$lodgeId', starttijd = '$beginTime',
                   eindtijd = '$endTime', status = '$status', toelichting = '$desc', aantalmensen = '$aantalmensen'
                   WHERE afspraakId=$afspraakId";

            if ($mysqli->query($update) === TRUE) {
                header("Location: planneritem.php?id={$afspraakId}");
                exit;
            } else {
                echo "DB update failed: " . $mysqli->error;
            }
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $afspraakId = intval($_POST['afspraakId']);
    $delete = "DELETE FROM afspraak WHERE afspraakId=$afspraakId";

    if ($mysqli->query($delete)) {
        header("Location: Index.php");
        exit;
    } else {
        echo "DB delete failed: " . $mysqli->error;
    }
}

$afspraakId = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Get afspraak data - filter by gebruikerid if user is customer (rol 0)
$afspraakQuery = "SELECT afspraakId, lodgeid, starttijd, eindtijd, status, toelichting, aantalmensen 
                  FROM afspraak WHERE afspraakId = $afspraakId";
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 0) {
    // If user is customer, only show their own appointments
    $afspraakQuery .= " AND gebruikerId = " . intval($_SESSION['gebruikerId']);
}
$afspraakQuery .= " LIMIT 1";
$result = $mysqli->query($afspraakQuery);
$item = $result ? $result->fetch_assoc() : [];

// Kijkt of de gebruiker toegang heeft naar de afspraak
if (empty($item) && isset($_SESSION['rol']) && $_SESSION['rol'] == 0) {
    die("Geen toegang tot deze afspraak.");
}

// Get user data
$userData = [];
$userQuery = "
    SELECT g.* 
    FROM afspraak a
    INNER JOIN gebruiker g ON a.gebruikerId = g.gebruikerId
    WHERE a.afspraakId = $afspraakId
    LIMIT 1";
$user = $mysqli->query($userQuery);
if ($user && $user->num_rows > 0) {
    $userData = $user->fetch_assoc();
}

// Get all lodge types for dropdown
$lodgeTypes = [];
$lodgeTypeQuery = "SELECT lodgetypeid, naam
                   FROM lodgetype
                   ORDER BY naam ASC";
$lodgeTypeResult = $mysqli->query($lodgeTypeQuery);
if ($lodgeTypeResult && $lodgeTypeResult->num_rows > 0) {
    while ($row = $lodgeTypeResult->fetch_assoc()) {
        $lodgeTypes[] = $row;
    }
}

// Get current lodge type name and ID
$lodgeTypeName = '';
$currentLodgeTypeId = null;
if (!empty($item['lodgeid'])) {
    $ltQuery = "SELECT lt.naam, lt.lodgetypeid FROM lodge l
               LEFT JOIN lodgetype lt ON l.typeid = lt.lodgetypeid
               WHERE l.lodgeid = " . intval($item['lodgeid']);
    $ltResult = $mysqli->query($ltQuery);
    if ($ltResult && $ltResult->num_rows > 0) {
        $ltRow = $ltResult->fetch_assoc();
        $lodgeTypeName = $ltRow['naam'] ?? $item['lodgeid'];
        $currentLodgeTypeId = $ltRow['lodgetypeid'] ?? null;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Planner Item Popup</title>
    <link rel="stylesheet" type="text/css" href="/GMS-SPAKAAS/Style/planneritem.css">
</head>

<body>
    <div class="popup-overlay" id="planneritem-popup">
        <div class="popup-panel" id="main-panel">
            <?php if ($message) echo "<p>$message</p>"; ?>
            <span class="popup-close" title="Close"
                onclick="window.location.href='Index.php'">
                &times;
            </span>
            <h2>View Planner Item</h2>
            <div class="popup-field">
                <label>afspraakId</label>
                <div class="popup-value"><?php echo htmlspecialchars($item['afspraakId'] ?? ''); ?></div>
            </div>
            <div class="popup-field">
                <label>lodgeid</label>
                <div class="popup-value"><?php echo htmlspecialchars($lodgeTypeName ?: $item['lodgeid'] ?? ''); ?></div>
            </div>
            <div class="popup-field">
                <label>starttijd</label>
                <div class="popup-value"><?php echo htmlspecialchars($item['starttijd'] ?? ''); ?></div>
            </div>
            <div class="popup-field">
                <label>eindtijd</label>
                <div class="popup-value"><?php echo htmlspecialchars($item['eindtijd'] ?? ''); ?></div>
            </div>
            <div class="popup-field">
                <label>status</label>
                <div class="popup-value"><?php echo htmlspecialchars($item['status'] ?? ''); ?></div>
            </div>
            <div class="popup-field">
                <label>toelichting</label>
                <div class="popup-value"><?php echo nl2br(htmlspecialchars($item['toelichting'] ?? '')); ?></div>
            </div>
            <div class="popup-field">
                <label>aantal mensen</label>
                <div class="popup-value"><?php echo nl2br(htmlspecialchars($item['aantalmensen'] ?? '')); ?></div>
            </div>

            <!-- Action buttons -->
            <div class="popup-actions">
                <button type="button" class="primary-btn voltooid-btn">Update</button>
                <button type="button" class="secondary-btn patient-btn">Gebruiker</button>
            </div>
            <div class="popup-actions">
                <form method="post" style="width:100%;" onsubmit="return confirm('Are you sure?');">
                    <input type="hidden" name="afspraakId" value="<?php echo $afspraakId; ?>">
                    <button type="submit" name="delete" class="danger-btn" style="width:100%;">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const panel = document.getElementById('main-panel');

        document.querySelector('.voltooid-btn').addEventListener('click', function() {
            panel.innerHTML = `
            <span class="popup-close" title="Close"
                onclick="window.location.href='Index.php'">
                &times;
            </span>

            <h2>Update Afspraak</h2>
            <form id="updateForm" method="post">

                <div class="popup-field">
                    <label>afspraakid</label>
                    <input type="text" value="<?php echo $afspraakId; ?>" disabled="true">
                </div>
                <div class="popup-field">
                    <label>gebruikerId</label>
                    <input type="number" name="gebruikerId" value="<?php echo htmlspecialchars($item['gebruikerId'] ?? ''); ?>" required disabled="true">
                </div>
                <div class="popup-field">
                    <label>lodgetype</label>
                    <select name="lodgetypeid" required>
                        <option value="">-- Select Lodgetype --</option>
                        <?php foreach ($lodgeTypes as $lodgeType): ?>
                            <option value="<?php echo htmlspecialchars($lodgeType['lodgetypeid']); ?>" <?php echo ($currentLodgeTypeId == $lodgeType['lodgetypeid']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lodgeType['naam']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="popup-field">
                    <label>starttijd</label>
                    <input type="date" name="starttijd" value="<?php echo htmlspecialchars($item['starttijd'] ?? ''); ?>" required>
                </div>
                <div class="popup-field">
                    <label>eindtijd</label>
                    <input type="date" name="eindtijd" value="<?php echo htmlspecialchars($item['eindtijd'] ?? ''); ?>" required>
                </div>
                <div class="popup-field">
                    <label>status</label>
                    <input type="text" name="status" value="<?php echo htmlspecialchars($item['status'] ?? ''); ?>" required>
                </div>
                <div class="popup-field">
                    <label>toelichting</label>
                    <input type="text" name="toelichting" value="<?php echo htmlspecialchars($item['toelichting'] ?? ''); ?>" required>
                </div>
                <div class="popup-field">
                    <label>aantal mensen</label>
                    <input type="number" name="aantalmensen" value="<?php echo htmlspecialchars($item['aantalmensen'] ?? ''); ?>" required>
                </div>

                </div>

                <input type="hidden" name="afspraakId" value="<?php echo $afspraakId; ?>">
                <div class="popup-actions">
                    <button type="submit" name="confirm" class="primary-btn">Confirm</button>
                    <button type="button" class="secondary-btn" onclick="window.location.href='planneritem.php?id=<?php echo $afspraakId; ?>'">Back</button>
                </div>
            </form>
        `;
        });

        document.querySelector('.patient-btn').addEventListener('click', function() {
            panel.innerHTML = `
        <span class="popup-close" title="Close"
            onclick="window.location.href='Index.php'">
            &times;
        </span>
        <h2>Gebruiker Details</h2>

        <?php if (!empty($userData)): ?>
        
        <div class="popup-field">
            <label>Gebruiker ID</label>
            <div class="popup-value"><?php echo htmlspecialchars($userData['gebruikerId']); ?></div>
        </div>

        <div class="popup-field">
            <label>Naam</label>
            <div class="popup-value"><?php echo htmlspecialchars($userData['naam']); ?></div>
        </div>

        <div class="popup-field">
            <label>Email</label>
            <div class="popup-value"><?php echo htmlspecialchars($userData['email']); ?></div>
        </div>

        <div class="popup-field">
            <label>Telefoonnummer</label>
            <div class="popup-value"><?php echo htmlspecialchars($userData['telefoonnummer']); ?></div>
        </div>

        <div class="popup-field">
            <label>Adres</label>
            <div class="popup-value"><?php echo htmlspecialchars($userData['adres']); ?></div>
        </div>

        <div class="popup-field">
            <label>Plaats</label>
            <div class="popup-value"><?php echo htmlspecialchars($userData['plaats']); ?></div>
        </div>

        <div class="popup-field">
            <label>Rol</label>
            <div class="popup-value"><?php echo htmlspecialchars($userData['rol']); ?></div>
        </div>

        <div class="popup-field">
            <label>2FA Ingeschakeld</label>
            <div class="popup-value"><?php echo htmlspecialchars($userData['is2faingeschakeld']); ?></div>
        </div>

        <?php else: ?>
            <p>Geen gebruiker gevonden voor deze afspraak.</p>
        <?php endif; ?>

        <div class="popup-actions">
            <button type="button" class="secondary-btn"
                onclick="window.location.href='planneritem.php?id=<?php echo $afspraakId; ?>'">
                Back
            </button>
        </div>
    `;
        });
    </script>
</body>

</html>