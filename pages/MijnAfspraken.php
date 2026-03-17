<?php
session_start();
include('config.php');

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: inlog.php');
    exit;
}

$gebruikerId = (int) $_SESSION['gebruikerId'];

$stmt = $db->prepare("SELECT rol FROM gebruiker WHERE gebruikerid = ?");
$stmt->execute([$gebruikerId]);
$_SESSION['rol'] = (int) $stmt->fetchColumn();

$query = "SELECT a.afspraakid, a.starttijd, a.eindtijd, a.status, a.toelichting, a.aantalmensen,
                 lt.naam AS lodgetype, l.huisnummer
          FROM afspraak a
          INNER JOIN lodge l ON l.lodgeid = a.lodgeid
          INNER JOIN lodgetype lt ON lt.lodgetypeid = l.typeid
          WHERE a.gebruikerid = :gebruikerid
          ORDER BY a.starttijd DESC";

$stmt = $db->prepare($query);
$stmt->execute([':gebruikerid' => $gebruikerId]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn Afspraken - DMS Spakaas</title>
    <link rel="stylesheet" href="../Style/mijnAfspraken.css">
</head>

<body>
    <?php include '../navbar.php'; ?>

    <div class="container">
        <header>
            <h1>Mijn Afspraken</h1>
            <p>Bekijk al je afspraken en open details</p>
        </header>

        <div class="content">
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <p>Je hebt nog geen afspraken.</p>
                    <a class="btn-primary" href="lodges.php">Maak een afspraak</a>
                </div>
            <?php else: ?>
                <div class="appointments-grid">
                    <?php foreach ($appointments as $appointment): ?>
                        <?php
                        $startTs = strtotime($appointment['starttijd']);
                        $endTs = strtotime($appointment['eindtijd']);
                        $statusClass = 'status-default';
                        $statusText = strtolower(trim((string) ($appointment['status'] ?? '')));

                        if ($statusText === 'gepland') {
                            $statusClass = 'status-gepland';
                        } elseif ($statusText === 'bezig') {
                            $statusClass = 'status-bezig';
                        } elseif ($statusText === 'voltooid') {
                            $statusClass = 'status-voltooid';
                        } elseif ($statusText === 'geannuleerd') {
                            $statusClass = 'status-geannuleerd';
                        }
                        ?>

                        <div class="appointment-card">
                            <div class="appointment-top">
                                <h3><?php echo htmlspecialchars($appointment['lodgetype']); ?></h3>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($appointment['status'] ?: 'Onbekend'); ?>
                                </span>
                            </div>

                            <p class="meta">
                                Lodge huisnummer: <strong><?php echo htmlspecialchars($appointment['huisnummer']); ?></strong>
                            </p>
                            <p class="meta">
                                Datum: <strong><?php echo date('d-m-Y', $startTs); ?></strong>
                            </p>
                            <p class="meta">
                                Tijd: <strong><?php echo date('H:i', $startTs); ?> - <?php echo date('H:i', $endTs); ?></strong>
                            </p>
                            <p class="meta">
                                Aantal mensen: <strong><?php echo htmlspecialchars((string) $appointment['aantalmensen']); ?></strong>
                            </p>

                            <?php if (!empty($appointment['toelichting'])): ?>
                                <p class="note"><?php echo nl2br(htmlspecialchars($appointment['toelichting'])); ?></p>
                            <?php endif; ?>

                            <a class="btn-secondary" href="planneritem.php?id=<?php echo (int) $appointment['afspraakid']; ?>">
                                Open in planneritem
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
