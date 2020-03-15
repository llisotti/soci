<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Colorlib Templates">
    <meta name="author" content="Colorlib">
    <meta name="keywords" content="Colorlib Templates">

    <!-- Title Page-->
    <title>Osservatorio Copernico - HelpDesk</title>

    <!-- Icons font CSS-->
    <link href="../vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="../vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <!-- Font special for pages-->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i" rel="stylesheet">

    <!-- Vendor CSS-->
    <link href="../vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="../vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="../css/index_guest.css" rel="stylesheet" media="all">
    

</head>
<body>
    <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
        <div class="wrapper wrapper--w680">
            <div class="card card-1">
                <div class="card-heading">
                <?php
                /* Se l'utente accede con Internet Explorer o col browser stock di Android non lo faccio proseguire */
                $ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
                $matches = [];
                preg_match ( '/Android.*AppleWebKit\/([\d.]+)/', $ua, $matches);
                if (preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos($ua, 'Trident/7.0') !== false && strpos($ua, 'rv:11.0') !== false)) {
                    echo "<br><br>Il browser Microsoft explorer potrebbe non funzionare correttamente. Si prega di utilizzare un altro browser (ad esempio Google Chrome, Firefox, ecc..)><br><br>";
                    die();
                }
                elseif( isset($matches[0]) && ( isset($matches[1]) && intval($matches[1] < 537))) {
                    echo "<br><br>Il browser stock di Android potrebbe non funzionare correttamente. Si prega di utilizzare un altro browser (ad esempio Google Chrome, Firefox, ecc..)<br><br>";
                    die();
                }
                ?>
                </div>
   		<div class="card-body" style="font-size: 17px; font-family: Times new Roman">
            <h4 class="title">Compila i campi sottostanti per inviare la segnalazione</h4>

		    <div class="row row-space">
		    	<div class="col-2">
            		<div class="input-group">
                        <input id="cognome" style="text-transform: capitalize" class="input--style-1" type="text" placeholder="Cognome" name="cognome" required>
					</div>
				</div>
				<div class="col-2">
					<div class="input-group">
                        <input id="nome" style="text-transform: capitalize" class="input--style-1" type="text" placeholder="Nome" name="nome" required>
                    </div>
				</div>
			</div>
			<div class="row row-space">
		    <div class="col-2">
                        <div class="input-group">
                            <input type="text" id="contatto" class="input--style-1" placeholder="Telefono o email" name="telefono" required>
			</div>
			</div>
			</div>
			<div class="row row-space">
		    	<div class="col-2">
                	<div class="input-group">
                         <textarea id="messaggio" class="area" rows="6" cols="68" style="resize:none; " placeholder="testo segnalazione"></textarea> 
					</div>
				</div>
			</div>
        		<div class="p-t-20">
                            <button id="bt" class="btn btn--radius btn--green" type="submit" style="float: right; padding: 5px 20px 5px 20px">Invia segnalazione</button><br>
                        </div>
	    
                </div>
            </div>
        </div>
    </div>

<script type="text/javascript" src="../js/jquery-1.11.1.js"> </script>
<script type="text/javascript">
$(document).ready(function(){


    /* Validazione cognome */
	$("#cognome").blur(function() {
		var hasNumber = /\d/;
		var value = $(this).val();
		if(hasNumber.test(value) || !($.trim(value)))
        	alert("Attenzione: controllare che il cognome immesso sia corretto");
	});

	/* Validazione nome */
	$("#nome").blur(function() {
		var hasNumber = /\d/;
		var value = $(this).val();
		if(hasNumber.test(value) || !($.trim(value)))
        	alert("Attenzione: controllare che il nome immesso sia corretto");
	});

	/* Invio segnalazione al bot telegram OCHelpDesk */
	$('#bt').on('click', function(e) {
        e.preventDefault();
		var cognome = $("#cognome").val();
		var nome = $("#nome").val();
		var contatto = $("#contatto").val();
		var messaggio = $("#messaggio").val();
		if(cognome.trim() && nome.trim() && contatto.trim() && messaggio.trim()) {
    		$.ajax({
    			type: "GET",
    	        url: "https://api.telegram.org/bot934524385:AAF1mrxMHPyXRWtVGBfFgWwj8WZDokIrevc/sendMessage?chat_id=123730580&text=Cognome: "+cognome+"%0ANome: "+nome+"%0Acontatto: "+contatto+"%0ASegnalazione: "+messaggio,
    	        dataType: 'html',
    	        async: false,		
        		success: function() {
                    alert("La Tua richiesta e' stata inviata correttamente. Sarai contattato il prima possibile\nGrazie");
                    $("#bt").prop("disabled", true);           
        		},
        		error: function() {
                    alert("C'e' stato un problema nell'elaborazione della Tua richiesta. Riprova piu' tardi. Nel caso il problema persista puoi inviare una mail a: lisotti.l@osservatoriocopernico.it\nGrazie");
        		}
    		});
		}
		else
			alert("Per inviare una segnalazione e' necessario compilare tutti i campi");
	});       
});
</script>
<?php
/* Come inviare mesasggi al bot in php
$ret=false;
$ret=file_get_contents("https://api.telegram.org/bot934524385:AAF1mrxMHPyXRWtVGBfFgWwj8WZDokIrevc/sendMessage?chat_id=123730580&text="
    ."Cognome: ".$_POST['cognome']
    ."%0ANome: ".$_POST['nome']
    ."%0Acontatto: ".$_POST['contatto']
    ."%0ASegnalazione: ".$_POST['oggetto']);

if (!$ret)
    echo "ko";
else 
    echo "ok";*/
?>
</body>
</html>



