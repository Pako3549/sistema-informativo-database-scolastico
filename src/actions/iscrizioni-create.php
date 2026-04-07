<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idStudenteRaw = trim($_POST['id_studente'] ?? '');
$idCorsoRaw = trim($_POST['id_corso'] ?? '');
$dataIscrizione = trim($_POST['data_iscrizione'] ?? '');

if (!ctype_digit($idStudenteRaw) || !ctype_digit($idCorsoRaw)) {
    renderErrorAndExit('ID studente o corso non valido.', '../pages/iscrizioni.php');
}

$idStudente = (int) $idStudenteRaw;
$idCorso = (int) $idCorsoRaw;

if ($idStudente <= 0 || $idCorso <= 0) {
    renderErrorAndExit('ID studente o corso non valido.', '../pages/iscrizioni.php');
}

if (!isValidDateYmd($dataIscrizione)) {
    renderErrorAndExit('Data iscrizione non valida.', '../pages/iscrizioni.php');
}

if (!existsStudente($pdo, $idStudente)) {
    renderErrorAndExit('Studente non esistente.', '../pages/iscrizioni.php');
}

if (!existsCorso($pdo, $idCorso)) {
    renderErrorAndExit('Corso non esistente.', '../pages/iscrizioni.php');
}

$checkSql = 'SELECT 1 FROM iscrizioni WHERE id_studente = :id_studente AND id_corso = :id_corso LIMIT 1';
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->bindValue(':id_studente', $idStudente, PDO::PARAM_INT);
$checkStmt->bindValue(':id_corso', $idCorso, PDO::PARAM_INT);
$checkStmt->execute();
if ($checkStmt->fetchColumn()) {
    renderErrorAndExit('Relazione già presente: studente già iscritto a questo corso.', '../pages/iscrizioni.php');
}

$sql = '
    INSERT INTO iscrizioni (id_studente, id_corso, data_iscrizione)
    VALUES (:id_studente, :id_corso, :data_iscrizione)
';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id_studente', $idStudente, PDO::PARAM_INT);
$stmt->bindValue(':id_corso', $idCorso, PDO::PARAM_INT);
$stmt->bindValue(':data_iscrizione', $dataIscrizione, PDO::PARAM_STR);
$stmt->execute();

redirectWithMessage('../pages/iscrizioni.php', 'Iscrizione creata con successo.');
