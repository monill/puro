<?php

namespace App\Controllers;

use App\Http\Request;
use App\Database\Models\User;
use App\Database\Models\Village;

class HomeController extends BaseController {
    public function index(Request $request) {
        // Estatísticas do jogo
        $totalUsers = User::count();
        $totalVillages = Village::count();
        $onlineUsers = User::where('last_login', '>', date('Y-m-d H:i:s', time() - 300))->count();
        
        // Usuários recentes
        $recentUsers = User::select(['id', 'username', 'tribe', 'created_at'])
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        // Aldeias principais
        $capitalVillages = Village::where('is_capital', 1)
            ->select(['name', 'population', 'owner_id'])
            ->orderBy('population', 'DESC')
            ->limit(10)
            ->get();

        return $this->view('home', [
            'totalUsers' => $totalUsers,
            'totalVillages' => $totalVillages,
            'onlineUsers' => $onlineUsers,
            'recentUsers' => $recentUsers,
            'capitalVillages' => $capitalVillages
        ]);
    }

    public function about(Request $request) {
        return $this->view('about');
    }

    public function rules(Request $request) {
        return $this->view('rules');
    }

    public function contact(Request $request) {
        return $this->view('contact');
    }

    public function stats(Request $request) {
        $tribeStats = [
            'romanos' => User::where('tribe', 1)->count(),
            'teutoes' => User::where('tribe', 2)->count(),
            'galias' => User::where('tribe', 3)->count()
        ];

        $topPlayers = User::select(['username', 'tribe', 'population'])
            ->orderBy('population', 'DESC')
            ->limit(10)
            ->get();

        $topVillages = Village::select(['name', 'population', 'owner_id'])
            ->orderBy('population', 'DESC')
            ->limit(10)
            ->get();

        return $this->view('stats', [
            'tribeStats' => $tribeStats,
            'topPlayers' => $topPlayers,
            'topVillages' => $topVillages
        ]);
    }
}
