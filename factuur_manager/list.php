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

if ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 3) {
    die('Geen toegang.');
}


if (isset($_GET['action']) && isset($_GET['factuurid'])) {
    $id = (int) $_GET['factuurid'];
    if ($_GET['action'] == 'markeer_betaald') {
        $db->prepare("UPDATE factuur SET betaalstatus = 1 WHERE factuurid = ?")->execute([$id]);
    } elseif ($_GET['action'] == 'markeer_open') {
        $db->prepare("UPDATE factuur SET betaalstatus = 0 WHERE factuurid = ?")->execute([$id]);
    } elseif ($_GET['action'] == 'herinnering_verstuurd') {
        $db->prepare("UPDATE factuur SET herinneringsmailstatus = 1 WHERE factuurid = ?")->execute([$id]);

        // gebruik $id om in de database emailadres en huisnummer te zoeken met select en join
        // maak hier mailfunctie aan om de herinnering te sturen

    } elseif ($_GET['action'] == 'herinnering_reset') {
        $db->prepare("UPDATE factuur SET herinneringsmailstatus = 0 WHERE factuurid = ?")->execute([$id]);
    }
    header('Location: list.php');
    exit;
}


$sql = "SELECT f.factuurid, f.factuurdatum, f.totaalbedragexbtw, f.btwpercentage,
               f.betaalstatus, f.herinneringsmailstatus, f.aantalmensen, f.toelichting,
               g.naam AS klantnaam, g.email AS klantemail,
               l.naam AS lodgetype_naam,
               a.starttijd, a.eindtijd
        FROM factuur f
        JOIN gebruiker g ON f.gebruikerid = g.gebruikerid
        JOIN lodgetype l ON f.typeid = l.lodgetypeid
        JOIN afspraak a ON f.afspraakid = a.afspraakid
        ORDER BY f.factuurdatum DESC";
$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$betaald = 0;
$open = 0;
$aantalOpen = 0;
foreach ($result as $r) {
    $incl = $r['totaalbedragexbtw'] * (1 + $r['btwpercentage'] / 100);
    if ($r['betaalstatus']) {
        $betaald += $incl;
    } else {
        $open += $incl;
        $aantalOpen++;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factuuroverzicht – SpaKaas</title>
    <link rel="stylesheet" href="../Style/manager.css">
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin: 20px 0 28px;
        }

        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            border-top: 4px solid #3498db;
        }

        .stat-card.rood {
            border-color: #e74c3c;
        }

        .stat-card.groen {
            border-color: #27ae60;
        }

        .stat-card .getal {
            font-size: 1.7rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .stat-card .label {
            font-size: .8rem;
            color: #7f8c8d;
            margin-top: 4px;
        }




        .badge-betaald {
            background: #d5f5e3;
            color: #1e8449;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }

        .badge-open {
            background: #fde8e8;
            color: #b91c1c;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }

        .badge-herinn {
            background: #fef9e7;
            color: #7d6608;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .78rem;
        }


        .actions {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .actions a {
            font-size: .78rem;
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
            background: #e8f4fd;
            color: #1f6799;
            border: 1px solid #aed6f1;
            white-space: nowrap;
        }

        .actions a:hover {
            background: #aed6f1;
        }

        .actions a.print {
            background: #eafaf1;
            color: #1e8449;
            border-color: #a9dfbf;
        }

        .actions a.print:hover {
            background: #a9dfbf;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>

    <div class="manager-container">
        <h1>Factuuroverzicht</h1>


        <div class="stat-grid">
            <div class="stat-card">
                <div class="getal"><?php echo count($result); ?></div>
                <div class="label">Totaal facturen</div>
            </div>
            <div class="stat-card groen">
                <div class="getal">€ <?php echo number_format($betaald, 2, ',', '.'); ?></div>
                <div class="label">Betaald (incl. BTW)</div>
            </div>
            <div class="stat-card rood">
                <div class="getal">€ <?php echo number_format($open, 2, ',', '.'); ?></div>
                <div class="label">Openstaand (incl. BTW)</div>
            </div>
            <div class="stat-card rood">
                <div class="getal"><?php echo $aantalOpen; ?></div>
                <div class="label">Onbetaalde facturen</div>
            </div>
        </div>



        <?php if (!empty($result)): ?>
            <div style="overflow-x:auto;">
                <table class="manager-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Datum</th>
                            <th>Klant</th>
                            <th>Lodgetype</th>
                            <th>Periode</th>
                            <th>Totaal incl. BTW</th>
                            <th>Betaalstatus</th>
                            <th>Herinnering</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result as $row):
                            $totaal = $row['totaalbedragexbtw'] * (1 + $row['btwpercentage'] / 100);
                            ?>
                            <tr>
                                <td>#<?php echo $row['factuurid']; ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['factuurdatum'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['klantnaam']); ?></strong><br>
                                    <small style="color:#999;"><?php echo htmlspecialchars($row['klantemail']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['lodgetype_naam']); ?></td>
                                <td style="white-space:nowrap;">
                                    <?php echo date('d-m-Y', strtotime($row['starttijd'])); ?> t/m<br>
                                    <?php echo date('d-m-Y', strtotime($row['eindtijd'])); ?>
                                </td>
                                <td style="font-weight:600;">
                                    € <?php echo number_format($totaal, 2, ',', '.'); ?>
                                    <br><small style="color:#999;font-weight:normal;"><?php echo $row['btwpercentage']; ?>%
                                        BTW</small>
                                </td>
                                <td>
                                    <?php if ($row['betaalstatus']): ?>
                                        <span class="badge-betaald">✓ Betaald</span>
                                    <?php else: ?>
                                        <span class="badge-open">✗ Openstaand</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-herinn">
                                        <?php echo $row['herinneringsmailstatus'] ? '✓ Verstuurd' : '– Niet verstuurd'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <?php if ($row['betaalstatus']): ?>
                                            <a href="?action=markeer_open&factuurid=<?php echo $row['factuurid']; ?>">Zet open</a>
                                        <?php else: ?>
                                            <a href="?action=markeer_betaald&factuurid=<?php echo $row['factuurid']; ?>">Markeer
                                                betaald</a>
                                        <?php endif; ?>
                                        <?php if ($row['herinneringsmailstatus']): ?>
                                            <a href="?action=herinnering_reset&factuurid=<?php echo $row['factuurid']; ?>">Herinnering
                                                resetten</a>
                                        <?php else: ?>
                                            <a href="?action=herinnering_verstuurd&factuurid=<?php echo $row['factuurid']; ?>">Herinnering
                                                markeren</a>
                                        <?php endif; ?>
                                        <a href="print.php?factuurid=<?php echo $row['factuurid']; ?>" target="_blank"
                                            class="print">Afdrukken</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Geen facturen gevonden voor dit filter.</div>
        <?php endif; ?>
    </div>
</body>

</html>