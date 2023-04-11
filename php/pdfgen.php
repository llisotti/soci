<?php

require "member.php"; //OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE  !
require_once('./TCPDF/tcpdf.php');

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Include the main TCPDF library (search for installation path).


/* Mi connetto al database */
try {
    if(!isset($dbh)) {
        $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
    }
}
catch (PDOException $exception) {
    $mylog->logError("Errore di connessione al database: ".$exception->getMessage());
    die("Errore di connessione al database: ".$exception->getMessage());
}

$members=$dbh->query("SELECT *, DATE_FORMAT(anagrafica.data_nascita,'%d/%m/%Y') data_nascita, DATE_FORMAT(socio.scadenza,'%d/%m/%Y') scadenza, DATE_FORMAT(socio.data_tessera,'%d/%m/%Y') data_tessera FROM anagrafica LEFT JOIN socio ON anagrafica.id = socio.id WHERE socio.numero_tessera = $_GET[tessera]");
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
$pdf->SetHeaderData("../../../../img/logo_copernico.jpg", "90", "MODULO ADESIONE N. ".$_GET['tessera'], $member->cognome." ".$member->nome, "");
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
if($string==false)
    $pdf->writeHTML("ERRORE, firma non trovata", true, false, true, false, '');

$img = '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $string) . '">';
$pdf->writeHTMLCell(0,0,95,170,$img,'B');
$pdf->ln(32);

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
if($string==false)
    $pdf->writeHTML("ERRORE, firma non trovata", true, false, true, false, '');

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
if($string==false)
    $pdf->writeHTML("ERRORE, firma non trovata", true, false, true, false, '');
    
$img = '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $string) . '">';
$pdf->writeHTMLCell(0,0,95,230,$img,'B');
$pdf->ln(32);

/* Controllo se minorenne o maggiorenne */
//$adesioni=pack('C', $member->flags);
if($adesioni & 4) //Se e' minorenne
    $pdf->MultiCell(140, 10, "(firma del genitore)", 0, 'R', 0, 0, '', '', true);
else //Se e' maggiorenne
    $pdf->MultiCell(140, 10, "(firma del socio)", 0, 'R', 0, 0, '', '', true);

/* Stampo e apro il PDF */
$pdf->Output(PDF_PATH.$member->tessera."-".$member->cognome.$member->nome.".pdf", 'FI');


?>