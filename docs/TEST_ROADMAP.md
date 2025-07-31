# Roteiro de Testes - Laravel Mini CRM

Este documento apresenta um roteiro completo de testes para validar todas as funcionalidades do Laravel Mini CRM.

## 📋 Visão Geral dos Testes

### Objetivos
- Validar todas as funcionalidades da API
- Verificar o tratamento de erros
- Testar autenticação e autorização
- Validar operações CRUD de contatos
- Testar processamento assíncrono
- Verificar logs e monitoramento

### Estrutura dos Testes
1. **Testes de Configuração e Ambiente**
2. **Testes de Autenticação**
3. **Testes de Contatos (CRUD)**
4. **Testes de Tratamento de Erros**
5. **Testes de Processamento Assíncrono**
6. **Testes de Performance**
7. **Testes de Segurança**

---

## 🧪 1. Testes de Configuração e Ambiente

### 1.1 Verificação do Ambiente
```bash
# Verificar se o ambiente está configurado corretamente
php artisan --version
composer --version
php --version
```

### 1.2 Configuração do Banco de Dados
```bash
# Verificar configurações do banco
php artisan config:show database

# Criar banco SQLite se não existir
touch database/database.sqlite

# Executar migrations
php artisan migrate

# Verificar status das migrations
php artisan migrate:status
```

### 1.3 Verificação de Dependências
```bash
# Verificar se todas as dependências estão instaladas
composer install --no-dev

# Verificar se o autoload está atualizado
composer dump-autoload
```

### 1.4 Configuração de Cache e Logs
```bash
# Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Verificar permissões de logs
chmod -R 775 storage/logs
chmod -R 775 storage/framework
```

---

## 🔐 2. Testes de Autenticação

### 2.1 Registro de Usuário

#### Cenário: Registro com dados válidos
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Resultado Esperado:**
- Status: 201
- Token de acesso retornado
- Dados do usuário criado

#### Cenário: Registro com email duplicado
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Maria Silva",
    "email": "joao@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Resultado Esperado:**
- Status: 422
- Erro de validação

#### Cenário: Registro com dados inválidos
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "email": "email-invalido",
    "password": "123"
  }'
```

**Resultado Esperado:**
- Status: 422
- Erros de validação detalhados

### 2.2 Login de Usuário

#### Cenário: Login com credenciais válidas
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "password123"
  }'
```

**Resultado Esperado:**
- Status: 200
- Token de acesso retornado
- Dados do usuário

#### Cenário: Login com credenciais inválidas
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "senha-errada"
  }'
```

**Resultado Esperado:**
- Status: 422
- Erro de credenciais inválidas

### 2.3 Logout

#### Cenário: Logout com token válido
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 200
- Mensagem de logout bem-sucedido

### 2.4 Perfil do Usuário

#### Cenário: Obter perfil autenticado
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 200
- Dados do usuário autenticado

#### Cenário: Acesso sem autenticação
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 401
- Erro de não autenticado

---

## 👥 3. Testes de Contatos (CRUD)

### 3.1 Listagem de Contatos

#### Cenário: Listar contatos autenticado
```bash
curl -X GET http://localhost:8000/api/contacts \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 200
- Lista paginada de contatos
- Estrutura de dados consistente

#### Cenário: Listar contatos sem autenticação
```bash
curl -X GET http://localhost:8000/api/contacts \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 401
- Erro de não autenticado

### 3.2 Criação de Contatos

#### Cenário: Criar contato com dados válidos
```bash
curl -X POST http://localhost:8000/api/contacts \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Maria Santos",
    "email": "maria@example.com",
    "phone": "11987654321",
    "address": "Rua das Flores, 123",
    "company": "Empresa ABC"
  }'
```

**Resultado Esperado:**
- Status: 201
- Contato criado com dados completos
- ID do contato retornado

#### Cenário: Criar contato com dados inválidos
```bash
curl -X POST http://localhost:8000/api/contacts \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "email": "email-invalido",
    "phone": "123"
  }'
```

**Resultado Esperado:**
- Status: 422
- Erros de validação detalhados

### 3.3 Visualização de Contato

#### Cenário: Visualizar contato existente
```bash
curl -X GET http://localhost:8000/api/contacts/{ID} \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 200
- Dados completos do contato

#### Cenário: Visualizar contato inexistente
```bash
curl -X GET http://localhost:8000/api/contacts/999 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 404
- Erro de recurso não encontrado

### 3.4 Atualização de Contatos

#### Cenário: Atualizar contato com dados válidos
```bash
curl -X PUT http://localhost:8000/api/contacts/{ID} \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Maria Santos Silva",
    "email": "maria.silva@example.com",
    "phone": "11987654321",
    "address": "Rua das Flores, 456",
    "company": "Empresa XYZ"
  }'
```

**Resultado Esperado:**
- Status: 200
- Contato atualizado com novos dados

#### Cenário: Atualizar contato inexistente
```bash
curl -X PUT http://localhost:8000/api/contacts/999 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Teste"
  }'
```

**Resultado Esperado:**
- Status: 404
- Erro de recurso não encontrado

### 3.5 Exclusão de Contatos

#### Cenário: Excluir contato existente
```bash
curl -X DELETE http://localhost:8000/api/contacts/{ID} \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 200
- Mensagem de exclusão bem-sucedida

#### Cenário: Excluir contato inexistente
```bash
curl -X DELETE http://localhost:8000/api/contacts/999 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 404
- Erro de recurso não encontrado

---

## ⚠️ 4. Testes de Tratamento de Erros

### 4.1 Erros de Validação

#### Teste: Campos obrigatórios
```bash
curl -X POST http://localhost:8000/api/contacts \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{}'
```

**Resultado Esperado:**
- Status: 422
- Erros de validação para campos obrigatórios

#### Teste: Formato de email inválido
```bash
curl -X POST http://localhost:8000/api/contacts \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Teste",
    "email": "email-invalido",
    "phone": "1234567890"
  }'
```

**Resultado Esperado:**
- Status: 422
- Erro de formato de email inválido

### 4.2 Erros de Autenticação

#### Teste: Acesso sem token
```bash
curl -X GET http://localhost:8000/api/contacts \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 401
- Erro de não autenticado

#### Teste: Token inválido
```bash
curl -X GET http://localhost:8000/api/contacts \
  -H "Authorization: Bearer token-invalido" \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 401
- Erro de token inválido

### 4.3 Erros de Recurso

#### Teste: Endpoint inexistente
```bash
curl -X GET http://localhost:8000/api/endpoint-inexistente \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 404
- Erro de endpoint não encontrado

### 4.4 Erros de Banco de Dados

#### Teste: Violação de constraint
```bash
# Tentar criar contato com user_id inexistente
curl -X POST http://localhost:8000/api/contacts \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Teste",
    "email": "teste@example.com",
    "phone": "1234567890",
    "user_id": 999
  }'
```

**Resultado Esperado:**
- Status: 500
- Erro de operação de banco de dados

---

## 🔄 5. Testes de Processamento Assíncrono

### 5.1 Processamento de Score

#### Cenário: Iniciar processamento de score
```bash
curl -X POST http://localhost:8000/api/contacts/{ID}/process-score \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json"
```

**Resultado Esperado:**
- Status: 200
- Mensagem de processamento iniciado
- ID do contato retornado

#### Verificação de Jobs na Fila
```bash
# Verificar jobs na fila
php artisan queue:work --once

# Verificar logs de processamento
tail -f storage/logs/laravel.log
```

### 5.2 Eventos e Listeners

#### Teste: Evento de score processado
```bash
# Verificar se o evento foi disparado
grep "ContactScoreProcessed" storage/logs/laravel.log
```

**Resultado Esperado:**
- Evento registrado no log
- Listener executado corretamente

---

## ⚡ 6. Testes de Performance

### 6.1 Teste de Carga

#### Cenário: Múltiplas requisições simultâneas
```bash
# Usar Apache Bench ou similar
ab -n 100 -c 10 -H "Authorization: Bearer {TOKEN}" \
  http://localhost:8000/api/contacts
```

**Métricas a Verificar:**
- Tempo de resposta médio
- Taxa de sucesso
- Uso de memória
- Tempo de processamento

### 6.2 Teste de Paginação

#### Cenário: Listar muitos contatos
```bash
# Criar 100 contatos de teste
php artisan tinker
# Contact::factory()->count(100)->create();

# Testar paginação
curl -X GET "http://localhost:8000/api/contacts?page=1&per_page=10" \
  -H "Authorization: Bearer {TOKEN}"
```

**Resultado Esperado:**
- Resposta rápida (< 500ms)
- Paginação funcionando corretamente
- Metadados de paginação presentes

---

## 🔒 7. Testes de Segurança

### 7.1 Validação de Input

#### Teste: SQL Injection
```bash
curl -X POST http://localhost:8000/api/contacts \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "'; DROP TABLE contacts; --",
    "email": "test@example.com",
    "phone": "1234567890"
  }'
```

**Resultado Esperado:**
- Status: 422 ou 500
- Nenhuma tabela deletada
- Erro tratado adequadamente

#### Teste: XSS
```bash
curl -X POST http://localhost:8000/api/contacts \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "<script>alert(\"xss\")</script>",
    "email": "test@example.com",
    "phone": "1234567890"
  }'
```

**Resultado Esperado:**
- Status: 422
- Script não executado
- Dados sanitizados

### 7.2 Autenticação e Autorização

#### Teste: Acesso a recursos de outros usuários
```bash
# Tentar acessar contato de outro usuário
curl -X GET http://localhost:8000/api/contacts/{ID_DE_OUTRO_USUARIO} \
  -H "Authorization: Bearer {TOKEN_USUARIO_1}"
```

**Resultado Esperado:**
- Status: 404 ou 403
- Acesso negado adequadamente

---

## 📊 8. Testes de Logs e Monitoramento

### 8.1 Verificação de Logs

#### Verificar logs de erro
```bash
# Verificar se logs estão sendo gerados
tail -f storage/logs/laravel.log

# Procurar por erros específicos
grep "ERROR" storage/logs/laravel.log
grep "Exception" storage/logs/laravel.log
```

### 8.2 Verificação de Métricas

#### Teste: Contadores de requisições
```bash
# Verificar se métricas estão sendo registradas
grep "Request" storage/logs/laravel.log
```

---

## 🧪 9. Testes Automatizados

### 9.1 Executar Testes Unitários
```bash
# Executar todos os testes
php artisan test

# Executar testes específicos
php artisan test --filter=AuthTest
php artisan test --filter=ContactTest
php artisan test --filter=ErrorHandlingTest
```

### 9.2 Executar Testes de Feature
```bash
# Executar testes de feature
php artisan test --testsuite=Feature

# Executar com cobertura
php artisan test --coverage
```

---

## 📋 10. Checklist de Validação

### ✅ Configuração
- [ ] Ambiente configurado corretamente
- [ ] Banco de dados criado e migrado
- [ ] Dependências instaladas
- [ ] Cache limpo

### ✅ Autenticação
- [ ] Registro de usuário funcionando
- [ ] Login funcionando
- [ ] Logout funcionando
- [ ] Perfil do usuário acessível
- [ ] Validação de credenciais

### ✅ CRUD de Contatos
- [ ] Listagem de contatos
- [ ] Criação de contatos
- [ ] Visualização de contatos
- [ ] Atualização de contatos
- [ ] Exclusão de contatos
- [ ] Paginação funcionando

### ✅ Tratamento de Erros
- [ ] Erros de validação
- [ ] Erros de autenticação
- [ ] Erros de recurso não encontrado
- [ ] Erros de banco de dados
- [ ] Logs de erro sendo gerados

### ✅ Processamento Assíncrono
- [ ] Jobs sendo processados
- [ ] Eventos sendo disparados
- [ ] Listeners sendo executados

### ✅ Performance
- [ ] Tempo de resposta adequado
- [ ] Paginação funcionando
- [ ] Sem vazamentos de memória

### ✅ Segurança
- [ ] Validação de input
- [ ] Proteção contra SQL Injection
- [ ] Proteção contra XSS
- [ ] Autorização adequada

### ✅ Logs e Monitoramento
- [ ] Logs sendo gerados
- [ ] Estrutura de logs consistente
- [ ] Informações de debug adequadas

---

## 🚀 11. Comandos de Execução Rápida

### Script de Teste Completo
```bash
#!/bin/bash

echo "🧪 Iniciando testes do Laravel Mini CRM..."

# 1. Configuração
echo "📋 Verificando configuração..."
php artisan config:clear
php artisan cache:clear
php artisan migrate:fresh

# 2. Testes automatizados
echo "🧪 Executando testes automatizados..."
php artisan test

# 3. Testes manuais
echo "👤 Testando autenticação..."
# Executar comandos curl de autenticação

echo "👥 Testando CRUD de contatos..."
# Executar comandos curl de contatos

echo "⚠️ Testando tratamento de erros..."
# Executar comandos curl de erro

echo "✅ Testes concluídos!"
```

### Comandos Úteis
```bash
# Verificar status da aplicação
php artisan about

# Verificar rotas disponíveis
php artisan route:list

# Verificar configurações
php artisan config:show

# Verificar logs em tempo real
tail -f storage/logs/laravel.log

# Executar queue worker
php artisan queue:work
```

---

## 📝 12. Relatório de Testes

### Template de Relatório
```markdown
# Relatório de Testes - Laravel Mini CRM

**Data:** [DATA]
**Versão:** [VERSÃO]
**Testador:** [NOME]

## Resumo Executivo
- Total de testes executados: [NÚMERO]
- Testes aprovados: [NÚMERO]
- Testes reprovados: [NÚMERO]
- Taxa de sucesso: [PERCENTUAL]%

## Detalhamento por Área

### Autenticação
- ✅ Registro de usuário
- ✅ Login/Logout
- ✅ Validação de credenciais

### CRUD de Contatos
- ✅ Listagem
- ✅ Criação
- ✅ Visualização
- ✅ Atualização
- ✅ Exclusão

### Tratamento de Erros
- ✅ Validação
- ✅ Autenticação
- ✅ Recursos não encontrados
- ✅ Banco de dados

### Performance
- ✅ Tempo de resposta
- ✅ Paginação
- ✅ Carga

### Segurança
- ✅ Validação de input
- ✅ Autorização
- ✅ Proteção contra ataques

## Problemas Encontrados
1. [DESCRIÇÃO DO PROBLEMA]
2. [DESCRIÇÃO DO PROBLEMA]

## Recomendações
1. [RECOMENDAÇÃO]
2. [RECOMENDAÇÃO]

## Conclusão
[CONCLUSÃO GERAL]
```

Este roteiro de testes garante uma validação completa e sistemática de todas as funcionalidades do Laravel Mini CRM. 