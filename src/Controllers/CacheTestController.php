<?php

namespace App\Controllers;

use App\Http\Request;

class CacheTestController extends BaseController {
    public function index(Request $request) {
        $startTime = microtime(true);
        
        // Teste 1: Cache simples
        info('Testando cache simples');
        
        // Primeira vez - vai executar a query
        $users1 = cache('test_users', function() {
            info('Executando query de usuários (primeira vez)');
            sleep(2); // Simula query lenta
            return \App\Database\Models\User::count();
        }, 10);
        
        // Segunda vez - vai ler do cache
        $users2 = cache('test_users', function() {
            info('Executando query de usuários (segunda vez - NÃO DEVE APARECER)');
            sleep(2);
            return \App\Database\Models\User::count();
        });
        
        $timeWithoutCache = microtime(true);
        
        // Teste 2: Cache de dados complexos
        $complexData = cache('complex_data', function() {
            info('Gerando dados complexos');
            return [
                'users' => \App\Database\Models\User::limit(5)->get(),
                'villages' => \App\Database\Models\Village::limit(5)->get(),
                'stats' => [
                    'total_users' => \App\Database\Models\User::count(),
                    'total_villages' => \App\Database\Models\Village::count(),
                    'generated_at' => date('Y-m-d H:i:s')
                ]
            ];
        }, 30);
        
        $timeWithCache = microtime(true);
        
        // Estatísticas do cache
        $cacheStats = \App\Helpers\CacheHelper::stats();
        
        return $this->view('cache_test', [
            'users1' => $users1,
            'users2' => $users2,
            'complexData' => $complexData,
            'timeWithoutCache' => ($timeWithoutCache - $startTime) * 1000,
            'timeWithCache' => ($timeWithCache - $timeWithoutCache) * 1000,
            'cacheStats' => $cacheStats,
            'cacheDir' => storage_path('cache')
        ]);
    }
    
    public function clear() {
        \App\Helpers\CacheHelper::flush();
        
        return $this->json([
            'message' => 'Cache limpo com sucesso!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function remember() {
        // Teste de remember function
        $data = remember('remember_test', function() {
            info('Executando remember test');
            return [
                'message' => 'Gerado em ' . date('Y-m-d H:i:s'),
                'random' => rand(1, 1000)
            ];
        }, 5);
        
        return $this->json($data);
    }
}
