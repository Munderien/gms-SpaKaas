<?php
require '../pages/config.php';
session_start();

if (!isset($_SESSION['gebruikerId'])) {
    header('Location: ../pages/inlog.php');
    exit;
}
$stmt = $db->prepare("SELECT rol FROM gebruiker WHERE gebruikerid = ?");
$stmt->execute([$_SESSION['gebruikerId']]);
$_SESSION['rol'] = (int) $stmt->fetchColumn();
if ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 3) {
    die('Geen toegang');
}

$melding = '';
$factuurLink = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['lodgeid'])) {
    $lodgeid = (int) $_POST['lodgeid'];

    $stmtAfspraak = $db->prepare("
        SELECT a.afspraakid, a.gebruikerid, a.starttijd, a.eindtijd, a.aantalmensen,
               l.lodgetypeid, lt.prijs
        FROM afspraak a
        JOIN lodge l ON l.lodgeid = a.lodgeid
        JOIN lodgetype lt ON lt.typeid = l.lodgetypeid
        WHERE a.lodgeid = ?
        ORDER BY a.starttijd DESC
        LIMIT 1
    ");
    $stmtAfspraak->execute([$lodgeid]);
    $afspraak = $stmtAfspraak->fetch(PDO::FETCH_ASSOC);

    if (!$afspraak) {
        $melding = 'Geen afspraak gevonden voor deze lodge.';
    } else {
        $start = new DateTime($afspraak['starttijd']);
        $eind = new DateTime($afspraak['eindtijd']);
        $nachten = (int) $start->diff($eind)->days;
        if ($nachten < 1)
            $nachten = 1;

        $totaalExBtw = $afspraak['prijs'] * $nachten;

        $extraKosten = 0;
        if (isset($_POST['extra_kosten']) && $_POST['extra_kosten'] != '') {
            $extraKosten = (float) $_POST['extra_kosten'];
        }

        $korting = 0;
        if (isset($_POST['korting']) && $_POST['korting'] != '') {
            $korting = (float) $_POST['korting'];
        }

        $toelichting = '';
        if (isset($_POST['toelichting']) && $_POST['toelichting'] != '') {
            $toelichting = trim($_POST['toelichting']);
        }

        $totaalExBtw = $totaalExBtw + $extraKosten - $korting;
        if ($totaalExBtw < 0)
            $totaalExBtw = 0;

        $omschrijving = 'Uitchecken lodge ' . $lodgeid;
        if ($toelichting != '')
            $omschrijving .= ' - ' . $toelichting;

        $btwPercentage = 21;

        $stmtFactuur = $db->prepare("
            INSERT INTO factuur (afspraakid, gebruikerid, lodgetypeid, factuurdatum,
                                 aantalmensen, totaalbedragexbtw, btwpercentage,
                                 betaalstatus, herinneringsmailstatus, toelichting)
            VALUES (?, ?, ?, CURDATE(), ?, ?, ?, 0, 0, ?)
        ");
        $stmtFactuur->execute([
            $afspraak['afspraakid'],
            $afspraak['gebruikerid'],
            $afspraak['lodgetypeid'],
            $afspraak['aantalmensen'],
            $totaalExBtw,
            $btwPercentage,
            $omschrijving
        ]);

        $nieuwFactuurId = $db->lastInsertId();

        $db->prepare("UPDATE lodge SET status = 'vrij' WHERE lodgeid = ?")->execute([$lodgeid]);

        $melding = 'Lodge uitcheckt! Factuur #' . $nieuwFactuurId . ' is aangemaakt.';
        $factuurLink = '../factuur_manager/print.php?factuurid=' . $nieuwFactuurId;
    }
}

$bezetteLodges = $db->query("
    SELECT l.lodgeid, l.huisnummer, lt.naam AS typename, lt.prijs,
           a.afspraakid, a.starttijd, a.eindtijd, a.aantalmensen,
           g.naam AS klantnaam
    FROM lodge l
    JOIN lodgetype lt ON lt.lodgetypeid = l.typeid
    LEFT JOIN afspraak a ON a.lodgeid = l.lodgeid
    LEFT JOIN gebruiker g ON g.gebruikerid = a.gebruikerid
    WHERE l.status = 'bezet'
    ORDER BY l.lodgeid
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uitchecken – SpaKaas</title>
    <link rel="stylesheet" href="../Style/manager.css">
    <style>
        .lodge-kaart {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .lodge-info h3 {
            margin: 0 0 4px;
            font-size: 1rem;
            color: #2c3e50;
        }

        .lodge-info p {
            margin: 2px 0;
            font-size: .85rem;
            color: #7f8c8d;
        }

        .lodge-info .prijs {
            font-weight: 600;
            color: #27ae60;
        }

        .btn-uitchecken {
            padding: 10px 20px;
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: .9rem;
            cursor: pointer;
        }

        .btn-uitchecken:hover {
            background: #c0392b;
        }

        .melding-succes {
            background: #d4edda;
            color: #155724;
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .melding-succes a {
            color: #155724;
            text-decoration: underline;
        }

        .melding-fout {
            background: #f8d7da;
            color: #721c24;
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .extra-velden {
            display: flex;
            gap: 12px;
            margin-top: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .extra-velden label {
            font-size: .8rem;
            color: #7f8c8d;
            display: block;
            margin-bottom: 4px;
        }

        .extra-velden input {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: .85rem;
            width: 120px;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <div class="manager-container">
        <h1>Uitchecken</h1>

        <?php if ($melding != '' && $factuurLink != ''): ?>
            <div class="melding-succes">
                <?php echo $melding; ?>
                – <a href="<?php echo $factuurLink; ?>">Bekijk factuur</a>
            </div>
        <?php elseif ($melding != ''): ?>
            <div class="melding-fout">
                <?php echo $melding; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($bezetteLodges)): ?>
            <div class="empty-state">Geen bezette lodges op dit moment.</div>
        <?php else: ?>
            <?php foreach ($bezetteLodges as $bl): ?>
                <div class="lodge-kaart">
                    <div class="lodge-info">
                        <h3>Lodge
                            <?php echo $bl['huisnummer']; ?> –
                            <?php echo htmlspecialchars($bl['typename']); ?>
                        </h3>
                        <?php if ($bl['afspraakid']): ?>
                            <p>Klant:
                                <?php echo htmlspecialchars($bl['klantnaam']); ?>
                            </p>
                            <p>Periode:
                                <?php echo date('d-m-Y', strtotime($bl['starttijd'])); ?>
                                t/m
                                <?php echo date('d-m-Y', strtotime($bl['eindtijd'])); ?>
                                (
                                <?php
                                $nachten = (int) (new DateTime($bl['starttijd']))->diff(new DateTime($bl['eindtijd']))->days;
                                if ($nachten < 1) {
                                    $nachten = 1;
                                }

                                echo $nachten . ' nacht';
                                if ($nachten > 1) {
                                    echo 'en';
                                }
                                ?>)
                            </p>
                            <p>Personen:
                                <?php echo $bl['aantalmensen']; ?>
                            </p>
                            <p class="prijs">&euro;
                                <?php echo number_format($bl['prijs'] * $nachten, 2, ',', '.'); ?> excl. BTW
                            </p>
                        <?php else: ?>
                            <p>Geen afspraak gekoppeld</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($bl['afspraakid']): ?>
                        <form method="post">
                            <input type="hidden" name="lodgeid" value="<?php echo $bl['lodgeid']; ?>">
                            <div class="extra-velden">
                                <div>
                                    <label>Extra kosten (&euro;)</label>
                                    <input type="number" name="extra_kosten" value="0" min="0" step="0.01">
                                </div>
                                <div>
                                    <label>Korting (&euro;)</label>
                                    <input type="number" name="korting" value="0" min="0" step="0.01">
                                </div>
                                <div>
                                    <label>Toelichting</label>
                                    <input type="text" name="toelichting">
                                </div>
                                <button type="submit" class="btn-uitchecken"
                                    onclick="return confirm('Weet je zeker dat je lodge <?php echo $bl['huisnummer']; ?> wilt uitchecken?')">
                                    Uitchecken
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>