<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'dms-spakaas';
$user = 'root';
$pass = '';

$recentlodges = [];

// 1) For guests: read from cookie
if (isset($_COOKIE['recentLodges'])) {
    $decoded = json_decode($_COOKIE['recentLodges'], true);
    if (is_array($decoded)) {
        $recentlodges = array_values(array_unique(array_map('intval', $decoded)));
        $recentlodges = array_slice($recentlodges, 0, 5);
    }
}

// 2) For logged-in users: merge cookie history into DB and sync cookie
if (isset($_SESSION['gebruikerId'])) {
    $conn = new mysqli($host, $user, $pass, $db);
    if (!$conn->connect_error) {
        $gebruikerId = (int) $_SESSION['gebruikerId'];
        $stmt = $conn->prepare("SELECT lodgetypeid FROM viewed WHERE gebruikerid = ? ORDER BY viewed_at DESC LIMIT 5");

        if ($stmt) {
            $stmt->bind_param('i', $gebruikerId);
            $stmt->execute();
            $result = $stmt->get_result();

            $dbRecent = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $dbRecent[] = (int) $row['lodgetypeid'];
                }
            }

            $mergedRecent = array_values(array_unique(array_merge($recentlodges, $dbRecent)));
            $mergedRecent = array_slice($mergedRecent, 0, 5);

            if (!empty($mergedRecent)) {
                $deleteAll = $conn->prepare("DELETE FROM viewed WHERE gebruikerid = ?");
                if ($deleteAll) {
                    $deleteAll->bind_param('i', $gebruikerId);
                    $deleteAll->execute();
                    $deleteAll->close();
                }

                $insertView = $conn->prepare("INSERT INTO viewed (gebruikerid, lodgetypeid, viewed_at) VALUES (?, ?, ?)");
                if ($insertView) {
                    $now = time();
                    foreach ($mergedRecent as $index => $lodgetypeId) {
                        $viewedAt = date('Y-m-d H:i:s', $now - $index);
                        $insertView->bind_param('iis', $gebruikerId, $lodgetypeId, $viewedAt);
                        $insertView->execute();
                    }
                    $insertView->close();
                }

                $recentlodges = $mergedRecent;
                setcookie('recentLodges', json_encode($recentlodges), time() + (14 * 24 * 60 * 60), '/');
            }

            $stmt->close();
        }

        $conn->close();
    }
}
