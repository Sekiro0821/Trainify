<?php
require_once "includes/sessioncheck.php";
require_once "config/db.php";
require_once "includes/functions.php";

$userId = $_SESSION["user_id"];
$successMessage = "";
$unlockedList = [];

// 1. Hämta alla övningar till rullgardinsmenyn
$sql = "SELECT exercise_id, exercise_name FROM exercises ORDER BY exercise_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Hantera formuläret när användaren skickar in ett träningspass
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $exerciseId = (int)$_POST["exercise_id"];
    $weight     = (float)$_POST["weight"];
    $reps       = (int)$_POST["reps"];
    $duration   = (int)$_POST["duration"];

    // Räkna ut XP (50 grund-XP + vikt * reps)
    $earnedXP = 50 + (int)($weight * $reps);

    try {
        $pdo->beginTransaction();

        // Steg A: Spara själva passet i workouts
        $sqlWorkout = "INSERT INTO workouts (user_id, `date`, duration) VALUES (:user_id, :date, :duration)";
        $stmtWorkout = $pdo->prepare($sqlWorkout);
        $stmtWorkout->execute([
            ':user_id'  => $userId,
            ':date'     => date('Y-m-d'),
            ':duration' => $duration
        ]);
        $workoutId = $pdo->lastInsertId();

        // Steg B: Koppla övningen till passet i workout_exercises
        $sqlWe = "INSERT INTO workout_exercises (workout_id, exercise_id) VALUES (:workout_id, :exercise_id)";
        $stmtWe = $pdo->prepare($sqlWe);
        $stmtWe->execute([
            ':workout_id'  => $workoutId,
            ':exercise_id' => $exerciseId
        ]);
        $workoutExerciseId = $pdo->lastInsertId();

        // Steg C: Spara set, vikt och reps i sets
        $sqlSet = "INSERT INTO sets (workout_exercise_id, reps, set_number, weight) 
                   VALUES (:workout_exercise_id, :reps, :set_number, :weight)";
        $stmtSet = $pdo->prepare($sqlSet);
        $stmtSet->execute([
            ':workout_exercise_id' => $workoutExerciseId,
            ':reps'                => $reps,
            ':set_number'          => 1,
            ':weight'              => $weight
        ]);

        // Bekräfta att allt sparades i databasen
        $pdo->commit();

        // Ge användaren XP och uppdatera level (från functions.php)
        $xpResult = addXP($pdo, $userId, $earnedXP);

        // Kolla Achievement 1: "Första passet!" (ID 1)
        $ach1 = unlockAchievement($pdo, $userId, 1);
        if ($ach1) {
            $unlockedList[] = $ach1;
        }

        // Kolla Achievement 2: "Tungviktare" (ID 2) om vikten är 100 kg eller mer
        if ($weight >= 100) {
            $ach2 = unlockAchievement($pdo, $userId, 2);
            if ($ach2) {
                $unlockedList[] = $ach2;
            }
        }

        $successMessage = "Passet är sparat! Du tjänade " . $earnedXP . " XP.";

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Databasfel: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Trainify - Registrera pass</title>
</head>
<body>
    <h1>Registrera träningspass</h1>
    <p><a href="dashboard.php">← Tillbaka till Dashboard</a></p>

    <?php if ($successMessage !== ""): ?>
        <div style="border: 2px solid green; padding: 10px; margin-bottom: 15px;">
            <strong><?php echo htmlspecialchars($successMessage); ?></strong>

            <?php if (!empty($unlockedList)): ?>
                <p>🏆 <strong>Ny Achievement upplåst:</strong></p>
                <ul>
                    <?php foreach ($unlockedList as $achName): ?>
                        <li><?php echo htmlspecialchars($achName); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="workouts.php">
        <label>Välj övning:</label><br>
        <select name="exercise_id" required>
            <?php foreach ($exercises as $exercise): ?>
                <option value="<?php echo (int)$exercise['exercise_id']; ?>">
                    <?php echo htmlspecialchars($exercise['exercise_name']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Vikt (kg):</label><br>
        <input type="number" step="0.5" name="weight" min="0" required><br><br>

        <label>Antal reps:</label><br>
        <input type="number" name="reps" min="1" required><br><br>

        <label>Tid för passet (minuter):</label><br>
        <input type="number" name="duration" min="1" value="45" required><br><br>

        <button type="submit">Spara träningspass</button>
    </form>
</body>
</html>