<?php
require_once "includes/sessioncheck.php";
require_once "config/db.php";

// 1. Hämta alla muskelgrupper till ett filter
$mgStmt = $pdo->query("SELECT muscle_group_id, muscle_group_name FROM muscle_group ORDER BY muscle_group_name ASC");
$muscleGroups = $mgStmt->fetchAll(PDO::FETCH_ASSOC);

// Kolla om användaren har valt en specifik muskelgrupp i filtret
$selectedGroup = isset($_GET['muscle_group']) && $_GET['muscle_group'] !== "" ? (int)$_GET['muscle_group'] : null;

// 2. Hämta övningar och slå ihop deras muskelgrupper med GROUP_CONCAT
$sql = "SELECT 
            e.exercise_id, 
            e.exercise_name, 
            GROUP_CONCAT(CONCAT(mg.muscle_group_name, ' (', emg.muscle_role, ')') SEPARATOR ', ') AS muscles
        FROM exercises e
        LEFT JOIN exercise_muscle_groups emg ON e.exercise_id = emg.exercise_id
        LEFT JOIN muscle_group mg ON emg.muscle_group_id = mg.muscle_group_id";

if ($selectedGroup !== null) {
    $sql .= " WHERE e.exercise_id IN (
                SELECT exercise_id FROM exercise_muscle_groups WHERE muscle_group_id = :mg_id
              )";
}

$sql .= " GROUP BY e.exercise_id, e.exercise_name ORDER BY e.exercise_name ASC";

$stmt = $pdo->prepare($sql);
if ($selectedGroup !== null) {
    $stmt->execute([':mg_id' => $selectedGroup]);
} else {
    $stmt->execute();
}
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Trainify - Övningsbibliotek</title>
</head>
<body>
    <h1>Övningsbibliotek</h1>
    <p><a href="dashboard.php">← Tillbaka till Dashboard</a></p>

    <form method="GET" action="exercises.php">
        <label>Filtrera efter muskelgrupp:</label>
        <select name="muscle_group" onchange="this.form.submit()">
            <option value="">Alla muskelgrupper</option>
            <?php foreach ($muscleGroups as $mg): ?>
                <option value="<?php echo (int)$mg['muscle_group_id']; ?>" <?php if ($selectedGroup === (int)$mg['muscle_group_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($mg['muscle_group_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <br>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Övning</th>
                <th>Muskelgrupper (Roll)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($exercises as $ex): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($ex['exercise_name']); ?></strong></td>
                    <td><?php echo $ex['muscles'] ? htmlspecialchars($ex['muscles']) : "Inga kopplade ännu"; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>