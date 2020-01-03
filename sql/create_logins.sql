/**
Crea tabella per i login
*/

DROP TABLE IF EXISTS logins;

CREATE TABLE logins
(
 username		VARCHAR(20) NOT NULL,	#username (massimo 20 caratteri)
 psw				CHAR(32) NOT NULL		#Password criptata md5 (16 Byte)
) ENGINE=INNODB;

