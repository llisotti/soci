<?php
/**
 * @file classes.inc.php
 * @brief Definizione di classi generiche
 * @version 1.0
 * @author Luca Lisotti
 * @attention Solo PHP 5
 */



/**
 * @class Messages
 * @brief Messaggi di ritorno
 */
class Messages {
    const ISOK=0; /**< Nessun errore */
    const E_GENERIC=1; /**< Errore generico */
    const E_VOIDSTRING=2; /**< La stringa è vuota */
    const E_NUMSTRING=3; /**< La stringa contiene numeri o caratteri non ammessi */
    const E_CHARSTRING=4; /**< La stringa contiene lettere */
    const E_INVALIDINT=5; /**< Il numero non è un intero */
    const E_INVALIDEMAIL=6; /**< La mail non è valida */
    const E_LENGHTSTRING=7; /**< Lunghezza stringa errata: minore, diversa o maggiore di quanto richiesto */
}



/**
 * @class MyException
 * @brief Eccezioni con stringa di messaggio personalizzato
 */
class MyException extends Exception {
    /** 
     * @brief Costruttore
     * @details Inizializza una nuova eccezione
     * @param[in] string $message Messaggio custom
     * @param[in] int $code Codice numerico messaggio custom
     * @param[in] Exception $previous Mostra l'eccezione precedente se eccezioni annidate
     * @attention I parametri $message e $code sono obbligatori mentre $previus è facoltativo
     * @code
     * //Creo un'eccezione
     * if($status!=0)
     *      throw new MyException("Impossibile creare l'oggetto", $status);
     * @endcode
     */
    public function __construct($message, $code, Exception $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }

    /** 
     * @brief Stampa l'eccezione
     * @param[in] bool $hide_path Nasconde o meno il path del file che ha generato l'eccezione
     * @note Stampa a video l'eccezione nel seguente formato
     * @li se $hide_path non specificato o FALSE: <em>Path del file.riga: messaggio custom[numero di errore classe Messages]</em>
     * @li se $hide_path=TRUE: <em>Messaggio custom[numero di errore classe Messages]</em>
     * 
     * @code
     * try
     * {
     *   $member=new Socio_Copernico($_POST['cognome'], $_POST['nome']); 
     * } catch (MyException $ex) {
     *   die($ex->show()); //Mostro anche il path
     * }
     * @endcode
     * @code
     * die($ex->show(TRUE)); //Non mostro il path
     * @endcode
     */
    public function show($hide_path=FALSE) {
        if(!$hide_path)
            return "{$this->file}.{$this->line}: {$this->message}[{$this->code}]"; //Path del file.riga: messaggio custom[numero di errore classe Messages]
        else
            return "{$this->message}[{$this->code}]"; //Messaggio custom[numero di errore classe Messages]
    }
}



/**
 * @class InputValidate
 * @brief Validatore di dati provenienti dai form 
 */
class InputValidate {
    const EXCEPTION_MESSAGE="Errore validazione input";
    private $ctrl; /**< Array di controllo */
    private $permitted_chars; /**< Array di caratteri permessi nelle stringchar*/
    
    /** 
     * @brief Costruttore
     * @details Inizializza un nuovo validatore
     * @param[in] array $control Array di controllo
     * @attention L'array di controllo deve essere associativo: nome_variabile=>tipo_variabile\n
     * I tipo_variabile permessi sono:
     * @li @c stringnum: stringa che può contenere solo numeri
     * @li @c stringchar: stringa che può contenere solo lettere più lo spazio ed il singolo apice (')
     * @li @c int: numero intero
     * @li @c email: email
     * 
     * Se esiste $POST['cognome'] e cognome deve essere una stringa senza numeri allora l'array deve contenere "cognome"=>"stringchar"\n
     * Se esiste $POST['cap'] e cap deve essere una stringa di soli numeri allora l'array deve contenere "cap"=>"stringnum"\n
     * @code
     * //Creo l'array di controllo
     * $mio_array=array("cognome" => "stringchar", "cap" => "stringnum");
     * //Creo un nuovo validatore
     * $validator=new InputValidate($mio_array);
     * @endcode
     */
    public function __construct(&$control) {
        /* Nelle stringchar permetto i seguenti caratteri: spazio, ', lettere accentate */
        $this->permitted_chars=array(" ","'","à","è","é","ì","ò","ù");
        
        $this->ctrl=$control;
    }
    
    
    /** 
     * @brief Aggiorna l'array di controllo
     * @param[in] array $new_control Array di controllo
     * @note Per l'array di controllo valgono le stesse regole viste nel costruttore\n
     * @code
     * //Aggiorno l'array di controllo
     * $mio_array=array("email" => "email");
     * $validator->updateControl($control_input);
     * @endcode
     */
    public function updateControl(&$new_control) {
        $this->ctrl=$new_control;
    }
    
    
    /** 
     * @brief Validatore di tipo 
     * @details Controlla che il tipo di variabile sia corretto
     * @param[in] string $gpc Variabile superglobale GET, POST o COOKIE
     * @return Messages
     * @note I tipi che si possono controllare sono:
     * @li @c stringnum
     * @li @c stringchar
     * @li @c int
     * 
     * @code
     * $status=$validator->isValidType($_POST);
     * @endcode 
     */
    public function isValidType(&$gpc) {
        foreach ($this->ctrl as $key => $value) {//Ciclo nell'array di controllo
            foreach ($gpc as $gpckey => $gpcvalue) {//Ciclo nell'array da controllare (solitamente GET, POST o COOKIE)
                if((string)$gpckey!=$key || empty($gpcvalue)) //Non eseguo controlli anche se l'input da controllare è vuoto. (OCCHIO: E' necessario trasformare in stringa $gpckey !!)
                    continue;

                switch ($value) {
                    case 'stringnum': //Stringa di soli numeri
                        if(!ctype_digit($gpcvalue))
                            throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_CHARSTRING);
                        break;
                    case 'stringchar': //Stringa senza numeri (sono permessi altri caratteri come ad esempio gli apostrofi (cognome D'Angelo)
                        if(!ctype_alpha(str_replace($this->permitted_chars, '', $gpcvalue)))
                            throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_NUMSTRING);
                        break;
                    case 'int': //Numero intero
                        if(!filter_var($gpcvalue, FILTER_VALIDATE_INT))
                            throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_INVALIDINT);
                        break;
                    default:
                        throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_GENERIC);
                        break;
                }
            }
        }        
        return Messages::ISOK;
    }
    
    
    /** 
     * @brief Validatore di lunghezza 
     * @details Controlla che la lunghezza di una variabile sia corretta
     * @param[in] string $string Variabile da controllare
     * @param[in] int $lenght Valore della lunghezza da controllare
     * @param[in] int $how Confronto tra lunghezze\n
     * @li @c 0 se lunghezza deve essere inferiore di $lenght
     * @li @c 1 se lunghezza deve essere uguale a $lenght
     * @li @c 2 se lunghezza deve essere maggiore di $lenght\n
     * @return Messages
     * 
     * @code
     * //Controllo che la lunghezza del codice fiscale sia di 16 caratteri alfanumerici esatti
     * isValidLenght($_POST['cf'], 16, 1);
     * @endcode 
     */
    public function isValidLenght($string, $lenght, $how) {
        if(empty($string))
            return Message::ISOK;
        switch ($how) {
            case 0: //Lunghezza stringa deve essere inferiore
                if(strlen($string) >= $lenght)
                    throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_LENGHTSTRING);
                break;
            case 1: //Lunghezza stringa deve essere uguale
                if(strlen($string) != $lenght)
                    throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_LENGHTSTRING);
                break;
            case 2: //Lunghezza stringa deve essere uguale
                if(strlen($string) <= $lenght)
                    throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_LENGHTSTRING);
                break;
            default:
                throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_GENERIC);
                break;
        }
        return Messages::ISOK;    
    }
    
    
    /** 
     * @brief Non vuoto 
     * @details Controlla che una stringa non sia vuota
     * @param[in] string $param Stringa da controllare
     * @return Messages
     */
    public function isNotVoid($param) {
        if (empty ($param)) //Errore se stringa vuota
            throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_VOIDSTRING);      
        return Message::ISOK;   
    }
    
    
    /** 
     * @brief Validatore di formato 
     * @details Controlla che la variabile sia del formato corretto
     * @param[in] string $gpc Variabile superglobale GET, POST o COOKIE
     * @return Message
     * @note I formati che si possono controllare sono:
     * @li @c email
     * 
     * @code
     * $mio_array=array("email" => "email");
     * $status=$validator->isValidFormat($_GET);
     * @endcode 
     */
    public function isValidFormat(&$gpc) {
        foreach ($this->ctrl as $key => $value) {//Ciclo nell'array di controllo
            foreach ($gpc as $gpckey => $gpcvalue) {//Ciclo nell'array da controllare (solitamente GET, POST o COOKIE)
                if($gpckey!=$key || empty($gpcvalue)) //Non eseguo controlli anche se l'input da controllare è vuoto
                    continue;

                switch ($value) {
                    case 'email': //La stringa deve essere un'email
                        if(!filter_var($gpcvalue, FILTER_VALIDATE_EMAIL))
                            throw new MyException(self::EXCEPTION_MESSAGE, Messages::E_INVALIDEMAIL);
                        break;
                }
            }
        }
        return Messages::ISOK;
    }
}



/**
 * @class InputFormat
 * @brief Formattatore di dati provenienti dai form 
 */
class InputFormat {
    private $ctrl; /**< Array di controllo */
    
    /** 
     * @brief Costruttore
     * @details Inizializza un nuovo formattatore
     * @param[in] array $control Array di controllo
     * @attention L'array di controllo deve essere associativo: nome_variabile=>tipo_variabile\n
     * I tipo_variabile permessi sono:
     * @li @c UC: UpperCase - Tutti i caratteri diventeranno maiuscoli
     * @li @c LC: LowerCase - Tutti i caratteri diventeranno minuscoli
     * @li @c FUC: FirstUpperCase - Solamente il primo carattere della stringa e il primo carattere dopo ogni spazio diventerà maiuscolo. Gli altri caratteri saranno tutti minuscoli
     * 
     * Se esiste $POST['cognome'] e cognome deve essere una stringa con il primo carattere maiusolo allora l'array deve contenere "cognome"=>"FUC"\n
     * Se esiste $POST['cap'] e cap deve essere una stringa con tutti caratteri maiuscoli allora l'array deve contenere "cap"=>"UC"\n
     * @code
     * //Creo l'array di controllo
     * $mio_array=array("cognome" => "FUC", "cap" => "UC");
     * //Creo un nuovo validatore
     * $formatter=new InputValidate($mio_array);
     * @endcode
     */
    public function __construct(&$control) {
        $this->ctrl=$control;
    }
    
    
     /** 
     * @brief Formattatore 
     * @details Formatta le variabili secondo quanto dichiarato nell'array di controllo
     * @param[in] string $gpc Variabile superglobale GET, POST o COOKIE
     * @code
     * $formatter->format($_POST);
     * @endcode 
     */
    public function format(&$gpc) {
        foreach ($this->ctrl as $key => $value) { //Ciclo nell'array di controllo
            foreach ($gpc as $gpckey => $gpcvalue) { //Ciclo nell'array da controllare (solitamente GET, POST o COOKIE)
                if($gpckey!=$key || empty($gpcvalue)) //Non eseguo controlli anche se l'input da controllare è vuoto
                    continue;
                
                switch ($value) {
                    case 'UC': //Upper Case
                        $gpc[$gpckey]=strtoupper($gpc[$gpckey]);
                        break;
                    case 'FUC': //First Upper Case
                        $gpc[$gpckey]=strtolower($gpc[$gpckey]);
                        $gpc[$gpckey]=ucwords($gpc[$gpckey]);
                        break;
                    case 'LC': //Lower Case
                        $gpc[$gpckey]=strtolower($gpc[$gpckey]);
                        break;
                    default:
                        break;
                }
            }
        }
    } 
} 



/**
 * @class Logger
 * @brief Rappresenta un logger
 * @attention Necessita della classe MyException
 */
class Logger {
    private $file_path; /**< Percorso dove verrà salvato il file di log */
    private $file_size_max; /**< Dimensione massima del file di log in KByte */
    private $last_logfile_name; /**< Nome del file di log se già esistente all'avvio di una snuova sessione */
    private $last_logfile_size; /**< Dimensione del file di log se già esistente all'avvio di una snuova sessione */
    private $iterator; /**< Oggetto per il controllo della directory del file di log */
    private $is_new_logger_file; /**< Indica se il file di log è stato appena creato all'avvio di una nuova sessione */


    /** 
     * @brief Costruttore
     * @details Inizializza un file di log del tipo AAAAMMGG.log
     * @param[in] string $file_path Percorso del file di log
     * @param[in] int $file_size_max Dimensione massima del file in KByte
     * @code
     * //Creo un logger con dimensione massima 1MByte
     * log=new Logger(MYPATH, 1024);
     * @endcode
     */
    public function __construct($file_path, $file_size_max) {
        $this->file_path=$file_path;
        $this->file_size_max=$file_size_max;
        $this->is_new_logger_file=FALSE;        
        /* Verifico se la directory esiste */
        try {
            $this->iterator=new DirectoryIterator($this->file_path);
        } catch (Exception $ex) {
            throw new MyException("Errore creazione oggetto Logger: ".$ex->getMessage(), 0);
        }        
        /* Se tutto ok inizializzo il file di log */
        $this->initLogFile();
        unset($this->iterator); //Necessario perchè DirectoryIterator non si serializza quindi l'oggetto logger non potrei passarlo nella sessione
    }
    
    
    /** 
     * @brief Inizializza un file di log 
     * @details Se il file esiste ma è maggiore di $file_size_max oppure non esiste ne crea uno nuovo\n
     * Se il file esiste ed è minore di $file_size_max oontinua ad utilizzarlo
     */
    private function initLogFile() {
        /* Verifico, se esiste, qual'è l'ultimo file di log utile tra quelli presenti nella directory file_path */
        $accessed=0;
        foreach ($this->iterator as $fileinfo) {
            if($fileinfo->isFile() && $fileinfo->getExtension()=="log") { //Se è un file di log
                if($fileinfo->getCTime() > $accessed) { //Se il file di log è l'ultimo...
                    $accessed = $fileinfo->getAtime();
                    $this->last_logfile_name=$fileinfo->getBasename(); //...ne catturo il nome...
                    $this->last_logfile_size=$fileinfo->getSize(); //...e il size
                }
            }           
        }
        if(!$accessed || $this->last_logfile_size >= LOGFILE_MAXSIZE*$this->file_size_max) //Se non esiste alcun file di log oppure supera il file_size_max (in KByte) allora ne creo uno nuovo            
            $this->is_new_logger_file=TRUE;
        else
            $this->is_new_logger_file=FALSE;
    }


    /** 
     * @brief Scrive una riga nel file di log 
     * @param[in] int $type_message Stringa da controllare \n
     * $type_message può valere
     * @li @c 0: ERRORE - Messaggio di errore
     * @li @c 1: WARNING - Messaggio di warning
     * @li @c 2: INFO - Messaggio d'informazione
     * @li @c 3: DEBUG - Messaggio di debug
     * @note I messaggi sono del tipo \n
     * [data ora] TAB PID\@pagina\@linea di codice che hanno inviato il log TAB tipomessaggio messaggio
     * @return Messages
     */
    private function writeLog($type_message, $message, $caller_file, $caller_line) {
        $date=new DateTime(); //Aggiorno qui la data e l'ora dell'evento, è più preciso
        if($this->is_new_logger_file)
            $fp=fopen($this->file_path.$date->format('Ymd').".log", "a");
        else
            $fp= fopen($this->file_path.$this->last_logfile_name, "a");
        switch($type_message) {
           case 0:
               fwrite($fp, "[".$date->format('d-m-Y H:i:s')."]\t".getmypid()."@".$caller_file."@".$caller_line."\tERRORE ".$message.PHP_EOL);
               break;
           case 1:
               fwrite($fp, "[".$date->format('d-m-Y H:i:s')."]\t".getmypid()."@".$caller_file."@".$caller_line."\tWARNING ".$message.PHP_EOL);
               break;
           case 2:
               fwrite($fp, "[".$date->format('d-m-Y H:i:s')."]\t".getmypid()."@".$caller_file."@".$caller_line."\tINFO ".$message.PHP_EOL);
               break;
           case 3:
               fwrite($fp, "[".$date->format('d-m-Y H:i:s')."]\t".getmypid()."@".$caller_file."@".$caller_line."\tDEBUG ".$message.PHP_EOL);
               break;
       }
    }
    
    
    /** 
     * @brief Scrive una riga di ERRORE nel file di log 
     */
    public function logError($param) {
        $caller=debug_backtrace();
        $caller_file=basename($caller[0]["file"]);
        $this->writeLog(0, $param, $caller_file, $caller[0]["line"]);
    }
    
    
    /** 
     * @brief Scrive una riga di INFO nel file di log 
     */
    public function logInfo($param) {
        $caller=debug_backtrace();
        $caller_file=basename($caller[0]["file"]);
        $this->writeLog(2, $param, $caller_file, $caller[0]["line"]);
    }
    
    
    /** 
     * @brief Scrive una riga di WARNING nel file di log 
     */
    public function logWarning($param) {
        $caller=debug_backtrace();
        $caller_file=basename($caller[0]["file"]);
        $this->writeLog(1, $param, $caller_file, $caller[0]["line"]);
    }
    
    
    /** 
     * @brief Scrive una riga di DEBUG nel file di log 
     */
    public function logDebug($param) {
        $caller=debug_backtrace();
        $caller_file=basename($caller[0]["file"]);
        $this->writeLog(3, $param, $caller_file, $caller[0]["line"]);
    }
}


/**
 * @class Person
 * @brief Rappresenta l'entità di una persona
 * @details Contiene i requisiti minimi che una persona deve avere
 * @attention Necessita della classe InputValidate
 */
abstract class Person { 
    protected $cognome; /**< Cognome di una persona */
    protected $nome; /**< Nome di una persona */
    protected $data_nascita; /**< Data di nascita di una persona */
    protected $luogo_nascita; /**< Luogo di nascita di una persona */
    protected $sesso; /**< Sesso di una persona */
    protected $codice_fiscale; /**< Codice fiscale di una persona */
    
    /** 
     * @brief Costruttore
     * @details Inizializza una persona
     * @param[in] string $cognome Rappresenta il cognome della Persona
     * @param[in] string $nome Rappresenta il nome della Persona
     * @return Messages
     * @attention Le stringhe non possono essere vuote, contenere numeri o altri caratteri non ammessi da un Cognome e un Nome di persona \n
     */
    public function __construct($cognome, $nome) {      
        /* Controllo che Cognome e Nome siano di sole lettere e non siano vuoti */
        $control_input=array($cognome=>"stringchar", $nome=>"stringchar");
        $controlled_param=array($cognome, $nome);
        $validator= new InputValidate($control_input);        
        try {
            $validator->isNotVoid($cognome);
            $validator->isNotVoid($nome);
            $validator->isValidType($controlled_param);
        } catch (MyException $exc) {
            die($exc->show());
        }
        /* Se tutto OK inserisco Cognome e Nome */
        $this->cognome=$cognome;
        $this->nome=$nome;
    }
}



/**
 * @class DataForSelect
 * @brief Classe per popolare i campi <select>
 */
class DataForSelect {
    private $days; /**< Giorni del mese */
    private $months; /**< Mesi dell'anno */
    private $province; /**< Province italiane */
    private $states; /**< Stati nel mondo */
    private $day_not_set; /**< Valore del giorno quando non settato */
    private $month_not_set; /**< Valore del mese quando non settato */
    
    
    /** 
     * @brief Costruttore
     * @details Inizializza giorni, mesi, province e stati
     * @param[in] string $d_not_set Valore del giorno selezionato
     * @param[in] string $m_not_set Valore del mese selezionato
     * @note Se $d_not_set vale NULL nel menù a discesa non appare la stringa GG
     * @note Se $m_not_set vale NULL nel menù a discesa non appare la stringa MM
     * @code
     * //Creo l'oggetto senza che nel menù a discesa appaiano GG e MM
     * $date_not_null=new DatesForSelect(NULL, NULL)
     * //Oppure creo l'oggetto in modo che nel menù a discesa appaiano anche GG e MM
     * $date=new DatesForSelect();
     * @endcode
     */
    public function __construct($d_not_set="GG", $m_not_set="MM") {
        $this->days=range(1, 31);
        $this->months=range(1, 12);
        $this->day_not_set=$d_not_set;
        $this->month_not_set=$m_not_set;
    	$this->province = array (
    	"Agrigento" => "AG",
    	"Alessandria" => "AL",
    	"Ancona" => "AN",
    	"Aosta" => "AO",
    	"Arezzo" => "AR",
    	"Ascoli Piceno" => "AP",
    	"Asti" => "AT",
    	"Avellino" => "AV",
    	"Bari" => "BA",
    	"Barletta-Andria-Trani" => "BT",
    	"Belluno" => "BL",
    	"Benevento" => "BN",
    	"Bergamo" => "BG",
    	"Biella" => "BI",
    	"Bologna" => "BO",
    	"Bolzano" => "BZ",
    	"Brescia" => "BS",
    	"Brindisi" => "BR",
    	"Cagliari" => "CA",
    	"Caltanissetta" => "CL",
    	"Campobasso" => "CB",
    	"Carbonia-Iglesias" => "CI",
    	"Caserta" => "CE",
    	"Catania" => "CT",
    	"Catanzaro" => "CZ",
    	"Chieti" => "CH",
    	"Como" => "CO",
    	"Cosenza" => "CS",
    	"Cremona" => "CR",
    	"Crotone" => "KR",
    	"Cuneo" => "CN",
    	"Enna" => "EN",
    	"Fermo" => "FM",
    	"Ferrara" => "FE",
    	"Firenze" => "FI",
    	"Foggia" => "FG",
    	"Forlì-Cesena" => "FC",
    	"Frosinone" => "FR",
    	"Genova" => "GE",
    	"Gorizia" => "GO",
    	"Grosseto" => "GR",
    	"Imperia" => "IM",
    	"Isernia" => "IS",
    	"L'Aquila" => "AQ",
    	"La Spezia" => "SP",
    	"Latina" => "LT",
    	"Lecce" => "LE",
    	"Lecco" => "LC",
    	"Livorno" => "LI",
    	"Lodi" => "LO",
    	"Lucca" => "LU",
    	"Macerata" => "MC",
    	"Mantova" => "MN",
    	"Massa-Carrara" => "MS",
    	"Matera" => "MT",
    	"Medio Campidano" => "VS",
    	"Messina" => "ME",
    	"Milano" => "MI",
    	"Modena" => "MO",
    	"Monza e della Brianza" => "MB",
    	"Napoli" => "NA",
    	"Novara" => "NO",
    	"Nuoro" => "NU",
    	"Ogliastra" => "OG",
    	"Olbia-Tempio" => "OT",
    	"Oristano" => "OR",
    	"Padova" => "PD",
    	"Palermo" => "PA",
    	"Parma" => "PR",
    	"Pavia" => "PV",
    	"Perugia" => "PG",
    	"Pesaro e Urbino" => "PU",
    	"Pescara" => "PE",
    	"Piacenza" => "PC",
    	"Pisa" => "PI",
    	"Pistoia" => "PT",
    	"Pordenone" => "PN",
    	"Potenza" => "PZ",
    	"Prato" => "PO",
    	"Ragusa" => "RG",
    	"Ravenna" => "RA",
    	"Reggio Calabria" => "RC",
    	"Reggio Emilia" => "RE",
    	"Rieti" => "RI",
    	"Rimini" => "RN",
    	"Roma" => "RM",
    	"Rovigo" => "RO",
    	"Salerno" => "SA",
    	"Sassari" => "SS",
    	"Savona" => "SV",
    	"Siena" => "SI",
    	"Siracusa" => "SR",
    	"Sondrio" => "SO",
    	"Taranto" => "TA",
    	"Teramo" => "TE",
    	"Terni" => "TR",
    	"Torino" => "TO",
    	"Trapani" => "TP",
    	"Trento" => "TN",
    	"Treviso" => "TV",
    	"Trieste" => "TS",
    	"Udine" => "UD",
    	"Varese" => "VA",
    	"Venezia" => "VE",
    	"Verbano-Cusio-Ossola" => "VB",
    	"Vercelli" => "VC",
    	"Verona" => "VR",
    	"Vibo Valentia" => "VV",
    	"Vicenza" => "VI",
    	"Viterbo" => "VT"
    	   );
    	
    	$this->states = array(
    	    'US'	=>	'United States',
    	    'AF'	=>	'Afghanistan',
    	    'AL'	=>	'Albania',
    	    'DZ'	=>	'Algeria',
    	    'AS'	=>	'American Samoa',
    	    'AD'	=>	'Andorra',
    	    'AO'	=>	'Angola',
    	    'AI'	=>	'Anguilla',
    	    'AQ'	=>	'Antarctica',
    	    'AG'	=>	'Antigua And Barbuda',
    	    'AR'	=>	'Argentina',
    	    'AM'	=>	'Armenia',
    	    'AW'	=>	'Aruba',
    	    'AU'	=>	'Australia',
    	    'AT'	=>	'Austria',
    	    'AZ'	=>	'Azerbaijan',
    	    'BS'	=>	'Bahamas',
    	    'BH'	=>	'Bahrain',
    	    'BD'	=>	'Bangladesh',
    	    'BB'	=>	'Barbados',
    	    'BY'	=>	'Belarus',
    	    'BE'	=>	'Belgium',
    	    'BZ'	=>	'Belize',
    	    'BJ'	=>	'Benin',
    	    'BM'	=>	'Bermuda',
    	    'BT'	=>	'Bhutan',
    	    'BO'	=>	'Bolivia',
    	    'BA'	=>	'Bosnia And Herzegowina',
    	    'BW'	=>	'Botswana',
    	    'BV'	=>	'Bouvet Island',
    	    'BR'	=>	'Brazil',
    	    'IO'	=>	'British Indian Ocean Territory',
    	    'BN'	=>	'Brunei Darussalam',
    	    'BG'	=>	'Bulgaria',
    	    'BF'	=>	'Burkina Faso',
    	    'BI'	=>	'Burundi',
    	    'KH'	=>	'Cambodia',
    	    'CM'	=>	'Cameroon',
    	    'CA'	=>	'Canada',
    	    'CV'	=>	'Cape Verde',
    	    'KY'	=>	'Cayman Islands',
    	    'CF'	=>	'Central African Republic',
    	    'TD'	=>	'Chad',
    	    'CL'	=>	'Chile',
    	    'CN'	=>	'China',
    	    'CX'	=>	'Christmas Island',
    	    'CC'	=>	'Cocos (Keeling) Islands',
    	    'CO'	=>	'Colombia',
    	    'KM'	=>	'Comoros',
    	    'CG'	=>	'Congo',
    	    'CD'	=>	'Congo, The Democratic Republic Of The',
    	    'CK'	=>	'Cook Islands',
    	    'CR'	=>	'Costa Rica',
    	    'CI'	=>	'Cote D\'Ivoire',
    	    'HR'	=>	'Croatia (Local Name: Hrvatska)',
    	    'CU'	=>	'Cuba',
    	    'CY'	=>	'Cyprus',
    	    'CZ'	=>	'Czech Republic',
    	    'DK'	=>	'Denmark',
    	    'DJ'	=>	'Djibouti',
    	    'DM'	=>	'Dominica',
    	    'DO'	=>	'Dominican Republic',
    	    'TP'	=>	'East Timor',
    	    'EC'	=>	'Ecuador',
    	    'EG'	=>	'Egypt',
    	    'SV'	=>	'El Salvador',
    	    'GQ'	=>	'Equatorial Guinea',
    	    'ER'	=>	'Eritrea',
    	    'EE'	=>	'Estonia',
    	    'ET'	=>	'Ethiopia',
    	    'FK'	=>	'Falkland Islands (Malvinas)',
    	    'FO'	=>	'Faroe Islands',
    	    'FJ'	=>	'Fiji',
    	    'FI'	=>	'Finland',
    	    'FR'	=>	'France',
    	    'FX'	=>	'France, Metropolitan',
    	    'GF'	=>	'French Guiana',
    	    'PF'	=>	'French Polynesia',
    	    'TF'	=>	'French Southern Territories',
    	    'GA'	=>	'Gabon',
    	    'GM'	=>	'Gambia',
    	    'GE'	=>	'Georgia',
    	    'DE'	=>	'Germany',
    	    'GH'	=>	'Ghana',
    	    'GI'	=>	'Gibraltar',
    	    'GR'	=>	'Greece',
    	    'GL'	=>	'Greenland',
    	    'GD'	=>	'Grenada',
    	    'GP'	=>	'Guadeloupe',
    	    'GU'	=>	'Guam',
    	    'GT'	=>	'Guatemala',
    	    'GN'	=>	'Guinea',
    	    'GW'	=>	'Guinea-Bissau',
    	    'GY'	=>	'Guyana',
    	    'HT'	=>	'Haiti',
    	    'HM'	=>	'Heard And Mc Donald Islands',
    	    'HN'	=>	'Honduras',
    	    'HK'	=>	'Hong Kong',
    	    'HU'	=>	'Hungary',
    	    'IS'	=>	'Iceland',
    	    'IN'	=>	'India',
    	    'ID'	=>	'Indonesia',
    	    'IR'	=>	'Iran (Islamic Republic Of)',
    	    'IQ'	=>	'Iraq',
    	    'IE'	=>	'Ireland',
    	    'IL'	=>	'Israel',
    	    'IT'	=>	'Italia',
    	    'JM'	=>	'Jamaica',
    	    'JP'	=>	'Japan',
    	    'JO'	=>	'Jordan',
    	    'KZ'	=>	'Kazakhstan',
    	    'KE'	=>	'Kenya',
    	    'KI'	=>	'Kiribati',
    	    'KP'	=>	'Korea, Democratic People\'S Republic Of',
    	    'KR'	=>	'Korea, Republic Of',
    	    'KW'	=>	'Kuwait',
    	    'KG'	=>	'Kyrgyzstan',
    	    'LA'	=>	'Lao People\'S Democratic Republic',
    	    'LV'	=>	'Latvia',
    	    'LB'	=>	'Lebanon',
    	    'LS'	=>	'Lesotho',
    	    'LR'	=>	'Liberia',
    	    'LY'	=>	'Libyan Arab Jamahiriya',
    	    'LI'	=>	'Liechtenstein',
    	    'LT'	=>	'Lithuania',
    	    'LU'	=>	'Luxembourg',
    	    'MO'	=>	'Macau',
    	    'MK'	=>	'Macedonia, Former Yugoslav Republic Of',
    	    'MG'	=>	'Madagascar',
    	    'MW'	=>	'Malawi',
    	    'MY'	=>	'Malaysia',
    	    'MV'	=>	'Maldives',
    	    'ML'	=>	'Mali',
    	    'MT'	=>	'Malta',
    	    'MH'	=>	'Marshall Islands, Republic of the',
    	    'MQ'	=>	'Martinique',
    	    'MR'	=>	'Mauritania',
    	    'MU'	=>	'Mauritius',
    	    'YT'	=>	'Mayotte',
    	    'MX'	=>	'Mexico',
    	    'FM'	=>	'Micronesia, Federated States Of',
    	    'MD'	=>	'Moldova, Republic Of',
    	    'MC'	=>	'Monaco',
    	    'MN'	=>	'Mongolia',
    	    'MS'	=>	'Montserrat',
    	    'MA'	=>	'Morocco',
    	    'MZ'	=>	'Mozambique',
    	    'MM'	=>	'Myanmar',
    	    'NA'	=>	'Namibia',
    	    'NR'	=>	'Nauru',
    	    'NP'	=>	'Nepal',
    	    'NL'	=>	'Netherlands',
    	    'AN'	=>	'Netherlands Antilles',
    	    'NC'	=>	'New Caledonia',
    	    'NZ'	=>	'New Zealand',
    	    'NI'	=>	'Nicaragua',
    	    'NE'	=>	'Niger',
    	    'NG'	=>	'Nigeria',
    	    'NU'	=>	'Niue',
    	    'NF'	=>	'Norfolk Island',
    	    'MP'	=>	'Northern Mariana Islands, Commonwealth of the',
    	    'NO'	=>	'Norway',
    	    'OM'	=>	'Oman',
    	    'PK'	=>	'Pakistan',
    	    'PW'	=>	'Palau, Republic of',
    	    'PA'	=>	'Panama',
    	    'PG'	=>	'Papua New Guinea',
    	    'PY'	=>	'Paraguay',
    	    'PE'	=>	'Peru',
    	    'PH'	=>	'Philippines',
    	    'PN'	=>	'Pitcairn',
    	    'PL'	=>	'Poland',
    	    'PT'	=>	'Portugal',
    	    'PR'	=>	'Puerto Rico',
    	    'QA'	=>	'Qatar',
    	    'RE'	=>	'Reunion',
    	    'RO'	=>	'Romania',
    	    'RU'	=>	'Russian Federation',
    	    'RW'	=>	'Rwanda',
    	    'KN'	=>	'Saint Kitts And Nevis',
    	    'LC'	=>	'Saint Lucia',
    	    'VC'	=>	'Saint Vincent And The Grenadines',
    	    'WS'	=>	'Samoa',
    	    'SM'	=>	'San Marino',
    	    'ST'	=>	'Sao Tome And Principe',
    	    'SA'	=>	'Saudi Arabia',
    	    'SN'	=>	'Senegal',
    	    'SC'	=>	'Seychelles',
    	    'SL'	=>	'Sierra Leone',
    	    'SG'	=>	'Singapore',
    	    'SK'	=>	'Slovakia (Slovak Republic)',
    	    'SI'	=>	'Slovenia',
    	    'SB'	=>	'Solomon Islands',
    	    'SO'	=>	'Somalia',
    	    'ZA'	=>	'South Africa',
    	    'GS'	=>	'South Georgia, South Sandwich Islands',
    	    'ES'	=>	'Spain',
    	    'LK'	=>	'Sri Lanka',
    	    'SH'	=>	'St. Helena',
    	    'PM'	=>	'St. Pierre And Miquelon',
    	    'SD'	=>	'Sudan',
    	    'SR'	=>	'Suriname',
    	    'SJ'	=>	'Svalbard And Jan Mayen Islands',
    	    'SZ'	=>	'Swaziland',
    	    'SE'	=>	'Sweden',
    	    'CH'	=>	'Switzerland',
    	    'SY'	=>	'Syrian Arab Republic',
    	    'TW'	=>	'Taiwan',
    	    'TJ'	=>	'Tajikistan',
    	    'TZ'	=>	'Tanzania, United Republic Of',
    	    'TH'	=>	'Thailand',
    	    'TG'	=>	'Togo',
    	    'TK'	=>	'Tokelau',
    	    'TO'	=>	'Tonga',
    	    'TT'	=>	'Trinidad And Tobago',
    	    'TN'	=>	'Tunisia',
    	    'TR'	=>	'Turkey',
    	    'TM'	=>	'Turkmenistan',
    	    'TC'	=>	'Turks And Caicos Islands',
    	    'TV'	=>	'Tuvalu',
    	    'UG'	=>	'Uganda',
    	    'UA'	=>	'Ukraine',
    	    'AE'	=>	'United Arab Emirates',
    	    'GB'	=>	'United Kingdom',
    	    'UM'	=>	'United States Minor Outlying Islands',
    	    'UY'	=>	'Uruguay',
    	    'UZ'	=>	'Uzbekistan',
    	    'VU'	=>	'Vanuatu',
    	    'VA'	=>	'Vatican City, State of the',
    	    'VE'	=>	'Venezuela',
    	    'VN'	=>	'Viet Nam',
    	    'VG'	=>	'Virgin Islands (British)',
    	    'VI'	=>	'Virgin Islands (U.S.)',
    	    'WF'	=>	'Wallis And Futuna Islands',
    	    'EH'	=>	'Western Sahara',
    	    'YE'	=>	'Yemen',
    	    'YU'	=>	'Yugoslavia',
    	    'ZM'	=>	'Zambia',
    	    'ZW'	=>	'Zimbabwe'
    	);
    }

    /** 
     * @brief Mostra giorni
     * @details Riempie le option di un select con 31 giorni
     * @param[in] int $selected_day Giorno selezionato
     * @note Se $selected_day vale NULL nel menù a discesa sarà selezionato GG se nel costruttore è stato specificato
     * @code
     * //Nel menù a discesa sarà selezionato GG se specificato nel costruttore
     * $date->showDays(NULL);
     * //Oppure nel menù a discesa sarà selezionato 15
     * $date->showDays(15);
     * @endcode
     */
    public function showDays($selected_day=NULL) {
        if(!is_null($this->day_not_set)) {
            if($selected_day==NULL)
                echo "<option value=$this->day_not_set selected=selected>$this->day_not_set</option>";
            else
                echo "<option value=$this->day_not_set>$this->day_not_set</option>";
        }
        foreach ($this->days as $day) {            
            if($day!=$selected_day)
                echo "<option value=$day>$day</option>";
            else
                echo "<option value=$day selected=selected>$day</option>";
        } 
    }
    
    /** 
     * @brief Mostra mesi
     * @details Riempie le option di un select con 12 mesi
     * @param[in] int $selected_month Mese selezionato
     * @note Se $selected_month vale NULL nel menù a discesa sarà selezionato MM se nel costruttore è stato specificato
     * @code
     * //Nel menù a discesa sarà selezionato MM se specificato nel costruttore
     * $date->showMonths(NULL);
     * //Oppure nel menù a discesa sarà selezionato 10
     * $date->showMonths(10);
     * @endcode
     */
    public function showMonths($selected_month=NULL) {
        if(!is_null($this->month_not_set)) {
            if($selected_month==NULL)
                echo "<option value=$this->month_not_set selected=selected>$this->month_not_set</option>";
            else
                echo "<option value=$this->month_not_set>$this->month_not_set</option>";
        }
        foreach ($this->months as $month) {            
            if($month!=$selected_month)
                echo "<option value=$month>$month</option>";
            else
                echo "<option value=$month selected=selected>$month</option>";
        } 
    }

    /** 
     * @brief Mostra province italiane
     * @details Riempie le option con tutte le province italiane
     */
    public function showProvince() {
        foreach ($this->province as $key => $value)         
		echo "<option value=$value>$key</option>"; 
    }
    
    /**
     * @brief Mostra Stati
     * @details Riempie le option con tutti gli stati del mondo
     * @param[in] string $selected_state Stato selezionato
     * @note Se alla funzione non viene passato nulla allora di default seleziono "Italia".
     * @n E' necessario passare il nome esatto dello stato come appare nella lista 
     * @code
     * //Nel menù a discesa di default sarà selezionato Italia
     * $object->showStates();
     * //Nel menù a discesa sarà selezionata la Repubblica di Guinea-Bissau
     * $object->showState("Guinea-Bissau");
     * //Nel menù a discesa sarà selezionata la Repubblica della Moldova
     * $date->showMonths("Moldova, Republic Of");
     * @endcode
     */
    public function showStates($selected_state="Italia") {
        if($selected_state=="Italia")
            echo "<option value=IT selected=selected>Italia</option>";
        else {
            foreach ($this->states as $key => $value)
                if(!strcmp($selected_state, $value)) {
                    echo "<option value=$key selected=selected>$value</option>";
                    break;
                }
        }
        foreach ($this->states as $key => $value)
            echo "<option value=$key>$value</option>";
    } 
}
?>