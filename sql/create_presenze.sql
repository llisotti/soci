# Crea tabella presenze (InnoDB engine poichè MySQL versione 5.6) per il database soci del copernico

DROP TABLE IF EXISTS presenze;

CREATE TABLE presenze
(
  data	DATE NOT NULL,
  iscrizione	DATE NOT NULL,
  member_id		SMALLINT UNSIGNED NOT NULL
);

