<?php
session_start();
include("config.php");

$code = rand(100000, 999999);
$_SESSION['2fa_code'] = $code;

// include 2FA email helpers; path should go up one directory from pages
require_once __DIR__ . '/email/emailFuncties.php';

if (isset($_SESSION['gebruikerId']) && trim($_SESSION['gebruikerId']) != '') {
    $emailService = new EmailService();
            $emailService->sendEmail(
                $mail,
                '2FA Code',
                'Beste ' . htmlspecialchars($userData['naam']) . ', 

Hier is uw 2FA code: ' . htmlspecialchars($twoFACode) . '

Deze code is geldig voor 10 minuten.'
            );
}

header("Location: 2fa_login.php");
exit();
?>