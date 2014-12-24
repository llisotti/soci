/** Lancia la creazione di tabelle
* Lancia altri file sql che creano le tabelle nel database
* @table iz_logging
* @col number id ID for sorting purposes
* @col date log_time what time the entry was made
* @col char(1) log_level logging level. Here: "!" for errors, "*" for main (job start/end), "+" for sub (e.g. switch to next owner), "-" for minor
* @col varchar2(4000) log_entry the log message
* @author Luca Lisotti
*/
source create_anagrafica.sql;
source create_presenze.sql;
source create_collegamenti.sql;