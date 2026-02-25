<?php
include("../inlog/config.php");
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM onderhoud WHERE onderhoudid = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $taak = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    echo "No task ID provided.";
    exit();
}
?>
<form action="onderhoud_bewerk_functie.php" method="post">

<input type="hidden" name="onderhoudid" value="<?php echo $taak['onderhoudid']; ?>">

            <label for="lodgeid">Lodge ID:</label>
            <select id="lodgeid" name="lodgeid">
              <?php
                $stmt = $db->prepare("SELECT lodgeid, huisnummer FROM lodge");
                $stmt->execute();
                $lodges = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($lodges as $lodge) {
                    echo "<option value='" . $lodge['lodgeid'] . "'>" . $lodge['huisnummer'] . "</option>";
                }
                ?>
            </select>
            <br><br>

            <label for="monteurid">Monteur ID:</label>
            <select id="monteurid" name="monteurid" value="<?php echo $taak['monteurid']; ?>">
              <?php
                $stmt = $db->prepare("SELECT gebruikerid, naam FROM gebruiker WHERE rol = 2 ");
                $stmt->execute();
                $monteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($monteurs as $monteur) {
                    echo "<option value='" . $monteur['gebruikerid'] . "'>" . $monteur['naam'] . "</option>";
                }
                ?>
            </select>
            <br><br>


            <label for="omschrijving">Omschrijving:</label>
            <textarea id="omschrijving" name="omschrijving"><?php echo htmlspecialchars($taak['omschrijving']); ?></textarea>
            <br><br>

            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="open" <?php echo ($taak['status'] == 'open') ? 'selected' : ''; ?>>Open</option>
                <option value="in_progress" <?php echo ($taak['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                <option value="gesloten" <?php echo ($taak['status'] == 'gesloten') ? 'selected' : ''; ?>>Gesloten</option>
            </select>
            <br><br>

            <label for="prioriteit">Prioriteit:</label>
            <select id="prioriteit" name="prioriteit">
                <option value="hoog">Hoog</option>
                <option value="medium">Middel</option>
                <option value="laag">Laag</option>
                <option value="dringend">Dringend</option>

            </select>
            <br><br>

            <input type="submit" value="Taak toevoegen">
        </form>