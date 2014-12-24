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
<table>		
<?php
switch ($_GET['action'])
{
    case "members_export":
        if(!isset($_POST['export']))
        {
        ?>
            <form action="" method="post">
            <!-- <input autocomplete="off" type="password" name="root_password" autofocus placeholder="Password"/> -->
            <input type="submit" name="export" value="Esporta" />
            </form>
        <?php
        }
        else
        {
            /* Mi connetto al database */
            try
            {
                $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
            }
            catch (PDOException $exception)
            {
                echo '<img src="../img/check_ko.png" height="100" width="100">';
                echo "<br/>Errore di connessione al database: ".$exception->getMessage();
                die();
            }
            $date=new DateTime();
            $year=$date->format('Y');
            
            $members=$dbh->query("(SELECT 'Numero tessera', 'Cognome', 'Nome', 'Data di nascita', 'Data tessera') UNION "
                    ."(SELECT tessera, cognome, nome, DATE_FORMAT(anagrafica.data_nascita, '%d/%m/%Y') data_nascita, DATE_FORMAT(presenze.data, '%d/%m/%Y') data FROM anagrafica "
                    ."INNER JOIN presenze ON anagrafica.member_id = presenze.member_id WHERE anagrafica.tessera IS NOT NULL "
                    ."ORDER BY anagrafica.tessera DESC "
                    ."INTO OUTFILE 'D:\\\\dati\\\\xampp\\\\htdocs\\\\soci\\\\sql\\\\".$year."-ELENCO_SOCI.csv' "
                    ."FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n')");
            if(!$members)
            {
                echo '<img src="../img/check_ko.png" height="100" width="100">';
                echo "<br/>ERRORE CREAZIONE FILE !";
            }
            else
            {
                echo '<img src="../img/check_ok.png" height="100" width="100">';
                echo "<br/>FILE "?><a href="http://localhost/soci/sql/<?php echo $year."-ELENCO_SOCI.csv";?>"><?php echo $year."-ELENCO_SOCI ";?></a><?php echo "CREATO CORRETTAMENTE";     
            }
            
            $dbh = null; //Chiudo la connessione con mysql come root 
        }
        break;
    default:
        break;
}
?>
</div>	
</body> 
</html>