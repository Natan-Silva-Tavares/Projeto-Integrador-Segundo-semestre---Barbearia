# Projeto Integrador - Barbearia Tesoura de Ouro

Sistema web para gerenciar **Clientes**, **Servicos** e **Agendamentos** da barbearia de bairro **Tesoura de Ouro**, com:
- Autenticacao (login) via banco
- Area administrativa protegida
- CRUD completo de `agendamentos` e CRUD de `clientes` e `servicos`
- Banco MySQL integrado ao backend em PHP

## Requisitos

- PHP 8+
- MySQL ou MariaDB
- Navegador

## Estrutura do projeto

- `login.php` / `logout.php` / `dashboard.php`
- `clients.php` / `services.php` / `appointments.php`
- `app/` (conexao com banco, autenticacao e layout)
- `database/schema.sql` (tabelas)
- `scripts/seed_admin.php` (cria um admin para testes)
- `assets/` (CSS/JS)

## Como rodar com XAMPP (ambiente da faculdade)

1. **Copie o projeto para o `htdocs`** do XAMPP  
   Exemplo de caminho: `C:\xampp\htdocs\tesoura-de-ouro` (a pasta do projeto fica aqui dentro).

2. **Inicie o XAMPP** e ligue:
   - `Apache`
   - `MySQL`

3. **Crie o banco no MySQL (phpMyAdmin)**  
   - Acesse `http://localhost/phpmyadmin`
   - Clique em **Banco de dados** e crie um banco chamado `barbearia`

4. **Crie as tabelas**  
   - Dentro do banco `barbearia`, vá na aba **SQL**
   - Cole o conteúdo de `database/schema.sql` e execute

5. **Ajuste as credenciais do banco se necessario**  
   - No XAMPP, o padrao costuma ser:
     - host: `127.0.0.1`
     - usuario: `root`
     - senha: _(vazia)_  
   - Isso ja esta configurado em `app/db.php`, entao normalmente voce nao precisa mudar nada.

6. **Crie um usuario admin para testes**
   - No Windows da faculdade, abra o terminal do XAMPP (ou `cmd`) dentro da pasta do projeto em `htdocs`
   - Rode: `php scripts/seed_admin.php`

7. **Acesse o sistema no navegador**
   - URL: `http://localhost/tesoura-de-ouro/login.php` (dependendo do nome da pasta que voce escolheu)

## Credenciais de exemplo (apenas para testes)

O script `scripts/seed_admin.php` cria o usuario:
- Email: `admin@tesouradeouro.com`
- Senha: `admin123`

## Observacoes

- O login valida senha com `password_hash`/`password_verify`.
- A area administrativa (CRUD) exige `role = admin`.
- Para alterar o usuario admin, edite `scripts/seed_admin.php`.

