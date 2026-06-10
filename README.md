# Barbearia Tesoura de Ouro

Sistema web de gestão para a barbearia **Tesoura de Ouro**, desenvolvido como Projeto Integrador do 2º semestre do curso de **Sistemas para Internet**.

**Repositório:** [Projeto-Integrador-Segundo-semestre---Barbearia](https://github.com/Natan-Silva-Tavares/Projeto-Integrador-Segundo-semestre---Barbearia)

---

## Sobre o projeto

### Problema que o sistema resolve

Barbearias de bairro costumam controlar clientes, serviços e agendamentos em cadernos ou planilhas, o que gera desorganização, perda de informações e dificuldade para consultar a agenda do dia.

Este sistema centraliza essas operações em uma aplicação web com login, banco de dados e painel administrativo.

### Usuário principal

**Administrador da barbearia** — responsável por cadastrar clientes, serviços e agendamentos, além de acompanhar a agenda diária.

### Funcionalidades principais

- Autenticação com e-mail e senha (validação no banco de dados)
- Dashboard com indicadores e agenda do dia
- CRUD de **Clientes** (criar, listar, editar, excluir)
- CRUD de **Serviços** (criar, listar, editar, excluir)
- CRUD de **Agendamentos** (criar, listar, editar, excluir)
- Filtro de agendamentos por data
- Validação de dados (regex no cadastro de clientes)
- Modo claro e modo noturno
- Proteção de rotas administrativas por autenticação

### Entidades do banco de dados

| Entidade | Descrição |
|----------|-----------|
| `users` | Usuários do sistema (administrador) |
| `clients` | Clientes da barbearia |
| `services` | Serviços oferecidos (nome, duração, preço) |
| `appointments` | Agendamentos (relaciona cliente, serviço e usuário criador) |

### Fluxo principal

1. Administrador acessa a tela de login
2. Após autenticação, visualiza o dashboard
3. Cadastra clientes e serviços
4. Cria agendamentos vinculando cliente + serviço + data/hora
5. Consulta, edita ou exclui registros conforme necessário

---

## Tecnologias utilizadas

| Camada | Tecnologia |
|--------|------------|
| Front-end | HTML5, CSS3, JavaScript |
| Back-end | PHP 8+ |
| Banco de dados | MySQL / MariaDB |
| Servidor local | XAMPP (Apache + MySQL) |
| Versionamento | Git + GitHub |

---

## Integrantes

| **Natan Silva Tavares** | Desenvolvimento completo (front-end, back-end, banco e documentação) |

---

## Estrutura do projeto

```
Pi-Barbearia/
├── frontend/              # Interface (CSS, JS, imagens)
│   ├── css/
│   ├── js/
│   └── images/
├── backend/               # Lógica da aplicação (PHP)
│   ├── app/               # Autenticação, banco, layout
│   ├── scripts/           # Scripts auxiliares (seed do admin)
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── clients.php
│   ├── services.php
│   └── appointments.php
├── database/
│   └── schema.sql         # Modelagem e criação das tabelas
├── index.php              # Redireciona para o login
├── ESCOPO.md              # Documento de escopo do projeto
└── README.md
```

---

## Como rodar o projeto (XAMPP)

### Pré-requisitos

- PHP 8+
- MySQL ou MariaDB
- XAMPP instalado

### Passo a passo

1. **Copie a pasta `Pi-Barbearia` para o `htdocs` do XAMPP**  
   Exemplo: `C:\xampp\htdocs\Pi-Barbearia`

2. **Inicie o XAMPP** e ligue **Apache** e **MySQL**

3. **Crie o banco de dados**  
   - Acesse `http://localhost/phpmyadmin`
   - Crie um banco chamado `barbearia`

4. **Execute o script SQL**  
   - No banco `barbearia`, abra a aba **SQL**
   - Cole o conteúdo de `database/schema.sql` e execute

5. **Crie o usuário administrador de teste**  
   No terminal, dentro da pasta do projeto:
   ```bash
   php backend/scripts/seed_admin.php
   ```

6. **Acesse no navegador**  
   ```
   http://localhost/Pi-Barbearia/
   ```
   ou diretamente:
   ```
   http://localhost/Pi-Barbearia/backend/login.php
   ```

### Credenciais de teste

| Campo | Valor |
|-------|-------|
| E-mail | `admin@tesouradeouro.com` |
| Senha | `admin123` |

> As credenciais do banco padrão do XAMPP (`root` sem senha) já estão configuradas em `backend/app/db.php`.

---

## Versionamento (Git)

O projeto utiliza Git com commits padronizados. Exemplos de prefixos:

| Prefixo | Uso |
|---------|-----|
| `feat:` | Nova funcionalidade |
| `fix:` | Correção de bug |
| `docs:` | Documentação |
| `refactor:` | Refatoração de código |
| `style:` | Ajustes visuais/CSS |

### Branches sugeridas

| Branch | Finalidade |
|--------|------------|
| `main` | Versão estável para entrega |
| `develop` | Integração de novas funcionalidades |
| `feature/nome` | Desenvolvimento de features específicas |

### Como publicar esta versão no GitHub

```bash
git add .
git commit -m "feat: reorganiza estrutura e atualiza documentação do projeto"
git push origin main
```

---

## Segurança implementada

- Senhas armazenadas com `password_hash` / `password_verify`
- Proteção CSRF nos formulários
- Rotas administrativas protegidas por `require_admin()`
- Escape de saída HTML com `htmlspecialchars`
- Consultas SQL com prepared statements (PDO)

---

## Documentação adicional

- [ESCOPO.md](ESCOPO.md) — Documento de escopo do projeto integrador

---

## Licença

Projeto acadêmico — uso educacional.
