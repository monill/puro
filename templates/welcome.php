<?php $title = 'Welcome - Minimal PHP Framework'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
    <div class="max-w-4xl mx-auto text-center px-4">
        <div class="bg-white rounded-2xl shadow-xl p-12">
            <h1 class="text-5xl font-bold text-gray-900 mb-6">
                Welcome to <span class="text-blue-600"><?= $framework ?></span>
            </h1>
            
            <p class="text-xl text-gray-600 mb-8">
                A minimal yet powerful PHP framework for modern web applications
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 rounded-lg p-6">
                    <div class="text-3xl mb-4">🚀</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Fast & Lightweight</h3>
                    <p class="text-gray-600 text-sm">Built for performance with minimal overhead</p>
                </div>
                
                <div class="bg-green-50 rounded-lg p-6">
                    <div class="text-3xl mb-4">🛡️</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Secure by Default</h3>
                    <p class="text-gray-600 text-sm">Built-in security features and best practices</p>
                </div>
                
                <div class="bg-purple-50 rounded-lg p-6">
                    <div class="text-3xl mb-4">🔧</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Developer Friendly</h3>
                    <p class="text-gray-600 text-sm">Clean architecture and helpful tools</p>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/docs" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                    📚 Documentation
                </a>
                
                <a href="/examples" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    💡 Examples
                </a>
                
                <a href="/json" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    🌐 API Demo
                </a>
            </div>
            
            <div class="mt-8 pt-8 border-t border-gray-200">
                <p class="text-gray-500 text-sm">
                    Version <?= $version ?> • PHP 8.4+ • MIT License
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
