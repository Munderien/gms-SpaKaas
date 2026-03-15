<?php
require '../../config.php';
session_start();

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: ../../pages/inlog.php');
    exit;
}
$rolCheck = $db->prepare("SELECT rol FROM gebruiker WHERE gebruikerid = ?");
$rolCheck->execute([$_SESSION['gebruikerId']]);
$_SESSION['rol'] = (int) $rolCheck->fetchColumn();
if ($_SESSION['rol'] != 3) {
    header('Location: ../../pages/inlog.php');
    exit;
}

$melding = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $naam = trim($_POST['naam']);
    $beschrijving = trim($_POST['beschrijving']);
    $capaciteit = (int) $_POST['capaciteit'];
    $prijs = (float) $_POST['prijs'];

    if ($naam == '') {
        $melding = 'Naam is verplicht.';
    } else {
        $db->prepare("INSERT INTO lodgetype (naam, beschrijving, capaciteit, prijs) VALUES (?, ?, ?, ?)")
            ->execute([$naam, $beschrijving, $capaciteit, $prijs]);
        header('Location: type_overzicht.php?toegevoegd=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lodgetype Toevoegen – SpaKaas</title>
    <link rel="stylesheet" href="../../../Style/manager.css">
</head>

<body>
    <?php include '../../../navbar.php'; ?>
    <div class="manager-container">
        <h1>Nieuw Lodgetype Toevoegen</h1>

        <?php if ($melding != ''): ?>
            <p style="color:red; font-weight:bold;"><?php echo $melding; ?></p>
        <?php endif; ?>

        <div class="manager-form">
            <form method="post" novalidate>
                <div class="form-group">
                    <label for="naam">Naam <span style="color:#e74c3c;">*</span></label>
                    <input type="text" id="naam" name="naam" maxlength="30"
                        value="<?php echo htmlspecialchars($_POST['naam'] ?? ''); ?>" placeholder="bijv. Luxe Lodge"
                        required>
                </div>
                <div class="form-group">
                    <label for="beschrijving">Beschrijving</label>
                    <textarea id="beschrijving" name="beschrijving" maxlength="80"
                        placeholder="Korte beschrijving (max. 80 tekens)"><?php echo htmlspecialchars($_POST['beschrijving'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="capaciteit">Capaciteit (personen) <span style="color:#e74c3c;">*</span></label>
                    <input type="number" id="capaciteit" name="capaciteit" min="1" max="99"
                        value="<?php echo htmlspecialchars($_POST['capaciteit'] ?? ''); ?>" placeholder="bijv. 4"
                        required>
                </div>
                <div class="form-group">
                    <label for="prijs">Prijs per nacht (€) <span style="color:#e74c3c;">*</span></label>
                    <input type="number" id="prijs" name="prijs" min="0" step="1"
                        value="<?php echo htmlspecialchars($_POST['prijs'] ?? ''); ?>" placeholder="bijv. 150" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Opslaan</button>
                    <a href="type_overzicht.php" class="btn btn-secondary">Annuleren</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>