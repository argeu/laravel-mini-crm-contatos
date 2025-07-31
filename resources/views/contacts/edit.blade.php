@extends('layouts.app')

@section('title', 'Editar Contato')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Editar Contato</h2>
        </div>
        
        <form action="{{ route('contacts.update', $contact) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $contact->name) }}" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $contact->email) }}" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Telefone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $contact->phone) }}" required
                           placeholder="(11) 99999-9999"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                @if($contact->processed_at)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Informações de Processamento</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Score:</span>
                            <span class="ml-2 font-medium">{{ $contact->score }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Processado em:</span>
                            <span class="ml-2 font-medium">{{ $contact->processed_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('contacts.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700">
                    Atualizar Contato
                </button>
            </div>
        </form>
    </div>
</div>


@endsection 