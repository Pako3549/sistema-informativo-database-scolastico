<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$studenti = getStudenti($pdo);
$corsi = getCorsi($pdo);
$classi = getClassi($pdo);

$idCorso = null;
$idClasse = null;

$idCorsoRaw = trim($_GET['id_corso'] ?? '');
$idClasseRaw = trim($_GET['id_classe'] ?? '');

if ($idCorsoRaw !== '') {
    if (!ctype_digit($idCorsoRaw) || (int) $idCorsoRaw <= 0) {
        renderErrorAndExit('Filtro corso non valido.', 'iscrizioni.php');
    }
    $idCorso = (int) $idCorsoRaw;
}

if ($idClasseRaw !== '') {
    if (!ctype_digit($idClasseRaw) || (int) $idClasseRaw <= 0) {
        renderErrorAndExit('Filtro classe non valido.', 'iscrizioni.php');
    }
    $idClasse = (int) $idClasseRaw;
}

$sql = '
    SELECT
        i.id_iscrizione,
        i.data_iscrizione,
        cl.id_classe,
        cl.anno,
        cl.sezione,
        s.nome AS nome_studente,
        s.cognome AS cognome_studente,
        c.nome_corso
    FROM iscrizioni i
    JOIN studenti s ON s.id_studente = i.id_studente
    JOIN classi cl ON cl.id_classe = s.id_classe
    JOIN corsi c ON c.id_corso = i.id_corso
    WHERE 1 = 1
';

$params = [];
if ($idCorso !== null) {
    $sql .= ' AND c.id_corso = :id_corso';
    $params[':id_corso'] = $idCorso;
}
if ($idClasse !== null) {
    $sql .= ' AND cl.id_classe = :id_classe';
    $params[':id_classe'] = $idClasse;
}
$sql .= ' ORDER BY i.data_iscrizione DESC, s.cognome, s.nome';

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt->execute();
$iscrizioni = $stmt->fetchAll();

renderPageStart('Gestione Iscrizioni');
renderFlashMessage();
?>

<?php if (count($studenti) === 0 || count($corsi) === 0): ?>
    <p>Per creare un'iscrizione servono almeno uno studente e un corso.</p>
    <p><a href="iscrizioni.php">Vai all'elenco iscrizioni</a></p>
<?php else: ?>
    <form action="../actions/iscrizioni-create.php" method="post">
        <p>
            <label for="id_studente">Studente:</label><br>
            <select id="id_studente" name="id_studente" required>
                <option value="">Seleziona studente</option>
                <?php foreach ($studenti as $studente): ?>
                    <option value="<?php echo h($studente['id_studente']); ?>">
                        <?php echo h($studente['cognome'] . ' ' . $studente['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

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
            <label for="data_iscrizione">Data iscrizione:</label><br>
            <input type="date" id="data_iscrizione" name="data_iscrizione" value="<?php echo h(date('Y-m-d')); ?>" required>
        </p>

        <button type="submit">Crea iscrizione</button>
    </form>
<?php endif; ?>

<h2>Filtri iscrizioni</h2>
<form action="iscrizioni.php" method="get">
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
        <label for="f_id_classe">Classe:</label><br>
        <select id="f_id_classe" name="id_classe">
            <option value="">Tutte</option>
            <?php foreach ($classi as $classe): ?>
                <option value="<?php echo h($classe['id_classe']); ?>" <?php echo $idClasse === (int) $classe['id_classe'] ? 'selected' : ''; ?>>
                    <?php echo h($classe['anno'] . $classe['sezione']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <button type="submit">Applica filtri</button>
    <a class="button-link" href="iscrizioni.php">Reimposta filtri</a>
</form>

<h2>Elenco Iscrizioni</h2>
<?php if (count($iscrizioni) === 0): ?>
    <p>Non sono presenti iscrizioni.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID iscrizione</th>
            <th>Studente</th>
            <th>Classe</th>
            <th>Corso</th>
            <th>Data iscrizione</th>
            <th>Azioni</th>
        </tr>
        <?php foreach ($iscrizioni as $riga): ?>
            <tr>
                <td><?php echo h($riga['id_iscrizione']); ?></td>
                <td><?php echo h($riga['cognome_studente'] . ' ' . $riga['nome_studente']); ?></td>
                <td>
                    <?php
                    if ($riga['id_classe'] === null) {
                        echo 'Non assegnata';
                    } else {
                        echo h($riga['anno'] . $riga['sezione']);
                    }
                    ?>
                </td>
                <td><?php echo h($riga['nome_corso']); ?></td>
                <td><?php echo h($riga['data_iscrizione']); ?></td>
                <td>
                    <form action="../actions/iscrizioni-delete.php" method="post" style="display:inline;">
                        <input type="hidden" name="id_iscrizione" value="<?php echo h($riga['id_iscrizione']); ?>">
                        <button type="submit" onclick="return confirm('Confermi l\'eliminazione dell\'iscrizione?');">Elimina</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php renderPageEnd(); ?>
