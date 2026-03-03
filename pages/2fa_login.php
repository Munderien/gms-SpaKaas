
<?php
include("config.php");
session_start();

// Check if 2FA code exists and hasn't expired
if (isset($_SESSION['2fa_code_time'])) {
    $codeAge = time() - $_SESSION['2fa_code_time'];
    if ($codeAge > 600) { // 10 minutes
        $_SESSION['error'] = '2FA code is verlopen. Probeer opnieuw in te loggen.';
        unset($_SESSION['2fa_code']);
        unset($_SESSION['2fa_code_time']);
        unset($_SESSION['2fa_email_sent']);
        header('Location: login.php');
        exit();
    }
}

// Only generate and send code if it hasn't been sent yet
if (!isset($_SESSION['2fa_email_sent']) || $_SESSION['2fa_email_sent'] === false) {
    if (isset($_SESSION['gebruikermail']) && !empty(trim($_SESSION['gebruikermail']))) {
        // Generate new 2FA code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['2fa_code'] = $code;
        $_SESSION['2fa_code_time'] = time();
        
        require_once __DIR__ . '/email/EmailService.php';
        
        try {
            $emailService = new EmailService();
            $emailService->sendEmail(
                $_SESSION['gebruikermail'],
                '2FA Code',
                'Uw 2FA code is: ' . htmlspecialchars($code) . '
                
                Deze code is 10 minuten geldig.'
            );
            $_SESSION['2fa_email_sent'] = true;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Email kon niet verzonden worden: ' . htmlspecialchars($e->getMessage());
        }
    }
}   


$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
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
                <?php if (!empty($error)): ?>
                    <div class="error-message" style="background-color: #fee; border: 1px solid #fcc; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action='verwerk_2fa.php' method="post" class="form-card active" id="loginForm">
                    <h1>2FA Inloggen</h1>

                    <div class="form-group">
                        <label for="inlogMail">Code</label>
                        <input type="text" id="inlogMail" class="inlogMail" name="inlogcode" placeholder="Voer uw 6-cijferige 2FA code in" required maxlength="6" pattern="[0-9]{6}">
                        <small class="help-text">Voer de 6-cijferige code in die u via email heeft ontvangen</small>
                        <div class="error-message" id="loginEmailError" style="display: none;">Voer alstublieft een geldige 6-cijferige 2FA code in.</div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="inlogButton">Inloggen</button>
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