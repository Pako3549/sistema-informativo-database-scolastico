<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idVotoRaw = trim($_GET['id_voto'] ?? '');
if (!ctype_digit($idVotoRaw) || (int) $idVotoRaw <= 0) {
    renderErrorAndExit('ID voto non valido.', 'voti.php');
}

$idVoto = (int) $idVotoRaw;

$stmt = $pdo->prepare('SELECT id_voto, id_verifica, id_studente, voto, commento FROM voti WHERE id_voto = :id LIMIT 1');
$stmt->bindValue(':id', $idVoto, PDO::PARAM_INT);
$stmt->execute();
$voto = $stmt->fetch();

if (!$voto) {
    renderErrorAndExit('Voto non trovato.', 'voti.php');
}

$studenti = getStudenti($pdo);
$verifiche = getVerificheConCorso($pdo);

renderPageStart('Modifica Voto');
?>

<form action="../actions/voti-update.php" method="post">
    <input type="hidden" name="id_voto" value="<?php echo h($voto['id_voto']); ?>">

    <p>
        <label for="id_studente">Studente:</label><br>
        <select id="id_studente" name="id_studente" required>
            <?php foreach ($studenti as $studente): ?>
                <option value="<?php echo h($studente['id_studente']); ?>" <?php echo (int) $voto['id_studente'] === (int) $studente['id_studente'] ? 'selected' : ''; ?>>
                    <?php echo h($studente['cognome'] . ' ' . $studente['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="id_verifica">Verifica:</label><br>
        <select id="id_verifica" name="id_verifica" required>
            <?php foreach ($verifiche as $verifica): ?>
                <option value="<?php echo h($verifica['id_verifica']); ?>" <?php echo (int) $voto['id_verifica'] === (int) $verifica['id_verifica'] ? 'selected' : ''; ?>>
                    <?php echo h($verifica['nome_corso'] . ' - ' . $verifica['tipo'] . ' - ' . $verifica['data_verifica']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="voto">Voto (1-10):</label><br>
        <input type="number" id="voto" name="voto" min="1" max="10" value="<?php echo h($voto['voto']); ?>" required>
    </p>

    <p>
        <label for="commento">Commento (opzionale):</label><br>
        <input type="text" id="commento" name="commento" maxlength="100" value="<?php echo h($voto['commento'] ?? ''); ?>">
    </p>

    <button type="submit">Salva modifiche</button>
</form>

<?php renderPageEnd(); ?>
