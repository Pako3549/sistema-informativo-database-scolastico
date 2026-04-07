<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idCorsoRaw = trim($_POST['id_corso'] ?? '');
$nomeCorso = trim($_POST['nome_corso'] ?? '');
$idMateriaRaw = trim($_POST['id_materia'] ?? '');
$idDocenteRaw = trim($_POST['id_docente'] ?? '');

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

if ($nomeCorso === '' || $idMateriaRaw === '') {
    renderErrorAndExit('Compila i campi obbligatori.', '../pages/corsi.php');
}

if (strlen($nomeCorso) > 50) {
    renderErrorAndExit('Il nome corso non può superare 50 caratteri.', '../pages/corsi.php');
}

if (!ctype_digit($idMateriaRaw) || (int) $idMateriaRaw <= 0) {
    renderErrorAndExit('Materia non valida.', '../pages/corsi.php');
}
$idMateria = (int) $idMateriaRaw;
if (!existsMateria($pdo, $idMateria)) {
    renderErrorAndExit('La materia selezionata non esiste.', '../pages/corsi.php');
}

$idDocente = null;
if ($idDocenteRaw !== '') {
    if (!ctype_digit($idDocenteRaw) || (int) $idDocenteRaw <= 0) {
        renderErrorAndExit('Docente non valido.', '../pages/corsi.php');
    }
    $idDocente = (int) $idDocenteRaw;
    if (!existsDocente($pdo, $idDocente)) {
        renderErrorAndExit('Il docente selezionato non esiste.', '../pages/corsi.php');
    }
}

$sql = '
    UPDATE corsi
    SET nome_corso = :nome_corso,
        id_docente = :id_docente,
        id_materia = :id_materia
    WHERE id_corso = :id_corso
';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':nome_corso', $nomeCorso, PDO::PARAM_STR);
$stmt->bindValue(':id_docente', $idDocente, $idDocente === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
$stmt->bindValue(':id_materia', $idMateria, PDO::PARAM_INT);
$stmt->bindValue(':id_corso', $idCorso, PDO::PARAM_INT);
$stmt->execute();

redirectWithMessage('../pages/corsi.php', 'Corso aggiornato con successo.');
