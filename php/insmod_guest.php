<?php
require "member.php";

/* Togliere commento per debug */
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

?>
<!DOCTYPE html>
<html>
<head>
<title>Gruppo Astrofili "N. Copernico"</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="../css/index_guest.css" media="all"/>
</head>
<body>
<div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
	<div class="wrapper wrapper--w680">
        <div class="card card-1">
            <div class="card-heading"></div>
            <div class="card-body" style="font-size: 17px; font-family: Times new Roman">            
            <?php
            // TODO Implementare il logger per chi si registra online 
            //$mylog_guest=$_SESSION['logger'];
            
            /* Mi connetto al database FIXME Occhio se sbaglio connessione a non far apparire in chiaro l'errore*/
            try {
                $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "",[PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            }
            catch (PDOException $e) {               
                ?>
                <div style="display: flex; justify-content: center;">
            		<img src="../img/check_ko.png" height="256" width="256">
        		</div>
        		<?php
        		errorMessage($e);
        		die();
            }
            
            /* Inizializzo un array di formattazione */
            $format_input=array("cognome" => "FUC",
                                "nome" => "FUC",
                                "cf" => "UC",
                                "comune_nascita" => "FUC",
                                "provincia_nascita" => "UC",
                                "stato_nascita" => "UC",
                                "sesso" => "UC",
                                "indirizzo" => "FUC",
                                "citta" => "FUC",
                                "cap" => "UC",
                                "provincia" => "UC",
                                "stato" => "UC",
                                "email" => "LC");                               
            
            /* Formatto i dati provenienti da $_POST */
            $formatter= new InputFormat($format_input);
            $formatter->format($_POST);
            
            /* Inserisco i dati in anagrafica */
            try {                
                $prepared=$dbh->prepare("INSERT INTO anagrafica (cognome,
                                                                    nome,
                                                                    data_nascita,
                                                                    cf,
                                                                    comune_nascita,
                                                                    provincia_nascita,
                                                                    stato_nascita,
                                                                    sesso,
                                                                    indirizzo,
                                                                    citta,
                                                                    cap,
                                                                    provincia,
                                                                    stato,
                                                                    telefono,
                                                                    email)
                                                                    VALUES (?, ?, STR_TO_DATE(?, '%d/%m/%Y'), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
                $prepared->execute([$_POST['cognome'],
                                    $_POST['nome'],
                                    $_POST['data_nascita'],
                                    $_POST['cf'],
                                    $_POST['comune_nascita'],
                                    $_POST['provincia_nascita'],
                                    $_POST['stato_nascita'],
                                    NULL,
                                    $_POST['indirizzo'],
                                    $_POST['citta'],
                                    $_POST['cap'],
                                    $_POST['provincia'],
                                    $_POST['stato_residenza'],
                                    $_POST['telefono'],
                                    $_POST['email'],
                                    ]);
            }
            catch (PDOException $e) {               
                ?>
                <div style="display: flex; justify-content: center;">
            		<img src="../img/check_ko.png" height="256" width="256">
        		</div>
        		<?php
        		errorMessage($e);
        		die();
            }
            
            /* Leggo i flag di adesione */
            try {
                $prepared=$dbh->prepare("SELECT CAST(adesioni AS unsigned integer) FROM socio WHERE cf=?");
                $prepared->execute([$_POST['cf']]);
            }
            catch (PDOException $e) {               
                ?>
                <div style="display: flex; justify-content: center;">
            		<img src="../img/check_ko.png" height="256" width="256">
        		</div>
        		<?php 
        		errorMessage($e);
        		die();
            }
            
            /* Trasformo la stringa in unsigned char e faccio la verifica bit a bit */
            $adesioni=pack('C', $prepared->fetch(PDO::FETCH_COLUMN));                                      
            isset($_POST['diffusione_nominativo']) ? $adesioni|=1 : $adesioni&=254;
            isset($_POST['newsletter']) ? $adesioni|=2 : $adesioni&=253;
            
            /* Inserisco i dati in socio */
            try {
                $prepared=$dbh->prepare("INSERT INTO socio (cf,
                                                            scadenza,
                                                            data_tessera,
                                                            numero_tessera,
                                                            adesioni,
                                                            firma)
                                                            VALUES(?, DATE_ADD(LAST_DAY(DATE_ADD(NOW(), INTERVAL 12-MONTH(NOW()) MONTH)),".DROP_IDENTITY_MYSQL."), ?, ?, ?, ?)");
                
                $prepared->execute([$_POST['cf'], NULL, NULL, $adesioni, $_POST['cognome'].$_POST['nome']."-".str_replace("/", "", $_POST['data_nascita']).".png"]);
            }
            catch (PDOException $e) {               
                ?>
                <div style="display: flex; justify-content: center;">
            		<img src="../img/check_ko.png" height="256" width="256">
        		</div>
        		<?php
        		/* Se ho inserito correttamente i dati in anagrafica li devo cancellare */
        		$prepared=$dbh->prepare("DELETE FROM anagrafica WHERE cf=?");
        		$prepared->execute([$_POST['cf']]);
        		
        		errorMessage($e);
        		die();
            }
            ?>
            <div style="display: flex; justify-content: center;">
            	<img src="../img/rocket.png" height="256" width="256">
        	</div>
        	<?php 
        	successMessage()
        	?>       
		</div>
	</div>
</div>
</div>
<?php 
function errorMessage(PDOException $ex) {
    echo "La procedura di iscrizione e' fallita con codice errore: ".$ex->errorInfo[1];
    echo "<p>Torna alla pagina di registrazione e verifica di non essere gia' iscritto. Verifica inoltre i dati immessi nei campi.</p>";
    echo "<p>Se l'errore persiste invia una segnalazione cliccando sull'apposito link nella pagina di registrazione.</p>";
}

function successMessage() {
    echo "<br><ul style='font-family: Arial; font-size:15px'>";
    echo "COMPLIMENTI, la Tua iscrizione e' andata a buon fine.";
    echo "<li>Ora puoi recarti in Osservatorio nei giorni di apertura e, previo il versamento della quota associativa di 5€, Ti sara' rilasciata subito la tessera.</li>";
    echo "<li>La tessera e' valida un anno solare (sino al 31/12 dell'anno in corso)</li>";
    echo "<li>I tuoi dati di iscrizione saranno automaticamente cancellati dal nostro archivio dopo cinque anni consecutivi in cui non rinnovi la tessera. Puoi comunque richiedere la cancellazione dei tuoi dati inviando una segnalazione alla pagina di registrazione.</li>";
    echo "</ul><br>";
}
?>
</body>
</html>