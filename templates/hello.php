<?php $title = 'Hello - Minimal PHP Framework'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 flex items-center justify-center">
    <div class="max-w-2xl mx-auto text-center px-4">
        <div class="bg-white rounded-2xl shadow-xl p-12">
            <div class="text-6xl mb-6">👋</div>
            
            <h1 class="text-4xl font-bold text-gray-900 mb-6">
                Hello, <span class="text-green-600"><?= $name ?></span>!
            </h1>
            
            <p class="text-xl text-gray-600 mb-8">
                This is a demonstration of route parameters in the Minimal PHP Framework
            </p>
            
            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                <h3 class="font-semibold text-gray-900 mb-4">How this works:</h3>
                <div class="text-left space-y-2">
                    <div class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <span class="text-gray-700">Route defined: <code class="bg-gray-200 px-2 py-1 rounded">/hello/{name}</code></span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <span class="text-gray-700">Parameter captured: <code class="bg-gray-200 px-2 py-1 rounded"><?= $name ?></code></span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <span class="text-gray-700">Template rendered with data</span>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                    🏠 Back Home
                </a>
                
                <a href="/docs" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    📚 Documentation
                </a>
            </div>
            
            <div class="mt-8 pt-8 border-t border-gray-200">
                <p class="text-gray-500 text-sm">
                    Try different names: <a href="/hello/World" class="text-green-600 hover:text-green-700">World</a>, 
                    <a href="/hello/Developer" class="text-green-600 hover:text-green-700">Developer</a>, 
                    <a href="/hello/PHP" class="text-green-600 hover:text-green-700">PHP</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
