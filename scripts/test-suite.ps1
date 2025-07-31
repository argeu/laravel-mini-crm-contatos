# Script de Teste Completo - Laravel Mini CRM (PowerShell)
# Este script executa uma bateria completa de testes no Windows

param(
    [switch]$SkipAutomatedTests,
    [switch]$SkipManualTests,
    [switch]$Verbose
)

# Função para imprimir mensagens coloridas
function Write-Status {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Blue
}

function Write-Success {
    param([string]$Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

# Função para verificar se o Laravel está configurado
function Test-LaravelSetup {
    if (-not (Test-Path "artisan")) {
        Write-Error "Laravel não encontrado. Execute este script no diretório raiz do projeto."
        exit 1
    }
}

# Função para configurar o ambiente
function Setup-Environment {
    Write-Status "Configurando ambiente..."
    
    # Verificar se o banco SQLite existe
    if (-not (Test-Path "database/database.sqlite")) {
        Write-Status "Criando banco SQLite..."
        New-Item -ItemType File -Path "database/database.sqlite" -Force | Out-Null
    }
    
    # Limpar caches
    Write-Status "Limpando caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    # Executar migrations
    Write-Status "Executando migrations..."
    php artisan migrate:fresh --seed
    
    Write-Success "Ambiente configurado com sucesso!"
}

# Função para executar testes automatizados
function Run-AutomatedTests {
    if ($SkipAutomatedTests) {
        Write-Warning "Pulando testes automatizados..."
        return
    }
    
    Write-Status "Executando testes automatizados..."
    
    try {
        $result = php artisan test
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Todos os testes automatizados passaram!"
        } else {
            Write-Error "Alguns testes automatizados falharam!"
            return $false
        }
    } catch {
        Write-Error "Erro ao executar testes automatizados: $($_.Exception.Message)"
        return $false
    }
    
    return $true
}

# Função para testar autenticação
function Test-Authentication {
    if ($SkipManualTests) {
        Write-Warning "Pulando testes manuais..."
        return
    }
    
    Write-Status "Testando autenticação..."
    
    # Iniciar servidor em background
    $serverJob = Start-Job -ScriptBlock {
        Set-Location $using:PWD
        php artisan serve --host=127.0.0.1 --port=8000
    }
    
    # Aguardar servidor iniciar
    Start-Sleep -Seconds 3
    
    try {
        # Teste de registro
        Write-Status "Testando registro de usuário..."
        $registerBody = @{
            name = "Test User"
            email = "test@example.com"
            password = "password123"
            password_confirmation = "password123"
        } | ConvertTo-Json
        
        $registerResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/register" `
            -Method POST `
            -ContentType "application/json" `
            -Body $registerBody
        
        if ($registerResponse.token) {
            Write-Success "Registro funcionando!"
            $token = $registerResponse.token
            
            # Teste de login
            Write-Status "Testando login..."
            $loginBody = @{
                email = "test@example.com"
                password = "password123"
            } | ConvertTo-Json
            
            $loginResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/login" `
                -Method POST `
                -ContentType "application/json" `
                -Body $loginBody
            
            if ($loginResponse.token) {
                Write-Success "Login funcionando!"
                
                # Teste de perfil
                Write-Status "Testando perfil do usuário..."
                $profileResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/me" `
                    -Method GET `
                    -Headers @{Authorization = "Bearer $token"}
                
                if ($profileResponse.user) {
                    Write-Success "Perfil do usuário funcionando!"
                } else {
                    Write-Error "Perfil do usuário falhou!"
                }
            } else {
                Write-Error "Login falhou!"
            }
        } else {
            Write-Error "Registro falhou!"
        }
    } catch {
        Write-Error "Erro nos testes de autenticação: $($_.Exception.Message)"
    } finally {
        # Parar servidor
        Stop-Job $serverJob -ErrorAction SilentlyContinue
        Remove-Job $serverJob -ErrorAction SilentlyContinue
    }
}

# Função para testar CRUD de contatos
function Test-ContactsCRUD {
    if ($SkipManualTests) {
        Write-Warning "Pulando testes manuais..."
        return
    }
    
    Write-Status "Testando CRUD de contatos..."
    
    # Iniciar servidor em background
    $serverJob = Start-Job -ScriptBlock {
        Set-Location $using:PWD
        php artisan serve --host=127.0.0.1 --port=8000
    }
    
    # Aguardar servidor iniciar
    Start-Sleep -Seconds 3
    
    try {
        # Fazer login para obter token
        $loginBody = @{
            email = "test@example.com"
            password = "password123"
        } | ConvertTo-Json
        
        $loginResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/login" `
            -Method POST `
            -ContentType "application/json" `
            -Body $loginBody
        
        $token = $loginResponse.token
        
        if ($token) {
            # Teste de criação de contato
            Write-Status "Testando criação de contato..."
            $contactBody = @{
                name = "João Silva"
                email = "joao@example.com"
                phone = "11987654321"
                address = "Rua das Flores, 123"
                company = "Empresa ABC"
            } | ConvertTo-Json
            
            $createResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/contacts" `
                -Method POST `
                -Headers @{Authorization = "Bearer $token"} `
                -ContentType "application/json" `
                -Body $contactBody
            
            if ($createResponse.data) {
                Write-Success "Criação de contato funcionando!"
                $contactId = $createResponse.data.id
                
                # Teste de listagem
                Write-Status "Testando listagem de contatos..."
                $listResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/contacts" `
                    -Method GET `
                    -Headers @{Authorization = "Bearer $token"}
                
                if ($listResponse.data) {
                    Write-Success "Listagem de contatos funcionando!"
                } else {
                    Write-Error "Listagem de contatos falhou!"
                }
                
                # Teste de visualização
                Write-Status "Testando visualização de contato..."
                $showResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/contacts/$contactId" `
                    -Method GET `
                    -Headers @{Authorization = "Bearer $token"}
                
                if ($showResponse.data) {
                    Write-Success "Visualização de contato funcionando!"
                } else {
                    Write-Error "Visualização de contato falhou!"
                }
                
                # Teste de atualização
                Write-Status "Testando atualização de contato..."
                $updateBody = @{
                    name = "João Silva Santos"
                    email = "joao.santos@example.com"
                } | ConvertTo-Json
                
                $updateResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/contacts/$contactId" `
                    -Method PUT `
                    -Headers @{Authorization = "Bearer $token"} `
                    -ContentType "application/json" `
                    -Body $updateBody
                
                if ($updateResponse.data) {
                    Write-Success "Atualização de contato funcionando!"
                } else {
                    Write-Error "Atualização de contato falhou!"
                }
                
                # Teste de exclusão
                Write-Status "Testando exclusão de contato..."
                $deleteResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/contacts/$contactId" `
                    -Method DELETE `
                    -Headers @{Authorization = "Bearer $token"}
                
                if ($deleteResponse.message) {
                    Write-Success "Exclusão de contato funcionando!"
                } else {
                    Write-Error "Exclusão de contato falhou!"
                }
            } else {
                Write-Error "Criação de contato falhou!"
            }
        } else {
            Write-Error "Não foi possível obter token de autenticação!"
        }
    } catch {
        Write-Error "Erro nos testes de CRUD: $($_.Exception.Message)"
    } finally {
        # Parar servidor
        Stop-Job $serverJob -ErrorAction SilentlyContinue
        Remove-Job $serverJob -ErrorAction SilentlyContinue
    }
}

# Função para testar tratamento de erros
function Test-ErrorHandling {
    if ($SkipManualTests) {
        Write-Warning "Pulando testes manuais..."
        return
    }
    
    Write-Status "Testando tratamento de erros..."
    
    # Iniciar servidor em background
    $serverJob = Start-Job -ScriptBlock {
        Set-Location $using:PWD
        php artisan serve --host=127.0.0.1 --port=8000
    }
    
    # Aguardar servidor iniciar
    Start-Sleep -Seconds 3
    
    try {
        # Teste de acesso sem autenticação
        Write-Status "Testando acesso sem autenticação..."
        try {
            $unauthResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/contacts" `
                -Method GET `
                -ContentType "application/json"
        } catch {
            if ($_.Exception.Response.StatusCode -eq 401) {
                Write-Success "Proteção de autenticação funcionando!"
            } else {
                Write-Error "Proteção de autenticação falhou!"
            }
        }
        
        # Teste de endpoint inexistente
        Write-Status "Testando endpoint inexistente..."
        try {
            $notFoundResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/endpoint-inexistente" `
                -Method GET `
                -ContentType "application/json"
        } catch {
            if ($_.Exception.Response.StatusCode -eq 404) {
                Write-Success "Tratamento de endpoint inexistente funcionando!"
            } else {
                Write-Error "Tratamento de endpoint inexistente falhou!"
            }
        }
        
        # Teste de validação inválida
        Write-Status "Testando validação inválida..."
        $invalidBody = @{
            name = ""
            email = "email-invalido"
            phone = "123"
        } | ConvertTo-Json
        
        try {
            $validationResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/contacts" `
                -Method POST `
                -ContentType "application/json" `
                -Body $invalidBody
        } catch {
            if ($_.Exception.Response.StatusCode -eq 422) {
                Write-Success "Validação de dados funcionando!"
            } else {
                Write-Error "Validação de dados falhou!"
            }
        }
    } catch {
        Write-Error "Erro nos testes de tratamento de erros: $($_.Exception.Message)"
    } finally {
        # Parar servidor
        Stop-Job $serverJob -ErrorAction SilentlyContinue
        Remove-Job $serverJob -ErrorAction SilentlyContinue
    }
}

# Função para verificar logs
function Check-Logs {
    Write-Status "Verificando logs..."
    
    if (Test-Path "storage/logs/laravel.log") {
        $logContent = Get-Content "storage/logs/laravel.log"
        $logSize = $logContent.Count
        Write-Success "Log encontrado com $logSize linhas"
        
        # Verificar por erros
        $errorCount = ($logContent | Select-String "ERROR").Count
        if ($errorCount -gt 0) {
            Write-Warning "Encontrados $errorCount erros no log"
        } else {
            Write-Success "Nenhum erro encontrado no log"
        }
    } else {
        Write-Warning "Arquivo de log não encontrado"
    }
}

# Função para verificar performance
function Check-Performance {
    Write-Status "Verificando performance..."
    
    # Verificar tempo de resposta da aplicação
    $startTime = Get-Date
    php artisan about | Out-Null
    $endTime = Get-Date
    
    $responseTime = ($endTime - $startTime).TotalSeconds
    Write-Status "Tempo de resposta: ${responseTime}s"
    
    if ($responseTime -lt 1.0) {
        Write-Success "Performance adequada!"
    } else {
        Write-Warning "Performance pode ser melhorada"
    }
}

# Função principal
function Main {
    Write-Host "🧪 Iniciando Teste Completo - Laravel Mini CRM" -ForegroundColor Cyan
    Write-Host "================================================" -ForegroundColor Cyan
    
    # Verificar se estamos no diretório correto
    Test-LaravelSetup
    
    # Verificar dependências
    if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
        Write-Error "PHP não encontrado!"
        exit 1
    }
    
    if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
        Write-Error "Composer não encontrado!"
        exit 1
    }
    
    # Configurar ambiente
    Setup-Environment
    
    # Executar testes automatizados
    $automatedTestsResult = Run-AutomatedTests
    
    # Executar testes manuais
    Test-Authentication
    Test-ContactsCRUD
    Test-ErrorHandling
    
    # Verificações finais
    Check-Logs
    Check-Performance
    
    Write-Host ""
    Write-Host "================================================" -ForegroundColor Cyan
    Write-Success "Teste completo finalizado!"
    Write-Status "Verifique os logs em storage/logs/laravel.log para mais detalhes"
}

# Executar função principal
Main 