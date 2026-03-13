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

// Get all unique lodge types
$lodgeTypes = [];
$query = "SELECT lt.lodgetypeid, lt.naam, lt.beschrijving, lt.capaciteit, lt.prijs
          FROM lodgetype lt
          ORDER BY lt.naam ASC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lodgeTypes[] = $row;
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
        <h1>Onze Lodgetypes</h1>
        <div class="lodges-grid">
            <?php foreach ($lodgeTypes as $lodgeType): ?>
                <div class="lodge-card" onclick="toggleDetails(this)">
                    <div class="lodge-card-header">
                        <h2><?php echo htmlspecialchars($lodgeType['naam']); ?></h2>
                    </div>
                    <div class="lodge-card-content">
                        <p class="description"><?php echo htmlspecialchars(substr($lodgeType['beschrijving'], 0, 100)) . '...'; ?></p>
                        <div class="lodge-card-footer">
                            <span class="price">€<?php echo htmlspecialchars($lodgeType['prijs']); ?></span>
                        </div>
                    </div>
                    <div class="lodge-card-details">
                        <div class="details-content">
                            <div class="detail-item">
                                <label>Beschrijving:</label>
                                <p><?php echo htmlspecialchars($lodgeType['beschrijving']); ?></p>
                            </div>
                            <div class="detail-item">
                                <label>Capaciteit:</label>
                                <p><?php echo htmlspecialchars($lodgeType['capaciteit']); ?> personen</p>
                            </div>
                            <div class="detail-item">
                                <label>Prijs:</label>
                                <p>€<?php echo htmlspecialchars($lodgeType['prijs']); ?></p>
                            </div>
                        </div>
                        <button class="close-details" onclick="bookAppointment(<?php echo htmlspecialchars($lodgeType['lodgetypeid']); ?>)">Maak afspraak</button>
                        <button class="close-details" onclick="productLodge(<?php echo htmlspecialchars($lodgeType['lodgetypeid']); ?>)">Details</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function toggleDetails(card) {
            card.classList.toggle("expanded");
        }

        function bookAppointment(lodgeTypeId) {
            event.stopPropagation();
            window.location.href = 'MaakAfspraak.php?lodgetypeid=' + lodgeTypeId;
        }
        function productLodge(lodgetypeId) {
            event.stopPropagation();
            window.location.href = 'Lodgepdp.php?lodgetypeid=' + lodgetypeId;
        }
    </script>
</body>

</html>