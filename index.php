<?php
// index.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/GameController2.php'; // correct controller

// Instantiate the controller
$game = new GamesController($pdo);

// Determine action from query parameter
$action = $_GET['action'] ?? 'home';

// Handle form submissions first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle "Start Game" action
    if (isset($_POST['start_game'])) {
        $game->startGame();
    }

    // Handle "Move King" action
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

// Determine action from query parameter
$action = $_GET['action'] ?? 'home';
$gameId = $_GET['game_id'] ?? 1; // default to 1 if not provided

switch ($action) {
    case 'play_game':
        $game = new GamesController($pdo, $gameId); // pass $gameId
        $pieces = $game->getBoardState();          // fetch pieces for this game
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

