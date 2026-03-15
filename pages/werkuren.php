<?php
session_start();

if ($_SESSION['rol'] != 3) {
    header('Location: inlog.php');
    exit;
}

// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "dms-spakaas";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Initialize variables
$gebruikerid = $_POST['gebruikerid'] ?? $_GET['gebruikerid'] ?? '';
$week_offset = isset($_GET['week']) ? intval($_GET['week']) : 0;
$message = '';
$error = '';

// Calculate week start and end dates
$start_date = date('Y-m-d', strtotime('monday this week +' . $week_offset . ' week'));
$end_date = date('Y-m-d', strtotime('sunday this week +' . $week_offset . ' week'));

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_workuur'])) {
        // Add new work hours (exception)
        $begintijd = $_POST['begintijd'] ?? '';
        $begintijd_time = $_POST['begintijd_time'] ?? '';
        $eindtijd_time = $_POST['eindtijd_time'] ?? '';
        
        if (!$begintijd || !$begintijd_time || !$eindtijd_time) {
            $error = "Alle velden zijn verplicht.";
        } else {
            // Convert time to datetime
            $begintijd_full = $begintijd . ' ' . $begintijd_time;
            $eindtijd_full = $begintijd . ' ' . $eindtijd_time;
            
            if (strtotime($begintijd_full) >= strtotime($eindtijd_full)) {
                $error = "Begintijd moet voor eindtijd liggen.";
            } else {
                $stmt = $conn->prepare("INSERT INTO werkuur (gebruikerid, begintijd, eindtijd) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $gebruikerid, $begintijd_full, $eindtijd_full);
                
                if ($stmt->execute()) {
                    $message = "Werkuur succesvol toegevoegd.";
                } else {
                    $error = "Fout bij toevoegen: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    } elseif (isset($_POST['edit_workuur'])) {
        // Edit existing work hours - ONLY TIME, NOT DATE
        $werkuur_id = $_POST['werkuur_id'] ?? '';
        $begintijd_time = $_POST['begintijd_time'] ?? '';
        $eindtijd_time = $_POST['eindtijd_time'] ?? '';
        
        if (!$werkuur_id || !$begintijd_time || !$eindtijd_time) {
            $error = "Alle velden zijn verplicht.";
        } else {
            // Get original date from database
            $date_stmt = $conn->prepare("SELECT begintijd FROM werkuur WHERE werkuurid = ? AND gebruikerid = ?");
            $date_stmt->bind_param("ii", $werkuur_id, $gebruikerid);
            $date_stmt->execute();
            $date_result = $date_stmt->get_result()->fetch_assoc();
            $date_stmt->close();
            
            if (!$date_result) {
                $error = "Werkuur niet gevonden.";
            } else {
                $original_date = date('Y-m-d', strtotime($date_result['begintijd']));
                $begintijd_full = $original_date . ' ' . $begintijd_time;
                $eindtijd_full = $original_date . ' ' . $eindtijd_time;
                
                if (strtotime($begintijd_full) >= strtotime($eindtijd_full)) {
                    $error = "Begintijd moet voor eindtijd liggen.";
                } else {
                    $stmt = $conn->prepare("UPDATE werkuur SET begintijd = ?, eindtijd = ? WHERE werkuurid = ? AND gebruikerid = ?");
                    $stmt->bind_param("ssii", $begintijd_full, $eindtijd_full, $werkuur_id, $gebruikerid);
                    
                    if ($stmt->execute()) {
                        $message = "Werkuur succesvol bijgewerkt.";
                    } else {
                        $error = "Fout bij bijwerken: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    } elseif (isset($_POST['delete_workuur'])) {
        // Delete work hours exception
        $werkuur_id = $_POST['werkuur_id'] ?? '';
        
        if ($werkuur_id) {
            $stmt = $conn->prepare("DELETE FROM werkuur WHERE werkuurid = ? AND gebruikerid = ?");
            $stmt->bind_param("ii", $werkuur_id, $gebruikerid);
            
            if ($stmt->execute()) {
                $message = "Werkuur succesvol verwijderd. Schema geldt weer voor deze dag.";
            } else {
                $error = "Fout bij verwijderen: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['add_schedule'])) {
        // Add weekly schedule
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        
        if (!$day_of_week || !$start_time || !$end_time) {
            $error = "Er bestaat hier al een schema.";
        } elseif (strtotime($start_time) >= strtotime($end_time)) {
            $error = "Starttijd moet voor eindtijd liggen.";
        } else {
            // Check if schedule already exists for this day
            $check_stmt = $conn->prepare("SELECT weekschemaid FROM weekschema WHERE gebruikerid = ? AND dag_van_week = ?");
            $check_stmt->bind_param("ii", $gebruikerid, $day_of_week);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $existing = $check_result->fetch_assoc();
            $check_stmt->close();
            
            if ($existing) {
                // Update existing
                $stmt = $conn->prepare("UPDATE weekschema SET starttijd = ?, eindtijd = ? WHERE weekschemaid = ?");
                $stmt->bind_param("ssi", $start_time, $end_time, $existing['weekschemaid']);
            } else {
                // Insert new
                $stmt = $conn->prepare("INSERT INTO weekschema (gebruikerid, dag_van_week, starttijd, eindtijd) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $gebruikerid, $day_of_week, $start_time, $end_time);
            }
            
            if ($stmt->execute()) {
                $message = "Weekschema succesvol opgeslagen.";
            } else {
                $error = "Fout bij opslaan: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_schedule'])) {
        // Delete weekly schedule
        $schedule_id = $_POST['schedule_id'] ?? '';
        
        if ($schedule_id) {
            $stmt = $conn->prepare("DELETE FROM weekschema WHERE weekschemaid = ? AND gebruikerid = ?");
            $stmt->bind_param("ii", $schedule_id, $gebruikerid);
            
            if ($stmt->execute()) {
                $message = "Weekschema verwijderd.";
            } else {
                $error = "Fout bij verwijderen: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch employees (role 1 and 2)
$employees_array = [];
$employees_result = $conn->query("SELECT gebruikerid, naam, rol FROM gebruiker WHERE rol IN (1, 2) ORDER BY naam");
if ($employees_result) {
    while ($row = $employees_result->fetch_assoc()) {
        $employees_array[] = $row;
    }
}

// Fetch selected employee's weekly schedules
$schedules = [];
$work_hours_by_day = [];

if ($gebruikerid) {
    // Get all weekly schedules for this employee
    $stmt = $conn->prepare("SELECT weekschemaid, dag_van_week, starttijd, eindtijd FROM weekschema WHERE gebruikerid = ? ORDER BY dag_van_week");
    $stmt->bind_param("i", $gebruikerid);
    $stmt->execute();
    $schedule_result = $stmt->get_result();
    while ($row = $schedule_result->fetch_assoc()) {
        $schedules[$row['dag_van_week']] = $row;
    }
    $stmt->close();
    
    // Get work hours for this week
    $stmt = $conn->prepare("SELECT werkuurid, begintijd, eindtijd FROM werkuur WHERE gebruikerid = ? AND DATE(begintijd) >= ? AND DATE(begintijd) <= ? ORDER BY begintijd");
    $stmt->bind_param("iss", $gebruikerid, $start_date, $end_date);
    $stmt->execute();
    $work_hours_result = $stmt->get_result();
    
    // Organize work hours by day
    while ($hour = $work_hours_result->fetch_assoc()) {
        $day_num = date('w', strtotime($hour['begintijd']));
        $day_num = $day_num == 0 ? 6 : $day_num - 1; // Convert Sunday=0 to 6
        $work_hours_by_day[$day_num][] = $hour;
    }
    $stmt->close();
}

$days = ['Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag', 'Zondag'];
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uren Beheer - DMS Spakaas</title>
    <link rel="stylesheet" href="werkuren.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <!-- MODAL VOOR WERKUUR BEWERKEN -->
    <div id="editWorkuurModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Werkuur Aanpassen</h2>
                <span class="close" onclick="closeWorkuurModal()">&times;</span>
            </div>
            <form method="POST" action="" id="editWorkuurForm">
                <input type="hidden" name="gebruikerid" value="<?php echo htmlspecialchars($gebruikerid); ?>">
                <input type="hidden" name="werkuur_id" id="edit_werkuur_id">
                <div class="modal-body">
                    <div id="editDateInfo" class="modal-info"></div>
                    <div id="scheduleCompare" class="schedule-compare"></div>
                    
                    <div class="form-group">
                        <label for="edit_begintijd_time">Begintijd:</label>
                        <input type="time" id="edit_begintijd_time" name="begintijd_time" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_eindtijd_time">Eindtijd:</label>
                        <input type="time" id="edit_eindtijd_time" name="eindtijd_time" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeWorkuurModal()">Annuleren</button>
                    <button type="submit" name="edit_workuur">Opslaan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL VOOR WERKUUR TOEVOEGEN (UITZONDERING) -->
    <div id="addWorkuurModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Werkuur Toevoegen</h2>
                <span class="close" onclick="closeAddWorkuurModal()">&times;</span>
            </div>
            <form method="POST" action="" id="addWorkuurForm">
                <input type="hidden" name="gebruikerid" value="<?php echo htmlspecialchars($gebruikerid); ?>">
                <div class="modal-body">
                    <div id="schemaInfo" class="modal-info" style="display: none;"></div>
                    
                    <div class="form-group">
                        <label for="add_date">Datum:</label>
                        <input type="date" id="add_date" name="begintijd" required>
                    </div>
                    <div class="form-group">
                        <label for="add_begintijd_time">Begintijd:</label>
                        <input type="time" id="add_begintijd_time" name="begintijd_time" required>
                    </div>
                    <div class="form-group">
                        <label for="add_eindtijd_time">Eindtijd:</label>
                        <input type="time" id="add_eindtijd_time" name="eindtijd_time" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeAddWorkuurModal()">Annuleren</button>
                    <button type="submit" name="add_workuur">Toevoegen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL VOOR WEEKSCHEMA BEWERKEN/TOEVOEGEN -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="scheduleModalTitle">Weekschema Bewerken</h2>
                <span class="close" onclick="closeScheduleModal()">&times;</span>
            </div>
            <form method="POST" action="" id="scheduleForm">
                <input type="hidden" name="gebruikerid" value="<?php echo htmlspecialchars($gebruikerid); ?>">
                <input type="hidden" name="schedule_id" id="edit_schedule_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="schedule_day">Dag van de Week:</label>
                        <select name="day_of_week" id="schedule_day" required>
                            <option value="">-- Kies een dag --</option>
                            <?php foreach ($days as $index => $day): ?>
                                <option value="<?php echo $index; ?>"><?php echo htmlspecialchars($day); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="schedule_start">Starttijd:</label>
                        <input type="time" id="schedule_start" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label for="schedule_end">Eindtijd:</label>
                        <input type="time" id="schedule_end" name="end_time" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeScheduleModal()">Annuleren</button>
                    <button type="submit" name="add_schedule">Opslaan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="container">
        <header>
            <h1>Uren Beheer</h1>
            <p>Beheer weekschema (standaard rooster) en werkuren (uitzonderingen)</p>
        </header>

        <div class="content">
            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- CONTROLS: EMPLOYEE & WEEK SELECTION -->
            <div class="controls">
                <div class="form-group">
                    <label for="employee_select">Selecteer Werknemer:</label>
                    <form method="GET" action="" id="employeeForm" style="margin: 0;">
                        <select name="gebruikerid" id="employee_select" onchange="document.getElementById('employeeForm').submit()">
                            <option value="">-- Kies een werknemer --</option>
                            <?php foreach ($employees_array as $employee): ?>
                                <option value="<?php echo htmlspecialchars($employee['gebruikerid']); ?>" 
                                        <?php echo ($gebruikerid == $employee['gebruikerid']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($employee['naam']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <?php if ($gebruikerid): ?>
                    <div>
                        <form method="GET" action="" class="week-navigation">
                            <input type="hidden" name="gebruikerid" value="<?php echo htmlspecialchars($gebruikerid); ?>">
                            <button type="submit" name="week" value="<?php echo $week_offset - 1; ?>">Vorige</button>
                            <div class="week-info">
                                <?php echo 'Week van ' . date('d-m-Y', strtotime($start_date)) . ' t/m ' . date('d-m-Y', strtotime($end_date)); ?>
                            </div>
                            <button type="submit" name="week" value="<?php echo $week_offset + 1; ?>">Volgende</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($gebruikerid): ?>
                <!-- WEEKSCHEMA MANAGEMENT FORM -->
                <div class="schedule-form">
                    <h3>Weekschema Beheren (Standaard Rooster)</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_day">Dag:</label>
                            <select id="new_day" name="day_select">
                                <option value="">-- Selecteer dag --</option>
                                <?php foreach ($days as $index => $day): ?>
                                    <option value="<?php echo $index; ?>"><?php echo htmlspecialchars($day); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="new_start">Starttijd:</label>
                            <input type="time" id="new_start" name="new_start">
                        </div>
                        <div class="form-group">
                            <label for="new_end">Eindtijd:</label>
                            <input type="time" id="new_end" name="new_end">
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="button" onclick="addOrEditSchedule()">Schema Opslaan</button>
                        </div>
                    </div>
                </div>

                <!-- WEEKLY SCHEDULE TABLE -->
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th class="day-column">Dag</th>
                            <th class="schedule-column">Weekschema</th>
                            <th class="workour-column">Werkuren (Deze Week)</th>
                            <th class="action-column">Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($day_num = 0; $day_num < 7; $day_num++): ?>
                            <?php
                            // Calculate actual date for this day
                            $date_for_day = date('Y-m-d', strtotime($start_date . ' +' . $day_num . ' days'));
                            $has_schedule = isset($schedules[$day_num]);
                            $has_workours = isset($work_hours_by_day[$day_num]);
                            $schema_start = $has_schedule ? $schedules[$day_num]['starttijd'] : '';
                            $schema_end = $has_schedule ? $schedules[$day_num]['eindtijd'] : '';
                            ?>
                            <tr>
                                <td class="day-column"><?php echo htmlspecialchars($days[$day_num]); ?><br><small style="color: #999;"><?php echo date('d-m', strtotime($date_for_day)); ?></small></td>
                                
                                <!-- Schedule Column -->
                                <td class="schedule-column">
                                    <?php if ($has_schedule): ?>
                                        <div class="schedule-item">
                                            <strong><?php echo htmlspecialchars($schema_start . ' - ' . $schema_end); ?></strong>
                                            <div style="margin-top: 8px;">
                                                <button type="button" class="btn-small btn-edit" onclick="openScheduleEditModal(<?php echo $schedules[$day_num]['weekschemaid']; ?>, '<?php echo $day_num; ?>', '<?php echo htmlspecialchars($schema_start); ?>', '<?php echo htmlspecialchars($schema_end); ?>')">Bewerk</button>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="gebruikerid" value="<?php echo htmlspecialchars($gebruikerid); ?>">
                                                    <input type="hidden" name="schedule_id" value="<?php echo $schedules[$day_num]['weekschemaid']; ?>">
                                                    <button type="submit" name="delete_schedule" class="btn-small btn-delete" onclick="return confirm('Schema verwijderen?')">Verwijder</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-schedule">Geen schema</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Work Hours Column - NOW SHOWS SCHEDULE OR OVERRIDES IT -->
                                <td class="workour-column">
                                    <?php if ($has_workours): ?>
                                        <?php foreach ($work_hours_by_day[$day_num] as $hour): ?>
                                            <div class="workour-item overridden" onclick="openWorkuurEditModal(
                                                <?php echo $hour['werkuurid']; ?>,
                                                '<?php echo date('Y-m-d', strtotime($hour['begintijd'])); ?>',
                                                '<?php echo date('H:i', strtotime($hour['begintijd'])); ?>',
                                                '<?php echo date('H:i', strtotime($hour['eindtijd'])); ?>'
                                            )">
                                                <div class="workour-item-time">
                                                    <?php echo date('H:i', strtotime($hour['begintijd'])); ?> - <?php echo date('H:i', strtotime($hour['eindtijd'])); ?>
                                                    <?php if ($has_schedule): ?>
                                                        <div class="schedule-compare">(<s><?php echo htmlspecialchars($schema_start); ?> - <?php echo htmlspecialchars($schema_end); ?></s>)</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="display: flex; gap: 5px;">
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="gebruikerid" value="<?php echo htmlspecialchars($gebruikerid); ?>">
                                                        <input type="hidden" name="werkuur_id" value="<?php echo $hour['werkuurid']; ?>">
                                                        <button type="submit" name="delete_workuur" class="btn-small btn-delete" onclick="event.stopPropagation(); return confirm('Verwijderen?')">Verwijder</button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php elseif ($has_schedule): ?>
                                        <!-- NO WORKUUR - SHOW SCHEDULE -->
                                        <div class="workour-item" onclick="openAddWorkuurModal(
                                            '<?php echo htmlspecialchars($date_for_day); ?>',
                                            '<?php echo htmlspecialchars($schema_start); ?>',
                                            '<?php echo htmlspecialchars($schema_end); ?>'
                                        )" style="background: #e7f3ff; border-left-color: #667eea; cursor: pointer;">
                                            <div class="workour-item-time">
                                                <?php echo htmlspecialchars($schema_start . ' - ' . $schema_end); ?>
                                                <div class="schedule-compare">(volgt schema)</div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- NO SCHEDULE, NO WORKUUR -->
                                        <span class="no-schedule">Geen schema</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Actions Column -->
                                <td class="action-column">
                                    <?php if ($has_schedule && !$has_workours): ?>
                                        <button 
                                            type="button" 
                                            class="btn-small btn-add" 
                                            onclick="openAddWorkuurModal(
                                                '<?php echo htmlspecialchars($date_for_day); ?>',
                                                '<?php echo htmlspecialchars($schema_start); ?>',
                                                '<?php echo htmlspecialchars($schema_end); ?>'
                                            )"
                                        >Uitzondering</button>
                                    <?php elseif ($has_schedule && $has_workours): ?>
                                        <span style="color: #999; font-size: 0.9em;">Aangepast</span>
                                    <?php else: ?>
                                        <button 
                                            type="button" 
                                            class="btn-small btn-add" 
                                            onclick="openAddWorkuurModal(
                                                '<?php echo htmlspecialchars($date_for_day); ?>',
                                                '',
                                                ''
                                            )"
                                        >Toevoegen</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <div style="text-align: center; padding: 50px; color: #999; font-size: 1.2em;">
                    Selecteer een werknemer om werkuren en schema's te beheren
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // WERKUUR MODALS
        function openWorkuurEditModal(id, date, begintijd, eindtijd) {
            document.getElementById('edit_werkuur_id').value = id;
            document.getElementById('edit_begintijd_time').value = begintijd;
            document.getElementById('edit_eindtijd_time').value = eindtijd;
            
            // Toon de datum in de info box
            const dateObj = new Date(date);
            const dateFormatted = dateObj.toLocaleDateString('nl-NL', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('editDateInfo').innerHTML = `<strong>📅 Datum:</strong> ${dateFormatted}`;
            
            document.getElementById('editWorkuurModal').style.display = 'block';
        }

        function closeWorkuurModal() {
            document.getElementById('editWorkuurModal').style.display = 'none';
        }

        function openAddWorkuurModal(dateString, defaultStart, defaultEnd) {
            document.getElementById('add_date').value = dateString;
            document.getElementById('add_begintijd_time').value = defaultStart || '';
            document.getElementById('add_eindtijd_time').value = defaultEnd || '';
            
            // Toon info over het weekschema als aanwezig
            const schemaInfo = document.getElementById('schemaInfo');
            if (defaultStart && defaultEnd) {
                schemaInfo.style.display = 'block';
                schemaInfo.innerHTML = `<strong>Weekschema:</strong> ${defaultStart} - ${defaultEnd}<br><em>(Pas de tijden aan voor een uitzondering, bv. eerder naar huis)</em>`;
            } else {
                schemaInfo.style.display = 'none';
            }
            
            document.getElementById('addWorkuurModal').style.display = 'block';
        }

        function closeAddWorkuurModal() {
            document.getElementById('addWorkuurModal').style.display = 'none';
        }

        // SCHEDULE MODALS
        function openScheduleEditModal(id, dag, start, end) {
            document.getElementById('scheduleModalTitle').textContent = 'Weekschema Bewerken';
            document.getElementById('edit_schedule_id').value = id;
            document.getElementById('schedule_day').value = dag;
            document.getElementById('schedule_start').value = start;
            document.getElementById('schedule_end').value = end;
            document.getElementById('scheduleModal').style.display = 'block';
        }

        function addOrEditSchedule() {
            const day = document.getElementById('new_day').value;
            const start = document.getElementById('new_start').value;
            const end = document.getElementById('new_end').value;

            if (!day || !start || !end) {
                alert('Vul alstublieft alle velden in');
                return;
            }

            document.getElementById('scheduleModalTitle').textContent = 'Weekschema Toevoegen';
            document.getElementById('edit_schedule_id').value = '';
            document.getElementById('schedule_day').value = day;
            document.getElementById('schedule_start').value = start;
            document.getElementById('schedule_end').value = end;
            document.getElementById('scheduleModal').style.display = 'block';
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').style.display = 'none';
        }

        // Close modals on outside click
        window.onclick = function(event) {
            let modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>