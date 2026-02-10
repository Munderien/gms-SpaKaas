<?php
/*  
onderhoudid
lodgeid
monteurid
prioriteit
omschrijving
status
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS onderhoud (
            onderhoudid INT(11) AUTO_INCREMENT PRIMARY KEY,
            lodgeid INT(11),
            monteurid INT(11),
            prioriteit VARCHAR(50),
            omschrijving VARCHAR(80),
            status VARCHAR(30),
        
            CONSTRAINT fk_onderhoud_lodge
                FOREIGN KEY (lodgeid)
                REFERENCES lodge(lodgeid)
                ON DELETE CASCADE
                ON UPDATE CASCADE,

            CONSTRAINT fk_onderhoud_monteur
                FOREIGN KEY (monteurid)
                REFERENCES gebruiker(gebruikerid)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )
    ");
};
?>