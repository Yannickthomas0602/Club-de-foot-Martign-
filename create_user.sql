CREATE DATABASE IF NOT EXISTS appdb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE appdb;


CREATE TABLE roles (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug       VARCHAR(50) NOT NULL UNIQUE,
  label      VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE teams (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE users (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  last_name       VARCHAR(100) NOT NULL,
  first_name      VARCHAR(100) NOT NULL,
  username        VARCHAR(100) NOT NULL UNIQUE,
  email           VARCHAR(190) NOT NULL UNIQUE,
  password_hash   VARCHAR(255) NOT NULL,
  role_id         INT UNSIGNED NOT NULL,
  is_active       TINYINT(1) NOT NULL DEFAULT 1,
  convocation_id  INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_users_role FOREIGN KEY (role_id)
    REFERENCES roles(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE user_teams (
  user_id  INT UNSIGNED NOT NULL,
  team_id  INT UNSIGNED NOT NULL,
  role_attribution VARCHAR(50) NOT NULL DEFAULT 'member',
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (user_id, team_id),
  CONSTRAINT fk_userteams_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_userteams_team FOREIGN KEY (team_id)
    REFERENCES teams(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE convocations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  team_id INT UNSIGNED NOT NULL,
  match_place VARCHAR(100) NOT NULL,
  match_date VARCHAR(100) NOT NULL,  -- saisir dans le formulaire 
  opposing_team VARCHAR(100) NOT NULL,
  player_name JSON NOT NULL,         -- Liste des joueurs (à revoir)

  CONSTRAINT fk_convocations_team FOREIGN KEY (team_id)
    REFERENCES teams(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE pictures (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  picture_path VARCHAR(255) NOT NULL,
  link VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (slug, label) VALUES
  ('admin', 'Administrateur'),
  ('coach', 'Coach'),
  ('user', 'Utilisateur')
ON DUPLICATE KEY UPDATE label = VALUES(label);

INSERT INTO teams (name) VALUES
  ('Equipe A'), ('Equipe B'), ('Equipe C')
ON DUPLICATE KEY UPDATE name = VALUES(name);


-- INSERT INTO users (last_name, first_name, username, email, password_hash, role_id)
-- VALUES (
--   'Admin',
--   'Super',
--   'admin',
--   'admin@example.com',
--   '$2y$10$U7bc8wWzF1dW2a4QmG5yeO2pTqXx5lBc1NaHkQXbPsdPyAplMJ0iW',
--   (SELECT id FROM roles WHERE slug='admin')
-- );