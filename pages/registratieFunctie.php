<?php
include("config.php");
//include("email/emailFuncties.php");
session_start();

$mail = htmlspecialchars($_POST['mail']);
$password = md5(addslashes($_POST['password']));
$adress = htmlspecialchars($_POST['adress']);
$phone = htmlspecialchars($_POST['phone']);
$name = htmlspecialchars($_POST['name']);
$postcode = htmlspecialchars($_POST['postcode']);
$plaats = htmlspecialchars($_POST['Plaats']);
$rol = 0;

if (trim($mail) === "" || trim($password) === "" || trim($adress) === "" || trim($phone) === "" || trim($name) === "") {
    echo "Vul alle gegevens in";
    header("refresh:1;url=inlog.php");
    exit();
}

// Check if email already exists
$v = $db->prepare("SELECT * FROM gebruiker WHERE email = ?");
$v->execute([$mail]);
if ($v->rowCount() > 0) {
    echo "E-mail bestaat al";
    header("refresh:1;url=inlog.php");
    exit();
}

$v = $db->prepare("INSERT INTO gebruiker (email, wachtwoord, rol, isactief, is2faingeschakeld,adres,naam,plaats,telefoonnummer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$x = $v->execute([$mail, $password,0,0,0, $adress, $name,$plaats,$phone]); 

if (!$x) {
    echo "INSERT failed: " . implode(" ", $v->errorInfo());
    header("refresh:1;url=inlog.php");
    exit();
}
$r = $db->prepare("SELECT * FROM gebruiker WHERE email = ?");
$r->execute([$mail]);
$user = $r->fetch(PDO::FETCH_ASSOC);

if ($user) {
  $_SESSION['gebruikerId'] = $user['gebruikerid'];
  $_SESSION['rol'] = $user['rol'];
  $_SESSION['gebruikermail'] = $user['email'];
  $_SESSION['two_factor'] = $user['is2faingeschakeld'];
    // Redirect to home page
    //sendCustomMail($gebruikerId, 'Welkom bij APKaas', 'Bedankt voor uw registratie bij APKaas! We zijn verheugd u aan boord te hebben. Mocht u vragen hebben, aarzel dan niet om contact met ons op te nemen.', true);
    header("Location: home.php");
    exit();
} else {
    echo "Registratie mislukt";
    header("refresh:1;url=inlog.php");
    exit();
}
?>