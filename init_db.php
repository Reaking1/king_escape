<?php
// init_db.php
$host = "localhost";
$username = "root";  // Default XAMPP
$password = "";      // Default XAMPP

try {
    // Connect WITHOUT specifying database (so we can create it)
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS kings_escape CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database 'kings_escape' created or already exists.<br>";

    // Switch to using the new database
    $pdo->exec("USE kings_escape");

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

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS exits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        game_id INT NOT NULL,
        exit_x INT NOT NULL,
        exit_y INT NOT NULL,
        FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
    )
");
echo "✅ Table 'exits' created.<br>";


    echo "<br>🎉 Initialization completed successfully!";
} catch (PDOException $e) {
    die("❌ Database initialization failed: " . $e->getMessage());
}
