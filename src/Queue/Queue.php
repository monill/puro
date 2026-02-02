<?php

namespace App\Queue;

use App\Helpers\LogHelper;
use App\Helpers\DatabaseHelper;

/**
 * Queue System - Sistema de Filas Simples
 * 
 * Para processos em background como:
 * - Incrementar recursos a cada minuto
 * - Processar movimentações de tropas
 * - Enviar emails em lote
 * - Calcular batalhas
 */
class Queue {
    
    private static $jobs = [];
    private static $isRunning = false;
    
    /**
     * Adiciona job à fila
     */
    public static function push($jobClass, $data = [], $priority = 'normal', $delay = 0) {
        $job = [
            'id' => uniqid('job_', true),
            'class' => $jobClass,
            'data' => $data,
            'priority' => $priority,
            'delay' => $delay,
            'attempts' => 0,
            'max_attempts' => 3,
            'created_at' => time(),
            'available_at' => time() + $delay,
            'status' => 'pending'
        ];
        
        self::$jobs[] = $job;
        
        LogHelper::info("Job added to queue", [
            'job_id' => $job['id'],
            'class' => $jobClass,
            'priority' => $priority,
            'delay' => $delay
        ]);
        
        return $job['id'];
    }
    
    /**
     * Adiciona job para executar em X segundos
     */
    public static function later($seconds, $jobClass, $data = []) {
        return self::push($jobClass, $data, 'normal', $seconds);
    }
    
    /**
     * Adiciona job de alta prioridade
     */
    public static function pushHigh($jobClass, $data = []) {
        return self::push($jobClass, $data, 'high', 0);
    }
    
    /**
     * Adiciona job de baixa prioridade
     */
    public static function pushLow($jobClass, $data = []) {
        return self::push($jobClass, $data, 'low', 0);
    }
    
    /**
     * Processa todos os jobs disponíveis
     */
    public static function work($maxJobs = 10) {
        if (self::$isRunning) {
            LogHelper::warning("Queue worker already running");
            return false;
        }
        
        self::$isRunning = true;
        $processed = 0;
        $failed = 0;
        
        LogHelper::info("Queue worker started", ['max_jobs' => $maxJobs]);
        
        try {
            // Ordena jobs por prioridade e data
            $availableJobs = self::getAvailableJobs();
            
            foreach ($availableJobs as $job) {
                if ($processed >= $maxJobs) {
                    break;
                }
                
                try {
                    self::processJob($job);
                    $processed++;
                } catch (Exception $e) {
                    self::handleFailedJob($job, $e);
                    $failed++;
                }
            }
            
        } finally {
            self::$isRunning = false;
        }
        
        LogHelper::info("Queue worker finished", [
            'processed' => $processed,
            'failed' => $failed,
            'total_jobs' => count(self::$jobs)
        ]);
        
        return [
            'processed' => $processed,
            'failed' => $failed,
            'remaining' => count(self::$jobs)
        ];
    }
    
    /**
     * Obtém jobs disponíveis para processamento
     */
    private static function getAvailableJobs() {
        $now = time();
        $available = [];
        
        // Filtra jobs disponíveis
        foreach (self::$jobs as $job) {
            if ($job['status'] === 'pending' && $job['available_at'] <= $now) {
                $available[] = $job;
            }
        }
        
        // Ordena por prioridade
        usort($available, function($a, $b) {
            $priorities = ['high' => 3, 'normal' => 2, 'low' => 1];
            $priorityA = $priorities[$a['priority']] ?? 2;
            $priorityB = $priorities[$b['priority']] ?? 2;
            
            if ($priorityA === $priorityB) {
                return $a['created_at'] - $b['created_at']; // FIFO mesma prioridade
            }
            
            return $priorityB - $priorityA; // Maior prioridade primeiro
        });
        
        return $available;
    }
    
    /**
     * Processa um job específico
     */
    private static function processJob($job) {
        $className = $job['class'];
        
        if (!class_exists($className)) {
            throw new Exception("Job class not found: $className");
        }
        
        $jobInstance = new $className();
        
        if (!method_exists($jobInstance, 'handle')) {
            throw new Exception("Job class must have handle() method: $className");
        }
        
        LogHelper::info("Processing job", [
            'job_id' => $job['id'],
            'class' => $className,
            'attempt' => $job['attempts'] + 1
        ]);
        
        // Marca como processando
        self::updateJobStatus($job['id'], 'processing');
        
        try {
            // Executa o job
            $jobInstance->handle($job['data']);
            
            // Marca como concluído
            self::updateJobStatus($job['id'], 'completed');
            
            LogHelper::info("Job completed successfully", [
                'job_id' => $job['id'],
                'class' => $className
            ]);
            
        } catch (Exception $e) {
            // Marca como failed
            self::updateJobStatus($job['id'], 'failed');
            throw $e;
        }
    }
    
    /**
     * Lida com jobs que falharam
     */
    private static function handleFailedJob($job, $exception) {
        $job['attempts']++;
        
        LogHelper::error("Job failed", [
            'job_id' => $job['id'],
            'class' => $job['class'],
            'attempt' => $job['attempts'],
            'max_attempts' => $job['max_attempts'],
            'error' => $exception->getMessage()
        ]);
        
        if ($job['attempts'] >= $job['max_attempts']) {
            // Marca como permanentemente falho
            self::updateJobStatus($job['id'], 'failed_permanent');
            
            LogHelper::error("Job failed permanently", [
                'job_id' => $job['id'],
                'class' => $job['class'],
                'attempts' => $job['attempts']
            ]);
            
        } else {
            // Tenta novamente depois (exponential backoff)
            $delay = min(300, pow(2, $job['attempts']) * 10); // 10s, 20s, 40s, 80s, 160s, max 5min
            
            $job['available_at'] = time() + $delay;
            $job['status'] = 'pending';
            
            // Re-adiciona à fila
            self::updateJob($job);
            
            LogHelper::info("Job queued for retry", [
                'job_id' => $job['id'],
                'class' => $job['class'],
                'attempt' => $job['attempts'],
                'retry_delay' => $delay
            ]);
        }
    }
    
    /**
     * Atualiza status do job
     */
    private static function updateJobStatus($jobId, $status) {
        foreach (self::$jobs as &$job) {
            if ($job['id'] === $jobId) {
                $job['status'] = $status;
                $job['updated_at'] = time();
                break;
            }
        }
    }
    
    /**
     * Atualiza job completo
     */
    private static function updateJob($updatedJob) {
        foreach (self::$jobs as &$job) {
            if ($job['id'] === $updatedJob['id']) {
                $job = $updatedJob;
                break;
            }
        }
    }
    
    /**
     * Limpa jobs completos/antigos
     */
    public static function cleanup($maxAge = 3600) {
        $now = time();
        $removed = 0;
        
        foreach (self::$jobs as $key => $job) {
            if (
                ($job['status'] === 'completed' && ($now - $job['updated_at']) > $maxAge) ||
                ($job['status'] === 'failed_permanent' && ($now - $job['updated_at']) > ($maxAge * 24))
            ) {
                unset(self::$jobs[$key]);
                $removed++;
            }
        }
        
        // Re-indexa array
        self::$jobs = array_values(self::$jobs);
        
        LogHelper::info("Queue cleanup completed", [
            'removed' => $removed,
            'remaining' => count(self::$jobs)
        ]);
        
        return $removed;
    }
    
    /**
     * Obtém estatísticas da fila
     */
    public static function getStats() {
        $stats = [
            'total' => count(self::$jobs),
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'failed' => 0,
            'failed_permanent' => 0
        ];
        
        foreach (self::$jobs as $job) {
            $stats[$job['status']]++;
        }
        
        return $stats;
    }
    
    /**
     * Verifica se worker está rodando
     */
    public static function isRunning() {
        return self::$isRunning;
    }
    
    /**
     * Obtém jobs pendentes
     */
    public static function getPendingJobs($limit = 10) {
        $pending = array_filter(self::$jobs, function($job) {
            return $job['status'] === 'pending';
        });
        
        return array_slice($pending, 0, $limit);
    }
}
