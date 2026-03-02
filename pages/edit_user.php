<?php
include("config.php");
session_start();
// include navigation bar for consistency
$sql="select * from gebruiker where gebruikerid = ?";
$stmt=$db->prepare($sql);
$stmt->execute([$_SESSION['gebruikerId']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// debug: inspect loaded user data (disabled for production)
// echo '<pre>'; var_dump($user); echo '</pre>';

// include navbar after fetching user; pages/navbar.php now uses a separate
// variable to avoid clobbering $user
require_once __DIR__ . '/../navbar.php';
$two_factor = $_SESSION['two_factor'];
?>
<link rel="stylesheet" href="../style/login.css">

<div class="forms-container">
    <div class="forms-wrapper">
        <!-- User edit card -->
        <form class="form-card active" action="update_user.php" method="post">
            <h1>Gegevens bijwerken</h1>
            <input type="hidden" name="id" value="<?= htmlspecialchars($_SESSION['gebruikerId']) ?>">


            <div class="form-group">
                <label for="mail">Email</label>
                <input type="email" id="mail" name="mail" value="<?= htmlspecialchars(
                    $user['email'] ?? ''
                ) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Wachtwoord</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password"  required placeholder="Laat leeg om niet te wijzigen">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">Tonen</button>
                </div>
            </div>

            <div class="error-message" id="passwordError">Wachtwoord voldoet niet aan de vereisten.</div>
                        <div class="password-requirements" id="passwordReqs">
                            <div class="requirement unmet" id="req-length">
                                <span class="requirement-icon">✗</span>
                                <span>Minimaal 8 karakters</span>
                            </div>
                            <div class="requirement unmet" id="req-uppercase">
                                <span class="requirement-icon">✗</span>
                                <span>Minimaal 1 hoofdletter</span>
                            </div>
                            <div class="requirement unmet" id="req-number">
                                <span class="requirement-icon">✗</span>
                                <span>Minimaal 1 getal</span>
                            </div>
                            <div class="requirement unmet" id="req-special">
                                <span class="requirement-icon">✗</span>
                                <span>Minimaal 1 speciaal teken (!@#$%^&*)</span>
                            </div>
                        </div>
            

            <div class="form-group">
                <label for="adres">Adres</label>
                <input type="text" id="adres" required name="adres" value="<?= htmlspecialchars(
                    $user['adres'] ?? ''
                ) ?>">
            </div>

            <div class="form-group">
                <label for="postcode">Postcode</label>
                <input type="text" id="postcode" required name="postcode" value="<?= htmlspecialchars(
                    $user['postcode'] ?? ''
                ) ?>">
            </div>

            <div class="form-group">
                <label for="plaats">Plaats</label>
                <input type="text" id="plaats" required name="plaats" value="<?= htmlspecialchars(
                    $user['plaats'] ?? ''
                ) ?>">
            </div>

            <div class="form-group">
                <label for="naam">Naam</label>
                <input type="text" id="naam" required name="naam" value="<?= htmlspecialchars(
                    $user['naam'] ?? ''
                ) ?>">
            </div>

            <div class="form-group">
                <label for="telefoonnummer">Telefoon</label>
                <input type="tel" id="telefoonnummer"  required name="telefoonnummer" value="<?= htmlspecialchars(
                    $user['telefoonnummer'] ?? ''
                ) ?>">
            </div>

            <div class="form-group">
                <label for="two_factor">Twee-factor authenticatie</label>
                <select id="two_factor" name="two_factor" class="form_select">
                    <option value="0" <?= ($two_factor === '0') ? 'selected' : '' ?>>Nee</option>
                    <option value="1" <?= ($two_factor === '1') ? 'selected' : '' ?>>Ja</option>
                </select>
            </div>
            
            <div class="button-group">
                <button type="submit" class="registratieButton">Opslaan</button>
            </div>
        </form>
        
    
    </div>
</div>

<script>
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const button = event.target.closest('.password-toggle');
    if (field.type === 'password') {
        field.type = 'text';
        button.textContent = 'Verbergen';
    } else {
        field.type = 'password';
        button.textContent = 'Tonen';
    }
}
</script>

