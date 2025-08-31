<?php
// views/game.php



$controller = new GamesController($pdo, 1); // Player ID = 1
$moveMessage = "";

// Handle AJAX: find earliest escape
if (isset($_GET['action']) && $_GET['action'] === 'findEscape') {
    $result = $controller->findEscape($_GET['gameId'] ?? null);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}


// Handle King move submission
if (isset($_POST['submit_move'])) {
    $input = strtoupper(trim($_POST['king_move'])); // e.g., "E2"
    if (preg_match('/^[A-H][1-8]$/', $input)) {
        $x = ord($input[0]) - 64; // A=1
        $y = intval($input[1]);

        $result = $controller->processMove(['x' => $x, 'y' => $y]);
        $pieces = $result['pieces'];

        if ($result['win']) {
            $moveMessage = "🎉 Congratulations, you escaped!";
        } else if (!empty($result['error'])) {
            $moveMessage = "⚠️ " . $result['error'];
        } else {
            $moveMessage = "✅ King moved to $input (enemies spawned!)";
        }
    } else {
        $pieces = $controller->getBoardState();
        $moveMessage = "⚠️ Enter a valid move (A1–H8).";
    }
} else {
    $pieces = $controller->getBoardState();
}

// Optional: show earliest escape info
$escapeResult = $controller->findEscape();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The King's Escape</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <h2>The King's Escape 🏰</h2>

    <?php if (!empty($moveMessage)): ?>
        <div class="feedback <?= strpos($moveMessage, 'Congratulations') !== false ? 'win' : '' ?>">
            <?= htmlspecialchars($moveMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($escapeResult['message'])): ?>
        <div class="feedback">
            <?= htmlspecialchars($escapeResult['message']) ?>
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
    <?php foreach ($pieces as $piece): ?>
        <?php
        $left = ($piece['position_x'] - 1) * 60;
        $top  = (8 - $piece['position_y']) * 60;
        $symbols = ['King'=>'♔','Queen'=>'♕','Rook'=>'♖','Bishop'=>'♗','Knight'=>'♘','Pawn'=>'♙'];
        $symbol = $symbols[$piece['piece_type']] ?? '?';
        $class = $piece['is_enemy'] ? 'enemy' : 'king';
        ?>
        <div class="piece <?= $class ?>" style="left: <?= $left ?>px; top: <?= $top ?>px;">
            <?= $symbol ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Feedback placeholder -->
<div id="escape-feedback"></div>

<!-- Move Form -->
<form id="move-form" method="post">
    <label>Move King (A1–H8): </label>
    <input type="text" name="king_move" maxlength="2" required>
    <button type="submit" name="submit_move">Move</button>
</form>

<a href="index.php">← Back to Home</a>

<!-- JS to fetch earliest escape asynchronously -->
<script>
document.addEventListener('DOMContentLoaded', () => {
 // game.js
fetch('ajax_find_escape.php?gameId=1')
    .then(res => res.json())
    .then(data => {
        document.getElementById('escape-feedback').innerText = data.message;
    });


    // Optional: handle move submission via AJAX too for smooth UX
});
</script>

    <!-- Move Form -->
    <form method="post">
        <label>Move King (A1–H8): </label>
        <input type="text" name="king_move" maxlength="2" required>
        <button type="submit" name="submit_move">Move</button>
    </form>

    <a href="index.php">← Back to Home</a>
</body>
</html>
