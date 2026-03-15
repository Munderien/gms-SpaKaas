<?php
require '../pages/config.php';
session_start();

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: /dms-spakaas/gms-SpaKaas/pages/inlog.php');
    exit;
}
$stmt = $db->prepare("SELECT rol FROM gebruiker WHERE gebruikerid = ?");
$stmt->execute([$_SESSION['gebruikerId']]);
$_SESSION['rol'] = (int) $stmt->fetchColumn();
if ($_SESSION['rol'] != 2 && $_SESSION['rol'] != 3) {
    die('Geen toegang');
}

$melding = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['onderhoudid'])) {
    $onderhoudid = (int) $_POST['onderhoudid'];
    $nieuweStatus = $_POST['status'];
    $db->prepare("UPDATE onderhoud SET status = ? WHERE onderhoudid = ?")->execute([$nieuweStatus, $onderhoudid]);

    if ($nieuweStatus == 'gesloten') {
        $stmtLodge = $db->prepare("SELECT lodgeid FROM onderhoud WHERE onderhoudid = ?");
        $stmtLodge->execute([$onderhoudid]);
        $lodgeid = $stmtLodge->fetchColumn();
        if ($lodgeid) {
            $openTaken = $db->prepare("SELECT COUNT(*) FROM onderhoud WHERE lodgeid = ? AND status != 'gesloten'");
            $openTaken->execute([$lodgeid]);
            if ($openTaken->fetchColumn() == 0) {
                $db->prepare("UPDATE lodge SET status = 'vrij' WHERE lodgeid = ? AND status = 'onderhoud'")->execute([$lodgeid]);
            }
        }
    }

    $melding = 'Status bijgewerkt.';
}

$problemen = $db->query("
    SELECT o.onderhoudid, o.omschrijving, o.status,
           l.huisnummer, lt.naam AS typename,
           g.naam AS monteurnaam
    FROM onderhoud o
    JOIN lodge l ON l.lodgeid = o.lodgeid
    JOIN lodgetype lt ON lt.lodgetypeid = l.typeid
    LEFT JOIN gebruiker g ON g.gebruikerid = o.monteurid
    ORDER BY
        CASE o.status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'gesloten' THEN 3 END
")->fetchAll(PDO::FETCH_ASSOC);

$totaalOpen = 0;
$totaalBezig = 0;
$totaalGesloten = 0;
foreach ($problemen as $p) {
    if ($p['status'] == 'open')
        $totaalOpen++;
    elseif ($p['status'] == 'in_progress')
        $totaalBezig++;
    elseif ($p['status'] == 'gesloten')
        $totaalGesloten++;
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemelde Problemen – SpaKaas</title>
    <link rel="stylesheet" href="/dms-spakaas/gms-SpaKaas/Style/manager.css">
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }

        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            border-top: 4px solid #3498db;
        }

        .stat-card.rood {
            border-color: #e74c3c;
        }

        .stat-card.oranje {
            border-color: #f39c12;
        }

        .stat-card.groen {
            border-color: #27ae60;
        }

        .stat-card .getal {
            font-size: 1.6rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .stat-card .label {
            font-size: .75rem;
            color: #7f8c8d;
            margin-top: 2px;
        }



        .status-open {
            color: #e74c3c;
            font-weight: 600;
        }

        .status-in_progress {
            color: #f39c12;
            font-weight: 600;
        }

        .status-gesloten {
            color: #27ae60;
            font-weight: 600;
        }

        select.compact {
            padding: 5px 8px;
            font-size: .85rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .inline-form {
            display: inline;
        }
    </style>
</head>

<body>
    <div class="manager-container">
        <h1>Gemelde Problemen</h1>

        <?php if ($melding != ''): ?>
            <p style="color:green;font-weight:bold;">
                <?php echo $melding; ?>
            </p>
        <?php endif; ?>

        <div class="stat-grid">
            <div class="stat-card rood">
                <div class="getal">
                    <?php echo $totaalOpen; ?>
                </div>
                <div class="label">Open</div>
            </div>
            <div class="stat-card oranje">
                <div class="getal">
                    <?php echo $totaalBezig; ?>
                </div>
                <div class="label">In behandeling</div>
            </div>
            <div class="stat-card groen">
                <div class="getal">
                    <?php echo $totaalGesloten; ?>
                </div>
                <div class="label">Gesloten</div>
            </div>
            <div class="stat-card">
                <div class="getal">
                    <?php echo count($problemen); ?>
                </div>
                <div class="label">Totaal</div>
            </div>
        </div>

        <?php if (empty($problemen)): ?>
            <div class="empty-state">Geen problemen gemeld.</div>
        <?php else: ?>
            <table class="manager-table">
                <thead>
                    <tr>
                        <th>Lodge</th>
                        <th>Omschrijving</th>
                        <th>Monteur</th>
                        <th>Status</th>
                        <th>Status wijzigen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($problemen as $p): ?>
                        <tr>
                            <td>
                                <?php echo $p['huisnummer'] . ' – ' . htmlspecialchars($p['typename']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($p['omschrijving']); ?>
                            </td>
                            <td>
                                <?php
                                if (isset($p['monteurnaam']) && $p['monteurnaam'] != '') {
                                    echo htmlspecialchars($p['monteurnaam']);
                                } else {
                                    echo 'Niet toegewezen';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="status-<?php echo $p['status']; ?>">
                                    <?php
                                    if ($p['status'] == 'open')
                                        echo 'Open';
                                    elseif ($p['status'] == 'in_progress')
                                        echo 'In behandeling';
                                    else
                                        echo 'Gesloten';
                                    ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="onderhoudid" value="<?php echo $p['onderhoudid']; ?>">
                                    <select name="status" class="compact">
                                        <option value="open" <?php if ($p['status'] == 'open')
                                            echo 'selected'; ?>>Open</option>
                                        <option value="in_progress" <?php if ($p['status'] == 'in_progress')
                                            echo 'selected'; ?>>
                                            In behandeling</option>
                                        <option value="gesloten" <?php if ($p['status'] == 'gesloten')
                                            echo 'selected'; ?>>
                                            Gesloten</option>
                                    </select>
                                    <button type="submit" class="btn btn-warning btn-sm">Opslaan</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php include '../navbar.php'; ?>
</body>

</html>


























