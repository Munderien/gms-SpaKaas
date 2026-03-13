<?php
include("config.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['gebruikerId'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['gebruikerId'];

try {
    // Handle delete
    if (isset($_POST['delete_profile_picture']) && $_POST['delete_profile_picture'] == '1') {
        $sql = "UPDATE gebruiker SET profielfoto = NULL WHERE gebruikerId = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        
        $_SESSION['success_message'] = 'Profielfoto is verwijderd.';
    }
    // Handle upload
    else if (isset($_FILES['profielfoto']) && $_FILES['profielfoto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profielfoto'];
        
        // Validate file
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if ($file['size'] > $maxSize) {
            throw new Exception('Bestand is te groot. Maximum 5MB toegestaan.');
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Ongeldig bestandstype. Alleen JPG, PNG en GIF zijn toegestaan.');
        }
        
        // Read file content
        $imageContent = file_get_contents($file['tmp_name']);
        
        if ($imageContent === false) {
            throw new Exception('Kon bestand niet lezen.');
        }
        
        // Update database with binary image data
        $sql = "UPDATE gebruiker SET profielfoto = ? WHERE gebruikerId = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$imageContent, $userId]);
        
        $_SESSION['success_message'] = 'Profielfoto is bijgewerkt.';
    }
    else {
        throw new Exception('Geen bestand geselecteerd of fout bij uploaden.');
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
}

// Redirect back to the profile page
header('Location: edit_user.php');
exit;
?>