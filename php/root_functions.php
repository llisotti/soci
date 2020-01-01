<?php
require "member.php"; //OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE  !

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
				<tr><td style="text-align: left"><input class="enable_submit" name="azione" value="frontespizio" type="radio" checked />Estrai frontespizio</td></tr>
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="allmembers" type="radio" checked />Esporta tutti i soci</td></tr>
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="members_evening" type="radio" />Esporta soci serata</tr>
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
                <tr><td style="text-align: left"><input name="azione" value="allidentities" type="radio" />Esporta tutte le identità</td></tr>
            </table>                    
            <hr>
            <table>           
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="backup-completo" type="radio" />Backup database</td></tr>
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="backup-solodati" type="radio" />Backup database (solo dati)</td></tr>
                <tr><td style="text-align: left"><input class="enable_submit" name="azione" value="backup-solostruttura" type="radio" />Backup database (solo struttura)</td></tr>
                <tr><td style="text-align: left"><input class="disable_submit" name="azione" value="restore" type="radio" />Ripristino database</td><td><input type="file" name="restorefile" accept=".sql" /></td>
                <td><img style="border:0;height:31px" src="../img/question.png" alt="" title="Il file .csv (separato da , e fine riga \n) deve trovarsi nella cartella /doc" /><td>
                </tr>
            </table>
            <hr>
            <table>           
                <tr><td style="text-align: left"><input class="disable_submit" name="azione" value="loadCustomList" type="radio"/>Carica lista custom newsletter</td>
                <td><input type="file" name="customList" accept=".csv" /></td>
                <td><img style="border:0;height:31px" src="../img/question.png" alt="" title="Il file .csv (separato da , e fine riga \n) deve trovarsi nella cartella /doc e deve avere i seguenti campi: codice_fiscale,cognome,nome,email" /><td>             
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
        case "update": //Aggiornamento software
            $mylog->logInfo("Tentativo di aggiornamento software dalla versione ".VERSION);
            system(GIT_EXECUTABLE."pull --tags origin master", $return_value); //Per eseguire delle prove aggiungere --dry-run al comando git
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
            echo "Aggiornamento fallito!, <a href=http://{$_SERVER['HTTP_HOST']}/soci/index.php>Torna alla Pagina iniziale</a>";
        }
        break;
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
        case "frontespizio": //Esporto tutti i soci
            $members=$dbh->query(" SELECT numero_tessera, anagrafica.cognome, anagrafica.nome FROM socio "
            ."INNER JOIN anagrafica ON anagrafica.cf = socio.cf WHERE socio.numero_tessera IS NOT NULL "
            ."ORDER BY socio.numero_tessera ASC "
            ."INTO OUTFILE '".BACKUP_PATH.$data."_frontespizio.csv' "
            ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' ");
            break;
        case "allmembers": //Esporto tutti i soci
            $members=$dbh->query(" SELECT * FROM anagrafica "
            ."INNER JOIN socio ON anagrafica.cf = socio.cf WHERE socio.numero_tessera IS NOT NULL "
            ."ORDER BY socio.numero_tessera ASC "
            ."INTO OUTFILE '".BACKUP_PATH.$data."_Elenco soci.csv' "
            ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' ");
            break;
        case "members_evening": //Esporto i soci inseriti in questa sessione
            $cards=  join(',', $_SESSION['members_evening']);
            echo $cards;
            $members=$dbh->query(" SELECT numero_tessera, anagrafica.cognome, anagrafica.nome FROM socio "
            ."INNER JOIN anagrafica ON anagrafica.cf = socio.cf WHERE socio.numero_tessera IN ($cards) "
            ."ORDER BY socio.numero_tessera ASC "
            ."INTO OUTFILE '".BACKUP_PATH.$data."_Elenco soci serata.csv'  "
            ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' ");              
            break;
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
            $members=$dbh->query(" SELECT numero_tessera, anagrafica.cognome, anagrafica.nome FROM socio "
            ."INNER JOIN anagrafica ON anagrafica.cf = socio.cf WHERE socio.data_tessera>='$start' AND socio.data_tessera<='$end' "
            ."ORDER BY socio.numero_tessera ASC "
            ."INTO OUTFILE '".BACKUP_PATH.$data."_Elenco soci $namefile.csv' "
            ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' ");                
            break;
        case "allidentities": //Esporto identità
            $members=$dbh->query(" SELECT * FROM anagrafica "
            ."INNER JOIN socio ON anagrafica.cf = socio.cf "
            ."ORDER BY socio.numero_tessera ASC "
            ."INTO OUTFILE '".BACKUP_PATH.$data."_Elenco identita\'.csv' "
            ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' ");
            break;
        case "backup-completo": //Faccio il backup completo (struttura + dati)
            system(MYSQLDUMP_EXECUTABLE."-u copernico --routines soci > ".BACKUP_PATH.$data."_Backup.sql 2>&1", $return_value);
            break;
        case "backup-solodati": //Faccio il backup solo dei dati (non della struttura)
            system(MYSQLDUMP_EXECUTABLE."-u copernico --routines soci --no-create-info > ".BACKUP_PATH.$data."_Backup.sql 2>&1", $return_value);
            break;
        case "backup-solostruttura": //Faccio il backup solo della struttura (non dei dati)
            system(MYSQLDUMP_EXECUTABLE."-u copernico --routines soci --no-data > ".BACKUP_PATH.$data."_Backup.sql 2>&1", $return_value);
            break;
        case "restore": //Faccio il backup solo della struttura (non dei dati)
            system(MYSQL_EXECUTABLE."-u copernico soci < ".BACKUP_PATH.$_POST['restorefile']." 2>&1", $return_value);
        break;
        case "loadCustomList": //Caricamento custom list per newsletter
            $members=$dbh->query("DELETE FROM customList"); //svuoto l'attuale lista custom
            $members=$dbh->query("LOAD DATA INFILE '".BACKUP_PATH.$_POST[customList]."' INTO TABLE customList FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'");         
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