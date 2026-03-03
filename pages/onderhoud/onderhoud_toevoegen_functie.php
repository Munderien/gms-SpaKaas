<?php
include("../config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lodgeid = $_POST['lodgeid'];
    $monteurid = $_POST['monteurid'];
    $omschrijving = $_POST['omschrijving'];
    $status = $_POST['status'];
    $prioriteit = $_POST['prioriteit'];

    if(empty($lodgeid) || empty($monteurid) || empty($status) || empty($prioriteit)) {
        echo "All fields are required.";
        exit();
    }

    $sql = "INSERT INTO onderhoud (lodgeid, monteurid, omschrijving, status, prioriteit) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    if ($stmt->execute([$lodgeid, $monteurid, $omschrijving, $status, $prioriteit])) {
        header("Location: onderhoud_taken.php");
        exit();
    } else {
        echo "Error adding task.";
    }
}
?>