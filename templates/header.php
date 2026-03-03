<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Minimal PHP Framework' ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom styles -->
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
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
                    <a href="/" class="text-gray-600 hover:text-gray-900 transition-colors">Início</a>
                    <a href="/docs" class="text-gray-600 hover:text-gray-900 transition-colors">Documentação</a>
                    <a href="/examples" class="text-gray-600 hover:text-gray-900 transition-colors">Exemplos</a>
                    <a href="/json" class="text-gray-600 hover:text-gray-900 transition-colors">API</a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['_flash'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <?php foreach ($_SESSION['_flash'] as $type => $messages): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="mb-4 p-4 rounded-lg <?= $type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700' ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php unset($_SESSION['_flash']); ?>
        </div>
    <?php endif; ?>
