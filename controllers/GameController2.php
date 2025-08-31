<?php
// controllers/GamesController.php

require_once __DIR__ . '/../models/Chessboard.php';

class GamesController {
    private $chessboard;
    private $gameId;
    private $playerId;
    private $moveNumber = 0;
     private $board; // in-memory board for fast access


    public function __construct($db, $playerId) {
        $this->chessboard = new Chessboard($db);
        $this->playerId = $playerId;

        // Ensure game exists or create a new one
        $this->gameId = $this->chessboard->getOrCreateGame($playerId);
      $this->loadBoard();
        // If no pieces exist, start a fresh game
        $pieces = $this->chessboard->getAllPieces($this->gameId);
        if (empty($pieces)) {
            $this->startGame();
        }
    }
   private function loadBoard() {
        $pieces = $this->chessboard->getAllPieces($this->gameId);
        $board = [];
        foreach ($pieces as $p) {
            $board[$p['position_x']][$p['position_y']] = $p;
        }
        $this->board = $board;
    }
    // Display game board
    public function index() {
        $pieces = $this->chessboard->getAllPieces($this->gameId);
        if (empty($pieces)) {
            $this->startGame();
            $pieces = $this->chessboard->getAllPieces($this->gameId);
        }
        include __DIR__ . '/../views/game.php';
    }

    // Get board state as JSON
    public function getBoardState() {
        header('Content-Type: application/json');
        echo json_encode($this->chessboard->getAllPieces($this->gameId));
    }

    // Reset the current game
    public function resetGame() {
        $this->chessboard->markGameResult($this->gameId, 'reset');
        $this->chessboard->clearBoard($this->gameId);
        $this->moveNumber = 0;
        $this->gameId = $this->startGame();
        return $this->getBoardState();
    }

    // Start a new game
    public function startGame() {
        $this->gameId = $this->chessboard->createNewGame($this->playerId);
        $this->moveNumber = 0;

        // Add King
        $kingId = $this->chessboard->getPieceTypeId('King');
        $this->chessboard->addPieceToGame($this->gameId, $kingId, 1, 1);

        // Add initial enemies
        $enemies = [
            ['name' => 'Pawn', 'x' => 2, 'y' => 2],
            ['name' => 'Knight', 'x' => 3, 'y' => 3]
        ];
        foreach ($enemies as $enemy) {
            $enemyId = $this->chessboard->getPieceTypeId($enemy['name']);
            $this->chessboard->addPieceToGame($this->gameId, $enemyId, $enemy['x'], $enemy['y']);
        }

        // Ensure exit exists
        $this->chessboard->ensureExitExists($this->gameId, 8, 8);

        return $this->gameId;
    }

    // Move King
  public function moveKing($x, $y) {
    $king = null;
    foreach ($this->board as $col) {
        foreach ($col as $p) {
            if ($p['piece_type'] === 'King') {
                $king = $p;
                break 2;
            }
        }
    }
    if (!$king) return false;

    $currentX = $king['position_x'];
    $currentY = $king['position_y'];

    $dx = abs($x - $currentX);
    $dy = abs($y - $currentY);

    if ($dx > 1 || $dy > 1) return false; // King moves only 1 square
    if ($this->chessboard->isSquareAttackedMemory($this->board, $x, $y)) return false;

    if (isset($this->board[$x][$y]) && !$this->board[$x][$y]['is_enemy']) return false;

    $this->chessboard->moveKing($this->gameId, $x, $y, ++$this->moveNumber);

    // Update in-memory board
    unset($this->board[$currentX][$currentY]);
    $this->board[$x][$y] = [
        'position_x' => $x,
        'position_y' => $y,
        'piece_type' => 'King',
        'is_enemy' => false
    ];

    return true;
}


    // Check if player has won
    public function checkWinCondition() {
        $pieces = $this->chessboard->getAllPieces($this->gameId);
        foreach ($pieces as $p) {
            if ($p['piece_type'] === 'King' && $p['position_y'] == 8) {
                $this->chessboard->markGameResult($this->gameId, 'won');
                return true;
            }
        }
        return false;
    }

    // Process a player's move
 public function processMove($kingMove) {
    $x = $kingMove['x'];
    $y = $kingMove['y'];

    $kingMoved = $this->moveKing($x, $y);
    if (!$kingMoved) {
        return [
            'win' => false,
            'error' => 'Invalid move',
            'pieces' => $this->board
        ];
    }

    if ($this->checkWinCondition()) {
        return [
            'win' => true,
            'pieces' => $this->board
        ];
    }

    // Spawn enemies in-memory
    $numEnemies = rand(1, 2);
    $enemyTypes = ['Pawn', 'Knight', 'Bishop', 'Rook'];

    $occupied = [];
    foreach ($this->board as $px => $col) {
        foreach ($col as $py => $p) {
            $occupied["$px,$py"] = true;
        }
    }

    for ($i = 0; $i < $numEnemies; $i++) {
        do {
            $randX = rand(1, 8);
            $randY = rand(1, 8);
        } while (isset($occupied["$randX,$randY"]));

        $type = $enemyTypes[array_rand($enemyTypes)];
        $this->chessboard->placePiece($this->gameId, $type, true, $randX, $randY);

        $occupied["$randX,$randY"] = true;
        $this->board[$randX][$randY] = [
            'position_x' => $randX,
            'position_y' => $randY,
            'piece_type' => $type,
            'is_enemy' => true
        ];
    }

    return [
        'win' => false,
        'pieces' => $this->board
    ];
}


    // Optional helper: calculate earliest escape
public function findEscape($gameId = null) {
    if ($gameId === null) $gameId = $this->gameId;
    $this->chessboard->ensureExitExists($gameId);

    $enemySpawnArray = [
        1 => [['type'=>'Pawn','x'=>2,'y'=>2]],
        2 => [['type'=>'Knight','x'=>3,'y'=>3], ['type'=>'Pawn','x'=>4,'y'=>4]],
        3 => [['type'=>'Rook','x'=>5,'y'=>5]]
    ];

    $startX = 1;
    $startY = 1;

    $earliest = $this->chessboard->findEarliestEscape($gameId, $startX, $startY, $enemySpawnArray);

    return $earliest === -1
        ? ['success'=>true, 'message'=>'No escape possible.']
        : ['success'=>true, 'message'=>"Earliest escape in $earliest moves."];
}

    // In GamesController.php

// 🔹 Check the earliest possible escape for the King
public function checkEarliestEscape() {
    $this->chessboard->ensureExitExists($this->gameId);

    // Build a simple enemy spawn map for simulation (can adjust as needed)
    $enemySpawnArray = [
        1 => [['type'=>'Pawn','x'=>2,'y'=>2]],
        2 => [['type'=>'Knight','x'=>3,'y'=>3], ['type'=>'Pawn','x'=>4,'y'=>4]],
        3 => [['type'=>'Rook','x'=>5,'y'=>5]]
    ];

    // Assume King starts at (1,1)
    $startX = 1;
    $startY = 1;

    return $this->chessboard->findEarliestEscape($this->gameId, $startX, $startY, $enemySpawnArray);
}

// Optional: expose a helper to get pieces (already partially done)
public function getPieces() {
    return $this->chessboard->getAllPieces($this->gameId);
}

}
