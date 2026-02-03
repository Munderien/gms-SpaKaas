<?php
require '../config.php'; 

if (!isset($_GET['factuurid'])) {
    die('Geen factuur opgegeven.');
}

$factuurid = (int)$_GET['factuurid'];

$sql = "
    SELECT 
        f.factuurid,
        f.factuurdatum,
        f.totaalbedragexbtw,
        f.btwpercentage,
        f.betaalstatus,
        f.aantalmensen,
        f.toelichting,
        g.naam AS klantnaam,
        g.adres AS klantadres,
        g.plaats AS klantplaats,
        g.email AS klantemail,
        l.naam AS lodgetype_naam,
        a.starttijd,
        a.eindtijd
    FROM factuur f
    INNER JOIN gebruiker g ON f.gebruikerid = g.gebruikerid
    INNER JOIN lodgetype l ON f.lodgetypeid = l.typeid
    INNER JOIN afspraak a ON f.afspraakid = a.afspraakid
    WHERE f.factuurid = :id
";

$stmt = $db->prepare($sql);
$stmt->execute([':id' => $factuurid]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Factuur niet gevonden.');
}

$totaalExcl = (float)$row['totaalbedragexbtw'];
$btwPerc = (float)$row['btwpercentage'];
$btwBedrag = $totaalExcl * ($btwPerc / 100);
$totaalIncl = $totaalExcl + $btwBedrag;

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Factuur #<?php echo htmlspecialchars($row['factuurid']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f5f7fa;
            color: #222;
        }

        .factuur-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            padding: 28px;
        }

        .factuur-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .factuur-kop {
            font-size: 26px;
            font-weight: bold;
            color: #111827;
        }

        .factuur-sectie {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .factuur-sectie strong {
            display: block;
            margin-bottom: 6px;
            color: #111827;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
            font-size: 14px;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
        }

        th {
            background-color: #f9fafb;
            color: #111827;
            font-weight: 600;
        }

        .right {
            text-align: right;
        }

        .no-print {
            margin-bottom: 16px;
        }

        .no-print button,
        .no-print a {
            font-size: 14px;
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #cbd2ff;
            background-color: #e5edff;
            color: #111827;
            text-decoration: none;
            cursor: pointer;
        }

        .no-print a {
            margin-left: 8px;
        }

        .no-print button:hover,
        .no-print a:hover {
            background-color: #d0d7ff;
        }

        @media print {
            body {
                margin: 0;
                background-color: #ffffff;
            }

            .no-print {
                display: none;
            }

            .factuur-container {
                box-shadow: none;
                border-radius: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print();">Print</button>
    <a href="list.php">Terug naar overzicht</a>
</div>

<div class="factuur-container">

<div class="factuur-header">
    <div>
        <div class="factuur-kop">Factuur</div>
        <div>Factuurnummer: <?php echo htmlspecialchars($row['factuurid']); ?></div>
        <div>Factuurdatum: <?php echo htmlspecialchars($row['factuurdatum']); ?></div>
    </div>
    <div>
        <strong>SpaKaas Resort</strong><br>
        testsstraat 123<br>
        1234 AB SpKaas<br>
        info@spakaas.nl
    </div>
</div>

<div class="factuur-sectie">
    <strong>Gefactureerd aan:</strong><br>
    <?php echo htmlspecialchars($row['klantnaam']); ?><br>
    <?php echo htmlspecialchars($row['klantadres']); ?><br>
    <?php echo htmlspecialchars($row['klantplaats']); ?><br>
    <?php echo htmlspecialchars($row['klantemail']); ?>
</div>

<div class="factuur-sectie">
    <strong>Afspraakgegevens:</strong><br>
    Lodgetype: <?php echo htmlspecialchars($row['lodgetype_naam']); ?><br>
    Start: <?php echo htmlspecialchars($row['starttijd']); ?><br>
    Einde: <?php echo htmlspecialchars($row['eindtijd']); ?><br>
    Aantal personen: <?php echo htmlspecialchars($row['aantalmensen']); ?>
</div>

<div class="factuur-sectie">
    <strong>Specificatie:</strong>
    <table>
        <thead>
        <tr>
            <th>Omschrijving</th>
            <th class="right">Bedrag excl. btw</th>
            <th class="right">BTW %</th>
            <th class="right">BTW bedrag</th>
            <th class="right">Totaal incl. btw</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php echo htmlspecialchars($row['toelichting']); ?></td>
            <td class="right">&euro; <?php echo number_format($totaalExcl, 2, ',', '.'); ?></td>
            <td class="right"><?php echo htmlspecialchars($row['btwpercentage']); ?>%</td>
            <td class="right">&euro; <?php echo number_format($btwBedrag, 2, ',', '.'); ?></td>
            <td class="right">&euro; <?php echo number_format($totaalIncl, 2, ',', '.'); ?></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="factuur-sectie" style="margin-top: 30px;">
    <strong>Betalingsstatus:</strong>
    <?php echo $row['betaalstatus'] ? 'Betaald' : 'Openstaand'; ?>
    </div>

</div>

</body>
</html>
