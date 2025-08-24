<?php
// views/home.php
?>

<main style="text-align:center; margin-top:50px;">
    <h2>Welcome to The King's Escape 🏰</h2>
    <p>Defend the King and defeat the enemies!</p>

    <form method="get" action="index.php">
        <input type="hidden" name="action" value="play_game">
        <button type="submit" style="padding:10px 20px; font-size:16px;">Play Game</button>
    </form>

    <h2>Available Games</h2>
<ul>
<?php
$stmt = $pdo->query("SELECT id, result FROM games ORDER BY id ASC");
$allGames = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($allGames as $g):
?>
    <li>
        Game #<?= $g['id'] ?> — Status: <?= htmlspecialchars($g['result']) ?>
        <a href="index.php?action=play_game&game_id=<?= $g['id'] ?>">Play</a>
    </li>
<?php endforeach; ?>
</ul>

</main>
