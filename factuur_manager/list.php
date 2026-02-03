<?php
// tijdelijke databaseverbinding
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'dms-spakaas';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die('Database verbinding mislukt: ' . $conn->connect_error);
}

if (isset($_GET['action'], $_GET['factuurid'])) {
    $action = $_GET['action'];
    $factuurid = (int) $_GET['factuurid'];

    if ($factuurid > 0) {
        if ($action === 'markeer_betaald') {
            $updateSql = "UPDATE factuur SET betaalstatus = 1 WHERE factuurid = $factuurid";
            $conn->query($updateSql);
        } elseif ($action === 'markeer_open') {
            $updateSql = "UPDATE factuur SET betaalstatus = 0 WHERE factuurid = $factuurid";
            $conn->query($updateSql);
        } elseif ($action === 'herinnering_verstuurd') {
            $updateSql = "UPDATE factuur SET herinneringsmailstatus = 1 WHERE factuurid = $factuurid";
            $conn->query($updateSql);
        } elseif ($action === 'herinnering_reset') {
            $updateSql = "UPDATE factuur SET herinneringsmailstatus = 0 WHERE factuurid = $factuurid";
            $conn->query($updateSql);
        }
    }
}

$sql = "
    SELECT 
        f.factuurid,
        f.factuurdatum,
        f.totaalbedragexbtw,
        f.btwpercentage,
        f.betaalstatus,
        f.herinneringsmailstatus,
        f.aantalmensen,
        f.toelichting,
        g.naam AS klantnaam,
        g.email AS klantemail,
        l.naam AS lodgetype_naam,
        a.starttijd,
        a.eindtijd
    FROM factuur f
    INNER JOIN gebruiker g ON f.gebruikerid = g.gebruikerid
    INNER JOIN lodgetype l ON f.lodgetypeid = l.typeid
    INNER JOIN afspraak a ON f.afspraakid = a.afspraakid
    ORDER BY f.factuurdatum DESC, f.factuurid DESC
";

$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Factuuroverzicht</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #fafafa; }
        .status-open { color: red; font-weight: bold; }
        .status-betaald { color: green; font-weight: bold; }
    </style>
</head>
<body>

<h1>Factuuroverzicht</h1>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <thead>
        <tr>
            <th>Factuur ID</th>
            <th>Factuurdatum</th>
            <th>Klant</th>
            <th>Email</th>
            <th>Lodgetype</th>
            <th>Starttijd afspraak</th>
            <th>Eindtijd afspraak</th>
            <th>Aantal mensen</th>
            <th>Totaal ex. BTW</th>
            <th>BTW %</th>
            <th>Betaalstatus</th>
            <th>Herinneringsmail</th>
            <th>Toelichting</th>
            <th>Acties</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            $betaalstatusText = $row['betaalstatus'] ? 'Betaald' : 'Open';
            $betaalstatusClass = $row['betaalstatus'] ? 'status-betaald' : 'status-open';
            $herinneringText = $row['herinneringsmailstatus'] ? 'Verstuurd' : 'Niet verstuurd';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['factuurid']); ?></td>
                <td><?php echo htmlspecialchars($row['factuurdatum']); ?></td>
                <td><?php echo htmlspecialchars($row['klantnaam']); ?></td>
                <td><?php echo htmlspecialchars($row['klantemail']); ?></td>
                <td><?php echo htmlspecialchars($row['lodgetype_naam']); ?></td>
                <td><?php echo htmlspecialchars($row['starttijd']); ?></td>
                <td><?php echo htmlspecialchars($row['eindtijd']); ?></td>
                <td><?php echo htmlspecialchars($row['aantalmensen']); ?></td>
                <td><?php echo htmlspecialchars($row['totaalbedragexbtw']); ?></td>
                <td><?php echo htmlspecialchars($row['btwpercentage']); ?></td>
                <td class="<?php echo $betaalstatusClass; ?>"><?php echo $betaalstatusText; ?></td>
                <td><?php echo $herinneringText; ?></td>
                <td><?php echo htmlspecialchars($row['toelichting']); ?></td>
                <td>
                    <?php if ($row['betaalstatus']): ?>
                        <a href="?action=markeer_open&amp;factuurid=<?php echo (int)$row['factuurid']; ?>">Markeer als open</a>
                    <?php else: ?>
                        <a href="?action=markeer_betaald&amp;factuurid=<?php echo (int)$row['factuurid']; ?>">Markeer als betaald</a>
                    <?php endif; ?>
                    <br>
                    <?php if ($row['herinneringsmailstatus']): ?>
                        <a href="?action=herinnering_reset&amp;factuurid=<?php echo (int)$row['factuurid']; ?>">Herinnering terugzetten</a>
                    <?php else: ?>
                        <a href="?action=herinnering_verstuurd&amp;factuurid=<?php echo (int)$row['factuurid']; ?>">Herinnering verstuurd</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Er zijn nog geen facturen gevonden.</p>
<?php endif; ?>

<?php
if ($result) {
    $result->free();
}
$conn->close();
?>

</body>
</html>

