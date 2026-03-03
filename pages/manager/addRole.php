<?php
require '../config.php';
session_start();


if (!isset($_SESSION['gebruikerId'])) {
    header('Location: /dms-spakaas/gms-SpaKaas/pages/inlog.php');
    exit;
}
$stmt = $db->prepare("SELECT rol FROM gebruiker WHERE gebruikerid = ?");
$stmt->execute([$_SESSION['gebruikerId']]);
$_SESSION['rol'] = (int) $stmt->fetchColumn();

if ($_SESSION['rol'] != 3) {
    die('Geen toegang');
}

$melding = '';


if (isset($_POST['actie']) && $_POST['actie'] == 'rol') {
    $stmt = $db->prepare("UPDATE gebruiker SET rol = ? WHERE gebruikerid = ?");
    $stmt->execute([$_POST['rol'], $_POST['gebruikerid']]);
    $melding = 'Rol bijgewerkt.';
}


if (isset($_POST['actie']) && $_POST['actie'] == 'verwijderen') {
    if ($_POST['verwijder_id'] == $_SESSION['gebruikerId']) {
        $melding = 'Je kunt je eigen account niet verwijderen.';
    } else {
        $stmt = $db->prepare("DELETE FROM gebruiker WHERE gebruikerid = ?");
        $stmt->execute([$_POST['verwijder_id']]);
        $melding = 'Account verwijderd.';
    }
}


if (isset($_POST['actie']) && $_POST['actie'] == 'aanmaken') {
    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);
    $ww = trim($_POST['wachtwoord']);

    if ($naam == '' || $email == '' || $ww == '') {
        $melding = 'Naam, e-mail en wachtwoord zijn verplicht.';
    } else {
        $hash = md5($ww);
        $stmt = $db->prepare("INSERT INTO gebruiker
            (email, wachtwoord, rol, isactief, is2faingeschakeld, adres, naam, plaats, telefoonnummer)
            VALUES (?, ?, ?, 1, 0, ?, ?, ?, ?)");
        $stmt->execute([$email, $hash, $_POST['nieuw_rol'], $_POST['adres'], $naam, $_POST['plaats'], $_POST['telefoonnummer']]);
        $melding = 'Account aangemaakt!';
    }
}

$rolnamen = [0 => 'Klant', 1 => 'Baliemedewerker', 2 => 'Onderhoudsmonteur', 3 => 'Manager'];

$gebruikers = $db->query("SELECT gebruikerid, email, naam, rol, isactief FROM gebruiker ORDER BY gebruikerid")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruikersbeheer – SpaKaas</title>
    <link rel="stylesheet" href="../../Style/manager.css">
    <style>
        select.compact {
            padding: 5px 8px;
            font-size: 0.85rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .sectie-titel {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 32px 0 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #ecf0f1;
        }
    </style>
</head>

<body>
    <?php include '../../navbar.php'; ?>

    <div class="manager-container">
        <h1>Gebruikersbeheer</h1>

        <?php if ($melding != ''): ?>
            <p style="color:green; font-weight:bold;"><?php echo $melding; ?></p>
        <?php endif; ?>


        <p class="sectie-titel">Nieuw account aanmaken</p>
        <div class="manager-form">
            <form method="post">
                <input type="hidden" name="actie" value="aanmaken">

                <div class="form-group">
                    <label for="naam">Naam <span style="color:#e74c3c;">*</span></label>
                    <input type="text" id="naam" name="naam" maxlength="30"
                        value="<?php echo htmlspecialchars($_POST['naam'] ?? ''); ?>" placeholder="Volledige naam"
                        required>
                </div>

                <div class="form-group">
                    <label for="email">E-mail <span style="color:#e74c3c;">*</span>
                        <small style="color:#999;">(max. 20 tekens)</small>
                    </label>
                    <input type="text" id="email" name="email" maxlength="20"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="email@voorbeeld.nl"
                        required>
                </div>

                <div class="form-group">
                    <label for="wachtwoord">Wachtwoord <span style="color:#e74c3c;">*</span></label>
                    <input type="password" id="wachtwoord" name="wachtwoord" required
                        placeholder="Tijdelijk wachtwoord">
                </div>

                <div class="form-group">
                    <label for="nieuw_rol">Rol</label>
                    <select id="nieuw_rol" name="nieuw_rol">
                        <option value="0">Klant</option>
                        <option value="1">Baliemedewerker</option>
                        <option value="2">Onderhoudsmonteur</option>
                        <option value="3">Manager</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="adres">Adres</label>
                    <input type="text" id="adres" name="adres" maxlength="50"
                        value="<?php echo htmlspecialchars($_POST['adres'] ?? ''); ?>"
                        placeholder="Straat + huisnummer">
                </div>

                <div class="form-group">
                    <label for="plaats">Plaats</label>
                    <input type="text" id="plaats" name="plaats" maxlength="30"
                        value="<?php echo htmlspecialchars($_POST['plaats'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="telefoonnummer">Telefoonnummer</label>
                    <input type="text" id="telefoonnummer" name="telefoonnummer" maxlength="20"
                        value="<?php echo htmlspecialchars($_POST['telefoonnummer'] ?? ''); ?>" placeholder="06...">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Account aanmaken</button>
                </div>
            </form>
        </div>


        <p class="sectie-titel">Alle gebruikers</p>
        <table class="manager-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naam</th>
                    <th>E-mail</th>
                    <th>Huidige rol</th>
                    <th>Rol wijzigen</th>
                    <th>Verwijderen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gebruikers as $g): ?>
                    <tr>
                        <td><?php echo $g['gebruikerid']; ?></td>
                        <td><?php echo htmlspecialchars($g['naam']); ?></td>
                        <td><?php echo htmlspecialchars($g['email']); ?></td>
                        <td><?php echo $rolnamen[$g['rol']] ?? 'Onbekend'; ?></td>


                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="actie" value="rol">
                                <input type="hidden" name="gebruikerid" value="<?php echo $g['gebruikerid']; ?>">
                                <select name="rol" class="compact">
                                    <option value="0" <?php if ($g['rol'] == 0)
                                        echo 'selected'; ?>>Klant</option>
                                    <option value="1" <?php if ($g['rol'] == 1)
                                        echo 'selected'; ?>>Baliemedewerker</option>
                                    <option value="2" <?php if ($g['rol'] == 2)
                                        echo 'selected'; ?>>Onderhoudsmonteur</option>
                                    <option value="3" <?php if ($g['rol'] == 3)
                                        echo 'selected'; ?>>Manager</option>
                                </select>
                                <button type="submit" class="btn btn-warning btn-sm">Opslaan</button>
                            </form>
                        </td>


                        <td>
                            <?php if ($g['gebruikerid'] != $_SESSION['gebruikerId']): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="actie" value="verwijderen">
                                    <input type="hidden" name="verwijder_id" value="<?php echo $g['gebruikerid']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Weet je het zeker?');">
                                        Verwijderen
                                    </button>
                                </form>
                            <?php else: ?>
                                <small>Eigen account</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>