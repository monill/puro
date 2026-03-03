<?php $title = 'Test Page'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-gray-900 mb-6">Test Page</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4">Auth Test</h2>
            
            <?php 
            $authUser = auth();
            if ($authUser && $authUser !== null): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <strong>Usuário Logado:</strong> <?= htmlspecialchars($authUser->name ?? 'Unknown') ?>
                </div>
            <?php else: ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    Nenhum usuário logado
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4">Session Data</h2>
            <pre class="bg-gray-100 p-4 rounded overflow-x-auto">
                <?php 
                session_start();
                print_r($_SESSION);
                ?>
            </pre>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold mb-4">Variables</h2>
            <pre class="bg-gray-100 p-4 rounded overflow-x-auto">
                <?php 
                echo "auth(): " . var_export(auth(), true) . "\n";
                echo "auth() === null: " . (auth() === null ? 'true' : 'false') . "\n";
                echo "guest(): " . (guest() ? 'true' : 'false') . "\n";
                ?>
            </pre>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
