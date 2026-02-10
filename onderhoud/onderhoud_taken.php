<link rel="stylesheet" href="onderhoud_style.css">
<div class="top-bar-onderhoud">
    <h2>Onderhoud taken</h2>
    <button id="add-task" onClick="window.location.href='onderhoud_toevoegen.php'">Taak toevoegen</button>
</div>
<div class="taken-container">
<?php
include("../inlog/config.php");

$sql = "
    SELECT onderhoud.*, lodge.huisnummer AS lodgenaam
    FROM onderhoud
    JOIN lodge ON onderhoud.lodgeid = lodge.lodgeid
    ORDER BY CASE onderhoud.prioriteit
        WHEN 'hoog' THEN 1
        WHEN 'middel' THEN 2
        WHEN 'laag' THEN 3
    END ASC
";

$statement = $db->prepare($sql);
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $rij) {
    echo "<div class='taak-item'>";
    echo "<h3>Lodge:" . $rij['lodgenaam'] . "</h3>";
    echo "<p>medewerker: <br>" . $rij['monteurid'] .  "</p>";
    echo "<p>omschrijving: <br>" . $rij['omschrijving'] . "</p>";
    echo "<p>status:<br> " . $rij['status'] . "</p>";
    echo "<p>prioriteit:<br> " . $rij['prioriteit'] . "</p>";
    echo "</div>";
}
?>
</div>