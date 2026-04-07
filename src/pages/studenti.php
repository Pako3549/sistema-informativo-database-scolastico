<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$classi = getClassi($pdo);

$idClasse = null;
$cognomeLike = trim($_GET['cognome'] ?? '');
$idClasseRaw = trim($_GET['id_classe'] ?? '');

if ($idClasseRaw !== '') {
    if (!ctype_digit($idClasseRaw) || (int) $idClasseRaw <= 0) {
        renderErrorAndExit('Filtro classe non valido.', 'studenti.php');
    }
    $idClasse = (int) $idClasseRaw;
}

$sql = '
    SELECT
        s.id_studente,
        s.nome,
        s.cognome,
        s.data_nascita,
        c.id_classe,
        c.anno,
        c.sezione
    FROM studenti s
    LEFT JOIN classi c ON c.id_classe = s.id_classe
    WHERE 1 = 1
';
$params = [];
if ($idClasse !== null) {
    $sql .= ' AND c.id_classe = :id_classe';
    $params[':id_classe'] = ['value' => $idClasse, 'type' => PDO::PARAM_INT];
}
if ($cognomeLike !== '') {
    $sql .= ' AND s.cognome LIKE :cognome';
    $params[':cognome'] = ['value' => '%' . $cognomeLike . '%', 'type' => PDO::PARAM_STR];
}
$sql .= ' ORDER BY s.cognome, s.nome';

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $param) {
    $stmt->bindValue($key, $param['value'], $param['type']);
}
$stmt->execute();
$studenti = $stmt->fetchAll();

renderPageStart('Gestione Studenti');
renderFlashMessage();
?>

<h2>Inserimento Studente</h2>
<form action="../actions/studenti-create.php" method="post">
    <p>
        <label for="nome">Nome:</label><br>
        <input type="text" id="nome" name="nome" maxlength="30" required>
    </p>

    <p>
        <label for="cognome">Cognome:</label><br>
        <input type="text" id="cognome" name="cognome" maxlength="30" required>
    </p>

    <p>
        <label for="data_nascita">Data di nascita:</label><br>
        <input type="date" id="data_nascita" name="data_nascita" required>
    </p>

    <p>
        <label for="id_classe">Classe (opzionale):</label><br>
        <select id="id_classe" name="id_classe">
            <option value="">-- Nessuna classe --</option>
            <?php foreach ($classi as $classe): ?>
                <option value="<?php echo h($classe['id_classe']); ?>">
                    <?php echo h($classe['anno'] . $classe['sezione']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <button type="submit">Inserisci studente</button>
</form>

<h2>Filtri studenti</h2>
<form action="studenti.php" method="get">
    <p>
        <label for="f_cognome">Cognome contiene:</label><br>
        <input type="text" id="f_cognome" name="cognome" value="<?php echo h($cognomeLike); ?>">
    </p>

    <p>
        <label for="f_id_classe">Classe:</label><br>
        <select id="f_id_classe" name="id_classe">
            <option value="">-- Tutte --</option>
            <?php foreach ($classi as $classe): ?>
                <option value="<?php echo h($classe['id_classe']); ?>" <?php echo $idClasse === (int) $classe['id_classe'] ? 'selected' : ''; ?>>
                    <?php echo h($classe['anno'] . $classe['sezione']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <button type="submit">Applica filtri</button>
    <a class="button-link" href="studenti.php">Reimposta filtri</a>
</form>

<h2>Elenco Studenti</h2>
<?php if (count($studenti) === 0): ?>
    <p>Non sono presenti studenti.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID studente</th>
            <th>Nome</th>
            <th>Cognome</th>
            <th>Data di nascita</th>
            <th>Classe</th>
            <th>Azioni</th>
        </tr>
        <?php foreach ($studenti as $riga): ?>
            <tr>
                <td><?php echo h($riga['id_studente']); ?></td>
                <td><?php echo h($riga['nome']); ?></td>
                <td><?php echo h($riga['cognome']); ?></td>
                <td><?php echo h($riga['data_nascita']); ?></td>
                <td>
                    <?php
                    if ($riga['id_classe'] === null) {
                        echo 'Non assegnata';
                    } else {
                        echo h($riga['anno'] . $riga['sezione']);
                    }
                    ?>
                </td>
                <td>
                    <a class="action-link" href="studenti-edit.php?id_studente=<?php echo h($riga['id_studente']); ?>">Modifica</a>
                    <form action="../actions/studenti-delete.php" method="post" style="display:inline;">
                        <input type="hidden" name="id_studente" value="<?php echo h($riga['id_studente']); ?>">
                        <button type="submit" onclick="return confirm('Confermi l\'eliminazione dello studente?');">Elimina</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php renderPageEnd(); ?>
