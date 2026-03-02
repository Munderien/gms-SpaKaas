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
if ($_SESSION['rol'] != 3) {
    die('Geen toegang');
}

$uurloon = 15;

$weekStart = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d', strtotime('monday this week'));
$weekEind = date('Y-m-d', strtotime('+6 days', strtotime($weekStart)));

$vorigeWeek = date('Y-m-d', strtotime('-7 days', strtotime($weekStart)));
$volgendeWeek = date('Y-m-d', strtotime('+7 days', strtotime($weekStart)));

$medewerkers = $db->query("
    SELECT gebruikerid, naam, rol
    FROM gebruiker
    WHERE rol IN (1, 2, 3) AND isactief = 1
    ORDER BY naam
")->fetchAll(PDO::FETCH_ASSOC);

$dagNamen = ['Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag', 'Zondag'];

$totaalUren = 0;
$totaalKosten = 0;
$perMedewerker = [];

foreach ($medewerkers as $m) {
    $mId = $m['gebruikerid'];
    $weekUren = 0;
    $dagen = [];

    for ($d = 0; $d < 7; $d++) {
        $datum = date('Y-m-d', strtotime("+$d days", strtotime($weekStart)));
        $dagNr = $d + 1;

        $stmtWerk = $db->prepare("
            SELECT begintijd, eindtijd
            FROM werkuur
            WHERE gebruikerid = ?
              AND DATE(begintijd) = ?
        ");
        $stmtWerk->execute([$mId, $datum]);
        $werkuur = $stmtWerk->fetch(PDO::FETCH_ASSOC);

        if ($werkuur) {
            $start = new DateTime($werkuur['begintijd']);
            $eind = new DateTime($werkuur['eindtijd']);
            $uren = round(($eind->getTimestamp() - $start->getTimestamp()) / 3600, 2);
        } else {
            $stmtSchema = $db->prepare("
                SELECT starttijd, eindtijd
                FROM weekschema
                WHERE gebruikerid = ?
                  AND dag_van_week = ?
            ");
            $stmtSchema->execute([$mId, $dagNr]);
            $schema = $stmtSchema->fetch(PDO::FETCH_ASSOC);

            if ($schema) {
                $start = new DateTime($schema['starttijd']);
                $eind = new DateTime($schema['eindtijd']);
                $uren = round(($eind->getTimestamp() - $start->getTimestamp()) / 3600, 2);
            } else {
                $uren = 0;
            }
        }

        $dagen[] = [
            'dag' => $dagNamen[$d],
            'datum' => $datum,
            'uren' => $uren
        ];
        $weekUren += $uren;
    }

    $weekKosten = $weekUren * $uurloon;
    $totaalUren += $weekUren;
    $totaalKosten += $weekKosten;

    $rolNaam = '';
    if ($m['rol'] == 1)
        $rolNaam = 'Balie';
    if ($m['rol'] == 2)
        $rolNaam = 'Monteur';
    if ($m['rol'] == 3)
        $rolNaam = 'Manager';

    $perMedewerker[] = [
        'naam' => $m['naam'],
        'rol' => $rolNaam,
        'dagen' => $dagen,
        'weekUren' => $weekUren,
        'weekKosten' => $weekKosten
    ];
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapportage Personeel – SpaKaas</title>
    <link rel="stylesheet" href="/dms-spakaas/gms-SpaKaas/Style/manager.css">
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

        .stat-card.oranje {
            border-color: #f39c12;
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

        .sectie-titel {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 32px 0 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #ecf0f1;
        }

        .medewerker-blok {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .medewerker-blok h3 {
            margin: 0 0 4px;
            font-size: 1rem;
            color: #2c3e50;
        }

        .medewerker-blok .rol-label {
            font-size: .8rem;
            color: #7f8c8d;
            margin-bottom: 12px;
        }

        .medewerker-blok .totaal-rij {
            font-weight: 700;
            background: #f0f4f8;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <div class="manager-container">
        <h1>Rapportage – Personeelskosten</h1>

        <div class="week-nav">
            <a href="?week=<?php echo $vorigeWeek; ?>">Vorige week</a>
            <span>
                <?php echo date('d-m-Y', strtotime($weekStart)); ?>
                t/m
                <?php echo date('d-m-Y', strtotime($weekEind)); ?>
            </span>
            <a href="?week=<?php echo $volgendeWeek; ?>">Volgende week</a>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="getal"><?php echo count($medewerkers); ?></div>
                <div class="label">Medewerkers</div>
            </div>
            <div class="stat-card groen">
                <div class="getal"><?php echo number_format($totaalUren, 1, ',', '.'); ?></div>
                <div class="label">Totaal uren deze week</div>
            </div>
            <div class="stat-card oranje">
                <div class="getal">&euro; <?php echo number_format($totaalKosten, 2, ',', '.'); ?></div>
                <div class="label">Totale kosten (&euro;<?php echo $uurloon; ?>/uur)</div>
            </div>
        </div>

        <p class="sectie-titel">Overzicht per medewerker</p>

        <?php if (empty($perMedewerker)): ?>
            <div class="empty-state">Geen medewerkers gevonden.</div>
        <?php else: ?>
            <?php foreach ($perMedewerker as $mw): ?>
                <div class="medewerker-blok">
                    <h3><?php echo htmlspecialchars($mw['naam']); ?></h3>
                    <div class="rol-label"><?php echo $mw['rol']; ?></div>
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Dag</th>
                                <th>Datum</th>
                                <th>Uren</th>
                                <th>Kosten</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mw['dagen'] as $dag): ?>
                                <tr>
                                    <td><?php echo $dag['dag']; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($dag['datum'])); ?></td>
                                    <td>
                                        <?php echo $dag['uren'] > 0
                                            ? number_format($dag['uren'], 1, ',', '.') . ' uur'
                                            : '–'; ?>
                                    </td>
                                    <td>
                                        <?php echo $dag['uren'] > 0
                                            ? '&euro; ' . number_format($dag['uren'] * $uurloon, 2, ',', '.')
                                            : '–'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="totaal-rij">
                                <td>Totaal</td>
                                <td></td>
                                <td><?php echo number_format($mw['weekUren'], 1, ',', '.'); ?> uur</td>
                                <td>&euro; <?php echo number_format($mw['weekKosten'], 2, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <p class="sectie-titel">Totaaloverzicht</p>
        <table class="manager-table">
            <thead>
                <tr>
                    <th>Medewerker</th>
                    <th>Rol</th>
                    <th>Uren</th>
                    <th>Kosten</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($perMedewerker as $mw): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($mw['naam']); ?></td>
                        <td><?php echo $mw['rol']; ?></td>
                        <td><?php echo number_format($mw['weekUren'], 1, ',', '.'); ?> uur</td>
                        <td>&euro; <?php echo number_format($mw['weekKosten'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="font-weight:700;background:#f0f4f8;">
                    <td>Totaal</td>
                    <td></td>
                    <td><?php echo number_format($totaalUren, 1, ',', '.'); ?> uur</td>
                    <td>&euro; <?php echo number_format($totaalKosten, 2, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</body></html>