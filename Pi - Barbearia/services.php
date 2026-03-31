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
    $durationMinutes = trim((string)($_POST['duration_minutes'] ?? ''));
    $price = trim((string)($_POST['price'] ?? ''));

    $durationMinutesInt = (int)$durationMinutes;
    $priceFloat = (float)$price;

    if ($name === '' || $durationMinutesInt <= 0 || $priceFloat <= 0) {
        set_flash('error', 'Preencha nome, duracao (minutos) e preco corretamente.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO services (name, duration_minutes, price) VALUES (?, ?, ?)');
        $stmt->execute([$name, $durationMinutesInt, $priceFloat]);
        set_flash('success', 'Servico cadastrado com sucesso.');
        redirect('services.php');
    }
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $durationMinutes = trim((string)($_POST['duration_minutes'] ?? ''));
    $price = trim((string)($_POST['price'] ?? ''));

    $durationMinutesInt = (int)$durationMinutes;
    $priceFloat = (float)$price;

    if ($id <= 0) {
        http_response_code(400);
        echo "ID invalido.";
        exit;
    }

    if ($name === '' || $durationMinutesInt <= 0 || $priceFloat <= 0) {
        set_flash('error', 'Preencha nome, duracao (minutos) e preco corretamente.');
    } else {
        $stmt = $pdo->prepare('UPDATE services SET name = ?, duration_minutes = ?, price = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$name, $durationMinutesInt, $priceFloat, $id]);
        set_flash('success', 'Servico atualizado com sucesso.');
        redirect('services.php');
    }
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id <= 0) {
        http_response_code(400);
        echo "ID invalido.";
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('success', 'Servico excluido com sucesso.');
    redirect('services.php');
}

// Busca dados para edit/delete.
$service = null;
if (in_array($action, ['edit', 'delete'], true)) {
    if ($id <= 0) {
        http_response_code(400);
        echo "ID invalido.";
        exit;
    }
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    if (!$service) {
        http_response_code(404);
        echo "Servico nao encontrado.";
        exit;
    }
}

render_header('Servicos');
?>
<section class="card" style="margin-top:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <div>
            <h2 style="margin:0">Servicos</h2>
            <p class="muted" style="margin:6px 0 0">Cadastre os servicos e seus valores.</p>
        </div>
        <div class="actions">
            <a class="btn btn--primary" href="services.php?action=create">Novo servico</a>
        </div>
    </div>

    <?php if ($action === 'create'): ?>
        <div style="margin-top:16px">
            <form method="post" action="services.php?action=create" class="grid grid--2">
                <?php echo csrf_field(); ?>
                <div style="grid-column:1 / -1">
                    <label for="name">Nome *</label>
                    <input id="name" name="name" type="text" required value="">
                </div>
                <div>
                    <label for="duration_minutes">Duracao (min) *</label>
                    <input id="duration_minutes" name="duration_minutes" type="number" min="1" required value="">
                </div>
                <div>
                    <label for="price">Preco (R$) *</label>
                    <input id="price" name="price" type="number" step="0.01" min="0" required value="">
                </div>
                <div style="grid-column:1 / -1; display:flex; gap:10px; flex-wrap:wrap">
                    <button class="btn btn--primary" type="submit">Salvar</button>
                    <a class="btn btn--ghost" href="services.php">Cancelar</a>
                </div>
            </form>
        </div>
    <?php elseif ($action === 'edit'): ?>
        <div style="margin-top:16px">
            <form method="post" action="services.php?action=edit&id=<?php echo (int)$id; ?>" class="grid grid--2">
                <?php echo csrf_field(); ?>
                <div style="grid-column:1 / -1">
                    <label for="name">Nome *</label>
                    <input id="name" name="name" type="text" required value="<?php echo h($service['name'] ?? ''); ?>">
                </div>
                <div>
                    <label for="duration_minutes">Duracao (min) *</label>
                    <input id="duration_minutes" name="duration_minutes" type="number" min="1" required value="<?php echo h((string)($service['duration_minutes'] ?? '')); ?>">
                </div>
                <div>
                    <label for="price">Preco (R$) *</label>
                    <input id="price" name="price" type="number" step="0.01" min="0" required value="<?php echo h((string)($service['price'] ?? '')); ?>">
                </div>
                <div style="grid-column:1 / -1; display:flex; gap:10px; flex-wrap:wrap">
                    <button class="btn btn--primary" type="submit">Atualizar</button>
                    <a class="btn btn--ghost" href="services.php">Cancelar</a>
                </div>
            </form>
        </div>
    <?php elseif ($action === 'delete'): ?>
        <div style="margin-top:16px">
            <p class="muted">Tem certeza que deseja excluir este servico?</p>
            <p style="margin-top:8px;font-weight:700"><?php echo h($service['name'] ?? ''); ?></p>
            <form method="post" action="services.php?action=delete&id=<?php echo (int)$id; ?>" style="margin-top:12px">
                <?php echo csrf_field(); ?>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <button class="btn btn--danger" type="submit">Excluir</button>
                    <a class="btn btn--ghost" href="services.php">Cancelar</a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <?php
        $stmt = $pdo->query('SELECT id, name, duration_minutes, price, created_at, updated_at FROM services ORDER BY id DESC');
        $services = $stmt->fetchAll();
        ?>
        <div style="margin-top:16px">
            <?php if (!$services): ?>
                <p class="muted">Nenhum servico cadastrado.</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Duracao</th>
                        <th>Preco</th>
                        <th>Acoes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($services as $s): ?>
                        <tr>
                            <td><?php echo h($s['name'] ?? ''); ?></td>
                            <td><?php echo h((string)($s['duration_minutes'] ?? '')); ?> min</td>
                            <td>R$ <?php echo h(number_format((float)($s['price'] ?? 0), 2, ',', '.')); ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn" href="services.php?action=edit&id=<?php echo (int)$s['id']; ?>">Editar</a>
                                    <a class="btn btn--ghost" href="services.php?action=delete&id=<?php echo (int)$s['id']; ?>">Excluir</a>
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

