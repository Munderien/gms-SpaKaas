<?php
// reusable navigation bar for authenticated users
session_start();
if (!isset($_SESSION['gebruikerId'])) {
    // redirect unauthenticated users
    header("Location: inlog.php");
    exit();
}
?>
<link rel="stylesheet" href="../style/topbar.css">
<nav class="navbar">
    <a href="home.php"><button id="navbarItem">Home</button></a>
    <!-- Add other common links here -->
    
    <div class="dropdown" style="float:right;">
        <button class="dropbtn"><?php echo htmlspecialchars(
            isset($_SESSION['gebruikermail']) ? $_SESSION['gebruikermail'] : 'Profile'
        ); ?> &#x25BC;</button>
        <div class="dropdown-content">
            <?php
            if(isset($_SESSION['gebruikerId'])) {
                echo"<a href='edit_user.php'>Edit Profile</a>";
                echo"<a href='logout.php'>Logout</a>";
            }
            else {
                echo"<a href='inlog.php'>Login</a>";
            }

            ?>
        </div>
    </div>
</nav>