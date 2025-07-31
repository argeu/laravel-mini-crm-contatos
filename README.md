# Laravel Mini CRM de Contatos

API demonstrativa em Laravel 10 que gerencia contatos com CRUD completo, processamento assíncrono via Jobs, Observers, Events & Listeners e atualização em tempo real usando broadcasting. Desenvolvido como desafio técnico para avaliar domínio de boas práticas no backend Laravel.

## 🚀 Funcionalidades

- **Autenticação** com Laravel Sanctum (JWT tokens)
- **CRUD completo** para contatos (Create, Read, Update, Delete)
- **Form Requests** para validação centralizada
- **API Resources** para serialização de respostas
- **Jobs** para processamento assíncrono de scores
- **Observers** para normalização de dados e logging
- **Events & Listeners** para logging de processamento
- **Broadcasting** em tempo real via Redis
- **Soft Deletes** para exclusão segura
- **Testes** completos para todas as funcionalidades

## 📋 Requisitos

- PHP 8.2+ (recomendado para Laravel Reverb)
- Composer
- PostgreSQL
- Redis (para filas e broadcasting)
- Laravel 10+
- Docker (opcional, para ambiente isolado)

## 🛠️ Setup

### Opção 1: Setup com Docker (Recomendado)

#### 1. Clone o repositório
```bash
git clone <repository-url>
cd laravel-mini-crm-contatos
```

#### 2. Iniciar containers Docker
```bash
docker-compose up -d
```

#### 3. Verificar status dos containers
```bash
docker-compose ps
```

#### 4. Instalar dependências PHP
```bash
docker-compose exec app composer install
```

#### 5. Configurar ambiente
```bash
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate
```

#### 6. Executar migrations
```bash
docker-compose exec app php artisan migrate
```

#### 7. Executar seeders (opcional)
```bash
docker-compose exec app php artisan db:seed
```

#### 8. Executar seeders (opcional)
```bash
docker-compose exec app php artisan db:seed
```

#### 9. Acessar a aplicação
```
http://localhost:8000
```

#### 10. Credenciais de Acesso
Após executar os seeders, você pode fazer login com as seguintes credenciais:

**Usuário Padrão:**
- **Email:** `admin@gmail.com`
- **Senha:** `password`

**Para acessar o dashboard:**
1. Acesse: `http://localhost:8000/login`
2. Use as credenciais acima
3. Após o login, você será redirecionado para o dashboard

### Opção 2: Setup Tradicional

#### 1. Clone o repositório
```bash
git clone <repository-url>
cd laravel-mini-crm-contatos
```

#### 2. Instalar dependências
```bash
composer install
```

#### 3. Configurar ambiente
```bash
cp .env.example .env
php artisan key:generate
```

#### 4. Configurar banco de dados
```bash
# Para SQLite (padrão)
touch database/database.sqlite

# Para MySQL/PostgreSQL, configure no .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel_mini_crm
# DB_USERNAME=root
# DB_PASSWORD=
```

#### 5. Executar migrations
```bash
php artisan migrate
```

#### 6. Executar seeders (opcional)
```bash
php artisan db:seed
```

#### 6. Configurar Redis (para filas e broadcasting)
```bash
# Instalar Redis
# Windows: https://redis.io/download
# Linux: sudo apt-get install redis-server
# macOS: brew install redis

# Configurar no .env:
# QUEUE_CONNECTION=redis
# BROADCAST_DRIVER=redis
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379
```

#### 7. Credenciais de Acesso
Após executar os seeders, você pode fazer login com as seguintes credenciais:

**Usuário Padrão:**
- **Email:** `admin@gmail.com`
- **Senha:** `password`

**Para acessar o dashboard:**
1. Acesse: `http://localhost:8000/login`
2. Use as credenciais acima
3. Após o login, você será redirecionado para o dashboard

## 🚀 Como Executar

### Com Docker

#### 1. Iniciar todos os serviços
```bash
docker-compose up -d
```

#### 2. Verificar status
```bash
docker-compose ps
```

#### 3. Executar comandos no container
```bash
# Executar testes
docker-compose exec app php artisan test

# Executar migrations
docker-compose exec app php artisan migrate

# Executar seeders
docker-compose exec app php artisan db:seed

# Acessar shell do container
docker-compose exec app bash

# Ver logs
docker-compose logs app
docker-compose logs queue
docker-compose logs nginx
```

#### 4. Iniciar Worker de Filas (Docker)
```bash
# Iniciar worker em background
docker-compose exec queue php artisan queue:work --queue=contacts --daemon

# Ou executar em modo foreground
docker-compose exec queue php artisan queue:work --queue=contacts
```

#### 5. Monitorar filas
```bash
# Ver jobs na fila
docker-compose exec app php artisan queue:monitor

# Ver jobs falhados
docker-compose exec app php artisan queue:failed

# Limpar jobs falhados
docker-compose exec app php artisan queue:flush
```

### Sem Docker

#### 1. Iniciar o Worker de Filas
```bash
# Para desenvolvimento
php artisan queue:work --queue=contacts

# Para produção
php artisan queue:work --queue=contacts --daemon
```

#### 2. Iniciar o Broadcasting (Redis)
```bash
# Certifique-se de que o Redis está rodando
redis-server

# Para desenvolvimento local, você pode usar o Redis CLI para monitorar:
redis-cli
```

## 🧪 Testes

### Executar Todos os Testes (Docker)
```bash
docker-compose exec app php artisan test
```

### Executar Todos os Testes (Local)
```bash
php artisan test
```

### Executar Testes Específicos
```bash
# Testes de autenticação
docker-compose exec app php artisan test --filter=AuthTest

# Testes de contatos
docker-compose exec app php artisan test --filter=ContactTest

# Testes de tratamento de erros
docker-compose exec app php artisan test --filter=ErrorHandlingTest
```

### Cobertura de Testes
- ✅ Autenticação (registro, login, logout, perfil)
- ✅ CRUD de contatos (criar, listar, mostrar, atualizar, excluir)
- ✅ Processamento assíncrono de scores
- ✅ Tratamento de erros (404, 422, 401, 500)
- ✅ Validação de dados
- ✅ Broadcasting de eventos

## 📊 Fluxo de Processamento

### 1. Processar Score de um Contato
```bash
# Via API (Docker)
curl -X POST http://localhost:8000/api/contacts/1/process-score \
  -H "Authorization: Bearer YOUR_TOKEN"

# Via Web Interface
# Acesse: http://localhost:8000/contacts
# Clique em "Processar" no contato desejado
```

### 2. Monitorar Logs (Docker)
```bash
# Ver logs de processamento
docker-compose exec app tail -f storage/logs/contact.log

# Ver logs gerais
docker-compose exec app tail -f storage/logs/laravel.log

# Ver logs do container
docker-compose logs -f app
```

### 3. Verificar Filas (Docker)
```bash
# Ver jobs na fila
docker-compose exec app php artisan queue:monitor

# Ver jobs falhados
docker-compose exec app php artisan queue:failed

# Retry jobs falhados
docker-compose exec app php artisan queue:retry all
```

## 🔧 Comandos Docker Úteis

### Gerenciamento de Containers
```bash
# Iniciar todos os serviços
docker-compose up -d

# Parar todos os serviços
docker-compose down

# Reiniciar serviços
docker-compose restart

# Ver logs em tempo real
docker-compose logs -f

# Ver logs de um serviço específico
docker-compose logs -f app
docker-compose logs -f queue
docker-compose logs -f nginx
```

### Executar Comandos
```bash
# Acessar shell do container app
docker-compose exec app bash

# Executar artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan test
docker-compose exec app php artisan queue:work

# Executar composer
docker-compose exec app composer install
docker-compose exec app composer update
```

### Debugging
```bash
# Ver status dos containers
docker-compose ps

# Ver uso de recursos
docker stats

# Limpar containers não utilizados
docker system prune

# Rebuild containers
docker-compose build --no-cache
```

## 📁 Estrutura do Projeto

```
laravel-mini-crm-contatos/
├── app/
│   ├── Events/
│   │   └── ContactScoreProcessed.php      # Evento de score processado
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php         # API de autenticação
│   │   │   ├── ContactController.php      # API de contatos
│   │   │   └── Web/                       # Controllers web
│   │   ├── Requests/
│   │   │   ├── LoginRequest.php           # Validação de login
│   │   │   ├── RegisterRequest.php        # Validação de registro
│   │   │   ├── StoreContactRequest.php    # Validação de criação
│   │   │   └── UpdateContactRequest.php   # Validação de atualização
│   │   └── Resources/
│   │       ├── ContactResource.php        # Serialização de contato
│   │       └── ContactCollection.php      # Serialização de lista
│   ├── Jobs/
│   │   └── ProcessContactScore.php        # Job de processamento
│   ├── Listeners/
│   │   └── LogContactScoreProcessed.php   # Listener para logging
│   ├── Models/
│   │   ├── Contact.php                    # Modelo de contato
│   │   └── User.php                       # Modelo de usuário
│   └── Observers/
│       └── ContactObserver.php            # Observer para contatos
├── database/
│   ├── migrations/
│   │   └── create_contacts_table.php      # Migração de contatos
│   └── seeders/
│       └── DatabaseSeeder.php             # Seeder de dados
├── routes/
│   ├── api.php                            # Rotas da API
│   └── web.php                            # Rotas web
├── tests/
│   └── Feature/
│       ├── AuthTest.php                   # Testes de autenticação
│       ├── ContactTest.php                # Testes de contatos
│       └── ErrorHandlingTest.php          # Testes de erros
├── docker/
│   ├── nginx/
│   │   └── conf.d/
│   │       └── app.conf                   # Configuração Nginx
│   └── php/
│       └── local.ini                      # Configuração PHP
├── docker-compose.yml                     # Configuração Docker
└── Dockerfile                             # Dockerfile da aplicação
```

## 🔧 Configurações Importantes

### .env (Docker)
```env
# Database
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel_mini_crm
DB_USERNAME=postgres
DB_PASSWORD=secret

# Queue
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

# Broadcasting
BROADCAST_DRIVER=redis

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### .env (Local)
```env
# Database
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Queue
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Broadcasting
BROADCAST_DRIVER=redis

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## 🎯 Critérios de Avaliação

| ✅ | Critério | Status |
|---|---|---|
| ✅ | Uso correto de **Form Requests** e **API Resources** | Implementado |
| ✅ | Emprego adequado de **Jobs, Events, Listeners e Observers** | Implementado |
| ✅ | **Broadcasting** funcionando via Redis | Implementado |
| ✅ | Estrutura e organização do código | Implementado |
| ✅ | Qualidade dos **testes** e cobertura do fluxo principal | Implementado |
| ✅ | Clareza da documentação e **facilidade de setup** | Implementado |

## 🚀 Funcionalidades Implementadas

### ✅ CRUD Completo
- Criar, listar, mostrar, atualizar e excluir contatos
- Validação centralizada com Form Requests
- Serialização com API Resources
- Soft deletes para exclusão segura

### ✅ Processamento Assíncrono
- Jobs na fila `contacts`
- Processamento com `sleep(2)` para simular carga
- Score aleatório entre 0-100
- Atualização de `processed_at`

### ✅ Events & Listeners
- Evento `ContactScoreProcessed`
- Listener `LogContactScoreProcessed`
- Logging em `storage/logs/contact.log`

### ✅ Broadcasting
- Canal público `contacts.{id}`
- Atualização em tempo real
- Exemplo JavaScript para frontend

### ✅ Observers
- `ContactObserver` para normalização de telefone
- Logging de criação de contatos

### ✅ Autenticação
- Laravel Sanctum para API
- Sistema de login/registro web
- Proteção de rotas

### ✅ Testes
- 18 testes passando (100% sucesso)
- Cobertura completa de funcionalidades
- Testes de tratamento de erros

## 📝 Notas

- **Docker**: Ambiente completo com PHP 8.2, PostgreSQL, Redis e Nginx
- **Broadcasting**: Configurado com Redis para compatibilidade
- **Testes**: Todos os testes passando com sucesso
- **Frontend**: Exemplo JavaScript fornecido para integração

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

---

**Desenvolvido como desafio técnico para avaliar domínio de boas práticas no backend Laravel.** 🚀
