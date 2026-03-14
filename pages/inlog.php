<?php
session_start();
if (isset($_SESSION['gebruikermail'])) {
    echo "<script>window.location.href='home.php'</script>";
    exit;
}

$errorMsg = '';
if (isset($_SESSION['error'])) {
    $errorMsg = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Spa Resort</title>
    <link rel='stylesheet' href="../Style/login.css">
</head>
<body>
    <div class="main-container">
        <div class="spa-marketing">
            <h2>Welkom bij Luxe Spa Resort</h2>
            <p>Ervaar ultieme ontspanning en vernieuwing in onze premium spa-faciliteit.</p>
            
            <div class="spa-features">
                <div class="feature">
                    <h3>Spa Services</h3>
                    <p>Wereldklasse massages en behandelingen</p>
                </div>
                <div class="feature">
                    <h3>Welzijn</h3>
                    <p>Zwembaden, sauna's en ontspanningsruimtes</p>
                </div>
                <div class="feature">
                    <h3>Luxe</h3>
                    <p>Premium voorzieningen en accommodaties</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Forms -->
        <div class="forms-container">
            <div class="forms-wrapper">
                <!-- Error/Success Messages -->
                <div id="success" class="error-message" style="display: none;"></div>
                
                <!-- Login Form -->
                <form action="inlogFunctie.php" method="post" class="form-card active" id="loginForm" novalidate>
                    <div id="error" class="error-message" style="display: <?php echo ($errorMsg !== '' ? 'block' : 'none'); ?>;"><?php echo htmlspecialchars($errorMsg); ?></div>
                    <h1>Inloggen</h1>

                    <div class="error-message" >voer alles in alstublieft.</div>
                    <div class="form-group">
                        <label for="inlogMail">Email</label>
                        <input type="email" id="inlogMail" class="inlogMail" name="inlogMail" placeholder="uw@email.com">
                        <div class="error-message" id="loginEmailError">Voer alstublieft een geldig email adres in.</div>
                    </div>

                    <div class="form-group">
                        <label for="inlogPassword">Wachtwoord</label>
                        <div class="password-wrapper">
                            <input type="password" id="inlogPassword" class="inlogPassword" name="inlogPassword" placeholder="Uw wachtwoord">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('inlogPassword')">Tonen</button>
                        </div>
                        <div class="error-message" id="loginPasswordError">Wachtwoord mag niet leeg zijn.</div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="inlogButton">Login</button>
                    </div>

                    <div class="form-footer">
                        Geen account? <button type="button" class="toggle-form" onclick="switchForm('registerForm')">Registreer hier</button>
                    </div>
                </form>

                <!-- Registration Form -->
                <form class="form-card" id="registerForm">
                    <h1>Registreren</h1>

                    <div class="form-group">
                        <label for="mail">Email</label>
                        <input type="email" id="mail" class="registratieMail" name="mail" placeholder="uw@email.com" required>
                        <div class="error-message" id="emailError">Voer alstublieft een geldig email adres in.</div>
                    </div>

                    <div class="form-group">
                        <label for="password">Wachtwoord</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" class="registratieWachtwoord" name="password" placeholder="Minimaal 8 karakters" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">Tonen</button>
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
                    </div>

                    <div class="form-group">
                        <label for="straatnaam">Straatnaam</label>
                        <input type="text" id="straatnaam" class="registratieStraatnaam" name="straatnaam" placeholder="Voornaam van straat" required>
                        <div class="error-message" id="straatnaamError">Straatnaam is verplicht.</div>
                    </div>

                    <div class="form-group">
                        <label for="huisnummer">Huisnummer</label>
                        <input type="text" id="huisnummer" class="registratieHuisnummer" name="huisnummer" placeholder="Bijv. 42 of 42A" required>
                        <div class="error-message" id="huisnummerError">Huisnummer is verplicht.</div>
                    </div>

                    <div class="form-group">
                        <label for="postcode">Postcode</label>
                        <input type="text" id="postcode" class="registratiePostcode" name="postcode" placeholder="Bijv. 1234AB" required>
                        <div class="error-message" id="postcodeError">Postcode is verplicht.</div>
                    </div>

                    <div class="form-group">
                        <label for="plaats">Plaats</label>
                        <input type="text" id="plaats" class="registratiePlaats" name="plaats" placeholder="Uw plaats" required>
                        <div class="error-message" id="plaatsError">Plaats is verplicht.</div>
                    </div>

                    <div class="form-group">
                        <label for="name">Naam</label>
                        <input type="text" id="name" class="registratieENaam" name="name" placeholder="Uw volledige naam" required>
                        <div class="error-message" id="nameError">Naam is verplicht.</div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Telefoon</label>
                        <input type="tel" id="phone" class="registratieTelNummer" name="phone" placeholder="Bijv. 0612345678" required>
                        <div class="error-message" id="phoneError">Telefoon nummer is verplicht.</div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="registratieButton">Registreer</button>
                    </div>

                    <div class="form-footer">
                        Al een account? <button type="button" class="toggle-form" onclick="switchForm('loginForm')">Log hier in</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
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

        // Switch between login and registration forms
        function switchForm(formId) {
            document.querySelectorAll('.form-card').forEach(card => {
                card.classList.remove('active');
            });
            document.getElementById(formId).classList.add('active');
            
            // Clear error messages
            document.getElementById('error').style.display = 'none';
            document.getElementById('success').style.display = 'none';
            
            // Hide password requirements when switching away from register
            if (formId !== 'registerForm') {
                document.getElementById('passwordReqs').classList.remove('show');
            }
        }

        // Check password requirements in real-time
        document.getElementById('password')?.addEventListener('input', function() {
            const password = this.value;
            
            // Check length (8+)
            const lengthOk = password.length >= 8;
            updateRequirement('req-length', lengthOk);
            
            // Check uppercase
            const uppercaseOk = /[A-Z]/.test(password);
            updateRequirement('req-uppercase', uppercaseOk);
            
            // Check number
            const numberOk = /[0-9]/.test(password);
            updateRequirement('req-number', numberOk);
            
            // Check special character
            const specialOk = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
            updateRequirement('req-special', specialOk);
        });

        function updateRequirement(elementId, isMet) {
            const element = document.getElementById(elementId);
            if (isMet) {
                element.classList.remove('unmet');
                element.classList.add('met');
            } else {
                element.classList.remove('met');
                element.classList.add('unmet');
            }
        }

        // Validation for login form
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let isValid = true;
            const emailEl = document.getElementById('inlogMail');
            const passwordEl = document.getElementById('inlogPassword');
            const email = emailEl.value.trim();
            const password = passwordEl.value.trim();
            const errorBox = document.getElementById('error');

            // Reset errors
            errorBox.style.display = 'none';
            errorBox.textContent = '';
            document.querySelectorAll('#loginForm .error-message').forEach(el => el.classList.remove('show'));
            document.querySelectorAll('#loginForm input').forEach(el => el.classList.remove('error'));

            // Email validation
            if (email === '') {
                emailEl.classList.add('error');
                document.getElementById('loginEmailError').textContent = 'Email is verplicht.';
                document.getElementById('loginEmailError').classList.add('show');
                errorBox.textContent = 'Email is verplicht.';
                errorBox.style.display = 'block';
                isValid = false;
            } else if (!isValidEmail(email)) {
                emailEl.classList.add('error');
                document.getElementById('loginEmailError').textContent = 'Voer alstublieft een geldig email adres in.';
                document.getElementById('loginEmailError').classList.add('show');

                isValid = false;
            }

            // Password validation
            if (password === '') {
                passwordEl.classList.add('error');
                document.getElementById('loginPasswordError').classList.add('show');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                return;
            }

            // If valid, allow form submit to inlogFunctie.php for server-side authentication.
        });

        // Validation for registration form
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;
            const passwordReqs = document.getElementById('passwordReqs');

            // Reset errors
            document.querySelectorAll('#registerForm .error-message').forEach(el => {
                el.classList.remove('show');
            });
            document.querySelectorAll('#registerForm input').forEach(el => {
                el.classList.remove('error');
            });

            // Email validation
            const email = document.getElementById('mail').value.trim();
            if (email === '') {
                document.getElementById('mail').classList.add('error');
                isValid = false;
            } else if (!isValidEmail(email)) {
                document.getElementById('mail').classList.add('error');
                document.getElementById('emailError').textContent = 'Voer alstublieft een geldig email adres in.';
                document.getElementById('emailError').classList.add('show');
                isValid = false;
            }

            // Password validation
            const password = document.getElementById('password').value.trim();
            if (password === '') {
                document.getElementById('password').classList.add('error');
                document.getElementById('passwordError').textContent = 'Wachtwoord is verplicht.';
                document.getElementById('passwordError').classList.add('show');
                passwordReqs.classList.add('show');
                isValid = false;
            } else if (!isValidPassword(password)) {
                document.getElementById('password').classList.add('error');
                document.getElementById('passwordError').textContent = 'Wachtwoord voldoet niet aan de vereisten.';
                document.getElementById('passwordError').classList.add('show');
                passwordReqs.classList.add('show');
                isValid = false;
            } else {
                passwordReqs.classList.remove('show');
            }

            // Street name validation
            const straatnaam = document.getElementById('straatnaam').value.trim();
            if (straatnaam === '') {
                document.getElementById('straatnaam').classList.add('error');
                document.getElementById('straatnaamError').classList.add('show');
                isValid = false;
            }

            // House number validation
            const huisnummer = document.getElementById('huisnummer').value.trim();
            if (huisnummer === '') {
                document.getElementById('huisnummer').classList.add('error');
                document.getElementById('huisnummerError').classList.add('show');
                isValid = false;
            }

            // Postcode validation
            const postcode = document.getElementById('postcode').value.trim();
            if (postcode === '') {
                document.getElementById('postcode').classList.add('error');
                document.getElementById('postcodeError').classList.add('show');
                isValid = false;
            }

            // City validation
            const plaats = document.getElementById('plaats').value.trim();
            if (plaats === '') {
                document.getElementById('plaats').classList.add('error');
                document.getElementById('plaatsError').classList.add('show');
                isValid = false;
            }

            // Name validation
            const name = document.getElementById('name').value.trim();
            if (name === '') {
                document.getElementById('name').classList.add('error');
                document.getElementById('nameError').classList.add('show');
                isValid = false;
            }

            // Phone validation
            const phone = document.getElementById('phone').value.trim();
            if (phone === '') {
                document.getElementById('phone').classList.add('error');
                document.getElementById('phoneError').classList.add('show');
                isValid = false;
            }

            if (isValid) {
                // Submit via AJAX
                submitRegistrationForm();
            }
        });

        function submitRegistrationForm() {
            const formData = new FormData(document.getElementById('registerForm'));
            const button = document.querySelector('#registerForm button[type="submit"]');
            button.disabled = true;
            button.textContent = 'Bezig met registreren...';

            fetch('registratieFunctie.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                console.log('Response:', text);
                try {
                    const data = JSON.parse(text);
                    const errorDiv = document.getElementById('error');
                    const successDiv = document.getElementById('success');
                    
                    if (data.success) {
                        successDiv.textContent = data.message;
                        successDiv.style.display = 'block';
                        setTimeout(() => {
                            window.location.href = 'home.php';
                        }, 2000);
                    } else {
                        errorDiv.textContent = data.message;
                        errorDiv.style.display = 'block';
                        button.disabled = false;
                        button.textContent = 'Registreer';
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    document.getElementById('error').textContent = 'Er is een fout opgetreden. Probeer het later opnieuw.';
                    document.getElementById('error').style.display = 'block';
                    button.disabled = false;
                    button.textContent = 'Registreer';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                document.getElementById('error').textContent = 'Er is een fout opgetreden. Probeer het later opnieuw.';
                document.getElementById('error').style.display = 'block';
                button.disabled = false;
                button.textContent = 'Registreer';
            });
        }

        // Email validation helper
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Password validation helper - check all requirements
        function isValidPassword(password) {
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
            return hasLength && hasUppercase && hasNumber && hasSpecial;
        }

        // Clear error on input
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorMsg = this.closest('.form-group').querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>