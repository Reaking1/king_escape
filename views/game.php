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
 <link rel="stylesheet" href="assets/css/styles.css">

</head>
<body>
    <h2>The King's Escape 🏰</h2>

    <?php if (!empty($moveMessage)): ?>
        <div class="feedback"><?= htmlspecialchars($moveMessage) ?></div>
    <?php endif; ?>

    <div class="chessboard-wrapper">
        <div class="chessboard">
            <?php
            // Draw board
            for ($row = 8; $row >= 1; $row--) {
                for ($col = 1; $col <= 8; $col++) {
                    $colorClass = (($row + $col) % 2 === 0) ? 'white' : 'black';
                    $pieceText  = '';
                    $pieceClass = '';

                    if (!empty($pieces)) {
                        foreach ($pieces as $piece) {
                            if ((int)$piece['position_x'] === $col && (int)$piece['position_y'] === $row) {
                                $pieceText  = htmlspecialchars($piece['piece_type']);
                                $pieceClass = $piece['is_enemy'] ? 'enemy' : 'king';
                                break;
                            }
                        }
                    }

                    echo "<div class='cell {$colorClass} {$pieceClass}'>{$pieceText}</div>";
                }
            }
            ?>
        </div>

        <!-- File labels A–H -->
        <?php
        $files = ['A','B','C','D','E','F','G','H'];
        foreach ($files as $i => $file): ?>
            <div class="file-label" style="left: <?= $i*60 ?>px;"><?= $file ?></div>
        <?php endforeach; ?>

        <!-- Rank labels 1–8 -->
        <?php for ($i=0; $i<8; $i++): ?>
            <div class="rank-label" style="top: <?= $i*60 ?>px;"><?= 8-$i ?></div>
        <?php endfor; ?>
    </div>

    <form method="post">
        <label>Move King (e.g., E2): </label>
        <input type="text" name="king_move" maxlength="2" required>
        <button type="submit" name="submit_move">Move</button>
    </form>

    <a href="index.php">← Back to Home</a>
</body>
</html>
