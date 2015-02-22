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
            return "{$this->file}.{$this->line}: {$this->message}[{$this->code}]\n"; //Path del file.riga: messaggio custom[numero di errore classe Messages]
        else
            return "{$this->message}[{$this->code}]\n"; //Messaggio custom[numero di errore classe Messages]
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
 * @class DatesForSelect
 * @brief Popola i campi <select> con i giorni e i mesi dell'anno
 */
class DatesForSelect {
    private $days; /**< Giorni del mese */
    private $months; /**< Mesi dell'anno */
    private $day_not_set; /**< Valore del giorno quando non settato */
    private $month_not_set; /**< Valore del mese quando non settato */
    
    
    /** 
     * @brief Costruttore
     * @details Inizializza i giorni ed i mesi
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
}
?>