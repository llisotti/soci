/* Procedura registrata sul db soci */
USE soci;
DELIMITER $$
DROP PROCEDURE IF EXISTS find_duplicates;
CREATE PROCEDURE find_duplicates()
BEGIN
DECLARE surname VARCHAR(50);
DECLARE name VARCHAR(50);
DECLARE born DATE;
DECLARE surname_temp VARCHAR(50);
DECLARE name_temp VARCHAR(50);
DECLARE born_temp DATE;
DECLARE duplicato VARCHAR (100);
DECLARE id_index SMALLINT UNSIGNED DEFAULT 0;
DECLARE id_scroll SMALLINT UNSIGNED DEFAULT 0;
SET id_index = (SELECT MAX(id) FROM anagrafica); /* Trovo l'id più alto */
SET id_scroll = id_index-1; /* Parto a scrollare dall'id immediatamente inferiore */
/* Se l'id_index non esiste decremento id_index e naturalmente id_scroll altrimenti ... */
label:WHILE (id_index != 1) DO
IF NOT EXISTS(SELECT id FROM anagrafica WHERE id=id_index) THEN
SET id_index = id_index-1;
SET id_scroll = id_index-1;
ITERATE label;
END IF;
/* ... seleziono il cognome, il nome e la data di nascita di id_index */
SELECT cognome FROM anagrafica WHERE id=id_index INTO surname;
SELECT nome FROM anagrafica WHERE id=id_index INTO name;
SELECT data_nascita FROM anagrafica WHERE id=id_index INTO born;
/* Se l'id_scroll non esiste decremento id_scroll e riprovo altrimenti ... */
label_2:WHILE (id_scroll != 1) DO
IF NOT EXISTS(SELECT id FROM anagrafica WHERE id=id_scroll) THEN
SET id_scroll = id_scroll-1;
ITERATE label_2;
END IF;
/* ... seleziono il cognome di id_scroll e lo confronto con quello di id_index */
SELECT cognome FROM anagrafica WHERE id=id_scroll INTO surname_temp;
IF (STRCMP(surname, surname_temp) = 0) THEN
/* Se sono uguali seleziono il nome di id_scroll e lo confronto con quello di id_index */
SELECT nome FROM anagrafica WHERE id=id_scroll INTO name_temp;
IF (STRCMP(name, name_temp) = 0) THEN
/* Se sono uguali seleziono la data di nascita di id_scroll e la confronto con quella di id_index */
SELECT data_nascita FROM anagrafica WHERE id=id_scroll INTO born_temp;
IF (born_temp = born) THEN
/* Se anche le date di nascita sono uguali allora è un doppio! */
SELECT CONCAT(surname, " ", name) INTO duplicato;
SELECT duplicato;
END IF;
END IF;
END IF;
SET id_scroll = id_scroll-1;
END WHILE;
SET id_index = id_index-1;
SET id_scroll = id_index-1;
END WHILE;
END $$
DELIMITER ;