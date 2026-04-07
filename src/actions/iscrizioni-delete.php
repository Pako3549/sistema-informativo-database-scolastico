<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';

$idIscrizioneRaw = trim($_POST['id_iscrizione'] ?? '');

if (!ctype_digit($idIscrizioneRaw) || (int) $idIscrizioneRaw <= 0) {
    renderErrorAndExit('ID iscrizione non valido.', '../pages/iscrizioni.php');
}

$idIscrizione = (int) $idIscrizioneRaw;

$checkStmt = $pdo->prepare('SELECT 1 FROM iscrizioni WHERE id_iscrizione = :id LIMIT 1');
$checkStmt->bindValue(':id', $idIscrizione, PDO::PARAM_INT);
$checkStmt->execute();
if (!$checkStmt->fetchColumn()) {
    renderErrorAndExit('Iscrizione non trovata.', '../pages/iscrizioni.php');
}

$stmt = $pdo->prepare('DELETE FROM iscrizioni WHERE id_iscrizione = :id');
$stmt->bindValue(':id', $idIscrizione, PDO::PARAM_INT);
$stmt->execute();

redirectWithMessage('../pages/iscrizioni.php', 'Iscrizione eliminata con successo.');
