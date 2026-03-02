<?php
include("config.php");
//include("email/emailFuncties.php");
session_start();

// Set JSON header
header('Content-Type: application/json');

try {
    // Get form data - matching the new form field names
    $mail = htmlspecialchars($_POST['mail'] ?? '');
    $password = $_POST['password'] ?? ''; // Don't MD5 yet, validate first
    $straatnaam = htmlspecialchars($_POST['straatnaam'] ?? '');
    $huisnummer = htmlspecialchars($_POST['huisnummer'] ?? '');
    $postcode = htmlspecialchars($_POST['postcode'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $name = htmlspecialchars($_POST['name'] ?? '');
    $plaats = htmlspecialchars($_POST['plaats'] ?? '');
    $rol = 0;

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

    // Hash the password after validation
    $hashedPassword = md5(addslashes($password));

    // Insert new user
    $v = $db->prepare("INSERT INTO gebruiker (email, wachtwoord, rol, isactief, is2faingeschakeld, adres, naam, plaats, telefoonnummer, postcode) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $x = $v->execute([$mail, $hashedPassword, 0, 0, 0, $adres, $name, $plaats, $phone, $postcode]); 

    if (!$x) {
        echo json_encode(['success' => false, 'message' => 'Registratie mislukt. Probeer het later opnieuw.']);
        exit();
    }

    // Get the newly created user
    $r = $db->prepare("SELECT * FROM gebruiker WHERE email = ?");
    $r->execute([$mail]);
    $user = $r->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['gebruikerId'] = $user['gebruikerid'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['gebruikermail'] = $user['email'];
        $_SESSION['two_factor'] = $user['is2faingeschakeld'];
        
        echo json_encode(['success' => true, 'message' => 'Registratie succesvol! U wordt doorgestuurd...']);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Registratie mislukt. Probeer het later opnieuw.']);
        exit();
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Er is een fout opgetreden: ' . $e->getMessage()]);
    exit();
}

/**
 * Validate password strength
 * Requirements:
 * - Minimum 8 characters
 * - At least 1 uppercase letter
 * - At least 1 number
 * - At least 1 special character
 */
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