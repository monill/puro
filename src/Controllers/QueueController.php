<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Middleware\SecurityMiddleware;
use App\Queue\Queue;
use App\Helpers\LogHelper;

/**
 * Queue Controller - Controller para gerenciar filas
 */
class QueueController extends BaseController {
    
    /**
     * Dashboard das filas
     */
    public function dashboard(Request $request) {
        // Verifica se é admin
        if (!$this->isAdmin()) {
            return $this->redirect('/');
        }
        
        $stats = Queue::getStats();
        $pendingJobs = Queue::getPendingJobs(10);
        
        return $this->view('queue/dashboard', [
            'stats' => $stats,
            'pending_jobs' => $pendingJobs,
            'is_running' => Queue::isRunning()
        ]);
    }
    
    /**
     * Processa jobs manualmente
     */
    public function work(Request $request) {
        if (!$this->isAdmin()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }
        
        $maxJobs = $request->getPost('max_jobs', 10);
        $result = Queue::work($maxJobs);
        
        return $this->json([
            'success' => true,
            'result' => $result
        ]);
    }
    
    /**
     * Adiciona job de teste
     */
    public function addTestJob(Request $request) {
        if (!$this->isAdmin()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }
        
        $jobType = $request->getPost('job_type');
        $data = $request->getPost('data', []);
        
        $jobId = Queue::push($jobType, $data);
        
        return $this->json([
            'success' => true,
            'job_id' => $jobId
        ]);
    }
    
    /**
     * Limpa filas
     */
    public function cleanup(Request $request) {
        if (!$this->isAdmin()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }
        
        $removed = Queue::cleanup();
        
        return $this->json([
            'success' => true,
            'removed' => $removed
        ]);
    }
    
    /**
     * Verifica se é admin
     */
    private function isAdmin() {
        // Simples verificação - implementar conforme seu sistema de auth
        return isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'];
    }
}

/**
 * Security Controller - Controller para gerenciar segurança
 */
class SecurityController extends BaseController {
    
    /**
     * Dashboard de segurança
     */
    public function dashboard(Request $request) {
        if (!$this->isAdmin()) {
            return $this->redirect('/');
        }
        
        $stats = SecurityMiddleware::getSecurityStats();
        
        return $this->view('security/dashboard', [
            'stats' => $stats
        ]);
    }
    
    /**
     * Obtém token CSRF
     */
    public function csrfToken(Request $request) {
        return $this->json([
            'csrf_token' => SecurityMiddleware::getCSRFToken()
        ]);
    }
    
    /**
     * Verifica se é admin
     */
    private function isAdmin() {
        return isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'];
    }
}
