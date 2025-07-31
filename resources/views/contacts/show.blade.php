@extends('layouts.app')

@section('title', 'Detalhes do Contato')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Detalhes do Contato</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('contacts.edit', $contact) }}" 
                       class="px-3 py-1 text-sm font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100">
                        Editar
                    </a>
                    <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Tem certeza que deseja excluir este contato?')"
                                class="px-3 py-1 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $contact->name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $contact->email }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $contact->phone }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $contact->processed_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $contact->processed_at ? 'Processado' : 'Pendente' }}
                    </span>
                </div>
                
                @if($contact->processed_at)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Score</label>
                    <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $contact->score >= 80 ? 'bg-green-100 text-green-800' : ($contact->score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $contact->score }}
                    </span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Processado em</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $contact->processed_at->format('d/m/Y H:i:s') }}</p>
                </div>
                @endif
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Criado em</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $contact->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Atualizado em</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $contact->updated_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>
            
            @if(!$contact->processed_at)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-yellow-800">
                        Este contato ainda não foi processado. 
                        <button onclick="processContact({{ $contact->id }})" class="font-medium underline hover:no-underline">
                            Clique aqui para processar o score
                        </button>.
                    </p>
                </div>
            </div>
            @endif
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200">
            <a href="{{ route('contacts.index') }}" 
               class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Voltar para lista
            </a>
        </div>
    </div>
</div>

<script>
function processContact(contactId) {
    if (!confirm('Deseja processar o score deste contato?')) {
        return;
    }
    
    fetch(`/api/contacts/${contactId}/process-score`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Score processado com sucesso! A página será recarregada.');
            location.reload();
        } else {
            alert('Erro ao processar score');
        }
    })
    .catch(error => {
        console.error('Error processing contact:', error);
        alert('Erro ao processar score');
    });
}
</script>
@endsection 