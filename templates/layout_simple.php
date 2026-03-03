<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Minimal PHP Framework')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Additional head content -->
    @yield('head')
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold text-gray-900">Minimal PHP Framework</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/" class="text-gray-600 hover:text-gray-900">Início</a>
                    <a href="/docs" class="text-gray-600 hover:text-gray-900">Documentação</a>
                    <a href="/examples" class="text-gray-600 hover:text-gray-900">Exemplos</a>
                    <a href="/json" class="text-gray-600 hover:text-gray-900">API</a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    @if(flash('success') || flash('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(flash('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ flash('success') }}
        </div>
        @endif
        @if(flash('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ flash('error') }}
        </div>
        @endif
    </div>
    @endif
    
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <p>&copy; 2024 Minimal PHP Framework. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
    
    <!-- Additional scripts -->
    @yield('scripts')
</body>
</html>
