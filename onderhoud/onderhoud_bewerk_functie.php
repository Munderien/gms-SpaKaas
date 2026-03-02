<?php
include("../config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $onderhoudid = $_POST['onderhoudid'];
    $lodgeid = $_POST['lodgeid'];
    $monteurid = $_POST['monteurid'];
    $omschrijving = $_POST['omschrijving'];
    $status = $_POST['status'];
    $prioriteit = $_POST['prioriteit'];

    $sql = "UPDATE onderhoud 
            SET lodgeid = ?, 
                monteurid = ?, 
                omschrijving = ?, 
                status = ?, 
                prioriteit = ?
            WHERE onderhoudid = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$lodgeid, $monteurid, $omschrijving, $status, $prioriteit, $onderhoudid]);

    header("Location: onderhoud_taken.php"); 
    exit();
}
?>
