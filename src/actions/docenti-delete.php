<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';

$idDocenteRaw = trim($_POST['id_docente'] ?? '');
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

try {
    $stmt = $pdo->prepare('DELETE FROM docenti WHERE id_docente = :id');
    $stmt->bindValue(':id', $idDocente, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    renderErrorAndExit('Impossibile eliminare il docente: sono presenti record collegati.', '../pages/docenti.php');
}

redirectWithMessage('../pages/docenti.php', 'Docente eliminato con successo.');
