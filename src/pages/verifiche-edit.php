<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';
require_once __DIR__ . '/../common/helpers.php';

$idVerificaRaw = trim($_GET['id_verifica'] ?? '');
if (!ctype_digit($idVerificaRaw) || (int) $idVerificaRaw <= 0) {
    renderErrorAndExit('ID verifica non valido.', 'verifiche.php');
}
$idVerifica = (int) $idVerificaRaw;

$stmt = $pdo->prepare('SELECT id_verifica, id_corso, data_verifica, tipo FROM verifiche WHERE id_verifica = :id LIMIT 1');
$stmt->bindValue(':id', $idVerifica, PDO::PARAM_INT);
$stmt->execute();
$verifica = $stmt->fetch();

if (!$verifica) {
    renderErrorAndExit('Verifica non trovata.', 'verifiche.php');
}

$corsi = getCorsi($pdo);

renderPageStart('Modifica Verifica');
?>

<form action="../actions/verifiche-update.php" method="post">
    <input type="hidden" name="id_verifica" value="<?php echo h($verifica['id_verifica']); ?>">

    <p>
        <label for="id_corso">Corso:</label><br>
        <select id="id_corso" name="id_corso" required>
            <?php foreach ($corsi as $corso): ?>
                <option value="<?php echo h($corso['id_corso']); ?>" <?php echo (int) $verifica['id_corso'] === (int) $corso['id_corso'] ? 'selected' : ''; ?>>
                    <?php echo h($corso['nome_corso']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="data_verifica">Data verifica:</label><br>
        <input type="date" id="data_verifica" name="data_verifica" value="<?php echo h($verifica['data_verifica']); ?>" required>
    </p>

    <p>
        <label for="tipo">Tipo:</label><br>
        <input type="text" id="tipo" name="tipo" maxlength="20" value="<?php echo h($verifica['tipo']); ?>" required>
    </p>

    <button type="submit">Salva modifiche</button>
</form>

<?php renderPageEnd(); ?>
