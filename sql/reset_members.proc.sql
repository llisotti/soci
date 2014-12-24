/* Procedura registrata sul db soci */
USE soci;
#USE prova;
DELIMITER $$
DROP PROCEDURE IF EXISTS reset_members;
CREATE PROCEDURE reset_members()
BEGIN
DELETE FROM presenze;
UPDATE anagrafica SET tessera=NULL;
END$$
DELIMITER ;