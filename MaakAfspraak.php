<?php
$host = 'localhost';
$db   = 'dms-spakaas';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
// get all klanten
$gebruikers = [];
$result = $conn->query("SELECT gebruikerid, naam FROM gebruiker where rol = 3 ORDER BY naam ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $gebruikers[] = $row;
    }
}

// get all lodges
$lodges = [];
$result = $conn->query("SELECT lodgeid FROM lodge");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lodges[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel     = $conn->real_escape_string($_POST['titel']);
    $beginTime = $conn->real_escape_string($_POST['starttijd']);
    $endTime   = $conn->real_escape_string($_POST['eindtijd']);
    $status = 'Afwachten';
    $desc      = $conn->real_escape_string($_POST['toelichting']);
    $prioriteit = $conn->real_escape_string($_POST['prioriteit']);
    $aantalmensen = $conn->real_escape_string($_POST['aantalmensen']);
    $userId    = intval($_POST['gebruikerid']);
    $lodgeId = intval($_POST['lodgeid']);

    //$beginDateTime = date('Y-m-d H:i:s', strtotime("$date $beginTime"));
    //$endDateTime   = date('Y-m-d H:i:s', strtotime("$date $endTime"));

    $today = date('Y-m-d');
    if ($beginTime < $today) {
        $message = "Datum mag niet in het verleden liggen.";
    } elseif ($endTime <= $beginTime) {
        $message = "Eindtijd moet later zijn dan begintijd.";
    } else {
        $sql = "INSERT INTO afspraak (gebruikerid, lodgeid, titel, starttijd, eindtijd,
        status, toelichting, prioriteit, aantalmensen)
                VALUES ('$userId', '$lodgeId', '$titel', '$beginTime', '$endTime', 
                '$status', '$desc', '$prioriteit', '$aantalmensen')";

        if ($conn->query($sql) === TRUE) {
            $message = "Afspraak succesvol toegevoegd en iedereen is gekoppeld!";
        } else {
            $message = "Fout bij toevoegen: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Afspraak toevoegen</title>
    <link rel="stylesheet" href="Style/MaakAfspraak.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const gebruikers = <?php echo json_encode($gebruikers); ?>;
            const lodges = <?php echo json_encode($lodges); ?>;

            const gebruikerSelect = document.getElementById('gebruikerSelect');
            const lodgeSelect = document.getElementById('lodgeSelect');

            gebruikers.forEach(g => {
                const opt = document.createElement('option');
                opt.value = g.gebruikerid;
                opt.textContent = g.naam;
                gebruikerSelect.appendChild(opt);
            });

            lodges.forEach(l => {
                const opt = document.createElement('option');
                opt.value = l.lodgeid;
                opt.textContent = l.lodgeid;
                lodgeSelect.appendChild(opt);
            });
        });
    </script>
</head>

<body>
    <h1>Nieuwe afspraak toevoegen</h1>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <form method="post">
        <label for="titel">Titel:</label><br>
        <input type="text" id="titel" name="titel" required><br><br>

        <label for="starttijd">Begintijd:</label><br>
        <input type="date" id="starttijd" name="starttijd" required><br><br>

        <label for="eindtijd">Eindtijd:</label><br>
        <input type="date" id="eindtijd" name="eindtijd" required><br><br>

        <label for="toelichting">Beschrijving:</label><br>
        <input type="text" id="toelichting" name="toelichting" required><br><br>

        <label for="prioriteit">Prioriteit</label>
        <select name="prioriteit" id="prioriteit" required>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
        </select>
        <br> <br>

        <label for="gebruikerSelect">Select gebruiker:</label>
        <select id="gebruikerSelect" name="gebruikerid" required>
            <option value="">-- Select --</option>
        </select>
        <br> <br>

        <label for="lodgeSelect">Select Lodge:</label>
        <select id="lodgeSelect" name="lodgeid" required>
            <option value="">-- Select --</option>
        </select>
        <br> <br>
        
        <label for="aantalmensen">aantal mensen:</label><br>
        <input type="number" id="aantalmensen" name="aantalmensen" required><br><br>

        <br><br>

        <input type="submit" value="Toevoegen">
    </form>
</body>

</html>