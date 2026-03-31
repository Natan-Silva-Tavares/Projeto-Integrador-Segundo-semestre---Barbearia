<?php

require_once __DIR__ . '/auth.php';

function render_header(string $title): void
{
    $user = current_user();

    ?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h($title); ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<header class="topbar">
    <div class="topbar__brand">Barbearia Tesoura de Ouro</div>
    <?php if ($user): ?>
        <nav class="topbar__nav" aria-label="Navegacao principal">
            <a class="navlink" href="dashboard.php">Dashboard</a>
            <?php if ($user['role'] === 'admin'): ?>
                <a class="navlink" href="clients.php">Clientes</a>
                <a class="navlink" href="services.php">Servicos</a>
            <?php endif; ?>
            <?php if ($user['role'] === 'admin'): ?>
                <a class="navlink" href="appointments.php">Agendamentos</a>
            <?php endif; ?>
        </nav>
        <div class="topbar__user">
            <span class="muted"><?php echo h($user['name']); ?></span>
            <a class="btn btn--ghost" href="logout.php">Sair</a>
        </div>
    <?php endif; ?>
</header>

<main class="container">
    <?php
    $flash = get_flash();
    if ($flash): ?>
        <div class="flash flash--<?php echo h($flash['type'] ?? 'info'); ?>">
            <?php echo h($flash['message'] ?? ''); ?>
        </div>
    <?php endif; ?>
<?php
}

function render_footer(): void
{
    ?>
</main>
<footer class="footer">
    <div class="container footer__inner">
        <span class="muted">Sistema de gestao - Projeto Integrador</span>
    </div>
</footer>
<script src="assets/js/app.js"></script>
</body>
</html>
<?php
}

