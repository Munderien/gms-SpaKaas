<?php
/*  
weekschemaid
gebruikerid
dag_van_week
starttijd
eindtijd
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS weekschema (
            weekschemaid INT(11) AUTO_INCREMENT PRIMARY KEY,
            gebruikerid INT(11),
            dag_van_week INT(11),
            starttijd TIME,
            eindtijd TIME,

            CONSTRAINT fk_weekschema_gebruiker
                FOREIGN KEY (gebruikerid)
                REFERENCES gebruiker(gebruikerid)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        )
    ");
};
?>