<?php
/*  
schoonmaakid
gebruikerid
lodgeid
status
datum
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS schoonmaak (
            schoonmaakid INT(11) AUTO_INCREMENT PRIMARY KEY,
            gebruikerid INT(11),
            lodgeid INT(11),
            status VARCHAR(20),
            datum DATETIME,

            CONSTRAINT fk_schoonmaak_gebruiker
                FOREIGN KEY (gebruikerid)
                REFERENCES gebruiker(gebruikerid)
                ON DELETE CASCADE
                ON UPDATE CASCADE,

            CONSTRAINT fk_schoonmaak_lodge
                FOREIGN KEY (lodgeid)
                REFERENCES lodge(lodgeid)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )
    ");
};
?>