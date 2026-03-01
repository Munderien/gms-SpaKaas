<?php
/*  
lodgetypeid
naam
beschrijving
capaciteit
prijs
*/

return function (PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS lodgetype (
            typeid INT(11) AUTO_INCREMENT PRIMARY KEY,
            naam VARCHAR(30),
            beschrijving VARCHAR(80),
            capaciteit INT(11),
            prijs DECIMAL(20,0)
        )
    ");
};
?>