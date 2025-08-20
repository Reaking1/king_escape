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
        include __DIR__ . '/../views/games.php'; // adjusted path
    }

    // Reset the chessboard
    public function reset() {
        $this->chessboard->resetBoard();
        $this->moveNumber = 0;
        header("Location: index.php");
        exit;
    }

    // Start a new game (King + some enemies)
    public function startGame() {
        $this->reset(); // clear board first
        $this->moveNumber = 0;

        // Place King at (1,1)
        $this->chessboard->addPiece($this->moveNumber, 'King', 1, 1, 0);

        // Place some enemies
        $this->chessboard->addPiece($this->moveNumber, 'Pawn', 2, 2, 1);
        $this->chessboard->addPiece($this->moveNumber, 'Knight', 3, 3, 1);
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
}
