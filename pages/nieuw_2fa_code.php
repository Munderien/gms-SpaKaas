<?php
session_start();
include("config.php");

$code = rand(100000, 999999);
$_SESSION['2fa_code'] = $code;

// include 2FA email helpers; path should go up one directory from pages
require_once __DIR__ . '/../email/emailFuncties.php';

if (isset($_SESSION['gebruikerId']) && trim($_SESSION['gebruikerId']) != '') {
    //send2faMail($_SESSION['gebruikermail'], $code);
}

header("Location: 2fa_login.php");
exit();
?>