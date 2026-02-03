<?php
include("config.php");
include("library.php");
session_start();
$two_factor = @htmlspecialchars($_POST['two_factor']);
$sql = "UPDATE gebruiker SET 
            is2faingeschakeld = ?
        WHERE
            gebruikerId = ?";
    $v = $db->prepare($sql);
    $x = $v->execute([$two_factor, $gebruikersId]);
//hiervoor moet je session userId hebben die word gegeven bij inloggen en registratie
//user2fa moet ook zelf moeten toegevoegd worden in de database
?>
