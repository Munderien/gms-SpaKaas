<?php
session_start();
if(isset($_SESSION['gebruikerId'])){
        echo "<script>window.location.href='home.php'</script>";

}

?>
<link rel='stylesheet' href="style/login_regis_pagina.css">
<Form action="inlogFunctie.php" method="post">
<div class="container">
  <div class="inlogkaart">
    <h1>Inloggen</h1>
    <table>
        <tr>
        <td><p>Email:</p></td>
        <td><input class="inlogMail"name="inlogMail" type="text"></input></td>
</tr>
<tr>
    
        <td><p>Wachtwoord:</p></td>
        <td><input class="inlogPassword" name="inlogPassword"type="password"></input></td>
</tr>
<tr>
    <td></td><td><button class="inlogButton">Login</button><td>
</table>
  </div>
</form>
  <form action="registratieFunctie.php" method="post">
  <div class="registratiekaart">
    <h1>Registreren</h1>
    <table>
        <tr>
        <td><p>Email:</p></td>
        <td><input class="registratieMail" name="mail" type="text"></input></td>
</tr>
<tr>
        <td><p>Wachtwoord:</p></td>
        <td><input class="registratieWachtwoord" name="password" type="password"></input></td>
</tr>
<tr>
        <td><p>Adress:</p></td>
        <td><input class="registratieAdress"name="adress" type="text"></input></td>
</tr>
<tr>
        <td><p>Telefoon:</p></td>
        <td><input class="registratieTelNummer"name="phone" type="text"></input></td>
</tr>
<tr>
        <td><p>naam:</p></td>
        <td><input class="registratieENaam" name="name"type="text"></input></td>
</tr>
<tr>
        <td><p>postcode:</p></td>
        <td><input class="registratiePostcode" name="postcode"type="text"></input></td>
</tr>
<tr>
        <td><p>plaats:</p></td>
        <td><input class="registratiePlaats" name="Plaats"type="text"></input></td>
</tr>

<tr>
    <td></td><td><button class="registratieButton">Registreer</button><td>
</tr>
</div>
</form>
