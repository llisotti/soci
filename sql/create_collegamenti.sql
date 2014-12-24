# Crea tabella collegamenti (InnoDB engine poichè MySQL versione 5.6) per il database soci del copernico

DROP TABLE IF EXISTS collegamenti;

CREATE TABLE collegamenti
(
  tipo			TINYINT UNSIGNED NOT NULL,	#255 tipi di parentela
  member_id_rel	SMALLINT UNSIGNED NOT NULL,
  member_id		SMALLINT UNSIGNED NOT NULL
);

