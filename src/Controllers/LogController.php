<?php

namespace App\Controllers;

use App\Http\Request;
use App\Helpers\LogHelper;
use App\Helpers\FileHelper;

class LogController extends BaseController {
    public function index(Request $request) {
        $stats = LogHelper::stats();
        
        // Obter logs recentes
        $logFiles = glob(FileHelper::logs('*.log'));
        $recentLogs = [];
        
        foreach ($logFiles as $file) {
            $content = FileHelper::get($file);
            $lines = array_slice(explode("\n", $content), -50); // Últimas 50 linhas
            $recentLogs[basename($file)] = array_reverse($lines);
        }
        
        return $this->view('logs/index', [
            'stats' => $stats,
            'recent_logs' => $recentLogs
        ]);
    }
    
    public function view(Request $request, $filename) {
        $logFile = FileHelper::logs($filename);
        
        if (!FileHelper::exists($logFile)) {
            return $this->error('Arquivo de log não encontrado', 404);
        }
        
        $content = FileHelper::get($logFile);
        $lines = explode("\n", $content);
        
        // Paginação
        $page = (int) $request->get('page', 1);
        $perPage = 100;
        $totalLines = count($lines);
        $totalPages = ceil($totalLines / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $paginatedLines = array_slice($lines, $offset, $perPage);
        
        return $this->view('logs/view', [
            'filename' => $filename,
            'lines' => $paginatedLines,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalLines' => $totalLines
        ]);
    }
    
    public function clear(Request $request) {
        $days = (int) $request->post('days', 7);
        
        LogHelper::clear($days);
        
        return $this->success('Logs antigos removidos', [
            'days' => $days,
            'cleared_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function download(Request $request, $filename) {
        $logFile = FileHelper::logs($filename);
        
        if (!FileHelper::exists($logFile)) {
            return $this->error('Arquivo de log não encontrado', 404);
        }
        
        // Configurar headers para download
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . FileHelper::size($logFile));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($logFile);
        exit;
    }
    
    public function search(Request $request) {
        $query = $request->get('q');
        $filename = $request->get('file');
        
        if (!$query || !$filename) {
            return $this->error('Parâmetros inválidos');
        }
        
        $logFile = FileHelper::logs($filename);
        
        if (!FileHelper::exists($logFile)) {
            return $this->error('Arquivo de log não encontrado', 404);
        }
        
        $content = FileHelper::get($logFile);
        $lines = explode("\n", $content);
        
        // Buscar nas linhas
        $matchingLines = array_filter($lines, function($line) use ($query) {
            return stripos($line, $query) !== false;
        });
        
        return $this->json([
            'query' => $query,
            'filename' => $filename,
            'total_matches' => count($matchingLines),
            'lines' => $matchingLines
        ]);
    }
}
