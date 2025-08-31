<?php
// models/Chessboard.php

class Chessboard {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get or create a new game for a player
     */
// Chessboard.php
public function getOrCreateGame($playerId) {
    // 1. Look for an existing in-progress game
    $sql = "SELECT id FROM games WHERE player_id = :player_id AND result = 'in_progress' LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':player_id' => $playerId]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($game) {
        return $game['id'];
    }

    // 2. Otherwise create a new game
    $sql = "INSERT INTO games (player_id) VALUES (:player_id)";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':player_id' => $playerId]);
    return $this->conn->lastInsertId();
}

// When the game finishes
public function finishGame($gameId, $result = 'won') {
    $sql = "UPDATE games SET result = :result, ended_at = NOW() WHERE id = :gameId";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':result' => $result, ':gameId' => $gameId]);
}


    /**
     * Place a new piece on the board
     */
    public function placePiece($gameId, $pieceTypeName, $isEnemy, $x, $y) {
        // Ensure piece type exists
        $pieceTypeId = $this->getOrCreatePieceType($pieceTypeName, $isEnemy);

        $sql = "INSERT INTO game_pieces (game_id, piece_type_id, position_x, position_y)
                VALUES (:game_id, :piece_type_id, :x, :y)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':game_id' => $gameId,
            ':piece_type_id' => $pieceTypeId,
            ':x' => $x,
            ':y' => $y
        ]);
    }

    /**
     * Get or create piece type
     */
    private function getOrCreatePieceType($name, $isEnemy) {
        $sql = "SELECT id FROM piece_types WHERE name = :name AND is_enemy = :is_enemy LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':is_enemy' => $isEnemy
        ]);
        $type = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($type) {
            return $type['id'];
        }

        // Create if not found
        $sql = "INSERT INTO piece_types (name, is_enemy) VALUES (:name, :is_enemy)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':is_enemy' => $isEnemy
        ]);
        return $this->conn->lastInsertId();
    }

    /**
     * Move the King to a new position
     */
    public function moveKing($gameId, $x, $y, $moveNumber) {
        // Find King in this game
        $sql = "SELECT gp.id 
                FROM game_pieces gp
                INNER JOIN piece_types pt ON gp.piece_type_id = pt.id
                WHERE gp.game_id = :game_id AND pt.name = 'King' LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':game_id' => $gameId]);
        $king = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$king) {
            return false; // no king to move
        }

        // Update position
        $sql = "UPDATE game_pieces 
                SET position_x = :x, position_y = :y
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':x' => $x,
            ':y' => $y,
            ':id' => $king['id']
        ]);

        // Record move
        $sql = "INSERT INTO moves (game_id, move_number, piece_id, from_x, from_y, to_x, to_y)
                VALUES (:game_id, :move_number, :piece_id,
                        (SELECT position_x FROM game_pieces WHERE id = :piece_id),
                        (SELECT position_y FROM game_pieces WHERE id = :piece_id),
                        :to_x, :to_y)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':game_id' => $gameId,
            ':move_number' => $moveNumber,
            ':piece_id' => $king['id'],
            ':to_x' => $x,
            ':to_y' => $y
        ]);

        return true;
    }

    /**
     * Get all pieces on the board for a game
     */
    public function getAllPieces($gameId) {
        $sql = "SELECT gp.id, pt.name AS piece_type, gp.position_x, gp.position_y, pt.is_enemy
                FROM game_pieces gp
                INNER JOIN piece_types pt ON gp.piece_type_id = pt.id
                WHERE gp.game_id = :game_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':game_id' => $gameId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if square is occupied
     */
    public function isOccupied($gameId, $x, $y) {
        $sql = "SELECT COUNT(*) as cnt 
                FROM game_pieces 
                WHERE game_id = :game_id AND position_x = :x AND position_y = :y";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':game_id' => $gameId,
            ':x' => $x,
            ':y' => $y
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['cnt'] > 0;
    }

    /**
     * Clear board for a game
     */
    public function clearBoard($gameId) {
        $sql = "DELETE FROM game_pieces WHERE game_id = :game_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':game_id' => $gameId]);
    }

public function ensureGameExists($gameId) {
    $sql = "SELECT id FROM games WHERE id = :gameId";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':gameId' => $gameId]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$game) {
        $playerId = 1; // make sure player with id 1 exists

        // Insert game and get the auto-incremented ID
        $this->conn->exec("INSERT INTO games (player_id) VALUES ($playerId)");
        $newGameId = $this->conn->lastInsertId();

        return $newGameId; // return the ID of the new game
    }

    return $game['id']; // return existing game ID
}


public function ensureExitExists($gameId, $x=8, $y=8) {
    $sql = "SELECT id FROM exits WHERE game_id = :gid LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':gid'=>$gameId]);
    if (!$stmt->fetch()) {
        $sql = "INSERT INTO exits (game_id, exit_x, exit_y) VALUES (:gid, :x, :y)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':gid'=>$gameId, ':x'=>$x, ':y'=>$y]);
    }
}


public function getPieceTypeId($name) {
    $sql = "SELECT id FROM piece_types WHERE name = :name";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':name' => $name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['id'] : null;
}

public function addPieceToGame($gameId, $pieceTypeId, $x, $y) {
    $sql = "INSERT INTO game_pieces (game_id, piece_type_id, position_x, position_y)
            VALUES (:gameId, :pieceTypeId, :x, :y)";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([
        ':gameId' => $gameId,
        ':pieceTypeId' => $pieceTypeId,
        ':x' => $x,
        ':y' => $y
    ]);
}


public function findEarliestEscape($gameId, $startX, $startY, $enemySpawnArray = []) {
    // get exit
    $sql = "SELECT exit_x, exit_y FROM exits WHERE game_id = :gid LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':gid'=>$gameId]);
    $exit = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$exit) return -1;

    $exitX = $exit['exit_x'];
    $exitY = $exit['exit_y'];

    // BFS queue: [x,y,moveNumber]
    $queue = [[$startX,$startY,0]];
    $visited = [];

    while (!empty($queue)) {
        [$x,$y,$move] = array_shift($queue);

        // reached exit?
        if ($x == $exitX && $y == $exitY) {
            return $move;
        }

        // mark visited at this depth
        $visited["$x,$y,$move"] = true;

        // --- Spawn enemies for NEXT move (virtual blocking) ---
        $blockedNext = [];
        if (isset($enemySpawnArray[$move+1])) {
            foreach ($enemySpawnArray[$move+1] as $enemy) {
                $blockedNext["{$enemy['x']},{$enemy['y']}"] = true;
            }
        }

        // 8 possible king moves
        for ($dx=-1;$dx<=1;$dx++) {
            for ($dy=-1;$dy<=1;$dy++) {
                if ($dx==0 && $dy==0) continue;
                $nx = $x+$dx; 
                $ny = $y+$dy;

                if ($nx<1 || $nx>8 || $ny<1 || $ny>8) continue;

                // skip if occupied by existing DB piece
                if ($this->isOccupied($gameId,$nx,$ny)) continue;

                // skip if enemy spawns there next turn
                if (isset($blockedNext["$nx,$ny"])) continue;

                // skip if already visited at this depth
                if (isset($visited["$nx,$ny,".($move+1)])) continue;

                $queue[] = [$nx,$ny,$move+1];
            }
        }
    }

    return -1; // no escape found
}
}



