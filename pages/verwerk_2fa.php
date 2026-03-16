<?php
session_start();
include("config.php");

$sql="SELECT * FROM gebruiker WHERE gebruikerId=?";
$v=$db->prepare($sql);
$v->execute([$_SESSION['gebruikerId']]);
$x=$v->fetch(PDO::FETCH_ASSOC);

$code=@htmlspecialchars($_POST['inlogcode']);

if(trim($_SESSION['gebruikerId'])=='' || trim($_SESSION['two_factor']) != '1'){
    echo "<script>window.location.href='../pages/inlog.php'</script>";
    exit();
}

if (trim($code) === '') {
    // Same as inlog: if required input missing, only notify about missing input
    $_SESSION['two_factor_input_error'] = true;
    header('Location: ../pages/2fa_login.php');
    exit();
}

if (trim($code) !== $_SESSION['2fa_code']) {
    // Wrong code with complete input uses general error box
    $_SESSION['error'] = 'De 2FA code is onjuist.';
    header('Location: ../pages/2fa_login.php');
    exit();
}

if (trim($code) === $_SESSION['2fa_code']) {
    $_SESSION['two_factor'] = '0';
    $_SESSION['2fa_email_sent'] = false;
    unset($_SESSION['2fa_code']);
    $_SESSION['gebruikermail'] = $x['email'];
    $_SESSION['two_factor'] = $x['is2faingeschakeld'];
    setcookie('two_factor', '0', time() + (86400 * 30), "/"); 
    $_SESSION['rol'] = $x['rol'];
    $_SESSION['2fa_code'] = '';
    echo "<script>window.location.href='../pages/home.php'</script>";
    exit();
}

// Fallback
$_SESSION['two_factor_error'] = true;
header('Location: ../pages/2fa_login.php');
exit();

?>