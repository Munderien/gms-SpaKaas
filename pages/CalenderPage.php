<?php
session_start();
include('config.php');

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: inlog.php');
    exit;
}
$stmt = $db->prepare("SELECT rol FROM gebruiker WHERE gebruikerid = ?");
$stmt->execute([$_SESSION['gebruikerId']]);
$_SESSION['rol'] = (int) $stmt->fetchColumn();

if ($_SESSION['rol'] = 0) {
    die('Geen toegang');
}
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

include __DIR__ . '/Calendar.php';
$calendar = new Calendar($_SESSION['calendar_date']);
$getDataCalendar = new getDataCalendar();
$selectedLodgeType = isset($_GET['lodgetype']) ? $_GET['lodgetype'] : 'all';
$lodgeTypes = $getDataCalendar->getLodgeTypes();

$data = $getDataCalendar->getData($selectedLodgeType);
foreach ($data as $events) {
    $calendar->addEvent(
        $events['afspraakid'],
        $events['gebruikerid'],
        $events['lodgeid'],
        $events['starttijd'],
        $events['eindtijd'],
        $events['status'],
        $events['toelichting'],
        $events['aantalmensen'],
    );
}
?>
<html>

<head>
    <style>
        .calendar {
    width: 100%;
    max-width: 1400px;
    margin: 40px auto;
    font-family: 'Segoe UI', sans-serif;
}

/* ===== HEADER ===== */

.calendar .header {
    text-align: center;
    margin-bottom: 20px;
}

.calendar .month-year {
    font-size: 28px;
    font-weight: 700;
    color: #0f3d2e;
}

/* ===== WEEK DAY NAMES ===== */

.calendar .days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border-radius: 12px;
    overflow: hidden;
}

.calendar .day_name {
    background-color: #0f3d2e;
    color: white;
    text-align: center;
    padding: 14px 0;
    font-weight: 600;
    letter-spacing: 1px;
}

/* ===== DAY CELLS ===== */

.calendar .day_num {
    min-height: 140px;
    border: 1px solid #e1e5e8;
    padding: 10px;
    background-color: #f8f9fa;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: 0.2s ease;
}

.calendar .day_num:hover {
    background-color: #ffffff;
}

.calendar .day_num span {
    font-size: 14px;
    font-weight: bold;
    color: #0f3d2e;
}

/* Current Day Highlight */
.calendar .day_num.current_Day {
    background-color: #e7dcc0;
}

/* Days outside current month */
.calendar .day_num.ignore {
    background-color: #f1f1f1;
    color: #b5b5b5;
}

/* ===== EVENTS ===== */

.calendar .event {
    display: block;              /* makes it a box */
    margin-top: 8px;
    padding: 6px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;       /* removes underline */
    color: white;                /* text color */
    background-color: #2e7d5b;   /* default green */
    transition: 0.2s ease;
}

.calendar .event:hover {
    transform: scale(1.03);
    opacity: 0.9;
}

/* ===== TOOLBAR WRAPPER ===== */

.calendar-toolbar {
    max-width: 1400px;
    margin: 20px auto 10px auto;
    padding: 16px 20px;
    background: white;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}

/* ===== FORM LAYOUT ===== */

.filter-form {
    display: flex;
    align-items: end;
    gap: 20px;
    flex-wrap: wrap;
}

/* ===== LABEL ===== */

.filter-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #0f3d2e;
}

/* ===== SELECT ===== */

.filter-group select {
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid #dcdcdc;
    font-size: 14px;
    min-width: 220px;
    outline: none;
    transition: 0.2s ease;
}

.filter-group select:focus {
    border-color: #2e7d5b;
    box-shadow: 0 0 0 3px rgba(46,125,91,0.15);
}

/* ===== BUTTON ===== */

.btn-filter {
    background-color: #0f3d2e;
    color: white;
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
}

.btn-filter:hover {
    background-color: #2e7d5b;
    transform: translateY(-1px);
}

/* ===== HEADER ===== */

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background: white;
    border-radius: 14px 14px 0 0;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}

.month-year {
    font-size: 20px;
    font-weight: 700;
    color: #0f3d2e;
}

.nav-left,
.nav-right {
    display: flex;
    gap: 12px;
    align-items: center;
}

/* ===== PREV/NEXT BUTTON ===== */

.btn-nav {
    padding: 8px 14px;
    background: #f4f7f6;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #0f3d2e;
    border: 1px solid #e3e3e3;
    cursor: pointer;
    transition: 0.2s ease;
}

.btn-nav:hover {
    background: #e9f3ef;
    transform: translateY(-1px);
}

/* ===== PRIMARY BUTTON ===== */

.btn-primary {
    padding: 9px 16px;
    background: #0f3d2e;
    color: white;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: 0.2s ease;
    box-shadow: 0 4px 10px rgba(15,61,46,0.2);
}

.btn-primary:hover {
    background: #2e7d5b;
    transform: translateY(-1px);
}
    </style>
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
    <?php include '../navbar.php'; ?>

        
</body>

</html>