<?php
include "definitions.inc.php";
include "classes.inc.php";

/** @defgroup Macro Macro
 *  Gruppo di macro
 *  @{
 */


/**
 * Parametri di connessione al database soci
 */
define ("SOCI_DBCONNECTION", "mysql:host=localhost;dbname=soci;charset=utf8");
//define ("SOCI_DBCONNECTION", "mysql:host=localhost;dbname=prova;charset=utf8"); //Per agire sul database di prova


/**
 * Numero di soci da visualizzare nella tabella iniziale
 */
define ("MEMBERS_RECENT_MAX", 20);


/**
 * Numero di anni consecutivi dopo i quali viene rimossa l'identità dal database
 */
define ("DROP_IDENTITY", "+5 years");


/**
 * Versione del software
 */
define ("VERSION", "GESTIONE SOCI 2.1");


/**
 * Nome che appare nel campo from della newsletter
 */
define ("FROM_NAME", "Osservatorio Copernico");


/**
 * Indirizzo che appare nel campo from della newsletter
 */
define ("FROM_ADDRESS", "localhost@osservatoriocopernico");


/**
 * Percorso per il backup del database
 */
define("BACKUP_PATH", "D:\\\\dati\\\\xampp\\\\htdocs\\\\soci\\\\doc\\\\");


/**
 * Dimensione massima (in KByte) per il file di log
 */
define("LOGFILE_MAXSIZE", 1024);


/**
 * Percorso dove salvare il file di log
 */
define("LOGFILE_PATH", "D:\\\\dati\\\\xampp\\\\htdocs\\\\soci\\\\doc\\\\");




/** @} */



/**
 * @class Message
 * @brief Ulteriori mesaggi di ritorno
 */
class Message extends Messages {    
    const E_DBCONNECTIONFAILURE=10; /**< Errore di connessione al database */
    const E_BADSEX=11; /**< Sesso diverso da 'M' o 'F' */
    const E_MODIFYDENIED=12; /**< Tentativo di modifica non permesso */
    const E_NOFIELDEXISTENT=13; /**< Il campo della classe richiesto non esiste */
    const E_WRONGDATEFORMAT=14; /**< Formato data non valido: diverso da GG/MM/AAAA */
} 


/**
 * @class Socio_Copernico
 * @brief Rappresenta un'identità dell'osservatorio Copernico.
 */
class Socio_Copernico extends Person {
    const EXCEPTION_MESSAGE="Errore socio copernico";
    private $id; /**< Identificatore univoco di un'identità */
    private $indirizzo; /**< Indirizzo di residenza */
    private $cap; /**< Codoce di Avviamento Postale di residenza */
    private $citta; /**< Città di residenza */
    private $provincia; /**< Provincia di residenza */
    private $stato; /**< Stato di residenza */
    private $telefono; /**< Numero di telefono */
    private $email; /**< Indirizzo email: obbligatorio per iscrizione alla Newsletter */
    private $tessera; /**< Numero tessera socio per l'anno corrente */
    private $data_tessera; /**< Data in cui è stata effettuata la tessera */
    private $data_iscrizione; /**< Data di iscrizione per la prima volta ovvero nuova identità */
    private $scadenza_id; /**< Scadenza identità: cancellazione dal database */

    /* Array di controllo per l'esistenza del campo */
    private $fields=array("cognome",
                                    "nome",
                                    "data_nascita",
                                    "luogo_nascita",
                                    "sesso",
                                    "codice_fiscale",
                                    "id",
                                    "indirizzo",
                                    "cap", 
                                    "citta",
                                    "provincia",
                                    "stato",
                                    "telefono",
                                    "email",
                                    "tessera",
                                    "data_tessera",
                                    "data_iscrizione",
                                    "scadenza_id"
                        );
	
	
    /** 
     * @brief Costruttore
     * @details Invoco costruttore padre
     */
    public function __construct($cognome, $nome) {
        try {
            parent::__construct($cognome, $nome);
        } catch (MyException $exc) {
            die($exc->show());
        }
    }

    
	
    /** 
     * @brief Mutator
     * @details Inizializza i valori dei campi di una persona
     * @param[in] string $name Nome del campo da modificare
     * @param[in] string $value Valore del campo da modificare
     * @attention Le stringhe non possono essere vuote o contenere numeri
     */
    public function __set($name, $value) {
        
        if (!in_array($name, $this->fields))
            throw new MyException(self::EXCEPTION_MESSAGE, Message::E_NOFIELDEXISTENT);
        $control_input=array("cognome" => "stringchar", "nome" => "stringchar", "luogo_nascita" => "stringchar", "provincia" => "stringchar", "stato" => "stingchar");
        $controlled_param=array($name, $value);        
        $validator= new InputValidate($control_input);
        if($name=="nome" || $name=="cognome") {
            try{
                $validator->isNotVoid($value);
            } catch (MyException $ex) {
                die($ex->show());
            }      
        }
        
        try {
            $validator->isValidType($controlled_param);
        } catch (MyException $exc) {
            die($exc->show());
        }
        unset($validator);
        $this->$name=$value;
        return Messages::ISOK;
    }
	
	
    /** 
     * @brief Accessor
     * @details Ritorna i valori dei campi di una persona
     * @param[out] string $name Nome del campo di cui si richiede il valore
     */
    public function __get($name) {
        /* Controllo se il campo richiesto esiste */
        if (in_array($name, $this->fields))
            return $this->$name;
        else
            throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_NOFIELDEXISTENT);
    }
}
?>