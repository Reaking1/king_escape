<?php
class Chessboard {
    private $conn;
    private $table = "chessboard_positions";

    // Constructor receives DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Insert a new piece (King or Enemy)
    public function addPiece($moveNumber, $pieceType, $x, $y, $isEnemy) {
        $sql = "INSERT INTO {$this->table} 
                (move_number, piece_type, position_x, position_y, is_enemy)
                VALUES (:move_number, :piece_type, :position_x, :position_y, :is_enemy)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':move_number' => $moveNumber,
            ':piece_type'  => $pieceType,
            ':position_x'  => $x,
            ':position_y'  => $y,
            ':is_enemy'    => $isEnemy
        ]);
    }

    // Get all pieces for a given move
    public function getPiecesByMove($moveNumber) {
        $sql = "SELECT * FROM {$this->table} WHERE move_number = :move_number";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':move_number' => $moveNumber]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get ALL pieces (regardless of move number)
    public function getAllPieces() {
        $sql = "SELECT * FROM {$this->table} ORDER BY move_number ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get King’s current position
    public function getKingPosition($moveNumber) {
        $sql = "SELECT position_x, position_y 
                FROM {$this->table} 
                WHERE move_number = :move_number AND piece_type = 'King'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':move_number' => $moveNumber]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

 // Update King's position instead of adding a new row
public function moveKing($moveNumber, $x, $y) {
    // Get latest king move
    $sql = "SELECT id FROM {$this->table} WHERE piece_type='King' ORDER BY move_number DESC LIMIT 1";
    $stmt = $this->conn->query($sql);
    $king = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($king) {
        // Update existing king
        $sqlUpdate = "UPDATE {$this->table} SET position_x=:x, position_y=:y, move_number=:move_number WHERE id=:id";
        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        return $stmtUpdate->execute([
            ':x' => $x,
            ':y' => $y,
            ':move_number' => $moveNumber,
            ':id' => $king['id']
        ]);
    } else {
        // No king yet, insert new
        $sqlInsert = "INSERT INTO {$this->table} (move_number, piece_type, position_x, position_y, is_enemy)
                      VALUES (:move_number, 'King', :x, :y, 0)";
        $stmtInsert = $this->conn->prepare($sqlInsert);
        return $stmtInsert->execute([
            ':move_number' => $moveNumber,
            ':x' => $x,
            ':y' => $y
        ]);
    }
}


    // Check if a square is occupied
    public function isOccupied($moveNumber, $x, $y) {
        $sql = "SELECT COUNT(*) as cnt 
                FROM {$this->table} 
                WHERE move_number = :move_number AND position_x = :x AND position_y = :y";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':move_number' => $moveNumber,
            ':x' => $x,
            ':y' => $y
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['cnt'] > 0;
    }

    // Clear the board
    public function resetBoard() {
        $sql = "DELETE FROM {$this->table}";
        return $this->conn->exec($sql);
    }

    public function spawnEnemies($moveNumber, $enemyArray) {
    foreach ($enemyArray as $enemy) {
        $this->addPiece($moveNumber, $enemy['type'], $enemy['x'], $enemy['y'], 1);
    }
}

}
