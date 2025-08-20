<?php
// init_db.php
require_once "config.php";

try {
    // 1. Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS king_escape CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database 'king_escape' created or already exists.<br>";

    // Switch to using the new database
    $pdo->exec("USE king_escape");

    // 2. Create chessboard_positions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS chessboard_positions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            move_number INT NOT NULL,
            piece_type VARCHAR(50) NOT NULL,
            position_x INT NOT NULL,
            position_y INT NOT NULL,
            is_enemy BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Table 'chessboard_positions' created.<br>";

    // 3. Insert sample data
    $pdo->exec("
        INSERT INTO chessboard_positions (move_number, piece_type, position_x, position_y, is_enemy) VALUES
        (0, 'King', 1, 1, FALSE),
        (0, 'Pawn', 2, 2, TRUE),
        (0, 'Knight', 3, 3, TRUE)
    ");
    echo "✅ Sample chessboard data inserted.<br>";

    echo "<br>🎉 Initialization completed successfully!";
} catch (PDOException $e) {
    die("❌ Database initialization failed: " . $e->getMessage());
}
