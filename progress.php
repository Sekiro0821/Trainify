<?php
require_once "includes/sessioncheck.php";
require_once "config/db.php";

$userId = $_SESSION["user_id"];

// 1. Hämta användarens Personbästa (tyngsta vikt per övning)
$sqlPB = "SELECT 
            e.exercise_name,
            MAX(s.weight) AS max_weight,
            MAX(s.reps) AS max_reps
          FROM sets s
          INNER JOIN workout_exercises we ON s.workout_exercise_id = we.workoutexercise_id
          INNER JOIN workouts w ON we.workout_id = w.workout_id
          INNER JOIN exercises e ON we.exercise_id = e.exercise_id
          WHERE w.user_id = :user_id
          GROUP BY e.exercise_id, e.exercise_name
          ORDER BY max_weight DESC";

$stmtPB = $pdo->prepare($sqlPB);
$stmtPB->execute([':user_id' => $userId]);
$personalBests = $stmtPB->fetchAll(PDO::FETCH_ASSOC);

// 2. Hämta hela träningshistoriken (senaste passet överst)
$sqlHistory = "SELECT 
                w.workout_id,
                w.`date`,
                w.duration,
                e.exercise_name,
                s.set_number,
                s.weight,
                s.reps
               FROM workouts w
               INNER JOIN workout_exercises we ON w.workout_id = we.workout_id
               INNER JOIN exercises e ON we.exercise_id = e.exercise_id
               INNER JOIN sets s ON we.workoutexercise_id = s.workout_exercise_id
               WHERE w.user_id = :user_id
               ORDER BY w.`date` DESC, w.workout_id DESC";

$stmtHistory = $pdo->prepare($sqlHistory);
$stmtHistory->execute([':user_id' => $userId]);
$history = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Trainify - Min Utveckling</title>
</head>
<body>
    <h1>Min Utveckling</h1>
    <p><a href="dashboard.php">← Tillbaka till Dashboard</a></p>

    <h2>🏆 Personbästa (PB)</h2>
    <?php if (empty($personalBests)): ?>
        <p>Du har inte registrerat några träningspass ännu.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Övning</th>
                    <th>Tyngsta vikt</th>
                    <th>Flest reps</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($personalBests as $pb): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($pb['exercise_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($pb['max_weight']); ?> kg</td>
                        <td><?php echo (int)$pb['max_reps']; ?> reps</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <hr>

    <h2>📅 Träningshistorik</h2>
    <?php if (empty($history)): ?>
        <p>Ingen historik att visa ännu. <a href="workouts.php">Registrera ditt första pass!</a></p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Övning</th>
                    <th>Vikt</th>
                    <th>Reps</th>
                    <th>Tid</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['exercise_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['weight']); ?> kg</td>
                        <td><?php echo (int)$row['reps']; ?></td>
                        <td><?php echo (int)$row['duration']; ?> min</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>