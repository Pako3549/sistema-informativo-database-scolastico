<?php

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function renderMainMenu(): void
{
    echo '<div class="menu">';
    echo '<a href="voti.php">Gestione voti</a>';
    echo '<a href="iscrizioni.php">Gestione iscrizioni</a>';
    echo '<a href="verifiche.php">Gestione verifiche</a>';
    echo '<a href="studenti.php">Gestione studenti</a>';
    echo '<a href="docenti.php">Gestione docenti</a>';
    echo '<a href="corsi.php">Gestione corsi</a>';
    echo '</div>';
}

function renderPageStart(string $title): void
{
    echo '<!DOCTYPE html>';
    echo '<html lang="it">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<link rel="stylesheet" href="../common/style.css">';
    echo '<title>' . h($title) . '</title>';
    echo '</head>';
    echo '<body>';
    echo '<div class="container">';
    echo '<div class="header">';
    echo '<h1>Sistema Informativo Scolastico</h1>';
    echo '<p>' . h($title) . '</p>';
    echo '<a class="home-link" href="../index.html">Torna al menu principale</a>';
    echo '</div>';
    renderMainMenu();
    echo '<div class="content">';
}

function renderPageEnd(): void
{
    echo '</div>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
}

function renderFlashMessage(): void
{
    $message = trim($_GET['msg'] ?? '');
    if ($message !== '') {
        echo '<div class="flash-message flash-info">' . h($message) . '</div>';
    }
}

function renderErrorAndExit(string $message, string $backUrl = '../index.html'): void
{
    echo '<!DOCTYPE html>';
    echo '<html lang="it">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<link rel="stylesheet" href="../common/style.css">';
    echo '<title>Errore</title>';
    echo '</head>';
    echo '<body>';
    echo '<div class="container">';
    echo '<div class="header">';
    echo '<h1>Errore</h1>';
    echo '<p>Si è verificato un problema</p>';
    echo '</div>';
    echo '<div class="content">';
    echo '<div class="flash-message flash-error">' . h($message) . '</div>';
    echo '<p><a class="button-link" href="' . h($backUrl) . '">Torna indietro</a></p>';
    echo '</div>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
    exit;
}

function redirectWithMessage(string $target, string $message): void
{
    $separator = strpos($target, '?') === false ? '?' : '&';
    header('Location: ' . $target . $separator . 'msg=' . rawurlencode($message));
    exit;
}
