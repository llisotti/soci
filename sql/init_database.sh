#!/bin/bash

# File inizializzare un database
# il nome utente che crea il database deve essere dichirato in questo file
# il nome del database deve essere specificato nel file create_database.sql
# il nome delle tabelle deve essere specificato nel file create_tables.sql
EXIT_STATUS="0"
echo "\nATTENZIONE: Questa operazione inizializzera' il database \"soci\"\n" 
echo "******** VUOI VERAMENTE CONTINUARE ? (SI/N) ********"
read RISPOSTA
if [ $RISPOSTA != "SI" ];
then
    echo "Nessuna operazione effettuata"
    EXIT_STATUS="1"
    exit $EXIT_STATUS
else
    # ad esempio per connettersi come utente copernico: mysql -e "source create_database.sql" -u copernico
    mysql -e "source create_database.sql" -u copernico
    if [ $? -eq 0 ];
    then
        echo "Database inizializzato correttamente :)"
    else
        echo "Errore nella creazione del database :("
        EXIT_STATUS="2"
    fi
fi
exit $EXIT_STATUS