<?php
// Fehler anzeigen für die Debugging-Phase
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log-Datei erstellen
$log_file = 'form_log.txt';
function log_message($message) {
    global $log_file;
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

log_message("Formular wurde gesendet");

// Konfigurationsvariablen
$empfaenger_email = "test.strom@gmx.de"; // Empfänger-E-Mail-Adresse
$betreff = "Neue Kontaktanfrage von TarifWechsler.de";

// Überprüfen, ob das Formular abgesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    log_message("POST-Daten erhalten: " . print_r($_POST, true));
    
    // Formulardaten sammeln und bereinigen
    $name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : '';
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? filter_var($_POST['phone'], FILTER_SANITIZE_STRING) : '';
    $contactMethod = isset($_POST['contact-method']) ? filter_var($_POST['contact-method'], FILTER_SANITIZE_STRING) : '';
    $message = isset($_POST['message']) ? filter_var($_POST['message'], FILTER_SANITIZE_STRING) : '';
    
    log_message("Verarbeitete Daten: Name=$name, Email=$email, Telefon=$phone, Kontaktart=$contactMethod");
    
    // Überprüfen, ob die erforderlichen Felder ausgefüllt sind
    if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        log_message("Validierungsfehler: Erforderliche Felder nicht ausgefüllt oder ungültige E-Mail");
        $response = [
            'success' => false,
            'message' => 'Bitte füllen Sie alle Pflichtfelder korrekt aus.'
        ];
        echo json_encode($response);
        exit;
    }
    
    // E-Mail-Inhalt erstellen
    $email_inhalt = "Neue Kontaktanfrage über die Website:\n\n";
    $email_inhalt .= "Name: $name\n";
    $email_inhalt .= "E-Mail: $email\n";
    $email_inhalt .= "Telefon: $phone\n";
    $email_inhalt .= "Bevorzugte Kontaktart: $contactMethod\n";
    $email_inhalt .= "Nachricht:\n$message\n";
    
    // E-Mail-Header erstellen
    $header = "From: test.strom@gmx.de\r\n"; // Verwenden Sie eine gültige Absenderadresse
    $header .= "Reply-To: $email\r\n";
    $header .= "X-Mailer: PHP/" . phpversion();
    
    log_message("Versuche E-Mail zu senden an: $empfaenger_email");
    
    // E-Mail senden
    $mail_sent = mail($empfaenger_email, $betreff, $email_inhalt, $header);
    
    if ($mail_sent) {
        log_message("E-Mail erfolgreich gesendet");
        $response = [
            'success' => true,
            'message' => 'Vielen Dank für Ihre Anfrage! Wir werden uns so schnell wie möglich bei Ihnen melden.'
        ];
    } else {
        log_message("Fehler beim Senden der E-Mail: " . error_get_last()['message']);
        $response = [
            'success' => false,
            'message' => 'Beim Senden Ihrer Anfrage ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut oder kontaktieren Sie uns direkt unter ' . $empfaenger_email
        ];
    }
    
    // JSON-Antwort zurückgeben
    echo json_encode($response);
    exit;
} else {
    log_message("Keine POST-Daten erhalten. Methode: " . $_SERVER["REQUEST_METHOD"]);
    $response = [
        'success' => false,
        'message' => 'Ungültige Anfragemethode'
    ];
    echo json_encode($response);
}

// Optional: Funktion zum Speichern in einer Datenbank
/*
function saveToDatabase($name, $email, $phone, $contactMethod, $message) {
    // Hier Datenbankverbindung und -abfrage implementieren
    // Beispiel:
    // $db = new PDO('mysql:host=localhost;dbname=tarifwechsler', 'username', 'password');
    // $stmt = $db->prepare("INSERT INTO kontakte (name, email, telefon, kontaktart, nachricht, datum) 
    //                       VALUES (?, ?, ?, ?, ?, NOW())");
    // $stmt->execute([$name, $email, $phone, $contactMethod, $message]);
}
*/
?> 