<?php

$host = "localhost";
$dbname = "dms-spakaas";
$user = "root";
$pass = "";

$db = new PDO(
    "mysql:host=$host;charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$db->exec("
    CREATE DATABASE IF NOT EXISTS `$dbname`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
");

$db->exec("USE `$dbname`");

foreach (glob(__DIR__ . '/migrations/*.php') as $file) {
    echo "Running " . basename($file) . "...\n";

    $migration = require $file;
    $migration($db);
}

echo "✔ Database & migrations klaar\n";
