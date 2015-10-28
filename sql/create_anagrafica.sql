/** Crea una tabella
* @table anagrafica
* @col number_id Chiave univoca che si autoincrementa
* @col cognome Cognome di una persona
* @author Luca Lisotti
*/




# Crea tabella anagrafica (InnoDB engine poichè MySQL versione 5.6) per il database soci del copernico

DROP TABLE IF EXISTS anagrafica;

CREATE TABLE anagrafica
(
  member_id		SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,	#65536 membri in 5 anni massimo
  PRIMARY KEY	(member_id), #la chiave primaria è implicitamente anche UNIQUE
  cognome		VARCHAR(30) NOT NULL,
  nome			VARCHAR(30) NOT NULL,
  data_nascita	DATE NULL,	#data nel formato YYYY-MM-GG
  luogo_nascita	VARCHAR(40) NULL,
  sesso			ENUM('M','F') NULL,
  cf			CHAR(16) NULL,	#codice fiscale sempre 16 cifre
  indirizzo		VARCHAR(50) NULL,
  cap			VARCHAR(7) NULL,	#codice di avviamento postale massimo 7 cifre
  citta			VARCHAR(40) NULL,
  provincia		CHAR(2) NULL, #provincia sempre 2 lettere
  stato			VARCHAR(20) NULL,
  telefono		VARCHAR(15) NULL, #anche se è un numero lo tratto come testo
  email      	VARCHAR(40) NULL,
  tessera	    SMALLINT UNSIGNED NULL,
  scadenza		DATE NOT NULL
);