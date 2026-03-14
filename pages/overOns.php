<?php
session_start();

// Language configuration
$availableLanguages = ['nl', 'en', 'de', 'fr', 'tr'];
$currentLang = $_SESSION['language'] ?? 'nl';

// Validate language exists
if (!in_array($currentLang, $availableLanguages)) {
    $currentLang = 'nl';
    $_SESSION['language'] = $currentLang;
}

// Load language file
$langFile = __DIR__ . "/vertaling/{$currentLang}.php";

if (file_exists($langFile)) {
    $lang = require_once($langFile);
} else {
    die("Error: Language file not found at {$langFile}");
}

// Determine application root dynamically
$script = $_SERVER['SCRIPT_NAME'];
if (preg_match('#^(.*?/gms-SpaKaas)#', $script, $m)) {
    $base = $m[1];
} else {
    $base = '';
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['about_title'] ?> - SpaKaas</title>
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
        <h1><?= $lang['about_title'] ?></h1>
        <p><?= $lang['about_subtitle'] ?></p>
    </div>

    <div class="container">
        <!-- Our Story Section -->
        <div class="section">
            <h2><?= $lang['about_story_heading'] ?></h2>
            <p><?= $lang['about_story_p1'] ?></p>
            <p><?= $lang['about_story_p2'] ?></p>
            <p><?= $lang['about_story_p3'] ?></p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">14+</div>
                    <div class="stat-label"><?= $lang['about_stat_years'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">25+</div>
                    <div class="stat-label"><?= $lang['about_stat_lodges'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">15K+</div>
                    <div class="stat-label"><?= $lang['about_stat_guests'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">4.9★</div>
                    <div class="stat-label"><?= $lang['about_stat_rating'] ?></div>
                </div>
            </div>
        </div>

        <!-- Our Values Section -->
        <div class="section">
            <h2><?= $lang['about_values_heading'] ?></h2>
            <p><?= $lang['about_values_intro'] ?></p>

            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">🌿</div>
                    <div class="value-title"><?= $lang['about_value_nature'] ?></div>
                    <div class="value-desc"><?= $lang['about_value_nature_desc'] ?></div>
                </div>
                <div class="value-card">
                    <div class="value-icon">❤️</div>
                    <div class="value-title"><?= $lang['about_value_care'] ?></div>
                    <div class="value-desc"><?= $lang['about_value_care_desc'] ?></div>
                </div>
                <div class="value-card">
                    <div class="value-icon">✨</div>
                    <div class="value-title"><?= $lang['about_value_excellence'] ?></div>
                    <div class="value-desc"><?= $lang['about_value_excellence_desc'] ?></div>
                </div>
                <div class="value-card">
                    <div class="value-icon">🌍</div>
                    <div class="value-title"><?= $lang['about_value_sustainability'] ?></div>
                    <div class="value-desc"><?= $lang['about_value_sustainability_desc'] ?></div>
                </div>
            </div>
        </div>

        <!-- Our Team Section -->
        <div class="section">
            <h2><?= $lang['about_team_heading'] ?></h2>
            <p><?= $lang['about_team_intro'] ?></p>

            <div class="team-grid">
                <div class="team-member">
                    <div class="member-image">👨‍💼</div>
                    <div class="member-info">
                        <div class="member-name">Oskar Krabbe</div>
                        <div class="member-role"><?= $lang['about_team_director'] ?></div>
                        <div class="member-bio"><?= $lang['about_team_director_bio'] ?></div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">👩‍⚕️</div>
                    <div class="member-info">
                        <div class="member-name">Jesse Ranter</div>
                        <div class="member-role"><?= $lang['about_team_wellness'] ?></div>
                        <div class="member-bio"><?= $lang['about_team_wellness_bio'] ?></div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">👨‍🎓</div>
                    <div class="member-info">
                        <div class="member-name">Marijn Goedhart</div>
                        <div class="member-role"><?= $lang['about_team_therapist'] ?></div>
                        <div class="member-bio"><?= $lang['about_team_therapist_bio'] ?></div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">👩‍💼</div>
                    <div class="member-info">
                        <div class="member-name">Mert Akdag</div>
                        <div class="member-role"><?= $lang['about_team_experience'] ?></div>
                        <div class="member-bio"><?= $lang['about_team_experience_bio'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Facilities Section -->
        <div class="section">
            <h2><?= $lang['about_facilities_heading'] ?></h2>
            <p><?= $lang['about_facilities_intro'] ?></p>
            <ul>
                <li><strong><?= $lang['about_stat_lodges'] ?> <?= $lang['about_facility_lodges'] ?>:</strong> <?= $lang['about_facility_lodges_desc'] ?></li>
                <li><strong><?= $lang['about_facility_pool'] ?>:</strong> <?= $lang['about_facility_pool_desc'] ?></li>
                <li><strong><?= $lang['about_facility_sauna'] ?>:</strong> <?= $lang['about_facility_sauna_desc'] ?></li>
                <li><strong><?= $lang['about_facility_yoga'] ?>:</strong> <?= $lang['about_facility_yoga_desc'] ?></li>
                <li><strong><?= $lang['about_facility_lounge'] ?>:</strong> <?= $lang['about_facility_lounge_desc'] ?></li>
                <li><strong><?= $lang['about_facility_restaurant'] ?>:</strong> <?= $lang['about_facility_restaurant_desc'] ?></li>
                <li><strong><?= $lang['about_facility_boutique'] ?>:</strong> <?= $lang['about_facility_boutique_desc'] ?></li>
                <li><strong><?= $lang['about_facility_parking'] ?>:</strong> <?= $lang['about_facility_parking_desc'] ?></li>
            </ul>
        </div>

        <!-- Our Commitment Section -->
        <div class="section">
            <h2><?= $lang['about_sustainability_heading'] ?></h2>
            <h3><?= $lang['about_sustainability_subheading'] ?></h3>
            <p><?= $lang['about_sustainability_intro'] ?></p>
            <ul>
                <li>☀️ <strong><?= $lang['about_sustainability_solar'] ?>:</strong> <?= $lang['about_sustainability_solar_desc'] ?></li>
                <li>♻️ <strong><?= $lang['about_sustainability_waste'] ?>:</strong> <?= $lang['about_sustainability_waste_desc'] ?></li>
                <li>💧 <strong><?= $lang['about_sustainability_water'] ?>:</strong> <?= $lang['about_sustainability_water_desc'] ?></li>
                <li>🌳 <strong><?= $lang['about_sustainability_reforestation'] ?>:</strong> <?= $lang['about_sustainability_reforestation_desc'] ?></li>
                <li>🐝 <strong><?= $lang['about_sustainability_biodiversity'] ?>:</strong> <?= $lang['about_sustainability_biodiversity_desc'] ?></li>
            </ul>
        </div>

        <!-- Contact Section -->
        <div class="contact-section">
            <h2><?= $lang['about_contact_heading'] ?></h2>
            <p style="margin-bottom: 30px;"><?= $lang['about_contact_intro'] ?></p>

            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">📍</div>
                    <div class="contact-label"><?= $lang['about_contact_location'] ?></div>
                    <div class="contact-value"><?= $lang['about_contact_location_address'] ?></div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <div class="contact-label"><?= $lang['about_contact_phone'] ?></div>
                    <div class="contact-value"><?= $lang['about_contact_phone_number'] ?></div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">✉️</div>
                    <div class="contact-label"><?= $lang['about_contact_email'] ?></div>
                    <div class="contact-value"><?= $lang['about_contact_email_address'] ?></div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">🕐</div>
                    <div class="contact-label"><?= $lang['about_contact_hours'] ?></div>
                    <div class="contact-value"><?= $lang['about_contact_hours_value'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <br><br>
</body>

</html>