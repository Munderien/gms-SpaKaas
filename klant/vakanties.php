<?php
require '../pages/config.php';
session_start();

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: ../pages/inlog.php');
    exit;
}

$gebruikerId = $_SESSION['gebruikerId'];

$vakanties = $db->prepare("
    SELECT a.afspraakid, a.starttijd, a.eindtijd, a.status,
           a.aantalmensen, a.toelichting,
           l.huisnummer, lt.naam AS typename
    FROM afspraak a
    JOIN lodge l ON l.lodgeid = a.lodgeid
    JOIN lodgetype lt ON lt.lodgetypeid = l.typeid
    WHERE a.gebruikerid = ?
    ORDER BY a.starttijd DESC
");
$vakanties->execute([$gebruikerId]);
$result = $vakanties->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn Vakanties – SpaKaas</title>
    <link rel="stylesheet" href="../Style/manager.css">
    <style>
        .vakantie-kaart {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .vakantie-kaart h3 {
            margin: 0 0 6px;
            font-size: 1rem;
            color: #2c3e50;
        }

        .vakantie-kaart p {
            margin: 2px 0;
            font-size: .85rem;
            color: #7f8c8d;
        }

        .vakantie-kaart .lodge-naam {
            font-weight: 600;
            color: #3498db;
        }

        .vakantie-kaart .periode {
            font-weight: 600;
            color: #2c3e50;
        }

        .status-bevestigd {
            color: #27ae60;
            font-weight: 600;
        }

        .status-geannuleerd {
            color: #e74c3c;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <div class="manager-container">
        <h1>Mijn Vakanties</h1>

        <?php if (empty($result)): ?>
            <div class="empty-state">Je hebt nog geen vakanties geboekt.</div>
        <?php else: ?>
            <?php foreach ($result as $v): ?>
                <?php
                $start = new DateTime($v['starttijd']);
                $eind = new DateTime($v['eindtijd']);
                $nachten = (int) $start->diff($eind)->days;
                if ($nachten < 1)
                    $nachten = 1;
                ?>
                <div class="vakantie-kaart">
                    <h3>
                        <?php echo htmlspecialchars($v['titel']); ?>
                    </h3>
                    <p class="lodge-naam">Lodge
                        <?php echo $v['huisnummer']; ?> –
                        <?php echo htmlspecialchars($v['typename']); ?>
                    </p>
                    <p class="periode">
                        <?php echo $start->format('d-m-Y'); ?> t/m
                        <?php echo $eind->format('d-m-Y'); ?>
                        (
                        <?php
                        echo $nachten . ' nacht';
                        if ($nachten > 1) {
                            echo 'en';
                        }
                        ?>)
                    </p>
                    <p>Personen:
                        <?php echo $v['aantalmensen']; ?>
                    </p>
                    <?php if ($v['toelichting'] != ''): ?>
                        <p>Toelichting:
                            <?php echo htmlspecialchars($v['toelichting']); ?>
                        </p>
                    <?php endif; ?>
                    <p>
                        Status:
                        <?php if ($v['status'] == 'bevestigd'): ?>
                            <span class="status-bevestigd">Bevestigd</span>
                        <?php elseif ($v['status'] == 'geannuleerd'): ?>
                            <span class="status-geannuleerd">Geannuleerd</span>
                        <?php else: ?>
                            <?php echo htmlspecialchars($v['status']); ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>