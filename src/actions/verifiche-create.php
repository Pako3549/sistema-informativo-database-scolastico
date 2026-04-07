<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idCorsoRaw = trim($_POST['id_corso'] ?? '');
$dataVerifica = trim($_POST['data_verifica'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');

if ($idCorsoRaw === '' || $dataVerifica === '' || $tipo === '') {
    renderErrorAndExit('Compila tutti i campi.', '../pages/verifiche.php');
}

if (!ctype_digit($idCorsoRaw) || (int) $idCorsoRaw <= 0) {
    renderErrorAndExit('Corso non valido.', '../pages/verifiche.php');
}
$idCorso = (int) $idCorsoRaw;
if (!existsCorso($pdo, $idCorso)) {
    renderErrorAndExit('Il corso selezionato non esiste.', '../pages/verifiche.php');
}

if (!isValidDateYmd($dataVerifica)) {
    renderErrorAndExit('Data verifica non valida.', '../pages/verifiche.php');
}

if (strlen($tipo) > 20) {
    renderErrorAndExit('Il tipo non può superare 20 caratteri.', '../pages/verifiche.php');
}

$sql = 'INSERT INTO verifiche (id_corso, data_verifica, tipo) VALUES (:id_corso, :data_verifica, :tipo)';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id_corso', $idCorso, PDO::PARAM_INT);
$stmt->bindValue(':data_verifica', $dataVerifica, PDO::PARAM_STR);
$stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
$stmt->execute();

redirectWithMessage('../pages/verifiche.php', 'Verifica inserita con successo.');
