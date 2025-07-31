@extends('layouts.app')

@section('title', 'Contatos')

@section('content')
<!-- Processing Overlay -->
<div id="processing-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden transition-opacity duration-300">
    <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center shadow-xl transform transition-all duration-300 scale-95 opacity-0" id="processing-modal">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-indigo-600 mx-auto mb-4"></div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Processando Contato...</h3>
        <p class="text-gray-600 mb-4">Aguarde enquanto calculamos o score do contato.</p>
        <div class="flex space-x-2 justify-center">
            <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce"></div>
            <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
            <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('warning') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Contatos</h2>
            <p class="text-gray-600">Gerencie seus contatos e processe scores</p>
        </div>
        
        <div class="flex space-x-3">
            <button onclick="location.reload()" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Atualizar
            </button>
            
            <button onclick="createContact()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Novo Contato
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
            @if(request('status') || request('score') || request('search'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Filtros Ativos
                </span>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendentes</option>
                    <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Processados</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Score</label>
                <select id="score-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    <option value="high" {{ request('score') === 'high' ? 'selected' : '' }}>Alto (80-100)</option>
                    <option value="medium" {{ request('score') === 'medium' ? 'selected' : '' }}>Médio (40-79)</option>
                    <option value="low" {{ request('score') === 'low' ? 'selected' : '' }}>Baixo (0-39)</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input type="text" id="search-input" placeholder="Nome ou email..." value="{{ request('search') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="flex items-end">
                <button onclick="clearFilters()" class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    Limpar Filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Lista de Contatos</h3>
                <div class="text-sm text-gray-500">
                    {{ $contacts->total() }} contatos encontrados
                </div>
            </div>
        </div>
        
        <!-- Mobile View (Cards) -->
        <div class="block lg:hidden">
            @forelse($contacts as $contact)
                <div class="border-b border-gray-200 p-4" data-contact-id="{{ $contact->id }}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">{{ $contact->name }}</h4>
                            <p class="text-xs text-gray-500">{{ $contact->email }}</p>
                            <p class="text-xs text-gray-500">{{ $contact->phone }}</p>
                        </div>
                        <div class="flex flex-col items-end space-y-1">
                            <span class="score-badge inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $contact->score >= 80 ? 'bg-green-100 text-green-800' : ($contact->score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $contact->score > 0 ? $contact->score : 'N/A' }}
                            </span>
                            <span class="status-badge inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $contact->processed_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $contact->processed_at ? 'Processado' : 'Pendente' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500">{{ $contact->created_at->format('d/m/Y H:i') }}</span>
                        <div class="flex space-x-2">
                            @if($contact->processed_at)
                                <button disabled class="action-button text-gray-400 p-1 rounded cursor-not-allowed" title="Contato já processado">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                            @else
                                <button onclick="processContact({{ $contact->id }})" class="action-button text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" title="Processar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                            @endif
                            <a href="{{ route('contacts.edit', $contact) }}" class="text-indigo-600 hover:text-indigo-900 p-1 rounded hover:bg-indigo-50" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <button onclick="deleteContact({{ $contact->id }})" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" title="Excluir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500">Nenhum contato encontrado</div>
            @endforelse
        </div>
        
        <!-- Desktop View (Table) -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">
                            <button onclick="sortBy('name')" class="flex items-center hover:text-gray-700">
                                Nome
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </button>
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[180px]">
                            <button onclick="sortBy('email')" class="flex items-center hover:text-gray-700">
                                Email
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </button>
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 tracking-wider min-w-[140px]">Telefone</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 tracking-wider min-w-[80px]">
                            <button onclick="sortBy('score')" class="flex items-center hover:text-gray-700">
                                Score
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </button>
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 tracking-wider min-w-[100px]">Status</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 tracking-wider min-w-[120px]">
                            <button onclick="sortBy('created_at')" class="flex items-center hover:text-gray-700">
                                Criado em
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </button>
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 tracking-wider min-w-[150px]">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="contacts-table">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-gray-50" data-contact-id="{{ $contact->id }}">
                            <td class="px-3 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $contact->name }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 truncate" title="{{ $contact->email }}">{{ $contact->email }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $contact->phone }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <span class="score-badge inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $contact->score >= 80 ? 'bg-green-100 text-green-800' : ($contact->score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $contact->score > 0 ? $contact->score : 'N/A' }}
                                </span>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <span class="status-badge inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $contact->processed_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $contact->processed_at ? 'Processado' : 'Pendente' }}
                                </span>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $contact->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @if($contact->processed_at)
                                        <button disabled class="action-button text-gray-400 p-1 rounded cursor-not-allowed" title="Contato já processado">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </button>
                                    @else
                                        <button onclick="processContact({{ $contact->id }})" class="action-button text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" title="Processar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    <a href="{{ route('contacts.edit', $contact) }}" class="text-indigo-600 hover:text-indigo-900 p-1 rounded hover:bg-indigo-50" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button onclick="deleteContact({{ $contact->id }})" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" title="Excluir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-4 text-center text-gray-500">Nenhum contato encontrado</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Mostrando {{ $contacts->firstItem() ?? 0 }} a {{ $contacts->lastItem() ?? 0 }} de {{ $contacts->total() }} contatos
                </div>
                <div class="flex space-x-2">
                    @if($contacts->hasPages())
                        @if($contacts->onFirstPage())
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-l-md">Anterior</span>
                        @else
                            <a href="{{ $contacts->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">Anterior</a>
                        @endif
                        
                        @foreach($contacts->getUrlRange(1, $contacts->lastPage()) as $page => $url)
                            @if($page == $contacts->currentPage())
                                <span class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 border border-indigo-600">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">{{ $page }}</a>
                            @endif
                        @endforeach
                        
                        @if($contacts->hasMorePages())
                            <a href="{{ $contacts->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">Próximo</a>
                        @else
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-r-md">Próximo</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>


// Process contact
function processContact(contactId) {
    if (!confirm('Tem certeza que deseja processar o score deste contato?')) {
        return;
    }
    
    // Show processing overlay
    showProcessingOverlay();
    
    // Show processing state on button
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processando...';
    button.disabled = true;
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Send request using fetch
    fetch(`/contacts/${contactId}/process-score`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            showNotification(data.message, 'success');
            // Start polling to check if contact was processed
            pollContactStatus(contactId, button, originalText);
        }
    })
    .catch(error => {
        console.error('Error processing contact:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        hideProcessingOverlay();
        showNotification('Erro ao processar contato.', 'error');
    });
}

// Show processing overlay
function showProcessingOverlay() {
    const overlay = document.getElementById('processing-overlay');
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
    // Animate the modal in
    const modal = document.getElementById('processing-modal');
    modal.classList.remove('scale-95', 'opacity-0');
    modal.classList.add('scale-100', 'opacity-100');
}

// Hide processing overlay
function hideProcessingOverlay() {
    const overlay = document.getElementById('processing-overlay');
    const modal = document.getElementById('processing-modal');
    
    // Animate the modal out
    modal.classList.remove('scale-100', 'opacity-100');
    modal.classList.add('scale-95', 'opacity-0');
    
    // Hide overlay after animation
    setTimeout(() => {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
    }, 300);
}

// Poll contact status until processed
function pollContactStatus(contactId, button, originalText) {
    let attempts = 0;
    const maxAttempts = 30;
    
    const poll = () => {
        attempts++;
        
        fetch(`/contacts/${contactId}/json`)
            .then(response => response.json())
            .then(data => {
                if (data.contact.processed_at) {
                    // Update the UI without reloading
                    updateContactStatus(contactId, data.contact);
                    showNotification('Contato processado com sucesso!', 'success');
                    button.innerHTML = originalText;
                    button.disabled = false;
                    hideProcessingOverlay();
                } else if (attempts < maxAttempts) {
                    setTimeout(poll, 1000);
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    hideProcessingOverlay();
                    showNotification('Tempo limite excedido. O processamento pode estar em andamento.', 'warning');
                }
            })
            .catch(error => {
                console.error('Error polling contact status:', error);
                if (attempts < maxAttempts) {
                    setTimeout(poll, 1000);
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    hideProcessingOverlay();
                    showNotification('Erro ao verificar status do contato.', 'error');
                }
            });
    };
    
    setTimeout(poll, 2000);
}

// Update contact status in the UI
function updateContactStatus(contactId, contactData) {
    // Update mobile view
    const mobileRow = document.querySelector(`[data-contact-id="${contactId}"]`);
    if (mobileRow) {
        // Update score
        const scoreElement = mobileRow.querySelector('.score-badge');
        if (scoreElement) {
            scoreElement.textContent = contactData.score;
            scoreElement.className = `score-badge inline-flex px-2 py-1 text-xs font-semibold rounded-full ${contactData.score >= 80 ? 'bg-green-100 text-green-800' : (contactData.score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')}`;
        }
        
        // Update status
        const statusElement = mobileRow.querySelector('.status-badge');
        if (statusElement) {
            statusElement.textContent = 'Processado';
            statusElement.className = 'status-badge inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800';
        }
        
        // Update action button
        const actionButton = mobileRow.querySelector('.action-button');
        if (actionButton) {
            actionButton.disabled = true;
            actionButton.className = 'text-gray-400 p-1 rounded cursor-not-allowed';
            actionButton.title = 'Contato já processado';
            actionButton.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>';
        }
    }
    
    // Update desktop view
    const desktopRow = document.querySelector(`tr[data-contact-id="${contactId}"]`);
    if (desktopRow) {
        // Update score
        const scoreElement = desktopRow.querySelector('.score-badge');
        if (scoreElement) {
            scoreElement.textContent = contactData.score;
            scoreElement.className = `score-badge inline-flex px-2 py-1 text-xs font-semibold rounded-full ${contactData.score >= 80 ? 'bg-green-100 text-green-800' : (contactData.score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')}`;
        }
        
        // Update status
        const statusElement = desktopRow.querySelector('.status-badge');
        if (statusElement) {
            statusElement.textContent = 'Processado';
            statusElement.className = 'status-badge inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800';
        }
        
        // Update action button
        const actionButton = desktopRow.querySelector('.action-button');
        if (actionButton) {
            actionButton.disabled = true;
            actionButton.className = 'text-gray-400 p-1 rounded cursor-not-allowed';
            actionButton.title = 'Contato já processado';
            actionButton.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>';
        }
    }
}

// Edit contact
function editContact(contactId) {
    window.location.href = `/contacts/${contactId}/edit`;
}

// Delete contact
function deleteContact(contactId) {
    if (!confirm('Tem certeza que deseja excluir este contato?')) {
        return;
    }
    
    // Create a form to submit the DELETE request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/contacts/${contactId}`;
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    // Add method override for DELETE
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    // Submit the form
    document.body.appendChild(form);
    form.submit();
}

// Create contact
function createContact() {
    window.location.href = '/contacts/create';
}

// Debounce function to limit API calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply filters
function applyFilters() {
    const statusFilter = document.getElementById('status-filter').value;
    const scoreFilter = document.getElementById('score-filter').value;
    const searchInput = document.getElementById('search-input').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    
    if (statusFilter) {
        params.append('status', statusFilter);
    }
    
    if (scoreFilter) {
        params.append('score', scoreFilter);
    }
    
    if (searchInput) {
        params.append('search', searchInput);
    }
    
    // Redirect to filtered results
    const currentUrl = window.location.pathname;
    const queryString = params.toString();
    const newUrl = queryString ? `${currentUrl}?${queryString}` : currentUrl;
    
    window.location.href = newUrl;
}

// Clear all filters
function clearFilters() {
    document.getElementById('status-filter').value = '';
    document.getElementById('score-filter').value = '';
    document.getElementById('search-input').value = '';
    
    // Redirect to base URL without parameters
    window.location.href = window.location.pathname;
}

// Auto-apply filters with debounce
const autoApplyFilters = debounce(applyFilters, 500);

// Initialize event listeners for auto-filtering
document.addEventListener('DOMContentLoaded', function() {
    // Auto-filter on select change
    const statusFilter = document.getElementById('status-filter');
    const scoreFilter = document.getElementById('score-filter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', autoApplyFilters);
    }
    
    if (scoreFilter) {
        scoreFilter.addEventListener('change', autoApplyFilters);
    }
    
    // Auto-filter on search input (with debounce)
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', autoApplyFilters);
    }
});

// Sort by column
function sortBy(column) {
    const currentUrl = new URL(window.location);
    const currentSort = currentUrl.searchParams.get('sort');
    const currentOrder = currentUrl.searchParams.get('order');
    
    // Toggle order if same column, otherwise default to asc
    let newOrder = 'asc';
    if (currentSort === column && currentOrder === 'asc') {
        newOrder = 'desc';
    }
    
    // Update URL parameters
    currentUrl.searchParams.set('sort', column);
    currentUrl.searchParams.set('order', newOrder);
    
    // Preserve other filters
    const statusFilter = document.getElementById('status-filter').value;
    const scoreFilter = document.getElementById('score-filter').value;
    const searchInput = document.getElementById('search-input').value;
    
    if (statusFilter) {
        currentUrl.searchParams.set('status', statusFilter);
    }
    if (scoreFilter) {
        currentUrl.searchParams.set('score', scoreFilter);
    }
    if (searchInput) {
        currentUrl.searchParams.set('search', searchInput);
    }
    
    window.location.href = currentUrl.toString();
}





// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}


</script>
@endsection 