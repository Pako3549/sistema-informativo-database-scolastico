<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';

$idVerificaRaw = trim($_POST['id_verifica'] ?? '');
if (!ctype_digit($idVerificaRaw) || (int) $idVerificaRaw <= 0) {
    renderErrorAndExit('ID verifica non valido.', '../pages/verifiche.php');
}
$idVerifica = (int) $idVerificaRaw;

$checkStmt = $pdo->prepare('SELECT 1 FROM verifiche WHERE id_verifica = :id LIMIT 1');
$checkStmt->bindValue(':id', $idVerifica, PDO::PARAM_INT);
$checkStmt->execute();
if (!$checkStmt->fetchColumn()) {
    renderErrorAndExit('Verifica non trovata.', '../pages/verifiche.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM verifiche WHERE id_verifica = :id');
    $stmt->bindValue(':id', $idVerifica, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    renderErrorAndExit('Impossibile eliminare la verifica: sono presenti record collegati.', '../pages/verifiche.php');
}

redirectWithMessage('../pages/verifiche.php', 'Verifica eliminata con successo.');
