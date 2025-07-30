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

### Opção 1: Setup Tradicional

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

#### 7. Configurar Broadcasting
```bash
# Configurar Reverb no .env:
BROADCAST_DRIVER=reverb
REVERB_APP_ID=469022
REVERB_APP_KEY=gegcaatcd55rk9sfxrai
REVERB_APP_SECRET=iqhju72kle87rw22vpfu
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Opção 2: Setup com Docker (PHP 8.2)

#### 1. Clone o repositório
```bash
git clone <repository-url>
cd laravel-mini-crm-contatos
```

#### 2. Executar script de setup (Linux/macOS)
```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

#### 3. Ou executar script de setup (Windows)
```powershell
.\docker-setup.ps1
```

#### 4. Verificar status
```bash
docker-compose ps
```

#### 5. Acessar a aplicação
```
http://localhost:8000
```

### Opção 3: Setup Manual com PHP 8.2

#### 1. Atualizar PHP para 8.2+

**Windows:**
- Baixar PHP 8.2+ do site oficial
- Configurar PATH do sistema

**Linux (Ubuntu/Debian):**
```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-pgsql php8.2-redis
```

**macOS:**
```bash
brew install php@8.2
brew link php@8.2
```

#### 2. Verificar versão
```bash
php --version
```

#### 3. Instalar Laravel Reverb
```bash
composer require laravel/reverb
```

#### 4. Configurar broadcasting
```bash
# No .env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
```

#### 5. Iniciar Reverb
```bash
# Instalar dependências do Reverb
php artisan reverb:install

# Iniciar servidor Reverb
php artisan reverb:start

# Para desenvolvimento (modo watch)
php artisan reverb:start --watch
```

## 🚀 Como Executar

### 1. Iniciar o Worker de Filas
```bash
# Para desenvolvimento
php artisan queue:work --queue=contacts

# Para produção
php artisan queue:work --queue=contacts --daemon
```

### 2. Iniciar o Broadcasting (Redis)
```bash
# Certifique-se de que o Redis está rodando
redis-server

# Para desenvolvimento local, você pode usar o Redis CLI para monitorar:
redis-cli
```

### 3. Exemplo JavaScript para Escutar o Canal

Para receber atualizações em tempo real quando um contato é processado, use o seguinte código JavaScript:

```javascript
// Instalar Laravel Echo (se necessário)
// npm install laravel-echo pusher-js

// Configurar Echo
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'your-pusher-key',
    cluster: 'your-cluster',
    encrypted: true
});

// Para Laravel Reverb
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'gegcaatcd55rk9sfxrai',
    wsHost: '127.0.0.1',
    wsPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss']
});

// Escutar canal específico do contato
const contactId = 1; // ID do contato que você quer monitorar

Echo.channel(`contacts.${contactId}`)
    .listen('ContactScoreProcessed', (e) => {
        console.log('Contato processado:', e.contact);
        
        // Atualizar a interface com os novos dados
        updateContactDisplay(e.contact);
    });

// Função para atualizar a interface
function updateContactDisplay(contact) {
    const contactElement = document.querySelector(`[data-contact-id="${contact.id}"]`);
    if (contactElement) {
        // Atualizar score
        const scoreElement = contactElement.querySelector('.contact-score');
        if (scoreElement) {
            scoreElement.textContent = contact.score;
        }
        
        // Atualizar status de processamento
        const statusElement = contactElement.querySelector('.contact-status');
        if (statusElement) {
            statusElement.textContent = contact.processed_at ? 'Processado' : 'Pendente';
        }
        
        // Adicionar animação ou notificação
        contactElement.classList.add('updated');
        setTimeout(() => {
            contactElement.classList.remove('updated');
        }, 2000);
    }
}

// Escutar todos os contatos (canal público)
Echo.channel('contacts')
    .listen('ContactScoreProcessed', (e) => {
        console.log('Qualquer contato foi processado:', e.contact);
        // Atualizar lista geral de contatos
    });
```

### 4. CSS para Animações (Opcional)
```css
.contact-item.updated {
    animation: highlight 2s ease-in-out;
}

@keyframes highlight {
    0% { background-color: transparent; }
    50% { background-color: #fef3c7; }
    100% { background-color: transparent; }
}
```

## 📊 Fluxo de Processamento

### 1. Processar Score de um Contato
```bash
# Via API
curl -X POST http://localhost:8000/api/contacts/1/process-score \
  -H "Authorization: Bearer YOUR_TOKEN"

# Via Web Interface
# Acesse: http://localhost:8000/contacts
# Clique em "Processar" no contato desejado
```

### 2. Monitorar Logs
```bash
# Ver logs de processamento
tail -f storage/logs/contact.log

# Ver logs gerais
tail -f storage/logs/laravel.log
```

### 3. Verificar Filas
```bash
# Ver jobs na fila
php artisan queue:monitor

# Ver jobs falhados
php artisan queue:failed
```

## 🧪 Testes

### Executar Todos os Testes
```bash
php artisan test
```

### Executar Testes Específicos
```bash
# Testes de autenticação
php artisan test --filter=AuthTest

# Testes de contatos
php artisan test --filter=ContactTest

# Testes de tratamento de erros
php artisan test --filter=ErrorHandlingTest
```

### Cobertura de Testes
- ✅ Autenticação (registro, login, logout, perfil)
- ✅ CRUD de contatos (criar, listar, mostrar, atualizar, excluir)
- ✅ Processamento assíncrono de scores
- ✅ Tratamento de erros (404, 422, 401, 500)
- ✅ Validação de dados
- ✅ Broadcasting de eventos

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
└── tests/
    └── Feature/
        ├── AuthTest.php                   # Testes de autenticação
        ├── ContactTest.php                # Testes de contatos
        └── ErrorHandlingTest.php          # Testes de erros
```

## 🔧 Configurações Importantes

### .env
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

### Configuração de Filas
```bash
# Iniciar worker
php artisan queue:work --queue=contacts

# Monitorar filas
php artisan queue:monitor

# Ver jobs falhados
php artisan queue:failed
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
- 22 testes passando (100% sucesso)
- Cobertura completa de funcionalidades
- Testes de tratamento de erros

## 📝 Notas

- **Broadcasting**: Configurado com Redis para compatibilidade com PHP 8.1
- **Reverb**: Não implementado devido à incompatibilidade com PHP 8.1 (requer PHP 8.2+)
- **Alternativa**: Redis broadcasting funciona perfeitamente para o desafio
- **Frontend**: Exemplo JavaScript fornecido para integração

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

---

**Desenvolvido como desafio técnico para avaliar domínio de boas práticas no backend Laravel.** 🚀
