<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idStudenteRaw = trim($_GET['id_studente'] ?? '');
if (!ctype_digit($idStudenteRaw) || (int) $idStudenteRaw <= 0) {
    renderErrorAndExit('ID studente non valido.', 'studenti.php');
}
$idStudente = (int) $idStudenteRaw;

$stmt = $pdo->prepare('SELECT id_studente, nome, cognome, data_nascita, id_classe FROM studenti WHERE id_studente = :id LIMIT 1');
$stmt->bindValue(':id', $idStudente, PDO::PARAM_INT);
$stmt->execute();
$studente = $stmt->fetch();

if (!$studente) {
    renderErrorAndExit('Studente non trovato.', 'studenti.php');
}

$classi = getClassi($pdo);

renderPageStart('Modifica Studente');
?>

<form action="../actions/studenti-update.php" method="post">
    <input type="hidden" name="id_studente" value="<?php echo h($studente['id_studente']); ?>">

    <p>
        <label for="nome">Nome:</label><br>
        <input type="text" id="nome" name="nome" maxlength="30" value="<?php echo h($studente['nome']); ?>" required>
    </p>

    <p>
        <label for="cognome">Cognome:</label><br>
        <input type="text" id="cognome" name="cognome" maxlength="30" value="<?php echo h($studente['cognome']); ?>" required>
    </p>

    <p>
        <label for="data_nascita">Data di nascita:</label><br>
        <input type="date" id="data_nascita" name="data_nascita" value="<?php echo h($studente['data_nascita']); ?>" required>
    </p>

    <p>
        <label for="id_classe">Classe (opzionale):</label><br>
        <select id="id_classe" name="id_classe">
            <option value="">Nessuna classe</option>
            <?php foreach ($classi as $classe): ?>
                <option value="<?php echo h($classe['id_classe']); ?>" <?php echo $studente['id_classe'] !== null && (int) $studente['id_classe'] === (int) $classe['id_classe'] ? 'selected' : ''; ?>>
                    <?php echo h($classe['anno'] . $classe['sezione']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <button type="submit">Salva modifiche</button>
</form>

<?php renderPageEnd(); ?>
