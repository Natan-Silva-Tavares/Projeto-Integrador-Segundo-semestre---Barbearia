<?php

require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/render.php';

require_admin();

$pdo = db();

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));

    if ($name === '') {
        set_flash('error', 'Nome do cliente e obrigatorio.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO clients (name, phone, email) VALUES (?, ?, ?)');
        $stmt->execute([$name, $phone !== '' ? $phone : null, $email !== '' ? $email : null]);
        set_flash('success', 'Cliente cadastrado com sucesso.');
        redirect('clients.php');
    }
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));

    if ($id <= 0) {
        http_response_code(400);
        echo "ID invalido.";
        exit;
    }

    if ($name === '') {
        set_flash('error', 'Nome do cliente e obrigatorio.');
    } else {
        $stmt = $pdo->prepare('UPDATE clients SET name = ?, phone = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$name, $phone !== '' ? $phone : null, $email !== '' ? $email : null, $id]);
        set_flash('success', 'Cliente atualizado com sucesso.');
        redirect('clients.php');
    }
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id <= 0) {
        http_response_code(400);
        echo "ID invalido.";
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM clients WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('success', 'Cliente excluido com sucesso.');
    redirect('clients.php');
}

// Busca dados para edit/delete.
$client = null;
if (in_array($action, ['edit', 'delete'], true)) {
    if ($id <= 0) {
        http_response_code(400);
        echo "ID invalido.";
        exit;
    }
    $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $client = $stmt->fetch();
    if (!$client) {
        http_response_code(404);
        echo "Cliente nao encontrado.";
        exit;
    }
}

render_header('Clientes');
?>
<section class="card" style="margin-top:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <div>
            <h2 style="margin:0">Clientes</h2>
            <p class="muted" style="margin:6px 0 0">Cadastre e gerencie os clientes da barbearia.</p>
        </div>
        <div class="actions">
            <a class="btn btn--primary" href="clients.php?action=create">Novo cliente</a>
        </div>
    </div>

    <?php if ($action === 'create'): ?>
        <div style="margin-top:16px">
            <form method="post" action="clients.php?action=create" class="grid grid--2">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="name">Nome *</label>
                    <input id="name" name="name" type="text" required value="">
                </div>
                <div>
                    <label for="phone">Telefone</label>
                    <input id="phone" name="phone" type="text" value="">
                </div>
                <div style="grid-column:1 / -1">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="">
                </div>
                <div style="grid-column:1 / -1; display:flex; gap:10px; flex-wrap:wrap">
                    <button class="btn btn--primary" type="submit">Salvar</button>
                    <a class="btn btn--ghost" href="clients.php">Cancelar</a>
                </div>
            </form>
        </div>
    <?php elseif ($action === 'edit'): ?>
        <div style="margin-top:16px">
            <form method="post" action="clients.php?action=edit&id=<?php echo (int)$id; ?>" class="grid grid--2">
                <div>
                    <label for="name">Nome *</label>
                    <input id="name" name="name" type="text" required value="<?php echo h($client['name'] ?? ''); ?>">
                </div>
                <div>
                    <label for="phone">Telefone</label>
                    <input id="phone" name="phone" type="text" value="<?php echo h($client['phone'] ?? ''); ?>">
                </div>
                <div style="grid-column:1 / -1">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?php echo h($client['email'] ?? ''); ?>">
                </div>
                <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
                <div style="grid-column:1 / -1; display:flex; gap:10px; flex-wrap:wrap">
                    <button class="btn btn--primary" type="submit">Atualizar</button>
                    <a class="btn btn--ghost" href="clients.php">Cancelar</a>
                </div>
            </form>
        </div>
    <?php elseif ($action === 'delete'): ?>
        <div style="margin-top:16px">
            <p class="muted">Tem certeza que deseja excluir este cliente?</p>
            <p style="margin-top:8px;font-weight:700"><?php echo h($client['name'] ?? ''); ?></p>
            <form method="post" action="clients.php?action=delete&id=<?php echo (int)$id; ?>" style="margin-top:12px">
                <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <button class="btn btn--danger" type="submit">Excluir</button>
                    <a class="btn btn--ghost" href="clients.php">Cancelar</a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <?php
        $stmt = $pdo->query('SELECT id, name, phone, email, created_at, updated_at FROM clients ORDER BY id DESC');
        $clients = $stmt->fetchAll();
        ?>
        <div style="margin-top:16px">
            <?php if (!$clients): ?>
                <p class="muted">Nenhum cliente cadastrado.</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Acoes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($clients as $c): ?>
                        <tr>
                            <td><?php echo h($c['name'] ?? ''); ?></td>
                            <td><?php echo h($c['phone'] ?? ''); ?></td>
                            <td><?php echo h($c['email'] ?? ''); ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn" href="clients.php?action=edit&id=<?php echo (int)$c['id']; ?>">Editar</a>
                                    <a class="btn btn--ghost" href="clients.php?action=delete&id=<?php echo (int)$c['id']; ?>">Excluir</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
<?php render_footer(); ?>

