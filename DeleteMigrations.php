<?php

$host = "localhost";
$dbname = "dms-spakaas";
$user = "root";
$pass = "";

// Connect zonder database
$db = new PDO(
    "mysql:host=$host;charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Database verwijderen
$db->exec("DROP DATABASE IF EXISTS `$dbname`");

echo "🗑 Database `$dbname` is volledig verwijderd\n";
