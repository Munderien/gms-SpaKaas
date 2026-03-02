<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onderhoud taken</title>
    <!-- make sure we load the correct stylesheet and bust caches -->
    <link rel="stylesheet" href="./onderhoud_style.css?v=<?php echo time(); ?>">
</head>
<body>
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
    // lodge number header at top of card
    echo "<h3 class='lodge-header'>Lodge: " . $rij['lodgenaam'] . "</h3>";
    echo "<div class='taak-details'>";
    echo "<p>medewerker: <br>" . $rij['monteurid'] .  "</p>";
    // make omschrijving text itself clickable; show actual description in alert
    $omschrijving = $rij['omschrijving'];
    // hide only the label paragraph; keep the clickable span visible
    echo "<p style='display:none;'>omschrijving:<br></p>";
    echo "<input id='omschrijving-" . $rij['onderhoudid'] . "' type='hidden' value='" . htmlspecialchars($omschrijving) . "'>";
    echo "<span class='clickable-description' onclick=\"showDescription(document.getElementById('omschrijving-" . $rij['onderhoudid'] . "').value)\">omschrijving</span><br>";
    echo "<p>status:<br> " . $rij['status'] . "</p>";
    echo "<p>prioriteit:<br> " . $rij['prioriteit'] . "</p>";
    echo "</div>";
    echo "<div class='taak-actions'>";
    echo "<button class='edit-button' onClick=\"window.location.href='onderhoud_bewerken.php?id=" . "'\">Bewerken</button>";
    echo "<button class='delete-button' onClick=\"window.location.href='onderhoud_verwijder_functie.php?id=" . $rij['onderhoudid'] . "'\">Verwijderen</button>";
    echo "</div>";
    echo "</div>";
}
?>
</div>

<script>
// shows a constant notification when description is clicked
function showDescription(text) {
    alert(text);
}
</script>

</body>
</html>