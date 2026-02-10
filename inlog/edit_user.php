<?php
include("config.php");
include("library.php");
session_start();
?>
<form action ="update_2fa.php" method="post">
                <h6>2 factor authenticatie</h6>

<select class="form_select" name="two_factor" style="width: 29%;">
    <option value="0" <?php echo ($two_factor === '0') ? 'selected' : ''; ?>>Nee</option>
    <option value="1" <?php echo ($two_factor === '1') ? 'selected' : ''; ?>>Ja</option>
</select>
        <button id="save" style="width: 100px;">Opslaan</button>
</form>
<!--
<h1>Uw facturen</h1>
<table border="1">
    <tr>
        <th>datum</th>
        <th>bedragExBtw</th>
        <th>btwPercentage</th>
        <th>betaalstatus</th>
        <th>toelichting</th>
    <tr>
<?php
$stmt = $db->prepare("SELECT * FROM factuur WHERE gebruikerId = ?");
$stmt->execute([$_SESSION['userId']]);
$facturen = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($facturen) === 0) {
    echo "<tr><td colspan='5'>Geen facturen gevonden</td></tr>";
} else {
    foreach ($facturen as $factuur) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($factuur['Factuurdatum']) . "</td>";
        echo "<td>" . htmlspecialchars($factuur['TotaalBedragExBTW']) . "</td>";
        echo "<td>" . htmlspecialchars($factuur['BTWPercentage']) . "</td>";
        echo "<td>" . htmlspecialchars($factuur['Betaalstatus']) . "</td>";
        echo "<td>" . htmlspecialchars($factuur['Toelichting']) . "</td>";
        echo "</tr>";
    }
}
?>
</table>

<table border="1">
<H1>uw vroegere apk keuringen/reperaties</h1>
<tr>
    <th>voertuig</th>
    <th>datum</th>
    <th>diensttype</th>
    <th>kilometerstand</th>
    <th>toelichting</th>
</tr>
<?php

// get appointments and vehicle details
$stmt = $db->prepare(
  "SELECT a.*, v.merk, v.model, o.Type
   FROM afspraak a
   JOIN voertuig v ON a.voertuigId = v.voertuigid
   Join operatie o ON a.operatieId = o.operatieid
   WHERE a.gebruikerId = ? AND o.operatieId=a.operatieId AND v.gebruikerId = ? AND a.EindDatumTijd < NOW()"
   
);
$stmt->execute([$_SESSION['userId'], $_SESSION['userId']]);
$afspraken = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($afspraken) === 0) {
    echo "<tr><td colspan='5'>Geen afspraken gevonden</td></tr>";
} else {
    foreach ($afspraken as $afspraak) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($afspraak['merk']) . " " . htmlspecialchars($afspraak['model']) . "</td>";
        echo "<td>" . htmlspecialchars($afspraak['StartDatumTijd']) . "</td>";
        echo "<td>" . htmlspecialchars($afspraak['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($afspraak['KilometerstandBijService']) . "</td>";
        echo "<td>" . htmlspecialchars($afspraak['Toelichting']) . "</td>";
        echo "</tr>";
    }
}
?>
-->
</table>