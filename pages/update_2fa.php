<?php
include("config.php");
session_start();
$two_factor = @htmlspecialchars($_POST['two_factor']);
$gebruikersId = $_SESSION['gebruikerId'];
$sql = "UPDATE gebruiker SET 
            is2faingeschakeld = ?
        WHERE
            gebruikerId = ?";
    $v = $db->prepare($sql);
    $x = $v->execute([$two_factor, $gebruikersId]);
//hiervoor moet je session userId hebben die word gegeven bij inloggen en registratie
//user2fa moet ook zelf moeten toegevoegd worden in de database
?>
