<?php
try{
    $db= new PDO("mysql:host=localhost;dbname=dms-spakaas","root","");
}
catch(PDOException $melding)
{
    echo $melding->getmessage();
}
?>
