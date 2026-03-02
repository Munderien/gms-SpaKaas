<?php
include ("config.php");
session_start();

if (trim($gebruikermail) == '') {
 header("Location: ../pages/inlog.php; refresh=1;");
}

$code = rand(100000, 999999);
if (!isset($_SESSION['2fa_code']) || trim($_SESSION['2fa_code']) == '') {
    $_SESSION['2fa_code'] = $code; 
}

$_SESSION['2fa_email_sent']= false;
if ($_SESSION['2fa_email_sent']==false&&trim($_SESSION['userid'])!='') {
   
require_once __DIR__ . '/email/EmailService.php';

try {
    $emailService = new EmailService();
    $emailService->sendEmail(
        $_SESSION['gebruikermail'],
        '2FA Code',
        "Uw 2FA code is: $code"
    );
} catch (Exception $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}

    $_SESSION['2fa_email_sent'] = true;
}
?>
<link rel='stylesheet' href="style/login_regis_pagina.css">
<form method="post" action='verwerk_2fa.php'>
<div class="container">
  <div class="inlogkaart">
    <h1>Inloggen 2fa</h1>
    <table>
        <tr>
        <td><p>code:</p></td>
        <td><input class="inlogcode" name="inlogcode" type="text"></td>
        </tr>
        <tr>
            <td></td><td><button class="inlogButton">Login</button></td>
        </tr>
    </table>
  </div>
</div>
</form>