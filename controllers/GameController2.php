<?php
// controllers/GamesController.php

require_once __DIR__ . '/../models/Chessboard.php';

class GamesController {
    private $chessboard;
    private $moveNumber = 0;
    private $gameId;

public function __construct($db, $gameId = 1) {
    $this->chessboard = new Chessboard($db);

    // Ensure game exists and get the actual ID
    $this->gameId = $this->chessboard->ensureGameExists($gameId);

    // Start game only if it's a new game
    $pieces = $this->chessboard->getAllPieces($this->gameId);
    if (empty($pieces)) {
        $this->startGame();
    }
}



    // Show chessboard
    public function index() {
        $pieces = $this->chessboard->getAllPieces($this->gameId);

        // If no pieces yet, start a new game
        if (empty($pieces)) {
            $this->startGame();
            $pieces = $this->chessboard->getAllPieces($this->gameId);
        }

        include __DIR__ . '/../views/game.php';
    }

    // Return board state as JSON (AJAX)
    public function getAllPieces() {
        $pieces = $this->chessboard->getAllPieces($this->gameId);
        header('Content-Type: application/json');
        echo json_encode($pieces);
    }

    // Reset the board and start fresh
    public function reset() {
        $this->chessboard->clearBoard($this->gameId);
        $this->moveNumber = 0;
    }

    // Start a new game with King + Enemies
public function startGame() {
    // 1. Ensure a game exists for this player
    $gameId = $this->gameId;

    // 2. Place the King at starting position
    $kingId = $this->chessboard->getPieceTypeId('King');
    $this->chessboard->addPieceToGame($gameId, $kingId, 1, 1);

    // 3. Spawn some enemies
    $enemyData = [
        ['name' => 'Pawn', 'x' => 2, 'y' => 2],
        ['name' => 'Knight', 'x' => 3, 'y' => 3]
    ];

    foreach ($enemyData as $enemy) {
        $enemyId = $this->chessboard->getPieceTypeId($enemy['name']);
        $this->chessboard->addPieceToGame($gameId, $enemyId, $enemy['x'], $enemy['y']);
    }
}


    // Move the King
    public function moveKing($x, $y) {
        $this->moveNumber++;

        // Check if occupied
        if ($this->chessboard->isOccupied($this->gameId, $x, $y)) {
            return false;
        }

        return $this->chessboard->moveKing($this->gameId, $x, $y, $this->moveNumber);
    }

// In GamesController
public function getBoardState() {
    return $this->chessboard->getAllPieces($this->gameId);
}


    // Process a turn (king moves + new enemies)
    public function processMove($kingMove, $enemyArray) {
        $this->moveNumber++;

        // Move King
        $x = $kingMove['x'];
        $y = $kingMove['y'];
        $this->moveKing($x, $y);

        // Spawn new enemies
        foreach ($enemyArray as $enemy) {
            $this->chessboard->placePiece($this->gameId, $enemy['type'], true, $enemy['x'], $enemy['y']);
        }

        // Return updated board
        return $this->chessboard->getAllPieces($this->gameId);
    }

    
}
