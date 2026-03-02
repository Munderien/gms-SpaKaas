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
    <a href="home.php"><button id="navbarItem">Home</button></a>
    <!-- Add other common links here -->
    
    <div class="dropdown" style="float:right;">
        <button class="dropbtn"><?php echo htmlspecialchars(
            isset($_SESSION['gebruikermail']) ? $_SESSION['gebruikermail'] : 'Profile'
        ); ?> &#x25BC;</button>
        <div class="dropdown-content">
            <a href="edit_user.php">Edit Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</nav>