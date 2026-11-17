<?php
require_once "config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $name     = trim($_POST["name"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (username, name, email, password, xp, level) 
                VALUES (:username, :name, :email, :password, 0, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $hashedPassword
        ]);

        header("Location: login.php?registered=1");
        exit();

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Användarnamnet eller e-postadressen används redan.";
        } else {
            $error = "Ett fel uppstod: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Trainify - Skapa konto</title>
</head>
<body>
    <h1>Skapa konto på Trainify</h1>

    <?php if ($error !== ""): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label>Användarnamn:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Namn:</label><br>
        <input type="text" name="name" required><br><br>

        <label>E-post:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Lösenord:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Registrera</button>
    </form>

    <p>Har du redan ett konto? <a href="login.php">Logga in här</a></p>
</body>
</html>