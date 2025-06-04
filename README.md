# Projeto FullStack - Gerenciador de Tarefas (FluentPDO & Medoo)

Este projeto é um gerenciador de tarefas fullstack, com backend em PHP, frontend em HTML/JS/CSS, autenticação de usuários, painel administrativo, API RESTful e integração com dois ORMs: **FluentPDO** e **Medoo**. O ambiente é totalmente dockerizado para facilitar o desenvolvimento e testes.

---

## Sumário

- [Funcionalidades](#funcionalidades)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Instruções de Instalação e Configuração](#instruções-de-instalação-e-configuração)
  - [Configuração dos domínios locais](#configuração-dos-domínios-locais)
  - [Certificados SSL](#certificados-ssl)
- [Como rodar o projeto (Docker)](#como-rodar-o-projeto-docker)
- [Seeders e Banco de Dados](#seeders-e-banco-de-dados)
- [Acesso ao Sistema](#acesso-ao-sistema)
- [Frontend](#frontend)
- [APIs](#apis)
- [Administração](#administração)
- [Customização e Dicas](#customização-e-dicas)
- [Problemas comuns](#problemas-comuns)
- [Decisões de Arquitetura](#decisões-de-arquitetura)
- [Medidas de Segurança Implementadas](#medidas-de-segurança-implementadas)

---

## Funcionalidades

- Cadastro, login e recuperação de senha de usuários (por identificação e combinação de dados)
- Sessão segura com tokens e expiração
- CRUD de tarefas com categorias, prioridade, status e datas
- Filtros e ordenação de tarefas
- Painel administrativo para gerenciamento de usuários
- API RESTful para tarefas e usuários
- Rate limiting no login
- Sistema de seed para popular o banco automaticamente
- Suporte a múltiplos ORMs (FluentPDO e Medoo)
- Frontend responsivo com Bootstrap e DataTables

---

## Tecnologias Utilizadas

- **PHP 8+**
- **MySQL 8**
- **Nginx**
- **Docker & Docker Compose**
- **FluentPDO** (ORM)
- **Medoo** (ORM)
- **Bootstrap 5**
- **jQuery**
- **DataTables**
- **phpMyAdmin** (opcional)

---

## Estrutura do Projeto

```
/
├── docker/                 # Configurações Docker, scripts e SQL
│   ├── mysql/              # Scripts de criação de banco/tabelas
│   └── scripts/            # Seeders, wait-for-it.sh
├── fluentpdo/              # App usando FluentPDO
│   ├── app/
│   └── public/
├── medoo/                  # App usando Medoo
│   ├── app/
│   └── public/
├── docker-compose.yml      # Orquestração dos containers
└── README.md
```

---

## Instruções de Instalação e Configuração

### 1. Clone o repositório

```sh
git clone git@github.com:henriquesalesmg/fullstack_php.git
cd project
```

### 2. Configuração dos domínios locais

Para acessar os sistemas via HTTPS e domínios amigáveis, adicione as seguintes entradas ao seu arquivo `hosts`:

- **Windows:**  
  Edite o arquivo:  
  `C:\Windows\System32\drivers\etc\hosts`

- **Linux/Mac:**  
  Edite o arquivo:  
  `/etc/hosts`

Adicione as linhas abaixo ao final do arquivo:

```
127.0.0.1 projetofluentpdo.test
127.0.0.1 projetomedoo.test
```

Salve o arquivo.  
> **Obs:** No Windows, pode ser necessário abrir o editor como administrador.

### 3. Certificados SSL

O projeto já inclui certificados autoassinados para os domínios locais, localizados em `docker/nginx/certs/`.  
Esses certificados permitem acesso HTTPS aos domínios `https://projetofluentpdo.test` e `https://projetomedoo.test`.

- Se quiser gerar novos certificados, utilize o script ou comando OpenSSL:
  ```sh
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout projetofluentpdo.test.key -out projetofluentpdo.test.crt \
    -subj "/CN=projetofluentpdo.test"
  ```
  Repita para `projetomedoo.test` se necessário.

- **Importante:**  
  Ao acessar pelo navegador, aceite o certificado como confiável (pode aparecer um aviso de segurança por ser autoassinado).

---

## Como rodar o projeto (Docker)

1. **Suba os containers:**
   ```sh
   docker-compose up --build
   ```

2. **Aguarde a inicialização do MySQL e dos seeders.**
   - O seed é executado automaticamente pelos containers `app-seed-fluentpdo` e `app-seed-medoo`.
   - Veja logs com:
     ```sh
     docker logs app-seed-fluentpdo
     docker logs app-seed-medoo
     ```

3. **Acesse no navegador:**
   - **FluentPDO:** https://projetofluentpdo.test
   - **Medoo:** https://projetomedoo.test
   - **phpMyAdmin:** http://localhost:8080 (usuário: root, senha: root)

---

## Seeders e Banco de Dados

- O banco é populado automaticamente ao subir os containers.
- Usuários criados por padrão:
  - **Admin:**  
    - Email: `admin@admin.com`  
    - Senha: `senha123`
  - **Usuário comum:**  
    - Email: `user@teste.com`  
    - Senha: `senha123`
- Categorias e tarefas de exemplo também são criadas.

---

## Acesso ao Sistema

- **Login:** `/login`
- **Cadastro:** `/register`
- **Recuperação de senha:** `/forgot-password`
- **Logout:** `/logout`
- O sistema de login e recuperação foi feito via dados de identificação e combinação (e-mail e senha).

---

## Frontend

O frontend é responsivo e utiliza:

- **Bootstrap 5**: Layout, modais, botões e responsividade.
- **jQuery**: Manipulação de DOM, AJAX e integração com APIs.
- **DataTables**: Listagem dinâmica e ordenação de tarefas.
- **JS customizado**: Scripts em `/assets/js/` para interações, filtros, validações e integração com a API.

---

## APIs

- **Medoo:** `/medoo/app/api/`
- **FluentPDO:** `/fluentpdo/app/api/`

### Exemplos de endpoints:
- `/api/user?action=login` (POST)
- `/api/tasks?action=list` (GET)
- `/api/tasks?action=create` (POST)
- `/api/admin?action=list` (GET, admin)

#### Documentação da API

- Todas as rotas aceitam e retornam JSON.
- Autenticação via sessão PHP.
- Endpoints de admin requerem usuário com role `admin`.
- Consulte os arquivos em `app/api/` para detalhes de parâmetros e respostas.

---

## Administração

- Usuários com `role = admin` têm acesso ao painel administrativo e à API de admin.
- Não é permitido remover o próprio usuário logado.
- O painel permite criar, editar e excluir usuários.

---

## Customização e Dicas

- **Configuração do banco:**  
  Veja e ajuste os arquivos em `docker/mysql` e `app/config/db.php` conforme necessário.
- **Seeders:**  
  O script `docker/scripts/seed.php` pode ser editado para adicionar mais dados de exemplo.
- **Cache:**  
  O sistema usa cache simples para categorias (`helpers/cache.php`).
- **Segurança:**  
  CSRF token é gerado e validado nos formulários.
- **Rate limiting:**  
  Implementado no login para evitar brute force.

---

## Problemas comuns

- **Seed não roda:**  
  Verifique se o volume do container de seed está como `- ./:/var/www/html` e se o script está no caminho correto.
- **Métodos do ORM:**  
  Use sempre os métodos do Medoo (`select`, `get`, `insert`, `delete`) e não métodos do FluentPDO (`from`, `deleteFrom`) no projeto Medoo.
- **Banco vazio:**  
  Aguarde o container MySQL estar saudável antes do seed rodar. Veja logs dos containers de seed.
- **Permissões:**  
  Se der erro de permissão, rode `chmod -R 777 docker/mysql` (apenas para desenvolvimento).
- **Certificado não confiável:**  
  Aceite o certificado autoassinado no navegador para acessar via HTTPS.

---

## Decisões de Arquitetura

- **Separação por ORM:**  
  O projeto possui duas aplicações independentes (FluentPDO e Medoo) para facilitar testes e comparações.
- **APIs RESTful:**  
  Toda a comunicação entre frontend e backend é feita via API, facilitando manutenção e integração.
- **Dockerização:**  
  Todo o ambiente é isolado em containers, garantindo reprodutibilidade e facilidade de setup.
- **Seed automatizado:**  
  O banco é populado automaticamente ao subir o ambiente, facilitando testes e onboarding.
- **Frontend desacoplado:**  
  O frontend consome a API e pode ser facilmente substituído ou evoluído.

---

## Medidas de Segurança Implementadas

- **Autenticação por sessão:**  
  Sessão PHP com token e expiração.
- **Validação de CSRF:**  
  Todos os formulários sensíveis possuem token CSRF.
- **Rate limiting:**  
  Limite de tentativas de login por IP para evitar brute force.
- **Hash de senha:**  
  Senhas armazenadas com `password_hash` e verificadas com `password_verify`.
- **Validação de entrada:**  
  Todos os dados recebidos via POST/GET são validados e filtrados.
- **Proteção contra remoção do próprio admin:**  
  Não é possível excluir o usuário logado via painel admin.
- **HTTPS:**  
  Certificados SSL autoassinados para desenvolvimento, garantindo comunicação criptografada.

---
