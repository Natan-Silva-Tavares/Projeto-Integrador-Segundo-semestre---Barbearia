<?php

require_once __DIR__ . '/../app/db.php';

$email = 'admin@tesouradeouro.com';
$name = 'Admin Tesoura de Ouro';
$password = 'admin123';
$role = 'admin';

$pdo = db();

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$existing = $stmt->fetch();

if ($existing) {
    echo "Admin ja existe ({$email}). Nao foi necessario recriar.\n";
    exit;
}

// Hash seguro de senha (nao salvar senha em texto puro).
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $email, $hash, $role]);

echo "Admin criado com sucesso.\n";
echo "Email: {$email}\n";
echo "Senha: {$password}\n";

