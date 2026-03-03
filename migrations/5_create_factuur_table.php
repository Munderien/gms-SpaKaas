<?php
/*
factuurid
afspraakid
gebruikerid
lodgetypeid
factuurdatum
aantalmensen
totaalbedragexbtw
btwpercentage
betaalstatus
herrinneringsmailstatus
toelichting  
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS factuur (
            factuurid INT(11) AUTO_INCREMENT PRIMARY KEY,
            afspraakid INT(11),
            gebruikerid INT(11),
            typeid INT(11),
            factuurdatum DATE,
            aantalmensen INT(11),
            totaalbedragexbtw DECIMAL(20,0),
            btwprecentage DECIMAL(20,0),
            betaalstatus TINYINT(1) NOT NULL DEFAULT 0,
            herinneringsmailstatus TINYINT(1) NOT NULL DEFAULT 0,
            toelichting VARCHAR(80),

            CONSTRAINT fk_factuur_afspraak
                FOREIGN KEY (afspraakid)
                REFERENCES afspraak(afspraakid)
                ON DELETE CASCADE
                ON UPDATE CASCADE,

            CONSTRAINT fk_factuur_gebruiker
                FOREIGN KEY (gebruikerid)
                REFERENCES gebruiker(gebruikerid)
                ON DELETE CASCADE
                ON UPDATE CASCADE,

            CONSTRAINT fk_factuur_lodgetype
                FOREIGN KEY (typeid)
                REFERENCES lodgetype(lodgetypeid)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )
    ");
};
?>