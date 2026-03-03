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
    if(trim($code)=='' || trim($code) != $_SESSION['2fa_code']){
        echo "De code is onjuist, u wordt terug gestuurd naar de inlog pagina";
        var_dump($code);
        var_dump($_SESSION['2fa_code']);

        unset($_SESSION['2fa_code']);
        //header("refresh:1;url=../pages/inlog.php");
        exit();
        }
        if(trim($code) == $_SESSION['2fa_code']){
            $_SESSION['two_factor'] = '0';
            $_SESSION['2fa_email_sent'] = false;
            unset($_SESSION['2fa_code']);
            $_SESSION['gebruikermail']=$x['email'];
            $_SESSION['two_factor']=$x['is2faingeschakeld'];
            $_SESSION['rol']=$x['rol'];
    $_SESSION['2fa_code'] = '';
    echo "<script>window.location.href='../pages/home.php'</script>";
    exit();
}
?>