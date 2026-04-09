<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$corsi = getCorsi($pdo);

$idCorso = null;
$dataDa = '';
$dataA = '';
$idCorsoRaw = trim($_GET['id_corso'] ?? '');
$dataDaRaw = trim($_GET['data_da'] ?? '');
$dataARaw = trim($_GET['data_a'] ?? '');

if ($idCorsoRaw !== '') {
    if (!ctype_digit($idCorsoRaw) || (int) $idCorsoRaw <= 0) {
        renderErrorAndExit('Filtro corso non valido.', 'verifiche.php');
    }
    $idCorso = (int) $idCorsoRaw;
}
if ($dataDaRaw !== '') {
    if (!isValidDateYmd($dataDaRaw)) {
        renderErrorAndExit('Data iniziale non valida.', 'verifiche.php');
    }
    $dataDa = $dataDaRaw;
}
if ($dataARaw !== '') {
    if (!isValidDateYmd($dataARaw)) {
        renderErrorAndExit('Data finale non valida.', 'verifiche.php');
    }
    $dataA = $dataARaw;
}
if ($dataDa !== '' && $dataA !== '' && $dataDa > $dataA) {
    renderErrorAndExit('Intervallo date non valido.', 'verifiche.php');
}

$sql = '
    SELECT
        v.id_verifica,
        v.data_verifica,
        v.tipo,
        c.id_corso,
        c.nome_corso
    FROM verifiche v
    JOIN corsi c ON c.id_corso = v.id_corso
    WHERE 1 = 1
';
$params = [];
if ($idCorso !== null) {
    $sql .= ' AND c.id_corso = :id_corso';
    $params[':id_corso'] = ['value' => $idCorso, 'type' => PDO::PARAM_INT];
}
if ($dataDa !== '') {
    $sql .= ' AND v.data_verifica >= :data_da';
    $params[':data_da'] = ['value' => $dataDa, 'type' => PDO::PARAM_STR];
}
if ($dataA !== '') {
    $sql .= ' AND v.data_verifica <= :data_a';
    $params[':data_a'] = ['value' => $dataA, 'type' => PDO::PARAM_STR];
}
$sql .= ' ORDER BY v.data_verifica DESC, c.nome_corso';

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $param) {
    $stmt->bindValue($key, $param['value'], $param['type']);
}
$stmt->execute();
$verifiche = $stmt->fetchAll();

renderPageStart('Gestione Verifiche');
renderFlashMessage();
?>

<h2>Inserimento Verifica</h2>
<?php if (count($corsi) === 0): ?>
    <p>Per inserire una verifica serve almeno un corso.</p>
    <p><a href="corsi.php">Inserisci un corso</a></p>
<?php else: ?>
    <form action="../actions/verifiche-create.php" method="post">
        <p>
            <label for="id_corso">Corso:</label><br>
            <select id="id_corso" name="id_corso" required>
                <option value="">Seleziona corso</option>
                <?php foreach ($corsi as $corso): ?>
                    <option value="<?php echo h($corso['id_corso']); ?>">
                        <?php echo h($corso['nome_corso']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="data_verifica">Data verifica:</label><br>
            <input type="date" id="data_verifica" name="data_verifica" required>
        </p>

        <p>
            <label for="tipo">Tipo:</label><br>
            <select id="tipo" name="tipo" required>
                <option value="">Seleziona tipo</option>
                <option value="orale">Orale</option>
                <option value="scritto">Scritto</option>
            </select>
        </p>

        <button type="submit">Inserisci verifica</button>
    </form>
<?php endif; ?>

<h2>Filtri verifiche</h2>
<form action="verifiche.php" method="get">
    <p>
        <label for="f_id_corso">Corso:</label><br>
        <select id="f_id_corso" name="id_corso">
            <option value="">Tutti</option>
            <?php foreach ($corsi as $corso): ?>
                <option value="<?php echo h($corso['id_corso']); ?>" <?php echo $idCorso === (int) $corso['id_corso'] ? 'selected' : ''; ?>>
                    <?php echo h($corso['nome_corso']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="data_da">Data da:</label><br>
        <input type="date" id="data_da" name="data_da" value="<?php echo h($dataDa); ?>">
    </p>

    <p>
        <label for="data_a">Data a:</label><br>
        <input type="date" id="data_a" name="data_a" value="<?php echo h($dataA); ?>">
    </p>

    <button type="submit">Applica filtri</button>
    <a class="button-link" href="verifiche.php">Reimposta filtri</a>
</form>

<h2>Elenco Verifiche</h2>
<?php if (count($verifiche) === 0): ?>
    <p>Non sono presenti verifiche.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID verifica</th>
            <th>Corso</th>
            <th>Data verifica</th>
            <th>Tipo</th>
            <th>Azioni</th>
        </tr>
        <?php foreach ($verifiche as $riga): ?>
            <tr>
                <td><?php echo h($riga['id_verifica']); ?></td>
                <td><?php echo h($riga['nome_corso']); ?></td>
                <td><?php echo h($riga['data_verifica']); ?></td>
                <td><?php echo h($riga['tipo']); ?></td>
                <td>
                    <a class="action-link" href="verifiche-edit.php?id_verifica=<?php echo h($riga['id_verifica']); ?>">Modifica</a>
                    <form action="../actions/verifiche-delete.php" method="post" style="display:inline;">
                        <input type="hidden" name="id_verifica" value="<?php echo h($riga['id_verifica']); ?>">
                        <button type="submit" onclick="return confirm('Confermi l\'eliminazione della verifica?');">Elimina</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php renderPageEnd(); ?>
