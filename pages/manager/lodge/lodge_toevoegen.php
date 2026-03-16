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
$lodgetypes = $db->query("SELECT lodgetypeid, naam FROM lodgetype ORDER BY naam")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $huisnummer = trim($_POST['huisnummer']);
    $typeid = (int) $_POST['typeid'];
    $status = $_POST['status'];

    if ($huisnummer == '') {
        $melding = 'Huisnummer is verplicht.';
    } elseif ($typeid == 0) {
        $melding = 'Kies een lodgetype.';
    } else {
        $checkNummer = $db->prepare("SELECT COUNT(*) FROM lodge WHERE huisnummer = ?");
        $checkNummer->execute([$huisnummer]);
        if ($checkNummer->fetchColumn() > 0) {
            $melding = 'Er bestaat al een lodge met dit huisnummer.';
        } else {
            $db->prepare("INSERT INTO lodge (typeid, huisnummer, status) VALUES (?, ?, ?)")
                ->execute([$typeid, $huisnummer, $status]);
            header('Location: overzicht.php?toegevoegd=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lodge Toevoegen – SpaKaas</title>
    <link rel="stylesheet" href="../../../Style/manager.css">
</head>

<body>
    <?php include '../../../navbar.php'; ?>
    <div class="manager-container">
        <h1>Nieuwe Lodge Toevoegen</h1>
        <?php if ($melding != ''): ?>
            <p style="color:red; font-weight:bold;">
                <?php echo $melding; ?>
            </p>
        <?php endif; ?>
        <?php if (empty($lodgetypes)): ?>
            <p style="color:#e74c3c; font-weight:bold;">
                Er zijn nog geen lodgetypes aangemaakt.
                <a href="type_toevoegen.php">Maak eerst een lodgetype aan</a>.
            </p>
        <?php else: ?>
            <div class="manager-form">
                <form method="post" novalidate>
                    <div class="form-group">
                        <label for="huisnummer">Huisnummer <span style="color:#e74c3c;">*</span></label>
                        <input type="text" id="huisnummer" name="huisnummer" maxlength="20"
                            value="<?php echo htmlspecialchars($_POST['huisnummer'] ?? ''); ?>" placeholder="bijv. L01"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="typeid">Lodgetype <span style="color:#e74c3c;">*</span></label>
                        <select id="typeid" name="typeid" required>
                            <option value="0">-- Kies een type --</option>
                            <?php foreach ($lodgetypes as $lt): ?>
                                <option value="<?php echo $lt['lodgetypeid']; ?>" <?php if (($_POST['typeid'] ?? '') == $lt['lodgetypeid'])
                                       echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($lt['naam']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="vrij" <?php if (($_POST['status'] ?? '') == 'vrij')
                                echo 'selected'; ?>>Vrij
                            </option>
                            <option value="bezet" <?php if (($_POST['status'] ?? '') == 'bezet')
                                echo 'selected'; ?>>Bezet
                            </option>
                            <option value="onderhoud" <?php if (($_POST['status'] ?? '') == 'onderhoud')
                                echo 'selected'; ?>>Onderhoud</option>
                            <option value="schoonmaak" <?php if (($_POST['status'] ?? '') == 'schoonmaak')
                                echo 'selected'; ?>>Aan de schoonmaak</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">Opslaan</button>
                        <a href="overzicht.php" class="btn btn-secondary">Annuleren</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>