<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'dms-spakaas';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

$cookieConsentGiven = isset($_COOKIE['cookieConsent']) && $_COOKIE['cookieConsent'] === 'accepted';
$recentLodgeTypeIds = [];

// 1) Logged-in users: fetch from DB
if (isset($_SESSION['gebruikerId']) && !$conn->connect_error) {
    $gebruikerId = (int) $_SESSION['gebruikerId'];
    $sql = $conn->prepare("SELECT lodgetypeid FROM viewed WHERE gebruikerid = ? ORDER BY viewed_at DESC LIMIT 5");
    if ($sql) {
        $sql->bind_param('i', $gebruikerId);
        $sql->execute();
        $result = $sql->get_result();
        while ($row = $result->fetch_assoc()) {
            $recentLodgeTypeIds[] = (int) $row['lodgetypeid'];
        }
        $sql->close();
    }
}

// 2) Guests (or fallback): read from cookie if consent given
if (empty($recentLodgeTypeIds) && $cookieConsentGiven && isset($_COOKIE['recentLodges'])) {
    $decoded = json_decode($_COOKIE['recentLodges'], true);
    if (is_array($decoded)) {
        $recentLodgeTypeIds = array_slice(array_map('intval', $decoded), 0, 5);
    }
}

// 3) Fetch full lodgetype details for each ID
$recentLodgeTypes = [];
if (!empty($recentLodgeTypeIds) && !$conn->connect_error) {
    $placeholders = implode(',', array_fill(0, count($recentLodgeTypeIds), '?'));
    $types = str_repeat('i', count($recentLodgeTypeIds));
    $detailStmt = $conn->prepare("SELECT lodgetypeid, naam, beschrijving, capaciteit, prijs
                                   FROM lodgetype
                                   WHERE lodgetypeid IN ($placeholders)");
    if ($detailStmt) {
        $detailStmt->bind_param($types, ...$recentLodgeTypeIds);
        $detailStmt->execute();
        $detailResult = $detailStmt->get_result();
        $fetched = [];
        while ($row = $detailResult->fetch_assoc()) {
            $fetched[$row['lodgetypeid']] = $row;
        }
        // Preserve the viewed order
        foreach ($recentLodgeTypeIds as $id) {
            if (isset($fetched[$id])) {
                $recentLodgeTypes[] = $fetched[$id];
            }
        }
        $detailStmt->close();
    }
}
?>
<link rel="stylesheet" href="../Style/RecentLodges.css">
<?php if (!empty($recentLodgeTypes)): ?>
<section class="recent-lodges-section">
    <h2 class="recent-lodges-title">Recent bekeken lodges</h2>
    <div class="recent-lodges-grid">
        <?php foreach ($recentLodgeTypes as $lodge): ?>
            <div class="recent-lodge-card" onclick="window.location.href='Lodgepdp.php?lodgetypeid=<?= (int) $lodge['lodgetypeid'] ?>'">
                <div class="recent-lodge-name"><?= htmlspecialchars($lodge['naam']) ?></div>
                <div class="recent-lodge-desc"><?= htmlspecialchars(substr($lodge['beschrijving'], 0, 80)) ?>...</div>
                <div class="recent-lodge-footer">
                    <span class="recent-lodge-price">&euro;<?= htmlspecialchars($lodge['prijs']) ?></span>
                    <span class="recent-lodge-capacity"><?= htmlspecialchars($lodge['capaciteit']) ?> personen</span>
                </div>
                <button class="recent-lodge-btn" onclick="event.stopPropagation(); window.location.href='MaakAfspraak.php?lodgetypeid=<?= (int) $lodge['lodgetypeid'] ?>'">
                    Boek nu
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
