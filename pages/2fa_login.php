<?php
include ("config.php");
session_start();

$code = rand(100000, 999999);
if (!isset($_SESSION['2fa_code']) || trim($_SESSION['2fa_code']) == '') {
    $_SESSION['2fa_code'] = $code; 
}

// Only send email if not already sent
if (!isset($_SESSION['2fa_email_sent']) || $_SESSION['2fa_email_sent'] === false) {
    if (isset($_SESSION['gebruikermail']) && !empty(trim($_SESSION['gebruikermail']))) {
        require_once __DIR__ . '/email/EmailService.php';
        
        try {
            $emailService = new EmailService();
            $emailService->sendEmail(
                $_SESSION['gebruikermail'],
                '2FA Code',
                "Uw 2FA code is: $code"
            );
            $_SESSION['2fa_email_sent'] = true;
            var_dump($_SESSION['2fa_code']);
        } catch (Exception $e) {
            echo 'Error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Login - Luxe Spa Resort</title>
    <link rel='stylesheet' href="../Style/login.css">
</head>
<body>
    <div class="main-container">
        <div class="spa-marketing">
            <h2>Welkom bij Luxe Spa Resort</h2>
            <p>Ervaar ultieme ontspanning en vernieuwing in onze premium spa-faciliteit.</p>
            
            <div class="spa-features">
                <div class="feature">
                    <h3>Spa Services</h3>
                    <p>Wereldklasse massages en behandelingen</p>
                </div>
                <div class="feature">
                    <h3>Welzijn</h3>
                    <p>Zwembaden, sauna's en ontspanningsruimtes</p>
                </div>
                <div class="feature">
                    <h3>Luxe</h3>
                    <p>Premium voorzieningen en accommodaties</p>
                </div>
            </div>
        </div>

        <div class="forms-container">
            <div class="forms-wrapper">
                <form action='verwerk_2fa.php' method="post" class="form-card active" id="loginForm">
                    <h1>2FA Inloggen</h1>

                    <div class="form-group">
                        <label for="inlogMail">code</label>
                        <input type="text" id="inlogMail" class="inlogMail" name="inlogMail" placeholder="uw 2fa code" required>
                        <div class="error-message" id="loginEmailError">Voer alstublieft een geldige 2fa code in.</div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="inlogButton">Login</button>
                    </div>
                </form>
                
                <div class="form-footer">
                    
                        click voor nieuwe code? <button type="button" class="toggle-form" onclick="location.href='nieuw_2fa_code.php'">Nieuwe code</button>
                    </div>
<!--<div class="container">
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