<?php
/**
 * Dutch Language File
 * Language: Nederlands (nl)
 * Used for session-based language management
 */

return [
    // ==================== HOME PAGE ====================
    // Welcome section
    'welcome' => 'Welkom',
    'welcome_back' => 'Welkom terug',
    'welcome_guest' => 'Welkom bij SpaKaas',
    'welcome_subtitle' => 'Geniet van pure ontspanning en luxe',
    'welcome_intro' => 'Fijn dat je bij onze Luxe Spa Resort bent. Hier kun je volledig ontsnappen van het dagelijkse leven en jezelf verwennen met de beste behandelingen.',
    
    // Features
    'premium_treatments' => 'Premium Behandelingen',
    'premium_treatments_desc' => 'Kies uit onze exclusieve wellness-aanbiedingen',
    'easy_booking' => 'Makkelijk Boeken',
    'easy_booking_desc' => 'Reserveer direct jouw favoriete behandeling',
    'special_offers' => 'Speciale Aanbiedingen',
    'special_offers_desc' => 'Exclusieve deals voor onze leden',
    'vip_status' => 'VIP Status',
    'vip_status_desc' => 'Geniet van extra voordelen en privileges',
    
    // Statistics
    'availability_24_7' => '24/7',
    'availability_label' => 'Beschikbaarheid',
    'great_reviews' => 'Geweldige',
    'reviews_label' => 'Beoordelingen',
    
    // Reviews section
    'recent_reviews' => 'Recente reviews',
    'create_review' => 'Review maken',
    'login_to_review' => 'Log in om een review te schrijven',
    'rating' => 'Beoordeling',
    'out_of_5' => '/5',
    'edit' => 'Bewerken',
    
    // Database/Error messages
    'db_connection_failed' => 'Databaseverbinding mislukt',
    'guest' => 'Gast',
    'login_link' => 'Log in',
    
    // ==================== NAVIGATION ====================
    'nav_home' => 'Home',
    'nav_bookings' => 'Boekingen',
    'nav_profile' => 'Profiel',
    'nav_about' => 'Over ons',
    'nav_reviews' => 'Reviews',
    'nav_logout' => 'Uitloggen',
    'nav_login' => 'Inloggen',
    'nav_employee' => 'Medewerkerpagina',
    
    // ==================== LODGES PAGE ====================
    'lodges_title' => 'Onze Lodges',
    'lodges_description' => 'Beschrijving',
    'lodges_capacity' => 'Capaciteit',
    'lodges_price' => 'Prijs',
    'lodges_persons' => 'personen',
    'lodges_book_appointment' => 'Maak afspraak',
    'lodges_currency' => '€',
    
    // ==================== LODGE PDP (Product Detail Page) ====================
    'lodge_pdp_invalid' => 'Ongeldig lodgetype.',
    'lodge_pdp_not_found' => 'Lodgetype niet gevonden.',
    'lodge_pdp_capacity_label' => 'Capaciteit:',
    'lodge_pdp_capacity_unit' => 'personen',
    'lodge_pdp_id_label' => 'Lodgetype ID:',
    'lodge_pdp_book_btn' => 'Maak afspraak',
    'lodge_pdp_favorite_btn' => 'Favorite',
    'lodge_pdp_unfavorite_btn' => 'Unfavorite',
    
    // ==================== MAKE APPOINTMENT PAGE ====================
    'appointment_title' => 'Nieuwe afspraak toevoegen',
    'appointment_start_time' => 'Begintijd:',
    'appointment_end_time' => 'Eindtijd:',
    'appointment_description' => 'Beschrijving:',
    'appointment_select_user' => 'Select gebruiker:',
    'appointment_select_lodgetype' => 'Select Lodgetype:',
    'appointment_select_lodgetype_placeholder' => '-- Select Lodgetype --',
    'appointment_lodge_info_title' => 'Geselecteerde lodge informatie',
    'appointment_lodge_info_name' => 'Naam:',
    'appointment_lodge_info_capacity' => 'Capaciteit:',
    'appointment_lodge_info_price' => 'Prijs:',
    'appointment_lodge_info_description' => 'Beschrijving:',
    'appointment_select_users' => '-- Select --',
    'appointment_number_of_people' => 'Aantal mensen:',
    'appointment_submit_btn' => 'Toevoegen',
    'appointment_connection_failed' => 'Connection failed:',
    'appointment_not_logged_in' => 'Je bent niet ingelogd.',
    
    // Appointment error messages
    'appointment_error_no_user' => 'Selecteer een gebruiker.',
    'appointment_error_no_lodgetype' => 'Selecteer een lodgetype.',
    'appointment_error_invalid_people_count' => 'Aantal mensen moet groter zijn dan 0.',
    'appointment_error_invalid_capacity' => 'Ongeldig lodgetype of capaciteit niet gevonden.',
    'appointment_error_capacity_exceeded' => 'Je kan niet meer dan :capacity personen boeken voor dit lodgetype.',
    'appointment_error_past_date' => 'Datum mag niet in het verleden liggen.',
    'appointment_error_invalid_time' => 'Eindtijd moet later zijn dan begintijd.',
    'appointment_error_no_availability' => 'Geen beschikbare lodge gevonden voor dit lodgetype in deze periode.',
    'appointment_error_db' => 'Fout bij toevoegen:',
    
    // Appointment success messages
    'appointment_success_title' => 'Bevestiging boeking',
    'appointment_success_message' => 'Beste :name,

Uw afspraak is succesvol aangemaakt.

Details:
- Starttijd: :startTime
- Eindtijd: :endTime
- Toelichting: :description
- Aantal mensen: :numberOfPeople

Wij zien u graag op de afgesproken datum en wensen u alvast een fijne tijd toe!',
    
    // ==================== PROFILE/EDIT USER PAGE ====================
    'edit_user_title' => 'Gegevens bijwerken',
    'edit_user_email' => 'Email',
    'edit_user_email_required' => 'Moet een geldige email zijn',
    'edit_user_password' => 'Wachtwoord',
    'edit_user_password_placeholder' => 'Laat leeg om niet te wijzigen',
    'edit_user_password_empty_note' => 'Laat leeg als u het wachtwoord niet wil wijzigen',
    'edit_user_password_invalid' => 'Wachtwoord voldoet niet aan de vereisten.',
    'edit_user_name' => 'Naam',
    'edit_user_address' => 'Adres',
    'edit_user_postcode' => 'Postcode',
    'edit_user_postcode_format' => 'Formaat: NNNN AA (bijv. 1234 AB)',
    'edit_user_city' => 'Plaats',
    'edit_user_phone' => 'Telefoon',
    'edit_user_phone_note' => 'Minimaal 9 cijfers',
    'edit_user_two_factor' => 'Twee-factor authenticatie',
    'edit_user_two_factor_no' => 'Nee',
    'edit_user_two_factor_yes' => 'Ja',
    'edit_user_save' => 'Opslaan',
    'edit_user_errors_found' => 'Fouten gevonden',
    'edit_user_required_field' => 'Verplicht veld',
    
    // Password requirements
    'password_requirement_length' => 'Minimaal 8 karakters',
    'password_requirement_uppercase' => 'Minimaal 1 hoofdletter',
    'password_requirement_lowercase' => 'Minimaal 1 kleine letter',
    'password_requirement_number' => 'Minimaal 1 getal',
    'password_requirement_special' => 'Minimaal 1 speciaal teken (!@#$%^&*)',
    
    // Validation messages
    'validation_required_fields' => 'Alle verplichte velden moeten ingevuld zijn (gemarkeerd met *)',
    'validation_invalid_email' => 'Voer een geldig email adres in',
    'validation_invalid_postcode' => 'Postcode moet het formaat NNNN AA hebben (bijv. 1234 AB)',
    'validation_invalid_phone' => 'Telefoon nummer moet minimaal 9 cijfers bevatten',
    'validation_invalid_password' => 'Wachtwoord voldoet niet aan alle vereisten',
    
    // Profile Picture Section
    'profile_picture_title' => 'Profielfoto',
    'profile_picture_current' => 'Huidige profielfoto',
    'profile_picture_none' => 'Geen profielfoto geüpload',
    'profile_picture_select' => 'Selecteer een foto',
    'profile_picture_formats' => 'Toegestane formaten: JPG, PNG, GIF (Max 5MB)',
    'profile_picture_preview_new' => 'Voorbeeld van nieuwe foto',
    'profile_picture_no_selected' => 'Geen foto geselecteerd',
    'profile_picture_change' => 'Wijzigen',
    'profile_picture_delete' => 'Verwijderen',
    'profile_picture_save' => 'Opslaan',
    'profile_picture_cancel' => 'Annuleren',
    'profile_picture_delete_confirm' => 'Weet u zeker dat u uw profielfoto wil verwijderen?',
    'profile_picture_error_required' => 'Selecteer alstublieft een foto',
    'profile_picture_error_too_large' => 'Bestand is te groot. Maximum 5MB is toegestaan.',
    'profile_picture_error_invalid_type' => 'Ongeldig bestandstype. Gebruik JPG, PNG of GIF.',
    
    // ==================== ABOUT US PAGE ====================
    'about_title' => 'Over SpaKaas Luxe Spa Resort',
    'about_subtitle' => 'Een oase van rust en verjonging in het hart van Nederland, sinds 2010',
    'about_story_heading' => '✨ Ons Verhaal',
    'about_story_p1' => 'SpaKaas Luxe Spa Resort is in 2010 opgericht met een simpel maar krachtig doel: het creëren van een heilige plek waar gasten volledig kunnen ontsnappen aan de dagelijkse stress en zichzelf kunnen herontdekken. Gelegen in de prachtige Achterhoek, geniet onze resort van een unieke locatie omgeven door natuurlijke schoonheid en sereniteit.',
    'about_story_p2' => 'Wat begon als een kleine spa met slechts 5 lodges, is inmiddels uitgegroeid tot een toonaangevend wellnessresort met meer dan 25 luxe lodges. Ons succes is gebaseerd op onze onwankelbare toewijding aan kwaliteit, vakmanschap en gastenservice.',
    'about_story_p3' => 'We behandelen meer dan 15.000 gasten per jaar, waarvan het merendeel regelmatig terugkomt. Dit is het beste bewijs dat we onze missie goed vervullen: het bieden van een uitzonderlijke spa-ervaring die transformatief en herstellend is.',
    'about_stat_years' => 'Jaar ervaring',
    'about_stat_lodges' => 'Lodges',
    'about_stat_guests' => 'Jaarlijkse gasten',
    'about_stat_rating' => 'Gemiddelde rating',
    
    'about_values_heading' => '💎 Onze Waarden',
    'about_values_intro' => 'Bij SpaKaas Luxe Spa Resort worden we geleid door vier kernwaarden die het hart van ons bedrijf vormen:',
    'about_value_nature' => 'Natuurlijkheid',
    'about_value_nature_desc' => 'We geloven in de kracht van natuurlijke ingrediënten en traditionele wellness-praktijken. Al onze behandelingen gebruiken biologische en milieuvriendelijke producten.',
    'about_value_care' => 'Zorg',
    'about_value_care_desc' => 'Elke gast is uniek en verdient persoonlijke aandacht. Ons team besteedt tijd om uw behoeften te begrijpen en een aangepaste ervaring te creëren.',
    'about_value_excellence' => 'Excellentie',
    'about_value_excellence_desc' => 'We streven naar perfectie in alles wat we doen. Van onze therapeuten tot onze faciliteiten, we handhaven de hoogste standaarden.',
    'about_value_sustainability' => 'Duurzaamheid',
    'about_value_sustainability_desc' => 'We zijn verplicht aan het milieu. Onze resort werkt 100% op hernieuwbare energie en we hebben een nul-afvalbeleid.',
    
    'about_team_heading' => '👥 Ons Team',
    'about_team_intro' => 'Ons team bestaat uit meer dan 50 gekwalificeerde professionals, elk betrokken bij hun vakgebied en toegewijd aan gastenservice.',
    'about_team_director' => 'Directeur & Oprichter',
    'about_team_director_bio' => 'Met meer dan 20 jaar ervaring in de hospitality-industrie, leidde Oskar de visie van SpaKaas van droom tot werkelijkheid.',
    'about_team_wellness' => 'Wellness Adviseur',
    'about_team_wellness_bio' => 'Met een Ph.D. in gezondheidswetenschap, Jesse zorgt ervoor dat al onze behandelingen wetenschappelijk verantwoord zijn.',
    'about_team_therapist' => 'Head Therapeut',
    'about_team_therapist_bio' => 'Marijn traint en begeleidt ons team van 35 therapeuten, zorgende voor consistent hoge kwaliteit en innovatie.',
    'about_team_experience' => 'Gasten Ervaring Manager',
    'about_team_experience_bio' => 'Mert zorgt ervoor dat elke gast een onvergetelijke ervaring heeft, van boekingsbegeleiding tot nazorg.',
    
    'about_facilities_heading' => '🏛️ Onze Faciliteiten',
    'about_facilities_intro' => 'SpaKaas beschikt over state-of-the-art faciliteiten ontworpen voor maximaal comfort en ontspanning:',
    'about_facility_lodges' => 'Luxe Lodges',
    'about_facility_lodges_desc' => 'Elk voorzien van massage-bedden, warmtesystemen en aromadiffusers',
    'about_facility_pool' => 'Spa-zwembad',
    'about_facility_pool_desc' => 'Verwarmde zwembad van 500m² met waterstromingen en hydrotherapie-jets',
    'about_facility_sauna' => 'Sauna Komplex',
    'about_facility_sauna_desc' => 'Vier verschillende sauna\'s inclusief Fins, infrarood en stoom sauna\'s',
    'about_facility_yoga' => 'Yoga & Meditation Studio',
    'about_facility_yoga_desc' => 'Serene ruimte met bamboe interieur en natuurlijk licht',
    'about_facility_lounge' => 'Wellness Lounge',
    'about_facility_lounge_desc' => 'Relax-ruimte met open haardplaats en bergkristallen',
    'about_facility_restaurant' => 'Bio Restaurant',
    'about_facility_restaurant_desc' => 'Farm-to-table restaurant met gezonde, biologische menu\'s',
    'about_facility_boutique' => 'Retail Boutique',
    'about_facility_boutique_desc' => 'Selectie van premium wellness-producten en souvenirs',
    'about_facility_parking' => 'Parkeerterrein',
    'about_facility_parking_desc' => 'Gratis parkeren voor 200+ voertuigen',
    
    'about_sustainability_heading' => '🌱 Onze Duurzaamheidscommitment',
    'about_sustainability_subheading' => 'Milieubewustzijn in Actie',
    'about_sustainability_intro' => 'SpaKaas is trots op onze inspanningen om een duurzame spa-ervaring te bieden. In 2018 maakten we de overgang naar 100% hernieuwbare energie, en in 2021 bereikten we carbon-neutraliteit door onze operationele emissies op te vangen en om te zetten in groene initiatieven.',
    'about_sustainability_solar' => 'Zonnepanelen',
    'about_sustainability_solar_desc' => '500kW zonnepaneelcapaciteit op ons dak',
    'about_sustainability_waste' => 'Nul-afval',
    'about_sustainability_waste_desc' => '95% van ons afval wordt gerecycled of gecomposteerd',
    'about_sustainability_water' => 'Waterbesparing',
    'about_sustainability_water_desc' => 'Geavanceerde recyclingssystemen reduceren watergebruik met 40%',
    'about_sustainability_reforestation' => 'Reforestation',
    'about_sustainability_reforestation_desc' => 'Voor elke boekingsreservering planten we twee bomen',
    'about_sustainability_biodiversity' => 'Biodivers',
    'about_sustainability_biodiversity_desc' => 'Ons terrein huisvest 15 bijenkasten en wilde bloemweides',
    
    'about_contact_heading' => '📞 Neem Contact met Ons Op',
    'about_contact_intro' => 'We horen graag van je! Stuur je vragen, opmerkingen of boekingsverzoeken naar ons team.',
    'about_contact_location' => 'Locatie',
    'about_contact_location_address' => 'Superweg 420, 6769 CP Urk, Nederland',
    'about_contact_phone' => 'Telefoonnummer',
    'about_contact_phone_number' => '+31 (0)611 365 315',
    'about_contact_email' => 'Email',
    'about_contact_email_address' => 'SpaKaasBV@gmail.com',
    'about_contact_hours' => 'Openingstijden',
    'about_contact_hours_value' => 'Altijd open',
    
    // ==================== REVIEW PAGE ====================
    'review_page_title' => 'Reviews',
    'review_form_title_new' => 'Review plaatsen',
    'review_form_title_edit' => 'Review bewerken',
    'review_form_rating' => 'Beoordeling (1-5)',
    'review_form_message' => 'Bericht',
    'review_form_illustration' => 'Illustratie (optioneel)',
    'review_form_submit' => 'Review plaatsen',
    'review_form_update' => 'Review bijwerken',
    'review_form_delete' => 'Review verwijderen',
    'review_form_delete_confirm' => 'Deze review verwijderen?',
    
    'review_login_prompt' => 'Wil je een review plaatsen?',
    'review_login_required' => 'Je moet ingelogd zijn om een review te schrijven.',
    'review_login_here' => 'Log hier in',
    'review_create_account' => 'maak een account aan',
    
    'review_success_new' => 'Review succesvol verzonden!',
    'review_success_update' => 'Review succesvol bijgewerkt!',
    'review_success_delete' => 'Review succesvol verwijderd.',
    'review_error_rating' => 'Beoordeling moet tussen 1 en 5 liggen.',
    'review_error_message_empty' => 'Bericht is verplicht.',
    'review_error_upload' => 'Fout bij uploaden van bestand.',
    'review_error_file_size' => 'Afbeelding is groter dan 5MB.',
    'review_error_file_type' => 'Alleen JPG, PNG of GIF toegestaan.',
    'review_error_not_found' => 'Review niet gevonden of niet van jou om te bewerken.',
    'review_error_not_found_delete' => 'Review niet gevonden of niet van jou om te verwijderen.',
    'review_error_db' => 'Databaseverbinding mislukt',
    
    'review_heading_recent' => 'Recente reviews',
    'review_rating_label' => 'Beoordeling',
    'review_edit_link' => 'Bewerken',

    // ==================== MY APPOINTMENTS PAGE ====================
    'my_appointments_title' => 'Mijn Afspraken',
    'my_appointments_subtitle' => 'Bekijk al je afspraken en open details',
    'my_appointments_no_appointments' => 'Je hebt nog geen afspraken.',
    'my_appointments_make_appointment' => 'Maak een afspraak',
    'my_appointments_lodge_type' => 'Lodgetype',
    'my_appointments_lodge_number' => 'Lodge huisnummer:',
    'my_appointments_date' => 'Datum:',
    'my_appointments_time' => 'Tijd:',
    'my_appointments_people' => 'Aantal mensen:',
    'my_appointments_open_planner' => 'Open in planneritem',
    
    // Appointment status translations
    'appointment_status_gepland' => 'Gepland',
    'appointment_status_bezig' => 'Bezig',
    'appointment_status_voltooid' => 'Voltooid',
    'appointment_status_geannuleerd' => 'Geannuleerd',
    'appointment_status_bevestigd' => 'Bevestigd',
    'appointment_status_verplaatst' => 'Verplaatst',
    'appointment_status_niet_verschenen' => 'Niet verschenen',
    'appointment_status_in_afwachting' => 'In afwachting',
    'appointment_status_unknown' => 'Onbekend',
];
?>