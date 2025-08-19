<?php
// index.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/GameController.php';

// Instantiate the controller
$game = new GameController($pdo);

// Handle "Start Game" action
if (isset($_POST['start_game'])) {
    $game->startGame();
}

// Handle "Move King" action
$moveMessage = '';
if (isset($_POST['king_move'])) {
    $newPos = strtoupper(trim($_POST['king_move']));
    if ($game->moveKing($newPos)) {
        $moveMessage = "King moved to $newPos";
    } else {
        $moveMessage = "Invalid move: $newPos";
    }
}

// Get current board state
$pieces = $game->getBoardState();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The King's Escape</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>The King's Escape</h1>

        <!-- Start Game -->
        <form method="post">
            <button type="submit" name="start_game">Start New Game</button>
        </form>

        <!-- Move King -->
        <form method="post" style="margin-top: 20px;">
            <label for="king_move">Move King (e.g., E2):</label>
            <input type="text" name="king_move" id="king_move" maxlength="2" required>
            <button type="submit">Move</button>
        </form>

        <div class="result">
            <?= htmlspecialchars($moveMessage) ?>
        </div>

        <!-- Display Board State -->
        <h2>Current Board</h2>
        <?php if (!empty($pieces)): ?>
            <table>
                <tr>
                    <th>Move #</th>
                    <th>Piece</th>
                    <th>Position</th>
                    <th>Enemy?</th>
                </tr>
                <?php foreach ($pieces as $p): ?>
                    <tr>
                        <td><?= $p['move_number'] ?></td>
                        <td><?= ucfirst($p['piece_type']) ?></td>
                        <td><?= $p['position_x'] . $p['position_y'] ?></td>
                        <td><?= $p['is_enemy'] ? 'Yes' : 'No' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No pieces on the board. Start a new game!</p>
        <?php endif; ?>
    </div>
</body>
</html>
