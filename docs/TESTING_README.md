# Guia de Testes - Laravel Mini CRM

Este guia explica como executar todos os testes do Laravel Mini CRM de forma completa e sistemática.

## 🚀 Execução Rápida

### Windows (PowerShell)
```powershell
# Executar todos os testes
.\scripts\test-suite.ps1

# Executar apenas testes automatizados
.\scripts\test-suite.ps1 -SkipManualTests

# Executar apenas testes manuais
.\scripts\test-suite.ps1 -SkipAutomatedTests
```

### Linux/Mac (Bash)
```bash
# Tornar o script executável
chmod +x scripts/test-suite.sh

# Executar todos os testes
./scripts/test-suite.sh
```

## 📋 Pré-requisitos

### Software Necessário
- **PHP 8.1+** - Linguagem principal
- **Composer** - Gerenciador de dependências
- **Laravel** - Framework PHP
- **SQLite** - Banco de dados (incluído no PHP)

### Verificação de Instalação
```bash
# Verificar versões
php --version
composer --version
php artisan --version
```

## 🧪 Tipos de Testes

### 1. Testes Automatizados (PHPUnit)
- **Localização**: `tests/` directory
- **Execução**: `php artisan test`
- **Cobertura**: Autenticação, CRUD, Tratamento de Erros

### 2. Testes Manuais (API)
- **Autenticação**: Registro, Login, Logout, Perfil
- **CRUD de Contatos**: Criar, Listar, Visualizar, Atualizar, Excluir
- **Tratamento de Erros**: Validação, Autenticação, Recursos não encontrados

### 3. Testes de Performance
- **Tempo de Resposta**: Verificação de performance
- **Logs**: Análise de logs de erro
- **Banco de Dados**: Verificação de integridade

## 🔧 Configuração do Ambiente

### 1. Preparar o Banco de Dados
```bash
# Criar banco SQLite (se não existir)
touch database/database.sqlite

# Executar migrations
php artisan migrate:fresh --seed

# Verificar status
php artisan migrate:status
```

### 2. Limpar Caches
```bash
# Limpar todos os caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 3. Verificar Configurações
```bash
# Verificar configurações do banco
php artisan config:show database

# Verificar rotas disponíveis
php artisan route:list --path=api
```

## 📊 Execução de Testes Específicos

### Testes de Autenticação
```bash
# Executar apenas testes de autenticação
php artisan test --filter=AuthTest

# Testar registro manual
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Testes de Contatos
```bash
# Executar apenas testes de contatos
php artisan test --filter=ContactTest

# Testar CRUD manual
# 1. Fazer login para obter token
# 2. Criar contato
# 3. Listar contatos
# 4. Visualizar contato específico
# 5. Atualizar contato
# 6. Excluir contato
```

### Testes de Tratamento de Erros
```bash
# Executar apenas testes de erro
php artisan test --filter=ErrorHandlingTest

# Testar erros manualmente
# - Acesso sem autenticação
# - Endpoint inexistente
# - Dados inválidos
# - Recurso não encontrado
```

## 🔍 Análise de Resultados

### Verificar Logs
```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Procurar por erros específicos
grep "ERROR" storage/logs/laravel.log
grep "Exception" storage/logs/laravel.log
```

### Verificar Performance
```bash
# Medir tempo de resposta
time php artisan about

# Verificar uso de memória
php artisan tinker
# memory_get_usage(true)
```

### Verificar Banco de Dados
```bash
# Verificar integridade
php artisan tinker
# DB::select('PRAGMA integrity_check;')
```

## 🚨 Troubleshooting

### Problemas Comuns

#### 1. Erro de Banco de Dados
```bash
# Solução: Recriar banco
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh --seed
```

#### 2. Erro de Permissões
```bash
# Solução: Ajustar permissões
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

#### 3. Erro de Cache
```bash
# Solução: Limpar caches
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

#### 4. Erro de Dependências
```bash
# Solução: Reinstalar dependências
composer install --no-dev
composer dump-autoload
```

### Verificação de Ambiente
```bash
# Verificar se tudo está configurado
php artisan about

# Verificar configurações
php artisan config:show

# Verificar rotas
php artisan route:list
```

## 📈 Métricas de Qualidade

### Cobertura de Testes
- **Autenticação**: 100%
- **CRUD de Contatos**: 100%
- **Tratamento de Erros**: 100%
- **Validação**: 100%

### Performance Esperada
- **Tempo de Resposta**: < 500ms
- **Uso de Memória**: < 50MB
- **Taxa de Sucesso**: > 95%

### Segurança
- **Validação de Input**: 100%
- **Autenticação**: 100%
- **Autorização**: 100%

## 📝 Relatórios

### Gerar Relatório de Testes
```bash
# Executar testes com cobertura
php artisan test --coverage

# Gerar relatório HTML
php artisan test --coverage-html coverage/
```

### Estrutura do Relatório
```
📊 Relatório de Testes
├── Resumo Executivo
├── Testes de Autenticação
├── Testes de CRUD
├── Testes de Erro
├── Testes de Performance
├── Problemas Encontrados
└── Recomendações
```

## 🔄 Integração Contínua

### GitHub Actions (Exemplo)
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php artisan test
```

## 📚 Recursos Adicionais

### Documentação
- [Laravel Testing](https://laravel.com/docs/testing)
- [PHPUnit](https://phpunit.de/)
- [API Testing](https://laravel.com/docs/http-tests)

### Ferramentas Úteis
- **Postman**: Testar APIs manualmente
- **Insomnia**: Alternativa ao Postman
- **curl**: Testes via linha de comando

### Comandos Úteis
```bash
# Verificar status da aplicação
php artisan about

# Verificar logs
tail -f storage/logs/laravel.log

# Executar queue worker
php artisan queue:work

# Verificar jobs na fila
php artisan queue:failed
```

## 🎯 Checklist de Validação

### ✅ Antes dos Testes
- [ ] Ambiente configurado
- [ ] Banco de dados criado
- [ ] Dependências instaladas
- [ ] Caches limpos
- [ ] Migrations executadas

### ✅ Durante os Testes
- [ ] Testes automatizados passando
- [ ] APIs respondendo corretamente
- [ ] Logs sendo gerados
- [ ] Performance adequada
- [ ] Tratamento de erros funcionando

### ✅ Após os Testes
- [ ] Relatório gerado
- [ ] Problemas documentados
- [ ] Logs analisados
- [ ] Performance verificada
- [ ] Segurança validada

Este guia garante uma execução completa e sistemática de todos os testes do Laravel Mini CRM. 