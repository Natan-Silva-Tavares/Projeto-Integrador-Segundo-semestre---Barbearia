<?php

require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/render.php';

require_admin();

$pdo = db();

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

function parse_datetime_local(string $raw): ?string
{
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }

    // datetime-local vem geralmente no formato "YYYY-MM-DDTHH:MM"
    $raw = str_replace('T', ' ', $raw);
    try {
        $dt = new DateTime($raw);
        return $dt->format('Y-m-d H:i:s');
    } catch (Throwable) {
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
}

// ===== DELETE =====
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id <= 0) {
        http_response_code(400);
        echo "ID invalido.";
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('success', 'Agendamento excluido com sucesso.');
    redirect('appointments.php');
}

// ===== CREATE =====
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = (int)($_POST['client_id'] ?? 0);
    $service_id = (int)($_POST['service_id'] ?? 0);
    $scheduled_at_raw = (string)($_POST['scheduled_at'] ?? '');
    $status = (string)($_POST['status'] ?? 'agendado');
    $notes = trim((string)($_POST['notes'] ?? ''));

    $scheduled_at = parse_datetime_local($scheduled_at_raw);

    $validStatuses = ['agendado', 'concluido', 'cancelado'];
    if ($client_id <= 0 || $service_id <= 0 || $scheduled_at === null || !in_array($status, $validStatuses, true)) {
        set_flash('error', 'Verifique os campos do agendamento.');
    } else {
        $created_by = (int)(current_user()['id'] ?? 0);
        $stmt = $pdo->prepare('
            INSERT INTO appointments (client_id, service_id, scheduled_at, status, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$client_id, $service_id, $scheduled_at, $status, $notes !== '' ? $notes : null, $created_by]);
        set_flash('success', 'Agendamento criado com sucesso.');
        redirect('appointments.php');
    }
}

// ===== UPDATE =====
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id <= 0) {
        http_response_code(400);
        echo "ID invalido.";
        exit;
    }

    $client_id = (int)($_POST['client_id'] ?? 0);
    $service_id = (int)($_POST['service_id'] ?? 0);
    $scheduled_at_raw = (string)($_POST['scheduled_at'] ?? '');
    $status = (string)($_POST['status'] ?? 'agendado');
    $notes = trim((string)($_POST['notes'] ?? ''));

    $scheduled_at = parse_datetime_local($scheduled_at_raw);

    $validStatuses = ['agendado', 'concluido', 'cancelado'];
    if ($client_id <= 0 || $service_id <= 0 || $scheduled_at === null || !in_array($status, $validStatuses, true)) {
        set_flash('error', 'Verifique os campos do agendamento.');
    } else {
        $stmt = $pdo->prepare('
            UPDATE appointments
            SET client_id = ?, service_id = ?, scheduled_at = ?, status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ');
        $stmt->execute([$client_id, $service_id, $scheduled_at, $status, $notes !== '' ? $notes : null, $id]);
        set_flash('success', 'Agendamento atualizado com sucesso.');
        redirect('appointments.php');
    }
}

// ===== LOAD FOR EDIT/DELETE =====
$appointment = null;
if (in_array($action, ['edit', 'delete'], true)) {
    if ($id <= 0) {
        http_response_code(400);
        echo "ID invalido.";
        exit;
    }
    $stmt = $pdo->prepare('
        SELECT a.*, c.name AS client_name, s.name AS service_name
        FROM appointments a
        INNER JOIN clients c ON c.id = a.client_id
        INNER JOIN services s ON s.id = a.service_id
        WHERE a.id = ?
        LIMIT 1
    ');
    $stmt->execute([$id]);
    $appointment = $stmt->fetch();
    if (!$appointment) {
        http_response_code(404);
        echo "Agendamento nao encontrado.";
        exit;
    }
}

// ===== LIST =====
$date = (string)($_GET['date'] ?? '');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

$stmtClients = $pdo->query('SELECT id, name FROM clients ORDER BY name');
$clients = $stmtClients->fetchAll();

$stmtServices = $pdo->query('SELECT id, name FROM services ORDER BY name');
$services = $stmtServices->fetchAll();

render_header('Agendamentos');
?>
<section class="card" style="margin-top:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <div>
            <h2 style="margin:0">Agendamentos</h2>
            <p class="muted" style="margin:6px 0 0">CRUD completo do agendamento. Relaciona Cliente + Servico.</p>
        </div>
        <div class="actions">
            <a class="btn btn--primary" href="appointments.php?action=create">Novo agendamento</a>
        </div>
    </div>

    <div style="margin-top:14px">
        <form method="get" action="appointments.php" class="grid grid--2">
            <div>
                <label for="date">Data</label>
                <input id="date" name="date" type="date" value="<?php echo h($date); ?>">
            </div>
            <div style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn btn--ghost" href="appointments.php">Hoje</a>
            </div>
        </form>
    </div>

    <div style="margin-top:16px">
        <?php if ($action === 'create'): ?>
            <form method="post" action="appointments.php?action=create" class="grid grid--2">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="client_id">Cliente *</label>
                    <select id="client_id" name="client_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?php echo (int)$c['id']; ?>"><?php echo h($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="service_id">Servico *</label>
                    <select id="service_id" name="service_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($services as $s): ?>
                            <option value="<?php echo (int)$s['id']; ?>"><?php echo h($s['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="grid-column:1 / -1">
                    <label for="scheduled_at">Data e hora *</label>
                    <input id="scheduled_at" name="scheduled_at" type="datetime-local" required value="">
                </div>
                <div>
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="agendado">agendado</option>
                        <option value="concluido">concluido</option>
                        <option value="cancelado">cancelado</option>
                    </select>
                </div>
                <div style="grid-column:1 / -1">
                    <label for="notes">Observacoes</label>
                    <textarea id="notes" name="notes"></textarea>
                </div>
                <div style="grid-column:1 / -1; display:flex; gap:10px; flex-wrap:wrap">
                    <button class="btn btn--primary" type="submit">Salvar</button>
                    <a class="btn btn--ghost" href="appointments.php">Cancelar</a>
                </div>
            </form>
        <?php elseif ($action === 'edit'): ?>
            <form method="post" action="appointments.php?action=edit&id=<?php echo (int)$appointment['id']; ?>" class="grid grid--2">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="client_id">Cliente *</label>
                    <select id="client_id" name="client_id" required>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)$c['id'] === (int)$appointment['client_id']) ? 'selected' : ''; ?>>
                                <?php echo h($c['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="service_id">Servico *</label>
                    <select id="service_id" name="service_id" required>
                        <?php foreach ($services as $s): ?>
                            <option value="<?php echo (int)$s['id']; ?>" <?php echo ((int)$s['id'] === (int)$appointment['service_id']) ? 'selected' : ''; ?>>
                                <?php echo h($s['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="grid-column:1 / -1">
                    <label for="scheduled_at">Data e hora *</label>
                    <?php
                    // datetime-local espera "YYYY-MM-DDTHH:MM"
                    $scheduledAtLocal = str_replace(' ', 'T', substr((string)$appointment['scheduled_at'], 0, 16));
                    ?>
                    <input id="scheduled_at" name="scheduled_at" type="datetime-local" required value="<?php echo h($scheduledAtLocal); ?>">
                </div>
                <div>
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="agendado" <?php echo ($appointment['status'] === 'agendado') ? 'selected' : ''; ?>>agendado</option>
                        <option value="concluido" <?php echo ($appointment['status'] === 'concluido') ? 'selected' : ''; ?>>concluido</option>
                        <option value="cancelado" <?php echo ($appointment['status'] === 'cancelado') ? 'selected' : ''; ?>>cancelado</option>
                    </select>
                </div>
                <div style="grid-column:1 / -1">
                    <label for="notes">Observacoes</label>
                    <textarea id="notes" name="notes"><?php echo h($appointment['notes'] ?? ''); ?></textarea>
                </div>
                <div style="grid-column:1 / -1; display:flex; gap:10px; flex-wrap:wrap">
                    <button class="btn btn--primary" type="submit">Atualizar</button>
                    <a class="btn btn--ghost" href="appointments.php">Cancelar</a>
                </div>
            </form>
        <?php elseif ($action === 'delete'): ?>
            <div>
                <p class="muted">Tem certeza que deseja excluir este agendamento?</p>
                <div class="card" style="margin-top:12px">
                    <div><b>Cliente:</b> <?php echo h($appointment['client_name'] ?? ''); ?></div>
                    <div><b>Servico:</b> <?php echo h($appointment['service_name'] ?? ''); ?></div>
                    <div><b>Horario:</b> <?php echo h((string)$appointment['scheduled_at']); ?></div>
                    <div style="margin-top:6px">
                        <span class="badge badge--<?php echo h($appointment['status'] ?? 'agendado'); ?>">
                            <?php echo h($appointment['status'] ?? 'agendado'); ?>
                        </span>
                    </div>
                </div>

                <form method="post" action="appointments.php?action=delete&id=<?php echo (int)$appointment['id']; ?>" style="margin-top:12px">
                    <?php echo csrf_field(); ?>
                    <div style="display:flex; gap:10px; flex-wrap:wrap">
                        <button class="btn btn--danger" type="submit">Excluir</button>
                        <a class="btn btn--ghost" href="appointments.php">Cancelar</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <?php
            $stmt = $pdo->prepare('
                SELECT
                    a.id,
                    a.scheduled_at,
                    a.status,
                    c.name AS client_name,
                    s.name AS service_name
                FROM appointments a
                INNER JOIN clients c ON c.id = a.client_id
                INNER JOIN services s ON s.id = a.service_id
                WHERE DATE(a.scheduled_at) = ?
                ORDER BY a.scheduled_at ASC
            ');
            $stmt->execute([$date]);
            $rows = $stmt->fetchAll();
            ?>

            <?php if (!$rows): ?>
                <p class="muted" style="margin-top:10px">Nenhum agendamento encontrado para esta data.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Horario</th>
                            <th>Cliente</th>
                            <th>Servico</th>
                            <th>Status</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?php echo h(date('H:i', strtotime($row['scheduled_at']))); ?></td>
                            <td><?php echo h($row['client_name']); ?></td>
                            <td><?php echo h($row['service_name']); ?></td>
                            <td>
                                <span class="badge badge--<?php echo h($row['status']); ?>">
                                    <?php echo h($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="btn" href="appointments.php?action=edit&id=<?php echo (int)$row['id']; ?>">Editar</a>
                                    <a class="btn btn--ghost" href="appointments.php?action=delete&id=<?php echo (int)$row['id']; ?>">Excluir</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php render_footer(); ?>

