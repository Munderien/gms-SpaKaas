<?php
session_start();
session_destroy();
header("Location: ../pages/inlog.php");
exit();
?>