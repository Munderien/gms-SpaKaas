<?php
include("config.php");
session_start();
$mail = htmlspecialchars($_POST['mail'] ?? '');
    $password = $_POST['password'] ?? ''; // Don't MD5 yet, validate first
    $straatnaam = htmlspecialchars($_POST['straatnaam'] ?? '');
    $huisnummer = htmlspecialchars($_POST['huisnummer'] ?? '');
    $postcode = htmlspecialchars($_POST['postcode'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $name = htmlspecialchars($_POST['name'] ?? '');
    $plaats = htmlspecialchars($_POST['plaats'] ?? '');


 // Combine street name and house number for address
    $adres = $straatnaam . ' ' . $huisnummer;

    // Validate all required fields are not empty
    if (trim($mail) === "" || trim($password) === "" || trim($straatnaam) === "" || trim($huisnummer) === "" || 
        trim($phone) === "" || trim($name) === "" || trim($postcode) === "" || trim($plaats) === "") {
        echo json_encode(['success' => false, 'message' => 'Vul alstublieft alle velden in.']);
        exit();
    }

    // Validate email format
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Voer alstublieft een geldig email adres in.']);
        exit();
    }

    // Validate password strength
    $passwordValidation = validatePasswordStrength($password);
    if (!$passwordValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $passwordValidation['message']]);
        exit();
    }

    // Check if email already exists
    $v = $db->prepare("SELECT * FROM gebruiker WHERE email = ?");
    $v->execute([$mail]);
    if ($v->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Dit email adres is al geregistreerd.']);
        exit();
    }

$sql = "UPDATE gebruiker SET 
            email = ?,
            wachtwoord = ?,
            adres = ?,
            postcode = ?,
            plaats = ?,
            telefoonnummer = ?,
            is2faingeschakeld = ?
        WHERE
            gebruikerId = ?";
    $v = $db->prepare($sql);
    $x = $v->execute([$mail, $password, $adres, $postcode, $plaats, $phone, $_POST['two_factor'] ?? 0, $_SESSION['gebruikerId']]);
echo "<script>window.location.href='../pages/home.php'</script>";
//hiervoor moet je session userId hebben die word gegeven bij inloggen en registratie
//user2fa moet ook zelf moeten toegevoegd worden in de database
function validatePasswordStrength($password) {
    $errors = [];
    
    // Check length
    if (strlen($password) < 8) {
        $errors[] = 'Minimaal 8 karakters';
    }
    
    // Check for uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Minimaal 1 hoofdletter';
    }
    
    // Check for number
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Minimaal 1 getal';
    }
    
    // Check for special character
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $errors[] = 'Minimaal 1 speciaal teken (!@#$%^&* etc)';
    }
    
    if (count($errors) > 0) {
        return [
            'valid' => false,
            'message' => 'Wachtwoord voldoet niet aan de vereisten: ' . implode(', ', $errors)
        ];
    }
    
    return ['valid' => true];
}
?>

