<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idCorsoRaw = trim($_GET['id_corso'] ?? '');
if (!ctype_digit($idCorsoRaw) || (int) $idCorsoRaw <= 0) {
    renderErrorAndExit('ID corso non valido.', 'corsi.php');
}
$idCorso = (int) $idCorsoRaw;

$stmt = $pdo->prepare('SELECT id_corso, nome_corso, id_docente, id_materia FROM corsi WHERE id_corso = :id LIMIT 1');
$stmt->bindValue(':id', $idCorso, PDO::PARAM_INT);
$stmt->execute();
$corso = $stmt->fetch();

if (!$corso) {
    renderErrorAndExit('Corso non trovato.', 'corsi.php');
}

$docenti = getDocenti($pdo);
$materie = getMaterie($pdo);

renderPageStart('Modifica Corso');
?>

<form action="../actions/corsi-update.php" method="post">
    <input type="hidden" name="id_corso" value="<?php echo h($corso['id_corso']); ?>">

    <p>
        <label for="nome_corso">Nome corso:</label><br>
        <input type="text" id="nome_corso" name="nome_corso" maxlength="50" value="<?php echo h($corso['nome_corso']); ?>" required>
    </p>

    <p>
        <label for="id_materia">Materia:</label><br>
        <select id="id_materia" name="id_materia" required>
            <?php foreach ($materie as $materia): ?>
                <option value="<?php echo h($materia['id_materia']); ?>" <?php echo (int) $corso['id_materia'] === (int) $materia['id_materia'] ? 'selected' : ''; ?>>
                    <?php echo h($materia['nome_materia']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="id_docente">Docente (opzionale):</label><br>
        <select id="id_docente" name="id_docente">
            <option value="">-- Nessun docente --</option>
            <?php foreach ($docenti as $docente): ?>
                <option value="<?php echo h($docente['id_docente']); ?>" <?php echo $corso['id_docente'] !== null && (int) $corso['id_docente'] === (int) $docente['id_docente'] ? 'selected' : ''; ?>>
                    <?php echo h($docente['cognome'] . ' ' . $docente['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <button type="submit">Salva modifiche</button>
</form>

<?php renderPageEnd(); ?>
