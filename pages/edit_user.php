<?php
include("config.php");
session_start();
// include navigation bar for consistency
require_once __DIR__ . '/navbarKlant.php';
$sql="select * from gebruiker where gebruikerId = ?";
$stmt=$db->prepare($sql);
$stmt->execute([$_SESSION['gebruikerId']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get validation errors from session
$errors = $_SESSION['validation_errors'] ?? [];
unset($_SESSION['validation_errors']);

// Get 2FA status from database (is2faingeschakeld)
$two_factor = $user['is2faingeschakeld'] ?? '0';
?>
<link rel="stylesheet" href="../style/login.css">

<div class="forms-container">
    <div class="forms-wrapper">
        <!-- User edit card -->
        <form class="form-card active" action="update_user.php" method="post" id="userEditForm">
            <h1>Gegevens bijwerken</h1>
            <input type="hidden" name="id" value="<?= htmlspecialchars($_SESSION['gebruikerId']) ?>">

            <!-- Display validation errors -->
            <?php if (!empty($errors)): ?>
                <div class="error-message" style="background-color: #fee; border: 1px solid #fcc; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <strong>Fouten gevonden:</strong>
                    <ul style="margin: 10px 0 0 20px; padding: 0;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="mail">Email *</label>
                <input type="email" id="mail" name="mail" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                <small class="help-text">Moet een geldige email zijn</small>
            </div>

            <div class="form-group">
                <label for="password">Wachtwoord</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Laat leeg om niet te wijzigen" 
                           oninput="validatePassword(this.value)">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">Tonen</button>
                </div>
                <small class="help-text">Laat leeg als u het wachtwoord niet wil wijzigen</small>
            </div>

            <div class="error-message" id="passwordError" style="display: none;">Wachtwoord voldoet niet aan de vereisten.</div>
            
            <div class="password-requirements" id="passwordReqs">
                <div class="requirement unmet" id="req-length">
                    <span class="requirement-icon">✗</span>
                    <span>Minimaal 8 karakters</span>
                </div>
                <div class="requirement unmet" id="req-uppercase">
                    <span class="requirement-icon">✗</span>
                    <span>Minimaal 1 hoofdletter</span>
                </div>
                <div class="requirement unmet" id="req-lowercase">
                    <span class="requirement-icon">✗</span>
                    <span>Minimaal 1 kleine letter</span>
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
                <label for="naam">Naam *</label>
                <input type="text" id="naam" name="naam" value="<?= htmlspecialchars($user['naam'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="adres">Adres *</label>
                <input type="text" id="adres" name="adres" value="<?= htmlspecialchars($user['adres'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="postcode">Postcode *</label>
                <input type="text" id="postcode" name="postcode" placeholder="Bijvoorbeeld: 1234 AB" 
                       value="<?= htmlspecialchars($user['postcode'] ?? '') ?>" required>
                <small class="help-text">Formaat: NNNN AA (bijv. 1234 AB)</small>
            </div>

            <div class="form-group">
                <label for="plaats">Plaats *</label>
                <input type="text" id="plaats" name="plaats" value="<?= htmlspecialchars($user['plaats'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="telefoonnummer">Telefoon *</label>
                <input type="tel" id="telefoonnummer" name="telefoonnummer" 
                       value="<?= htmlspecialchars($user['telefoonnummer'] ?? '') ?>" required>
                <small class="help-text">Minimaal 9 cijfers</small>
            </div>

            <div class="form-group">
                <label for="two_factor">Twee-factor authenticatie</label>
                <select id="two_factor" name="two_factor" class="form_select">
                    <option value="0" <?= ($two_factor === '0' || $two_factor === 0) ? 'selected' : '' ?>>Nee</option>
                    <option value="1" <?= ($two_factor === '1' || $two_factor === 1) ? 'selected' : '' ?>>Ja</option>
                </select>
            </div>
            
            <div class="button-group">
                <button type="submit" class="registratieButton" id="submitBtn">Opslaan</button>
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

function validatePassword(password) {
    const requirements = {
        'req-length': password.length >= 8,
        'req-uppercase': /[A-Z]/.test(password),
        'req-lowercase': /[a-z]/.test(password),
        'req-number': /[0-9]/.test(password),
        'req-special': /[!@#$%^&*()_\-+=\[\]{};:'"",.<>?\/\\|`~]/.test(password)
    };

    let allMet = true;
    for (const [reqId, isMet] of Object.entries(requirements)) {
        const element = document.getElementById(reqId);
        if (isMet) {
            element.classList.remove('unmet');
            element.classList.add('met');
            element.querySelector('.requirement-icon').textContent = '✓';
        } else {
            element.classList.add('unmet');
            element.classList.remove('met');
            element.querySelector('.requirement-icon').textContent = '✗';
            if (password.length > 0) allMet = false;
        }
    }

    const errorDiv = document.getElementById('passwordError');
    if (password.length > 0 && !allMet) {
        errorDiv.style.display = 'block';
    } else {
        errorDiv.style.display = 'none';
    }
}

// Form submission validation
document.getElementById('userEditForm').addEventListener('submit', function(e) {
    const mail = document.getElementById('mail').value.trim();
    const password = document.getElementById('password').value;
    const adres = document.getElementById('adres').value.trim();
    const postcode = document.getElementById('postcode').value.trim();
    const plaats = document.getElementById('plaats').value.trim();
    const naam = document.getElementById('naam').value.trim();
    const telefoonnummer = document.getElementById('telefoonnummer').value.trim();

    // Check all required fields
    if (!mail || !adres || !postcode || !plaats || !naam || !telefoonnummer) {
        e.preventDefault();
        alert('Alle verplichte velden moeten ingevuld zijn (gemarkeerd met *)');
        return false;
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(mail)) {
        e.preventDefault();
        alert('Voer een geldig email adres in');
        return false;
    }

    // Validate postcode format
    const postcodeRegex = /^[0-9]{4}\s?[A-Z]{2}$/i;
    if (!postcodeRegex.test(postcode)) {
        e.preventDefault();
        alert('Postcode moet het formaat NNNN AA hebben (bijv. 1234 AB)');
        return false;
    }

    // Validate phone number (at least 9 digits)
    const phoneDigits = telefoonnummer.replace(/[^0-9]/g, '');
    if (phoneDigits.length < 9) {
        e.preventDefault();
        alert('Telefoon nummer moet minimaal 9 cijfers bevatten');
        return false;
    }

    // If password is provided, check strength
    if (password.length > 0) {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=\[\]{};:'"",.<>?\/\\|`~]).{8,}$/;
        if (!passwordRegex.test(password)) {
            e.preventDefault();
            alert('Wachtwoord voldoet niet aan alle vereisten');
            return false;
        }
    }
});
</script>

<style>
.help-text {
    display: block;
    font-size: 0.85em;
    color: #666;
    margin-top: 4px;
}

.password-requirements {
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin-top: 10px;
    margin-bottom: 15px;
}

.requirement {
    padding: 8px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95em;
}

.requirement.unmet {
    color: #666;
}

.requirement.met {
    color: #28a745;
    font-weight: 500;
}

.requirement-icon {
    min-width: 20px;
    text-align: center;
    font-weight: bold;
}
</style>