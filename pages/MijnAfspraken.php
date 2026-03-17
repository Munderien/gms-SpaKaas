<?php
session_start();

// Language configuration
$availableLanguages = ['nl', 'en', 'de', 'fr', 'tr'];
$currentLang = $_SESSION['language'] ?? 'nl';

// Validate language exists
if (!in_array($currentLang, $availableLanguages)) {
    $currentLang = 'nl';
    $_SESSION['language'] = $currentLang;
}

// Load language file
$langFile = __DIR__ . "/vertaling/{$currentLang}.php";

if (file_exists($langFile)) {
    $lang = require($langFile);
} else {
    die("Error: Language file not found at {$langFile}");
}

// Ensure $lang is an array
if (!is_array($lang)) {
    $lang = [];
}

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
<html lang="<?= $currentLang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['my_appointments_title'] ?> - DMS Spakaas</title>
    <link rel="stylesheet" href="../Style/mijnAfspraken.css">
</head>

<body>
    <?php include '../navbar.php'; ?>

    <div class="container">
        <header>
            <h1><?= $lang['my_appointments_title'] ?></h1>
            <p><?= $lang['my_appointments_subtitle'] ?></p>
        </header>

        <div class="content">
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <p><?= $lang['my_appointments_no_appointments'] ?></p>
                    <a class="btn-primary" href="lodges.php"><?= $lang['my_appointments_make_appointment'] ?></a>
                </div>
            <?php else: ?>
                <div class="appointments-grid">
                    <?php foreach ($appointments as $appointment): ?>
                        <?php
                        $startTs = strtotime($appointment['starttijd']);
                        $endTs = strtotime($appointment['eindtijd']);
                        $statusClass = 'status-default';
                        $statusText = strtolower(trim((string) ($appointment['status'] ?? '')));
                        $statusLabel = $lang['appointment_status_unknown'] ?? 'Onbekend';

                        if ($statusText === 'gepland') {
                            $statusClass = 'status-gepland';
                            $statusLabel = $lang['appointment_status_gepland'] ?? 'Gepland';
                        } elseif ($statusText === 'bezig') {
                            $statusClass = 'status-bezig';
                            $statusLabel = $lang['appointment_status_bezig'] ?? 'Bezig';
                        } elseif ($statusText === 'voltooid') {
                            $statusClass = 'status-voltooid';
                            $statusLabel = $lang['appointment_status_voltooid'] ?? 'Voltooid';
                        } elseif ($statusText === 'geannuleerd') {
                            $statusClass = 'status-geannuleerd';
                            $statusLabel = $lang['appointment_status_geannuleerd'] ?? 'Geannuleerd';
                        } elseif ($statusText === 'bevestigd') {
                            $statusClass = 'status-bevestigd';
                            $statusLabel = $lang['appointment_status_bevestigd'] ?? 'Bevestigd';
                        } elseif ($statusText === 'verplaatst') {
                            $statusClass = 'status-verplaatst';
                            $statusLabel = $lang['appointment_status_verplaatst'] ?? 'Verplaatst';
                        } elseif ($statusText === 'niet verschenen') {
                            $statusClass = 'status-niet-verschenen';
                            $statusLabel = $lang['appointment_status_niet_verschenen'] ?? 'Niet verschenen';
                        } elseif ($statusText === 'in afwachting') {
                            $statusClass = 'status-in-afwachting';
                            $statusLabel = $lang['appointment_status_in_afwachting'] ?? 'In afwachting';
                        } else {
                            $statusLabel = htmlspecialchars($appointment['status'] ?: ($lang['appointment_status_unknown'] ?? 'Onbekend'));
                        }
                        ?>

                        <div class="appointment-card">
                            <div class="appointment-top">
                                <h3><?php echo htmlspecialchars($appointment['lodgetype']); ?></h3>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </div>

                            <p class="meta">
                                <?= $lang['my_appointments_lodge_number'] ?> <strong><?php echo htmlspecialchars($appointment['huisnummer']); ?></strong>
                            </p>
                            <p class="meta">
                                <?= $lang['my_appointments_date'] ?> <strong><?php echo date('d-m-Y', $startTs); ?></strong>
                            </p>
                            <p class="meta">
                                <?= $lang['my_appointments_time'] ?> <strong><?php echo date('H:i', $startTs); ?> - <?php echo date('H:i', $endTs); ?></strong>
                            </p>
                            <p class="meta">
                                <?= $lang['my_appointments_people'] ?> <strong><?php echo htmlspecialchars((string) $appointment['aantalmensen']); ?></strong>
                            </p>

                            <?php if (!empty($appointment['toelichting'])): ?>
                                <p class="note"><?php echo nl2br(htmlspecialchars($appointment['toelichting'])); ?></p>
                            <?php endif; ?>

                            <a class="btn-secondary" href="planneritem.php?id=<?php echo (int) $appointment['afspraakid']; ?>">
                                <?= $lang['my_appointments_open_planner'] ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>