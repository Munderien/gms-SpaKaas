
<html>
    <img src="image/placeholder.png" id="frontpageimage"></img>
</html>
<style>
    #frontpageimage{
        width:100%;
        height:350px;
        opacity:0.4;
    }
</style>
<?php
session_start();
if (!isset($_SESSION['gebruikerId'])) {
    header("Location: inlog.php");
    exit();
}
echo $_SESSION['gebruikerId'];
?>