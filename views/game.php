<?php
// views/game.php
// expects $pieces from controller

require_once __DIR__ . '/../controllers/GameController2.php';
require_once __DIR__ . '/../init_db.php'; // adjust if you bootstrap db differently

// Create controller instance
$controller = new GamesController($pdo, 1);
$moveMessage = "";

if ($_GET['action'] === 'findEscape') {
    $gameId = $_GET['gameId'] ?? 1;
    $result = $controller->findEscape($gameId);
    echo json_encode($result);
}
// Handle move form
if (isset($_POST['submit_move'])) {
    $input = strtoupper(trim($_POST['king_move'])); // e.g., "E2"

    if (preg_match('/^[A-H][1-8]$/', $input)) {
        $x = ord($input[0]) - 64;  // A=1, B=2...
        $y = intval($input[1]);

        $result = $controller->processMove(['x' => $x, 'y' => $y]);
        $pieces = $result['pieces'];

        if ($result['win']) {
            $moveMessage = "🎉 Congratulations, you escaped!";
        } else {
            $moveMessage = "✅ King moved to $input (enemies spawned!)";
        }
    } else {
        $moveMessage = "⚠️ Enter a valid move (A1–H8).";
    }
} else {
    $pieces = $controller->getBoardState();
}

$escapeAt = $controller->checkEarliestEscape();
if ($escapeAt > 0) {
    echo "<div class='feedback'>🎉 Earliest escape possible at move $escapeAt!</div>";
}

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
    <div class="feedback <?= strpos($moveMessage, 'Congratulations') !== false ? 'win' : '' ?>">
        <?= htmlspecialchars($moveMessage) ?>
    </div>
<?php endif; ?>


    <div class="chessboard-wrapper">
        <div class="chessboard">
            <?php
            // Draw 8x8 grid
            for ($row = 8; $row >= 1; $row--) {
                for ($col = 1; $col <= 8; $col++) {
                    $colorClass = (($row + $col) % 2 === 0) ? 'white' : 'black';
                    echo "<div class='cell {$colorClass}'></div>";
                }
            }
            ?>
        </div>

        <!-- Place pieces -->
        <?php if (!empty($pieces)): ?>
            <?php foreach ($pieces as $piece): ?>
                <?php
                $fileIndex = intval($piece['position_x']) - 1; // 1–8 → 0–7
                $rankIndex = 8 - intval($piece['position_y']); // invert Y
                $left = $fileIndex * 60;
                $top  = $rankIndex * 60;

                // Unicode symbols
                $symbols = [
                    'King'   => '♔',
                    'Queen'  => '♕',
                    'Rook'   => '♖',
                    'Bishop' => '♗',
                    'Knight' => '♘',
                    'Pawn'   => '♙'
                ];
                $symbol = $symbols[$piece['piece_type']] ?? '?';

                // CSS class for enemy / king
                $class = $piece['is_enemy'] ? 'enemy' : 'king';
                ?>
                <div class="piece <?= $class ?>" style="left: <?= $left ?>px; top: <?= $top ?>px;">
                    <?= $symbol ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- File labels (A–H) -->
        <?php
        $files = ['A','B','C','D','E','F','G','H'];
        foreach ($files as $i => $file): ?>
            <div class="file-label" style="left: <?= $i*60 ?>px;"><?= $file ?></div>
        <?php endforeach; ?>

        <!-- Rank labels (1–8) -->
        <?php for ($i=0; $i<8; $i++): ?>
            <div class="rank-label" style="top: <?= $i*60 ?>px;"><?= 8-$i ?></div>
        <?php endfor; ?>
    </div>

    <!-- Move Form -->
    <form method="post">
        <label>Move King (A1–H8): </label>
        <input type="text" name="king_move" maxlength="2" required>
        <button type="submit" name="submit_move">Move</button>
    </form>

    <a href="index.php">← Back to Home</a>
</body>
</html>
