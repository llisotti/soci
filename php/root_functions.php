<?php
require "member.php"; //OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE  !
require_once('./TCPDF/tcpdf.php');

/* Setto la sessione di 5 ore */
ini_set('session.gc_maxlifetime', 18000);
//echo ini_get('session.gc_maxlifetime');

/* Abilito il garbage collector con la probabilita' dell'1% di girare ad ogni session_start() */
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

/* Definisco un mio path dove dovra' essere creato il file della sessione */
session_save_path(LOGFILE_PATH);

/* Inizio sessione */
session_start();

 
?>
<!DOCTYPE html> 
<html> 
<head> 
<title>Gruppo Astrofili "N. Copernico"</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="../css/root_functions.css" media="all"/>
</head> 
<body>
<div class="container">
<div class="form">		
<?php
/* 
 * Controlla se le strutture del file di backup sono aggiornate
 * $previous_fields Array di stringhe che rappresentano la riga precedente a quella da controllare
 * $target_fields Array di stringhe che rappresentano la riga da controllare
 * Le due Array devono avere la stessa lunghezza
 * Se ritorna FALSE il file non e' aggiornato altrimenti ritorna TRUE
 */
function BackupFileStatus(array $previous_fields, array $target_fields) {
    $fields=count($target_fields);
    $found=0;
    $file=new SplFileObject(BACKUP_PATH.$_POST['restorefile'], 'r+b');
    foreach ($previous_fields as $previous_key => $previous_value) {
        while (!$file->eof()) {
            $current_line= $file->fgets();
            if(strpos($current_line, $previous_value)==TRUE) { 
                $next_line= $file->fgets();
                if(strpos($next_line, $target_fields[$previous_key])==TRUE) { //Ho trovato un valore gia' aggiornato
                    $found++;
                    $file->rewind();
                    unset($next_line);
                    break;
                }
            }
        }
    }    
    if($found < $fields)
        $return_value=FALSE; //Occorre aggiornare il file di backup
    else
        $return_value=TRUE; //Il file di backup e' gia' aggiornato
    
    unset($file);
    return $return_value;
}    
    

/* 
 * Modifica la struttura del file di backup
 * $actual_fields Array di stringhe che rappresentano la riga da sostituire
 * $new_fields Array di stringhe che rappresentano la nuova riga
 * Le due Array devono avere la stessa lunghezza
 * Ritorna quante occorrenze sono state sostituite (affinche' tutto bene devono essere tante quanti gli elementi degli array)
 */
function UpdateBackupFile(array $actual_fields, array $new_fields) {
    $replaced=0;
    $file_in_string= file_get_contents(BACKUP_PATH.$_POST['restorefile']);
    $file_in_string_updated=  str_replace($actual_fields, $new_fields, $file_in_string, $replaced);
    file_put_contents(BACKUP_PATH.$_POST['restorefile'], $file_in_string_updated);
    return $replaced;
}



/* RIchiedo il logger */
$mylog=$_SESSION['logger'];

/* Se apro per la prima volta la pagina mostro il pulsante Esegui operazione e termino lo script */
if(!isset($_POST['export'])) {
    ?>
    <form action="<?php echo "$_SERVER[PHP_SELF]"."?"."$_SERVER[QUERY_STRING]" ?>" method="post">
    <!-- <input autocomplete="off" type="password" name="root_password" autofocus placeholder="Password"/> -->
    <!-- <input type="submit" name="export" value="Esegui" /> <br/><br/> -->
    <?php
    $time=getdate();
    $date=new DataForSelect();
    switch ($_GET['action']) {
        case "DB_functions": //Voglio eseguire un'operazine sul database
            ?>
			<table>
				<!--<tr><td style="text-align: left"><input class="enable_submit" name="azione" value="frontespizio" type="radio" checked />Estrai frontespizio</td></tr>-->
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="allmembers" type="radio" checked />Esporta libro soci</td></tr>
                <!--<tr><td style="text-align: left"><input class="enable_submit" name="azione" value="members_evening" type="radio" />Esporta soci serata</tr>-->
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="members_date" type="radio" />Esporta soci dal</td>
                    <td>
                    <?php
                    echo "<select style='width: 100%' name=gg_start>";
                    $date->showDays();
                    ?>
                    </td>                         
                    <td>
                    <?php
                    echo "<select style='width: 100%' name=mm_start>";
                    $date->showMonths();
                    ?>
                    </td>
                    <td>
                    &nbsp;&nbsp;al&nbsp;&nbsp;
                    </td>
                    <td>
                    <?php
                    echo "<select style='width: 100%' name=gg_end>";
                    $date->showDays();
                    ?>
                    </td>                         
                    <td>
                    <?php
                    echo "<select style='width: 100%' name=mm_end>";
                    $date->showMonths();
                    ?>
                    </td>
                    <td>
                    &nbsp;
                    <?php
                    echo $time['year'];
                    ?>
                    </td>
                </tr>
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="pdf_export" type="radio" />Stampa moduli PDF dalla tessera</td>
                    <td><input name="tessera_iniziale" size="4" value="0" type="number" /></td>                         
                    <td colspan="2">
                    &nbsp;&nbsp;alla tessera&nbsp;&nbsp;
                    </td>
                    <td><input name="tessera_finale" size="4" value="0" type="number" /></td>
                </tr>               
                <tr><td style="text-align: left"><input name="azione" value="allidentities" type="radio" />Esporta tutti gli iscritti</td></tr>
            </table>                    
            <hr>
            <table>           
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="backup-completo" type="radio" />Backup database</td></tr>
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="backup-solodati" type="radio" />Backup database (solo dati)</td></tr>
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="backup-solostruttura" type="radio" />Backup database (solo struttura)</td></tr>
                <tr><td style="text-align: left"><input class="disable_submit" name="azione" value="restore" type="radio" />Ripristino database</td><td><input type="file" name="restorefile" accept=".sql" /></td>
                <td><img style="border:0;height:31px" src="../img/question.png" alt="" title="Il file .tsv (separato da \t e fine riga \n) deve trovarsi nella cartella /doc" /><td>
                </tr>
            </table>
            <hr>
            <table>           
                <tr><td style="text-align: left"><input class="disable_submit" name="azione" value="loadCustomList" type="radio"/>Carica lista custom newsletter</td>
                <td><input type="file" name="customList" accept=".csv" /></td>
                <td><img style="border:0;height:31px" src="../img/question.png" alt="" title="Il file .tsv (separato da \t e fine riga \n) deve trovarsi nella cartella /doc e deve avere i seguenti campi: codice_fiscale,cognome,nome,email" /><td>             
                </tr>
            </table>
            <hr>
            <?php
            break;
        case "view_members_evening": //Visualizzo i soci della serata
            foreach (array_reverse($_SESSION['members_evening'], TRUE) as $key => $value) {
                if($key==0)
                    continue;
                echo $key."<sup>a</sup> tessera inserita: ".$value."<br/>";
            }
            die();
        case "view_drop_cards": //Visualizzo le tessere mancanti
            echo "I numeri di tessera seguenti risultano mancanti:<br/><br/>";
            foreach ($_SESSION['breakCards'] as $breakCards) {
                echo $breakCards."<br/>";
            }
            die();
        /*
        case "update": //Aggiornamento software
            $mylog->logInfo("Tentativo di aggiornamento software dalla versione ".VERSION);
            system(GIT_EXECUTABLE."pull --tags origin", $return_value); //Per eseguire delle prove aggiungere --dry-run al comando git
            if($return_value==0) {
                $mylog->logInfo("Tentativo riuscito: nuova versione: ".VERSION);
                $_SESSION['update']=FALSE;
                ?>
            <br/><br/>
            <?php
            echo "Aggiornamento riuscito. Chiudere completamente il browser ed aprirlo nuovamente per rendere attive le modifiche</a>";
        }
        else {
            //$mylog->logError("Tentativo fallito (system return value: ".$return_value.")");
            ?>
            <br/><br/>
            <?php
            echo "Aggiornamento fallito!, <a href=https://{$_SERVER['HTTP_HOST']}/index.php>Torna alla Pagina iniziale</a>";
        }
        break;
        */
        default:
            break;
    }

    ?>
    <br/><input type="submit" name="export" value="Esegui" />
    </form>
    <?php
}
else {

    /* RIchiedo il logger */
    $mylog=$_SESSION['logger'];

    /* Mi connetto al database */
    try {
        $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
    }
    catch (PDOException $exception) {
        echo '<img src="../img/check_ko.png" height="100" width="100" alt="check_ko">';
        echo "<br/>Errore di connessione al database: ".$exception->getMessage();
        die();
    }
    $date=new DateTime();
    
    $data=$date->format('Ymd');
    //$month=$date->format('m');

    /* Verifico l'azione che devo fare */
    switch ($_POST['azione']) {
        case "frontespizio": //Esporto il frontespizio
            $members=$dbh->query("SELECT numero_tessera, anagrafica.cognome, anagrafica.nome, anagrafica.data_nascita FROM socio "
            ."INNER JOIN anagrafica ON anagrafica.id = socio.id WHERE socio.numero_tessera IS NOT NULL "
            ."ORDER BY socio.numero_tessera ASC "
            ."INTO OUTFILE '".BACKUP_PATH.$data."-frontespizio.tsv' "
            ."FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n' ");
            break;
        case "allmembers": //Esporto il libro soci
            $members=$dbh->query("SELECT data_tessera, NULL, iscrizione, numero_tessera, anagrafica.cognome, anagrafica.nome, CONCAT(IFNULL(anagrafica.indirizzo, ''),' ', IFNULL(anagrafica.citta,''),' ', IFNULL(anagrafica.provincia,''),' [', anagrafica.stato,']') AS residenza, CONCAT(IFNULL(anagrafica.comune_nascita,''), ' [', anagrafica.stato_nascita, ']') AS nascita , anagrafica.data_nascita FROM socio "
            ."INNER JOIN anagrafica ON anagrafica.id = socio.id WHERE socio.numero_tessera IS NOT NULL "
            ."ORDER BY socio.numero_tessera ASC "
            ."INTO OUTFILE '".BACKUP_PATH.$data."-Libro soci online.tsv' "
            ."FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n' ");
            break;
        /*
        case "members_evening": //Esporto i soci inseriti in questa sessione
            $cards=  join(',', $_SESSION['members_evening']);
            echo $cards;
            $members=$dbh->query(" SELECT numero_tessera, anagrafica.cognome, anagrafica.nome, anagrafica.data_nascita FROM socio "
            ."INNER JOIN anagrafica ON anagrafica.id = socio.id WHERE socio.numero_tessera IN ($cards) "
            ."ORDER BY socio.numero_tessera ASC "
            ."INTO OUTFILE '".BACKUP_PATH.$data."-Elenco soci serata.tsv'  "
            ."FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n' ");              
            break;
        */
        case "members_date": //Esporto i soci da una certa data ad una certa data
            $date_start=DateTime::createFromFormat('Y-m-d', date('Y')."-$_POST[mm_start]-$_POST[gg_start]");
            $date_end=DateTime::createFromFormat('Y-m-d', date('Y')."-$_POST[mm_end]-$_POST[gg_end]");

            /* Se non setto le date le imposto io secondo i seguenti criteri */
            if($_POST['gg_end']=="GG" || $_POST['mm_end']=="MM") //Se nella data finale non setto nulla allora intendo sino ad oggi
                $date_end=DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
            if ($_POST['gg_start']=="GG" || $_POST['mm_start']=="MM") //Se nella data iniziale non setto nulla allora intendo dal 1° Gennaio
                $date_start=DateTime::createFromFormat('Y-m-d', date('Y')."-01-01");               

            /*Se la data finale è minore della iniziale emetto errore */
            if($date_end<$date_start) {
                echo "Errore: data iniziale più recente di quella finale!"
                ?>
                <form action="<?php echo "$_SERVER[PHP_SELF]"."?"."$_SERVER[QUERY_STRING]" ?>" method="post">
                <br/><input type="submit" name="" value="Indietro" />
                </form>
                <?php
                die();
            }
            $start=$date_start->format('Y-m-d');
            $end=$date_end->format('Y-m-d');
            $namefile=  str_replace('-', '', $start)."-".str_replace('-', '', $end); //Desinenza del nome del file: data iniziale-data finale nel formato AAAMMGG-AAAMMGG
            $members=$dbh->query(" SELECT data_tessera, numero_tessera, anagrafica.cognome, anagrafica.nome, anagrafica.data_nascita FROM socio "
            ."INNER JOIN anagrafica ON anagrafica.id = socio.id WHERE socio.data_tessera>='$start' AND socio.data_tessera<='$end' "
            ."ORDER BY socio.numero_tessera ASC "
            ."INTO OUTFILE '".BACKUP_PATH.$data."-Elenco soci $namefile.tsv' "
            ."FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n' ");                
            break;
        case "pdf_export": //Stampo PDF dei soci da una certa tessera ad una certa tessera
                /* Verifico se i campi delle tessere da stampare sono corretti */
                if ($_POST['tessera_iniziale'] <= 0 || !is_numeric ($_POST['tessera_iniziale']) || $_POST['tessera_finale'] <= 0 || !is_numeric ($_POST['tessera_finale'])) {
                    echo "Errore: controllare i numeri di tessera immessi"
                    ?>
                    <form action="<?php echo "$_SERVER[PHP_SELF]"."?"."$_SERVER[QUERY_STRING]" ?>" method="post">
                    <br/><input type="submit" name="" value="Indietro" />
                    </form>
                    <?php
                    die();
                }
                
                /* Verifico che il valore immesso nella tessera iniziale sia minore di quello immesso nella tessera finale */
                if ($_POST['tessera_iniziale'] > $_POST['tessera_finale']) {
                    echo "Errore: tessera iniziale maggiore di quella finale!"
                    ?>
                    <form action="<?php echo "$_SERVER[PHP_SELF]"."?"."$_SERVER[QUERY_STRING]" ?>" method="post">
                    <br/><input type="submit" name="" value="Indietro" />
                    </form>
                    <?php
                    die();
                }

                /* Stampo i PDF */
                $CardInitValue = $_POST['tessera_iniziale'];
                $CardFinalValue = $_POST['tessera_finale'];
                $printed = array();
                $cards=$dbh->query("SELECT numero_tessera FROM socio WHERE numero_tessera IS NOT NULL");
                $numCards=$cards->fetchAll(PDO::FETCH_COLUMN, 0);

                for ($i=$CardInitValue; $i <= $CardFinalValue; $i++) { 
                    if (in_array($i, $numCards)) {
                        printPDF($i);
                        array_push($printed, $i); 
                    }; 
                }
                echo "Sono stati stampati ".count($printed)." moduli in PDF:";
                echo "<br>";
                foreach($printed as $key => $value)
                {
                    echo $value;
                    echo "<br>";
                }
                break;
        case "allidentities": //Esporto identità
            $members=$dbh->query(" SELECT * FROM anagrafica "
            ."INNER JOIN socio ON anagrafica.id = socio.id "
            ."ORDER BY socio.numero_tessera ASC "
            ."INTO OUTFILE '".BACKUP_PATH.$data."-Elenco iscritti.tsv' "
            ."FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n' ");
            break;
        case "backup-completo": //Faccio il backup completo (struttura + dati)
            system(MYSQLDUMP_EXECUTABLE."-u copernico --routines soci > ".BACKUP_PATH.$data."-BackupCompleto.sql 2>&1", $return_value);
            break;
        case "backup-solodati": //Faccio il backup solo dei dati (non della struttura)
            system(MYSQLDUMP_EXECUTABLE."-u copernico --routines soci --no-create-info > ".BACKUP_PATH.$data."-BackupSoloDati.sql 2>&1", $return_value);
            break;
        case "backup-solostruttura": //Faccio il backup solo della struttura (non dei dati)
            system(MYSQLDUMP_EXECUTABLE."-u copernico --routines soci --no-data > ".BACKUP_PATH.$data."-BackupSoloStruttura.sql 2>&1", $return_value);
            break;
        case "restore": //Faccio il backup solo della struttura (non dei dati)
            system(MYSQL_EXECUTABLE."-u copernico soci < ".BACKUP_PATH.$_POST['restorefile']." 2>&1", $return_value);
        break;
        case "loadCustomList": //Caricamento custom list per newsletter
            $members=$dbh->query("DELETE FROM customList"); //svuoto l'attuale lista custom
            $members=$dbh->query("LOAD DATA INFILE '".BACKUP_PATH.$_POST[customList]."' INTO TABLE customList FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n'");         
            break;
        default:
            break;
    }    
    /* Se sto facendo un'operazione di estrazione da database */
    if($_POST['azione'] == "frontespizio" || $_POST['azione'] == "allmembers" || $_POST['azione'] == "members_evening" || $_POST['azione'] == "members_date" || $_POST['azione'] == "allidentities") {
        if(!$members) {
            echo '<img src="../img/check_ko.png" height="100" width="100" alt="check_ko">';
            echo "<br/>ERRORE CREAZIONE FILE !";
            $mylog->logError("Tentativo di esportare file identità fallito");
        }
        else {
            echo '<img src="../img/check_ok.png" height="100" width="100" alt="check_ok">';
            echo "<br/>FILE CREATO CORRETTAMENTE NELLA CARTELLA DOC";
            $mylog->logInfo("Tentativo di esportare file identità riuscito");
        }
    }
    /* Se invece sto caricando la lista custom per la newsletter */
    else if($_POST['azione'] == "loadCustomList") {
        if(!$members) {
            echo '<img src="../img/check_ko.png" height="100" width="100" alt="check_ko">';
            echo "<br/>CARICAMENTO LISTA CUSTOM FALLITO !";
            $mylog->logError("Caricamento lista custom fallito");
        }
        else {
            echo '<img src="../img/check_ok.png" height="100" width="100" alt="check_ok">';
            echo "<br/>CARICAMENTO LISTA CUSTOM RIUSCITO";
            $mylog->logInfo("Caricamento lista custom riuscito");   
        }
    }
    /* Se sto stampando i PDF */ 
    else if($_POST['azione'] == "pdf_export") {
        echo '<img src="../img/check_ok.png" height="100" width="100" alt="check_ok">';
        echo "<br/>STAMPA ESEGUITA CORRETTAMENTE";
    }
    /* Se invece sto facendo un'operazione di backup/ripristino del database */
    else {
        if($return_value!=0) {
            echo '<img src="../img/check_ko.png" height="100" width="100" alt="check_ko">';
        if(strpos($_POST['azione'], 'backup') !== false) { //Ho tentato di fare il backup
            echo "<br/>ERRORE BACKUP: tentativo di effettuare il backup fallito! (system return value: ".$return_value.")";
            //$mylog->logError("Tentativo di effettuare il backup soci fallito (system return value: ".$return_value.")");
        }
        else {
            echo "<br/>ERRORE RIPRISTINO: tentativo di effettuare il ripristino fallito! (system return value: ".$return_value.")";
            //$mylog->logError("Tentativo di effettuare il ripristino dal backup soci fallito (system return value: ".$return_value.")");
        }
    }           
        else {
            echo '<img src="../img/check_ok.png" height="100" width="100" alt="check_ok">';
            if(strpos($_POST['azione'], 'backup') !== false) { //Ho fatto il backup
                echo "<br/>FILE DI BACKUP CREATO CORRETTAMENTE NELLA CARTELLA DOC";
                //$mylog->logInfo("Tentativo di effettuare il backup soci riuscito");
            }
            else {
                echo "<br/>RIPRISTINO OK";
                //$mylog->logInfo("Tentativo di effettuare il ripristino soci riuscito");
            }
        }
    }
}

function printPDF($tessera)
{
    /* Mi connetto al database */
    try {
        if(!isset($dbh)) {
            $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
        }
    }
    catch (PDOException $exception) {
        die("Errore di connessione al database: ".$exception->getMessage());
    }

    $members=$dbh->query("SELECT *, DATE_FORMAT(anagrafica.data_nascita,'%d/%m/%Y') data_nascita, DATE_FORMAT(socio.scadenza,'%d/%m/%Y') scadenza, DATE_FORMAT(socio.data_tessera,'%d/%m/%Y') data_tessera FROM anagrafica LEFT JOIN socio ON anagrafica.id = socio.id WHERE socio.numero_tessera = $tessera");
    $row=$members->fetch();

    /* Creo l'oggetto socio e lo popolo con tutti i dati */
    $member=new Socio_Copernico($row['cognome'], $row['nome']);
    $member->id=($row['id']); //Ho usato un alias nella query
    $member->data_nascita=($row['data_nascita']);
    $member->comune_nascita=($row['comune_nascita']);
    $member->provincia_nascita=($row['provincia_nascita']);
    $member->stato_nascita=($row['stato_nascita']);
    $member->sesso=($row['sesso']);
    $member->indirizzo=($row['indirizzo']);
    $member->cap=($row['cap']);
    $member->citta=($row['citta']);
    $member->provincia=($row['provincia']);
    $member->stato=($row['stato']);
    $member->telefono=($row['telefono']);
    $member->email=($row['email']);
    $member->tessera=$row['numero_tessera'];
    $member->data_tessera=$row['data_tessera'];
    $member->scadenza=($row['scadenza']);
    $member->firma=$row['firma'];
    $member->flags=$row['adesioni'];

    // create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Luca Lisotti');
    $pdf->SetTitle('Modulo di adesione');
    //$pdf->SetSubject('TCPDF Tutorial');
    //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

    // set default header data
    //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
    $pdf->SetHeaderData("../../../../img/logo_copernico.jpg", "90", "MODULO ADESIONE N. ".$tessera, $member->cognome." ".$member->nome, "");
    $pdf->setFooterData(array(0,64,0), array(0,64,128));

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', "15"));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    /* set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        require_once(dirname(__FILE__).'/lang/eng.php');
        $pdf->setLanguageArray($l);
    }
    */
    // ---------------------------------------------------------

    // set default font subsetting mode
    $pdf->setFontSubsetting(true);

    // Set font
    // dejavusans is a UTF-8 Unicode font, if you only need to
    // print standard ASCII chars, you can use core fonts like
    // helvetica or times to reduce file size.
    $pdf->SetFont('dejavusans', '', 14, '', true);

    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();

    // set text shadow effect
    //$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

    $pdf->SetFont('times', '', 10);
    $pdf->MultiCell(0, 10, "Il sottoscritto/La sottoscritta:", 0, 'L', 0, 0, '', '', true);
    $pdf->ln(10);
    $pdf->SetFillColor(215, 235, 255);
    //$pdf->SetFont('times', '', 12);
    $pdf->MultiCell(90, 10, "COGNOME: ".$member->cognome, 'LT', 'L', 1, 0, '', '', true);
    $pdf->MultiCell(90, 10, "NOME: ".$member->nome, 'TR', 'L', 1, 0, '', '', true);
    $pdf->ln(10);
    $pdf->MultiCell(90, 10, "NATO/A IL: ".$member->data_nascita, 'L', 'L', 1, 0, '', '', true);
    $pdf->MultiCell(90, 10, "COMUNE: ".$member->comune_nascita, 'R', 'L', 1, 0, '', '', true);
    $pdf->ln(10);
    $pdf->MultiCell(90, 10, "PROVINCIA: ".$member->provincia_nascita, 'L', 'L', 1, 0, '', '', true);
    $pdf->MultiCell(90, 10, "STATO: ".$member->stato_nascita, 'R', 'L', 1, 0, '', '', true);
    $pdf->ln(10);
    //$pdf->MultiCell(0, 10, "CODICE FISCALE: ".$member->codice_fiscale, 'LR', 'L', 1, 0, '', '', true);
    $pdf->MultiCell(0, 10, "CODICE FISCALE: ", 'LR', 'L', 1, 0, '', '', true);
    $pdf->ln(10);
    $pdf->MultiCell(90, 10, "RESIDENTE IN: ".$member->indirizzo, 'L', 'L', 1, 0, '', '', true);
    $pdf->MultiCell(90, 10, "CITTA': ".$member->citta, 'R', 'L', 1, 0, '', '', true);
    $pdf->ln(10);
    $pdf->MultiCell(90, 10, "CAP: ".$member->cap, 'L', 'L', 1, 0, '', '', true);
    $pdf->MultiCell(45, 10, "PROVINCIA: ".$member->provincia, 0, 'L', 1, 0, '', '', true);
    $pdf->MultiCell(45, 10, "STATO: ".$member->stato, 'R', 'L', 1, 0, '', '', true);
    $pdf->ln(10);
    $pdf->MultiCell(90, 10, "TELEFONO: ".$member->telefono, 'LB', 'L', 1, 0, '', '', true);
    $pdf->MultiCell(90, 10, "EMAIL: ".$member->email, 'RB', 'L', 1, 0, '', '', true);
    $pdf->ln(15);

    //$pdf->SetFont('times', '', 12);
    $pdf->MultiCell(0, 10, "Premesso:", 0, 'C', 0, 0, '', '', true);
    $pdf->ln(5);
    $premessa = "- di aver preso visione dello statuto dell'Associazione senza scopo di lucro denominata \"ASSOCIAZIONE CULTURALE GRUPPO ASTROFILI N. COPERNICO\".\n"
    ."- che in particolare condivido gli scopi di natura ideale dell'Associazione (art. 2 dello statuto).";
    $pdf->MultiCell(0, 10, $premessa, 0, 'J', 0, 0, '', '', true);
    $pdf->ln(20);

    $pdf->MultiCell(0, 10, "Rivolgo istanza:", 0, 'C', 0, 0, '', '', true);
    $pdf->ln(5);
    $istanza ="Al presidente dell\"ASSOCIAZIONE CULTURALE GRUPPO ASTROFILI N. COPERNICO\" (art. 7 dello statuto), affinche' mi venga concessa l'ammissione alla stessa e, di conseguenza, la qualita' di SOCIO per l'anno solare in corso.\n"
    ."Alla scadenza dell'anno solare (31/12), la mia partecipazione all'Associazione in qualita' di socio potra' rinnovarsi solo previo il regolare pagamento della quota sociale, stabilita annualmente dal Consiglio del Direttivo dell'Associazione.";
    $pdf->MultiCell(0, 10, $istanza, 0, 'J', 0, 0, '', '', true);
    $pdf->ln(20);
    $pdf->MultiCell(80, 10, "Saludecio (RN) li, ".$member->data_tessera, 0, 'L', 0, 0, '', '', true);

    /* Aggiungo la firma del socio */
    $string=base64_encode(file_get_contents(str_replace(" ", "", SIGNATURE_IMAGE_PATH.$member->firma)));
    if($string)
    {
        $img = '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $string) . '">';
        $pdf->writeHTMLCell(0,0,95,170,$img,'B');
        $pdf->ln(32);
    }
    else
    {
        $pdf->ln(25);
    }    
    

    /* Controllo se minorenne o maggiorenne */
    $adesioni=pack('C', $member->flags);
    if($adesioni & 4) //Se e' minorenne
        $pdf->MultiCell(140, 10, "(firma del genitore)", 0, 'R', 0, 0, '', '', true);
    else //Se e' maggiorenne
        $pdf->MultiCell(140, 10, "(firma del socio)", 0, 'R', 0, 0, '', '', true);

    $pdf->ln(10);
    $accolta ="Vista la domanda prevenuta all' \"ASSOCIAZIONE CULTURALE GRUPPO ASTROFILI N. COPERNICO\" in data odierna, il Presidente decreta l'ammissione a Socio per l'anno solare ".date('Y')." di ".$member->cognome." ".$member->nome;
    $pdf->MultiCell(0, 10, $accolta, 0, 'L', 0, 0, '', '', true);

    /* Aggiungo la firma del Presidente */
    $string=base64_encode(file_get_contents(str_replace(" ", "", SIGNATURE_IMAGE_PATH."LollinoGianfranco-27111957.png")));
    $img = '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $string) . '">';
    $pdf->writeHTMLCell(0,0,95,230,$img,'B');

    $pdf->ln(32);
    $pdf->MultiCell(160, 10, "(il Presidente, Gian Franco Lollino)", 0, 'R', 0, 0, '', '', true);

    /* Pagina Informativa */
    $pdf->AddPage();
    if($adesioni & 4) {
        $titolo_informativa = "NFORMATIVA EX ART. 13 GDPR PER SOCI E ASPIRANTI SOCI MINORENNI E CONSENSO AL TRATTAMENTO";
        $pdf->MultiCell(0, 5, $titolo_informativa, 1, 'C', 0, 0, '', '', true);
        $pdf->ln(10);
        $pdf->SetFont('times', '', 8);
        $informativa="Gentile Signore/a,
    ai sensi degli art. 13 e 14 del Regolamento UE 2016/679 in materia di protezione dei dati personali (“GDPR”) La informiamo di quanto segue.
    Finalità del trattamento e base giuridica. L’Associazione tratterà i dati personali di Suo figlio/a esclusivamente per lo svolgimento dell’attività
    istituzionale ed in particolare:
    a) per la gestione del rapporto associativo (invio della corrispondenza, convocazione alle sedute degli organi, procedure amministrative interne
    b) per adempiere agli obblighi di legge (es. fiscali, assicurativi, ecc.) riferiti ai soci dell’Associazione;
    c) per l’invio (tramite posta, indirizzo e-mail o numero di cellulare o altri mezzi informatici) di comunicazioni legate all’attività e iniziative
    dell’Associazione
    d)
    in relazione alle immagini o video di Suo figlio/a, per la pubblicazione sul sito dell’Associazione, sulla pagina FB dell’Associazione o su newsletter o
    su materiale di promozione delle attività istituzionali dell’Associazione previo Suo esplicito consenso
    e) in relazione alla foto personale, per l’inserimento nel tesserino di riconoscimento
    f)
    per la partecipazione dei soci a corsi, incontri e iniziative e per l’organizzazione e gestione dei corsi
    g) per analisi statistiche, anche in forma aggregata.
    La base giuridica del trattamento è rappresentata dalla richiesta di adesione e dal contratto associativo (art. 6 comma 1 lett. b GDPR), dal consenso
    al trattamento (art. 6 comma 1 lett. a – art. 9 comma 2 lett. a GDPR), dai contatti regolari con l’Associazione (art. 9 comma 2 lett. d GDPR), dagli
    obblighi legali a cui è tenuta l’Associazione (art. 6 comma 1 lett. c GDPR)
    Modalità e principi del trattamento. Il trattamento avverrà nel rispetto del GDPR e del D.Lgs. n. 196/03 (“Codice in materia di protezione dei dati
    personali”), nonché dei principi di liceità, correttezza e trasparenza, adeguatezza e pertinenza, con modalità cartacee ed informatiche, ad opera di
    persone autorizzate dall’Associazione e con l’adozione di misure adeguate di protezione, in modo da garantire la sicurezza e la riservatezza dei dati.
    Non verrà svolto alcun processo decisionale automatizzato.
    Necessità del conferimento. Il conferimento dei dati anagrafici e di contatto è necessario in quanto strettamente legato alla gestione del rapporto
    associativo. Il consenso all’utilizzo delle immagini/video e alla diffusione dei dati nel sito istituzionale e nelle altre modalità sopra descritte è facoltativo.
    Comunicazione e trasferimento all’estero dei dati. I dati potranno essere comunicati ai soggetti deputati allo svolgimento di attività a cui
    l’Associazione è tenuta in base ad obbligo di legge (commercialista, assicuratore, sistemista, ecc.) e a tutte quelle persone fisiche e/o giuridiche,
    pubbliche e/o private quando la comunicazione risulti necessaria o funzionale allo svolgimento dell’attività istituzionale (formatori, Enti Locali, ditte
    che curano la manutenzione informatica, società organizzatrici dei corsi, ecc.). I dati potranno essere trasferiti a destinatari con sede extra UE che
    hanno sottoscritto accordi diretti ad assicurare un livello di protezione adeguato dei dati personali, o comunque previa verifica che il destinatario
    garantisca adeguate misure di protezione. Ove necessario o opportuno, i soggetti cui vengono trasmessi i dati per lo svolgimento di attività per
    conto dell’Associazione saranno nominati Responsabili (esterni) del trattamento ai sensi dell’art. 28 GDPR.
    Periodo di conservazione dei dati. I dati saranno utilizzati dall’Associazione fino alla cessazione del rapporto associativo. Dopo tale data, saranno
    conservati per finalità di archivio, obblighi legali o contabili o fiscali o per esigenze di tutela dell’Associazione, con esclusione di comunicazioni a terzi
    e in ogni caso applicando i principi di proporzionalità e minimizzazione.
    Diritti dell’interessato. Nella qualità di interessato, sono garantiti tutti i diritti specificati all’art. 15 - 20 GDPR, tra cui il diritto all’accesso, rettifica e
    cancellazione dei dati, il diritto di limitazione e opposizione al trattamento, il diritto di revocare il consenso al trattamento (senza pregiudizio per la
    liceità del trattamento basata sul consenso acquisito prima della revoca), nonché il di proporre reclamo al Garante per la Protezione dei dati personali
    qualora Lei ritenga che il trattamento che riguarda Suo figlio/a violi il GDPR o la normativa italiana. I suddetti diritti possono essere esercitati
    mediante comunicazione scritta da inviare a mezzo posta elettronica, p.e.c. o fax, o a mezzo Raccomandata presso la sede dell’Associazione.
    Il Data Protection Officer (DPO) nominato dall’Associazione è LOLLINO GIAN FRANCO, a cui ciascun interessato può scrivere, in relazione al
    trattamento dei dati svolto dall’Associazione e/o in relazione ai Suoi diritti, all’indirizzo lollinogianfranco@gmail.com. Il DPO può essere altresì contattato
    telefonicamente tramite l’Associazione al numero 3334055640.Il titolare del trattamento è l’Associazione Gruppo Astrofili \"N. Copernico\" - via Pulzona, 1708 Saludecio (RN) 47835.";
    }
    else {//Se e' maggiorenne
        $titolo_informativa = "INFORMATIVA EX ART. 13 GDPR PER SOCI E ASPIRANTI SOCI E CONSENSO AL TRATTAMENTO DATI";
        $pdf->MultiCell(0, 5, $titolo_informativa, 1, 'C', 0, 0, '', '', true);
        $pdf->ln(10);
        $pdf->SetFont('times', '', 8);
        $informativa="Caro socio/a o aspirante socio/a,
    ai sensi degli art. 13 e 14 del Regolamento UE 2016/679 in materia di protezione dei dati personali (“GDPR”) ti informiamo di quanto segue.
    Finalità del trattamento e base giuridica. L’Associazione tratta i tuoi dati personali esclusivamente per lo svolgimento dell’attività istituzionale ed
    in particolare:
    a) per la gestione del rapporto associativo (invio della corrispondenza, convocazione alle sedute degli organi, procedure amministrative
    interne) e per l’organizzazione ed esecuzione delle attività associative (workshop, incontri, corsi, ecc.)
    b) per adempiere agli obblighi di legge (es. fiscali, assicurativi, ecc.) riferiti ai soci dell’Associazione;
    c) per l’invio (tramite posta, posta elettronica, newsletter o numero di cellulare o altri mezzi informatici) di comunicazioni legate all’attività e
    iniziative dell’Associazione
    d) in relazione alle immagini/video, per la pubblicazione nel sito dell’Associazione, sui social network dell’Associazione o su newsletter o su materiale
    cartaceo di promozione delle attività istituzionali dell’Associazione previo Tuo esplicito consenso
    e) per la partecipazione dei soci a corsi, incontri e iniziative e per l’organizzazione e gestione dei corsi
    f) per analisi statistiche, anche in forma aggregata.
    La base giuridica del trattamento è rappresentata dalla richiesta di adesione e dal contratto associativo (art. 6 comma 1 lett. b GDPR), dal consenso
    al trattamento (art. 6 comma 1 lett. a – art. 9 comma 2 lett. a GDPR), dai contatti regolari con l’Associazione (art. 9 comma 2 lett. d GDPR), dagli
    obblighi legali a cui è tenuta l’Associazione (art. 6 comma 1 lett. c GDPR)
    Modalità e principi del trattamento. Il trattamento avverrà nel rispetto del GDPR e del D.Lgs. n. 196/03 (“Codice in materia di protezione dei dati
    personali”), nonché dei principi di liceità, correttezza e trasparenza, adeguatezza e pertinenza, con modalità cartacee ed informatiche, ad opera di
    persone autorizzate dall’Associazione e con l’adozione di misure adeguate di protezione, in modo da garantire la sicurezza e la riservatezza dei
    dati. Non verrà svolto alcun processo decisionale automatizzato.
    Necessità del conferimento. Il conferimento dei dati anagrafici e di contatto è necessario in quanto strettamente legato alla gestione del rapporto
    associativo. Il consenso all’utilizzo delle immagini/video e alla diffusione dei dati nel sito istituzionale e nelle altre modalità sopra descritte è facoltativo.
    Comunicazione dei dati e trasferimento all’esterno dei dati. I dati potranno essere comunicati agli altri soci ai fini dell’organizzazione ed esecuzione
    del servizio. I dati potranno essere comunicati ai soggetti deputati allo svolgimento di attività a cui l’Associazione è tenuta in base ad obbligo di
    legge (commercialista, assicuratore, sistemista, ecc.) e a tutte quelle persone fisiche e/o giuridiche, pubbliche e/o private quando la comunicazione
    risulti necessaria o funzionale allo svolgimento dell’attività istituzionale (formatori, Enti Locali, ditte che curano la manutenzione informatica,
    società organizzatrici dei corsi, ecc.). I dati potranno essere trasferiti a destinatari con sede extra UE che hanno sottoscritto accordi diretti ad
    assicurare un livello di protezione adeguato dei dati personali, o comunque previa verifica che il destinatario garantisca adeguate misure di
    protezione. Ove necessario o opportuno, i soggetti cui vengono trasmessi i dati per lo svolgimento di attività per conto dell’Associazione saranno
    nominati Responsabili (esterni) del trattamento ai sensi dell’art. 28 GDPR.
    Periodo di conservazione dei dati. I dati saranno utilizzati dall’Associazione fino alla cessazione del rapporto associativo. Dopo tale data, saranno
    conservati per finalità di archivio, obblighi legali o contabili o fiscali o per esigenze di tutela dell’Associazione, con esclusione di comunicazioni a
    terzi e diffusione in ogni caso applicando i principi di proporzionalità e minimizzazione.
    Diritti dell’interessato. Nella qualità di interessato, Ti sono garantiti tutti i diritti specificati all’art. 15 - 20 GDPR, tra cui il diritto all’accesso,
    rettifica e cancellazione dei dati, il diritto di limitazione e opposizione al trattamento, il diritto di revocare il consenso al trattamento (senza
    pregiudizio per la liceità del trattamento basata sul consenso acquisito prima della revoca), nonché di proporre reclamo al Garante per la
    Protezione dei dati personali qualora tu ritenga che il trattamento che ti riguarda violi il GDPR o la normativa italiana. I suddetti diritti possono
    essere esercitati mediante comunicazione scritta da inviare a mezzo posta elettronica, pec, o a mezzo Raccomandata presso la sede
    dell’Associazione.
    Il Data Protection Officer (DPO) nominato dall’Associazione è LOLLINO GIAN FRANCO, a cui ciascun interessato può scrivere, in relazione al
    trattamento dei dati svolto dall’Associazione e/o in relazione ai Suoi diritti, all’indirizzo lollinogianfranco@gmail.com. Il DPO può essere altresì contattato
    telefonicamente tramite l’Associazione al numero 3334055640.
    Titolare del trattamento. Il titolare del trattamento è l’Associazione Gruppo Astrofili \"N. Copernico\" - via Pulzona, 1708 Saludecio (RN) 47835.";
    }
    $pdf->MultiCell(0, 10, $informativa, 1, 'L', 0, 0, '', '', true);
    $pdf->ln(160);
    $pdf->MultiCell(0, 10, "Io sottoscritto/a ".$member->cognome." ".$member->nome." nella qualita' di interessato, letta la suddetta informativa resa ai sensi dell’art. 13 GDPR, autorizzo/do il consenso", 0, 'L', 0, 0, '', '', true);
    $pdf->ln(6);
    $pdf->RadioButton('adesione_statuto', 5, array(), array(), '', true);
    $pdf->MultiCell(190, 5, 'al trattamento dei miei dati personali, da svolgersi in conformità a quanto indicato nella suddetta informativa e nel rispetto delle disposizioni
    del GDPR e del D.Lgs. n. 196/03',0,'L');
    $pdf->ln(6);
    $pdf->RadioButton('adesione_diffusione', 5, array(), array(), '', ($adesioni & 1 ? true : false));
    $pdf->MultiCell(190, 5, 'alla diffusione del mio nome e cognome, della mia immagine o di video che mi riprendono nel sito istituzionale, nei social network (es. pagina
    Facebook/Instagram/Youtube) e sul materiale informativo cartaceo dell’Associazione, per soli fini di descrizione e promozione dell’attività
    istituzionale, nel rispetto delle disposizioni del GDPR e del D.Lgs. n. 196/03 e delle autorizzazioni/indicazioni della Commissione UE e del Garante
    per la Protezione dei Dati Personali',0,'L');


    $string=base64_encode(file_get_contents(str_replace(" ", "", SIGNATURE_IMAGE_PATH.$member->firma)));
    if($string)
    {
        $img = '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $string) . '">';
        $pdf->writeHTMLCell(0,0,95,230,$img,'B');
        $pdf->ln(32);
    }
    else
    {
        $pdf->ln(25);
    }  

    /* Controllo se minorenne o maggiorenne */
    if($adesioni & 4) //Se e' minorenne
        $pdf->MultiCell(140, 10, "(firma del genitore)", 0, 'R', 0, 0, '', '', true);
    else //Se e' maggiorenne
        $pdf->MultiCell(140, 10, "(firma del socio)", 0, 'R', 0, 0, '', '', true);

    /* Salvo il PDF */
    $pdf->Output(PDF_PATH.$member->tessera."-".$member->cognome.$member->nome.".pdf", 'F');
}




?>
</div>
</div>
<script type="text/javascript" src="../js/jquery-1.11.1.js"> </script>
<script type="text/javascript">
$(document).ready(function(){

    /* Se creo un file (backup o espartazione che sia) abilito sempre il pulsante Esegui */
    $(".enable_submit").change(function() {
        $('input[type="submit"]').attr('disabled', false);
    });

    
    /* Se faccio il restore e il file non e' caricato disabilito il pulsante Esegui */
    $(".disable_submit").change(function() {
    	if ($(this).prop('checked')) {
            if($('input[type=file]').val()=="") {
                $('input[type="submit"]').attr('disabled', true);
            }
            else {
                $('input[type="submit"]').attr('disabled', false);
            }
    	}    	    
    });


    /* Se carico un file riattivo il pulsante Esegui */
    $('input[type=file]').change(function(e){
        if($(this).val()!="")
        	$('input[type="submit"]').attr('disabled', false);
    })

    
});
</script>
</body> 
</html>