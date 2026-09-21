-- ------------------------------------------------------------
-- Adatbázis a bejelentkezési oldalhoz
--
-- Importálás: phpMyAdmin -> Importálás -> válaszd ki ezt a fájlt,
-- vagy parancssorból:  mysql -u root -p < database.sql
-- ------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS login_projekt
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE login_projekt;

-- FIGYELEM: az importálás törli a régi users táblát (és a benne lévő regisztrációkat),
-- és újat hoz létre.
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(30)  NOT NULL,
  email         VARCHAR(191) NOT NULL,
  -- FIGYELEM: a jelszó sima szövegként tárolódik. Csak helyi, iskolai projekthez jó!
  password      VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Opcionális: külön adatbázis-felhasználó a root helyett.
-- Ha használod, írd át a DB_USER és DB_PASS értékét a config.php-ban.
-- ------------------------------------------------------------
-- CREATE USER 'login_user'@'localhost' IDENTIFIED BY 'ide-jon-egy-eros-jelszo';
-- GRANT SELECT, INSERT ON login_projekt.* TO 'login_user'@'localhost';
-- FLUSH PRIVILEGES;
