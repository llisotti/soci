/* Procedura registrata sul db soci */
USE soci;
#USE prova;
DELIMITER $$
DROP PROCEDURE IF EXISTS reset_members;
CREATE PROCEDURE reset_members()
BEGIN
UPDATE presenze SET data=NULL;
UPDATE anagrafica SET tessera=NULL;
END$$
DELIMITER ;