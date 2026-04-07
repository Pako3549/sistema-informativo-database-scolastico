<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$nome = trim($_POST['nome'] ?? '');
$cognome = trim($_POST['cognome'] ?? '');
$dataNascita = trim($_POST['data_nascita'] ?? '');
$idClasseRaw = trim($_POST['id_classe'] ?? '');

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
    INSERT INTO studenti (nome, cognome, data_nascita, id_classe)
    VALUES (:nome, :cognome, :data_nascita, :id_classe)
';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
$stmt->bindValue(':cognome', $cognome, PDO::PARAM_STR);
$stmt->bindValue(':data_nascita', $dataNascita, PDO::PARAM_STR);
$stmt->bindValue(':id_classe', $idClasse, $idClasse === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
$stmt->execute();

redirectWithMessage('../pages/studenti.php', 'Studente inserito con successo.');
