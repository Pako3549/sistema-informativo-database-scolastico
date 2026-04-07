<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';

$nome = trim($_POST['nome'] ?? '');
$cognome = trim($_POST['cognome'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($nome === '' || $cognome === '' || $email === '') {
    renderErrorAndExit('Compila tutti i campi.', '../pages/docenti.php');
}

if (strlen($nome) > 30 || strlen($cognome) > 30 || strlen($email) > 50) {
    renderErrorAndExit('Lunghezza campi non valida.', '../pages/docenti.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    renderErrorAndExit('Email non valida.', '../pages/docenti.php');
}

$sql = 'INSERT INTO docenti (nome, cognome, email) VALUES (:nome, :cognome, :email)';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
$stmt->bindValue(':cognome', $cognome, PDO::PARAM_STR);
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->execute();

redirectWithMessage('../pages/docenti.php', 'Docente inserito con successo.');
