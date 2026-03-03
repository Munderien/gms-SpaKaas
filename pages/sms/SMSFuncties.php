<?php
require __DIR__ . '/vendor/autoload.php';

use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

$apiKey = "99321ba3";
$apiSecret = "ZVtoA8q70LCmZhsJ";
$from = "31611365315"; // max 11 chars for alphanumeric sender

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $to = $_POST["phone"] ?? '';
    $text = $_POST["message"] ?? '';

    if (empty($to) || empty($text)) {
        die("Phone number and message are required.");
    }

    try {
        $basic = new Basic($apiKey, $apiSecret);
        $client = new Client($basic);

        $response = $client->sms()->send(
            new SMS($to, $from, $text)
        );

        $message = $response->current();

        if ($message->getStatus() == 0) {
            echo "Message sent successfully!";
        } else {
            echo "Failed with status: " . $message->getStatus();
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send SMS</title>
</head>
<body>

<h2>Send SMS</h2>

<form method="POST">
    <label>Phone Number (with country code):</label><br>
    <input type="text" name="phone" placeholder="14155552671"><br><br>

    <label>Message:</label><br>
    <textarea name="message" rows="4" cols="40"></textarea><br><br>

    <button type="submit">Send</button>
</form>

</body>
</html>