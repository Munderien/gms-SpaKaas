<?php
session_start();

if (!isset($_SESSION['calendar_date'])) {
    $_SESSION['calendar_date'] = date("Y-m-d");
}

if (array_key_exists('back', $_POST)) {
    $timestamp = strtotime('-1 month', strtotime($_SESSION['calendar_date']));
    $_SESSION['calendar_date'] = date("Y-m-d", $timestamp);
} else if (array_key_exists('next', $_POST)) {
    $timestamp = strtotime('+1 month', strtotime($_SESSION['calendar_date']));
    $_SESSION['calendar_date'] = date("Y-m-d", $timestamp);
}

include 'Calendar.php';
$calendar = new Calendar($_SESSION['calendar_date']);
$getDataCalendar = new getDatacalendar();
$selectedLodgeType = isset($_GET['lodgetype']) ? $_GET['lodgetype'] : 'all';
$lodgeTypes = $getDataCalendar->getLodgeTypes();

$data = $getDataCalendar->getData($selectedLodgeType);
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
    <div class="calendar">
        <h1>Plain PHP MVC Starter</h1>
        <p>Dit is de eerste test! het werkte!</p>
        <div class="calendar-header">
            <div class="nav-left">
                <button type="button" class="btn-nav" onclick="navigateCalendar('back')">
                    ← Previous
                </button>
            </div>

            <div class="nav-right">
                <button type="button" class="btn-nav" onclick="navigateCalendar('next')">
                    Next →
                </button>

                <button type="button" class="btn-primary"
                    onclick="window.location.href='maakAfspraak.php'">
                    + New Appointment
                </button>
            </div>
        </div>
        <div class="calendar-toolbar">
            <form action="" method="get" class="filter-form">
                <div class="filter-group">
                    <label for="lodgetype">Filter op lodgetype:</label>
                    <select name="lodgetype" id="lodgetype">
                        <option value="all">Alle lodge types</option>
                        <?php foreach ($lodgeTypes as $type): ?>
                            <?php $safeType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>
                            <option value="<?php echo $safeType; ?>" <?php echo $selectedLodgeType === $type ? 'selected' : ''; ?>>
                                <?php echo $safeType; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-filter">Toepassen</button>
            </form>
        </div>
        <?= $calendar ?>

        <script>
            function navigateCalendar(direction) {
                const data = new FormData();
                data.append(direction, '1');
                fetch(window.location.href, {
                        method: 'POST',
                        body: data
                    })
                    .then(() => window.location.reload())
                    .catch(console.error);
            }
        </script>

        
</body>

</html>