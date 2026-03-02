<?php
session_start();
$host = 'localhost';
$db   = 'dms-spakaas';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all lodges with their lodge type details
$lodges = [];
$query = "SELECT l.lodgeid, lt.typeid, lt.naam, lt.beschrijving, lt.capaciteit, lt.prijs
          FROM lodge l
          INNER JOIN lodgetype lt ON l.lodgetypeid = lt.typeid
          ORDER BY lt.naam ASC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lodges[] = $row;
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../Style/Lodges.css">
</head>

<body>
    <?php require_once __DIR__ . '/navbarKlant.php'; ?>
    
    <div class="lodges-container">
        <h1>Onze Lodges</h1>
        <div class="lodges-grid">
            <?php foreach ($lodges as $lodge): ?>
                <div class="lodge-card" onclick="toggleDetails(this)">
                    <div class="lodge-card-header">
                        <h2><?php echo htmlspecialchars($lodge['naam']); ?></h2>
                    </div>
                    <div class="lodge-card-content">
                        <p class="description"><?php echo htmlspecialchars(substr($lodge['beschrijving'], 0, 100)) . '...'; ?></p>
                        <div class="lodge-card-footer">
                            <span class="price">€<?php echo htmlspecialchars($lodge['prijs']); ?></span>
                        </div>
                    </div>
                    <div class="lodge-card-details">
                        <div class="details-content">
                            <div class="detail-item">
                                <label>Beschrijving:</label>
                                <p><?php echo htmlspecialchars($lodge['beschrijving']); ?></p>
                            </div>
                            <div class="detail-item">
                                <label>capaciteit:</label>
                                <p><?php echo htmlspecialchars($lodge['capaciteit']); ?> personen</p>
                            </div>
                            <div class="detail-item">
                                <label>Prijs:</label>
                                <p>€<?php echo htmlspecialchars($lodge['prijs']); ?></p>
                            </div>
                        </div>
                        <button class="close-details" onclick="bookAppointment(<?php echo htmlspecialchars($lodge['lodgeid']); ?>)">Maak afspraak</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function toggleDetails(card) {
            card.classList.toggle("expanded");
        }

        function bookAppointment(lodgeId) {
            event.stopPropagation();
            window.location.href = 'MaakAfspraak.php?lodgeid=' + lodgeId;
        }
    </script>
</body>

</html>