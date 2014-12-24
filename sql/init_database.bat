@echo off
REM File inizializzare un database
REM il nome utente che crea il database deve essere dichirato in questo file
REM il nome del database deve essere specificato nel file create_database.sql
REM il nome delle tabelle deve essere specificato nel file create_tables.sql
echo ATTENZIONE: Questa operazione inizializzera' il database "soci"
echo. 
echo ******** VUOI VERAMENTE CONTINUARE ? ********
SET /P RISPOSTA=(SI/N)
if "%RISPOSTA%"=="SI" goto esegui
echo Nessuna operazione effettuata
pause
exit

:esegui
REM ad esempio per connettersi come utente copernico: mysql -e "source create_database.sql" -u copernico
D:\dati\xampp\mysql\bin\mysql -e "source create_database.sql" -u copernico
if %ERRORLEVEL% EQU 0 echo Database inizializzato correttamente :)
pause