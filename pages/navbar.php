<?php
// reusable navigation bar for authenticated users
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['gebruikerId'])) {
    header("Location: inlog.php");
    exit();
}

// Get user name from session
include("config.php");
$stmt = $db->prepare("SELECT naam FROM gebruiker WHERE gebruikerid = ?");
$stmt->execute([$_SESSION['gebruikerId']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$gebruikersnaam = $user ? $user['naam'] : 'Gebruiker';
$userInitial = strtoupper(substr($gebruikersnaam, 0, 1));
?>
<link rel="stylesheet" href="../Style/navbar.css">

<nav class="navbar">
    <a href="home.php" class="navbar-logo">Spa<span>Kaas</span></a>
    
    <ul class="navbar-menu">
        <li><a href="home.php">Home</a></li>
        <li><a href="MaakAfspraak.php">Boekingen</a></li>
        <li><a href="review.php">Reviews</a></li>
        <li><a href="profiel.php">Profiel</a></li>
        <li><a href="logout.php">Uitloggen</a></li>
    </ul>

    <div class="navbar-user">
        <div class="user-avatar"><?php echo $userInitial; ?></div>
        <span class="user-info"><?php echo htmlspecialchars(ucfirst($gebruikersnaam)); ?></span>
    </div>
</nav>