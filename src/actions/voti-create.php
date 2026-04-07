<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idStudenteRaw = $_POST['id_studente'] ?? '';
$idVerificaRaw = $_POST['id_verifica'] ?? '';
$votoRaw = $_POST['voto'] ?? '';
$commentoRaw = trim($_POST['commento'] ?? '');

if (!ctype_digit((string) $idStudenteRaw) || !ctype_digit((string) $idVerificaRaw) || !ctype_digit((string) $votoRaw)) {
    renderErrorAndExit('Dati non validi: studente, verifica e voto devono essere numerici.', '../pages/voti.php');
}

$idStudente = (int) $idStudenteRaw;
$idVerifica = (int) $idVerificaRaw;
$voto = (int) $votoRaw;

if ($idStudente <= 0 || $idVerifica <= 0) {
    renderErrorAndExit('Studente o verifica non validi.', '../pages/voti.php');
}

if ($voto < 1 || $voto > 10) {
    renderErrorAndExit('Il voto deve essere compreso tra 1 e 10.', '../pages/voti.php');
}

if (!existsStudente($pdo, $idStudente)) {
    renderErrorAndExit('Lo studente selezionato non esiste.', '../pages/voti.php');
}

if (!existsVerifica($pdo, $idVerifica)) {
    renderErrorAndExit('La verifica selezionata non esiste.', '../pages/voti.php');
}

$commento = $commentoRaw === '' ? null : $commentoRaw;
if ($commento !== null && strlen($commento) > 100) {
    renderErrorAndExit('Il commento non può superare i 100 caratteri.', '../pages/voti.php');
}

$sql = 'INSERT INTO voti (id_verifica, id_studente, voto, commento) VALUES (:id_verifica, :id_studente, :voto, :commento)';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id_verifica', $idVerifica, PDO::PARAM_INT);
$stmt->bindValue(':id_studente', $idStudente, PDO::PARAM_INT);
$stmt->bindValue(':voto', $voto, PDO::PARAM_INT);
$stmt->bindValue(':commento', $commento, $commento === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
$stmt->execute();

redirectWithMessage('../pages/voti.php', 'Voto inserito con successo.');
