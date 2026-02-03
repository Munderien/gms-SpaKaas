<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require DIR . '/PHPMailer/src/Exception.php';
require DIR . '/PHPMailer/src/PHPMailer.php';
require DIR . '/PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'apkaasbv@gmail.com';
        $mail->Password = 'ikpk tdtn lmes vnsd';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->addAddress($to);

        $mail->setFrom('apkaasbv@gmail.com', 'APKaas');

        $mail->Subject = $subject;

        $loggedin = isset($_POST['loggedin']) && $_POST['loggedin'] == '1'; 
        $footer = $loggedin ? '<p style="margin-top: 20px;">
                Met vriendelijke groet,
                <br>
                <strong>Test Persoon</strong>
            </p>': '<p style="margin-top: 20px;">
                Met vriendelijke groeten,
                <br>
                <strong>Het APKaas team</strong>
            </p>';
        $footer .= '<div style="margin-top: 30px; text-align: center;">
                <img src="https://via.placeholder.com/300x150" alt="APKaas" style="border-radius: 8px;">
            </div>';

        $mail->isHTML(true);
        $mail->Body = '
  <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>' . nl2br($message) . '</p>' . $footer . '
  </div>
';
    $mail->send();
    echo '<script type="text/javascript">
            if(confirm("Your mail has been sent"))
                document.location = "/";
          </script>';
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
    }
}
function send2faMail($to, $code) {
    $subject = "Uw 2FA code";
    $message = "Uw 2FA code is: $code";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'apkaasbv@gmail.com';
        $mail->Password = 'fbrb rplf feds ccxg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->addAddress($to);
        $mail->setFrom('apkaasbv@gmail.com', 'TandSucces');
        $mail->Subject = $subject;

        $footer = '<p style="margin-top: 20px;">
                Met vriendelijke groeten,
                <br>
                <strong>Het apkaas team</strong>
            </p>';
        $footer .= '<div style="margin-top: 30px; text-align: center;">
                <img src="https://via.placeholder.com/300x150" alt="Tandarts Praktijk" style="border-radius: 8px;">
            </div>';

        $mail->isHTML(true);
        $mail->Body = '
  <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>' . nl2br($message) . '</p>' . $footer . '
  </div>
';
        $mail->send();
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
    }
}
function sendCustomMail($to, $subject, $message, $isLoggedIn = true) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'apkaasbv@gmail.com';
        $mail->Password = 'ikpk tdtn lmes vnsd';  // Use your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->addAddress($to);
        $mail->setFrom('apkaasbv@gmail.com', 'APKaas');
        $mail->Subject = $subject;

        $footer = $isLoggedIn ? '<p style="margin-top: 20px;">
                Met vriendelijke groet,
                <br>
                <strong>Test Persoon</strong>
            </p>': '<p style="margin-top: 20px;">
                Met vriendelijke groeten,
                <br>
                <strong>Het APKaas team</strong>
            </p>';
        $footer .= '<div style="margin-top: 30px; text-align: center;">
                <img src="https://via.placeholder.com/300x150" alt="APKaas" style="border-radius: 8px;">
            </div>';

        $mail->isHTML(true);
        $mail->Body = '
  <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>' . nl2br($message) . '</p>' . $footer . '
  </div>
';
        $mail->send();
        return true;
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
        return false;
    }
}
?>