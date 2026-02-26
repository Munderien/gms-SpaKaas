<?php
include("../inlog/config.php");
?>


    <div style="padding: 20px;">
        <h2 style="">Onderhoud taak toevoegen</h2>
        <form action="onderhoud_toevoegen_functie.php" method="post">
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
            <select id="monteurid" name="monteurid">
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
            <textarea id="omschrijving" name="omschrijving"></textarea>
            <br><br>

            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="gesloten">Gesloten</option>
            </select>
            <br><br>

            <label for="prioriteit">Prioriteit:</label>
            <select id="prioriteit" name="prioriteit">
                <option value="high">Hoog</option>
                <option value="medium">Middel</option>
                <option value="low">Laag</option>
            </select>
            <br><br>

            <input type="submit" value="Taak toevoegen">
        </form>
    </div>
