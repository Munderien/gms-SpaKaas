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

$perType = $db->query("
    SELECT lt.naam,
           COUNT(l.lodgeid) AS totaal,
           SUM(CASE WHEN l.status = 'vrij' THEN 1 ELSE 0 END) AS vrij,
           SUM(CASE WHEN l.status = 'bezet' THEN 1 ELSE 0 END) AS bezet,
           SUM(CASE WHEN l.status = 'onderhoud' THEN 1 ELSE 0 END) AS onderhoud,
           SUM(CASE WHEN l.status = 'schoonmaak' THEN 1 ELSE 0 END) AS schoonmaak
    FROM lodgetype lt
    LEFT JOIN lodge l ON l.typeid = lt.lodgetypeid
    GROUP BY lt.lodgetypeid, lt.naam
    ORDER BY lt.naam
")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
    SELECT MONTH(starttijd) AS maand,
           COUNT(*) AS aantal_afspraken,
           COUNT(DISTINCT a.lodgeid) AS unieke_lodges
    FROM afspraak a
    WHERE YEAR(starttijd) = ?
    GROUP BY MONTH(starttijd)
    ORDER BY MONTH(starttijd)
");
$stmt->execute([$jaar]);
$perMaandRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$maandNamen = ['Jan', 'Feb', 'Mrt', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'];
$perMaand = array_fill(1, 12, ['maand' => 0, 'aantal_afspraken' => 0, 'unieke_lodges' => 0]);
foreach ($perMaandRaw as $r) {
    $perMaand[(int) $r['maand']] = $r;
}

$totLodges = array_sum(array_column($perType, 'totaal'));
$totVrij = array_sum(array_column($perType, 'vrij'));
$totBezet = array_sum(array_column($perType, 'bezet'));
$totOnderhoud = array_sum(array_column($perType, 'onderhoud'));
$totSchoonmaak = array_sum(array_column($perType, 'schoonmaak'));

$maxBoekingen = max(array_column($perMaand, 'aantal_afspraken') ?: [1]);

$jaren = $db->query("SELECT DISTINCT YEAR(starttijd) AS j FROM afspraak ORDER BY j DESC")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($ditJaar, $jaren)) {
    array_unshift($jaren, $ditJaar);
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapportage Lodges – SpaKaas</title>
    <link rel="stylesheet" href="../../Style/manager.css">
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin: 24px 0;
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

        .stat-card.paars {
            border-color: #8e44ad;
        }

        .stat-card .getal {
            font-size: 2rem;
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
            margin: 24px 0;
        }

        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            height: 200px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 4px;
            min-width: 600px;
        }

        .bar-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .bar {
            width: 100%;
            background: linear-gradient(180deg, #3498db, #2980b9);
            border-radius: 4px 4px 0 0;
            min-height: 4px;
            transition: height .3s;
            position: relative;
        }

        .bar:hover {
            background: linear-gradient(180deg, #2ecc71, #27ae60);
        }

        .bar .tip {
            display: none;
            position: absolute;
            top: -28px;
            left: 50%;
            transform: translateX(-50%);
            background: #2c3e50;
            color: #fff;
            font-size: .72rem;
            padding: 3px 7px;
            border-radius: 4px;
            white-space: nowrap;
        }

        .bar:hover .tip {
            display: block;
        }

        .bar-label {
            font-size: .72rem;
            color: #7f8c8d;
            margin-top: 6px;
        }


        .sectie-titel {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 32px 0 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #ecf0f1;
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
            background: #3498db;
            transition: width .3s;
        }

        .filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <?php include '../../navbar.php'; ?>

    <div class="manager-container">
        <h1>Rapportage – Aantal Lodges</h1>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="getal">
                    <?php echo $totLodges; ?>
                </div>
                <div class="label">Totaal lodges</div>
            </div>
            <div class="stat-card groen">
                <div class="getal">
                    <?php echo $totVrij; ?>
                </div>
                <div class="label">Vrij</div>
            </div>
            <div class="stat-card rood">
                <div class="getal">
                    <?php echo $totBezet; ?>
                </div>
                <div class="label">Bezet</div>
            </div>
            <div class="stat-card oranje">
                <div class="getal">
                    <?php echo $totOnderhoud; ?>
                </div>
                <div class="label">In onderhoud</div>
            </div>
            <div class="stat-card paars">
                <div class="getal">
                    <?php echo $totSchoonmaak; ?>
                </div>
                <div class="label">Schoonmaak</div>
            </div>
            <div class="stat-card">
                <div class="getal">
                    <?php echo count($perType); ?>
                </div>
                <div class="label">Lodgetypes</div>
            </div>
        </div>

        <p class="sectie-titel">Geboekte afspraken per maand</p>

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

        <div class="chart-wrap">
            <div class="bar-chart">
                <?php for ($m = 1; $m <= 12; $m++):
                    $antal = (int) $perMaand[$m]['aantal_afspraken'];
                    $hoogte = $maxBoekingen > 0 ? round(($antal / $maxBoekingen) * 160) : 0;
                    ?>
                    <div class="bar-col">
                        <div class="bar" style="height:<?php echo $hoogte; ?>px;">
                            <span class="tip">
                                <?php echo $antal; ?> afspraken
                            </span>
                        </div>
                        <div class="bar-label">
                            <?php echo $maandNamen[$m - 1]; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <table class="manager-table" style="margin-top:12px;">
            <thead>
                <tr>
                    <th>Maand</th>
                    <th>Afspraken</th>
                    <th>Unieke lodges geboekt</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($m = 1; $m <= 12; $m++):
                    $r = $perMaand[$m];
                    ?>
                    <tr>
                        <td>
                            <?php echo $maandNamen[$m - 1]; ?>
                            <?php echo $jaar; ?>
                        </td>
                        <td>
                            <?php echo $r['aantal_afspraken'] ?: '–'; ?>
                        </td>
                        <td>
                            <?php echo $r['unieke_lodges'] ?: '–'; ?>
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>


        <p class="sectie-titel">Lodges per lodgetype (huidige status)</p>
        <table class="manager-table">
            <thead>
                <tr>
                    <th>Lodgetype</th>
                    <th>Totaal</th>
                    <th>Vrij</th>
                    <th>Bezet</th>
                    <th>Onderhoud</th>
                    <th>Schoonmaak</th>
                    <th>Bezettingsgraad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($perType as $t):
                    $bezettingPct = $t['totaal'] > 0 ? round(($t['bezet'] / $t['totaal']) * 100) : 0;
                    ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($t['naam']); ?>
                        </td>
                        <td>
                            <?php echo $t['totaal']; ?>
                        </td>
                        <td style="color:#27ae60;font-weight:600;">
                            <?php echo $t['vrij']; ?>
                        </td>
                        <td style="color:#e74c3c;font-weight:600;">
                            <?php echo $t['bezet']; ?>
                        </td>
                        <td style="color:#f39c12;font-weight:600;">
                            <?php echo $t['onderhoud']; ?>
                        </td>
                        <td style="color:#8e44ad;font-weight:600;">
                            <?php echo $t['schoonmaak']; ?>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div class="progress" style="flex:1;">
                                    <div class="progress-bar"
                                        style="width:<?php echo $bezettingPct; ?>%;
                                    background:<?php echo $bezettingPct > 70 ? '#e74c3c' : ($bezettingPct > 40 ? '#f39c12' : '#27ae60'); ?>;">
                                    </div>
                                </div>
                                <span style="font-size:.8rem;color:#7f8c8d;min-width:32px;">
                                    <?php echo $bezettingPct; ?>%
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php include '../Gantt-Chart.php'; ?>
    </div>
</body>

</html>