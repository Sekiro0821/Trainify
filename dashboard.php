<?php
require_once "includes/sessioncheck.php";
require_once "config/db.php";

$userId = $_SESSION["user_id"];

// 1. Hämta användarens profil, XP och level
$sql = "SELECT username, name, xp, level FROM users WHERE user_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Räkna hur många träningspass användaren har gjort totalt
$sqlWorkouts = "SELECT COUNT(*) AS total_workouts FROM workouts WHERE user_id = :id";
$stmtWorkouts = $pdo->prepare($sqlWorkouts);
$stmtWorkouts->execute([':id' => $userId]);
$workoutStats = $stmtWorkouts->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Trainify - Dashboard</title>
</head>
<body>
    <h1>Välkommen tillbaka, <?php echo htmlspecialchars($user["name"]); ?>!</h1>
    <p>Användarnamn: <strong><?php echo htmlspecialchars($user["username"]); ?></strong></p>

    <hr>

    <h2>Din Spelarprofil</h2>
    <ul>
        <li><strong>Level:</strong> <?php echo (int)$user["level"]; ?></li>
        <li><strong>Total XP:</strong> <?php echo (int)$user["xp"]; ?> XP</li>
        <li><strong>Genomförda träningspass:</strong> <?php echo (int)$workoutStats["total_workouts"]; ?></li>
    </ul>

    <hr>

    <h2>Meny</h2>
    <ul>
        <li><a href="workouts.php">Registrera nytt träningspass</a></li>
        <li><a href="exercises.php">Visa övningar</a></li>
        <li><a href="progress.php">Min utveckling</a></li>
        <li><a href="achievements.php">Mina Achievements</a></li>
        <li><a href="logout.php">Logga ut</a></li>
    </ul>
</body>
</html>