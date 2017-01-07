-- EN: all times in seconds (3600 = 1 hour, 5400 = 1.5 hours, 10800 = 3 hours, 14400 = 4 hours, 25200 = 7 hours, etc.)
-- DE: Alle Zeitangaben in Sekunden (3600 = 1 Stunde, 5400 = 1.5 Stunden, 10800 = 3 Stunden, 14400 = 4 Stunden, 25200 = 7 Stunden, usw.)
INSERT INTO Player (login, email, administrator, password_hash) VALUES ('admin', 'admin@email.com', 1, '$2y$10$ReadManualToGeneratePasswordHash');

-- You can also use the admin.php web page to create regular users (players)
INSERT INTO Player (login, max_per_day, max_per_week) VALUES ('user1',   3600, 10800);
INSERT INTO Player (login, max_per_day, max_per_week) VALUES ('user2',   3600,  5400);
