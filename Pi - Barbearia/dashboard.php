<?php

require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/render.php';

require_admin();

// Dashboard simples com indicadores e agenda de hoje.
$pdo = db();

$stmtClients = $pdo->query('SELECT COUNT(*) AS total FROM clients');
$clientsTotal = (int)($stmtClients->fetch()['total'] ?? 0);

$stmtServices = $pdo->query('SELECT COUNT(*) AS total FROM services');
$servicesTotal = (int)($stmtServices->fetch()['total'] ?? 0);

$stmtAppointments = $pdo->query('SELECT COUNT(*) AS total FROM appointments');
$appointmentsTotal = (int)($stmtAppointments->fetch()['total'] ?? 0);

$stmtToday = $pdo->query("
    SELECT
        a.id,
        a.scheduled_at,
        a.status,
        c.name AS client_name,
        s.name AS service_name
    FROM appointments a
    INNER JOIN clients c ON c.id = a.client_id
    INNER JOIN services s ON s.id = a.service_id
    WHERE DATE(a.scheduled_at) = CURDATE()
    ORDER BY a.scheduled_at ASC
    LIMIT 50
");
$today = $stmtToday->fetchAll();

render_header('Dashboard');
?>
<section class="cards">
    <div class="card">
        <div class="muted">Clientes</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px"><?php echo $clientsTotal; ?></div>
    </div>
    <div class="card">
        <div class="muted">Servicos</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px"><?php echo $servicesTotal; ?></div>
    </div>
    <div class="card">
        <div class="muted">Agendamentos</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px"><?php echo $appointmentsTotal; ?></div>
    </div>
</section>

<section class="card" style="margin-top:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <div>
            <h2 style="margin:0">Agenda de hoje</h2>
            <p class="muted" style="margin:6px 0 0">Visualize, edite ou crie um agendamento.</p>
        </div>
        <div class="actions">
            <a class="btn btn--primary" href="appointments.php?action=create">Novo agendamento</a>
        </div>
    </div>

    <div style="margin-top:16px">
        <?php if (!$today): ?>
            <p class="muted">Nenhum agendamento para hoje.</p>
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
                    <?php foreach ($today as $row): ?>
                        <tr>
                            <td><?php echo h(date('H:i', strtotime($row['scheduled_at']))); ?></td>
                            <td><?php echo h($row['client_name']); ?></td>
                            <td><?php echo h($row['service_name']); ?></td>
                            <td>
                                <?php
                                $status = $row['status'];
                                ?>
                                <span class="badge badge--<?php echo h($status); ?>"><?php echo h($status); ?></span>
                            </td>
                            <td>
                                <a class="btn" href="appointments.php?action=edit&id=<?php echo (int)$row['id']; ?>">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

<?php render_footer(); ?>

