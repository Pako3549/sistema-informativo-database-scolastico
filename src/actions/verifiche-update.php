<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idVerificaRaw = trim($_POST['id_verifica'] ?? '');
$idCorsoRaw = trim($_POST['id_corso'] ?? '');
$dataVerifica = trim($_POST['data_verifica'] ?? '');
$tipo = strtolower(trim($_POST['tipo'] ?? ''));

if (!ctype_digit($idVerificaRaw) || (int) $idVerificaRaw <= 0) {
    renderErrorAndExit('ID verifica non valido.', '../pages/verifiche.php');
}
$idVerifica = (int) $idVerificaRaw;

if ($idCorsoRaw === '' || $dataVerifica === '' || $tipo === '') {
    renderErrorAndExit('Compila tutti i campi.', '../pages/verifiche.php');
}

if (!ctype_digit($idCorsoRaw) || (int) $idCorsoRaw <= 0) {
    renderErrorAndExit('Corso non valido.', '../pages/verifiche.php');
}
$idCorso = (int) $idCorsoRaw;

$checkVerifica = $pdo->prepare('SELECT 1 FROM verifiche WHERE id_verifica = :id LIMIT 1');
$checkVerifica->bindValue(':id', $idVerifica, PDO::PARAM_INT);
$checkVerifica->execute();
if (!$checkVerifica->fetchColumn()) {
    renderErrorAndExit('Verifica non trovata.', '../pages/verifiche.php');
}

if (!existsCorso($pdo, $idCorso)) {
    renderErrorAndExit('Il corso selezionato non esiste.', '../pages/verifiche.php');
}

if (!isValidDateYmd($dataVerifica)) {
    renderErrorAndExit('Data verifica non valida.', '../pages/verifiche.php');
}

if (strlen($tipo) > 20) {
    renderErrorAndExit('Il tipo non può superare 20 caratteri.', '../pages/verifiche.php');
}
if (!in_array($tipo, ['orale', 'scritto'], true)) {
    renderErrorAndExit('Tipo verifica non valido. Scegli orale o scritto.', '../pages/verifiche.php');
}

$sql = '
    UPDATE verifiche
    SET id_corso = :id_corso,
        data_verifica = :data_verifica,
        tipo = :tipo
    WHERE id_verifica = :id_verifica
';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id_corso', $idCorso, PDO::PARAM_INT);
$stmt->bindValue(':data_verifica', $dataVerifica, PDO::PARAM_STR);
$stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
$stmt->bindValue(':id_verifica', $idVerifica, PDO::PARAM_INT);
$stmt->execute();

redirectWithMessage('../pages/verifiche.php', 'Verifica aggiornata con successo.');
