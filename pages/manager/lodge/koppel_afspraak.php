<?php
require '../../pages/config.php';
session_start();

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: ../../../gms-SpaKaas/pages/inlog.php');
    exit;
}
$stmt = $db->prepare("SELECT rol FROM gebruiker WHERE gebruikerid = ?");
$stmt->execute([$_SESSION['gebruikerId']]);
$_SESSION['rol'] = (int) $stmt->fetchColumn();
if ($_SESSION['rol'] != 3) {
    die('Geen toegang');
}

$melding = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $afspraakid = (int) $_POST['afspraakid'];
    $lodgeid = (int) $_POST['lodgeid'];

    if ($afspraakid < 1 || $lodgeid < 1) {
        $melding = 'Kies een geldige afspraak en lodge.';
    } else {
        $db->prepare("UPDATE afspraak SET lodgeid = ? WHERE afspraakid = ?")->execute([$lodgeid, $afspraakid]);
        $db->prepare("UPDATE lodge SET status = 'bezet' WHERE lodgeid = ?")->execute([$lodgeid]);
        $melding = 'Lodge succesvol gekoppeld!';
    }
}

$afspraken = $db->query("SELECT a.afspraakid, a.titel, a.starttijd, a.eindtijd,
                             g.naam AS gebruikersnaam, a.lodgeid AS huidig_lodgeid
                      FROM afspraak a
                      JOIN gebruiker g ON g.gebruikerid = a.gebruikerid
                      ORDER BY a.starttijd DESC")->fetchAll(PDO::FETCH_ASSOC);

$vrijeLodges = $db->query("SELECT l.lodgeid, l.huisnummer, lt.naam AS typename
                     FROM lodge l
                     JOIN lodgetype lt ON lt.typeid = l.lodgetypeid
                     WHERE l.status = 'vrij'
                     ORDER BY l.lodgeid")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lodge koppelen aan Afspraak – SpaKaas</title>
    <link rel="stylesheet" href="/dms-spakaas/gms-SpaKaas/Style/manager.css">
</head>

<body>
    <?php include '../../navbar.php'; ?>
    <div class="manager-container">
        <h1>Lodge koppelen aan Afspraak</h1>

        <?php if ($melding != ''): ?>
            <p style="color:green; font-weight:bold;"><?php echo $melding; ?></p>
        <?php endif; ?>

        <?php if (empty($vrijeLodges)): ?>
            <div class="alert alert-warning">Er zijn momenteel geen vrije lodges beschikbaar.</div>
        <?php endif; ?>

        <div class="manager-form">
            <form method="post">
                <div class="form-group">
                    <label for="afspraakid">Afspraak <span style="color:#e74c3c;">*</span></label>
                    <select id="afspraakid" name="afspraakid" required>
                        <option value="">-- Kies een afspraak --</option>
                        <?php foreach ($afspraken as $a): ?>
                            <option value="<?php echo $a['afspraakid']; ?>" <?php if (isset($_POST['afspraakid']) && $_POST['afspraakid'] == $a['afspraakid'])
                                   echo 'selected'; ?>>
                                #
                                <?php echo $a['afspraakid']; ?> –
                                <?php echo htmlspecialchars($a['titel']); ?>
                                (
                                <?php echo htmlspecialchars($a['gebruikersnaam']); ?>,
                                <?php echo date('d-m-Y', strtotime($a['starttijd'])); ?> t/m
                                <?php echo date('d-m-Y', strtotime($a['eindtijd'])); ?>)
                                <?php if ($a['huidig_lodgeid']): ?> – lodge #
                                    <?php echo $a['huidig_lodgeid']; ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lodgeid">Vrije lodge <span style="color:#e74c3c;">*</span></label>
                    <select id="lodgeid" name="lodgeid" required <?php if (empty($vrijeLodges))
                        echo 'disabled'; ?>>
                        <option value="">-- Kies een lodge --</option>
                        <?php foreach ($vrijeLodges as $l): ?>
                            <option value="<?php echo $l['lodgeid']; ?>" <?php if (isset($_POST['lodgeid']) && $_POST['lodgeid'] == $l['lodgeid'])
                                   echo 'selected'; ?>>
                                Lodge #
                                <?php echo $l['lodgeid']; ?> –
                                <?php echo htmlspecialchars($l['huisnummer']); ?>
                                (
                                <?php echo htmlspecialchars($l['typename']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success" <?php if (empty($vrijeLodges))
                        echo 'disabled'; ?>>Koppelen</button>
                    <a href="overzicht.php" class="btn btn-secondary">Terug naar overzicht</a>
                </div>
            </form>
        </div>


        <h2 style="margin-top:40px;margin-bottom:12px;font-size:1.2rem;color:#2c3e50;">Overzicht afspraken &amp;
            gekoppelde lodges</h2>
        <table class="manager-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titel</th>
                    <th>Gast</th>
                    <th>Van</th>
                    <th>Tot</th>
                    <th>Gekoppelde lodge</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($afspraken as $a): ?>
                    <tr>
                        <td>
                            <?php echo $a['afspraakid']; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($a['titel']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($a['gebruikersnaam']); ?>
                        </td>
                        <td>
                            <?php echo date('d-m-Y', strtotime($a['starttijd'])); ?>
                        </td>
                        <td>
                            <?php echo date('d-m-Y', strtotime($a['eindtijd'])); ?>
                        </td>
                        <td>
                            <?php if ($a['huidig_lodgeid']): ?>
                                Lodge #
                                <?php echo $a['huidig_lodgeid']; ?>
                            <?php else: ?>
                                <em style="color:#999;">Nog niet gekoppeld</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>