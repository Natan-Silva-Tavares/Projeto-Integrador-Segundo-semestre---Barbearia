<?php

require_once __DIR__ . '/auth.php';

function status_label(string $status): string
{
    $labels = [
        'agendado' => 'Agendado',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
    ];

    return $labels[$status] ?? $status;
}

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
    <link rel="icon" href="../frontend/images/icon-tesoura.png" type="image/png">
    <link rel="stylesheet" href="../frontend/css/styles.css">
    <script>
        (function () {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body>
<header class="topbar">
    <div class="topbar__inner container">
        <a class="topbar__brand" href="<?php echo $user ? 'dashboard.php' : 'login.php'; ?>">
            <img class="topbar__logo topbar__logo--light" src="../frontend/images/logo-clara.png" alt="Barbearia Tesoura de Ouro" width="220" height="48">
            <img class="topbar__logo topbar__logo--dark" src="../frontend/images/logo.png" alt="Barbearia Tesoura de Ouro" width="220" height="48">
        </a>
        <div class="topbar__right">
            <?php if ($user): ?>
                <nav class="topbar__nav" aria-label="Navegação principal">
                    <a class="navlink" href="dashboard.php">Dashboard</a>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a class="navlink" href="clients.php">Clientes</a>
                        <a class="navlink" href="services.php">Serviços</a>
                        <a class="navlink" href="appointments.php">Agendamentos</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
            <button type="button" class="btn btn--ghost theme-toggle" aria-label="Alternar modo noturno" title="Modo noturno">
                <span class="theme-toggle__icon" aria-hidden="true">🌙</span>
            </button>
            <?php if ($user): ?>
                <div class="topbar__user">
                    <span class="muted"><?php echo h($user['name']); ?></span>
                    <a class="btn btn--ghost" href="logout.php">Sair</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
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
        <span class="muted">Sistema de gestão - Tesoura de Ouro</span>
    </div>
</footer>
<script src="../frontend/js/app.js"></script>
</body>
</html>
<?php
}

