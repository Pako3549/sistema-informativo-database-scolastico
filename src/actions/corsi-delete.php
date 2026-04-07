<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';

$idCorsoRaw = trim($_POST['id_corso'] ?? '');
if (!ctype_digit($idCorsoRaw) || (int) $idCorsoRaw <= 0) {
    renderErrorAndExit('ID corso non valido.', '../pages/corsi.php');
}
$idCorso = (int) $idCorsoRaw;

$checkStmt = $pdo->prepare('SELECT 1 FROM corsi WHERE id_corso = :id LIMIT 1');
$checkStmt->bindValue(':id', $idCorso, PDO::PARAM_INT);
$checkStmt->execute();
if (!$checkStmt->fetchColumn()) {
    renderErrorAndExit('Corso non trovato.', '../pages/corsi.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM corsi WHERE id_corso = :id');
    $stmt->bindValue(':id', $idCorso, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    renderErrorAndExit('Impossibile eliminare il corso: sono presenti record collegati.', '../pages/corsi.php');
}

redirectWithMessage('../pages/corsi.php', 'Corso eliminato con successo.');
