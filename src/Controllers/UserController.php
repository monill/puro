<?php

namespace App\Controllers;

use App\Http\Request;
use App\Database\Models\User;
use App\Database\Models\Village;

class UserController extends BaseController {
    public function index(Request $request) {
        $search = $request->get('search');
        $tribe = $request->get('tribe');
        $page = (int) $request->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $query = User::select(['id', 'username', 'email', 'tribe', 'population', 'last_login', 'created_at']);

        if ($search) {
            $query = $query->where('username', 'LIKE', "%{$search}%");
        }

        if ($tribe) {
            $query = $query->where('tribe', $tribe);
        }

        $totalUsers = $query->count();
        $users = $query->limit($limit, $offset)->get();

        $totalPages = ceil($totalUsers / $limit);

        return $this->view('users/index', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'tribe' => $tribe
        ]);
    }

    public function show(Request $request, $id) {
        $user = User::find($id);
        
        if (!$user) {
            return $this->error('Usuário não encontrado', 404);
        }

        $villages = $user->getVillages();
        $capitalVillage = $user->getCapitalVillage();
        $totalPopulation = $user->getTotalPopulation();

        return $this->view('users/show', [
            'user' => $user,
            'villages' => $villages,
            'capitalVillage' => $capitalVillage,
            'totalPopulation' => $totalPopulation
        ]);
    }

    public function create(Request $request) {
        return $this->view('users/create');
    }

    public function store(Request $request) {
        $validation = $this->validate($request, [
            'username' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'tribe' => 'required'
        ]);

        if ($validation) {
            return $validation;
        }

        // Verificar se usuário já existe
        if (User::where('username', $request->post('username'))->first()) {
            return $this->error('Nome de usuário já está em uso');
        }

        if (User::where('email', $request->post('email'))->first()) {
            return $this->error('E-mail já está em uso');
        }

        // Criar usuário
        $userData = [
            'username' => $request->post('username'),
            'email' => $request->post('email'),
            'password' => password_hash($request->post('password'), PASSWORD_DEFAULT),
            'tribe' => $request->post('tribe'),
            'population' => 2,
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => date('Y-m-d H:i:s')
        ];

        $user = User::create($userData);

        // Criar aldeia inicial
        $villageData = [
            'owner_id' => $user->id,
            'name' => "Aldeia de {$user->username}",
            'is_capital' => 1,
            'population' => 2,
            'wood' => 750,
            'clay' => 750,
            'iron' => 750,
            'crop' => 750,
            'wood_production' => 10,
            'clay_production' => 10,
            'iron_production' => 10,
            'crop_production' => 10,
            'max_store' => 800,
            'max_crop' => 800,
            'loyalty' => 100,
            'x' => rand(1, 100),
            'y' => rand(1, 100),
            'created_at' => date('Y-m-d H:i:s')
        ];

        Village::create($villageData);

        return $this->success('Usuário criado com sucesso!', ['user' => $user]);
    }

    public function edit(Request $request, $id) {
        $user = User::find($id);
        
        if (!$user) {
            return $this->error('Usuário não encontrado', 404);
        }

        return $this->view('users/edit', ['user' => $user]);
    }

    public function update(Request $request, $id) {
        $user = User::find($id);
        
        if (!$user) {
            return $this->error('Usuário não encontrado', 404);
        }

        $validation = $this->validate($request, [
            'username' => 'required|min:3',
            'email' => 'required|email'
        ]);

        if ($validation) {
            return $validation;
        }

        // Verificar se usuário já existe (exceto o próprio)
        if (User::where('username', $request->post('username'))->where('id', '!=', $id)->first()) {
            return $this->error('Nome de usuário já está em uso');
        }

        if (User::where('email', $request->post('email'))->where('id', '!=', $id)->first()) {
            return $this->error('E-mail já está em uso');
        }

        $userData = [
            'username' => $request->post('username'),
            'email' => $request->post('email'),
            'tribe' => $request->post('tribe')
        ];

        $user->fill($userData)->save();

        return $this->success('Usuário atualizado com sucesso!');
    }

    public function destroy(Request $request, $id) {
        $user = User::find($id);
        
        if (!$user) {
            return $this->error('Usuário não encontrado', 404);
        }

        // Deletar aldeias do usuário
        $villages = $user->getVillages();
        foreach ($villages as $village) {
            $village->delete();
        }

        // Deletar usuário
        $user->delete();

        return $this->success('Usuário deletado com sucesso!');
    }
}
