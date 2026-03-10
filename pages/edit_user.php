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

<div class="forms-container profile-forms-container">
    <!-- User edit card -->
    <div class="forms-wrapper user-info-wrapper">
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

    <!-- Profile Picture Card -->
    <div class="forms-wrapper profile-picture-wrapper">
        <div class="form-card active" id="profilePictureCard">
            <h1>Profielfoto</h1>
            
            <form id="profilePictureForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($_SESSION['gebruikerId']) ?>">
                
                <!-- Current Profile Picture Preview -->
                <div class="form-group" style="text-align: center; margin-bottom: 25px;">
                    <label>Huidige profielfoto</label>
                    <div id="profilePicturePreview" style="margin-top: 15px; min-height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f5f5f5; border: 2px dashed #ddd; border-radius: 8px; padding: 20px;">
                        <?php 
                        if (!empty($user['profielfoto'])): 
                            // Convert longblob to base64 for display
                            $imageData = base64_encode($user['profielfoto']);
                            echo '<img src="data:image/jpeg;base64,' . $imageData . '" alt="Profielfoto" style="max-width: 100%; max-height: 300px; border-radius: 8px;">';
                        else: 
                        ?>
                            <span style="color: #999; font-style: italic;">Geen profielfoto geüpload</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- File Input (Hidden by default) -->
                <div class="form-group" id="fileInputGroup" style="display: none;">
                    <label for="profielfoto">Selecteer een foto *</label>
                    <input type="file" id="profielfoto" name="profielfoto" accept="image/*" required>
                    <small class="help-text">Toegestane formaten: JPG, PNG, GIF (Max 5MB)</small>
                    <div id="fileError" class="error-message" style="display: none; margin-top: 10px;"></div>
                </div>

                <!-- New Picture Preview (shown after selecting a file) -->
                <div class="form-group" id="newPicturePreview" style="text-align: center; display: none; margin-bottom: 25px;">
                    <label>Voorbeeld van nieuwe foto</label>
                    <div id="newImagePreview" style="margin-top: 15px; min-height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f5f5f5; border: 2px dashed #ddd; border-radius: 8px; padding: 20px;">
                        <span style="color: #999; font-style: italic;">Geen foto geselecteerd</span>
                    </div>
                </div>

                <!-- Button Group -->
                <div class="button-group" id="buttonGroup">
                    <button type="button" class="registratieButton btn-change" id="changeBtn" onclick="startProfilePictureChange()">Wijzigen</button>
                    <?php if (!empty($user['profielfoto'])): ?>
                        <button type="button" class="registratieButton btn-delete" id="deleteBtn" onclick="deleteProfilePicture()">Verwijderen</button>
                    <?php endif; ?>
                </div>

                <!-- Save and Cancel Buttons (shown after change is initiated) -->
                <div class="button-group" id="saveButtonGroup" style="display: none;">
                    <button type="submit" class="registratieButton btn-save" id="saveBtn">Opslaan</button>
                    <button type="button" class="registratieButton btn-cancel" id="cancelBtn" onclick="cancelProfilePictureChange()">Annuleren</button>
                </div>
            </form>
        </div>
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

// Form submission validation for user edit form
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

// Profile Picture Functions
function startProfilePictureChange() {
    // Show file input and new picture preview
    document.getElementById('fileInputGroup').style.display = 'block';
    document.getElementById('newPicturePreview').style.display = 'block';
    
    // Hide change and delete buttons, show save and cancel
    document.getElementById('buttonGroup').style.display = 'none';
    document.getElementById('saveButtonGroup').style.display = 'flex';
    
    // Focus on file input
    document.getElementById('profielfoto').focus();
}

function cancelProfilePictureChange() {
    // Hide file input and new picture preview
    document.getElementById('fileInputGroup').style.display = 'none';
    document.getElementById('newPicturePreview').style.display = 'none';
    document.getElementById('newImagePreview').innerHTML = '<span style="color: #999; font-style: italic;">Geen foto geselecteerd</span>';
    
    // Show change and delete buttons, hide save and cancel
    document.getElementById('buttonGroup').style.display = 'flex';
    document.getElementById('saveButtonGroup').style.display = 'none';
    
    // Clear file input
    document.getElementById('profielfoto').value = '';
    document.getElementById('fileError').style.display = 'none';
}

function deleteProfilePicture() {
    if (confirm('Weet u zeker dat u uw profielfoto wil verwijderen?')) {
        const form = document.getElementById('profilePictureForm');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_profile_picture';
        input.value = '1';
        form.appendChild(input);
        form.action = 'update_profile_picture.php';
        form.submit();
    }
}

// File input change handler
document.getElementById('profielfoto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileError = document.getElementById('fileError');
    const newImagePreview = document.getElementById('newImagePreview');
    
    // Validation
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (file) {
        if (file.size > maxSize) {
            fileError.textContent = 'Bestand is te groot. Maximum 5MB is toegestaan.';
            fileError.style.display = 'block';
            newImagePreview.innerHTML = '<span style="color: #999; font-style: italic;">Geen foto geselecteerd</span>';
            this.value = '';
            return;
        }
        
        if (!allowedTypes.includes(file.type)) {
            fileError.textContent = 'Ongeldig bestandstype. Gebruik JPG, PNG of GIF.';
            fileError.style.display = 'block';
            newImagePreview.innerHTML = '<span style="color: #999; font-style: italic;">Geen foto geselecteerd</span>';
            this.value = '';
            return;
        }
        
        fileError.style.display = 'none';
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(event) {
            newImagePreview.innerHTML = '<img src="' + event.target.result + '" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px;">';
        };
        reader.readAsDataURL(file);
    }
});

// Form submission for profile picture
document.getElementById('profilePictureForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('profielfoto');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        alert('Selecteer alstublieft een foto');
        return;
    }
    
    const form = this;
    form.action = 'update_profile_picture.php';
    form.submit();
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

/* Profile Forms Container - Side by Side Layout */
.profile-forms-container {
    display: flex !important;
    flex-direction: row !important;
    align-items: flex-start !important;
    justify-content: center !important;
    gap: 60px !important;
    padding: 40px 20px !important;
    flex-wrap: wrap;
}

.user-info-wrapper {
    flex: 0 1 450px;
}

.profile-picture-wrapper {
    flex: 0 1 450px;
    margin-top: 0 !important;
}

/* Button Group Styling */
#buttonGroup, #saveButtonGroup {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 30px;
}

/* Enhanced Button Styles */
.registratieButton {
    position: relative;
    padding: 12px 30px;
    font-size: 1em;
    font-weight: 600;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.registratieButton::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.registratieButton:hover::before {
    width: 300px;
    height: 300px;
}

/* Primary Save Button */
.btn-save {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    color: white;
}

.btn-save:hover {
    background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
    box-shadow: 0 6px 12px rgba(40, 167, 69, 0.3);
    transform: translateY(-2px);
}

.btn-save:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

/* Change Button */
.btn-change {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-change:hover {
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
    box-shadow: 0 6px 12px rgba(0, 123, 255, 0.3);
    transform: translateY(-2px);
}

.btn-change:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

/* Delete Button */
.btn-delete {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    box-shadow: 0 6px 12px rgba(220, 53, 69, 0.3);
    transform: translateY(-2px);
}

.btn-delete:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

/* Cancel Button */
.btn-cancel {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.btn-cancel:hover {
    background: linear-gradient(135deg, #5a6268 0%, #545b62 100%);
    box-shadow: 0 6px 12px rgba(108, 117, 125, 0.3);
    transform: translateY(-2px);
}

.btn-cancel:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
}

/* Disabled state */
.registratieButton:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.registratieButton:disabled:hover {
    transform: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .profile-forms-container {
        gap: 40px;
    }
    
    .user-info-wrapper,
    .profile-picture-wrapper {
        flex: 0 1 400px;
    }
}

@media (max-width: 1024px) {
    .profile-forms-container {
        flex-direction: column;
        gap: 30px;
    }
    
    .user-info-wrapper,
    .profile-picture-wrapper {
        flex: 1;
        max-width: 100%;
    }
}

@media (max-width: 768px) {
    .registratieButton {
        padding: 10px 20px;
        font-size: 0.9em;
        flex: 1;
        min-width: 150px;
    }
    
    #buttonGroup, #saveButtonGroup {
        gap: 10px;
    }
    
    .profile-forms-container {
        padding: 20px 15px;
    }
}

@media (max-width: 480px) {
    .registratieButton {
        padding: 10px 15px;
        font-size: 0.85em;
        width: 100%;
    }
    
    #buttonGroup, #saveButtonGroup {
        flex-direction: column;
    }
}
</style>