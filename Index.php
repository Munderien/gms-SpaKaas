<?php
session_start();

if (!isset($_SESSION['calendar_date'])) {
    $_SESSION['calendar_date'] = date("Y-m-d");
}

if(array_key_exists('back', $_POST)) {
    $timestamp = strtotime('-1 month', strtotime($_SESSION['calendar_date']));
    $_SESSION['calendar_date'] = date("Y-m-d", $timestamp);
}
else if(array_key_exists('next', $_POST)) {
    $timestamp = strtotime('+1 month', strtotime($_SESSION['calendar_date']));
    $_SESSION['calendar_date'] = date("Y-m-d", $timestamp);
}

include 'Calendar.php';
$calendar = new Calendar($_SESSION['calendar_date']);
$getDataCalendar = new getDatacalendar();
$data = $getDataCalendar->getData();
foreach ($data as $events) {
    $calendar->addEvent(
        $events['afspraakid'],
        $events['gebruikerid'],
        $events['lodgeid'],
        $events['titel'],
        $events['starttijd'],
        $events['eindtijd'],
        $events['status'],
        $events['toelichting'],
        $events['prioriteit'] ?? 'low',
        $events['aantalmensen'],
    );
}
?>
<html>

<head>
    <link rel="stylesheet" href="Style/calendar.css">
</head>

<body>
    <h1>Plain PHP MVC Starter</h1>
    <p>Dit is de eerste test! het werkte!</p>
    <?= $calendar ?>

    <div class="nav-buttons">
        <button type="button" class="button" onclick="navigateCalendar('back')">back</button>
        <button type="button" class="button" onclick="navigateCalendar('next')">next</button>
    </div>
    <script>
        function navigateCalendar(direction) {
            const data = new FormData();
            data.append(direction, '1');
            fetch(window.location.href, { method: 'POST', body: data })
                .then(() => window.location.reload())
                .catch(console.error);
        }
    </script>
    <?php
    //echo '<a href="maakAfspraak.php" class="add-appointment-button">Nieuwe afspraak</a>';
    //echo '<a href="planneritem.php" class="view-appointments-button">Bekijk afspraken</a>';
    ?>
</body>
</html>