<!DOCTYPE html>
<html>
<head>
    <title>Send Mail</title>
</head>
<body>
    <h2>Send an Email</h2>
    <form action="emailFuncties.php" method="post">
        <label for="to">To:</label>
        <input type="email" id="to" name="to" required><br><br>

        <label for="subject">Subject:</label>
        <input type="text" id="subject" name="subject" required><br><br>

        <label for="message">Message:</label><br>
        <textarea id="message" name="message" rows="6" cols="40" required></textarea><br><br>

        <input type="hidden" name="loggedin" value="1">

        <input type="submit" value="Send Email">
    </form>
</body>
</html>
