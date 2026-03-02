<?php
/*  
afspraakid
gebruikerid
lodgeid
titel
starttijd
eindtijd
status
toelichting
aantalmensen
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS afspraak (
            afspraakid INT(11) AUTO_INCREMENT PRIMARY KEY,
            gebruikerid INT(11),
            lodgeid INT(11),
            starttijd DATETIME,
            eindtijd DATETIME,
            status VARCHAR(30),
            toelichting VARCHAR(80),
            aantalmensen INT(11),

            CONSTRAINT fk_afspraak_gebruiker
                FOREIGN KEY (gebruikerid)
                REFERENCES gebruiker(gebruikerid)
                ON DELETE CASCADE
                ON UPDATE CASCADE,

            CONSTRAINT fk_afspraak_lodge
                FOREIGN KEY (lodgeid)
                REFERENCES lodge(lodgeid)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )
    ");
};
?>