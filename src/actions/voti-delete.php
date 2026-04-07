<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idVotoRaw = $_POST['id_voto'] ?? '';

if (!ctype_digit((string) $idVotoRaw) || (int) $idVotoRaw <= 0) {
    renderErrorAndExit('ID voto non valido.', '../pages/voti.php');
}

$idVoto = (int) $idVotoRaw;

if (!existsVoto($pdo, $idVoto)) {
    renderErrorAndExit('Il voto da eliminare non esiste.', '../pages/voti.php');
}

$stmt = $pdo->prepare('DELETE FROM voti WHERE id_voto = :id_voto');
$stmt->bindValue(':id_voto', $idVoto, PDO::PARAM_INT);
$stmt->execute();

redirectWithMessage('../pages/voti.php', 'Voto eliminato con successo.');
