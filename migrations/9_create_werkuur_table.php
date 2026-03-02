<?php
/*  
werkuurid
gebruikerid
begintijd
eindtijd
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS werkuur (
            werkuurid INT(11) AUTO_INCREMENT PRIMARY KEY,
            gebruikerid INT(11),
            begintijd DATETIME,
            eindtijd DATETIME,

            CONSTRAINT fk_werkuur_gebruiker
                FOREIGN KEY (gebruikerid)
                REFERENCES gebruiker(gebruikerid)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )
    ");
};
?>