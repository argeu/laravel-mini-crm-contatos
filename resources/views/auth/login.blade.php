<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Mini CRM</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f1f5f9 100%);
        }
        .login-card {
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .login-card:hover {
            box-shadow: 0 10px 32px 0 rgba(99,102,241,0.15), 0 1.5px 4px 0 rgba(0,0,0,0.04);
            transform: translateY(-2px) scale(1.01);
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center">
    <div class="min-h-screen flex items-center justify-center py-8">
        <div class="max-w-md w-full space-y-8 login-card bg-white/90 rounded-2xl shadow-xl border border-indigo-100 p-8">
            <div>
                <div class="mx-auto h-16 w-16 bg-indigo-600 rounded-full flex items-center justify-center shadow-lg mb-2 animate-bounce-slow">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h2 class="mt-4 text-center text-3xl font-extrabold text-gray-900 tracking-tight">
                    Mini CRM Contatos
                </h2>
                <p class="mt-2 text-center text-base text-gray-600">
                    Faça login para acessar o sistema
                </p>
            </div>
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-2">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-2">
                    {{ session('success') }}
                </div>
            @endif
            
            <form class="mt-6 space-y-6" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input id="email" name="email" type="email" required 
                               class="appearance-none rounded-t-xl relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 focus:z-10 sm:text-base transition-all" 
                               placeholder="Email" value="{{ old('email') }}">
                    </div>
                    <div class="mt-2">
                        <label for="password" class="sr-only">Senha</label>
                        <input id="password" name="password" type="password" required 
                               class="appearance-none rounded-b-xl relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 focus:z-10 sm:text-base transition-all" 
                               placeholder="Senha">
                    </div>
                </div>

                <div class="flex items-center justify-between mt-2">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                            Lembrar de mim
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-base font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md transition-all duration-200">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-indigo-200 group-hover:text-indigo-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        Entrar
                    </button>
                </div>
                
                <div class="text-center mt-4">
                    <a href="{{ route('register') }}" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium transition-all">
                        Não tem uma conta? Registre-se
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 