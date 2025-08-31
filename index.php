<?php
// index.php

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/GameController2.php'; // correct controller

// ---------------------------
// 1. Get player ID
// ---------------------------
$playerId = $_SESSION['player_id'] ?? 1; // fallback to 1 if no session

// ---------------------------
// 2. Get game ID from query, or default to current player's game
// ---------------------------
$gameId = $_GET['game_id'] ?? null;

// ---------------------------
// 3. Instantiate controller
// ---------------------------
$game = new GamesController($pdo, $playerId); // pass playerId to constructor

// Use existing game if specified

// ---------------------------
// 4. Handle form submissions
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Start Game
    if (isset($_POST['start_game'])) {
        $game->startGame();
    }

    // Move King
    if (isset($_POST['king_move'])) {
        $newPos = strtoupper(trim($_POST['king_move']));
        $cols = ['A'=>1,'B'=>2,'C'=>3,'D'=>4,'E'=>5,'F'=>6,'G'=>7,'H'=>8];
        $col = substr($newPos,0,1);
        $row = intval(substr($newPos,1));

        if (isset($cols[$col])) {
            $x = $cols[$col];
            $y = $row;
            $moveMessage = $game->moveKing($x, $y)
                ? "King moved to $newPos"
                : "Invalid move: $newPos";
        } else {
            $moveMessage = "Invalid move: $newPos";
        }
    }
}

// ---------------------------
// 5. Determine action
// ---------------------------
$action = $_GET['action'] ?? 'home';

switch ($action) {
    case 'play_game':
        // Fetch current board state
        $pieces = $game->getBoardState();

        include __DIR__ . '/views/partials/header.php';
        include __DIR__ . '/views/game.php';
        include __DIR__ . '/views/partials/footer.php';
        break;

    default:
        include __DIR__ . '/views/partials/header.php';
        include __DIR__ . '/views/home.php';
        include __DIR__ . '/views/partials/footer.php';
        break;
}
