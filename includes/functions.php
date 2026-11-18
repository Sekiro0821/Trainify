<?php

// 1. Räkna ut vilken level man ska ha baserat på total XP
function calculateLevel($xp) {
    // Varje 500 XP ger en ny level (0-499 = Level 1, 500-999 = Level 2, osv.)
    return floor($xp / 500) + 1;
}

// 2. Ge användaren XP och uppdatera deras level i databasen
function addXP($pdo, $userId, $earnedXP) {
    // Hämta användarens nuvarande XP
    $sql = "SELECT xp FROM users WHERE user_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $newXP = $user['xp'] + $earnedXP;
        $newLevel = calculateLevel($newXP);

        // Spara ny XP och ny level i users-tabellen
        $updateSql = "UPDATE users SET xp = :xp, level = :level WHERE user_id = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            ':xp'    => $newXP,
            ':level' => $newLevel,
            ':id'    => $userId
        ]);

        return [
            'new_xp'    => $newXP,
            'new_level' => $newLevel
        ];
    }
    return null;
}

// 3. Lås upp en achievement för en användare (om de inte redan har den)
function unlockAchievement($pdo, $userId, $achievementId) {
    // Kolla först om användaren redan har låst upp just denna achievement
    $checkSql = "SELECT * FROM user_achievements WHERE user_id = :user_id AND achievement_id = :achievement_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([
        ':user_id'        => $userId,
        ':achievement_id' => $achievementId
    ]);

    // Om raden INTE finns sedan tidigare, lås upp den!
    if ($checkStmt->rowCount() === 0) {
        // Lägg till i user_achievements
        $insertSql = "INSERT INTO user_achievements (user_id, achievement_id, unlocked_at) 
                      VALUES (:user_id, :achievement_id, NOW())";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            ':user_id'        => $userId,
            ':achievement_id' => $achievementId
        ]);

        // Hämta hur mycket bonus-XP denna achievement ger
        $xpSql = "SELECT achievements_name, xp_reward FROM achievements WHERE achievement_id = :achievement_id";
        $xpStmt = $pdo->prepare($xpSql);
        $xpStmt->execute([':achievement_id' => $achievementId]);
        $achievement = $xpStmt->fetch(PDO::FETCH_ASSOC);

        if ($achievement && $achievement['xp_reward'] > 0) {
            addXP($pdo, $userId, $achievement['xp_reward']);
        }

        return $achievement['achievements_name']; // Returnerar namnet så vi kan visa en notis!
    }

    return false; // Användaren hade redan denna achievement
}
?>