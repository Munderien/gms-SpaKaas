<?php
session_start();
include("config.php");

$code = rand(100000, 999999);
$_SESSION['2fa_code'] = $code;

$selectSql = "SELECT * FROM gebruiker WHERE gebruikerId = ?";
$selectStmt = $db->prepare($selectSql);
$selectStmt->execute([$_SESSION['gebruikerId']]);
$userData = $selectStmt->fetch(PDO::FETCH_ASSOC);
// include 2FA email helpers; path should go up one directory from pages
require_once __DIR__ . '/email/emailService.php';

if (isset($_SESSION['gebruikerId']) && trim($_SESSION['gebruikerId']) != '') {
    $emailService = new EmailService();
            $emailService->sendEmail(
                $userData['email'],
                '2FA Code',
                'Beste ' . htmlspecialchars($userData['naam']) . ', 

Hier is uw 2FA code: ' . htmlspecialchars($_SESSION['2fa_code']) . '

Deze code is geldig voor 10 minuten.'
            );
}

header("Location: 2fa_login.php");
exit();
?>