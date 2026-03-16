<?php
session_start();

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: /dms-spakaas/gms-SpaKaas/pages/inlog.php');
    exit;
}

if (!isset($_SESSION['appointmentDetails'])) {
    die('geen toegang!');
}

$appointmentDetails = $_SESSION['appointmentDetails'];
unset($_SESSION['appointmentDetails']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Afspraak Bevestigd</title>
    <link rel="stylesheet" href="../Style/afspraakSucces.css">
</head>
<body>
    <div class="success-container">
        <div class="success-icon"></div>
        <h1>Afspraak Bevestigd!</h1>
        <p class="subtitle">
            Uw afspraak is succesvol aangemaakt.<br>
            Wij zien u graag op de afgesproken datum!
        </p>

        <?php if ($appointmentDetails): ?>
            <div class="details-card">
                <div class="detail-row">
                    <span class="detail-label">Begindatum:</span>
                    <span class="detail-value"><?php echo htmlspecialchars(date('d-m-Y', strtotime($appointmentDetails['starttijd'] ?? ''))); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Einddatum:</span>
                    <span class="detail-value"><?php echo htmlspecialchars(date('d-m-Y', strtotime($appointmentDetails['eindtijd'] ?? ''))); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Aantal personen:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($appointmentDetails['aantalmensen'] ?? '-'); ?></span>
                </div>
                <?php if (isset($appointmentDetails['toelichting'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Opmerkingen:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($appointmentDetails['toelichting']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="Lodges.php" class="btn btn-primary">Terug naar Lodges</a>
            <a href="index.php" class="btn btn-secondary">Naar Dashboard</a>
        </div>

        <p class="info-text">Een bevestigingsemail is naar u verzonden.</p>
    </div>
</body>
</html>
