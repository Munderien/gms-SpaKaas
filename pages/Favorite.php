<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'dms-spakaas';
$user = 'root';
$pass = '';

if (isset($_SESSION['gebruikerId'])) {
    $conn = new mysqli($host, $user, $pass, $db);

    if (!$conn->connect_error) {
        $parts = parse_url($_SERVER['REQUEST_URI']);
        parse_str($parts['query'] ?? '', $query);
        $lodgetypeId = (int) ($query['lodgetypeid'] ?? 0);

        $gebruikerId = (int) $_SESSION['gebruikerId'];

        $potentialFavoriteQuery = $conn->prepare("SELECT COUNT(*) FROM favoriete WHERE lodgetypeid = ? AND gebruikerid = ?");
        $alreadyExists = false;
        if ($potentialFavoriteQuery) {
            $potentialFavoriteQuery->bind_param("ii", $lodgetypeId, $gebruikerId);
            $potentialFavoriteQuery->execute();
            $potentialFavoriteQuery->bind_result($count);
            $potentialFavoriteQuery->fetch();
            $potentialFavoriteQuery->close();
            $alreadyExists = $count > 0;
        }

        if (!$alreadyExists) {
            $stmt = $conn->prepare("INSERT INTO favoriete (gebruikerid, lodgetypeid) VALUES (?, ?)");

            if ($stmt) {
                $stmt->bind_param("ii", $gebruikerId, $lodgetypeId);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $stmt = $conn->prepare("DELETE FROM favoriete WHERE gebruikerid = ? AND lodgetypeid = ?");

            if ($stmt) {
                $stmt->bind_param("ii", $gebruikerId, $lodgetypeId);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    $conn->close();
}

header("Location: Lodgepdp.php?lodgetypeid=" . (int) ($query['lodgetypeid'] ?? 0));
exit;
