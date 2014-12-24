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
SET @drop_date=(SELECT scadenza FROM anagrafica LIMIT member, 1);
SET @member_id=(SELECT member_id FROM anagrafica LIMIT member, 1);
IF (@drop_date <= @date_expire_identities) THEN
IF action THEN
DELETE FROM anagrafica WHERE member_id=@member_id;
DELETE FROM presenze WHERE member_id=@member_id;
SET identities_expired = identities_expired+1;
ELSE
SELECT member_id, cognome, nome, scadenza FROM anagrafica LIMIT member, 1;
END IF;
END IF;
SET member = member+1;
END WHILE;
IF action THEN
SELECT identities_expired, @message;
END IF;
END$$
DELIMITER ;