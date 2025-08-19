<?php
// models/Chessboard.php

class Chessboard {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Clear the board (delete all pieces)
    public function resetBoard() {
        $stmt = $this->pdo->prepare("DELETE FROM chessboard_positions");
        $stmt->execute();
    }

    // Add a piece to the board
    public function addPiece($pieceType, $position, $moveNumber = 0, $isEnemy = false) {
        $col = substr($position,0,1);
        $row = intval(substr($position,1));

        $stmt = $this->pdo->prepare("INSERT INTO chessboard_positions (move_number, piece_type, position_x, position_y, is_enemy) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$moveNumber, $pieceType, $col, $row, $isEnemy]);
    }

    // Get the position of a piece
    public function getPiecePosition($pieceType) {
        $stmt = $this->pdo->prepare("SELECT position_x, position_y FROM chessboard_positions WHERE piece_type = ? ORDER BY move_number DESC LIMIT 1");
        $stmt->execute([$pieceType]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Move a piece
    public function movePiece($pieceType, $newPosition) {
        $pos = $this->getPiecePosition($pieceType);
        if (!$pos) return false;

        $col = substr($newPosition,0,1);
        $row = intval(substr($newPosition,1));

        // Insert a new row for this move
        $stmt = $this->pdo->prepare("INSERT INTO chessboard_positions (move_number, piece_type, position_x, position_y, is_enemy) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([0, $pieceType, $col, $row, false]); // move_number = 0 for simplicity
        return true;
    }

    // Get all pieces on board
    public function getAllPieces() {
        $stmt = $this->pdo->query("SELECT * FROM chessboard_positions");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
