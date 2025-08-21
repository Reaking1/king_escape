-- Database
CREATE DATABASE IF NOT EXISTS kings_escape;
USE kings_escape;

-- Players table
CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Games table
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    result ENUM('won', 'lost', 'in_progress') DEFAULT 'in_progress',
    FOREIGN KEY (player_id) REFERENCES players(id)
);

-- Pieces table (defines types of chess pieces)
CREATE TABLE pieces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,         -- e.g., King, Pawn, Knight
    is_enemy BOOLEAN NOT NULL          -- TRUE = enemy piece, FALSE = player’s king
);

-- Chessboard positions table
CREATE TABLE chessboard_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    move_number INT NOT NULL,
    piece_type VARCHAR(10) NOT NULL,
    position_x INT NOT NULL,
    position_y INT NOT NULL,
    is_enemy BOOLEAN NOT NULL
);

-- Moves table (records each move made by the player)
CREATE TABLE moves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    move_number INT NOT NULL,
    from_x INT NOT NULL,
    from_y INT NOT NULL,
    to_x INT NOT NULL,
    to_y INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id)
);
