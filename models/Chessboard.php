<?php
// models/Chessboard.php

class Chessboard {
    private $conn;
     // $this->db is still null ❌
      private $db;

    public function __construct($db) {
        $this->conn = $db;
        $this->db = $db;  // now getPieceAt works
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

// Optimized: BFS + in-memory board + precomputed attacks
public function findEarliestEscape($gameId, $startX, $startY, $enemySpawnArray = []) {
    // 1️⃣ Load all pieces once
    $pieces = $this->getAllPieces($gameId);
    $board = [];
    foreach ($pieces as $p) {
        $board[$p['position_x']][$p['position_y']] = $p;
    }

    // 2️⃣ Get exit
    $sql = "SELECT exit_x, exit_y FROM exits WHERE game_id = :gid LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':gid' => $gameId]);
    $exit = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$exit) return -1;
    $exitX = $exit['exit_x'];
    $exitY = $exit['exit_y'];

    // 3️⃣ BFS queue: [x, y, moveNumber]
    $queue = [[$startX, $startY, 0]];
    $visited = [];

    // 4️⃣ Precompute all enemy attacks per move number
    $maxMoves = 20; // limit BFS depth
    $precomputedAttacks = [];

    for ($move = 0; $move <= $maxMoves; $move++) {
        $precomputedAttacks[$move] = [];
        // Add virtual enemy spawns
        if (isset($enemySpawnArray[$move])) {
            foreach ($enemySpawnArray[$move] as $enemy) {
                $precomputedAttacks[$move][$enemy['x']][$enemy['y']] = true;
            }
        }
        // Add current enemy positions
        foreach ($pieces as $p) {
            if ($p['is_enemy']) {
                $precomputedAttacks[$move][$p['position_x']][$p['position_y']] = true;
            }
        }
    }

    // 5️⃣ BFS loop
    while (!empty($queue)) {
        [$x, $y, $move] = array_shift($queue);
        if ($x == $exitX && $y == $exitY) return $move; // reached exit
        if ($move > $maxMoves) continue;

        $visited["$x,$y,$move"] = true;

        for ($dx = -1; $dx <= 1; $dx++) {
            for ($dy = -1; $dy <= 1; $dy++) {
                if ($dx == 0 && $dy == 0) continue;
                $nx = $x + $dx;
                $ny = $y + $dy;
                if ($nx < 1 || $nx > 8 || $ny < 1 || $ny > 8) continue;
                if (isset($board[$nx][$ny])) continue; // occupied
                if (isset($precomputedAttacks[$move+1][$nx][$ny])) continue; // attacked next
                if (isset($visited["$nx,$ny," . ($move + 1)])) continue;

                $queue[] = [$nx, $ny, $move + 1];
            }
        }
    }

    return -1; // no escape
}

// Optimized in-memory square attack check
public function isSquareAttackedMemory($board, $x, $y) {
    foreach ($board as $px => $col) {
        foreach ($col as $py => $piece) {
            $type = $piece['piece_type'];
            $isEnemy = $piece['is_enemy'];

            switch ($type) {
                case 'Pawn':
                    if ($isEnemy && (($px == $x-1 || $px == $x+1) && $py == $y-1)) return true;
                    break;
                case 'Knight':
                    $moves = [[2,1],[1,2],[-1,2],[-2,1],[-2,-1],[-1,-2],[1,-2],[2,-1]];
                    foreach ($moves as $m) if ($px+$m[0]==$x && $py+$m[1]==$y) return true;
                    break;
                case 'Bishop':
                    if (abs($px-$x)==abs($py-$y) && $this->isPathClearBoard($board,$px,$py,$x,$y)) return true;
                    break;
                case 'Rook':
                    if (($px==$x || $py==$y) && $this->isPathClearBoard($board,$px,$py,$x,$y)) return true;
                    break;
                case 'Queen':
                    if (($px==$x||$py==$y||abs($px-$x)==abs($py-$y)) && $this->isPathClearBoard($board,$px,$py,$x,$y)) return true;
                    break;
            }
        }
    }
    return false;
}

// Optimized path clear check in-memory
private function isPathClearBoard($board, $x1, $y1, $x2, $y2) {
    $dx = $x2-$x1; $dy=$y2-$y1;
    $stepX = $dx==0?0:$dx/abs($dx);
    $stepY = $dy==0?0:$dy/abs($dy);
    $cx=$x1+$stepX; $cy=$y1+$stepY;

    while ($cx != $x2 || $cy != $y2) {
        if (isset($board[$cx][$cy])) return false;
        $cx += $stepX;
        $cy += $stepY;
    }
    return true;
}


public function createNewGame($playerId) {
    $sql = "INSERT INTO games (player_id, result) VALUES (:player_id, 'in_progress')";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':player_id' => $playerId]);
    return $this->conn->lastInsertId();
}

public function markGameResult($gameId, $result) {
    $sql = "UPDATE games SET result = :result WHERE id = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':result' => $result, ':id' => $gameId]);
}

 public function getPieceAt($gameId, $x, $y) {
    $stmt = $this->conn->prepare("
    SELECT gp.*, pt.name AS piece_type, pt.is_enemy
    FROM game_pieces gp
    JOIN piece_types pt ON gp.piece_type_id = pt.id
    WHERE gp.game_id = ? 
      AND gp.position_x = ? 
      AND gp.position_y = ?
   ");

        $stmt->execute([$gameId, $x, $y]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 2. Helper: Check if path is clear for sliding pieces
    private function isPathClear($gameId, $x1, $y1, $x2, $y2) {
        $dx = $x2 - $x1;
        $dy = $y2 - $y1;

        $stepX = $dx == 0 ? 0 : $dx / abs($dx);
        $stepY = $dy == 0 ? 0 : $dy / abs($dy);

        $cx = $x1 + $stepX;
        $cy = $y1 + $stepY;

        while ($cx != $x2 || $cy != $y2) {
            if ($this->getPieceAt($gameId, $cx, $cy)) {
                return false; // blocked
            }
            $cx += $stepX;
            $cy += $stepY;
        }

        return true;
    }

}



