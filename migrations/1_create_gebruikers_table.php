<?php
/*  
gebruikerid
email
wachtwoord
rol
is2faingeschakeld
adres
naam
plaats
postcode
telefoonnummer
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS gebruiker (
            gebruikerid INT(11) AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(150) UNIQUE,
            wachtwoord VARCHAR(255),
            rol INT(11) DEFAULT 0,
            is2faingeschakeld TINYINT(1) NOT NULL DEFAULT 0,
            adres VARCHAR(50),
            naam VARCHAR(30),
            plaats VARCHAR(30),
            postcode VARCHAR(10),
            telefoonnummer VARCHAR(20)
        )
    ");
};
?>