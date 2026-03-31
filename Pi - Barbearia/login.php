<?php

require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/render.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();

    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        set_flash('error', 'Preencha email e senha.');
    } else {
        if (login_user($email, $password)) {
            set_flash('success', 'Login realizado com sucesso.');
            redirect('dashboard.php');
        }
        set_flash('error', 'Email ou senha invalidos.');
    }
}

render_header('Login');
?>
<section class="container">
    <div class="grid grid--2">
        <div class="card">
            <h2 style="margin-top:0">Entrar</h2>
            <form method="post" action="login.php">
                <?php echo csrf_field(); ?>
                <div style="margin-bottom:12px">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" required autocomplete="username">
                </div>
                <div style="margin-bottom:12px">
                    <label for="password">Senha</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                </div>
                <button class="btn btn--primary" type="submit">Login</button>
            </form>
        </div>
        <div class="card">
            <h2 style="margin-top:0">Sobre o sistema</h2>
            <p class="muted">
                Sistema de gestao para uma barbearia com login (autenticacao no banco) e painel administrativo protegido.
            </p>
            <p class="muted">
                Para testes, use o administrador criado em <code>scripts/seed_admin.php</code>.
            </p>
        </div>
    </div>
</section>
<?php render_footer(); ?>

