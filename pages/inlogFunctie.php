<?php
session_start();
include("config.php");
$mail = trim(htmlspecialchars($_POST['inlogMail'] ?? ''));
$passwordRaw = trim($_POST['inlogPassword'] ?? '');

if ($mail === '' || $passwordRaw === '') {
  // Do not show the general login error box when required fields are incomplete.
  $_SESSION['login_input_error'] = true;
  header('Location: ../pages/inlog.php');
  exit;
}

$password = md5(addslashes($passwordRaw));

$v = $db->prepare("select * from gebruiker where email=? and wachtwoord=?");

$v->execute(array($mail, $password));
$x = $v->fetch(PDO::FETCH_ASSOC);

$d = $v->rowcount();

if ($d > 0) {
  
  if ($x['is2faingeschakeld'] == 1&& $_COOKIE['two_factor'] != '0') {
    $_SESSION['two_factor'] = '1';
    $_SESSION['gebruikerId'] = $x['gebruikerid'];
    header('Location: nieuw_2fa_code.php');
    exit();
  }

  $_SESSION['gebruikerId'] = $x['gebruikerid'];
  $_SESSION['rol'] = $x['rol'];
  $_SESSION['gebruikermail'] = $x['email'];
  $_SESSION['two_factor'] = $x['is2faingeschakeld'];

  include('RecentlyViewed.php');
  echo "<script>window.location.href='../pages/home.php'</script>";
} else {
  // Only show error box when both credentials were provided but authentication failed
  $_SESSION['error'] = 'Email of wachtwoord is onjuist.';
  header('Location: ../pages/inlog.php');
  exit;
}
?>