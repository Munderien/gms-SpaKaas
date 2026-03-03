<?php
require '../../config.php';
session_start();

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: /dms-spakaas/gms-SpaKaas/pages/inlog.php');
    exit;
}
$rolCheck = $db->prepare("SELECT rol FROM gebruiker WHERE gebruikerid = ?");
$rolCheck->execute([$_SESSION['gebruikerId']]);
$_SESSION['rol'] = (int) $rolCheck->fetchColumn();
if ($_SESSION['rol'] != 3) {
    die('Geen toegang – je hebt geen managerrechten.');
}

$melding = '';

if (isset($_GET['toegevoegd'])) {
    $melding = 'Lodgetype succesvol toegevoegd.';
} elseif (isset($_GET['bijgewerkt'])) {
    $melding = 'Lodgetype succesvol bijgewerkt.';
}

if (isset($_POST['verwijder_typeid'])) {
    $typeid = (int) $_POST['verwijder_typeid'];
    $check = $db->prepare("SELECT COUNT(*) FROM lodge WHERE lodgetypeid = ?");
    $check->execute([$typeid]);
    if ($check->fetchColumn() > 0) {
        $melding = 'Dit lodgetype kan niet worden verwijderd omdat er nog lodges aan gekoppeld zijn.';
    } else {
        $db->prepare("DELETE FROM lodgetype WHERE typeid = ?")->execute([$typeid]);
        $melding = 'Lodgetype succesvol verwijderd.';
    }
}

$stmt = $db->query("SELECT lt.typeid, lt.naam, lt.beschrijving, lt.capiciteit, lt.prijs,
                                COUNT(l.lodgeid) AS aantal_lodges
                         FROM lodgetype lt
                         LEFT JOIN lodge l ON l.lodgetypeid = lt.typeid
                         GROUP BY lt.typeid ORDER BY lt.typeid");
$lodgetypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lodgetypes Beheren – SpaKaas</title>
    <link rel="stylesheet" href="../../../Style/manager.css">
</head>

<body>
    <?php include '../../../navbar.php'; ?>
    <div class="manager-container">
        <h1>Lodgetypes Beheren</h1>

        <?php if ($melding != ''): ?>
            <p style="color:green; font-weight:bold;"><?php echo $melding; ?></p>
        <?php endif; ?>

        <a href="type_toevoegen.php" class="btn btn-success" style="margin-bottom:20px;">+ Nieuw lodgetype toevoegen</a>
        <a href="overzicht.php" class="btn btn-secondary" style="margin-bottom:20px;margin-left:8px;">← Lodges</a>

        <?php if (empty($lodgetypes)): ?>
            <div class="empty-state">Geen lodgetypes gevonden.</div>
        <?php else: ?>
            <table class="manager-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>Beschrijving</th>
                        <th>Capaciteit</th>
                        <th>Prijs/nacht</th>
                        <th>Lodges</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lodgetypes as $lt): ?>
                        <tr>
                            <td><?php echo $lt['typeid']; ?></td>
                            <td><?php echo htmlspecialchars($lt['naam']); ?></td>
                            <td><?php echo htmlspecialchars($lt['beschrijving']); ?></td>
                            <td><?php echo $lt['capiciteit']; ?> personen</td>
                            <td>€ <?php echo number_format($lt['prijs'], 2, ',', '.'); ?></td>
                            <td><?php echo $lt['aantal_lodges']; ?></td>
                            <td class="actions">
                                <a href="type_bewerken.php?id=<?php echo $lt['typeid']; ?>"
                                    class="btn btn-warning btn-sm">Bewerken</a>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="verwijder_typeid" value="<?php echo $lt['typeid']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Weet je zeker dat je &quot;<?php echo htmlspecialchars(addslashes($lt['naam'])); ?>&quot; wilt verwijderen?');">
                                        Verwijderen
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>