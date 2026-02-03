<?php
include 'Calendar.php';
$calendar = new Calendar(date(("Y-m-d")));
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

    <?php
    //echo '<a href="maakAfspraak.php" class="add-appointment-button">Nieuwe afspraak</a>';
    //echo '<a href="planneritem.php" class="view-appointments-button">Bekijk afspraken</a>';
    ?>
</body>

</html>