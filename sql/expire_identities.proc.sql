/* Procedura registrata sul db soci */
USE soci;
#USE prova;
DELIMITER $$
DROP PROCEDURE IF EXISTS expire_identities;
CREATE PROCEDURE expire_identities(IN action BOOLEAN)
BEGIN
DECLARE member INT DEFAULT 0;
DECLARE identities_expired INT DEFAULT 0;
SET @message='OCCORRE RIAVVIARE IL SERVER MYSQL !';
SET @years_to_expire=5;
SET @date_expire_identities=STR_TO_DATE(CONCAT(31,12,YEAR(CURDATE())-@years_to_expire), '%d%m%Y');
SET @num_identities=(SELECT COUNT(*) FROM anagrafica);
WHILE (member != @num_identities) DO
SET @drop_date=(SELECT scadenza FROM socio LIMIT member, 1);
SET @codice_fiscale=(SELECT cf FROM anagrafica LIMIT member, 1);
IF (@drop_date <= @date_expire_identities) THEN /*IF se identita' viene cancellata (sia veramente che simulando) */
IF action THEN /*IF se cancello veramente */
DELETE FROM anagrafica WHERE cf=@codice_fiscale;
DELETE FROM socio WHERE cf=@codice_fiscale;
END IF; /*END IF se cancello veramente */
SELECT anagrafica.cf, cognome, nome, scadenza FROM anagrafica INNER JOIN socio WHERE anagrafica.cf=@codice_fiscale AND socio.cf=@codice_fiscale ;
SET identities_expired = identities_expired+1;
END IF; /*END IF se identita' viene cancellata (sia veramente che simulando) */
SET member = member+1;
END WHILE;
IF action THEN
SELECT identities_expired, @message;
ELSE
SELECT identities_expired;
END IF;
END$$
DELIMITER ;