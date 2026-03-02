<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mail;
    
    public function __construct() {
        require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
        require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
        
        $this->mail = new PHPMailer(true);
        
        // Configure SMTP settings once
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'SpaKaasBV@gmail.com';
        $this->mail->Password = 'mtvr tuxk pkbo uras ';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = 465;
        $this->mail->setFrom('SpaKaasBV@gmail.com', 'SpaKaas');
        $this->mail->isHTML(true);
    }
    
    public function sendEmail($to, $subject, $message) {
        try {
            // Clear previous recipient (if reusing instance)
            $this->mail->clearAddresses();
            
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            
            // Build the footer
            $footer = '<p style="margin-top: 20px;">
                    Met vriendelijke groeten,
                    <br>
                    <strong>Het SpaKaas team</strong>
                </p>';
            $footer .= '<div style="margin-top: 30px; text-align: center;">
                    <img src="https://via.placeholder.com/300x150" alt="SpaKaas" style="border-radius: 8px;">
                </div>';
            
            // Build the body
            $this->mail->Body = '
                <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
                    <p>' . nl2br(htmlspecialchars($message)) . '</p>' . $footer . '
                </div>
            ';
            
            return $this->mail->send();
        } catch (Exception $e) {
            throw new Exception('Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo);
        }
    }
}