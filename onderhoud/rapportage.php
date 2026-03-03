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

$totaal = $db->query("SELECT COUNT(*) FROM onderhoud")->fetchColumn();
$open = $db->query("SELECT COUNT(*) FROM onderhoud WHERE status = 'open'")->fetchColumn();
$bezig = $db->query("SELECT COUNT(*) FROM onderhoud WHERE status = 'in_progress'")->fetchColumn();
$gesloten = $db->query("SELECT COUNT(*) FROM onderhoud WHERE status = 'gesloten'")->fetchColumn();

$perLodge = $db->query("
    SELECT l.huisnummer, lt.naam AS typename,
           COUNT(o.onderhoudid) AS totaal,
           SUM(CASE WHEN o.status = 'open' THEN 1 ELSE 0 END) AS open_taken,
           SUM(CASE WHEN o.status = 'in_progress' THEN 1 ELSE 0 END) AS bezig_taken,
           SUM(CASE WHEN o.status = 'gesloten' THEN 1 ELSE 0 END) AS gesloten_taken
    FROM onderhoud o
    JOIN lodge l ON l.lodgeid = o.lodgeid
    JOIN lodgetype lt ON lt.typeid = l.lodgetypeid
    GROUP BY l.lodgeid, l.huisnummer, lt.naam
    ORDER BY open_taken DESC
")->fetchAll(PDO::FETCH_ASSOC);

$perMonteur = $db->query("
    SELECT g.naam,
           COUNT(o.onderhoudid) AS totaal,
           SUM(CASE WHEN o.status = 'open' THEN 1 ELSE 0 END) AS open_taken,
           SUM(CASE WHEN o.status = 'in_progress' THEN 1 ELSE 0 END) AS bezig_taken,
           SUM(CASE WHEN o.status = 'gesloten' THEN 1 ELSE 0 END) AS gesloten_taken
    FROM onderhoud o
    JOIN gebruiker g ON g.gebruikerid = o.monteurid
    GROUP BY o.monteurid, g.naam
    ORDER BY open_taken DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapportage Onderhoud – SpaKaas</title>
    <link rel="stylesheet" href="../Style/manager.css">
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

        .sectie-titel {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 32px 0 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #ecf0f1;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <div class="manager-container">
        <h1>Rapportage – Onderhoud</h1>

        <div class="stat-grid">
            <div class="stat-card rood">
                <div class="getal">
                    <?php echo $open; ?>
                </div>
                <div class="label">Open</div>
            </div>
            <div class="stat-card oranje">
                <div class="getal">
                    <?php echo $bezig; ?>
                </div>
                <div class="label">In behandeling</div>
            </div>
            <div class="stat-card groen">
                <div class="getal">
                    <?php echo $gesloten; ?>
                </div>
                <div class="label">Gesloten</div>
            </div>
            <div class="stat-card">
                <div class="getal">
                    <?php echo $totaal; ?>
                </div>
                <div class="label">Totaal taken</div>
            </div>
        </div>

        <p class="sectie-titel">Per lodge</p>
        <?php if (empty($perLodge)): ?>
            <div class="empty-state">Geen onderhoudsdata.</div>
        <?php else: ?>
            <table class="manager-table">
                <thead>
                    <tr>
                        <th>Lodge</th>
                        <th>Totaal</th>
                        <th style="color:#e74c3c;">Open</th>
                        <th style="color:#f39c12;">In behandeling</th>
                        <th style="color:#27ae60;">Gesloten</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perLodge as $l): ?>
                        <tr>
                            <td>
                                <?php echo $l['huisnummer'] . ' – ' . htmlspecialchars($l['typename']); ?>
                            </td>
                            <td>
                                <?php echo $l['totaal']; ?>
                            </td>
                            <td style="color:#e74c3c;font-weight:600;">
                                <?php echo $l['open_taken']; ?>
                            </td>
                            <td style="color:#f39c12;font-weight:600;">
                                <?php echo $l['bezig_taken']; ?>
                            </td>
                            <td style="color:#27ae60;font-weight:600;">
                                <?php echo $l['gesloten_taken']; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p class="sectie-titel">Per monteur</p>
        <?php if (empty($perMonteur)): ?>
            <div class="empty-state">Geen monteurs met taken.</div>
        <?php else: ?>
            <table class="manager-table">
                <thead>
                    <tr>
                        <th>Monteur</th>
                        <th>Totaal</th>
                        <th style="color:#e74c3c;">Open</th>
                        <th style="color:#f39c12;">In behandeling</th>
                        <th style="color:#27ae60;">Gesloten</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perMonteur as $m): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($m['naam']); ?>
                            </td>
                            <td>
                                <?php echo $m['totaal']; ?>
                            </td>
                            <td style="color:#e74c3c;font-weight:600;">
                                <?php echo $m['open_taken']; ?>
                            </td>
                            <td style="color:#f39c12;font-weight:600;">
                                <?php echo $m['bezig_taken']; ?>
                            </td>
                            <td style="color:#27ae60;font-weight:600;">
                                <?php echo $m['gesloten_taken']; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>