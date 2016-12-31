-- mysqladmin -u root create playguard -p
-- mysql -u root -p

-- PLEASE REPLACE ******** WITH REAL PASSWORD
CREATE USER 'playguard'@'localhost' IDENTIFIED BY '********';
GRANT ALL PRIVILEGES ON playguard.* TO  'playguard'@'localhost';
FLUSH PRIVILEGES;