<?php
/*  
viewedid
gebruikerid
lodgetypeid
viewed_at
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS viewed (
            viewedid INT(11) AUTO_INCREMENT PRIMARY KEY,
            gebruikerid INT(11),
            lodgetypeid INT(11),
            viewed_at DATETIME,

            CONSTRAINT fk_viewed_gebruiker
                FOREIGN KEY (gebruikerid)
                REFERENCES gebruiker(gebruikerid)
                ON DELETE CASCADE
                ON UPDATE CASCADE,

            CONSTRAINT fk_viewed_lodgetype
                FOREIGN KEY (lodgetypeid)
                REFERENCES lodgetype(typeid)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )
    ");
};
?>