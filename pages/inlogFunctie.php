<?php
session_start();
include("config.php");
$mail = trim(htmlspecialchars($_POST['inlogMail'] ?? ''));
$passwordRaw = trim($_POST['inlogPassword'] ?? '');

if ($mail === '' || $passwordRaw === '') {
  $_SESSION['error'] = 'Uw gebruikersnaam en wachtwoord mogen niet leeg zijn.';
  header('Location: ../pages/inlog.php');
  exit;
}
$password = md5(addslashes($passwordRaw));

$v = $db->prepare("select * from gebruiker where email=? and wachtwoord=?");

$v->execute(array($mail, $password));
$x = $v->fetch(PDO::FETCH_ASSOC);

$d = $v->rowcount();

if ($d >0) {
  if ($x['is2faingeschakeld'] == 1) {
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
  echo "u heeft verkeerde gegevens ingevoerd";
   echo '<script>
   setTimeout(function(){
   window.location.href = "../pages/inlog.php";
   }, 3000); 
 </script>';
  die();
}
?>