<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$nomeCorso = trim($_POST['nome_corso'] ?? '');
$idMateriaRaw = trim($_POST['id_materia'] ?? '');
$idDocenteRaw = trim($_POST['id_docente'] ?? '');

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
    INSERT INTO corsi (nome_corso, id_docente, id_materia)
    VALUES (:nome_corso, :id_docente, :id_materia)
';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':nome_corso', $nomeCorso, PDO::PARAM_STR);
$stmt->bindValue(':id_docente', $idDocente, $idDocente === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
$stmt->bindValue(':id_materia', $idMateria, PDO::PARAM_INT);
$stmt->execute();

redirectWithMessage('../pages/corsi.php', 'Corso inserito con successo.');
