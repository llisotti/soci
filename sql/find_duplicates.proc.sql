/* Procedura registrata sul db soci */
USE soci;
#USE prova;
DELIMITER $$
DROP PROCEDURE IF EXISTS find_duplicates;
CREATE PROCEDURE find_duplicates(IN action BOOLEAN)
BEGIN
DECLARE surname VARCHAR(30);
DECLARE name VARCHAR(30);
DECLARE temp VARCHAR(30);
DECLARE id_index SMALLINT UNSIGNED DEFAULT 0;
DECLARE id_scroll SMALLINT UNSIGNED DEFAULT 0;
DECLARE corretti SMALLINT UNSIGNED DEFAULT 0;
DECLARE _tessera SMALLINT UNSIGNED DEFAULT 0;
DECLARE _data DATE;
SET id_index = (SELECT MAX(member_id) FROM anagrafica); /* Trovo il member_id più alto */
SET id_scroll = id_index-1;
label:WHILE (id_index != 1) DO
IF NOT EXISTS(SELECT member_id FROM anagrafica WHERE member_id=id_index) THEN /* Se il member_id non esiste decremento member_id e itero di nuovo altrimenti ... */
SET id_index = id_index-1;
SET id_scroll = id_index-1;
ITERATE label;
END IF;
SELECT cognome FROM anagrafica WHERE member_id=id_index INTO surname; /* ... seleziono il cognome */
label_2:WHILE (id_scroll != 1) DO
IF NOT EXISTS(SELECT member_id FROM anagrafica WHERE member_id=id_scroll AND tessera IS NULL) THEN /* Se il member_id senza tessera non esiste decremento member_id e riprovo altrimenti ... */
SET id_scroll = id_scroll-1;
ITERATE label_2;
END IF;
SELECT cognome FROM anagrafica WHERE member_id=id_scroll  INTO temp; /* ... seleziono il cognome */
IF (temp=surname) THEN /* Se i due member_id hanno lo stesso cognome controllo i due nomi */
SELECT nome FROM anagrafica WHERE member_id=id_index INTO name;
SELECT nome FROM anagrafica WHERE member_id=id_scroll INTO temp;
IF (temp=name) THEN /* Se i due member_id hanno lo stesso nome allora è un duplicato */
SET corretti=corretti+1; 
IF (action) THEN /* Se ho chiamato la procedura con TRUE allora applico la correzione */
SELECT tessera FROM anagrafica WHERE member_id=id_index INTO _tessera;
SELECT data FROM presenze WHERE member_id=id_index INTO _data;
UPDATE anagrafica SET tessera=_tessera WHERE member_id=id_scroll;
UPDATE presenze SET data=_data WHERE member_id=id_scroll;
DELETE FROM anagrafica WHERE member_id=id_index;
DELETE FROM presenze WHERE member_id=id_index;
ELSE
SELECT member_id, cognome, nome FROM anagrafica WHERE member_id=id_index;
SELECT member_id, cognome, nome FROM anagrafica WHERE member_id=id_scroll;
SELECT '*****************************************';
END IF;
END IF;
END IF;
SET id_scroll = id_scroll-1;
END WHILE;
SET id_index = id_index-1;
SET id_scroll = id_index-1;
END WHILE;
IF (corretti=0) THEN
SELECT 'Nessuna' AS 'Identita\' duplicate';
ELSEIF(!action) THEN
#SELECT CONCAT(corretti, ' identita\' duplicate') AS '';
SELECT corretti AS 'Identita\' duplicate';
ELSE
SELECT corretti AS 'Identita\' corrette';
END IF;
END $$
DELIMITER ;