<?php
require '../inlog/config.php';
session_start();

// Alleen managers mogen deze pagina gebruiken (rol 3)
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
    die('Geen toegang');
}

// Rol aanpassen als formulier is verstuurd
if (!empty($_POST['gebruikerid']) && isset($_POST['rol'])) {
    $gebruikerid = (int)$_POST['gebruikerid'];
    $nieuweRol = (int)$_POST['rol'];

    $stmt = $db->prepare("UPDATE gebruiker SET rol = ? WHERE gebruikerid = ?");
    $stmt->execute([$nieuweRol, $gebruikerid]);
}

// Alle gebruikers ophalen
$stmt = $db->query("SELECT gebruikerid, email, naam, rol FROM gebruiker ORDER BY gebruikerid");
$gebruikers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Gebruikersrollen beheren</title>
</head>
<body>
<?php include '../navbar.php'; ?>
<h1>Gebruikersrollen beheren</h1>

<table border="1" cellpadding="4" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Naam</th>
        <th>Email</th>
        <th>Huidige rol</th>
        <th>Nieuwe rol</th>
    </tr>
    <?php foreach ($gebruikers as $gebruiker): ?>
        <tr>
            <td><?php echo $gebruiker['gebruikerid']; ?></td>
            <td><?php echo htmlspecialchars($gebruiker['naam']); ?></td>
            <td><?php echo htmlspecialchars($gebruiker['email']); ?></td>
            <td><?php echo $gebruiker['rol']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="gebruikerid" value="<?php echo $gebruiker['gebruikerid']; ?>">
                    <select name="rol">
                        <option value="0" <?php if ($gebruiker['rol'] == 0) echo 'selected'; ?>>Klant</option>
                        <option value="1" <?php if ($gebruiker['rol'] == 1) echo 'selected'; ?>>Baliemedewerker</option>
                        <option value="2" <?php if ($gebruiker['rol'] == 2) echo 'selected'; ?>>Onderhoudsmonteur</option>
                        <option value="3" <?php if ($gebruiker['rol'] == 3) echo 'selected'; ?>>Manager</option>
                    </select>
                    <button type="submit">Opslaan</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
