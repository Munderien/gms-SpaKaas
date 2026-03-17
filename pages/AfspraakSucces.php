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

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: /dms-spakaas/gms-SpaKaas/pages/inlog.php');
    exit;
}

if (!isset($_SESSION['appointmentDetails'])) {
    die($lang['appointment_success_no_details'] ?? 'No access!');
}

$appointmentDetails = $_SESSION['appointmentDetails'];
unset($_SESSION['appointmentDetails']);
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['appointment_success_page_title'] ?> - DMS Spakaas</title>
    <link rel="stylesheet" href="../Style/afspraakSucces.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="page-wrapper">
    <div class="success-container">
        <div class="success-icon"></div>
        <h1><?= $lang['appointment_success_heading'] ?></h1>
        <p class="subtitle">
            <?= $lang['appointment_success_subtitle_p1'] ?><br>
            <?= $lang['appointment_success_subtitle_p2'] ?>
        </p>

        <?php if ($appointmentDetails): ?>
            <div class="details-card">
                <div class="detail-row">
                    <span class="detail-label"><?= $lang['appointment_success_start_date'] ?></span>
                    <span class="detail-value"><?php echo htmlspecialchars(date('d-m-Y', strtotime($appointmentDetails['starttijd'] ?? ''))); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= $lang['appointment_success_end_date'] ?></span>
                    <span class="detail-value"><?php echo htmlspecialchars(date('d-m-Y', strtotime($appointmentDetails['eindtijd'] ?? ''))); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= $lang['appointment_success_number_of_people'] ?></span>
                    <span class="detail-value"><?php echo htmlspecialchars($appointmentDetails['aantalmensen'] ?? '-'); ?></span>
                </div>
                <?php if (isset($appointmentDetails['toelichting'])): ?>
                    <div class="detail-row">
                        <span class="detail-label"><?= $lang['appointment_success_notes'] ?></span>
                        <span class="detail-value"><?php echo htmlspecialchars($appointmentDetails['toelichting']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="Lodges.php" class="btn btn-primary"><?= $lang['appointment_success_back_to_lodges'] ?></a>
            <a href="index.php" class="btn btn-secondary"><?= $lang['appointment_success_to_dashboard'] ?></a>
        </div>

        <p class="info-text"><?= $lang['appointment_success_confirmation_email'] ?></p>
    </div>
    </div>
</body>
</html>