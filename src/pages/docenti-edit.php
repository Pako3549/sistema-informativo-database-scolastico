<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';

$idDocenteRaw = trim($_GET['id_docente'] ?? '');
if (!ctype_digit($idDocenteRaw) || (int) $idDocenteRaw <= 0) {
    renderErrorAndExit('ID docente non valido.', 'docenti.php');
}
$idDocente = (int) $idDocenteRaw;

$stmt = $pdo->prepare('SELECT id_docente, nome, cognome, email FROM docenti WHERE id_docente = :id LIMIT 1');
$stmt->bindValue(':id', $idDocente, PDO::PARAM_INT);
$stmt->execute();
$docente = $stmt->fetch();

if (!$docente) {
    renderErrorAndExit('Docente non trovato.', 'docenti.php');
}

renderPageStart('Modifica Docente');
?>

<form action="../actions/docenti-update.php" method="post">
    <input type="hidden" name="id_docente" value="<?php echo h($docente['id_docente']); ?>">

    <p>
        <label for="nome">Nome:</label><br>
        <input type="text" id="nome" name="nome" maxlength="30" value="<?php echo h($docente['nome']); ?>" required>
    </p>

    <p>
        <label for="cognome">Cognome:</label><br>
        <input type="text" id="cognome" name="cognome" maxlength="30" value="<?php echo h($docente['cognome']); ?>" required>
    </p>

    <p>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" maxlength="50" value="<?php echo h($docente['email']); ?>" required>
    </p>

    <button type="submit">Salva modifiche</button>
</form>

<?php renderPageEnd(); ?>
