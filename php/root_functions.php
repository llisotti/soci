<?php
require "member.php"; //OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE  !
session_start();

/* Setto la sessione di 5 ore */
ini_set('session.gc_maxlifetime',18000);
//echo ini_get('session.gc_maxlifetime'); 
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
/* Se apro per la prima volta la pagina mostro il pulsante Esporta e termino lo script */
if(!isset($_POST['export'])) {
    ?>
    <form action="<?php echo "$_SERVER[PHP_SELF]"."?"."$_SERVER[QUERY_STRING]" ?>" method="post">
    <!-- <input autocomplete="off" type="password" name="root_password" autofocus placeholder="Password"/> -->
    <!-- <input type="submit" name="export" value="Esegui" /> <br/><br/> -->
    <?php
    if(isset($_GET['action'])) {
        switch ($_GET['action']) {
            case "DB_functions":
                ?>
                <table>
                    <tr><td style="text-align: left"><input name="azione" value="backup" type="radio" checked="" />Backup database</td></tr>
                    <tr><td style="text-align: left"><input name="azione" value="restore" type="radio" />Ripristino database</td><td><input type="file" name="restorefile" accept=".sql" /></td></tr>
                </table>
                <?php
                break;
            case "members_export":
                $time=getdate();
                $date=new DatesForSelect();
                ?>
                <table>
                    <tr><td style="text-align: left"><input name="azione" value="all" type="radio" checked="" />Esporta tutti i soci</td></tr>
                    <tr><td style="text-align: left"><input name="azione" value="evening" type="radio" />Esporta soci serata</tr>
                    <tr><td style="text-align: left"><input name="azione" value="date" type="radio" />Esporta soci dal</td>
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
                </table>
                <?php
                break;
            case "view_members_evening":
                foreach (array_reverse($_SESSION['members_evening'], TRUE) as $key => $value) {
                    if($key==0)
                        continue;
                    echo $key."^<sup>a</sup> tessera inserita: ".$value."<br/>";
                }
                die();
            case "identities_export":
                ?>
                <table>
                    <tr><td style="text-align: left"><input name="azione" value="backup" type="radio" checked="" hidden="hidden" />Esporta tutte le identità</td></tr>                    
                </table>
                <?php
                break;
            default:
                break;
        }
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
    switch ($_GET['action']) {
        case "members_export": //Esporto soci
            switch ($_POST['azione']) {
                case "all": //Esporto tutti i soci
                    $members=$dbh->query(" SELECT cognome, nome, DATE_FORMAT(anagrafica.data_nascita, '%d/%m/%Y') data_nascita, tessera, DATE_FORMAT(presenze.data, '%d/%m/%Y') data FROM anagrafica "
                    ."INNER JOIN presenze ON anagrafica.member_id = presenze.member_id WHERE anagrafica.tessera IS NOT NULL "
                    ."ORDER BY anagrafica.cognome ASC "
                    ."INTO OUTFILE 'D:\\\\dati\\\\xampp\\\\htdocs\\\\soci\\\\doc\\\\".$data."_Elenco soci.csv' "
                    ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' ");
                    break;
                case "evening": //Esporto i soci inseriti in questa sessione
                    $cards=  join(',', $_SESSION['members_evening']); 
                    $members=$dbh->query(" SELECT DATE_FORMAT(presenze.data, '%d/%m/%Y') data, cognome, nome, tessera FROM anagrafica " //ATTENZIONE alla SQL Injection con questa query !!
                    ."INNER JOIN presenze ON anagrafica.member_id = presenze.member_id WHERE anagrafica.tessera IN ($cards) "
                    ."ORDER BY anagrafica.tessera ASC "
                    ."INTO OUTFILE 'D:\\\\dati\\\\xampp\\\\htdocs\\\\soci\\\\doc\\\\".$data."_Elenco soci serata.csv' "
                    ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' ");                
                    break;
                case "date": //Esporto i soci da una certa data ad una certa data
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
                    $members=$dbh->query(" SELECT DATE_FORMAT(presenze.data, '%d/%m/%Y') data, cognome, nome, tessera FROM anagrafica " //ATTENZIONE alla SQL Injection con questa query !!
                    ."INNER JOIN presenze ON anagrafica.member_id = presenze.member_id WHERE presenze.data>='$start' AND presenze.data<='$end' "
                    ."ORDER BY anagrafica.tessera ASC "
                    ."INTO OUTFILE 'D:\\\\dati\\\\xampp\\\\htdocs\\\\soci\\\\doc\\\\".$data."_Elenco soci $namefile.csv' "
                    ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' ");                
                    break;
                default:
                    break;
            }

            if(!$members) {
                echo '<img src="../img/check_ko.png" height="100" width="100" alt="check_ko">';
                echo "<br/>ERRORE CREAZIONE FILE !";
                $mylog->logError("Tentativo di esportare file soci fallito");
            }
            else {
                echo '<img src="../img/check_ok.png" height="100" width="100" alt="check_ok">';
                if($_POST['azione']=="all") { //Link al file di tutti i soci
                    echo "<br/>FILE "?><a href="http://localhost/soci/doc/<?php echo $data."_Elenco soci.csv";?>"><?php echo $data."_Elenco soci ";?></a><?php echo "CREATO CORRETTAMENTE";
                }
                elseif ($_POST['azione']=="evening") { //Link al file di tutti i soci
                    echo "<br/>FILE "?><a href="http://localhost/soci/doc/<?php echo $data."_Elenco soci serata.csv";?>"><?php echo $data."_Elenco soci serata ";?></a><?php echo "CREATO CORRETTAMENTE";
                }
                else {
                    echo "<br/>FILE "?><a href="http://localhost/soci/doc/<?php echo $data."_Elenco soci $namefile.csv";?>"><?php echo $data."_Elenco soci $namefile.csv ";?></a><?php echo "CREATO CORRETTAMENTE";
                }
                $mylog->logInfo("Tentativo di esportare file soci riuscito");
            }
            break;
        case "identities_export": //Esporto identità
            $members=$dbh->query(" SELECT anagrafica.member_id, cognome, nome, DATE_FORMAT(anagrafica.data_nascita, '%d/%m/%Y') data_nascita, DATE_FORMAT(presenze.iscrizione,'%d/%m/%Y') iscrizione FROM anagrafica "
                    ."INNER JOIN presenze ON anagrafica.member_id = presenze.member_id "
                    ."ORDER BY anagrafica.cognome ASC "
                    ."INTO OUTFILE 'D:\\\\dati\\\\xampp\\\\htdocs\\\\soci\\\\doc\\\\".$data."_Elenco identita\'.csv' "
                    ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' ");
             if(!$members) {
                echo '<img src="../img/check_ko.png" height="100" width="100" alt="check_ko">';
                echo "<br/>ERRORE CREAZIONE FILE !";
                $mylog->logError("Tentativo di esportare file identità fallito");
            }
            else {
                echo '<img src="../img/check_ok.png" height="100" width="100" alt="check_ok">';
                echo "<br/>FILE "?><a href="http://localhost/soci/doc/<?php echo $data."_Elenco identita'.csv";?>"><?php echo $data."_Elenco identità ";?></a><?php echo "CREATO CORRETTAMENTE";
                $mylog->logInfo("Tentativo di esportare file identità riuscito");
            }
            break;
        case "DB_functions": //Creazione backup database
            if($_POST['azione']=="backup") //Faccio il backup
                system("D:\\\\dati\\\\xampp\\\\mysql\\\\bin\\\\mysqldump.exe -u copernico --routines soci > ".BACKUP_PATH.$data."_Backup.sql 2>&1", $return_value);
            else //Faccio il restore
                system("D:\\\\dati\\\\xampp\\\\mysql\\\\bin\\\\mysql.exe -u copernico soci < ".BACKUP_PATH.$_POST['restorefile']." 2>&1", $return_value);
            if($return_value!=0) {
                echo '<img src="../img/check_ko.png" height="100" width="100" alt="check_ko">';
            if($_POST['azione']=="backup") {
                    echo "<br/>ERRORE BACKUP: controllare il contenuto del file di backup!";
                    $mylog->logError("Tentativo di effettuare il backup soci fallito (system return value: ".$return_value.")");
            }
                else {
                    echo "<br/>ERRORE RIPRISTINO";
                    $mylog->logError("Tentativo di effettuare il ripristino dal backup soci fallito (system return value: ".$return_value.")");
                }
            }           
            else {
                echo '<img src="../img/check_ok.png" height="100" width="100" alt="check_ok">';
                if($_POST['azione']=="backup") {
                    echo "<br/>BACKUP OK!";
                    $mylog->logInfo("Tentativo di effettuare il backup soci riuscito");
                }
                else {
                    echo "<br/>RIPRISTINO OK";
                    $mylog->logInfo("Tentativo di effettuare il ripristino soci riuscito");
                }
            }
        break;
    case "update": //Aggiornamento software
        $mylog->logInfo("Tentativo di aggiornamento software dalla versione ".VERSION);
        exec(GITPORTABLE_PATH."git.exe pull origin master --dry-run", $return_value );
        if($return_value==0) {
            $mylog->logInfo("Tentativo riuscito: nuova versione: ".VERSION);
            $_SESSION['update']=FALSE;
            echo "Aggiornamento riuscito, <a href=http://localhost/soci/index.php>Torna alla Pagina iniziale</a>";
        }
        else {
            $mylog->logError("Tentativo fallito");
            echo "Aggiornamento fallito!, <a href=http://localhost/soci/index.php>Torna alla Pagina iniziale</a>";
        }
        break;
    default:
        break;
    }
}
?>
</div>
</div>
</body> 
</html>