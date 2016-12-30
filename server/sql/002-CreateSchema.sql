-- mysql -u playguard -p
-- connect playguard;
CREATE TABLE Player (
  login          VARCHAR(10) PRIMARY KEY,
  max_per_day    INT NOT NULL,
  max_per_week   INT NOT NULL,
  extra_day      DATE,
  max_extra_day  INT,
  max_extra_week INT,
  locked_until   DATE,
  login_source   VARCHAR(20),
  login_ip       VARCHAR(20),
  login_date     DATETIME,
  logout_date    DATETIME,
  confirm_date   DATETIME,
  played_day     INT,
  played_week    INT,
  password_hash  VARCHAR(100)
);

CREATE TABLE Playtime (
  login        VARCHAR(10) NOT NULL,
  login_source VARCHAR(20) NOT NULL,
  login_ip     VARCHAR(20) NOT NULL,
  login_date   DATETIME NOT NULL,
  logout_date  DATETIME NOT NULL
);
