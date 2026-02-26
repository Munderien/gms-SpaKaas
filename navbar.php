<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div style="background-color:#eee;padding:8px;">
    <a href="/dms-spakaas/gms-SpaKaas/home.php">Home</a> |
    <a href="/dms-spakaas/gms-SpaKaas/Calendar.php">Agenda</a> |
    <a href="/dms-spakaas/gms-SpaKaas/factuur_manager/list.php">Facturen</a>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>
        | <a href="/dms-spakaas/gms-SpaKaas/manager/addRole.php">Gebruikersrollen</a>
    <?php endif; ?>

    <?php if (isset($_SESSION['gebruikerId'])): ?>
        | <a href="/dms-spakaas/gms-SpaKaas/logout.php">Uitloggen</a>
    <?php else: ?>
        | <a href="/dms-spakaas/gms-SpaKaas/inlog/inlog.php">Inloggen</a>
    <?php endif; ?>
</div>
