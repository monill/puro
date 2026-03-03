# Minimal PHP Framework

Um framework PHP 8.4 minimalista, completo e compartilhável, com todas as funcionalidades modernas que você precisa para desenvolver aplicações web robustas.

## 🚀 Características Principais

- **PHP 8.4+** com strict types e type hints
- **Multi-guard Authentication** para diferentes tipos de usuários
- **Shared Hosting Friendly** - funciona em host compartilhado
- **Zero Dependencies** externas (opcional: Redis, Memcached)
- **MVC Architecture** com controllers, models e views
- **Middleware System** para interceptação de requisições
- **CLI Tool** para gerenciamento do projeto
- **Debug Toolbar** para desenvolvimento
- **Cache System** com múltiplos drivers
- **Security Headers** e proteção contra ataques

## 📁 Estrutura de Arquivos

```
branco/
├── app/
│   ├── Core/                    # Classes principais do framework
│   │   ├── Database/           # Camada de dados
│   │   ├── Cache/              # Sistema de cache
│   │   ├── Security/           # Segurança
│   │   └── ...                 # Outros componentes
│   ├── Controllers/            # Controllers da aplicação
│   ├── Models/                 # Models da aplicação
│   ├── Middlewares/            # Middlewares personalizados
│   └── helpers.php             # Funções helpers globais
├── templates/                  # Views e templates
│   ├── errors/                 # Páginas de erro
│   ├── partials/               # Componentes reutilizáveis
│   └── layout.php              # Layout principal
├── public/                     # Arquivos públicos
│   ├── index.php              # Entry point
│   └── assets/                # CSS, JS, imagens
├── routes/                     # Definição de rotas
│   ├── web.php                # Rotas web
│   └── api.php                 # Rotas API
├── storage/                    # Armazenamento
│   ├── cache/                 # Cache files
│   ├── logs/                  # Logs
│   └── uploads/               # Uploads
├── config.php                  # Configurações
├── cli                         # CLI executable
└── README.md                   # Documentação
```

## 🛠️ Instalação e Configuração

### 1. Requisitos

- PHP 8.4+
- Servidor web (Apache, Nginx, ou PHP built-in)
- Banco de dados (MySQL/MariaDB, PostgreSQL, SQLite)
- (Opcional) Redis ou Memcached para cache

### 2. Configuração

1. **Copie o arquivo de configuração:**
   ```bash
   cp config.sample.php config.php
   ```

2. **Configure o banco de dados em `config.php`:**
   ```php
   'db' => [
       'host' => 'localhost',
       'name' => 'minimal_framework',
       'user' => 'root',
       'pass' => 'senha',
       'charset'  => 'utf8mb4',
       'timezone' => '-03:00',
   ],
   ```

3. **Configure outras opções:**
   - URL da aplicação
   - Configurações de e-mail
   - Drivers de cache
   - Security headers

### 3. Execução

**Para desenvolvimento:**
```bash
php cli serve
```

**Para produção:**
- Configure seu servidor web para apontar para a pasta `public/`
- Configure as permissões das pastas `storage/` e `public/assets/`

## 📚 Guia de Uso

### Database Layer

O framework inclui um completo sistema de banco de dados com Query Builder, Migrações e Seeders.

#### Query Builder

```php
use App\Core\Database\Connection;
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
    ->delete();
```

#### Migrações

**Criar migração:**
```bash
php cli make:migration create_users_table
```

**Executar migrações:**
```bash
php cli migrate
```

**Rollback:**
```bash
php cli migrate:rollback
```

#### Seeders

**Criar seeder:**
```bash
php cli make:seeder UsersTableSeeder
```

**Executar seeders:**
```bash
php cli seed
```

### View System

O sistema de views suporta layouts, sections, includes e helpers.

#### Criando Views

**Template básico (`templates/welcome.php`):**
```php
@extends('layout')

@section('title', 'Welcome')

@section('content')
<div class="container">
    <h1>Welcome to Minimal Framework!</h1>
    <p>Hello, {{ $name }}!</p>
    
    @if(auth()->check())
        <p>Welcome back, {{ auth()->user()->name }}!</p>
    @else
        <p>Please <a href="{{ route('login') }}">login</a></p>
    @endif
</div>
@endsection
```

**Layout principal (`templates/layout.php`):**
```php
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'My App')</title>
    @asset('css/app.css')
</head>
<body>
    @include('partials.navigation')
    
    <main>
        @yield('content')
    </main>
    
    @include('partials.footer')
    @asset('js/app.js')
</body>
</html>
```

#### Renderizando Views

**Em controllers:**
```php
use App\Core\View;

class HomeController
{
    public function index($request)
    {
        $data = [
            'name' => 'John Doe',
            'users' => User::all()
        ];
        
        return View::make('welcome', $data);
    }
}
```

**Em rotas:**
```php
Route::get('/', function($request) {
    return view('welcome', ['name' => 'World']);
});
```

### Authentication

Sistema de autenticação multi-guard para diferentes tipos de usuários.

#### Configuração

```php
// config.php
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
],
```

#### Usando Authentication

```php
// Login
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
}
```

#### Helpers em Views

```php
@if(auth()->check())
    <p>Welcome, {{ auth()->user()->name }}!</p>
@endif

@if(guest())
    <a href="{{ route('login') }}">Login</a>
@endif
```

### Cache System

Sistema de cache com múltiplos drivers (file, redis, memcached).

#### Configuração

```php
// config.php
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
],
```

#### Usando Cache

```php
use App\Core\Cache;

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
$users = $query->cache(3600)->table('users')->get();
```

### Middleware System

Sistema de middleware para interceptação de requisições.

#### Criando Middleware

```php
// app/Middlewares/AuthMiddleware.php
namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if (!AuthManager::check()) {
            return redirect('/login');
        }
        
        return $next($request);
    }
}
```

#### Aplicando Middleware

```php
// Em rotas
Route::get('/dashboard', function($request) {
    return view('dashboard');
})->middleware('auth');

// Global (em config.php ou index.php)
Route::middleware('auth', function($request, $next) {
    if (!AuthManager::check()) {
        return redirect('/login');
    }
    return $next($request);
});
```

### Security

Proteções integradas contra ataques comuns.

#### Input Sanitization

```php
use App\Core\Security\InputSanitizer;

// Limpar input
$cleanInput = InputSanitizer::clean($userInput);

// Validar email
if (InputSanitizer::validateEmail($email)) {
    // ...
}

// Validar CPF/CNPJ (brasileiro)
if (InputSanitizer::validateCpf($cpf)) {
    // ...
}

// Helpers
$email = sanitize($userInput);
$escaped = escape($htmlContent);
```

#### CORS Middleware

```php
use App\Core\Security\CorsMiddleware;

// Aplicar CORS
CorsMiddleware::configure([
    'allowed_origins' => ['https://yourdomain.com'],
    'allowed_methods' => ['GET', 'POST'],
    'allowed_headers' => ['Content-Type', 'Authorization'],
]);

Route::middleware('cors', function($request, $next) {
    return $next($request);
});
```

#### Security Headers

```php
use App\Core\Security\SecurityHeaders;

// Aplicar headers de segurança
SecurityHeaders::configure([
    'x_frame_options' => 'DENY',
    'x_content_type_options' => 'nosniff',
    'strict_transport_security' => 'max-age=31536000; includeSubDomains',
]);

Route::middleware('security', function($request, $next) {
    return $next($request);
});
```

### Rate Limiting

Proteção contra excesso de requisições.

```php
use App\Core\RateLimiter;

// Configurar
RateLimiter::configure([
    'driver' => 'cache',
    'default_limit' => 60, // 60 requisições
    'default_window' => 60, // por minuto
]);

// Aplicar em rotas
Route::get('/api/data', function($request) {
    return response()->json(['data' => 'protected']);
})->middleware('ratelimit:10,1'); // 10 requisições por minuto
```

### Validation

Sistema robusto de validação de inputs.

```php
use App\Core\RequestValidator;

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
class UserController
{
    public function store($request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);
        
        User::create($validated);
        
        return redirect('/users');
    }
}
```

### Helpers Globais

O framework inclui diversos helpers para facilitar o desenvolvimento.

#### Debug Helpers

```php
dd($variable); // Dump and die
dump($variable); // Apenas dump
```

#### URL/Route Helpers

```php
url('/path'); // http://localhost/path
route('users.index'); // URL da rota nomeada
asset('css/app.css'); // URL de asset
mix('js/app.js'); // URL com versionamento
```

#### Form Helpers

```php
old('name'); // Input antigo
error('name'); // Erro de validação
hasError('name'); // Verifica se tem erro
csrf(); // Token CSRF
csrf_field(); // Campo CSRF HTML
method_field('PUT'); // Campo método HTTP
```

#### Auth Helpers

```php
auth(); // Usuário autenticado
guest(); // Verifica se é visitante
user(); // Usuário atual
can('edit-post'); // Verifica permissão
cannot('delete-post'); // Verifica negação de permissão
```

#### String/Array Helpers

```php
str_limit('long text', 50); // Limita string
str_slug('Hello World'); // hello-world
str_random(16); // String aleatória
str_uuid(); // UUID v4

array_get($array, 'key.nested'); // Valor com notação de ponto
array_set($array, 'key.nested', 'value'); // Define valor
array_has($array, 'key.nested'); // Verifica existência
```

#### Validation Helpers

```php
is_email($email); // Valida email
is_url($url); // Valida URL
is_cpf($cpf); // Valida CPF
is_cnpj($cnpj); // Valida CNPJ
sanitize($input); // Limpa input
escape($html); // Escapa HTML
```

### CLI Commands

Ferramenta de linha de comando para gerenciamento do projeto.

#### Comandos Disponíveis

```bash
# Servidor de desenvolvimento
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
php cli logs:tail
```

#### Criando Comandos Personalizados

```php
// app/Commands/CustomCommand.php
namespace App\Commands;

use App\Core\Cli\Command;

class CustomCommand extends Command
{
    protected $signature = 'custom:command {arg}';
    protected $description = 'Custom command description';
    
    public function handle()
    {
        $arg = $this->argument('arg');
        $this->line("Hello, {$arg}!");
    }
}
```

### Error Handling

Sistema completo de tratamento de erros com páginas customizadas.

#### Páginas de Erro

**Debug mode (`templates/errors/debug.php`):**
- Exibe stack trace completo
- Mostra contexto do código
- Informações da requisição

**Production mode (`templates/errors/production.php`):**
- Página amigável para usuários
- Sem expor informações sensíveis

#### Custom Error Pages

```php
// Criar página 404
// templates/errors/404.php
<h1>Page Not Found</h1>
<p>The page you're looking for doesn't exist.</p>
<a href="{{ url('/') }}">Go Home</a>

// Em controllers
abort(404, 'Custom message');
abort_if(!$user, 403, 'Access denied');
abort_unless($isAdmin, 401);
```

### Debug Toolbar

Toolbar interativa para desenvolvimento com informações detalhadas.

#### Informações Exibidas

- **Queries SQL** com tempo de execução
- **Uso de memória** e pico
- **Dados da requisição** (headers, parameters)
- **Session data**
- **Cache hits/misses**
- **Logs recentes**
- **Timers de performance**

#### Ativação

```php
// config.php
'debug_toolbar' => [
    'enabled' => true,
    'position' => 'bottom-right',
    'max_tabs' => 10,
],
```

### Logging

Sistema de logging com múltiplos níveis e rotação.

#### Níveis de Log

```php
use App\Core\Log;

Log::debug('Debug message');
Log::info('Info message');
Log::warning('Warning message');
Log::error('Error message');
Log::critical('Critical message');
```

#### SQL Logging

```php
// Automaticamente loga queries se habilitado
Log::sql($query, $bindings, $executionTime);
```

#### Log Management

```bash
# Limpar logs
php cli logs:clear

# Verificar logs recentes
php cli logs:tail

# Logs por data
Log::getLogs('2024-01-01', 'error');
```

## 🚀 Deploy em Produção

### 1. Configuração do Servidor

**Apache (.htaccess):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx:**
```nginx
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
}
```

### 2. Configurações de Produção

```php
// config.php
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
],
```

### 3. Otimizações

- Ativar OPcache
- Configurar Redis/Memcached
- Usar CDN para assets
- Configurar gzip compression
- Ativar HTTP/2

## 📝 Melhores Práticas

### 1. Organização do Código

- Mantenha controllers magros
- Coloque lógica de negócio em models/services
- Use middlewares para validações
- Separe concerns em classes específicas

### 2. Security

- Sempre valide inputs
- Use prepared statements (Query Builder já faz isso)
- Ative security headers
- Configure CORS corretamente
- Use HTTPS em produção

### 3. Performance

- Use cache para dados frequentes
- Otimize queries SQL
- Comprima assets
- Use lazy loading quando possível

### 4. Testing

- Escreva testes unitários
- Teste middlewares
- Valide inputs nos testes
- Use ambiente de testes

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature
3. Faça commit das mudanças
4. Push para a branch
5. Abra um Pull Request

## 📄 Licença

MIT License - sinta-se livre para usar este framework em seus projetos!

## 🆘 Suporte

Se tiver dúvidas ou problemas:

1. Verifique a documentação
2. Revise os exemplos de código
3. Use o modo debug para investigar
4. Verifique os logs de erros

---

**Desenvolvido com ❤️ para a comunidade PHP brasileira!**
