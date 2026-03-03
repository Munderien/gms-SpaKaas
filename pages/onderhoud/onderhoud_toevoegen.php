<?php
include("../config.php");
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] ==0) {
    header("Location: ../home.php");
    exit();
}

require_once __DIR__ . '/../../navbar.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Onderhoud taak toevoegen</title>

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
    min-height: 80px;
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
    <form class="form-card" action="onderhoud_toevoegen_functie.php" method="post">
        <h2>Onderhoud taak toevoegen</h2>

        <div class="form-group">
            <label for="lodgeid">Lodge</label>
            <select id="lodgeid" name="lodgeid" required>
                <?php
                $stmt = $db->prepare("SELECT lodgeid, huisnummer FROM lodge");
                $stmt->execute();
                $lodges = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($lodges as $lodge) {
                    echo "<option value='{$lodge['lodgeid']}'>{$lodge['huisnummer']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="monteurid">Monteur</label>
            <select id="monteurid" name="monteurid" required>
                <?php
                $stmt = $db->prepare("SELECT gebruikerid, naam FROM gebruiker WHERE rol >= 1");
                $stmt->execute();
                $monteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($monteurs as $monteur) {
                    echo "<option value='{$monteur['gebruikerid']}'>{$monteur['naam']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="omschrijving">Omschrijving</label>
            <textarea id="omschrijving" name="omschrijving" required></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="gesloten">Gesloten</option>
            </select>
        </div>

        <div class="form-group">
            <label for="prioriteit">Prioriteit</label>
            <select id="prioriteit" name="prioriteit">
                <option value="hoog">Hoog</option>
                <option value="middel">Middel</option>
                <option value="laag">Laag</option>
            </select>
        </div>

        <div class="button-group">
            <input type="submit" value="Taak toevoegen">
        </div>
    </form>
</div>

</body>
</html>