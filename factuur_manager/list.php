<?php
require '../inlog/config.php';

if (isset($_GET['action'], $_GET['factuurid'])) {
    $action = $_GET['action'];
    $factuurid = (int) $_GET['factuurid'];

    if ($factuurid > 0) {
        if ($action === 'markeer_betaald') {
            $stmt = $db->prepare("UPDATE factuur SET betaalstatus = 1 WHERE factuurid = :id");
            $stmt->execute([':id' => $factuurid]);
        } elseif ($action === 'markeer_open') {
            $stmt = $db->prepare("UPDATE factuur SET betaalstatus = 0 WHERE factuurid = :id");
            $stmt->execute([':id' => $factuurid]);
        } elseif ($action === 'herinnering_verstuurd') {
            $stmt = $db->prepare("UPDATE factuur SET herinneringsmailstatus = 1 WHERE factuurid = :id");
            $stmt->execute([':id' => $factuurid]);
        } elseif ($action === 'herinnering_reset') {
            $stmt = $db->prepare("UPDATE factuur SET herinneringsmailstatus = 0 WHERE factuurid = :id");
            $stmt->execute([':id' => $factuurid]);
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

$stmt = $db->query($sql);
$result = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Factuuroverzicht</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
            color: #222;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #1f2933;
        }

        .table-wrapper {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            padding: 16px;
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 900px;
        }

        th, td {
            padding: 10px 12px;
            text-align: left;
            font-size: 14px;
            border-bottom: 1px solid #e0e7ff;
        }

        th {
            background: linear-gradient(to bottom, #f9fafb, #e5edff);
            color: #111827;
            font-weight: 600;
            white-space: nowrap;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tr:hover {
            background-color: #eef2ff;
        }

        .status-open {
            color: #b91c1c;
            font-weight: 600;
        }

        .status-betaald {
            color: #15803d;
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            background-color: #e5edff;
            color: #1f2933;
        }

        .actions a {
            display: inline-block;
            margin: 2px 0;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
            color: #1f2933;
            background-color: #e5edff;
            border: 1px solid #cbd2ff;
        }

        .actions a:hover {
            background-color: #d0d7ff;
        }

        .actions a:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<h1>Factuuroverzicht</h1>

<div class="table-wrapper">
<?php if (!empty($result)): ?>
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
        <?php foreach ($result as $row): ?>
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
                <td><span class="badge"><?php echo $herinneringText; ?></span></td>
                <td><?php echo htmlspecialchars($row['toelichting']); ?></td>
                <td class="actions">
                    <?php if ($row['betaalstatus']): ?>
                        <a href="?action=markeer_open&amp;factuurid=<?php echo (int)$row['factuurid']; ?>">Markeer als open</a>
                    <?php else: ?>
                        <a href="?action=markeer_betaald&amp;factuurid=<?php echo (int)$row['factuurid']; ?>">Markeer als betaald</a>
                    <?php endif; ?>
                    <?php if ($row['herinneringsmailstatus']): ?>
                        <a href="?action=herinnering_reset&amp;factuurid=<?php echo (int)$row['factuurid']; ?>">Herinnering terugzetten</a>
                    <?php else: ?>
                        <a href="?action=herinnering_verstuurd&amp;factuurid=<?php echo (int)$row['factuurid']; ?>">Herinnering verstuurd</a>
                    <?php endif; ?>
                    <a href="print.php?factuurid=<?php echo (int)$row['factuurid']; ?>" target="_blank">Afdrukken / PDF</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Er zijn nog geen facturen gevonden.</p>
<?php endif; ?>
</div>

</body>
</html>
