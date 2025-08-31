<?php
require_once __DIR__ . '/controllers/GamesController.php';
require_once __DIR__ . '/init_db.php';

$controller = new GamesController($pdo, 1); // player ID = 1

header('Content-Type: application/json');
echo json_encode($controller->findEscape());
exit;
