CREATE DATABASE IF NOT EXISTS samvedna_homeopathy
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE samvedna_homeopathy;

CREATE TABLE IF NOT EXISTS inquiries (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  parent_name     VARCHAR(255)  NOT NULL,
  child_age       INT           NOT NULL,
  `condition`     VARCHAR(255)  NOT NULL,
  country         VARCHAR(100)  NOT NULL,
  phone           VARCHAR(50)   NOT NULL,
  email           VARCHAR(255)  NOT NULL,
  preferred_time  VARCHAR(50)   NOT NULL DEFAULT 'morning',
  message         TEXT,
  ip_address      VARCHAR(45)   DEFAULT '',
  user_agent      VARCHAR(255)  DEFAULT '',
  created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
