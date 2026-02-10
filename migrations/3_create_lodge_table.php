<?php
/*  
lodgeid
lodgetypeid
huisnummer
status
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS lodge (
            lodgeid INT(11) AUTO_INCREMENT PRIMARY KEY,
            lodgetypeid INT(11),
            huisnummer VARCHAR(20),
            status VARCHAR(30),

            CONSTRAINT fk_lodge_lodgetype
                FOREIGN KEY (lodgetypeid)
                REFERENCES lodgetype(lodgetypeid)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )
    ");
};
?>