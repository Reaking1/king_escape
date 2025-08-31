<?php
// controllers/GamesController.php

require_once __DIR__ . '/../models/Chessboard.php';

class GamesController {
    private $chessboard;
    private $moveNumber = 0;
    private $gameId;
        private $playerId;

public function __construct($db, $playerId) {
    $this->chessboard = new Chessboard($db);
    $this->playerId = $playerId;

    // Ensure game exists and get the actual ID
    $this->gameId = $this->chessboard->getOrCreateGame($playerId);

    // Start game only if it's a new game
    $pieces = $this->chessboard->getAllPieces($this->gameId);
    if (empty($pieces)) {
        $this->startGame();
    }
}


 /**
     * API: Find earliest escape turn
     */
    public function findEscape($gameId) {
        // (1) Ensure game exists
        $gameId = $this->chessboard->ensureGameExists($gameId);

        // (2) Ensure exit exists (defaults to 8,8 if not set)
        $this->chessboard->ensureExitExists($gameId);

        // (3) Example hardcoded spawn array (could come from DB later)
        $enemySpawnArray = [
            1 => [['type'=>'Pawn','x'=>2,'y'=>2]],
            2 => [['type'=>'Knight','x'=>3,'y'=>3], ['type'=>'Pawn','x'=>4,'y'=>4]],
            3 => [['type'=>'Rook','x'=>5,'y'=>5]]
        ];

        // (4) Start coordinates (x1,y1) -> King assumed at (1,1)
        $startX = 1;
        $startY = 1;

        // (5) Run BFS
        $earliest = $this->chessboard->findEarliestEscape($gameId, $startX, $startY, $enemySpawnArray);

        // (6) Return response
        if ($earliest === -1) {
            return ['success' => true, 'message' => 'No escape possible.'];
        } else {
            return ['success' => true, 'message' => "Earliest escape in $earliest moves."];
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
    // 1. Get existing in-progress game or create a new one
    $gameId = $this->chessboard->getOrCreateGame($this->playerId);

    // 2. Only place pieces if board is empty
    $pieces = $this->chessboard->getAllPieces($gameId);
    if (empty($pieces)) {
        // Place the King at starting position
        $kingId = $this->chessboard->getPieceTypeId('King');
        $this->chessboard->addPieceToGame($gameId, $kingId, 1, 1);

        // Spawn enemies
        $enemyData = [
            ['name' => 'Pawn', 'x' => 2, 'y' => 2],
            ['name' => 'Knight', 'x' => 3, 'y' => 3]
        ];

        foreach ($enemyData as $enemy) {
            $enemyId = $this->chessboard->getPieceTypeId($enemy['name']);
            $this->chessboard->addPieceToGame($gameId, $enemyId, $enemy['x'], $enemy['y']);
        }

        // Ensure exit exists (you can customize coordinates)
        $this->chessboard->ensureExitExists($gameId, 8, 8);
    }

    return $gameId;
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


// controllers/GamesController.php

public function checkWinCondition() {
    $pieces = $this->chessboard->getAllPieces($this->gameId);

    foreach ($pieces as $piece) {
        if ($piece['piece_type'] === 'King') {
            // Escape condition: King reaches rank 8
            if ($piece['position_y'] == 8) {
                return true;
            }
        }
    }
    return false;
}


    // controllers/GamesController.php

// Process a turn (king moves + new enemies)
public function processMove($kingMove) {
    $this->moveNumber++;

    // Move King
    $x = $kingMove['x'];
    $y = $kingMove['y'];
    $this->moveKing($x, $y);

    // Check win BEFORE spawning enemies
    if ($this->checkWinCondition()) {
        return ['win' => true, 'pieces' => $this->chessboard->getAllPieces($this->gameId)];
    }

    // 🔥 Random enemy spawning
    $numEnemies = rand(1, 2);
    for ($i = 0; $i < $numEnemies; $i++) {
        do {
            $randX = rand(1, 8);
            $randY = rand(1, 8);
        } while ($this->chessboard->isOccupied($this->gameId, $randX, $randY));

        $enemyTypes = ['Pawn', 'Knight', 'Bishop', 'Rook'];
        $enemyType = $enemyTypes[array_rand($enemyTypes)];

        $this->chessboard->placePiece($this->gameId, $enemyType, true, $randX, $randY);
    }

    return ['win' => false, 'pieces' => $this->chessboard->getAllPieces($this->gameId)];
}

public function checkEarliestEscape() {
    $startX = 1; $startY = 1; // starting pos
    $earliest = $this->chessboard->findEarliestEscape($this->gameId, $startX, $startY);
    return $earliest;
}


}
