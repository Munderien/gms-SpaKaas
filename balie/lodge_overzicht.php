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
    die('Geen toegang');
}

$startDatum = date('Y-m-d');
if (isset($_GET['start']) && $_GET['start'] != '') {
    $startDatum = $_GET['start'];
}
$aantalDagen = 7;

$datums = [];
for ($i = 0; $i < $aantalDagen; $i++) {
    $datums[] = date('Y-m-d', strtotime("+$i days", strtotime($startDatum)));
}

$vorigePeriode = date('Y-m-d', strtotime("-$aantalDagen days", strtotime($startDatum)));
$volgendePeriode = date('Y-m-d', strtotime("+$aantalDagen days", strtotime($startDatum)));

$lodges = $db->query("
    SELECT l.lodgeid, l.huisnummer, l.status, lt.naam AS typename
    FROM lodge l
    JOIN lodgetype lt ON lt.typeid = l.lodgetypeid
    ORDER BY l.lodgeid
")->fetchAll(PDO::FETCH_ASSOC);

$afspraken = $db->prepare("
    SELECT lodgeid, starttijd, eindtijd
    FROM afspraak
    WHERE DATE(starttijd) <= ? AND DATE(eindtijd) >= ?
");
$afspraken->execute([end($datums), $datums[0]]);
$alleAfspraken = $afspraken->fetchAll(PDO::FETCH_ASSOC);

$dagNamen = ['Zo', 'Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za'];

$totaalVrij = 0;
$totaalBezet = 0;
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lodge Beschikbaarheid – SpaKaas</title>
    <link rel="stylesheet" href="/dms-spakaas/gms-SpaKaas/Style/manager.css">
    <style>
        .week-nav {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .week-nav a {
            padding: 8px 16px;
            background: #3498db;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-size: .85rem;
        }

        .week-nav a:hover {
            background: #2980b9;
        }

        .week-nav span {
            font-weight: 600;
            color: #2c3e50;
        }

        .beschikbaarheid-tabel {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .beschikbaarheid-tabel th {
            background: #2c3e50;
            color: #fff;
            padding: 10px 8px;
            font-size: .8rem;
            text-align: center;
        }

        .beschikbaarheid-tabel th:first-child {
            text-align: left;
            padding-left: 14px;
        }

        .beschikbaarheid-tabel td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #ecf0f1;
            font-size: .85rem;
        }

        .beschikbaarheid-tabel td:first-child {
            text-align: left;
            padding-left: 14px;
            font-weight: 600;
            color: #2c3e50;
        }

        .beschikbaarheid-tabel tr:hover {
            background: #f8f9fa;
        }

        .status-vrij-cel {
            background: #d4edda;
            color: #155724;
            font-weight: 600;
            border-radius: 4px;
        }

        .status-bezet-cel {
            background: #f8d7da;
            color: #721c24;
            font-weight: 600;
            border-radius: 4px;
        }

        .status-onderhoud-cel {
            background: #fff3cd;
            color: #856404;
            font-weight: 600;
            border-radius: 4px;
        }

        .status-schoonmaak-cel {
            background: #e8daef;
            color: #6c3483;
            font-weight: 600;
            border-radius: 4px;
        }

        .legenda {
            display: flex;
            gap: 16px;
            margin: 16px 0;
            flex-wrap: wrap;
        }

        .legenda-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .8rem;
            color: #7f8c8d;
        }

        .legenda-kleur {
            width: 14px;
            height: 14px;
            border-radius: 3px;
        }

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

        .stat-card.groen {
            border-color: #27ae60;
        }

        .stat-card.rood {
            border-color: #e74c3c;
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
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <div class="manager-container">
        <h1>Lodge Beschikbaarheid</h1>

        <div class="week-nav">
            <a href="?start=<?php echo $vorigePeriode; ?>">Vorige week</a>
            <span>
                <?php echo date('d-m-Y', strtotime($datums[0])); ?>
                t/m
                <?php echo date('d-m-Y', strtotime(end($datums))); ?>
            </span>
            <a href="?start=<?php echo $volgendePeriode; ?>">Volgende week</a>
            <a href="?start=<?php echo date('Y-m-d'); ?>" style="background:#27ae60;">Vandaag</a>
        </div>

        <div class="legenda">
            <div class="legenda-item">
                <div class="legenda-kleur" style="background:#d4edda;"></div> Vrij
            </div>
            <div class="legenda-item">
                <div class="legenda-kleur" style="background:#f8d7da;"></div> Bezet
            </div>
            <div class="legenda-item">
                <div class="legenda-kleur" style="background:#fff3cd;"></div> Onderhoud
            </div>
            <div class="legenda-item">
                <div class="legenda-kleur" style="background:#e8daef;"></div> Schoonmaak
            </div>
        </div>

        <table class="beschikbaarheid-tabel">
            <thead>
                <tr>
                    <th>Lodge</th>
                    <?php foreach ($datums as $d): ?>
                        <th>
                            <?php
                            $dagNr = (int) date('w', strtotime($d));
                            echo $dagNamen[$dagNr] . '<br>' . date('d/m', strtotime($d));
                            ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lodges as $lodge): ?>
                    <tr>
                        <td>
                            <?php echo $lodge['huisnummer'] . ' – ' . htmlspecialchars($lodge['typename']); ?>
                        </td>
                        <?php foreach ($datums as $d): ?>
                            <?php
                            $bezet = false;
                            foreach ($alleAfspraken as $a) {
                                if ($a['lodgeid'] == $lodge['lodgeid']) {
                                    $aStart = date('Y-m-d', strtotime($a['starttijd']));
                                    $aEind = date('Y-m-d', strtotime($a['eindtijd']));
                                    if ($d >= $aStart && $d <= $aEind) {
                                        $bezet = true;
                                        break;
                                    }
                                }
                            }

                            if ($lodge['status'] == 'onderhoud') {
                                $klasse = 'status-onderhoud-cel';
                                $tekst = 'OH';
                            } elseif ($lodge['status'] == 'schoonmaak') {
                                $klasse = 'status-schoonmaak-cel';
                                $tekst = 'SC';
                            } elseif ($bezet) {
                                $klasse = 'status-bezet-cel';
                                $tekst = 'Bezet';
                                $totaalBezet++;
                            } else {
                                $klasse = 'status-vrij-cel';
                                $tekst = 'Vrij';
                                $totaalVrij++;
                            }
                            ?>
                            <td class="<?php echo $klasse; ?>">
                                <?php echo $tekst; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="stat-grid">
            <div class="stat-card groen">
                <div class="getal">
                    <?php echo $totaalVrij; ?>
                </div>
                <div class="label">Vrije lodge-dagen</div>
            </div>
            <div class="stat-card rood">
                <div class="getal">
                    <?php echo $totaalBezet; ?>
                </div>
                <div class="label">Bezette lodge-dagen</div>
            </div>
            <div class="stat-card">
                <div class="getal">
                    <?php echo count($lodges); ?>
                </div>
                <div class="label">Totaal lodges</div>
            </div>
        </div>
    </div>
</body>

</html>