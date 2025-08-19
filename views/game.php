<?php
// views/games.php

// $chessboardData should be provided by GameController
// Example: [
//   ['id'=>1, 'piece_name'=>'King', 'x'=>1, 'y'=>1, 'is_enemy'=>false],
//   ['id'=>2, 'piece_name'=>'Enemy Rook', 'x'=>8, 'y'=>8, 'is_enemy'=>true]
// ]
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The King's Escape</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .chessboard {
            display: grid;
            grid-template-columns: repeat(8, 60px);
            grid-template-rows: repeat(8, 60px);
            gap: 2px;
            margin: 20px auto;
            width: 484px; /* 8*60 + 7 gaps */
            border: 2px solid #333;
        }
        .cell {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        .cell.white { background: #eee; }
        .cell.black { background: #666; color: #fff; }
        .king { color: gold; }
        .enemy { color: red; }
    </style>
</head>
<body>
    <h2>The King's Escape 🏰</h2>

    <div class="chessboard">
        <?php
        // Generate chessboard 8x8
        for ($row = 1; $row <= 8; $row++) {
            for ($col = 1; $col <= 8; $col++) {
                // Alternate colors
                $colorClass = ($row + $col) % 2 == 0 ? 'white' : 'black';

                // Check if a piece is on this cell
                $pieceText = '';
                $pieceClass = '';
                foreach ($chessboardData as $piece) {
                    if ($piece['position_x'] == $col && $piece['position_y'] == $row) {
                        $pieceText = $piece['piece_name'];
                        $pieceClass = $piece['is_enemy'] ? 'enemy' : 'king';
                        break;
                    }
                }

                echo "<div class='cell {$colorClass} {$pieceClass}'>{$pieceText}</div>";
            }
        }
        ?>
    </div>

    <a href="../index.php">← Back to Home</a>
</body>
</html>
