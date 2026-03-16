<?php
$db = new mysqli("localhost", "root", "", "dms-spakaas");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$sql = "
SELECT w.*, g.naam
FROM weekschema w
JOIN gebruiker g ON w.gebruikerid = g.gebruikerid
ORDER BY w.gebruikerid, w.dag_van_week
";

$result = $db->query($sql);

$schema = [];

while ($row = $result->fetch_assoc()) {

    $gebruikerid = $row['gebruikerid'];
    $dag = $row['dag_van_week'];

    // Save name once per employee
    $schema[$gebruikerid]['naam'] = $row['naam'];

    // Save shift per day
    $schema[$gebruikerid]['dagen'][$dag] = [
        'start' => $row['starttijd'],
        'end'   => $row['eindtijd']
    ];
}

?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medewerkers Rooster - DMS Spakaas</title>
    <link rel="stylesheet" href="../Style/medewerkers.css">
</head>

<body>
    <?php include '../navbar.php'; ?>

    <div class="container">
        <header>
            <h1>Medewerkers Rooster</h1>
            <p>Weekoverzicht van alle werkschema's</p>
        </header>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Medewerker</th>
                        <th>Maandag</th>
                        <th>Dinsdag</th>
                        <th>Woensdag</th>
                        <th>Donderdag</th>
                        <th>Vrijdag</th>
                        <th>Zaterdag</th>
                        <th>Zondag</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $dagen = [
                        0 => 'Maandag',
                        1 => 'Dinsdag',
                        2 => 'Woensdag',
                        3 => 'Donderdag',
                        4 => 'Vrijdag',
                        5 => 'Zaterdag',
                        6 => 'Zondag'
                    ];

                    foreach ($schema as $gebruikerid => $employee) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($employee['naam']) . "</td>";

                        foreach ($dagen as $dagNummer => $dagNaam) {
                            echo "<td>";
                            if (isset($employee['dagen'][$dagNummer])) {
                                $start = htmlspecialchars(substr($employee['dagen'][$dagNummer]['start'], 0, 5));
                                $end   = htmlspecialchars(substr($employee['dagen'][$dagNummer]['end'],   0, 5));
                                echo "<span class='shift'>{$start} &ndash; {$end}</span>";
                            } else {
                                echo "<span class='vrij'>vrij</span>";
                            }
                            echo "</td>";
                        }

                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>