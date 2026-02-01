<?php

namespace App\Controllers;

use App\Http\Request;

class GlobalExampleController extends BaseController {
    public function index(Request $request) {
        // Usando funções globais como Laravel!

        // Logs
        info('Acessando página com funções globais');
        debug('Debug information', ['user_id' => auth()->id()]);

        // Cache
        $stats = cache('stats', function() {
            return [
                'users' => \App\Database\Models\User::count(),
                'villages' => \App\Database\Models\Village::count()
            ];
        }, 300);

        // Configuração
        $appName = config('name', 'Puro');
        $isDebug = is_debug();

        // Paths
        $storagePath = storage_path();
        $publicPath = public_path();

        // Validação
        $email = 'test@example.com';
        $isValidEmail = is_email($email);

        // Geração de tokens
        $token = random_token();
        $uuid = uuid();

        // Formatação
        $number = 1234.56;
        $formattedNumber = format_number($number);
        $currency = format_currency($number, 'BRL');

        // Data
        $date = format_date('now', 'long');
        $timeAgo = time_ago('-2 hours');

        // Traduções
        $welcome = __('common.welcome');
        $userCount = trans_choice('common.items_count', $stats['users']);

        // Sessão
        session('last_visit', date('Y-m-d H:i:s'));
        $lastVisit = session('last_visit');

        // Flash messages
        session('flash.success', 'Página carregada com sucesso!');

        return $this->view('global_example', [
            'appName' => $appName,
            'isDebug' => $isDebug,
            'stats' => $stats,
            'storagePath' => $storagePath,
            'publicPath' => $publicPath,
            'isValidEmail' => $isValidEmail,
            'token' => $token,
            'uuid' => $uuid,
            'formattedNumber' => $formattedNumber,
            'currency' => $currency,
            'date' => $date,
            'timeAgo' => $timeAgo,
            'welcome' => $welcome,
            'userCount' => $userCount,
            'lastVisit' => $lastVisit
        ]);
    }

    public function testValidation(Request $request) {
        // Exemplo de validação com funções globais
        $data = $request->all();

        $rules = [
            'username' => 'required|string|min:3',
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ];

        $errors = validate($data, $rules);

        if (!empty($errors)) {
            session('flash.error', 'Erro de validação!');
            return back();
        }

        // Sanitizar dados
        $username = sanitize($data['username']);
        $email = sanitize($data['email']);

        // Gerar slug
        $slug = slug($username);

        info('Dados validados', [
            'username' => $username,
            'email' => $email,
            'slug' => $slug
        ]);

        session('flash.success', 'Dados validados com sucesso!');

        return redirect('/global-example');
    }

    public function testCache() {
        // Testar cache com funções globais

        // Cache simples
        cache('simple_key', 'simple_value', 60);
        $simpleValue = cache('simple_key');

        // Cache com callback
        $expensiveOperation = remember('expensive_operation', function() {
            // Simula operação demorada
            sleep(2);
            return 'Resultado da operação demorada: ' . date('H:i:s');
        }, 10);

        // Cache remember
        $userCount = remember('user_count', function() {
            return \App\Database\Models\User::count();
        });

        return $this->json([
            'simple_value' => $simpleValue,
            'expensive_operation' => $expensiveOperation,
            'user_count' => $userCount
        ]);
    }

    public function testAuth() {
        // Testar autenticação com funções globais

        $isLoggedIn = is_logged_in();
        $isAdmin = is_admin();
        $currentUser = user();

        if ($isLoggedIn) {
            info('Usuário logado', [
                'user_id' => auth()->id(),
                'username' => auth()->user()->username,
                'is_admin' => auth()->isAdmin()
            ]);
        } else {
            info('Visitante acessando página');
        }

        return $this->json([
            'is_logged_in' => $isLoggedIn,
            'is_admin' => $isAdmin,
            'current_user' => $currentUser ? [
                'id' => $currentUser->id,
                'username' => $currentUser->username,
                'email' => $currentUser->email
            ] : null
        ]);
    }

    public function testLanguage() {
        // Testar traduções com funções globais

        $welcome = __('common.welcome');
        $userList = __('users.user_list');
        $itemsCount = trans_choice('common.items_count', 5);

        // Mudar locale
        set_locale('en');
        $welcomeEn = __('common.welcome');

        // Voltar para pt-br
        set_locale('pt-br');

        return $this->json([
            'welcome_pt' => $welcome,
            'welcome_en' => $welcomeEn,
            'user_list' => $userList,
            'items_count' => $itemsCount,
            'current_locale' => locale()
        ]);
    }
}
