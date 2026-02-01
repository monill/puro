<?php

namespace App\Events;

class EventDispatcher {
    private static $listeners = [];
    
    /**
     * Adicionar listener
     */
    public static function listen($event, $listener) {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }
        
        self::$listeners[$event][] = $listener;
    }
    
    /**
     * Disparar evento
     */
    public static function dispatch($event, $data = []) {
        if (!isset(self::$listeners[$event])) {
            return;
        }
        
        foreach (self::$listeners[$event] as $listener) {
            if (is_callable($listener)) {
                call_user_func($listener, $data);
            } elseif (is_string($listener)) {
                // Se for string, tentar instanciar classe
                if (class_exists($listener)) {
                    $instance = new $listener();
                    if (method_exists($instance, 'handle')) {
                        $instance->handle($data);
                    }
                }
            }
        }
        
        LogHelper::debug('Evento disparado', [
            'event' => $event,
            'data' => $data,
            'listeners_count' => count(self::$listeners[$event] ?? [])
        ]);
    }
    
    /**
     * Remover listener
     */
    public static function forget($event, $listener = null) {
        if ($listener === null) {
            unset(self::$listeners[$event]);
        } else {
            if (isset(self::$listeners[$event])) {
                self::$listeners[$event] = array_filter(
                    self::$listeners[$event],
                    function($item) use ($listener) {
                        return $item !== $listener;
                    }
                );
            }
        }
    }
    
    /**
     * Obter listeners de um evento
     */
    public static function getListeners($event) {
        return self::$listeners[$event] ?? [];
    }
    
    /**
     * Verificar se evento tem listeners
     */
    public static function hasListeners($event) {
        return !empty(self::$listeners[$event]);
    }
}

/**
 * Event base
 */
abstract class Event {
    protected $data;
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function __get($key) {
        return $this->data[$key] ?? null;
    }
}

/**
 * Eventos específicos
 */
class UserRegisteredEvent extends Event {
    public function __construct($user) {
        parent::__construct(['user' => $user]);
    }
    
    public function getUser() {
        return $this->data['user'];
    }
}

class UserLoggedInEvent extends Event {
    public function __construct($user) {
        parent::__construct(['user' => $user]);
    }
    
    public function getUser() {
        return $this->data['user'];
    }
}

class VillageCreatedEvent extends Event {
    public function __construct($village) {
        parent::__construct(['village' => $village]);
    }
    
    public function getVillage() {
        return $this->data['village'];
    }
}

class ResourceUpdatedEvent extends Event {
    public function __construct($village, $resources) {
        parent::__construct(['village' => $village, 'resources' => $resources]);
    }
    
    public function getVillage() {
        return $this->data['village'];
    }
    
    public function getResources() {
        return $this->data['resources'];
    }
}

/**
 * Listeners
 */
class SendWelcomeEmailListener {
    public function handle($data) {
        $user = $data['user'];
        
        LogHelper::info('Email de boas-vindas enviado', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
        
        // TODO: Implementar envio de email
    }
}

class LogUserActivityListener {
    public function handle($data) {
        $user = $data['user'];
        
        LogHelper::info('Atividade do usuário registrada', [
            'user_id' => $user->id,
            'username' => $user->username,
            'timestamp' => time()
        ]);
    }
}

class UpdateVillageStatsListener {
    public function handle($data) {
        $village = $data['village'];
        
        // Atualizar estatísticas da aldeia
        $village->population = $village->getPopulation();
        $village->save();
        
        LogHelper::debug('Estatísticas da aldeia atualizadas', [
            'village_id' => $village->id,
            'population' => $village->population
        ]);
    }
}
