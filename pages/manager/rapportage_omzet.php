<?php
require '../config.php';
session_start();


if (!isset($_SESSION['gebruikerId'])) {
    header('Location: /dms-spakaas/gms-SpaKaas/pages/inlog.php');
    exit;
}
$stmt = $db->prepare("SELECT rol FROM gebruiker WHERE gebruikerid = ?");
$stmt->execute([$_SESSION['gebruikerId']]);
$_SESSION['rol'] = (int) $stmt->fetchColumn();
if ($_SESSION['rol'] != 3) {
    die('Geen toegang');
}

$ditJaar = (int) date('Y');
$jaar = isset($_GET['jaar']) ? (int) $_GET['jaar'] : $ditJaar;

$jaren = $db->query("SELECT DISTINCT YEAR(factuurdatum) AS j FROM factuur ORDER BY j DESC")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($ditJaar, $jaren))
    array_unshift($jaren, $ditJaar);

$maandNamen = ['Jan', 'Feb', 'Mrt', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'];

$stmt = $db->prepare("
    SELECT MONTH(factuurdatum) AS maand,
           SUM(totaalbedragexbtw * (1 + btwpercentage/100)) AS omzet_incl,
           SUM(CASE WHEN betaalstatus=1
                    THEN totaalbedragexbtw * (1 + btwpercentage/100)
                    ELSE 0 END) AS ontvangen,
           COUNT(*) AS aantal
    FROM factuur
    WHERE YEAR(factuurdatum) = ?
    GROUP BY MONTH(factuurdatum)
    ORDER BY MONTH(factuurdatum)
");
$stmt->execute([$jaar]);
$omzetRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$perMaand = array_fill(1, 12, ['omzet_incl' => 0, 'ontvangen' => 0, 'aantal' => 0]);
foreach ($omzetRaw as $r)
    $perMaand[(int) $r['maand']] = $r;

$totOmzet = array_sum(array_column($perMaand, 'omzet_incl'));
$totOntv = array_sum(array_column($perMaand, 'ontvangen'));
$totFact = array_sum(array_column($perMaand, 'aantal'));
$maxOmzet = max(array_column($perMaand, 'omzet_incl') ?: [1]);

$stmt2 = $db->prepare("
    SELECT lt.naam,
           COUNT(DISTINCT l.lodgeid) AS aantal_lodges,
           COALESCE(SUM(
               DATEDIFF(
                   LEAST(a.eindtijd, CONCAT(?, '-12-31')),
                   GREATEST(a.starttijd, CONCAT(?, '-01-01'))
               )
           ), 0) AS geboekte_dagen
    FROM lodgetype lt
    LEFT JOIN lodge l ON l.lodgetypeid = lt.typeid
    LEFT JOIN afspraak a ON a.lodgeid = l.lodgeid
        AND YEAR(a.starttijd) <= ?
        AND YEAR(a.eindtijd)  >= ?
        AND a.starttijd < a.eindtijd
    GROUP BY lt.typeid, lt.naam
    ORDER BY lt.naam
");
$stmt2->execute([$jaar, $jaar, $jaar, $jaar]);
$bezData = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$dagenJaar = (date('L', mktime(0, 0, 0, 1, 1, $jaar)) ? 366 : 365);

$stmt3 = $db->prepare("
    SELECT lt.typeid,
           lt.naam AS typename,
           lt.prijs AS huidige_prijs,
           COUNT(f.factuurid) AS boekingen,
           AVG(f.totaalbedragexbtw) AS gem_factuur_exbtw,
           MIN(f.factuurdatum) AS eerste_factuur,
           MAX(f.factuurdatum) AS laatste_factuur
    FROM lodgetype lt
    LEFT JOIN factuur f ON f.lodgetypeid = lt.typeid
    GROUP BY lt.typeid, lt.naam, lt.prijs
    ORDER BY lt.naam
");
$stmt3->execute();
$prijsData = $stmt3->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapportage Omzet – SpaKaas</title>
    <link rel="stylesheet" href="../../Style/manager.css">
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 16px;
            margin: 20px 0 32px;
        }

        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            border-top: 4px solid #3498db;
        }

        .stat-card.groen {
            border-color: #27ae60;
        }

        .stat-card.rood {
            border-color: #e74c3c;
        }

        .stat-card.oranje {
            border-color: #f39c12;
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


        .chart-wrap {
            overflow-x: auto;
            margin: 16px 0 8px;
        }

        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            height: 180px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 4px;
            min-width: 560px;
        }

        .bar-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .bar {
            width: 100%;
            background: linear-gradient(180deg, #3498db, #2471a3);
            border-radius: 4px 4px 0 0;
            min-height: 3px;
            position: relative;
        }

        .bar.ontvangen {
            background: linear-gradient(180deg, #27ae60, #1e8449);
        }

        .bar .tip {
            display: none;
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: #2c3e50;
            color: #fff;
            font-size: .7rem;
            padding: 3px 7px;
            border-radius: 4px;
            white-space: nowrap;
        }

        .bar:hover .tip {
            display: block;
        }

        .bar-label {
            font-size: .7rem;
            color: #7f8c8d;
            margin-top: 6px;
        }


        .legend {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            font-size: .82rem;
        }

        .legend span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        .dot-blauw {
            background: #3498db;
        }

        .dot-groen {
            background: #27ae60;
        }


        .progress {
            background: #ecf0f1;
            border-radius: 20px;
            height: 10px;
            overflow: hidden;
            min-width: 80px;
        }

        .progress-bar {
            height: 100%;
            border-radius: 20px;
            transition: width .3s;
        }

        .sectie-titel {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 36px 0 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #ecf0f1;
        }


        .filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>
    <?php include '../../navbar.php'; ?>

    <div class="manager-container" style="max-width:1000px;">
        <h1>Rapportage – Omzet, Bezetting &amp; Prijzen</h1>

        <form method="get" class="filter-bar">
            <label for="jaar" style="font-weight:600;">Jaar:</label>
            <select id="jaar" name="jaar" onchange="this.form.submit()"
                style="padding:6px 10px;border:1px solid #ccc;border-radius:6px;">
                <?php foreach ($jaren as $j): ?>
                    <option value="<?php echo $j; ?>" <?php if ($j == $jaar)
                           echo 'selected'; ?>>
                        <?php echo $j; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <p class="sectie-titel">Omzet
            <?php echo $jaar; ?>
        </p>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="getal">
                    <?php echo $totFact; ?>
                </div>
                <div class="label">Facturen
                    <?php echo $jaar; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="getal">€
                    <?php echo number_format($totOmzet, 0, ',', '.'); ?>
                </div>
                <div class="label">Totale omzet (incl. BTW)</div>
            </div>
            <div class="stat-card groen">
                <div class="getal">€
                    <?php echo number_format($totOntv, 0, ',', '.'); ?>
                </div>
                <div class="label">Ontvangen</div>
            </div>
            <div class="stat-card rood">
                <div class="getal">€
                    <?php echo number_format($totOmzet - $totOntv, 0, ',', '.'); ?>
                </div>
                <div class="label">Nog openstaand</div>
            </div>
        </div>

        <div class="legend">
            <span><span class="dot dot-blauw"></span>Totale omzet incl. BTW</span>
            <span><span class="dot dot-groen"></span>Ontvangen</span>
        </div>
        <div class="chart-wrap">
            <div class="bar-chart">
                <?php for ($m = 1; $m <= 12; $m++):
                    $omzet = (float) $perMaand[$m]['omzet_incl'];
                    $ontvangen = (float) $perMaand[$m]['ontvangen'];
                    $hoogteOmzet = $maxOmzet > 0 ? round(($omzet / $maxOmzet) * 155) : 0;
                    $hoogteOntv = $maxOmzet > 0 ? round(($ontvangen / $maxOmzet) * 155) : 0;
                    ?>
                    <div class="bar-col">
                        <div class="bar" style="height:<?php echo $hoogteOmzet; ?>px;">
                            <span class="tip">€
                                <?php echo number_format($omzet, 0, ',', '.'); ?>
                            </span>
                        </div>
                        <div class="bar ontvangen" style="height:<?php echo $hoogteOntv; ?>px; margin-top:2px;">
                            <span class="tip">Ontvangen: €
                                <?php echo number_format($ontvangen, 0, ',', '.'); ?>
                            </span>
                        </div>
                        <div class="bar-label">
                            <?php echo $maandNamen[$m - 1]; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>


        <table class="manager-table" style="margin-top:16px;">
            <thead>
                <tr>
                    <th>Maand</th>
                    <th>Facturen</th>
                    <th>Omzet incl. BTW</th>
                    <th>Ontvangen</th>
                    <th>Openstaand</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totaalFacturen = $totaalOmzet = $totaalOntv = 0;
                for ($m = 1; $m <= 12; $m++):
                    $maandData = $perMaand[$m];
                    $omzet = (float) $maandData['omzet_incl'];
                    $ontvangen = (float) $maandData['ontvangen'];
                    $openstaand = $omzet - $ontvangen;
                    $totaalFacturen += $maandData['aantal'];
                    $totaalOmzet += $omzet;
                    $totaalOntv += $ontvangen;
                    ?>
                    <tr>
                        <td>
                            <?php echo $maandNamen[$m - 1]; ?>
                            <?php echo $jaar; ?>
                        </td>
                        <td>
                            <?php echo $maandData['aantal'] ?: '–'; ?>
                        </td>
                        <td>
                            <?php echo $omzet > 0 ? '€ ' . number_format($omzet, 2, ',', '.') : '–'; ?>
                        </td>
                        <td style="color:#27ae60;font-weight:<?php echo $ontvangen > 0 ? '600' : '400'; ?>">
                            <?php echo $ontvangen > 0 ? '€ ' . number_format($ontvangen, 2, ',', '.') : '–'; ?>
                        </td>
                        <td style="color:<?php echo $openstaand > 0 ? '#e74c3c' : '#999'; ?>">
                            <?php echo $openstaand > 0 ? '€ ' . number_format($openstaand, 2, ',', '.') : '–'; ?>
                        </td>
                    </tr>
                <?php endfor; ?>
                <tr style="font-weight:700;background:#f0f4f8;">
                    <td>Totaal</td>
                    <td>
                        <?php echo $totaalFacturen; ?>
                    </td>
                    <td>€
                        <?php echo number_format($totaalOmzet, 2, ',', '.'); ?>
                    </td>
                    <td style="color:#27ae60;">€
                        <?php echo number_format($totaalOntv, 2, ',', '.'); ?>
                    </td>
                    <td style="color:#e74c3c;">€
                        <?php echo number_format($totaalOmzet - $totaalOntv, 2, ',', '.'); ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="sectie-titel">Bezettingsgraad per lodgetype –
            <?php echo $jaar; ?>
        </p>
        <table class="manager-table">
            <thead>
                <tr>
                    <th>Lodgetype</th>
                    <th>Aantal lodges</th>
                    <th>Beschikbare dagen</th>
                    <th>Geboekte dagen</th>
                    <th>Bezettingsgraad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bezData as $type):
                    $beschikbaar = (int) $type['aantal_lodges'] * $dagenJaar;
                    $geboekt = max(0, (int) $type['geboekte_dagen']);
                    $percentage = $beschikbaar > 0 ? min(100, round($geboekt / $beschikbaar * 100)) : 0;
                    $barKleur = $percentage >= 70 ? '#27ae60' : ($percentage >= 40 ? '#f39c12' : '#3498db');
                    ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($type['naam']); ?>
                        </td>
                        <td>
                            <?php echo $type['aantal_lodges']; ?>
                        </td>
                        <td>
                            <?php echo number_format($beschikbaar, 0, ',', '.'); ?>
                        </td>
                        <td>
                            <?php echo number_format($geboekt, 0, ',', '.'); ?>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div class="progress" style="flex:1;">
                                    <div class="progress-bar"
                                        style="width:<?php echo $percentage; ?>%;background:<?php echo $barKleur; ?>;">
                                    </div>
                                </div>
                                <strong style="color:<?php echo $barKleur; ?>;min-width:38px;">
                                    <?php echo $percentage; ?>%
                                </strong>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


        <p class="sectie-titel"> Prijsoverzicht per lodgetype</p>
        <p style="color:#7f8c8d;font-size:.85rem;margin-bottom:16px;">
            Huidige prijs per nacht
        </p>
        <table class="manager-table">
            <thead>
                <tr>
                    <th>Lodgetype</th>
                    <th>Huidige prijs/nacht</th>
                    <th>Totaal boekingen</th>
                    <th>Gem. factuurbedrag (excl. BTW)</th>
                    <th>Eerste boeking</th>
                    <th>Laatste boeking</th>
                    <th>Actie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prijsData as $p): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($p['typename']); ?>
                        </td>
                        <td style="font-weight:700;color:#2c3e50;">
                            €
                            <?php echo number_format((float) $p['huidige_prijs'], 2, ',', '.'); ?>
                        </td>
                        <td>
                            <?php echo $p['boekingen'] ?: '–'; ?>
                        </td>
                        <td>
                            <?php echo $p['boekingen'] > 0
                                ? '€ ' . number_format((float) $p['gem_factuur_exbtw'], 2, ',', '.')
                                : '–'; ?>
                        </td>
                        <td>
                            <?php echo $p['eerste_factuur']
                                ? date('d-m-Y', strtotime($p['eerste_factuur']))
                                : '–'; ?>
                        </td>
                        <td>
                            <?php echo $p['laatste_factuur']
                                ? date('d-m-Y', strtotime($p['laatste_factuur']))
                                : '–'; ?>
                        </td>
                        <td>
                            <a href="lodge/type_bewerken.php?id=<?php echo $p['typeid']; ?>"
                                class="btn btn-warning btn-sm">Prijs wijzigen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</body>

</html>