<?php
session_start();
require_once "config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT user_id, username, password FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["username"] = $user["username"];

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Fel e-postadress eller lösenord.";
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Trainify - Logga in</title>
</head>
<body>
    <h1>Logga in på Trainify</h1>

    <?php if (isset($_GET["registered"])): ?>
        <p style="color: green;">Kontot är skapat! Logga in nedan.</p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>E-post:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Lösenord:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Logga in</button>
    </form>

    <p>Har du inget konto? <a href="register.php">Registrera dig här</a></p>
</body>
</html>