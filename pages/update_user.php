<?php
include("config.php");
session_start();

// Helper function to validate password strength
function isStrongPassword($password) {
    if (empty($password)) {
        return true; // Allow empty password (means don't change it)
    }
    
    $errors = [];
    
    // Check length
    if (strlen($password) < 8) {
        $errors[] = "Wachtwoord moet minimaal 8 karakters zijn";
    }
    
    // Check for uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Wachtwoord moet minimaal 1 hoofdletter bevatten";
    }
    
    // Check for lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Wachtwoord moet minimaal 1 kleine letter bevatten";
    }
    
    // Check for number
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Wachtwoord moet minimaal 1 getal bevatten";
    }
    
    // Check for special character
    if (!preg_match('/[!@#$%^&*()_\-+=\[\]{};:\'",.<>?\/\\|`~]/', $password)) {
        $errors[] = "Wachtwoord moet minimaal 1 speciaal teken bevatten (!@#$%^&*)";
    }
    
    return count($errors) === 0 ? true : $errors;
}

// Helper function to validate all required fields
function validateFields($fields) {
    $errors = [];
    
    // Define required fields with user-friendly names
    $requiredFields = [
        'mail' => 'Email',
        'adres' => 'Adres',
        'postcode' => 'Postcode',
        'plaats' => 'Plaats',
        'naam' => 'Naam',
        'telefoonnummer' => 'Telefoon'
    ];
    
    foreach ($requiredFields as $fieldName => $fieldLabel) {
        if (empty($fields[$fieldName])) {
            $errors[] = "{$fieldLabel} is verplicht";
        }
    }
    
    return count($errors) === 0 ? true : $errors;
}

// Helper function to validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Helper function to validate postcode (Dutch format: NNNN AA)
function isValidPostcode($postcode) {
    return preg_match('/^[0-9]{4}\s?[A-Z]{2}$/i', $postcode) === 1;
}

// Helper function to validate phone number
function isValidPhone($phone) {
    // Removes common formatting, checks if only numbers/plus/spaces/dashes
    $cleaned = preg_replace('/[^0-9+\-\s]/', '', $phone);
    return strlen($cleaned) >= 9;
}

// Helper function to generate 2FA code
function generate2FACode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Collect POST data
$mail = htmlspecialchars(trim($_POST['mail'] ?? ''));
$password = $_POST['password'] ?? '';
$adres = htmlspecialchars(trim($_POST['adres'] ?? ''));
$postcode = htmlspecialchars(trim($_POST['postcode'] ?? ''));
$plaats = htmlspecialchars(trim($_POST['plaats'] ?? ''));
$naam = htmlspecialchars(trim($_POST['naam'] ?? ''));
$telefoonnummer = htmlspecialchars(trim($_POST['telefoonnummer'] ?? ''));
$two_factor = $_POST['two_factor'] ?? '0';

// Initialize error array
$validationErrors = [];

// Validate required fields
$fieldValidation = validateFields([
    'mail' => $mail,
    'adres' => $adres,
    'postcode' => $postcode,
    'plaats' => $plaats,
    'naam' => $naam,
    'telefoonnummer' => $telefoonnummer
]);

if ($fieldValidation !== true) {
    $validationErrors = array_merge($validationErrors, $fieldValidation);
}

// Validate email format
if (!empty($mail) && !isValidEmail($mail)) {
    $validationErrors[] = "Email adres is ongeldig";
}

// Validate postcode format
if (!empty($postcode) && !isValidPostcode($postcode)) {
    $validationErrors[] = "Postcode moet het formaat NNNN AA hebben";
}

// Validate phone number
if (!empty($telefoonnummer) && !isValidPhone($telefoonnummer)) {
    $validationErrors[] = "Telefoon nummer moet minimaal 9 cijfers bevatten";
}

// Validate password strength (only if password is provided)
if (!empty($password)) {
    $passwordValidation = isStrongPassword($password);
    if ($passwordValidation !== true) {
        $validationErrors = array_merge($validationErrors, $passwordValidation);
    }
}

// If there are validation errors, redirect back with error message
if (count($validationErrors) > 0) {
    $_SESSION['validation_errors'] = $validationErrors;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Fetch current user data
$selectSql = "SELECT * FROM gebruiker WHERE gebruikerId = ?";
$selectStmt = $db->prepare($selectSql);
$selectStmt->execute([$_SESSION['gebruikerId']]);
$userData = $selectStmt->fetch(PDO::FETCH_ASSOC);

// Prepare password: use new password if provided, otherwise keep existing
$newPassword = !empty($password) ? password_hash($password, PASSWORD_BCRYPT) : $userData['wachtwoord'];

// Update user data
$sql = "UPDATE gebruiker SET 
            email = ?,
            wachtwoord = ?,
            adres = ?,
            postcode = ?,
            plaats = ?,
            naam = ?,
            telefoonnummer = ?,
            is2faingeschakeld = ?
        WHERE
            gebruikerId = ?";

try {
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $mail,
        $newPassword,
        $adres,
        $postcode,
        $plaats,
        $naam,
        $telefoonnummer,
        $two_factor,
        $_SESSION['gebruikerId']
    ]);
    
    if (!$result) {
        throw new Exception("Database update failed");
    }
    
    // Handle two-factor authentication
    if ($two_factor == 1) {
        // Generate a new 2FA code
        $twoFACode = generate2FACode();
        $_SESSION['2fa_code'] = $twoFACode;
        
        require_once __DIR__ . '/email/EmailService.php';
        
        try {
            $emailService = new EmailService();
            $emailService->sendEmail(
                $mail,
                '2FA Code',
                'Beste ' . htmlspecialchars($userData['naam']) . ', 

Hier is uw 2FA code: ' . htmlspecialchars($twoFACode) . '

Deze code is geldig voor 10 minuten.'
            );
            
            // Store the timestamp when the code was generated
            $_SESSION['2fa_code_time'] = time();
            
            header('Location: ../pages/2fa_login.php');
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Email kon niet verzonden worden: ' . htmlspecialchars($e->getMessage());
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } else {
        $_SESSION['success'] = 'Gegevens succesvol bijgewerkt';
        header('Location: ../pages/home.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Een fout is opgetreden: ' . htmlspecialchars($e->getMessage());
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
?>