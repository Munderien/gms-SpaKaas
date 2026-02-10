<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'SpaKaasBV@gmail.com';
        $mail->Password = 'mtvr tuxk pkbo uras ';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->addAddress($to);

        $mail->setFrom('SpaKaasBV@gmail.com', 'SpaKaas');

        $mail->Subject = $subject;

        $footer = '<p style="margin-top: 20px;">
                Met vriendelijke groeten,
                <br>
                <strong>Het SpaKaas team</strong>
            </p>';
        $footer .= '<div style="margin-top: 30px; text-align: center;">
                <img src="https://via.placeholder.com/300x150" alt="SpaKaas" style="border-radius: 8px;">
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
?>