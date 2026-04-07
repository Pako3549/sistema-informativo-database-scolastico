<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';

$idStudenteRaw = trim($_POST['id_studente'] ?? '');
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

try {
    $stmt = $pdo->prepare('DELETE FROM studenti WHERE id_studente = :id');
    $stmt->bindValue(':id', $idStudente, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    renderErrorAndExit('Impossibile eliminare lo studente: sono presenti record collegati.', '../pages/studenti.php');
}

redirectWithMessage('../pages/studenti.php', 'Studente eliminato con successo.');
