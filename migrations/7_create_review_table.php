<?php
/*  
reviewid
gebruikerid
datum
rating
opmerking
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS review (
            reviewid INT(11) AUTO_INCREMENT PRIMARY KEY,
            gebruikerid INT(11),
            datum DATETIME,
            rating DECIMAL(10,0),
            opmerking VARCHAR(80),

            CONSTRAINT fk_review_gebruiker
                FOREIGN KEY (gebruikerid)
                REFERENCES gebruiker(gebruikerid)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )
    ");
};
?>