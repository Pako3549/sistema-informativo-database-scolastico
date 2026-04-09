<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$docenti = getDocenti($pdo);
$materie = getMaterie($pdo);

$idMateria = null;
$idDocente = null;
$idMateriaRaw = trim($_GET['id_materia'] ?? '');
$idDocenteRaw = trim($_GET['id_docente'] ?? '');

if ($idMateriaRaw !== '') {
    if (!ctype_digit($idMateriaRaw) || (int) $idMateriaRaw <= 0) {
        renderErrorAndExit('Filtro materia non valido.', 'corsi.php');
    }
    $idMateria = (int) $idMateriaRaw;
}
if ($idDocenteRaw !== '') {
    if (!ctype_digit($idDocenteRaw) || (int) $idDocenteRaw <= 0) {
        renderErrorAndExit('Filtro docente non valido.', 'corsi.php');
    }
    $idDocente = (int) $idDocenteRaw;
}

$sql = '
    SELECT
        c.id_corso,
        c.nome_corso,
        d.id_docente,
        d.nome AS nome_docente,
        d.cognome AS cognome_docente,
        m.id_materia,
        m.nome_materia
    FROM corsi c
    JOIN docenti d ON d.id_docente = c.id_docente
    JOIN materie m ON m.id_materia = c.id_materia
    WHERE 1 = 1
';
$params = [];
if ($idMateria !== null) {
    $sql .= ' AND m.id_materia = :id_materia';
    $params[':id_materia'] = $idMateria;
}
if ($idDocente !== null) {
    $sql .= ' AND d.id_docente = :id_docente';
    $params[':id_docente'] = $idDocente;
}
$sql .= ' ORDER BY c.nome_corso';

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt->execute();
$corsi = $stmt->fetchAll();

renderPageStart('Gestione Corsi');
renderFlashMessage();
?>

<h2>Inserimento Corso</h2>
<form action="../actions/corsi-create.php" method="post">
    <p>
        <label for="nome_corso">Nome corso:</label><br>
        <input type="text" id="nome_corso" name="nome_corso" maxlength="50" required>
    </p>

    <p>
        <label for="id_materia">Materia:</label><br>
        <select id="id_materia" name="id_materia" required>
            <option value="">Seleziona materia</option>
            <?php foreach ($materie as $materia): ?>
                <option value="<?php echo h($materia['id_materia']); ?>">
                    <?php echo h($materia['nome_materia']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="id_docente">Docente (opzionale):</label><br>
        <select id="id_docente" name="id_docente">
            <option value="">Nessun docente</option>
            <?php foreach ($docenti as $docente): ?>
                <option value="<?php echo h($docente['id_docente']); ?>">
                    <?php echo h($docente['cognome'] . ' ' . $docente['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <button type="submit">Inserisci corso</button>
</form>

<h2>Filtri corsi</h2>
<form action="corsi.php" method="get">
    <p>
        <label for="f_id_materia">Materia:</label><br>
        <select id="f_id_materia" name="id_materia">
            <option value="">Tutte</option>
            <?php foreach ($materie as $materia): ?>
                <option value="<?php echo h($materia['id_materia']); ?>" <?php echo $idMateria === (int) $materia['id_materia'] ? 'selected' : ''; ?>>
                    <?php echo h($materia['nome_materia']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="f_id_docente">Docente:</label><br>
        <select id="f_id_docente" name="id_docente">
            <option value="">Tutti</option>
            <?php foreach ($docenti as $docente): ?>
                <option value="<?php echo h($docente['id_docente']); ?>" <?php echo $idDocente === (int) $docente['id_docente'] ? 'selected' : ''; ?>>
                    <?php echo h($docente['cognome'] . ' ' . $docente['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <button type="submit">Applica filtri</button>
    <a class="button-link" href="corsi.php">Reimposta filtri</a>
</form>

<h2>Elenco Corsi</h2>
<?php if (count($corsi) === 0): ?>
    <p>Non sono presenti corsi.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID corso</th>
            <th>Nome corso</th>
            <th>Materia</th>
            <th>Docente</th>
            <th>Azioni</th>
        </tr>
        <?php foreach ($corsi as $riga): ?>
            <tr>
                <td><?php echo h($riga['id_corso']); ?></td>
                <td><?php echo h($riga['nome_corso']); ?></td>
                <td><?php echo h($riga['nome_materia']); ?></td>
                <td>
                    <?php
                    if ($riga['id_docente'] === null) {
                        echo 'Non assegnato';
                    } else {
                        echo h($riga['cognome_docente'] . ' ' . $riga['nome_docente']);
                    }
                    ?>
                </td>
                <td>
                    <a class="action-link" href="corsi-edit.php?id_corso=<?php echo h($riga['id_corso']); ?>">Modifica</a>
                    <form action="../actions/corsi-delete.php" method="post" style="display:inline;">
                        <input type="hidden" name="id_corso" value="<?php echo h($riga['id_corso']); ?>">
                        <button type="submit" onclick="return confirm('Confermi l\'eliminazione del corso?');">Elimina</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php renderPageEnd(); ?>
