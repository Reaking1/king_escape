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
</main>
