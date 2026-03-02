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
<html>

<head>
    <link rel="stylesheet" href="Style/Table.css">
</head>

<body>
    <table>
        <tr>
            <th>Medewerker</th>
            <th></th>
            <th>Maandag</th>
            <th>Dinsdag</th>
            <th>Woensdag</th>
            <th>Donderdag</th>
            <th>Vrijdag</th>
            <th>Zaterdag</th>
            <th>Zondag</th>
        </tr>

        <?php
        $dagen = [
            1 => 'Maandag',
            2 => 'Dinsdag',
            3 => 'Woensdag',
            4 => 'Donderdag',
            5 => 'Vrijdag',
            6 => 'Zaterdag',
            0 => 'Zondag'
        ];

        foreach ($schema as $gebruikerid => $employee) {

            echo "<tr>";

            echo "<td>" . $employee['naam'] . "</td>";
            echo "<td></td>";

            foreach ($dagen as $dagNummer => $dagNaam) {
                echo "<td>";

                if (isset($employee['dagen'][$dagNummer])) {
                    echo $employee['dagen'][$dagNummer]['start'] . " - " .
                        $employee['dagen'][$dagNummer]['end'];
                } else {
                    echo "VRIJ";
                }

                echo "</td>";
            }

            echo "</tr>";
        }
        ?>
    </table>

</body>

</html>