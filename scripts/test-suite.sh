#!/bin/bash

# Script de Teste Completo - Laravel Mini CRM
# Este script executa uma bateria completa de testes

set -e  # Para o script se qualquer comando falhar

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir mensagens coloridas
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Função para verificar se um comando existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Função para verificar se o Laravel está configurado
check_laravel() {
    if [ ! -f "artisan" ]; then
        print_error "Laravel não encontrado. Execute este script no diretório raiz do projeto."
        exit 1
    fi
}

# Função para configurar o ambiente
setup_environment() {
    print_status "Configurando ambiente..."
    
    # Verificar se o banco SQLite existe
    if [ ! -f "database/database.sqlite" ]; then
        print_status "Criando banco SQLite..."
        touch database/database.sqlite
    fi
    
    # Limpar caches
    print_status "Limpando caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    # Executar migrations
    print_status "Executando migrations..."
    php artisan migrate:fresh --seed
    
    print_success "Ambiente configurado com sucesso!"
}

# Função para executar testes automatizados
run_automated_tests() {
    print_status "Executando testes automatizados..."
    
    # Executar todos os testes
    if php artisan test; then
        print_success "Todos os testes automatizados passaram!"
    else
        print_error "Alguns testes automatizados falharam!"
        return 1
    fi
}

# Função para testar autenticação
test_authentication() {
    print_status "Testando autenticação..."
    
    # Iniciar servidor em background
    php artisan serve --host=127.0.0.1 --port=8000 > /dev/null 2>&1 &
    SERVER_PID=$!
    
    # Aguardar servidor iniciar
    sleep 3
    
    # Teste de registro
    print_status "Testando registro de usuário..."
    REGISTER_RESPONSE=$(curl -s -X POST http://127.0.0.1:8000/api/auth/register \
        -H "Content-Type: application/json" \
        -d '{
            "name": "Test User",
            "email": "test@example.com",
            "password": "password123",
            "password_confirmation": "password123"
        }')
    
    if echo "$REGISTER_RESPONSE" | grep -q "token"; then
        print_success "Registro funcionando!"
        
        # Extrair token
        TOKEN=$(echo "$REGISTER_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
        
        # Teste de login
        print_status "Testando login..."
        LOGIN_RESPONSE=$(curl -s -X POST http://127.0.0.1:8000/api/auth/login \
            -H "Content-Type: application/json" \
            -d '{
                "email": "test@example.com",
                "password": "password123"
            }')
        
        if echo "$LOGIN_RESPONSE" | grep -q "token"; then
            print_success "Login funcionando!"
            
            # Teste de perfil
            print_status "Testando perfil do usuário..."
            PROFILE_RESPONSE=$(curl -s -X GET http://127.0.0.1:8000/api/auth/me \
                -H "Authorization: Bearer $TOKEN" \
                -H "Content-Type: application/json")
            
            if echo "$PROFILE_RESPONSE" | grep -q "user"; then
                print_success "Perfil do usuário funcionando!"
            else
                print_error "Perfil do usuário falhou!"
            fi
        else
            print_error "Login falhou!"
        fi
    else
        print_error "Registro falhou!"
    fi
    
    # Parar servidor
    kill $SERVER_PID 2>/dev/null || true
}

# Função para testar CRUD de contatos
test_contacts_crud() {
    print_status "Testando CRUD de contatos..."
    
    # Iniciar servidor em background
    php artisan serve --host=127.0.0.1 --port=8000 > /dev/null 2>&1 &
    SERVER_PID=$!
    
    # Aguardar servidor iniciar
    sleep 3
    
    # Fazer login para obter token
    LOGIN_RESPONSE=$(curl -s -X POST http://127.0.0.1:8000/api/auth/login \
        -H "Content-Type: application/json" \
        -d '{
            "email": "test@example.com",
            "password": "password123"
        }')
    
    TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    
    if [ -n "$TOKEN" ]; then
        # Teste de criação de contato
        print_status "Testando criação de contato..."
        CREATE_RESPONSE=$(curl -s -X POST http://127.0.0.1:8000/api/contacts \
            -H "Authorization: Bearer $TOKEN" \
            -H "Content-Type: application/json" \
            -d '{
                "name": "João Silva",
                "email": "joao@example.com",
                "phone": "11987654321",
                "address": "Rua das Flores, 123",
                "company": "Empresa ABC"
            }')
        
        if echo "$CREATE_RESPONSE" | grep -q "data"; then
            print_success "Criação de contato funcionando!"
            
            # Extrair ID do contato
            CONTACT_ID=$(echo "$CREATE_RESPONSE" | grep -o '"id":[0-9]*' | cut -d':' -f2)
            
            # Teste de listagem
            print_status "Testando listagem de contatos..."
            LIST_RESPONSE=$(curl -s -X GET http://127.0.0.1:8000/api/contacts \
                -H "Authorization: Bearer $TOKEN" \
                -H "Content-Type: application/json")
            
            if echo "$LIST_RESPONSE" | grep -q "data"; then
                print_success "Listagem de contatos funcionando!"
            else
                print_error "Listagem de contatos falhou!"
            fi
            
            # Teste de visualização
            print_status "Testando visualização de contato..."
            SHOW_RESPONSE=$(curl -s -X GET http://127.0.0.1:8000/api/contacts/$CONTACT_ID \
                -H "Authorization: Bearer $TOKEN" \
                -H "Content-Type: application/json")
            
            if echo "$SHOW_RESPONSE" | grep -q "data"; then
                print_success "Visualização de contato funcionando!"
            else
                print_error "Visualização de contato falhou!"
            fi
            
            # Teste de atualização
            print_status "Testando atualização de contato..."
            UPDATE_RESPONSE=$(curl -s -X PUT http://127.0.0.1:8000/api/contacts/$CONTACT_ID \
                -H "Authorization: Bearer $TOKEN" \
                -H "Content-Type: application/json" \
                -d '{
                    "name": "João Silva Santos",
                    "email": "joao.santos@example.com"
                }')
            
            if echo "$UPDATE_RESPONSE" | grep -q "data"; then
                print_success "Atualização de contato funcionando!"
            else
                print_error "Atualização de contato falhou!"
            fi
            
            # Teste de exclusão
            print_status "Testando exclusão de contato..."
            DELETE_RESPONSE=$(curl -s -X DELETE http://127.0.0.1:8000/api/contacts/$CONTACT_ID \
                -H "Authorization: Bearer $TOKEN" \
                -H "Content-Type: application/json")
            
            if echo "$DELETE_RESPONSE" | grep -q "message"; then
                print_success "Exclusão de contato funcionando!"
            else
                print_error "Exclusão de contato falhou!"
            fi
        else
            print_error "Criação de contato falhou!"
        fi
    else
        print_error "Não foi possível obter token de autenticação!"
    fi
    
    # Parar servidor
    kill $SERVER_PID 2>/dev/null || true
}

# Função para testar tratamento de erros
test_error_handling() {
    print_status "Testando tratamento de erros..."
    
    # Iniciar servidor em background
    php artisan serve --host=127.0.0.1 --port=8000 > /dev/null 2>&1 &
    SERVER_PID=$!
    
    # Aguardar servidor iniciar
    sleep 3
    
    # Teste de acesso sem autenticação
    print_status "Testando acesso sem autenticação..."
    UNAUTH_RESPONSE=$(curl -s -X GET http://127.0.0.1:8000/api/contacts \
        -H "Content-Type: application/json")
    
    if echo "$UNAUTH_RESPONSE" | grep -q "401"; then
        print_success "Proteção de autenticação funcionando!"
    else
        print_error "Proteção de autenticação falhou!"
    fi
    
    # Teste de endpoint inexistente
    print_status "Testando endpoint inexistente..."
    NOT_FOUND_RESPONSE=$(curl -s -X GET http://127.0.0.1:8000/api/endpoint-inexistente \
        -H "Content-Type: application/json")
    
    if echo "$NOT_FOUND_RESPONSE" | grep -q "404"; then
        print_success "Tratamento de endpoint inexistente funcionando!"
    else
        print_error "Tratamento de endpoint inexistente falhou!"
    fi
    
    # Teste de validação inválida
    print_status "Testando validação inválida..."
    VALIDATION_RESPONSE=$(curl -s -X POST http://127.0.0.1:8000/api/contacts \
        -H "Content-Type: application/json" \
        -d '{
            "name": "",
            "email": "email-invalido",
            "phone": "123"
        }')
    
    if echo "$VALIDATION_RESPONSE" | grep -q "422"; then
        print_success "Validação de dados funcionando!"
    else
        print_error "Validação de dados falhou!"
    fi
    
    # Parar servidor
    kill $SERVER_PID 2>/dev/null || true
}

# Função para verificar logs
check_logs() {
    print_status "Verificando logs..."
    
    if [ -f "storage/logs/laravel.log" ]; then
        LOG_SIZE=$(wc -l < storage/logs/laravel.log)
        print_success "Log encontrado com $LOG_SIZE linhas"
        
        # Verificar por erros
        ERROR_COUNT=$(grep -c "ERROR" storage/logs/laravel.log || echo "0")
        if [ "$ERROR_COUNT" -gt 0 ]; then
            print_warning "Encontrados $ERROR_COUNT erros no log"
        else
            print_success "Nenhum erro encontrado no log"
        fi
    else
        print_warning "Arquivo de log não encontrado"
    fi
}

# Função para verificar performance
check_performance() {
    print_status "Verificando performance..."
    
    # Verificar tempo de resposta da aplicação
    START_TIME=$(date +%s.%N)
    php artisan about > /dev/null 2>&1
    END_TIME=$(date +%s.%N)
    
    RESPONSE_TIME=$(echo "$END_TIME - $START_TIME" | bc -l 2>/dev/null || echo "0")
    print_status "Tempo de resposta: ${RESPONSE_TIME}s"
    
    if (( $(echo "$RESPONSE_TIME < 1.0" | bc -l) )); then
        print_success "Performance adequada!"
    else
        print_warning "Performance pode ser melhorada"
    fi
}

# Função principal
main() {
    echo "🧪 Iniciando Teste Completo - Laravel Mini CRM"
    echo "================================================"
    
    # Verificar se estamos no diretório correto
    check_laravel
    
    # Verificar dependências
    if ! command_exists php; then
        print_error "PHP não encontrado!"
        exit 1
    fi
    
    if ! command_exists composer; then
        print_error "Composer não encontrado!"
        exit 1
    fi
    
    # Configurar ambiente
    setup_environment
    
    # Executar testes automatizados
    run_automated_tests
    
    # Executar testes manuais
    test_authentication
    test_contacts_crud
    test_error_handling
    
    # Verificações finais
    check_logs
    check_performance
    
    echo ""
    echo "================================================"
    print_success "Teste completo finalizado!"
    print_status "Verifique os logs em storage/logs/laravel.log para mais detalhes"
}

# Executar função principal
main "$@" 