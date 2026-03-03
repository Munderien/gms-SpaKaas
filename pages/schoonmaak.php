<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dms-spakaas";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Get all active cleaners (rol = 1)
$sql_schoonmakers = "SELECT gebruikerid, naam FROM gebruiker WHERE rol = 1 AND isactief = 1 ORDER BY naam";
$result_schoonmakers = $conn->query($sql_schoonmakers);
$schoonmakers = $result_schoonmakers->fetch_all(MYSQLI_ASSOC);

// Handle CRUD operations
$message = "";
$messageType = "";

// CREATE - Add new cleaning appointment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $lodgeid = intval($_POST['lodgeid']);
    //$gebruikerid = intval($_POST['gebruikerid']);
    $gebruikerid = 1;
    $datum = $_POST['datum'];
    $status = 'Gepland';

    $sql = "INSERT INTO schoonmaak (gebruikerid, lodgeid, status, datum) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $gebruikerid, $lodgeid, $status, $datum);

    if ($stmt->execute()) {
        $message = "Schoonmaakbeurt succesvol toegevoegd!";
        $messageType = "success";
    } else {
        $message = "Fout bij toevoegen: " . $stmt->error;
        $messageType = "error";
    }
    $stmt->close();
}

// UPDATE - Change cleaning appointment date/time and cleaner
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $schoonmaakid = intval($_POST['schoonmaakid']);
    $datum = $_POST['datum'];
    $gebruikerid = intval($_POST['gebruikerid']);

    $sql = "UPDATE schoonmaak SET datum = ?, gebruikerid = ? WHERE schoonmaakid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $datum, $gebruikerid, $schoonmaakid);

    if ($stmt->execute()) {
        $message = "Schoonmaakbeurt succesvol bijgewerkt!";
        $messageType = "success";
    } else {
        $message = "Fout bij bijwerken: " . $stmt->error;
        $messageType = "error";
    }
    $stmt->close();
}

// DELETE - Remove cleaning appointment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $schoonmaakid = intval($_POST['schoonmaakid']);

    $sql = "DELETE FROM schoonmaak WHERE schoonmaakid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $schoonmaakid);

    if ($stmt->execute()) {
        $message = "Schoonmaakbeurt succesvol verwijderd!";
        $messageType = "success";
    } else {
        $message = "Fout bij verwijderen: " . $stmt->error;
        $messageType = "error";
    }
    $stmt->close();
}

// UPDATE - Change cleaning status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $schoonmaakid = intval($_POST['schoonmaakid']);
    $status = $_POST['status'];

    $sql = "UPDATE schoonmaak SET status = ? WHERE schoonmaakid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $schoonmaakid);

    if ($stmt->execute()) {
        $message = "Status succesvol bijgewerkt!";
        $messageType = "success";
    } else {
        $message = "Fout bij statusupdate: " . $stmt->error;
        $messageType = "error";
    }
    $stmt->close();
}

// Function to get cleaning duration based on lodge capacity
function getCleaningDuration($capacity) {
    return ceil($capacity * 7.5); // Duration in minutes
}

// Function to calculate end time
function calculateEndTime($startTime, $durationMinutes) {
    $start = new DateTime($startTime);
    $start->modify("+{$durationMinutes} minutes");
    return $start->format('Y-m-d H:i:s');
}

// Get all lodges with cleaning status
$sql = "SELECT 
            l.lodgeid, 
            l.huisnummer,
            lt.naam as lodge_type,
            lt.capaciteit,
            MAX(s.schoonmaakid) as latest_schoonmaakid,
            MAX(s.datum) as latest_datum,
            MAX(s.status) as latest_status
        FROM lodge l
        LEFT JOIN lodgetype lt ON l.typeid = lt.lodgetypeid
        LEFT JOIN schoonmaak s ON l.lodgeid = s.lodgeid
        GROUP BY l.lodgeid, l.huisnummer, lt.naam, lt.capaciteit
        ORDER BY l.huisnummer";

$result = $conn->query($sql);
$lodges = $result->fetch_all(MYSQLI_ASSOC);

// Get all cleaning appointments
$sql_schoonmaak = "SELECT 
                    s.schoonmaakid,
                    s.lodgeid,
                    s.datum,
                    s.status,
                    s.gebruikerid,
                    g.naam as gebruikersnaam,
                    l.huisnummer,
                    lt.capaciteit
                FROM schoonmaak s
                LEFT JOIN gebruiker g ON s.gebruikerid = g.gebruikerid
                LEFT JOIN lodge l ON s.lodgeid = l.lodgeid
                LEFT JOIN lodgetype lt ON l.typeid = lt.lodgetypeid
                ORDER BY s.datum DESC";

$result_schoonmaak = $conn->query($sql_schoonmaak);
$schoonmaakAfspraken = $result_schoonmaak->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schoonmaakbeheer - DMS Spakaas</title>
    <link rel="stylesheet" href="schoonmaak.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container">
        <header>
            <h1>Schoonmaakbeheer</h1>
            <p>Beheer schoonmaakbeurten voor alle lodges</p>
        </header>

        <div class="content">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Section: Lodges Overview -->
            <div class="section">
                <h2>Overzicht Lodges</h2>
                
                <?php if (empty($lodges)): ?>
                    <div class="empty-state">
                        <p>Geen lodges gevonden in het systeem.</p>
                    </div>
                <?php else: ?>
                    <div class="lodge-grid">
                        <?php foreach ($lodges as $lodge): ?>
                            <?php
                            $statusClass = 'status-needs-cleaning';
                            $statusText = 'Schoonmaak nodig';

                            if ($lodge['latest_status'] == 'Gepland') {
                                $statusClass = 'status-planned';
                                $statusText = 'Gepland';
                            } elseif ($lodge['latest_status'] == 'In uitvoering') {
                                $statusClass = 'status-in-progress';
                                $statusText = 'In uitvoering';
                            } elseif ($lodge['latest_status'] == 'Voltooid') {
                                $statusClass = 'status-completed';
                                $statusText = 'Voltooid';
                            }

                            $duration = getCleaningDuration($lodge['capaciteit']);
                            $hoursMinutes = floor($duration / 60) . 'u ' . ($duration % 60) . 'min';
                            ?>
                            <div class="lodge-card">
                                <div class="lodge-header">
                                    <h3>Lodge #{<?php echo htmlspecialchars($lodge['huisnummer']); ?>}</h3>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </div>

                                <div class="lodge-info">
                                    <p><strong>Type:</strong> <?php echo htmlspecialchars($lodge['lodge_type']); ?></p>
                                    <p><strong>Capaciteit:</strong> <?php echo htmlspecialchars($lodge['capaciteit']); ?> personen</p>
                                    <p><strong>Schoonmaakduur:</strong> <?php echo $hoursMinutes; ?></p>
                                    <?php if ($lodge['latest_datum']): ?>
                                        <p><strong>Laatste afspraak:</strong> <?php echo date('d-m-Y H:i', strtotime($lodge['latest_datum'])); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="lodge-actions">
                                    <button class="btn btn-primary btn-small" onclick="openAddModal(<?php echo $lodge['lodgeid']; ?>, '<?php echo htmlspecialchars($lodge['huisnummer']); ?>', <?php echo $duration; ?>)">
                                        Voeg toe
                                    </button>
                                    <?php if ($lodge['latest_schoonmaakid']): ?>
                                        <button class="btn btn-warning btn-small" onclick="openEditModal(<?php echo $lodge['latest_schoonmaakid']; ?>)">
                                            Bewerk
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Section: All Cleaning Appointments -->
            <div class="section">
                <h2>📅 Alle Schoonmaakafspraken</h2>
                
                <?php if (empty($schoonmaakAfspraken)): ?>
                    <div class="empty-state">
                        <p>Geen schoonmaakafspraken gevonden.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Lodge</th>
                                    <th>Begindatum</th>
                                    <th>Eindtijd</th>
                                    <th>Duur</th>
                                    <th>Status</th>
                                    <th>Schoonmaker</th>
                                    <th>Acties</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schoonmaakAfspraken as $afspraak): ?>
                                    <?php
                                    $duration = getCleaningDuration($afspraak['capaciteit']);
                                    $endTime = calculateEndTime($afspraak['datum'], $duration);
                                    $durationHours = floor($duration / 60);
                                    $durationMinutes = $duration % 60;
                                    $hoursMinutes = $durationHours . 'u ' . $durationMinutes . 'min';

                                    $statusClass = 'status-needs-cleaning';
                                    if ($afspraak['status'] == 'Gepland') $statusClass = 'status-planned';
                                    elseif ($afspraak['status'] == 'In uitvoering') $statusClass = 'status-in-progress';
                                    elseif ($afspraak['status'] == 'Voltooid') $statusClass = 'status-completed';
                                    ?>
                                    <tr>
                                        <td><strong>Lodge #{<?php echo htmlspecialchars($afspraak['huisnummer']); ?>}</strong></td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($afspraak['datum'])); ?></td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($endTime)); ?></td>
                                        <td><?php echo $hoursMinutes; ?></td>
                                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($afspraak['status']); ?></span></td>
                                        <td><?php echo htmlspecialchars($afspraak['gebruikersnaam'] ?? 'Niet toegewezen'); ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-small" onclick="openEditModal(<?php echo $afspraak['schoonmaakid']; ?>)">
                                                Verzet
                                            </button>
                                            <button class="btn btn-danger btn-small" onclick="openDeleteModal(<?php echo $afspraak['schoonmaakid']; ?>)">
                                                Verwijder
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal: Add New Cleaning Appointment -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Voeg Schoonmaakbeurt Toe</h2>
                <button class="close-modal" onclick="closeAddModal()">×</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="lodgeid" id="addLodgeId">

                <div class="form-group">
                    <label>Lodge</label>
                    <input type="text" id="addLodgeName" disabled>
                </div>

                <div class="form-group">
                    <label for="addGebruikerid">Schoonmaker</label>
                    <select id="addGebruikerid" name="gebruikerid" required>
                        <option value="">Selecteer schoonmaker</option>
                        <?php foreach ($schoonmakers as $schoonmaker): ?>
                            <option value="<?php echo $schoonmaker['gebruikerid']; ?>">
                                <?php echo htmlspecialchars($schoonmaker['naam']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Begindatum en Tijd</label>
                    <input type="datetime-local" name="datum" id="addDatum" required>
                </div>

                <div class="duration-info">
                    <p><strong>Schoonmaakduur:</strong> <span id="addDuration"></span></p>
                    <p><strong>Geschatte eindtijd:</strong> <span id="addEndTime">-</span></p>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeAddModal()">Annuleer</button>
                    <button type="submit" class="btn btn-primary">Voeg toe</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Cleaning Appointment -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Verzet Schoonmaakbeurt</h2>
                <button class="close-modal" onclick="closeEditModal()">×</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="schoonmaakid" id="editSchoonmaakId">

                <div class="form-group">
                    <label for="editGebruikerid">Schoonmaker</label>
                    <select id="editGebruikerid" name="gebruikerid" required>
                        <option value="">Selecteer schoonmaker</option>
                        <?php foreach ($schoonmakers as $schoonmaker): ?>
                            <option value="<?php echo $schoonmaker['gebruikerid']; ?>">
                                <?php echo htmlspecialchars($schoonmaker['naam']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nieuwe Begindatum en Tijd</label>
                    <input type="datetime-local" name="datum" id="editDatum" required>
                </div>

                <div class="duration-info">
                    <p><strong>Schoonmaakduur:</strong> <span id="editDuration"></span></p>
                    <p><strong>Geschatte eindtijd:</strong> <span id="editEndTime">-</span></p>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeEditModal()">Annuleer</button>
                    <button type="submit" class="btn btn-primary">Bijwerken</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Delete Confirmation -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Verwijder Schoonmaakbeurt</h2>
                <button class="close-modal" onclick="closeDeleteModal()">×</button>
            </div>
            <p style="color: #555; margin-bottom: 20px;">Weet u zeker dat u deze schoonmaakbeurt wilt verwijderen? Deze actie kan niet ongedaan gemaakt worden.</p>
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="schoonmaakid" id="deleteSchoonmaakId">

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Annuleer</button>
                    <button type="submit" class="btn btn-danger">Verwijder</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Global duration variable
        let currentDuration = 0;

        // Add Modal Functions
        function openAddModal(lodgeId, lodgeName, duration) {
            document.getElementById('addLodgeId').value = lodgeId;
            document.getElementById('addLodgeName').value = 'Lodge #' + lodgeName;
            currentDuration = duration;

            const hours = Math.floor(duration / 60);
            const minutes = duration % 60;
            document.getElementById('addDuration').textContent = hours + 'u ' + minutes + 'min';

            // Set datetime to now
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours_now = String(now.getHours()).padStart(2, '0');
            const minutes_now = String(now.getMinutes()).padStart(2, '0');
            
            document.getElementById('addDatum').value = `${year}-${month}-${day}T${hours_now}:${minutes_now}`;
            document.getElementById('addDatum').addEventListener('change', calculateAddEndTime);
            document.getElementById('addGebruikerid').value = '';

            calculateAddEndTime();
            document.getElementById('addModal').classList.add('show');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.remove('show');
        }

        function calculateAddEndTime() {
            const datum = document.getElementById('addDatum').value;
            if (!datum) {
                document.getElementById('addEndTime').textContent = '-';
                return;
            }

            const date = new Date(datum);
            date.setMinutes(date.getMinutes() + currentDuration);

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            document.getElementById('addEndTime').textContent = `${day}-${month}-${year} ${hours}:${minutes}`;
        }

        // Edit Modal Functions
        function openEditModal(schoonmaakId) {
            // Fetch the current appointment data
            fetch('get_schoonmaak.php?id=' + schoonmaakId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editSchoonmaakId').value = data.schoonmaakid;
                    document.getElementById('editDatum').value = data.datum.replace(' ', 'T');
                    document.getElementById('editGebruikerid').value = data.gebruikerid;

                    currentDuration = data.duration;
                    const hours = Math.floor(data.duration / 60);
                    const minutes = data.duration % 60;
                    document.getElementById('editDuration').textContent = hours + 'u ' + minutes + 'min';

                    document.getElementById('editDatum').addEventListener('change', calculateEditEndTime);
                    calculateEditEndTime();

                    document.getElementById('editModal').classList.add('show');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Fout bij het ophalen van afspraakgegevens');
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }

        function calculateEditEndTime() {
            const datum = document.getElementById('editDatum').value;
            if (!datum) {
                document.getElementById('editEndTime').textContent = '-';
                return;
            }

            const date = new Date(datum);
            date.setMinutes(date.getMinutes() + currentDuration);

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            document.getElementById('editEndTime').textContent = `${day}-${month}-${year} ${hours}:${minutes}`;
        }

        // Delete Modal Functions
        function openDeleteModal(schoonmaakId) {
            document.getElementById('deleteSchoonmaakId').value = schoonmaakId;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            let addModal = document.getElementById('addModal');
            let editModal = document.getElementById('editModal');
            let deleteModal = document.getElementById('deleteModal');

            if (event.target == addModal) {
                addModal.classList.remove('show');
            }
            if (event.target == editModal) {
                editModal.classList.remove('show');
            }
            if (event.target == deleteModal) {
                deleteModal.classList.remove('show');
            }
        }
    </script>
</body>
</html>