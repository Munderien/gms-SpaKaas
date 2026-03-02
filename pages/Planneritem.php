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
$message = '';
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $titel     = $mysqli->real_escape_string($_POST['titel'] ?? '');
    $beginTime = $mysqli->real_escape_string($_POST['starttijd'] ?? '');
    $endTime   = $mysqli->real_escape_string($_POST['eindtijd'] ?? '');
    $status = $mysqli->real_escape_string($_POST['status'] ?? '');
    $desc      = $mysqli->real_escape_string($_POST['toelichting'] ?? '');
    $aantalmensen = $mysqli->real_escape_string($_POST['aantalmensen'] ?? '');
    $lodgeId = intval($_POST['lodgeid'] ?? 0);
    $afspraakId = intval($_POST['afspraakId'] ?? 0);

    $today = date('Y-m-d');
    if ($beginTime < $today) {
        $message = "Datum mag niet in het verleden liggen.";
    } elseif ($endTime <= $beginTime) {
        $message = "Eindtijd moet later zijn dan begintijd.";
    } else {
        // Kijkt of er een afspraak is op de lodge die overlapped met dezelfde datum
        $conflictQuery = "SELECT COUNT(*) as count FROM afspraak
                         WHERE lodgeid = $lodgeId
                         AND afspraakId != $afspraakId
                         AND ((starttijd <= '$beginTime' AND eindtijd > '$beginTime')
                              OR (starttijd < '$endTime' AND eindtijd >= '$endTime')
                              OR (starttijd >= '$beginTime' AND eindtijd <= '$endTime'))";
        $conflictResult = $mysqli->query($conflictQuery);
        $conflictRow = $conflictResult ? $conflictResult->fetch_assoc() : ['count' => 0];

        if ($conflictRow['count'] > 0) {
            $message = "Deze lodge heeft al een afspraak in deze periode.";
        } else {
            $update = "UPDATE afspraak 
                   SET lodgeid = '$lodgeId', titel = '$titel', starttijd = '$beginTime',
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
$afspraakQuery = "SELECT afspraakId, lodgeid, titel, starttijd, eindtijd, status, toelichting, aantalmensen 
                  FROM afspraak WHERE afspraakId = $afspraakId";
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 0) {
    // If user is customer, only show their own appointments
    $afspraakQuery .= " AND gebruikerid = " . intval($_SESSION['gebruikerid']);
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
    INNER JOIN gebruiker g ON a.gebruikerid = g.gebruikerid
    WHERE a.afspraakId = $afspraakId
    LIMIT 1";
$user = $mysqli->query($userQuery);
if ($user && $user->num_rows > 0) {
    $userData = $user->fetch_assoc();
}

// Get all lodges for dropdown
$lodges = [];
$lodgeQuery = "SELECT l.lodgeid, lt.naam as lodgetype_naam 
               FROM lodge l
               LEFT JOIN lodgetype lt ON l.lodgetypeid = lt.typeid
               ORDER BY l.lodgeid ASC";
$lodgeResult = $mysqli->query($lodgeQuery);
if ($lodgeResult && $lodgeResult->num_rows > 0) {
    while ($row = $lodgeResult->fetch_assoc()) {
        $lodges[] = $row;
    }
}

// Get current lodge type name
$lodgeTypeName = '';
if (!empty($item['lodgeid'])) {
    $ltQuery = "SELECT lt.naam FROM lodge l
               LEFT JOIN lodgetype lt ON l.lodgetypeid = lt.typeid
               WHERE l.lodgeid = " . intval($item['lodgeid']);
    $ltResult = $mysqli->query($ltQuery);
    if ($ltResult && $ltResult->num_rows > 0) {
        $ltRow = $ltResult->fetch_assoc();
        $lodgeTypeName = $ltRow['naam'] ?? $item['lodgeid'];
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Planner Item Popup</title>
    <link rel="stylesheet" type="text/css" href="Style/planneritem.css">
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
                <label>titel</label>
                <div class="popup-value"><?php echo nl2br(htmlspecialchars($item['titel'] ?? '')); ?></div>
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
                    <label>gebruikerid</label>
                    <input type="number" name="gebruikerid" value="<?php echo htmlspecialchars($item['gebruikerid'] ?? ''); ?>" required disabled="true">
                </div>
                <div class="popup-field">
                    <label>lodgeid</label>
                    <select name="lodgeid" required>
                        <option value="">-- Select Lodge --</option>
                        <?php foreach ($lodges as $lodge): ?>
                            <option value="<?php echo htmlspecialchars($lodge['lodgeid']); ?>" <?php echo ($item['lodgeid'] == $lodge['lodgeid']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lodge['lodgetype_naam'] ?? $lodge['lodgeid']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="popup-field">
                    <label>titel</label>
                    <input type="text" name="titel" value="<?php echo htmlspecialchars($item['titel'] ?? ''); ?>" required>
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
            <div class="popup-value"><?php echo htmlspecialchars($userData['gebruikerid']); ?></div>
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
            <label>Actief</label>
            <div class="popup-value"><?php echo htmlspecialchars($userData['isactief']); ?></div>
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