<?php
// index.php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/GameController2.php';

// ---------------------------
// 1. Get player ID
// ---------------------------
$playerId = $_SESSION['player_id'] ?? 1;

// ---------------------------
// 2. Instantiate controller
// ---------------------------
$gameController = new GamesController($pdo, $playerId);

// ---------------------------
// 3. Handle form submissions
// ---------------------------
$moveMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Start Game
    if (isset($_POST['start_game'])) {
        $gameController->startGame();
    }

    // Move King
    if (isset($_POST['king_move'])) {
        $input = strtoupper(trim($_POST['king_move'])); // e.g., "E2"
        if (preg_match('/^[A-H][1-8]$/', $input)) {
            $x = ord($input[0]) - 64; // A=1, B=2...
            $y = intval($input[1]);

            $result = $gameController->processMove(['x' => $x, 'y' => $y]);
            $pieces = $result['pieces'];

            if ($result['win']) {
                $moveMessage = "🎉 Congratulations, you escaped!";
            } else {
                $moveMessage = "✅ King moved to $input (enemies spawned!)";
            }
        } else {
            $moveMessage = "⚠️ Enter a valid move (A1–H8).";
            $pieces = $gameController->getBoardState();
        }
    }
}

// ---------------------------
// 4. Determine action
// ---------------------------
$action = $_GET['action'] ?? 'home';
include __DIR__ . '/views/partials/header.php';

if ($action === 'play_game') {
    $pieces = $gameController->getPieces(); // only fetch board pieces
    include __DIR__ . '/views/game.php';
} else {
    include __DIR__ . '/views/home.php';
}

include __DIR__ . '/views/partials/footer.php';
