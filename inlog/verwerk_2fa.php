<?php
session_start();
$code=@htmlspecialchars($_POST['inlogcode']);
if(trim($_SESSION['gebruikermail'])=='' || trim($_SESSION['two_factor']) != '1'){
    echo "<script>window.location.href='inlog.php'</script>";
    exit();
}
if(trim($code)=='' || trim($code) != $_SESSION['2fa_code']){
    echo "De code is onjuist, u wordt terug gestuurd naar de inlog pagina";
    unset($_SESSION['2fa_code']);
header("refresh:1;url=inlog.php");
    exit();
}
if(trim($code) == $_SESSION['2fa_code']){
    $_SESSION['two_factor'] = '0';
    $_SESSION['2fa_email_sent'] = false;
    unset($_SESSION['2fa_code']);
    echo "<script>window.location.href='home.php'</script>";
    exit();
}
?>