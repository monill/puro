<?php $title = 'Exemplos Práticos - Minimal PHP Framework'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Exemplos Práticos</h1>
            <p class="text-lg text-gray-600">Exemplos de código para começar a usar o framework</p>
        </div>

        <!-- CRUD Example -->
        <section class="mb-12">
            <h2 class="text-3xl font-bold mb-6">📝 CRUD Básico</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Controller de Usuários</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>&lt;?php
// app/Controllers/UserController.php
namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Core\RequestValidator;
use App\Models\User;

class UserController
{
    // Listar todos os usuários
    public function index(Request $request)
    {
        $users = User::all();
        return View::make('users.index', ['users' => $users]);
    }

    // Formulário de criação
    public function create(Request $request)
    {
        return View::make('users.create');
    }

    // Salvar novo usuário
    public function store(Request $request)
    {
        // Validar dados
        $validator = RequestValidator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('errors', $validator->errors())
                ->withInput();
        }

        // Criar usuário
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => password_hash($request->get('password'), PASSWORD_DEFAULT),
        ]);

        return redirect('/users')
            ->with('success', 'Usuário criado com sucesso!');
    }
}
?&gt;</code></pre>
                </div>
            </div>
        </section>

        <!-- API Example -->
        <section class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🌐 API REST</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">API Controller</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>&lt;?php
// app/Controllers/ApiController.php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\RequestValidator;
use App\Models\User;

class ApiController
{
    // GET /api/users
    public function index(Request $request)
    {
        $users = User::all();
        
        return response()->json([
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    // POST /api/users
    public function store(Request $request)
    {
        $validator = RequestValidator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => password_hash($request->get('password'), PASSWORD_DEFAULT),
        ]);

        return response()->json([
            'data' => $user,
            'message' => 'User created successfully'
        ], 201);
    }
}
?&gt;</code></pre>
                </div>
            </div>
        </section>

        <!-- Authentication Example -->
        <section class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🔐 Sistema de Autenticação</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Auth Controller</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>&lt;?php
// app/Controllers/AuthController.php
namespace App\Controllers;

use App\Core\Request;
use App\Core\AuthManager;
use App\Core\RequestValidator;
use App\Models\User;

class AuthController
{
    // Formulário de login
    public function showLoginForm()
    {
        if (AuthManager::check()) {
            return redirect('/dashboard');
        }
        
        return view('auth.login');
    }

    // Processar login
    public function login(Request $request)
    {
        $validator = RequestValidator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('errors', $validator->errors())
                ->withInput();
        }

        $credentials = [
            'email' => $request->get('email'),
            'password' => $request->get('password'),
        ];

        if (AuthManager::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/dashboard');
        }

        return redirect()->back()
            ->with('error', 'Credenciais inválidas')
            ->withInput();
    }

    // Logout
    public function logout(Request $request)
    {
        AuthManager::logout();
        $request->session()->invalidate();
        
        return redirect('/login');
    }
}
?&gt;</code></pre>
                </div>
            </div>
        </section>

        <!-- Cache Example -->
        <section class="mb-12">
            <h2 class="text-3xl font-bold mb-6">⚡ Cache Avançado</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Usando Cache em Controllers</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>&lt;?php
// app/Controllers/PostController.php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Cache;
use App\Models\Post;

class PostController
{
    // Listar posts com cache
    public function index(Request $request)
    {
        $cacheKey = "posts.all";
        
        $posts = Cache::remember($cacheKey, 3600, function() {
            return Post::with(['user', 'comments'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
        
        return view('posts.index', ['posts' => $posts]);
    }

    // Limpar cache ao atualizar
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            abort(404);
        }
        
        $post->update($request->all());
        
        // Limpar cache
        Cache::forget("posts.all");
        
        return redirect()->back()
            ->with('success', 'Post atualizado!');
    }
}
?&gt;</code></pre>
                </div>
            </div>
        </section>

        <!-- File Upload Example -->
        <section class="mb-12">
            <h2 class="text-3xl font-bold mb-6">📁 Upload de Arquivos</h2>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-4">Controller de Upload</h3>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre><code>&lt;?php
// app/Controllers/UploadController.php
namespace App\Controllers;

use App\Core\Request;
use App\Core\RequestValidator;

class UploadController
{
    // Formulário de upload
    public function showForm()
    {
        return view('upload.form');
    }

    // Processar upload
    public function upload(Request $request)
    {
        $validator = RequestValidator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('errors', $validator->errors())
                ->withInput();
        }

        $file = $request->file('file');
        
        if (!$file || !$file->isValid()) {
            return redirect()->back()
                ->with('error', 'Arquivo inválido');
        }

        // Validar tipo de arquivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return redirect()->back()
                ->with('error', 'Tipo de arquivo não permitido');
        }

        // Gerar nome único
        $extension = $file->getExtension();
        $filename = uniqid() . '.' . $extension;
        
        // Salvar arquivo
        $uploadPath = storage_path('uploads') . '/' . $filename;
        
        if (!$file->move($uploadPath)) {
            return redirect()->back()
                ->with('error', 'Erro ao salvar arquivo');
        }

        return redirect('/uploads')
            ->with('success', 'Arquivo enviado com sucesso!');
    }
}
?&gt;</code></pre>
                </div>
            </div>
        </section>

        <!-- Links para mais exemplos -->
        <section class="mb-12">
            <h2 class="text-3xl font-bold mb-6">🔗 Mais Exemplos</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">📧 Envio de E-mails</h3>
                    <p class="text-gray-600 mb-4">Exemplos de como usar o sistema de e-mails do framework.</p>
                    <a href="/examples/email" class="text-blue-600 hover:text-blue-800">Ver exemplos →</a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">🔔 Notificações</h3>
                    <p class="text-gray-600 mb-4">Sistema de notificações e eventos.</p>
                    <a href="/examples/notifications" class="text-blue-600 hover:text-blue-800">Ver exemplos →</a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">📊 Relatórios</h3>
                    <p class="text-gray-600 mb-4">Geração de relatórios e exportação de dados.</p>
                    <a href="/examples/reports" class="text-blue-600 hover:text-blue-800">Ver exemplos →</a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4">🎨 Templates Avançados</h3>
                    <p class="text-gray-600 mb-4">Templates complexos e componentes reutilizáveis.</p>
                    <a href="/examples/templates" class="text-blue-600 hover:text-blue-800">Ver exemplos →</a>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
