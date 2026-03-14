<?php
session_start();
include("config.php");
$mail = trim(htmlspecialchars($_POST['inlogMail']));
$password = trim(md5(addslashes($_POST['inlogPassword'])));

if (trim($mail) === "" or trim($password) === "") {
  echo "$('#error').html('Uw gebruikersnaam en wachtwoord mogen niet leeg zijn.').show();";
  echo '<script>
  setTimeout(function(){
    window.location.href = "../pages/inlog.php";
  }, 3000); 
</script>';
  die();
}

$v = $db->prepare("select * from gebruiker where email=? and wachtwoord=?");

$v->execute(array($mail, $password));
$x = $v->fetch(PDO::FETCH_ASSOC);

$d = $v->rowcount();

if ($d > 0) {
  if ($x['is2faingeschakeld'] == 1) {
    $_SESSION['two_factor'] = '1';
    header("Location: nieuw_2fa_code.php");
    $_SESSION['gebruikerId'] = $x['gebruikerid'];
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