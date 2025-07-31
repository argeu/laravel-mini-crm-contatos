# Sistema de Tratamento de Erros

Este documento descreve o sistema de tratamento de erros implementado no Laravel Mini CRM.

## Visão Geral

O sistema de tratamento de erros foi implementado em múltiplas camadas para garantir que todas as exceções sejam capturadas e tratadas adequadamente, fornecendo respostas consistentes e informativas para a API.

## Componentes do Sistema

### 1. Trait HandlesErrors

Localizado em `app/Http/Controllers/Traits/HandlesErrors.php`, este trait fornece métodos utilitários para tratamento de erros nos controllers.

#### Métodos Principais:

- `handleException(Throwable $exception, string $context)`: Trata exceções e retorna resposta JSON apropriada
- `successResponse($data, string $message, int $statusCode)`: Cria resposta de sucesso padronizada
- `errorResponse(string $message, int $statusCode)`: Cria resposta de erro padronizada

### 2. Middleware HandleApiExceptions

Localizado em `app/Http/Middleware/HandleApiExceptions.php`, este middleware captura exceções não tratadas que escapam dos controllers.

#### Funcionalidades:

- Captura exceções não tratadas
- Loga erros com contexto detalhado
- Retorna respostas JSON consistentes
- Mapeia tipos de exceção para códigos HTTP apropriados

### 3. Controller Base

O `app/Http/Controllers/Controller.php` foi atualizado para incluir o trait `HandlesErrors`, disponibilizando os métodos de tratamento de erro para todos os controllers.

## Tipos de Erro Tratados

### 1. ModelNotFoundException (404)
- **Causa**: Tentativa de acessar um recurso que não existe
- **Exemplo**: Buscar um contato com ID inexistente
- **Resposta**: `{"error": "Resource not found", "status": 404}`

### 2. ValidationException (422)
- **Causa**: Dados de entrada inválidos
- **Exemplo**: Email em formato inválido, campos obrigatórios vazios
- **Resposta**: `{"error": "Validation failed", "status": 422}`

### 3. QueryException (500)
- **Causa**: Erros de banco de dados
- **Exemplo**: Violação de constraints, problemas de conexão
- **Resposta**: `{"error": "Database operation failed", "status": 500}`

### 4. NotFoundHttpException (404)
- **Causa**: Endpoint não encontrado
- **Exemplo**: Rota inexistente
- **Resposta**: `{"error": "Endpoint not found", "status": 404}`

### 5. Exceções Genéricas (500)
- **Causa**: Erros inesperados
- **Resposta**: `{"error": "An unexpected error occurred", "status": 500}`

## Estrutura de Resposta

### Respostas de Sucesso
```json
{
    "data": {...},
    "message": "Operation completed successfully"
}
```

### Respostas de Erro
```json
{
    "error": "Error message",
    "status": 400,
    "timestamp": "2025-07-29T14:30:00.000000Z"
}
```

## Logging

Todos os erros são logados com informações detalhadas:

- Tipo da exceção
- Arquivo e linha onde ocorreu
- Stack trace completo
- Contexto da requisição (URL, método, usuário)
- Timestamp

## Uso nos Controllers

### Exemplo Básico com Try-Catch
```php
public function show(Contact $contact): JsonResponse
{
    try {
        return $this->successResponse(
            new ContactResource($contact),
            'Contact retrieved successfully'
        );
    } catch (Throwable $exception) {
        return $this->handleException($exception, 'Show Contact');
    }
}
```

### Exemplo com Validação
```php
public function store(StoreContactRequest $request): JsonResponse
{
    try {
        $contact = Contact::create($request->validated());
        return $this->successResponse(
            new ContactResource($contact),
            'Contact created successfully',
            201
        );
    } catch (Throwable $exception) {
        return $this->handleException($exception, 'Create Contact');
    }
}
```

### Exemplo com Operações de Banco de Dados
```php
public function destroy(Contact $contact): JsonResponse
{
    try {
        $contact->delete();
        return $this->successResponse(null, 'Contact deleted successfully');
    } catch (Throwable $exception) {
        return $this->handleException($exception, 'Delete Contact');
    }
}
```

## Vantagens do Try-Catch

1. **Legibilidade**: Código mais explícito e fácil de entender
2. **Controle**: Permite tratamento específico para diferentes tipos de exceção
3. **Flexibilidade**: Pode capturar exceções específicas quando necessário
4. **Padrão**: Segue as convenções padrão do PHP
5. **Debugging**: Mais fácil de debugar e rastrear erros

## Testes

Os testes de tratamento de erro estão localizados em `tests/Feature/ErrorHandlingTest.php` e cobrem:

- Erros 404 para recursos inexistentes
- Erros 422 para dados inválidos
- Erros 401 para requisições não autenticadas
- Erros 500 para problemas de banco de dados
- Estrutura correta das respostas de sucesso

## Configuração

### Middleware
O middleware `HandleApiExceptions` está registrado no grupo `api` no arquivo `app/Http/Kernel.php`.

### Logs
Os logs de erro são salvos em `storage/logs/laravel.log` com nível `error`.

## Boas Práticas

1. **Sempre use try-catch** para operações que podem gerar exceções
2. **Forneça contexto descritivo** no segundo parâmetro do `handleException()`
3. **Use `successResponse()` e `errorResponse()`** para manter consistência
4. **Monitore os logs** para identificar padrões de erro
5. **Teste cenários de erro** para garantir cobertura adequada
6. **Capture exceções específicas** quando necessário para tratamento customizado

## Monitoramento

Para monitorar erros em produção:

1. Configure alertas para logs de erro
2. Monitore códigos de status HTTP
3. Analise padrões de erro recorrentes
4. Implemente métricas de performance

## Troubleshooting

### Erro de Banco de Dados
Se você encontrar erros de banco de dados:

1. Verifique se o banco SQLite existe: `database/database.sqlite`
2. Execute as migrations: `php artisan migrate`
3. Verifique as configurações no `.env`

### Erros de Validação
Para debugar erros de validação:

1. Verifique as regras nos Request classes
2. Teste com dados válidos
3. Verifique os logs para detalhes específicos

### Performance
Se o tratamento de erro estiver impactando a performance:

1. Configure cache de logs
2. Use log rotation
3. Considere usar serviços de logging externos 