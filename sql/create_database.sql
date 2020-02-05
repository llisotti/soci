/* File sql per creare il database
il nome del database deve essere specificato in questo file
il nome delle tabelle deve essere specificato nel file create_tables.sql
*/

/* Creo il db soci con set di caratteri Unicode */
DROP DATABASE IF EXISTS soci;
CREATE DATABASE soci CHARACTER SET utf8 COLLATE utf8_general_ci;
USE soci;

/* Creo le tabelle */
source create_tables.sql;

/* Registro le procedure */
source expire_identities.proc.sql;
source reset_members.proc.sql;
/*source find_duplicates.proc.sql;*/