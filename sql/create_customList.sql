/**
Crea tabella customList
*/

DROP TABLE IF EXISTS customList;

CREATE TABLE customList
(
  cf				CHAR(16) NOT NULL,		#codice fiscale sempre 16 cifre
  cognome		VARCHAR(30) NOT NULL,	
  nome			VARCHAR(30) NOT NULL,	
  email      		VARCHAR(40) NOT NULL,
PRIMARY KEY (cf)
) ENGINE=INNODB;

