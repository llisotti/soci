/**
Crea tabella anagrafica
*/

DROP TABLE IF EXISTS anagrafica;

CREATE TABLE anagrafica
(
  cognome		VARCHAR(30) NOT NULL,
  nome			VARCHAR(30) NOT NULL,
  data_nascita		DATE NOT NULL,			#data nel formato YYYY-MM-GG
  id				SMALLINT UNSIGNED AUTO_INCREMENT,		#id
  comune_nascita	VARCHAR(40) NULL,		#puo essere nullo se nato all estero
  provincia_nascita	CHAR(2) NULL,			#puo essere nullo se nato all estero
  stato_nascita		CHAR(2) NOT NULL,		#stato nascita sempre 2 lettere (IT, US, ecc)
  sesso			ENUM('M','F') NULL,
  indirizzo			VARCHAR(100) NULL,
  citta			VARCHAR(40) NULL,
  cap			VARCHAR(7) NULL,		#codice di avviamento postale massimo 7 cifre
  provincia		CHAR(2) NULL, 			#provincia sempre 2 lettere
  stato			CHAR(2) NULL,			#stato sempre 2 lettere (IT, MD, ecc)
  telefono			VARCHAR(15) NULL, 		#anche se è un numero lo tratto come testo (formato E.164 vuole 15 digit)
  email      		VARCHAR(40) NULL,
  PRIMARY KEY (id)
) ENGINE=INNODB;