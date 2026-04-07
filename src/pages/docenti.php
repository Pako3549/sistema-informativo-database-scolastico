<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/layout.php';

$cognomeLike = trim($_GET['cognome'] ?? '');

$sql = 'SELECT id_docente, nome, cognome, email FROM docenti WHERE 1 = 1';
$params = [];
if ($cognomeLike !== '') {
    $sql .= ' AND cognome LIKE :cognome';
    $params[':cognome'] = '%' . $cognomeLike . '%';
}
$sql .= ' ORDER BY cognome, nome';

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->execute();
$docenti = $stmt->fetchAll();

renderPageStart('Gestione Docenti');
renderFlashMessage();
?>

<h2>Inserimento Docente</h2>
<form action="../actions/docenti-create.php" method="post">
    <p>
        <label for="nome">Nome:</label><br>
        <input type="text" id="nome" name="nome" maxlength="30" required>
    </p>

    <p>
        <label for="cognome">Cognome:</label><br>
        <input type="text" id="cognome" name="cognome" maxlength="30" required>
    </p>

    <p>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" maxlength="50" required>
    </p>

    <button type="submit">Inserisci docente</button>
</form>

<h2>Filtri docenti</h2>
<form action="docenti.php" method="get">
    <p>
        <label for="f_cognome">Cognome contiene:</label><br>
        <input type="text" id="f_cognome" name="cognome" value="<?php echo h($cognomeLike); ?>">
    </p>

    <button type="submit">Applica filtri</button>
    <a class="button-link" href="docenti.php">Reimposta filtri</a>
</form>

<h2>Elenco Docenti</h2>
<?php if (count($docenti) === 0): ?>
    <p>Non sono presenti docenti.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID docente</th>
            <th>Nome</th>
            <th>Cognome</th>
            <th>Email</th>
            <th>Azioni</th>
        </tr>
        <?php foreach ($docenti as $riga): ?>
            <tr>
                <td><?php echo h($riga['id_docente']); ?></td>
                <td><?php echo h($riga['nome']); ?></td>
                <td><?php echo h($riga['cognome']); ?></td>
                <td><?php echo h($riga['email']); ?></td>
                <td>
                    <a class="action-link" href="docenti-edit.php?id_docente=<?php echo h($riga['id_docente']); ?>">Modifica</a>
                    <form action="../actions/docenti-delete.php" method="post" style="display:inline;">
                        <input type="hidden" name="id_docente" value="<?php echo h($riga['id_docente']); ?>">
                        <button type="submit" onclick="return confirm('Confermi l\'eliminazione del docente?');">Elimina</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php renderPageEnd(); ?>
