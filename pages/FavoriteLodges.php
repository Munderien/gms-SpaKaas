<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$favoriteLodgeTypes = [];

// Only fetch for logged-in users
if (isset($_SESSION['gebruikerId'])) {
    $host = 'localhost';
    $db   = 'dms-spakaas';
    $user = 'root';
    $pass = '';

    $conn = new mysqli($host, $user, $pass, $db);

    if (!$conn->connect_error) {
        $gebruikerId = (int) $_SESSION['gebruikerId'];

        $stmt = $conn->prepare("
            SELECT lt.lodgetypeid, lt.naam, lt.beschrijving, lt.capaciteit, lt.prijs
            FROM favoriete f
            JOIN lodgetype lt ON lt.lodgetypeid = f.lodgetypeid
            WHERE f.gebruikerid = ?
              ORDER BY f.favorieteid DESC
        ");

        if ($stmt) {
            $stmt->bind_param('i', $gebruikerId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $favoriteLodgeTypes[] = $row;
            }
            $stmt->close();
        }

        $conn->close();
    }
}
?>
<link rel="stylesheet" href="../Style/FavoriteLodges.css">
<?php if (!empty($favoriteLodgeTypes)): ?>
<section class="fav-lodges-section">
    <h2 class="fav-lodges-title">&#128155; Mijn favoriete lodges</h2>
    <div class="fav-lodges-grid">
        <?php foreach ($favoriteLodgeTypes as $lodge): ?>
            <div class="fav-lodge-card" onclick="window.location.href='Lodgepdp.php?lodgetypeid=<?= (int) $lodge['lodgetypeid'] ?>'">
                <div class="fav-lodge-header">
                    <span class="fav-lodge-name"><?= htmlspecialchars($lodge['naam']) ?></span>
                    <span class="fav-heart">&#128155;</span>
                </div>
                <div class="fav-lodge-desc"><?= htmlspecialchars(substr($lodge['beschrijving'], 0, 90)) ?>...</div>
                <div class="fav-lodge-footer">
                    <span class="fav-lodge-price">&euro;<?= htmlspecialchars($lodge['prijs']) ?></span>
                    <span class="fav-lodge-capacity"><?= htmlspecialchars($lodge['capaciteit']) ?> personen</span>
                </div>
                <button class="fav-lodge-btn" onclick="event.stopPropagation(); window.location.href='MaakAfspraak.php?lodgetypeid=<?= (int) $lodge['lodgetypeid'] ?>'">
                    Boek nu
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
