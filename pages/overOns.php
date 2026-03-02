<?php
session_start();

// Determine application root dynamically
$script = $_SERVER['SCRIPT_NAME'];
if (preg_match('#^(.*?/gms-SpaKaas)#', $script, $m)) {
    $base = $m[1];
} else {
    $base = '';
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Over Ons - SpaKaas Luxe Spa Resort</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f4c5c 0%, #3d8f8f 30%, #80d0c7 100%);
            min-height: 100vh;
            padding-top: 70px;
        }

        .about-hero {
            background: linear-gradient(135deg, rgba(15, 76, 92, 0.9) 0%, rgba(61, 143, 143, 0.9) 100%),
                url('https://via.placeholder.com/1920x600/0f4c5c/ffffff?text=SpaKaas+Resort') center/cover;
            color: white;
            padding: 100px 20px;
            text-align: center;
            margin-bottom: 60px;
        }

        .about-hero h1 {
            font-size: 3.5em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.8s ease-out;
        }

        .about-hero p {
            font-size: 1.3em;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.95;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .section {
            background: white;
            border-radius: 15px;
            padding: 50px;
            margin-bottom: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section h2 {
            color: #0f4c5c;
            font-size: 2.5em;
            margin-bottom: 30px;
            border-bottom: 3px solid #3fa8a8;
            padding-bottom: 15px;
        }

        .section h3 {
            color: #3fa8a8;
            font-size: 1.5em;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .section p {
            color: #555;
            font-size: 1.05em;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .section ul {
            margin-left: 20px;
            margin-bottom: 20px;
        }

        .section li {
            color: #555;
            font-size: 1.05em;
            margin-bottom: 10px;
            line-height: 1.8;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #3fa8a8 0%, #0f4c5c 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(63, 168, 168, 0.2);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1em;
            opacity: 0.95;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .team-member {
            background: #f9f9f9;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-5px);
        }

        .member-image {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
        }

        .member-info {
            padding: 20px;
        }

        .member-name {
            color: #0f4c5c;
            font-size: 1.3em;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .member-role {
            color: #3fa8a8;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .member-bio {
            color: #666;
            font-size: 0.9em;
            line-height: 1.6;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .service-card {
            background: linear-gradient(135deg, rgba(63, 168, 168, 0.1) 0%, rgba(255, 235, 59, 0.05) 100%);
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid #3fa8a8;
            transition: all 0.3s ease;
        }

        .service-card:hover {
            box-shadow: 0 8px 20px rgba(63, 168, 168, 0.15);
            transform: translateY(-3px);
        }

        .service-icon {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .service-title {
            color: #0f4c5c;
            font-size: 1.2em;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .service-desc {
            color: #666;
            line-height: 1.6;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }

        .value-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            border-top: 4px solid #3fa8a8;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .value-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }

        .value-title {
            color: #0f4c5c;
            font-size: 1.3em;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .value-desc {
            color: #666;
            line-height: 1.7;
        }

        .contact-section {
            background: linear-gradient(135deg, #0f4c5c 0%, #3d8f8f 100%);
            color: white;
            border-radius: 15px;
            padding: 50px;
            text-align: center;
        }

        .contact-section h2 {
            color: white;
            border-bottom: 3px solid #ffeb3b;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .contact-item {
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .contact-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .contact-label {
            font-size: 0.9em;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .contact-value {
            font-size: 1.1em;
            font-weight: 600;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #ffeb3b 0%, #ff9800 100%);
            color: #0f4c5c;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 235, 59, 0.3);
        }

        @media (max-width: 768px) {
            .about-hero h1 {
                font-size: 2em;
            }

            .about-hero p {
                font-size: 1em;
            }

            .section {
                padding: 30px 20px;
            }

            .section h2 {
                font-size: 1.8em;
            }

            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/navbarKlant.php'; ?>

    <!-- Hero Section -->
    <div class="about-hero">
        <h1>Over SpaKaas Luxe Spa Resort</h1>
        <p>Een oase van rust en verjonging in het hart van Nederland, sinds 2010</p>
    </div>

    <div class="container">
        <!-- Our Story Section -->
        <div class="section">
            <h2>✨ Ons Verhaal</h2>
            <p>
                SpaKaas Luxe Spa Resort is in 2010 opgericht met een simpel maar krachtig doel: het creëren van een
                heilige plek waar gasten volledig kunnen ontsnappen aan de dagelijkse stress en zichzelf kunnen
                herontdekken. Gelegen in de prachtige Achterhoek, geniet onze resort van een unieke locatie omgeven door
                natuurlijke schoonheid en sereniteit.
            </p>
            <p>
                Wat begon als een kleine spa met slechts 5 lodges, is inmiddels uitgegroeid tot een toonaangevend
                wellnessresort met meer dan 25 luxe lodges. Ons succes is gebaseerd op onze onwankelbare toewijding aan
                kwaliteit, vakmanschap en gastenservice.
            </p>
            <p>
                We behandelen meer dan 15.000 gasten per jaar, waarvan het merendeel regelmatig terugkomt. Dit is het
                beste bewijs dat we onze missie goed vervullen: het bieden van een uitzonderlijke spa-ervaring die
                transformatief en herstellend is.
            </p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">14+</div>
                    <div class="stat-label">Jaar ervaring</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">25+</div>
                    <div class="stat-label">Lodges</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">15K+</div>
                    <div class="stat-label">Jaarlijkse gasten</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">4.9★</div>
                    <div class="stat-label">Gemiddelde rating</div>
                </div>
            </div>
        </div>

        <!-- Our Values Section -->
        <div class="section">
            <h2>💎 Onze Waarden</h2>
            <p>
                Bij SpaKaas Luxe Spa Resort worden we geleid door vier kernwaarden die het hart van ons bedrijf vormen:
            </p>

            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">🌿</div>
                    <div class="value-title">Natuurlijkheid</div>
                    <div class="value-desc">
                        We geloven in de kracht van natuurlijke ingrediënten en traditionele wellness-praktijken. Al
                        onze behandelingen gebruiken biologische en milieuvriendelijke producten.
                    </div>
                </div>
                <div class="value-card">
                    <div class="value-icon">❤️</div>
                    <div class="value-title">Zorg</div>
                    <div class="value-desc">
                        Elke gast is uniek en verdient persoonlijke aandacht. Ons team besteedt tijd om uw behoeften te
                        begrijpen en een aangepaste ervaring te creëren.
                    </div>
                </div>
                <div class="value-card">
                    <div class="value-icon">✨</div>
                    <div class="value-title">Excellentie</div>
                    <div class="value-desc">
                        We streven naar perfectie in alles wat we doen. Van onze therapeuten tot onze faciliteiten, we
                        handhaven de hoogste standaarden.
                    </div>
                </div>
                <div class="value-card">
                    <div class="value-icon">🌍</div>
                    <div class="value-title">Duurzaamheid</div>
                    <div class="value-desc">
                        We zijn verplicht aan het milieu. Onze resort werkt 100% op hernieuwbare energie en we hebben
                        een nul-afvalbeleid.
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Team Section -->
        <div class="section">
            <h2>👥 Ons Team</h2>
            <p>
                Ons team bestaat uit meer dan 50 gekwalificeerde professionals, elk betrokken bij hun vakgebied en
                toegewijd aan gastenservice.
            </p>

            <div class="team-grid">
                <div class="team-member">
                    <div class="member-image">👨‍💼</div>
                    <div class="member-info">
                        <div class="member-name">Oskar Krabbe</div>
                        <div class="member-role">Directeur & Oprichter</div>
                        <div class="member-bio">
                            Met meer dan 20 jaar ervaring in de hospitality-industrie, leidde Oskar de visie van SpaKaas
                            van droom tot werkelijkheid.
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">👩‍⚕️</div>
                    <div class="member-info">
                        <div class="member-name">Jesse Ranter</div>
                        <div class="member-role">Wellness Adviseur</div>
                        <div class="member-bio">
                            Met een Ph.D. in gezondheidswetenschap, Jesse zorgt ervoor dat al onze behandelingen
                            wetenschappelijk verantwoord zijn.
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">👨‍🎓</div>
                    <div class="member-info">
                        <div class="member-name">Marijn Goedhart</div>
                        <div class="member-role">Head Therapeut</div>
                        <div class="member-bio">
                            Marijn traint en begeleidt ons team van 35 therapeuten, zorgende voor consistent hoge
                            kwaliteit en innovatie.
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">👩‍💼</div>
                    <div class="member-info">
                        <div class="member-name">Mert Akdag</div>
                        <div class="member-role">Gasten Ervaring Manager</div>
                        <div class="member-bio">
                            Mert zorgt ervoor dat elke gast een onvergetelijke ervaring heeft, van boekingsbegeleiding
                            tot nazorg.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Facilities Section -->
        <div class="section">
            <h2>🏛️ Onze Faciliteiten</h2>
            <p>
                SpaKaas beschikt over state-of-the-art faciliteiten ontworpen voor maximaal comfort en ontspanning:
            </p>
            <ul>
                <li><strong>25 Luxe Lodges:</strong> Elk voorzien van massage-bedden, warmtesystemen en aromadiffusers
                </li>
                <li><strong>Spa-zwembad:</strong> Verwarmde zwembad van 500m² met waterstromingen en hydrotherapie-jets
                </li>
                <li><strong>Sauna Komplex:</strong> Vier verschillende sauna's inclusief Fins, infrarood en stoom
                    sauna's</li>
                <li><strong>Yoga & Meditation Studio:</strong> Serene ruimte met bamboe interieur en natuurlijk licht
                </li>
                <li><strong>Wellness Lounge:</strong> Relax-ruimte met open haardplaats en bergkristallen</li>
                <li><strong>Bio Restaurant:</strong> Farm-to-table restaurant met gezonde, biologische menu's</li>
                <li><strong>Retail Boutique:</strong> Selectie van premium wellness-producten en souvenirs</li>
                <li><strong>Parkeerterrein:</strong> Gratis parkeren voor 200+ voertuigen</li>
            </ul>
        </div>

        <!-- Our Commitment Section -->
        <div class="section">
            <h2>🌱 Onze Duurzaamheidscommitment</h2>
            <h3>Milieubewustzijn in Actie</h3>
            <p>
                SpaKaas is trots op onze inspanningen om een duurzame spa-ervaring te bieden. In 2018 maakten we de
                overgang naar 100% hernieuwbare energie, en in 2021 bereikten we carbon-neutraliteit door onze
                operationele emissies op te vangen en om te zetten in groene initiatieven.
            </p>
            <ul>
                <li>☀️ <strong>Zonnepanelen:</strong> 500kW zonnepaneelcapaciteit op ons dak</li>
                <li>♻️ <strong>Nul-afval:</strong> 95% van ons afval wordt gerecycled of gecomposteerd</li>
                <li>💧 <strong>Waterbesparing:</strong> Geavanceerde recyclingssystemen reduceren watergebruik met 40%
                </li>
                <li>🌳 <strong>Reforestation:</strong> Voor elke boekingsreservering planten we twee bomen</li>
                <li>🐝 <strong>Biodivers:</strong> Ons terrein huisvest 15 bijenkasten en wilde bloemweides</li>
            </ul>
        </div>

        <!-- Contact Section -->
        <div class="contact-section">
            <h2>📞 Neem Contact met Ons Op</h2>
            <p style="margin-bottom: 30px;">
                We horen graag van je! Stuur je vragen, opmerkingen of boekingsverzoeken naar ons team.
            </p>

            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">📍</div>
                    <div class="contact-label">Locatie</div>
                    <div class="contact-value">Superweg 420, 6769 CP Urk, Nederland</div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <div class="contact-label">Telefoonnummer</div>
                    <div class="contact-value">+31 (0)611 365 315</div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">✉️</div>
                    <div class="contact-label">Email</div>
                    <div class="contact-value">SpaKaasBV@gmail.com</div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">🕐</div>
                    <div class="contact-label">Openingstijden</div>
                    <div class="contact-value">Altijd open</div>
                </div>
            </div>
        </div>
    </div>

    <br><br>
</body>

</html>