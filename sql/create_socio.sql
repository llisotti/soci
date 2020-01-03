/**
Crea tabella socio
*/

DROP TABLE IF EXISTS socio;

CREATE TABLE socio
(
  cf				CHAR(16) NOT NULL,		#codice fiscale sempre 16 cifre
  scadenza		DATE NOT NULL,			#Data della scadenza iscrizione database (+ DROP_IDENTITY anni da ultima data_tessera)
  data_tessera		DATE,					#Data del tesseramento per anno corrente
  numero_tessera	SMALLINT UNSIGNED NULL UNIQUE,
  adesioni			BIT(8) NOT NULL,			#Campo bit che contiene diversi flag
  firma			VARCHAR(100) NOT NULL,	#CognomeNome-datanascita
PRIMARY KEY (cf)
) ENGINE=INNODB;

/**Configurazione bit adesioni
BIT 0: accettazione diffusione Nome e Cognome (0: non accettato; 1: accettato)
BIT 1: iscrizione newletter (0: non iscritto; 1: iscritto) 
BIT 2: maggiorenne o minorenne (0: maggiorenne; 1: minorenne)
BIT 3: disponibile
BIT 4: disponibile
BIT 5: disponibile
BIT 6: disponibile
BIT 7: disponibile
*/

