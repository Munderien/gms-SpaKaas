<?php
/*  
favorieteid
gebruikerid
lodgetypeid
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS favoriete (
            favorieteid INT(11) AUTO_INCREMENT PRIMARY KEY,
            gebruikerid INT(11),
            lodgetypeid INT(11),

            CONSTRAINT fk_favoriete_gebruiker
                FOREIGN KEY (gebruikerid)
                REFERENCES gebruiker(gebruikerid)
                ON DELETE CASCADE
                ON UPDATE CASCADE,

            CONSTRAINT fk_favoriete_lodgetype
                FOREIGN KEY (lodgetypeid)
                REFERENCES lodgetype(typeid)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )
    ");
};
?>