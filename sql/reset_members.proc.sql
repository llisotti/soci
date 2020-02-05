/* Procedura registrata sul db soci */
USE soci;
#USE prova;
DELIMITER $$
DROP PROCEDURE IF EXISTS reset_members;
CREATE PROCEDURE reset_members()
BEGIN
UPDATE socio SET data_tessera=NULL, numero_tessera=NULL;
END$$
DELIMITER ;