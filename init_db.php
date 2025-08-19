<?php
// init_db.php
require_once "config.php";

try {
    // 1. Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS marathon_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database 'marathon_tracker' created or already exists.<br>";

    // Switch to using the new database
    $pdo->exec("USE marathon_tracker");

    // 2. Create chessboard_positions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS chessboard_positions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            move_number INT NOT NULL,
            piece_name VARCHAR(50) NOT NULL,
            position_x INT NOT NULL,
            position_y INT NOT NULL,
            is_enemy BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Table 'chessboard_positions' created.<br>";

    // 3. Create marathon_players table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS marathon_players (
            id INT AUTO_INCREMENT PRIMARY KEY,
            player_name VARCHAR(100) NOT NULL,
            team_name VARCHAR(100),
            distance_ran FLOAT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Table 'marathon_players' created.<br>";

    // 4. Insert sample data into chessboard_positions
    $pdo->exec("
        INSERT INTO chessboard_positions (move_number, piece_name, position_x, position_y, is_enemy) VALUES
        (0, 'King', 1, 1, FALSE),
        (0, 'Queen', 4, 1, FALSE),
        (0, 'Pawn', 2, 2, FALSE),
        (0, 'Pawn', 3, 2, FALSE),
        (0, 'Enemy Rook', 8, 8, TRUE)
    ");
    echo "✅ Sample chessboard data inserted.<br>";

    // 5. Insert sample data into marathon_players
    $pdo->exec("
        INSERT INTO marathon_players (player_name, team_name, distance_ran) VALUES
        ('Alice', 'Team A', 5.2),
        ('Bob', 'Team A', 7.4),
        ('Charlie', 'Team B', 10.0)
    ");
    echo "✅ Sample marathon players inserted.<br>";

    echo "<br>🎉 Initialization completed successfully!";
} catch (PDOException $e) {
    die("❌ Database initialization failed: " . $e->getMessage());
}
