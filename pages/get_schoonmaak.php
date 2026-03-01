<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dms-spakaas";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database connection failed']));
}

$conn->set_charset("utf8mb4");

$schoonmaakid = intval($_GET['id']);

$sql = "SELECT 
            s.schoonmaakid,
            s.datum,
            s.gebruikerid,
            l.huisnummer,
            lt.capaciteit
        FROM schoonmaak s
        LEFT JOIN lodge l ON s.lodgeid = l.lodgeid
        LEFT JOIN lodgetype lt ON l.typeid = lt.typeid
        WHERE s.schoonmaakid = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $schoonmaakid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    http_response_code(404);
    exit(json_encode(['error' => 'Schoonmaakafspraak not found']));
}

$afspraak = $result->fetch_assoc();

$duration = ceil($afspraak['capaciteit'] * 7.5);

header('Content-Type: application/json');
echo json_encode([
    'schoonmaakid' => $afspraak['schoonmaakid'],
    'datum' => $afspraak['datum'],
    'gebruikerid' => $afspraak['gebruikerid'],
    'duration' => $duration,
    'huisnummer' => $afspraak['huisnummer']
]);

$stmt->close();
$conn->close();
?>