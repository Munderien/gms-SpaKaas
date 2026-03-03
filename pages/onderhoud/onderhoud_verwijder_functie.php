<?php
include("../config.php");
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM onderhoud WHERE onderhoudid = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    header("Location: onderhoud_taken.php");
} else {
    echo "No task ID provided.";
    exit();
}
?>