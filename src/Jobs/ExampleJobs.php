<?php

namespace App\Jobs;

use App\Helpers\LogHelper;
use App\Helpers\DatabaseHelper;

/**
 * Job de Exemplo - Incrementar Recursos
 * 
 * Exemplo de como usar o sistema de filas para processos em background
 */
class IncrementResourcesJob {
    
    /**
     * Executa o job
     */
    public function handle($data) {
        $villageId = $data['village_id'] ?? null;
        $resourceType = $data['resource_type'] ?? 'wood';
        $amount = $data['amount'] ?? 10;
        
        if (!$villageId) {
            throw new Exception("Village ID is required");
        }
        
        LogHelper::info("Incrementing resources", [
            'village_id' => $villageId,
            'resource_type' => $resourceType,
            'amount' => $amount
        ]);
        
        // Simula incremento de recursos
        $this->incrementResource($villageId, $resourceType, $amount);
        
        return true;
    }
    
    /**
     * Incrementa recurso no banco
     */
    private function incrementResource($villageId, $resourceType, $amount) {
        // Aqui você faria o UPDATE no banco
        // Exemplo: UPDATE villages SET wood = wood + ? WHERE id = ?
        
        $sql = "UPDATE villages SET {$resourceType} = {$resourceType} + ? WHERE id = ?";
        $params = [$amount, $villageId];
        
        // Usar DatabaseHelper para executar
        // DatabaseHelper::query($sql, $params);
        
        LogHelper::info("Resource incremented", [
            'village_id' => $villageId,
            'resource_type' => $resourceType,
            'amount' => $amount
        ]);
        
        return true;
    }
}

/**
 * Job de Exemplo - Processar Movimentação de Tropas
 */
class ProcessTroopMovementJob {
    
    public function handle($data) {
        $movementId = $data['movement_id'] ?? null;
        
        if (!$movementId) {
            throw new Exception("Movement ID is required");
        }
        
        LogHelper::info("Processing troop movement", [
            'movement_id' => $movementId
        ]);
        
        // Lógica para processar movimentação
        $this->completeMovement($movementId);
        
        return true;
    }
    
    private function completeMovement($movementId) {
        // Atualiza status do movimento
        // Entrega tropas no destino
        // Processa batalha se necessário
        
        LogHelper::info("Troop movement completed", [
            'movement_id' => $movementId
        ]);
    }
}

/**
 * Job de Exemplo - Enviar Email em Lote
 */
class SendEmailJob {
    
    public function handle($data) {
        $to = $data['to'] ?? null;
        $subject = $data['subject'] ?? '';
        $body = $data['body'] ?? '';
        
        if (!$to) {
            throw new Exception("Email recipient is required");
        }
        
        LogHelper::info("Sending email", [
            'to' => $to,
            'subject' => $subject
        ]);
        
        // Usar EmailHelper para enviar
        // EmailHelper::send($to, $subject, $body);
        
        return true;
    }
}

/**
 * Job de Exemplo - Calcular Produção de Recursos
 * 
 * Executa a cada minuto para todas as aldeias
 */
class CalculateResourceProductionJob {
    
    public function handle($data) {
        LogHelper::info("Calculating resource production for all villages");
        
        // Pega todas as aldeias ativas
        $villages = $this->getAllActiveVillages();
        
        foreach ($villages as $village) {
            $this->calculateVillageProduction($village['id']);
        }
        
        LogHelper::info("Resource production calculated", [
            'villages_processed' => count($villages)
        ]);
        
        return true;
    }
    
    private function getAllActiveVillages() {
        // SQL: SELECT id FROM villages WHERE active = 1
        return [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3]
        ]; // Exemplo
    }
    
    private function calculateVillageProduction($villageId) {
        // Calcula produção baseada em:
        // - Nível dos campos de recursos
        // - Bônus de itens
        // - Eficiência de trabalhadores
        
        // Atualiza recursos na aldeia
        
        LogHelper::debug("Production calculated for village", [
            'village_id' => $villageId
        ]);
    }
}

/**
 * Job de Exemplo - Game Tick Principal
 * 
 * Executa a cada minuto para processar tudo do jogo
 */
class GameTickJob {
    
    public function handle($data) {
        $tickNumber = $data['tick_number'] ?? 1;
        
        LogHelper::info("Processing game tick", [
            'tick_number' => $tickNumber
        ]);
        
        // 1. Produção de recursos
        $this->queueResourceProduction();
        
        // 2. Movimentações de tropas
        $this->queueTroopMovements();
        
        // 3. Construções
        $this->queueBuildingConstruction();
        
        // 4. Batalhas
        $this->queueBattleResolution();
        
        // 5. Eventos especiais
        $this->queueSpecialEvents();
        
        LogHelper::info("Game tick completed", [
            'tick_number' => $tickNumber
        ]);
        
        return true;
    }
    
    private function queueResourceProduction() {
        // Adiciona job para calcular produção
        \App\Queue\Queue::push('CalculateResourceProductionJob', [], 'normal');
    }
    
    private function queueTroopMovements() {
        // Adiciona jobs para movimentações
        \App\Queue\Queue::push('ProcessTroopMovementJob', [], 'high');
    }
    
    private function queueBuildingConstruction() {
        // Adiciona jobs para construções
        // \App\Queue\Queue::push('ProcessBuildingJob', [], 'normal');
    }
    
    private function queueBattleResolution() {
        // Adiciona jobs para batalhas
        // \App\Queue\Queue::push('ResolveBattleJob', [], 'high');
    }
    
    private function queueSpecialEvents() {
        // Adiciona jobs para eventos especiais
        // \App\Queue\Queue::push('ProcessEventJob', [], 'low');
    }
}
