<?php
require_once "includes/sessioncheck.php";
require_once "config/db.php";

$userId = $_SESSION["user_id"];

// Hämta alla achievements och kolla om användaren har låst upp dem
$sql = "SELECT 
            a.achievement_id,
            a.achievements_name,
            a.description,
            a.xp_reward,
            ua.unlocked_at
        FROM achievements a
        LEFT JOIN user_achievements ua 
            ON a.achievement_id = ua.achievement_id 
            AND ua.user_id = :user_id
        ORDER BY a.achievement_id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Trainify - Achievements</title>
</head>
<body>
    <h1>Mina Achievements</h1>
    <p><a href="dashboard.php">← Tillbaka till Dashboard</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Status</th>
                <th>Namn</th>
                <th>Beskrivning</th>
                <th>Belöning</th>
                <th>Upplåst</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($achievements as $ach): ?>
                <tr>
                    <td>
                        <?php if ($ach['unlocked_at'] !== null): ?>
                            🏆 Upplåst
                        <?php else: ?>
                            🔒 Låst
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($ach['achievements_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($ach['description']); ?></td>
                    <td>+<?php echo (int)$ach['xp_reward']; ?> XP</td>
                    <td>
                        <?php 
                            echo $ach['unlocked_at'] !== null 
                                ? htmlspecialchars($ach['unlocked_at']) 
                                : "-"; 
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>