<?php
require '../../pages/config.php';
session_start();

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: /dms-spakaas/gms-SpaKaas/pages/inlog.php');
    exit;
}
$rolCheck = $db->prepare("SELECT rol FROM gebruiker WHERE gebruikerid = ?");
$rolCheck->execute([$_SESSION['gebruikerId']]);
$_SESSION['rol'] = (int) $rolCheck->fetchColumn();
if ($_SESSION['rol'] != 3) {
    header('Location: /dms-spakaas/gms-SpaKaas/pages/inlog.php');
    exit;
}

$typeid = (int) ($_GET['id'] ?? 0);
if ($typeid < 1) {
    header('Location: type_overzicht.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM lodgetype WHERE typeid = ?");
$stmt->execute([$typeid]);
$lt = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$lt) {
    header('Location: type_overzicht.php');
    exit;
}

$melding = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $naam = trim($_POST['naam']);
    $beschrijving = trim($_POST['beschrijving']);
    $capaciteit = (int) $_POST['capiciteit'];
    $prijs = (float) $_POST['prijs'];

    if ($naam == '') {
        $melding = 'Naam is verplicht.';
    } else {
        $db->prepare("UPDATE lodgetype SET naam=?, beschrijving=?, capiciteit=?, prijs=? WHERE typeid=?")
            ->execute([$naam, $beschrijving, $capaciteit, $prijs, $typeid]);
        header('Location: type_overzicht.php?bijgewerkt=1');
        exit;
    }

    $lt['naam'] = $naam;
    $lt['beschrijving'] = $beschrijving;
    $lt['capiciteit'] = $capaciteit;
    $lt['prijs'] = $prijs;
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lodgetype Bewerken – SpaKaas</title>
    <link rel="stylesheet" href="../../Style/manager.css">
</head>

<body>
    <?php include '../../navbar.php'; ?>
    <div class="manager-container">
        <h1>Lodgetype Bewerken –
            <?php echo htmlspecialchars($lt['naam']); ?>
        </h1>

        <?php if ($melding != ''): ?>
            <p style="color:red; font-weight:bold;"><?php echo $melding; ?></p>
        <?php endif; ?>

        <div class="manager-form">
            <form method="post" novalidate>
                <div class="form-group">
                    <label for="naam">Naam <span style="color:#e74c3c;">*</span></label>
                    <input type="text" id="naam" name="naam" maxlength="30"
                        value="<?php echo htmlspecialchars($lt['naam']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="beschrijving">Beschrijving</label>
                    <textarea id="beschrijving" name="beschrijving"
                        maxlength="80"><?php echo htmlspecialchars($lt['beschrijving']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="capiciteit">Capaciteit (personen) <span style="color:#e74c3c;">*</span></label>
                    <input type="number" id="capiciteit" name="capiciteit" min="1" max="99"
                        value="<?php echo (int) $lt['capiciteit']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="prijs">Prijs per nacht (€) <span style="color:#e74c3c;">*</span></label>
                    <input type="number" id="prijs" name="prijs" min="0" step="1"
                        value="<?php echo (int) $lt['prijs']; ?>" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Wijzigingen opslaan</button>
                    <a href="type_overzicht.php" class="btn btn-secondary">Annuleren</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>