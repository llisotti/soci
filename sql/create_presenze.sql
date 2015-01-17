# Crea tabella presenze (InnoDB engine poichè MySQL versione 5.6) per il database soci del copernico

DROP TABLE IF EXISTS presenze;

CREATE TABLE presenze
(
  data	DATE, #Data del tesseramento per anno corrente
  iscrizione	DATE NOT NULL, #Data della prima iscrizione al database
  member_id		SMALLINT UNSIGNED NOT NULL
);

