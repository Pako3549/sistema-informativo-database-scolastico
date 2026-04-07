<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idStudenteRaw = trim($_POST['id_studente'] ?? '');
$nome = trim($_POST['nome'] ?? '');
$cognome = trim($_POST['cognome'] ?? '');
$dataNascita = trim($_POST['data_nascita'] ?? '');
$idClasseRaw = trim($_POST['id_classe'] ?? '');

if (!ctype_digit($idStudenteRaw) || (int) $idStudenteRaw <= 0) {
    renderErrorAndExit('ID studente non valido.', '../pages/studenti.php');
}
$idStudente = (int) $idStudenteRaw;

$checkStmt = $pdo->prepare('SELECT 1 FROM studenti WHERE id_studente = :id LIMIT 1');
$checkStmt->bindValue(':id', $idStudente, PDO::PARAM_INT);
$checkStmt->execute();
if (!$checkStmt->fetchColumn()) {
    renderErrorAndExit('Studente non trovato.', '../pages/studenti.php');
}

if ($nome === '' || $cognome === '' || $dataNascita === '') {
    renderErrorAndExit('Compila tutti i campi obbligatori.', '../pages/studenti.php');
}

if (strlen($nome) > 30 || strlen($cognome) > 30) {
    renderErrorAndExit('Nome e cognome non possono superare 30 caratteri.', '../pages/studenti.php');
}

if (!isValidDateYmd($dataNascita)) {
    renderErrorAndExit('Data di nascita non valida.', '../pages/studenti.php');
}

$idClasse = null;
if ($idClasseRaw !== '') {
    if (!ctype_digit($idClasseRaw) || (int) $idClasseRaw <= 0) {
        renderErrorAndExit('Classe non valida.', '../pages/studenti.php');
    }
    $idClasse = (int) $idClasseRaw;
    if (!existsClasse($pdo, $idClasse)) {
        renderErrorAndExit('La classe selezionata non esiste.', '../pages/studenti.php');
    }
}

$sql = '
    UPDATE studenti
    SET nome = :nome,
        cognome = :cognome,
        data_nascita = :data_nascita,
        id_classe = :id_classe
    WHERE id_studente = :id_studente
';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
$stmt->bindValue(':cognome', $cognome, PDO::PARAM_STR);
$stmt->bindValue(':data_nascita', $dataNascita, PDO::PARAM_STR);
$stmt->bindValue(':id_classe', $idClasse, $idClasse === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
$stmt->bindValue(':id_studente', $idStudente, PDO::PARAM_INT);
$stmt->execute();

redirectWithMessage('../pages/studenti.php', 'Studente aggiornato con successo.');
