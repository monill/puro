<?php $title = 'Documentação - Minimal PHP Framework'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Documentação do Framework</h1>
            <p class="text-lg text-gray-600">Guia completo de uso do Minimal PHP Framework</p>
        </div>

        <!-- Índice -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4">Índice</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <ul class="space-y-2">
                    <li><a href="#instalacao" class="text-blue-600 hover:text-blue-800">📦 Instalação</a></li>
                    <li><a href="#database" class="text-blue-600 hover:text-blue-800">🗄️ Database Layer</a></li>
                    <li><a href="#views" class="text-blue-600 hover:text-blue-800">🎨 View System</a></li>
                    <li><a href="#auth" class="text-blue-600 hover:text-blue-800">🔐 Authentication</a></li>
                    <li><a href="#cache" class="text-blue-600 hover:text-blue-800">⚡ Cache System</a></li>
                    <li><a href="#middleware" class="text-blue-600 hover:text-blue-800">🔧 Middleware</a></li>
                </ul>
                <ul class="space-y-2">
                    <li><a href="#security" class="text-blue-600 hover:text-blue-800">🛡️ Security</a></li>
                    <li><a href="#validation" class="text-blue-600 hover:text-blue-800">✅ Validation</a></li>
                    <li><a href="#helpers" class="text-blue-600 hover:text-blue-800">🛠️ Helpers</a></li>
                    <li><a href="#cli" class="text-blue-600 hover:text-blue-800">💻 CLI Commands</a></li>
                    <li><a href="#debug" class="text-blue-600 hover:text-blue-800">🐛 Debug Tools</a></li>
                    <li><a href="#deploy" class="text-blue-600 hover:text-blue-800">🚀 Deploy</a></li>
                </ul>
            </div>
        </div>

        <!-- Instalação -->
        <section id="instalacao" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">📦 Instalação e Configuração</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">1. Requisitos</h3>
                <ul class="list-disc list-inside space-y-2 text-gray-700">
                    <li>PHP 8.4+</li>
                    <li>Servidor web (Apache, Nginx, ou PHP built-in)</li>
                    <li>Banco de dados (MySQL/MariaDB, PostgreSQL, SQLite)</li>
                    <li>(Opcional) Redis ou Memcached para cache</li>
                </ul>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">2. Configuração</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code># Copiar arquivo de configuração
cp config.sample.php config.php

# Configurar banco de dados em config.php
'db' => [
    'host' => 'localhost',
    'name' => 'minimal_framework',
    'user' => 'root',
    'pass' => 'senha',
],</code></pre>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">3. Execução</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code># Para desenvolvimento
php cli serve

# Para produção
# Configure seu servidor para apontar para public/</code></pre>
                </div>
            </div>
        </section>

        <!-- Database Layer -->
        <section id="database" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🗄️ Database Layer</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Query Builder</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>use App\Core\Database\Connection;
use App\Core\Database\QueryBuilder;

// Obter conexão
$db = Connection::getInstance();

// Query Builder
$query = new QueryBuilder($db);

// SELECT
$users = $query->table('users')
    ->where('active', '=', 1)
    ->orderBy('created_at', 'DESC')
    ->get();

// INSERT
$query->table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => date('Y-m-d H:i:s')
]);

// UPDATE
$query->table('users')
    ->where('id', '=', 1)
    ->update(['name' => 'Jane Doe']);

// DELETE
$query->table('users')
    ->where('id', '=', 1)
    ->delete();</code></pre>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Migrações e Seeders</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code># Criar migração
php cli make:migration create_users_table

# Executar migrações
php cli migrate

# Criar seeder
php cli make:seeder UsersTableSeeder

# Executar seeders
php cli seed</code></pre>
                </div>
            </div>
        </section>

        <!-- View System -->
        <section id="views" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🎨 View System</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Criando Views</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>&lt;?php $title = 'Welcome Page'; ?&gt;
&lt;?php include __DIR__ . '/header.php'; ?&gt;

&lt;div class="container"&gt;
    &lt;h1&gt;Welcome to Minimal Framework!&lt;/h1&gt;
    &lt;p&gt;Hello, &lt;?= $name ?&gt;!&lt;/p&gt;
    
    &lt;?php if (auth() && auth()->check()): ?&gt;
        &lt;p&gt;Welcome back, &lt;?= auth()->user()->name ?&gt;!&lt;/p&gt;
    &lt;?php else: ?&gt;
        &lt;p&gt;Please &lt;a href="/login"&gt;login&lt;/a&gt;&lt;/p&gt;
    &lt;?php endif; ?&gt;
&lt;/div&gt;

&lt;?php include __DIR__ . '/footer.php'; ?&gt;</code></pre>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Renderizando Views</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>// Em controllers
use App\Core\View;

class HomeController {
    public function index($request) {
        return View::make('welcome', ['name' => 'World']);
    }
}

// Em rotas
Route::get('/', function($request) {
    return view('welcome', ['name' => 'World']);
});</code></pre>
                </div>
            </div>
        </section>

        <!-- Authentication -->
        <section id="auth" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🔐 Authentication</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Configuração</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>// config.php
'auth' => [
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
            'model' => 'App\\Models\\User',
        ],
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
            'model' => 'App\\Models\\Customer',
        ],
    ],
],</code></pre>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Usando Authentication</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>// Login
if (AuthManager::attempt(['email' => $email, 'password' => $password])) {
    return redirect('/dashboard');
}

// Verificar usuário
if (AuthManager::check()) {
    $user = AuthManager::user();
    echo "Welcome, " . $user->name;
}

// Logout
AuthManager::logout();

// Guard específico
if (AuthManager::guard('customer')->check()) {
    $customer = AuthManager::guard('customer')->user();
}</code></pre>
                </div>
            </div>
        </section>

        <!-- Cache System -->
        <section id="cache" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">⚡ Cache System</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Configuração</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>// config.php
'cache' => [
    'default' => 'file',
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => __DIR__ . '/storage/cache',
        ],
        'redis' => [
            'driver' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379,
        ],
    ],
],</code></pre>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Usando Cache</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>use App\Core\Cache;

// Armazenar
Cache::put('key', 'value', 3600); // 1 hora

// Recuperar
$value = Cache::get('key', 'default');

// Verificar existência
if (Cache::has('key')) {
    // ...
}

// Remover
Cache::forget('key');

// Remember (armazena se não existir)
$value = Cache::remember('key', 3600, function() {
    return expensiveOperation();
});

// Query Cache
$query = new QueryBuilder($db);
$users = $query->cache(3600)->table('users')->get();</code></pre>
                </div>
            </div>
        </section>

        <!-- Middleware -->
        <section id="middleware" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🔧 Middleware</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Criando e Aplicando Middleware</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>// app/Middlewares/AuthMiddleware.php
namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;

class AuthMiddleware extends Middleware {
    public function handle(Request $request, callable $next): mixed {
        if (!AuthManager::check()) {
            return redirect('/login');
        }
        return $next($request);
    }
}

// Aplicar em rotas
Route::get('/dashboard', function($request) {
    return view('dashboard');
})->middleware('auth');</code></pre>
                </div>
            </div>
        </section>

        <!-- Security -->
        <section id="security" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🛡️ Security</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Input Sanitization</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>use App\Core\Security\InputSanitizer;

// Limpar input
$cleanInput = InputSanitizer::clean($userInput);

// Validar
if (InputSanitizer::validateEmail($email)) { }
if (InputSanitizer::validateCpf($cpf)) { }

// Helpers
$email = sanitize($userInput);
$escaped = escape($htmlContent);</code></pre>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">CORS e Security Headers</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>use App\Core\Security\CorsMiddleware;
use App\Core\Security\SecurityHeaders;

// CORS
CorsMiddleware::configure([
    'allowed_origins' => ['https://yourdomain.com'],
    'allowed_methods' => ['GET', 'POST'],
]);

// Security Headers
SecurityHeaders::configure([
    'x_frame_options' => 'DENY',
    'strict_transport_security' => 'max-age=31536000',
]);</code></pre>
                </div>
            </div>
        </section>

        <!-- Validation -->
        <section id="validation" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">✅ Validation</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Validação de Inputs</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>use App\Core\RequestValidator;

// Validar request
$validator = RequestValidator::make($request->all(), [
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed',
    'age' => 'required|integer|min:18',
]);

if ($validator->fails()) {
    return redirect()->back()
        ->with('errors', $validator->errors())
        ->withInput();
}

// Em controllers
class UserController {
    public function store($request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);
        
        User::create($validated);
        
        return redirect('/users');
    }
}</code></pre>
                </div>
            </div>
        </section>

        <!-- Helpers -->
        <section id="helpers" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🛠️ Helpers Globais</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">Debug & URL</h3>
                    <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm">
                        <pre><code>dd($variable); // Dump and die
dump($variable); // Apenas dump

url('/path'); // http://localhost/path
route('users.index'); // URL da rota
asset('css/app.css'); // URL de asset
mix('js/app.js'); // URL com versionamento</code></pre>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">Form & Auth</h3>
                    <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm">
                        <pre><code>old('name'); // Input antigo
error('name'); // Erro de validação
csrf(); // Token CSRF
method_field('PUT'); // Campo método HTTP

auth(); // Usuário autenticado
guest(); // Verifica se é visitante
user(); // Usuário atual
can('edit-post'); // Verifica permissão</code></pre>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">String & Array</h3>
                    <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm">
                        <pre><code>str_limit('long text', 50); // Limita string
str_slug('Hello World'); // hello-world
str_random(16); // String aleatória
str_uuid(); // UUID v4

array_get($array, 'key.nested'); // Valor com notação de ponto
array_set($array, 'key.nested', 'value'); // Define valor
array_has($array, 'key.nested'); // Verifica existência</code></pre>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">Validation & Response</h3>
                    <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm">
                        <pre><code>is_email($email); // Valida email
is_url($url); // Valida URL
is_cpf($cpf); // Valida CPF
is_cnpj($cnpj); // Valida CNPJ
sanitize($input); // Limpa input
escape($html); // Escapa HTML

redirect('/path'); // Redireciona
back(); // Volta para página anterior
json($data); // Response JSON
view('template', $data); // Renderiza view</code></pre>
                    </div>
                </div>
            </div>
        </section>

        <!-- CLI Commands -->
        <section id="cli" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">💻 CLI Commands</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Comandos Disponíveis</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code># Servidor de desenvolvimento
php cli serve

# Migrações
php cli migrate
php cli migrate:rollback
php cli migrate:fresh

# Seeders
php cli seed
php cli seed:users

# Gerar arquivos
php cli make:controller HomeController
php cli make:model User
php cli make:migration create_users_table
php cli make:seeder UsersTableSeeder
php cli make:middleware AuthMiddleware

# Cache
php cli cache:clear
php cli config:clear
php cli view:clear

# Logs
php cli logs:clear
php cli logs:tail</code></pre>
                </div>
            </div>
        </section>

        <!-- Debug Tools -->
        <section id="debug" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🐛 Debug Tools</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Debug Toolbar</h3>
                <p class="text-gray-700 mb-4">Toolbar interativa que exibe informações detalhadas sobre:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700">
                    <li>Queries SQL com tempo de execução</li>
                    <li>Uso de memória e pico</li>
                    <li>Dados da requisição (headers, parameters)</li>
                    <li>Session data</li>
                    <li>Cache hits/misses</li>
                    <li>Logs recentes</li>
                    <li>Timers de performance</li>
                </ul>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Error Handling</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>// Custom error pages
abort(404, 'Custom message');
abort_if(!$user, 403, 'Access denied');
abort_unless($isAdmin, 401);

// Debug helpers
dd($variable); // Dump and die
dump($variable); // Apenas dump</code></pre>
                </div>
            </div>
        </section>

        <!-- Deploy -->
        <section id="deploy" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🚀 Deploy em Produção</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Configuração do Servidor</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code># Apache (.htaccess)
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/branco/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}</code></pre>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Configurações de Produção</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>// config.php
'app' => [
    'debug' => false, // Desativar debug
    'env' => 'production',
],

'cache' => [
    'default' => 'redis', // Usar Redis em produção
],

'security' => [
    'strict_transport_security' => 'max-age=31536000; includeSubDomains; preload',
    'content_security_policy' => 'default-src \'self\'; script-src \'self\'',
],</code></pre>
                </div>
            </div>
        </section>

        <!-- Melhores Práticas -->
        <section id="practices" class="mb-12">
            <h2 class="text-3xl font-bold mb-6">📝 Melhores Práticas</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">🎯 Organização do Código</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li>Mantenha controllers magros</li>
                        <li>Coloque lógica de negócio em models/services</li>
                        <li>Use middlewares para validações</li>
                        <li>Separate concerns em classes específicas</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">🛡️ Security</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li>Sempre valide inputs</li>
                        <li>Use prepared statements (Query Builder já faz isso)</li>
                        <li>Ative security headers</li>
                        <li>Configure CORS corretamente</li>
                        <li>Use HTTPS em produção</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">⚡ Performance</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li>Use cache para dados frequentes</li>
                        <li>Otimize queries SQL</li>
                        <li>Comprima assets</li>
                        <li>Use lazy loading quando possível</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">🧪 Testing</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li>Escreva testes unitários</li>
                        <li>Teste middlewares</li>
                        <li>Valide inputs nos testes</li>
                        <li>Use ambiente de testes</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Links Úteis -->
        <section class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🔗 Links Úteis</h2>
            
            <div class="bg-blue-50 rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="/api" class="block p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                        <h3 class="font-semibold text-blue-600">📚 API Documentation</h3>
                        <p class="text-gray-600 text-sm">Documentação completa da API</p>
                    </a>
                    <a href="/examples" class="block p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                        <h3 class="font-semibold text-blue-600">💡 Examples</h3>
                        <p class="text-gray-600 text-sm">Exemplos práticos de código</p>
                    </a>
                    <a href="/tutorials" class="block p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                        <h3 class="font-semibold text-blue-600">🎓 Tutorials</h3>
                        <p class="text-gray-600 text-sm">Tutoriais passo a passo</p>
                    </a>
                    <a href="/support" class="block p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                        <h3 class="font-semibold text-blue-600">🆘 Support</h3>
                        <p class="text-gray-600 text-sm">Ajuda e suporte</p>
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
