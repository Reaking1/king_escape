-- King starts at (1,1)
INSERT INTO chessboard_positions (move_number, piece_type, position_x, position_y, is_enemy)
VALUES (0, 'King', 1, 1, FALSE);

-- Exit is at (8,8) – not an enemy
INSERT INTO chessboard_positions (move_number, piece_type, position_x, position_y, is_enemy)
VALUES (0, 'Exit', 8, 8, FALSE);

-- Enemies appearing at move 1
INSERT INTO chessboard_positions (move_number, piece_type, position_x, position_y, is_enemy)
VALUES (1, 'Pawn', 2, 1, TRUE),
       (1, 'Knight', 3, 2, TRUE);

-- More enemies at move 2
INSERT INTO chessboard_positions (move_number, piece_type, position_x, position_y, is_enemy)
VALUES (2, 'Bishop', 4, 4, TRUE),
       (2, 'Rook', 5, 1, TRUE);

-- Ally at move 2
INSERT INTO chessboard_positions (move_number, piece_type, position_x, position_y, is_enemy)
VALUES (2, 'Pawn', 2, 2, FALSE);
