<?php
session_start();
include("config.php");

// Generate 2FA code
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$_SESSION['2fa_code'] = $code;
$_SESSION['2fa_code_time'] = time();

$selectSql = "SELECT * FROM gebruiker WHERE gebruikerId = ?";
$selectStmt = $db->prepare($selectSql);
$selectStmt->execute([$_SESSION['gebruikerId']]);
$userData = $selectStmt->fetch(PDO::FETCH_ASSOC);

// Check if user data was found
if (!$userData) {
    $_SESSION['error'] = 'Gebruiker niet gevonden';
    header("Location: inlog.php");
    exit();
}

// Verify email exists
if (empty($userData['email'])) {
    $_SESSION['error'] = 'Geen email adres gevonden voor deze gebruiker';
    header("Location: inlog.php");
    exit();
}

require_once __DIR__ . '/email/EmailService.php';

try {
    $emailService = new EmailService();
    
    $emailSubject = '2FA Code';
    $emailBody = 'Beste ' . htmlspecialchars($userData['naam']) . ', 

Hier is uw 2FA code: ' . htmlspecialchars($code) . '

Deze code is geldig voor 10 minuten.';
    
    // Attempt to send email
    $emailSent = $emailService->sendEmail(
        $userData['email'],
        $emailSubject,
        $emailBody
    );
    
    // Check if email was sent successfully
    if (!$emailSent) {
        throw new Exception('EmailService returned false - email may not have been sent');
    }
    
    $_SESSION['2fa_email_sent'] = true;
    header("Location: 2fa_login.php");
    exit();
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log('2FA Email Error: ' . $e->getMessage());
    
    $_SESSION['error'] = 'Email kon niet verzonden worden. Fout: ' . htmlspecialchars($e->getMessage());
    header("Location: inlog.php");
    exit();
}
?>