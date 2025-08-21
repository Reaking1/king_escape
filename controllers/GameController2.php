<?php
// controllers/GamesController.php

require_once __DIR__ . '/../models/Chessboard.php';

class GamesController {
    private $chessboard;
    private $moveNumber = 0; // track current move

    public function __construct($pdo) {
        $this->chessboard = new Chessboard($pdo);
    }

    // Show chessboard
    public function index() {
        $pieces = $this->chessboard->getAllPieces();
        include __DIR__ . '/../views/game.php'; // adjusted path
    }

   public function reset() {
    // Clear the board
    $this->chessboard->resetBoard();

    // Reset move number
    $this->moveNumber = 0;

    // Place single king at starting position
    $this->chessboard->moveKing($this->moveNumber, 1, 1);

    // Optionally redirect
    header("Location: index.php?action=play_game");
    exit;
}


    // Start a new game (King + some enemies)
public function startGame() {
    $this->reset(); // clear board first
    $this->moveNumber = 0;

    // Place King at (1,1) using moveKing (prevents duplicates)
    $this->chessboard->moveKing($this->moveNumber, 1, 1);

    // Place some enemies
    $enemies = [
        ['type' => 'Pawn', 'x' => 2, 'y' => 2],
        ['type' => 'Knight', 'x' => 3, 'y' => 3]
    ];

    $this->chessboard->spawnEnemies($this->moveNumber, $enemies);
}


    // Move the King
    public function moveKing($x, $y) {
        $this->moveNumber++;

        // Check if target square is occupied
        if ($this->chessboard->isOccupied($this->moveNumber, $x, $y)) {
            return false; // invalid move
        }

        return $this->chessboard->moveKing($this->moveNumber, $x, $y);
    }

    // Get current board state
    public function getBoardState() {
        return $this->chessboard->getAllPieces();
    }

public function processMove($kingMove, $enemyArray) {
    $this->moveNumber++;

    // Move king
    $x = $kingMove['x'];
    $y = $kingMove['y'];
    $this->moveKing($x, $y);

    // Spawn new enemies
    $this->chessboard->spawnEnemies($this->moveNumber, $enemyArray);

    // Return current board state
    return $this->chessboard->getPiecesByMove($this->moveNumber);
}

}
