<?php
// views/game.php
// expects $pieces = array of current move pieces from controller
// optional $moveMessage for feedback
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The King's Escape</title>
    <link rel="stylesheet" href="/king_escape/assets/css/styles.css">

</head>
<body>
    <h2>The King's Escape 🏰</h2>

     <?php if (!empty($moveMessage)): ?>
    <div class="feedback"><?= htmlspecialchars($moveMessage) ?></div>
<?php endif; ?>
    <div class="chessboard">
        <?php
        for ($row = 1; $row <= 8; $row++) {
            for ($col = 1; $col <= 8; $col++) {
                $colorClass = (($row + $col) % 2 === 0) ? 'white' : 'black';
                $pieceText  = '';
                $pieceClass = '';

                if (!empty($pieces)) {
                    foreach ($pieces as $piece) {
                        if ((int)$piece['position_x'] === $col && (int)$piece['position_y'] === $row) {
                            $pieceText  = htmlspecialchars($piece['piece_type']);
                            $pieceClass = !empty($piece['is_enemy']) ? 'enemy' : 'king';
                            break;
                        }
                    }
                }

                echo "<div class='cell {$colorClass} {$pieceClass}'>{$pieceText}</div>";
            }
        }
        ?>
    </div>

    <form method="post">
        <label>Move King (e.g., E2): </label>
        <input type="text" name="king_move" maxlength="2" required>
        <button type="submit" name="submit_move">Move</button>
    </form>

    <div style="text-align:center; margin-top:15px;">
        <a href="../index.php">← Back to Home</a>
    </div>
  

</body>
</html>
