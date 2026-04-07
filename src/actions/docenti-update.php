<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';

$idDocenteRaw = trim($_POST['id_docente'] ?? '');
$nome = trim($_POST['nome'] ?? '');
$cognome = trim($_POST['cognome'] ?? '');
$email = trim($_POST['email'] ?? '');

if (!ctype_digit($idDocenteRaw) || (int) $idDocenteRaw <= 0) {
    renderErrorAndExit('ID docente non valido.', '../pages/docenti.php');
}
$idDocente = (int) $idDocenteRaw;

$checkStmt = $pdo->prepare('SELECT 1 FROM docenti WHERE id_docente = :id LIMIT 1');
$checkStmt->bindValue(':id', $idDocente, PDO::PARAM_INT);
$checkStmt->execute();
if (!$checkStmt->fetchColumn()) {
    renderErrorAndExit('Docente non trovato.', '../pages/docenti.php');
}

if ($nome === '' || $cognome === '' || $email === '') {
    renderErrorAndExit('Compila tutti i campi.', '../pages/docenti.php');
}

if (strlen($nome) > 30 || strlen($cognome) > 30 || strlen($email) > 50) {
    renderErrorAndExit('Lunghezza campi non valida.', '../pages/docenti.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    renderErrorAndExit('Email non valida.', '../pages/docenti.php');
}

$sql = '
    UPDATE docenti
    SET nome = :nome,
        cognome = :cognome,
        email = :email
    WHERE id_docente = :id_docente
';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
$stmt->bindValue(':cognome', $cognome, PDO::PARAM_STR);
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->bindValue(':id_docente', $idDocente, PDO::PARAM_INT);
$stmt->execute();

redirectWithMessage('../pages/docenti.php', 'Docente aggiornato con successo.');
