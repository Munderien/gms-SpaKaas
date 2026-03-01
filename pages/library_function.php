<?php
function find_user($userMail, $password) {
    include("config.php");
    $v = $db->prepare("SELECT * FROM gebruiker WHERE email = ? AND wachtwoord = ?");
    $v->execute(array($userMail,$password));
    return $v->fetch(PDO::FETCH_ASSOC);
}

?>