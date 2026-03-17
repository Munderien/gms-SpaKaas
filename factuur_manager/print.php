<?php
require '../pages/config.php';
session_start();

if (!isset($_GET['factuurid'])) {
    die('Geen factuur opgegeven.');
}

$factuurid = (int) $_GET['factuurid'];

$sql = "
    SELECT
        f.factuurid, f.factuurdatum, f.totaalbedragexbtw, f.btwpercentage,
        f.betaalstatus, f.aantalmensen, f.toelichting, f.gebruikerid,
        g.naam AS klantnaam, g.adres AS klantadres, g.plaats AS klantplaats, g.email AS klantemail,
        l.naam AS lodgetype_naam,
        a.starttijd, a.eindtijd
    FROM factuur f
    INNER JOIN gebruiker g ON f.gebruikerid = g.gebruikerid
    INNER JOIN lodgetype l ON f.typeid = l.lodgetypeid
    INNER JOIN afspraak a  ON f.afspraakid  = a.afspraakid
    WHERE f.factuurid = :id
";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $factuurid]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Factuur niet gevonden.');
}

// Check if user is logged in
if (!isset($_SESSION['gebruikerId'])) {
    header('Location: ../pages/inlog.php');
    exit;
}

// Check if the factuur belongs to the logged-in user
if ((int)$row['gebruikerid'] !== (int)$_SESSION['gebruikerId']) {
    die('U hebt geen toegang tot deze factuur.');
}

$excl = (float) $row['totaalbedragexbtw'];
$btw = (float) $row['btwpercentage'];
$btwBedrag = $excl * ($btw / 100);
$incl = $excl + $btwBedrag;

$start = new DateTime($row['starttijd']);
$eind = new DateTime($row['eindtijd']);
$nachten = (int) $start->diff($eind)->days;
if ($nachten < 1)
    $nachten = 1;

// Check if opened from email (via GET parameter)
$fromEmail = isset($_GET['from_email']) && $_GET['from_email'] == 1;
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factuur #<?php echo $row['factuurid']; ?> – SpaKaas Resort</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            background: #f0f4f8;
            color: #1a202c;
        }


        .no-print {
            background: #2980b9;
            padding: 12px 24px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .no-print button,
        .no-print a {
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            font-weight: 600;
            border: none;
        }

        .no-print button {
            background: #fff;
            color: #2980b9;
        }

        .no-print a {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .no-print button:hover {
            background: #ecf0f1;
        }

        .no-print a:hover {
            background: rgba(255, 255, 255, .35);
        }


        .page {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .12);
            overflow: hidden;
        }


        .factuur-header {
            background: linear-gradient(135deg, #1a3c5e 0%, #2980b9 100%);
            color: #fff;
            padding: 32px 36px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .bedrijfsnaam {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: .5px;
        }

        .bedrijf-sub {
            font-size: 12px;
            opacity: .8;
            margin-top: 4px;
            line-height: 1.6;
        }

        .factuur-nr-blok {
            text-align: right;
        }

        .factuur-nr-blok .label {
            font-size: 11px;
            opacity: .7;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .factuur-nr-blok .nr {
            font-size: 28px;
            font-weight: 700;
        }

        .factuur-nr-blok .datum {
            font-size: 12px;
            opacity: .8;
            margin-top: 4px;
        }


        .factuur-body {
            padding: 32px 36px;
        }


        .info-rij {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .info-blok h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #718096;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid #e2e8f0;
        }

        .info-blok p {
            font-size: 14px;
            line-height: 1.7;
            color: #2d3748;
        }

        .info-blok strong {
            color: #1a202c;
        }


        table.spec {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 13px;
        }

        table.spec thead tr {
            background: #2980b9;
            color: #fff;
        }

        table.spec thead th {
            padding: 11px 14px;
            text-align: left;
            font-weight: 600;
        }

        table.spec thead th:not(:first-child) {
            text-align: right;
        }

        table.spec tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }

        table.spec tbody td:not(:first-child) {
            text-align: right;
        }

        table.spec tbody tr:last-child td {
            border-bottom: none;
        }


        .totaal-blok {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 32px;
        }

        .totaal-tabel {
            width: 280px;
            font-size: 14px;
        }

        .totaal-tabel tr td {
            padding: 6px 10px;
            color: #4a5568;
        }

        .totaal-tabel tr td:last-child {
            text-align: right;
            font-weight: 600;
            color: #2d3748;
        }

        .totaal-tabel tr.totaal-incl {
            background: #2980b9;
            color: #fff;
            border-radius: 6px;
        }

        .totaal-tabel tr.totaal-incl td {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            padding: 10px 10px;
        }


        .status-banner {
            padding: 14px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-banner.betaald {
            background: #c6f6d5;
            color: #276749;
            border-left: 5px solid #38a169;
        }

        .status-banner.open {
            background: #fed7d7;
            color: #9b2c2c;
            border-left: 5px solid #e53e3e;
        }


        .toelichting {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 18px;
            font-size: 13px;
            color: #4a5568;
            margin-bottom: 24px;
        }

        .toelichting h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #a0aec0;
            margin-bottom: 6px;
        }


        .factuur-footer {
            text-align: center;
            padding: 16px;
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #a0aec0;
        }


        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .page {
                box-shadow: none;
                border-radius: 0;
                margin: 0;
                max-width: 100%;
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>

<body>


    <div class="no-print">
        <button onclick="window.print();">Afdrukken</button>
        <button onclick="downloadPDF();" style="background:#27ae60;color:#fff;">⬇ Download als PDF</button>
        <a href="list.php">← Terug naar overzicht</a>
    </div>

    <script>
        function downloadPDF() {
            const element = document.querySelector('.page');
            const filename = 'Factuur_<?php echo str_pad($row['factuurid'], 5, '0', STR_PAD_LEFT); ?>.pdf';
            html2pdf().set({
                margin: 0,
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            }).from(element).save();
            
            // If opened from email, redirect to home after download
            <?php if ($fromEmail): ?>
            setTimeout(function() {
                window.location.href = '../index.php';
            }, 1500);
            <?php endif; ?>
        }

        // Auto-trigger PDF download if opened from email
        <?php if ($fromEmail): ?>
        window.addEventListener('load', function() {
            setTimeout(downloadPDF, 500);
        });
        <?php endif; ?>
    </script>

    <div class="page">


        <div class="factuur-header">
            <div>
                <div class="bedrijfsnaam">SpaKaas Resort</div>
                <div class="bedrijf-sub">
                    Teststraat 123<br>
                    1234 AB SpaKaas<br>
                    info@spakaas.nl
                </div>
            </div>
            <div class="factuur-nr-blok">
                <div class="label">Factuur</div>
                <div class="nr">#<?php echo str_pad($row['factuurid'], 5, '0', STR_PAD_LEFT); ?></div>
                <div class="datum">Datum: <?php echo date('d-m-Y', strtotime($row['factuurdatum'])); ?></div>
            </div>
        </div>

        <div class="factuur-body">


            <div class="info-rij">
                <div class="info-blok">
                    <h3>Gefactureerd aan</h3>
                    <p>
                        <strong><?php echo htmlspecialchars($row['klantnaam']); ?></strong><br>
                        <?php echo htmlspecialchars($row['klantadres']); ?><br>
                        <?php echo htmlspecialchars($row['klantplaats']); ?><br>
                        <?php echo htmlspecialchars($row['klantemail']); ?>
                    </p>
                </div>
                <div class="info-blok">
                    <h3>Afspraakgegevens</h3>
                    <p>
                        <strong>Lodgetype:</strong> <?php echo htmlspecialchars($row['lodgetype_naam']); ?><br>
                        <strong>Inchecken:</strong> <?php echo date('d-m-Y', strtotime($row['starttijd'])); ?><br>
                        <strong>Uitchecken:</strong> <?php echo date('d-m-Y', strtotime($row['eindtijd'])); ?><br>
                        <strong>Aantal nachten:</strong> <?php echo $nachten; ?><br>
                        <strong>Personen:</strong> <?php echo htmlspecialchars($row['aantalmensen']); ?>
                    </p>
                </div>
            </div>


            <table class="spec">
                <thead>
                    <tr>
                        <th>Omschrijving</th>
                        <th>Excl. BTW</th>
                        <th>BTW %</th>
                        <th>BTW bedrag</th>
                        <th>Incl. BTW</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            Verblijf <?php echo htmlspecialchars($row['lodgetype_naam']); ?> –
                            <?php echo $nachten; ?> nacht<?php echo $nachten > 1 ? 'en' : ''; ?>
                        </td>
                        <td>€ <?php echo number_format($excl, 2, ',', '.'); ?></td>
                        <td><?php echo $btw; ?>%</td>
                        <td>€ <?php echo number_format($btwBedrag, 2, ',', '.'); ?></td>
                        <td>€ <?php echo number_format($incl, 2, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>


            <div class="totaal-blok">
                <table class="totaal-tabel">
                    <tr>
                        <td>Subtotaal excl. BTW</td>
                        <td>€ <?php echo number_format($excl, 2, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>BTW (<?php echo $btw; ?>%)</td>
                        <td>€ <?php echo number_format($btwBedrag, 2, ',', '.'); ?></td>
                    </tr>
                    <tr class="totaal-incl">
                        <td>Totaal incl. BTW</td>
                        <td>€ <?php echo number_format($incl, 2, ',', '.'); ?></td>
                    </tr>
                </table>
            </div>


            <?php if ($row['betaalstatus']): ?>
                <div class="status-banner betaald">✓ Deze factuur is voldaan. Bedankt!</div>
            <?php else: ?>
                <div class="status-banner open">✗ Deze factuur staat nog open. Graag op tijd betalen.</div>
            <?php endif; ?>


            <?php if (!empty($row['toelichting'])): ?>
                <div class="toelichting">
                    <h3>Toelichting</h3>
                    <?php echo nl2br(htmlspecialchars($row['toelichting'])); ?>
                </div>
            <?php endif; ?>

        </div>


        <div class="factuur-footer">
            SpaKaas Resort · Superweg 420 · 6769 CP Urk · SpaKaasBV@gmail.com · KVK 12345678 · BTW NL123456789B01
        </div>

    </div>
</body>

</html>