<?php

function parsePositiveInt($value): ?int
{
    $raw = trim((string) $value);
    if ($raw === '') {
        return null;
    }
    if (!ctype_digit($raw)) {
        return null;
    }
    $number = (int) $raw;
    if ($number <= 0) {
        return null;
    }
    return $number;
}

function getStudenti(PDO $pdo): array
{
    $sql = 'SELECT id_studente, nome, cognome FROM studenti ORDER BY cognome, nome';
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getCorsi(PDO $pdo): array
{
    $sql = 'SELECT id_corso, nome_corso FROM corsi ORDER BY nome_corso';
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getClassi(PDO $pdo): array
{
    $sql = 'SELECT id_classe, anno, sezione FROM classi ORDER BY anno, sezione';
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getDocenti(PDO $pdo): array
{
    $sql = 'SELECT id_docente, nome, cognome FROM docenti ORDER BY cognome, nome';
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getMaterie(PDO $pdo): array
{
    $sql = 'SELECT id_materia, nome_materia FROM materie ORDER BY nome_materia';
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getVerificheConCorso(PDO $pdo): array
{
    $sql = '
        SELECT
            v.id_verifica,
            v.data_verifica,
            v.tipo,
            c.nome_corso
        FROM verifiche v
        JOIN corsi c ON c.id_corso = v.id_corso
        ORDER BY v.data_verifica DESC, c.nome_corso, v.id_verifica
    ';
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function existsStudente(PDO $pdo, int $idStudente): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM studenti WHERE id_studente = :id LIMIT 1');
    $stmt->bindValue(':id', $idStudente, PDO::PARAM_INT);
    $stmt->execute();
    return (bool) $stmt->fetchColumn();
}

function existsVerifica(PDO $pdo, int $idVerifica): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM verifiche WHERE id_verifica = :id LIMIT 1');
    $stmt->bindValue(':id', $idVerifica, PDO::PARAM_INT);
    $stmt->execute();
    return (bool) $stmt->fetchColumn();
}

function existsVoto(PDO $pdo, int $idVoto): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM voti WHERE id_voto = :id LIMIT 1');
    $stmt->bindValue(':id', $idVoto, PDO::PARAM_INT);
    $stmt->execute();
    return (bool) $stmt->fetchColumn();
}

function existsCorso(PDO $pdo, int $idCorso): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM corsi WHERE id_corso = :id LIMIT 1');
    $stmt->bindValue(':id', $idCorso, PDO::PARAM_INT);
    $stmt->execute();
    return (bool) $stmt->fetchColumn();
}

function existsClasse(PDO $pdo, int $idClasse): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM classi WHERE id_classe = :id LIMIT 1');
    $stmt->bindValue(':id', $idClasse, PDO::PARAM_INT);
    $stmt->execute();
    return (bool) $stmt->fetchColumn();
}

function existsDocente(PDO $pdo, int $idDocente): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM docenti WHERE id_docente = :id LIMIT 1');
    $stmt->bindValue(':id', $idDocente, PDO::PARAM_INT);
    $stmt->execute();
    return (bool) $stmt->fetchColumn();
}

function existsMateria(PDO $pdo, int $idMateria): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM materie WHERE id_materia = :id LIMIT 1');
    $stmt->bindValue(':id', $idMateria, PDO::PARAM_INT);
    $stmt->execute();
    return (bool) $stmt->fetchColumn();
}

function isValidDateYmd(string $date): bool
{
    $date = trim($date);
    if ($date === '') {
        return false;
    }

    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
    return $dateTime !== false && $dateTime->format('Y-m-d') === $date;
}
