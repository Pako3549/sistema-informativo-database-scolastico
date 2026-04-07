<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$studenti = getStudenti($pdo);
$verifiche = getVerificheConCorso($pdo);
$corsi = getCorsi($pdo);

$idStudente = null;
$idCorso = null;
$dataDa = '';
$dataA = '';
$minNumVoti = 1;

$idStudenteRaw = trim($_GET['id_studente'] ?? '');
$idCorsoRaw = trim($_GET['id_corso'] ?? '');
$dataDaRaw = trim($_GET['data_da'] ?? '');
$dataARaw = trim($_GET['data_a'] ?? '');
$minNumVotiRaw = trim($_GET['min_num_voti'] ?? '');

if ($idStudenteRaw !== '') {
    if (!ctype_digit($idStudenteRaw) || (int) $idStudenteRaw <= 0) {
        renderErrorAndExit('Filtro studente non valido.', 'voti.php');
    }
    $idStudente = (int) $idStudenteRaw;
}

if ($idCorsoRaw !== '') {
    if (!ctype_digit($idCorsoRaw) || (int) $idCorsoRaw <= 0) {
        renderErrorAndExit('Filtro corso non valido.', 'voti.php');
    }
    $idCorso = (int) $idCorsoRaw;
}

if ($dataDaRaw !== '') {
    if (!isValidDateYmd($dataDaRaw)) {
        renderErrorAndExit('Data iniziale non valida.', 'voti.php');
    }
    $dataDa = $dataDaRaw;
}

if ($dataARaw !== '') {
    if (!isValidDateYmd($dataARaw)) {
        renderErrorAndExit('Data finale non valida.', 'voti.php');
    }
    $dataA = $dataARaw;
}

if ($dataDa !== '' && $dataA !== '' && $dataDa > $dataA) {
    renderErrorAndExit('Intervallo date non valido: la data iniziale è successiva alla data finale.', 'voti.php');
}

if ($minNumVotiRaw !== '') {
    if (!ctype_digit($minNumVotiRaw) || (int) $minNumVotiRaw <= 0) {
        renderErrorAndExit('Numero minimo voti non valido.', 'voti.php');
    }
    $minNumVoti = (int) $minNumVotiRaw;
}

$sqlVoti = '
    SELECT
        v.id_voto,
        v.voto,
        v.commento,
        s.nome AS nome_studente,
        s.cognome AS cognome_studente,
        ver.data_verifica,
        ver.tipo,
        c.nome_corso
    FROM voti v
    INNER JOIN studenti s ON s.id_studente = v.id_studente
    INNER JOIN verifiche ver ON ver.id_verifica = v.id_verifica
    INNER JOIN corsi c ON c.id_corso = ver.id_corso
    WHERE 1 = 1
';

$params = [];
if ($idStudente !== null) {
    $sqlVoti .= ' AND s.id_studente = :id_studente';
    $params[':id_studente'] = ['value' => $idStudente, 'type' => PDO::PARAM_INT];
}
if ($idCorso !== null) {
    $sqlVoti .= ' AND c.id_corso = :id_corso';
    $params[':id_corso'] = ['value' => $idCorso, 'type' => PDO::PARAM_INT];
}
if ($dataDa !== '') {
    $sqlVoti .= ' AND ver.data_verifica >= :data_da';
    $params[':data_da'] = ['value' => $dataDa, 'type' => PDO::PARAM_STR];
}
if ($dataA !== '') {
    $sqlVoti .= ' AND ver.data_verifica <= :data_a';
    $params[':data_a'] = ['value' => $dataA, 'type' => PDO::PARAM_STR];
}
$sqlVoti .= ' ORDER BY ver.data_verifica DESC, s.cognome, s.nome';

$stmtVoti = $pdo->prepare($sqlVoti);
foreach ($params as $key => $param) {
    $stmtVoti->bindValue($key, $param['value'], $param['type']);
}
$stmtVoti->execute();
$voti = $stmtVoti->fetchAll();

$sqlStatsStudenti = '
    SELECT
        s.id_studente,
        s.nome,
        s.cognome,
        ROUND(AVG(v.voto), 2) AS media_voti,
        COUNT(v.id_voto) AS numero_voti
    FROM voti v
    INNER JOIN studenti s ON s.id_studente = v.id_studente
    INNER JOIN verifiche ver ON ver.id_verifica = v.id_verifica
    INNER JOIN corsi c ON c.id_corso = ver.id_corso
    WHERE 1 = 1
';

$statsParams = [];
if ($idStudente !== null) {
    $sqlStatsStudenti .= ' AND s.id_studente = :id_studente';
    $statsParams[':id_studente'] = $idStudente;
}
if ($idCorso !== null) {
    $sqlStatsStudenti .= ' AND c.id_corso = :id_corso';
    $statsParams[':id_corso'] = $idCorso;
}
if ($dataDa !== '') {
    $sqlStatsStudenti .= ' AND ver.data_verifica >= :data_da';
    $statsParams[':data_da'] = $dataDa;
}
if ($dataA !== '') {
    $sqlStatsStudenti .= ' AND ver.data_verifica <= :data_a';
    $statsParams[':data_a'] = $dataA;
}
$sqlStatsStudenti .= '
    GROUP BY s.id_studente, s.nome, s.cognome
    HAVING COUNT(v.id_voto) >= :min_num_voti
    ORDER BY media_voti DESC, s.cognome, s.nome
';

$stmtStatsStudenti = $pdo->prepare($sqlStatsStudenti);
foreach ($statsParams as $key => $value) {
    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmtStatsStudenti->bindValue($key, $value, $type);
}
$stmtStatsStudenti->bindValue(':min_num_voti', $minNumVoti, PDO::PARAM_INT);
$stmtStatsStudenti->execute();
$medieStudenti = $stmtStatsStudenti->fetchAll();

$sqlStatsCorsi = '
    SELECT
        c.id_corso,
        c.nome_corso,
        ROUND(AVG(v.voto), 2) AS media_corso,
        COUNT(v.id_voto) AS numero_voti
    FROM voti v
    INNER JOIN verifiche ver ON ver.id_verifica = v.id_verifica
    INNER JOIN corsi c ON c.id_corso = ver.id_corso
    INNER JOIN studenti s ON s.id_studente = v.id_studente
    WHERE 1 = 1
';

$statsCorsiParams = [];
if ($idStudente !== null) {
    $sqlStatsCorsi .= ' AND s.id_studente = :id_studente';
    $statsCorsiParams[':id_studente'] = $idStudente;
}
if ($idCorso !== null) {
    $sqlStatsCorsi .= ' AND c.id_corso = :id_corso';
    $statsCorsiParams[':id_corso'] = $idCorso;
}
if ($dataDa !== '') {
    $sqlStatsCorsi .= ' AND ver.data_verifica >= :data_da';
    $statsCorsiParams[':data_da'] = $dataDa;
}
if ($dataA !== '') {
    $sqlStatsCorsi .= ' AND ver.data_verifica <= :data_a';
    $statsCorsiParams[':data_a'] = $dataA;
}
$sqlStatsCorsi .= '
    GROUP BY c.id_corso, c.nome_corso
    HAVING COUNT(v.id_voto) >= :min_num_voti
    ORDER BY media_corso DESC, c.nome_corso
';

$stmtStatsCorsi = $pdo->prepare($sqlStatsCorsi);
foreach ($statsCorsiParams as $key => $value) {
    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmtStatsCorsi->bindValue($key, $value, $type);
}
$stmtStatsCorsi->bindValue(':min_num_voti', $minNumVoti, PDO::PARAM_INT);
$stmtStatsCorsi->execute();
$medieCorsi = $stmtStatsCorsi->fetchAll();

renderPageStart('Gestione Voti');
renderFlashMessage();
?>

<h2>Inserimento Nuovo Voto</h2>
<?php if (count($studenti) === 0 || count($verifiche) === 0): ?>
    <p>Per inserire un voto servono almeno uno studente e una verifica.</p>
<?php else: ?>
<form action="../actions/voti-create.php" method="post">
    <p>
        <label for="id_studente">Studente:</label><br>
        <select id="id_studente" name="id_studente" required>
            <option value="">-- Seleziona studente --</option>
            <?php foreach ($studenti as $studente): ?>
                <option value="<?php echo h($studente['id_studente']); ?>">
                    <?php echo h($studente['cognome'] . ' ' . $studente['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="id_verifica">Verifica:</label><br>
        <select id="id_verifica" name="id_verifica" required>
            <option value="">-- Seleziona verifica --</option>
            <?php foreach ($verifiche as $verifica): ?>
                <option value="<?php echo h($verifica['id_verifica']); ?>">
                    <?php echo h($verifica['nome_corso'] . ' - ' . $verifica['tipo'] . ' - ' . $verifica['data_verifica']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="voto">Voto (1-10):</label><br>
        <input type="number" id="voto" name="voto" min="1" max="10" required>
    </p>

    <p>
        <label for="commento">Commento (opzionale):</label><br>
        <input type="text" id="commento" name="commento" maxlength="100">
    </p>

    <button type="submit">Inserisci voto</button>
</form>
<?php endif; ?>

<h2>Filtri</h2>
<form action="voti.php" method="get">
    <p>
        <label for="f_id_studente">Studente:</label><br>
        <select id="f_id_studente" name="id_studente">
            <option value="">-- Tutti --</option>
            <?php foreach ($studenti as $studente): ?>
                <option value="<?php echo h($studente['id_studente']); ?>" <?php echo $idStudente === (int) $studente['id_studente'] ? 'selected' : ''; ?>>
                    <?php echo h($studente['cognome'] . ' ' . $studente['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="f_id_corso">Corso:</label><br>
        <select id="f_id_corso" name="id_corso">
            <option value="">-- Tutti --</option>
            <?php foreach ($corsi as $corso): ?>
                <option value="<?php echo h($corso['id_corso']); ?>" <?php echo $idCorso === (int) $corso['id_corso'] ? 'selected' : ''; ?>>
                    <?php echo h($corso['nome_corso']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="data_da">Data verifica da:</label><br>
        <input type="date" id="data_da" name="data_da" value="<?php echo h($dataDa); ?>">
    </p>

    <p>
        <label for="data_a">Data verifica a:</label><br>
        <input type="date" id="data_a" name="data_a" value="<?php echo h($dataA); ?>">
    </p>

    <p>
        <label for="min_num_voti">Numero minimo voti (statistiche):</label><br>
        <input type="number" id="min_num_voti" name="min_num_voti" min="1" value="<?php echo h($minNumVoti); ?>">
    </p>

    <button type="submit">Applica filtri</button>
    <a class="button-link" href="voti.php">Reimposta filtri</a>
</form>

<h2>Elenco Voti</h2>
<?php if (count($voti) === 0): ?>
    <p>Nessun voto trovato con i filtri selezionati.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID voto</th>
            <th>Studente</th>
            <th>Corso</th>
            <th>Verifica</th>
            <th>Voto</th>
            <th>Commento</th>
            <th>Azioni</th>
        </tr>
        <?php foreach ($voti as $riga): ?>
            <tr>
                <td><?php echo h($riga['id_voto']); ?></td>
                <td><?php echo h($riga['cognome_studente'] . ' ' . $riga['nome_studente']); ?></td>
                <td><?php echo h($riga['nome_corso']); ?></td>
                <td><?php echo h($riga['tipo'] . ' del ' . $riga['data_verifica']); ?></td>
                <td><?php echo h($riga['voto']); ?></td>
                <td><?php echo h($riga['commento'] ?? ''); ?></td>
                <td>
                    <a class="action-link" href="voti-edit.php?id_voto=<?php echo h($riga['id_voto']); ?>">Modifica</a>
                    <form action="../actions/voti-delete.php" method="post" style="display:inline;">
                        <input type="hidden" name="id_voto" value="<?php echo h($riga['id_voto']); ?>">
                        <button type="submit" onclick="return confirm('Confermi l\'eliminazione del voto?');">Elimina</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<h2>Statistiche Voti</h2>

<h3>Media voti per studente</h3>
<?php if (count($medieStudenti) === 0): ?>
    <p>Nessun dato disponibile con i filtri selezionati.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Studente</th>
            <th>Numero voti</th>
            <th>Media</th>
        </tr>
        <?php foreach ($medieStudenti as $riga): ?>
            <tr>
                <td><?php echo h($riga['cognome'] . ' ' . $riga['nome']); ?></td>
                <td><?php echo h($riga['numero_voti']); ?></td>
                <td><?php echo h($riga['media_voti']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<h3>Media voti per corso</h3>
<?php if (count($medieCorsi) === 0): ?>
    <p>Nessun corso con voti registrati.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Corso</th>
            <th>Numero voti</th>
            <th>Media</th>
        </tr>
        <?php foreach ($medieCorsi as $riga): ?>
            <tr>
                <td><?php echo h($riga['nome_corso']); ?></td>
                <td><?php echo h($riga['numero_voti']); ?></td>
                <td><?php echo h($riga['media_corso']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php renderPageEnd(); ?>
