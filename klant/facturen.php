<?php
require '../pages/config.php';
session_start();

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: /dms-spakaas/gms-SpaKaas/pages/inlog.php');
    exit;
}

$gebruikerId = $_SESSION['gebruikerId'];

$facturen = $db->prepare("
    SELECT f.factuurid, f.factuurdatum, f.totaalbedragexbtw, f.btwpercentage,
           f.betaalstatus, f.aantalmensen, f.toelichting,
           lt.naam AS lodgetype_naam,
           a.starttijd, a.eindtijd
    FROM factuur f
    JOIN lodgetype lt ON f.lodgetypeid = lt.typeid
    JOIN afspraak a ON f.afspraakid = a.afspraakid
    WHERE f.gebruikerid = ?
    ORDER BY f.factuurdatum DESC
");
$facturen->execute([$gebruikerId]);
$result = $facturen->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn Facturen – SpaKaas</title>
    <link rel="stylesheet" href="/dms-spakaas/gms-SpaKaas/Style/manager.css">
    <style>
        .factuur-kaart {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .factuur-info h3 {
            margin: 0 0 4px;
            font-size: 1rem;
            color: #2c3e50;
        }

        .factuur-info p {
            margin: 2px 0;
            font-size: .85rem;
            color: #7f8c8d;
        }

        .factuur-bedrag {
            text-align: right;
        }

        .factuur-bedrag .bedrag {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .factuur-bedrag .btw {
            font-size: .75rem;
            color: #7f8c8d;
        }

        .status-betaald {
            color: #27ae60;
            font-weight: 600;
            font-size: .85rem;
        }

        .status-open {
            color: #e74c3c;
            font-weight: 600;
            font-size: .85rem;
        }

        .btn-bekijk {
            padding: 6px 14px;
            background: #3498db;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-size: .8rem;
        }

        .btn-bekijk:hover {
            background: #2980b9;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <div class="manager-container">
        <h1>Mijn Facturen</h1>

        <?php if (empty($result)): ?>
            <div class="empty-state">Je hebt nog geen facturen.</div>
        <?php else: ?>
            <?php foreach ($result as $f): ?>
                <?php
                $excl = (float) $f['totaalbedragexbtw'];
                $btw = (float) $f['btwpercentage'];
                $incl = $excl + ($excl * $btw / 100);
                ?>
                <div class="factuur-kaart">
                    <div class="factuur-info">
                        <h3>Factuur #
                            <?php echo $f['factuurid']; ?> –
                            <?php echo htmlspecialchars($f['lodgetype_naam']); ?>
                        </h3>
                        <p>Datum:
                            <?php echo date('d-m-Y', strtotime($f['factuurdatum'])); ?>
                        </p>
                        <p>Periode:
                            <?php echo date('d-m-Y', strtotime($f['starttijd'])); ?> t/m
                            <?php echo date('d-m-Y', strtotime($f['eindtijd'])); ?>
                        </p>
                        <p>Personen:
                            <?php echo $f['aantalmensen']; ?>
                        </p>
                        <p>
                            <?php if ($f['betaalstatus']): ?>
                                <span class="status-betaald">Betaald</span>
                            <?php else: ?>
                                <span class="status-open">Openstaand</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="factuur-bedrag">
                        <div class="bedrag">&euro;
                            <?php echo number_format($incl, 2, ',', '.'); ?>
                        </div>
                        <div class="btw">excl. BTW: &euro;
                            <?php echo number_format($excl, 2, ',', '.'); ?>
                        </div>
                        <br>
                        <a href="/dms-spakaas/gms-SpaKaas/factuur_manager/print.php?factuurid=<?php echo $f['factuurid']; ?>"
                            class="btn-bekijk">Bekijken</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>