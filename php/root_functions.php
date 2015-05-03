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
    <input type="submit" name="export" value="Esporta" />
    </form>
    <?php
    die();
}

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
        $members=$dbh->query(" SELECT cognome, nome, DATE_FORMAT(anagrafica.data_nascita, '%d/%m/%Y') data_nascita, tessera, DATE_FORMAT(presenze.data, '%d/%m/%Y') data FROM anagrafica "
                ."INNER JOIN presenze ON anagrafica.member_id = presenze.member_id WHERE anagrafica.tessera IS NOT NULL "
                ."ORDER BY anagrafica.cognome ASC "
                ."INTO OUTFILE 'D:\\\\dati\\\\xampp\\\\htdocs\\\\soci\\\\doc\\\\".$data."_Elenco soci.csv' "
                ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' ");
        if(!$members) {
            echo '<img src="../img/check_ko.png" height="100" width="100" alt="check_ko">';
            echo "<br/>ERRORE CREAZIONE FILE !";
        }
        else {
            echo '<img src="../img/check_ok.png" height="100" width="100" alt="check_ok">';
            echo "<br/>FILE "?><a href="http://localhost/soci/doc/<?php echo $data."_Elenco soci.csv";?>"><?php echo $data."_Elenco soci ";?></a><?php echo "CREATO CORRETTAMENTE";     
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
        }
        else {
            echo '<img src="../img/check_ok.png" height="100" width="100" alt="check_ok">';
            echo "<br/>FILE "?><a href="http://localhost/soci/doc/<?php echo $data."_Elenco identita'.csv";?>"><?php echo $data."_Elenco identità ";?></a><?php echo "CREATO CORRETTAMENTE";     
        }
        break;
    case "backup": //Creazione backup database
        system("D:\\\\dati\\\\xampp\\\\mysql\\\\bin\\\\mysqldump.exe -u copernico --routines soci > ".BACKUP_PATH.$data."_Backup.sql 2>&1", $return_value);
        if($return_value!=0) {
            echo '<img src="../img/check_ko.png" height="100" width="100" alt="check_ko">';
            echo "<br/>ERRORE BACKUP: controllare il contenuto del file di backup!";
        }           
        else {
            echo '<img src="../img/check_ok.png" height="100" width="100" alt="check_ok">';
            echo "<br/>BACKUP OK";
        }
        break;            
    default:
        break;
}
?>
</div>
</div>	
</body> 
</html>