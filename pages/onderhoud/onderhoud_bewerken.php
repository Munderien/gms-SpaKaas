<?php
include("../config.php");
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] ==0) {
    header("Location: ../home.php");
    exit();
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM onderhoud WHERE onderhoudid = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $taak = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$taak) {
        echo "Taak niet gevonden.";
        exit();
    }
} else {
    echo "Geen taak ID meegegeven.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Onderhoud taak bewerken</title>

<style>
body {
    font-family: Arial, Helvetica, sans-serif;
    background-color: #f4f6f9;
    margin: 0;
}

.container {
    display: flex;
    justify-content: center;
    padding: 40px 20px;
}

.form-card {
    background-color: white;
    width: 600px;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.form-card h2 {
    margin: 0 0 10px 0;
    color: #004080;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

label {
    font-weight: bold;
    font-size: 14px;
}

select, textarea {
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: 0.2s;
}

select:focus, textarea:focus {
    outline: none;
    border-color: #004080;
}

textarea {
    resize: vertical;
    min-height: 90px;
}

.button-group {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px;
}

input[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 8px 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.2s;
}

input[type="submit"]:hover {
    background-color: #43a047;
}
</style>
</head>
<body>

<div class="container">
<form class="form-card" action="onderhoud_bewerk_functie.php" method="post">

<h2>Onderhoud taak bewerken</h2>

<input type="hidden" name="onderhoudid" value="<?= $taak['onderhoudid']; ?>">

<!-- Lodge -->
<div class="form-group">
<label for="lodgeid">Lodge</label>
<select id="lodgeid" name="lodgeid" required>
<?php
$stmt = $db->prepare("SELECT lodgeid, huisnummer FROM lodge");
$stmt->execute();
$lodges = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($lodges as $lodge) {
    $selected = ($lodge['lodgeid'] == $taak['lodgeid']) ? "selected" : "";
    echo "<option value='{$lodge['lodgeid']}' $selected>{$lodge['huisnummer']}</option>";
}
?>
</select>
</div>

<!-- Monteur -->
<div class="form-group">
<label for="monteurid">Monteur</label>
<select id="monteurid" name="monteurid" required>
<?php
$stmt = $db->prepare("SELECT gebruikerid, naam FROM gebruiker WHERE rol = 2");
$stmt->execute();
$monteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($monteurs as $monteur) {
    $selected = ($monteur['gebruikerid'] == $taak['monteurid']) ? "selected" : "";
    echo "<option value='{$monteur['gebruikerid']}' $selected>{$monteur['naam']}</option>";
}
?>
</select>
</div>

<!-- Omschrijving -->
<div class="form-group">
<label for="omschrijving">Omschrijving</label>
<textarea id="omschrijving" name="omschrijving" required><?= htmlspecialchars($taak['omschrijving']); ?></textarea>
</div>

<!-- Status -->
<div class="form-group">
<label for="status">Status</label>
<select id="status" name="status">
<option value="open" <?= ($taak['status'] == 'open') ? 'selected' : ''; ?>>Open</option>
<option value="in_progress" <?= ($taak['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
<option value="gesloten" <?= ($taak['status'] == 'gesloten') ? 'selected' : ''; ?>>Gesloten</option>
</select>
</div>

<!-- Prioriteit -->
<div class="form-group">
<label for="prioriteit">Prioriteit</label>
<select id="prioriteit" name="prioriteit">
<option value="hoog" <?= ($taak['prioriteit'] == 'hoog') ? 'selected' : ''; ?>>Hoog</option>
<option value="middel" <?= ($taak['prioriteit'] == 'middel') ? 'selected' : ''; ?>>Middel</option>
<option value="laag" <?= ($taak['prioriteit'] == 'laag') ? 'selected' : ''; ?>>Laag</option>
<option value="dringend" <?= ($taak['prioriteit'] == 'dringend') ? 'selected' : ''; ?>>Dringend</option>
</select>
</div>

<div class="button-group">
<input type="submit" value="Wijzigingen opslaan">
</div>

</form>
</div>

</body>
</html>