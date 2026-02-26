<?php
include("config.php");
session_start();
$two_factor = $_SESSION['two_factor'];
?>
<form action ="update_2fa.php" method="post">
                <h6>2 factor authenticatie</h6>

<select class="form_select" name="two_factor" style="width: 29%;">
    <option value="0" <?php echo ($two_factor === '0') ? 'selected' : ''; ?>>Nee</option>
    <option value="1" <?php echo ($two_factor === '1') ? 'selected' : ''; ?>>Ja</option>
</select>
        <button id="save" style="width: 100px;">Opslaan</button>
</form>
<button id="uitloggen" onClick="window.location.href='logout.php'">Uitloggen</button>

</table>