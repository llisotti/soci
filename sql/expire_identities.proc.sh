#!/bin/bash

# File per aggiornare la procedura senza la necessità di rifare l'init del db
EXIT_STATUS="0"
echo "\nATTENZIONE: Questa operazione aggiornera' la procedura \"reset_members\"\n" 
echo "******** VUOI VERAMENTE CONTINUARE ? (SI/N) ********"
read RISPOSTA
if [ $RISPOSTA != "SI" ];
then
    echo "Nessuna operazione effettuata"
    EXIT_STATUS="1"
    exit $EXIT_STATUS
else
    mysql -e "source expire_identities.proc.sql" -u copernico
    if [ $? -eq 0 ];
    then
        echo "Tutto OK, la procedura e' stata aggiornata :)"
    else
        echo "Errore nel tentativo di aggiornare la procedura :("
        EXIT_STATUS="2"
    fi
fi
exit $EXIT_STATUS