<?php
require '../../config.php';
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


if (isset($_POST['actie']) && $_POST['actie'] == 'status') {
    $db->prepare("UPDATE lodge SET status = ? WHERE lodgeid = ?")->execute([$_POST['status'], $_POST['lodgeid']]);
    $melding = 'Status bijgewerkt.';
}


if (isset($_POST['actie']) && $_POST['actie'] == 'lodgetype') {
    $db->prepare("UPDATE lodge SET lodgetypeid = ? WHERE lodgeid = ?")->execute([$_POST['lodgetypeid'], $_POST['lodgeid']]);
    $melding = 'Lodgetype bijgewerkt.';
}

$lodges = $db->query("SELECT l.lodgeid, l.huisnummer, l.status, l.typeid,
                           lt.naam AS typename, lt.prijs, lt.lodgetypeid
                    FROM lodge l
                    JOIN lodgetype lt ON lt.lodgetypeid = l.typeid
                    ORDER BY l.lodgeid")->fetchAll(PDO::FETCH_ASSOC);

$lodgetypes = $db->query("SELECT lodgetypeid, naam FROM lodgetype ORDER BY naam")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lodges Beheren – SpaKaas</title>
    <!-- force reload when CSS changes; use relative path to avoid 404 -->
    <link rel="stylesheet" href="../../../Style/manager.css?v=<?php echo time(); ?>">
    <style>
        /* fallback adjustments in case external stylesheet is cached */
        .inline-form {
            display: inline;
        }

        select.compact {
            padding: 5px 8px;
            font-size: .85rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .status-vrij {
            color: #27ae60;
            font-weight: 600;
        }

        .status-bezet {
            color: #e74c3c;
            font-weight: 600;
        }

        .status-onderhoud {
            color: #f39c12;
            font-weight: 600;
        }

        .status-schoonmaak {
            color: #8e44ad;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include '../../../navbar.php'; ?>
    <div class="manager-container">
        <div class="top-bar-manager">
            <h1>Lodges Beheren</h1>
            <div class="top-bar-actions">
                <a href="lodge_toevoegen.php" class="btn btn-success">+ Lodge toevoegen</a>
                <a href="koppel_afspraak.php" class="btn btn-primary">Lodge koppelen aan afspraak</a>
                <a href="type_overzicht.php" class="btn btn-secondary">Lodgetypes beheren</a>
            </div>
        </div>

        <?php if (isset($_GET['toegevoegd'])): ?>
            <p style="color:green; font-weight:bold;">Lodge succesvol toegevoegd!</p>
        <?php endif; ?>

        <?php if ($melding != ''): ?>
            <p style="color:green; font-weight:bold;"><?php echo $melding; ?></p>
        <?php endif; ?>

        <?php if (empty($lodges)): ?>
            <div class="empty-state">Geen lodges gevonden.</div>
        <?php else: ?>
            <table class="manager-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Huisnummer</th>
                        <th>Status</th>
                        <th>Lodgetype</th>
                        <th>Prijs/nacht</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lodges as $l): ?>
                        <tr>
                            <td><?php echo $l['lodgeid']; ?></td>
                            <td><?php echo htmlspecialchars($l['huisnummer']); ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="actie" value="status">
                                    <input type="hidden" name="lodgeid" value="<?php echo $l['lodgeid']; ?>">
                                    <select name="status" class="compact">
                                        <option value="vrij" <?php if ($l['status'] === 'vrij')
                                            echo 'selected'; ?>>Vrij</option>
                                        <option value="bezet" <?php if ($l['status'] === 'bezet')
                                            echo 'selected'; ?>>Bezet
                                        </option>
                                        <option value="onderhoud" <?php if ($l['status'] === 'onderhoud')
                                            echo 'selected'; ?>>
                                            Onderhoud</option>
                                        <option value="schoonmaak" <?php if ($l['status'] === 'schoonmaak')
                                            echo 'selected'; ?>>
                                            Aan de schoonmaak</option>
                                    </select>
                                    <button type="submit" class="btn btn-warning btn-sm">Opslaan</button>
                                </form>
                            </td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="actie" value="lodgetype">
                                    <input type="hidden" name="lodgeid" value="<?php echo $l['lodgeid']; ?>">
                                    <select name="lodgetypeid" class="compact">
                                        <?php foreach ($lodgetypes as $lt): ?>
                                            <option value="<?php echo $lt['typeid']; ?>" <?php if ($lt['lodgetypeid'] == $l['lodgetypeid'])
                                                   echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($lt['naam']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-warning btn-sm">Opslaan</button>
                                </form>
                            </td>
                            <td>€<?php echo number_format($l['prijs'], 2, ',', '.'); ?></td>
                            <td>
                                <a href="type_bewerken.php?id=<?php echo $l['lodgetypeid']; ?>"
                                    class="btn btn-secondary btn-sm">Prijs aanpassen</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>