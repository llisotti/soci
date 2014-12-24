@echo off
REM File per aggiornare la procedura senza la necessità di rifare l'init del db
echo ATTENZIONE: Questa operazione aggiornera' la procedura "reset_members"
echo. 
echo ******** VUOI VERAMENTE CONTINUARE ? ********
SET /P RISPOSTA=(SI/N)
if "%RISPOSTA%"=="SI" goto esegui
echo Nessuna operazione effettuata
pause
exit

:esegui
D:\dati\xampp\mysql\bin\mysql -e "source reset_members.proc.sql" -u copernico
echo.
if %ERRORLEVEL% GEQ 1 echo ERRORE !
if %ERRORLEVEL% EQU 0 echo Tutto OK, la procedura e' stata aggiornata :)
pause
exit