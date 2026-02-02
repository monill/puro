# 🛡️ Sistema de Segurança e Filas - Guia de Implementação

## 📋 Componentes Implementados

### ✅ 1. RateLimiter
**Para que serve:** Limita tentativas de requisições por IP

**Exemplo prático:**
```php
// IP só pode fazer 5 tentativas de login por minuto
if (!RateLimiter::attempt('192.168.1.1:login', 5, 1)) {
    // Bloqueia - excedeu limite
    BlackListManager::add('192.168.1.1', 30, 'Brute force');
}
```

### ✅ 2. BlackListManager  
**Para que serve:** Gerencia IPs bloqueados

**Exemplo prático:**
```php
// Bloquear IP por 1 hora
BlackListManager::add('192.168.1.1', 60, 'Security violation');

// Verificar se está bloqueado
if (BlackListManager::isBlacklisted('192.168.1.1')) {
    // Negar acesso
}
```

### ✅ 3. SecurityMiddleware
**Para que serve:** Protege todas as requisições

**Proteções:**
- ✅ Verificação de blacklist
- ✅ Rate limiting automático  
- ✅ CSRF protection
- ✅ XSS protection
- ✅ Detecção de bots

### ✅ 4. Queue System
**Para que serve:** Processos em background

**Exemplo prático:**
```php
// Incrementar recursos a cada minuto
queue_push('IncrementResourcesJob', [
    'village_id' => 123,
    'amount' => 10
]);

// Game tick principal
queue_later(60, 'GameTickJob', ['tick_number' => 1]);
```

---

## 🚀 Como Usar

### 1. Adicionar SecurityMiddleware ao Front Controller

No seu `public/index.php`:

```php
// Depois do autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Antes das rotas
use App\Middleware\SecurityMiddleware;

// Middleware global
$security = new SecurityMiddleware($request, $response);
$blockResponse = $security->handle();

if ($blockResponse) {
    // Requisição bloqueada - retorna resposta de erro
    $blockResponse->send();
    exit;
}

// Continua com as rotas normalmente...
```

### 2. Adicionar CSRF aos Formulários

```php
<form method="POST" action="/login">
    <?= csrf_field() ?>
    <input type="text" name="username" placeholder="Username">
    <input type="password" name="password" placeholder="Password">
    <button type="submit">Login</button>
</form>
```

### 3. Usar Queue System

```php
// Em qualquer controller ou helper:

// Job imediato
queue_push('IncrementResourcesJob', [
    'village_id' => $villageId,
    'resource_type' => 'wood',
    'amount' => 50
]);

// Job agendado (5 minutos)
queue_later(300, 'SendEmailJob', [
    'to' => 'player@example.com',
    'subject' => 'Your village was attacked!'
]);

// Job de alta prioridade
queue_push('ProcessTroopMovementJob', $movementData, 'high');
```

### 4. Processar Filas (Cron Job)

Criar script `worker.php`:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

// Processa até 10 jobs
$result = \App\Queue\Queue::work(10);

echo "Processed: {$result['processed']}\n";
echo "Failed: {$result['failed']}\n";
echo "Remaining: {$result['remaining']}\n";
```

Adicionar ao crontab:
```bash
# Executa a cada minuto
* * * * * php /path/to/puro/worker.php

# Ou a cada 30 segundos para game ticks
*/30 * * * * php /path/to/puro/worker.php
```

---

## 🎮 Jobs para Game Server

### 1. IncrementResourcesJob
```php
// Executa a cada minuto para cada aldeia
queue_push('IncrementResourcesJob', [
    'village_id' => 123,
    'resource_type' => 'wood',  // wood, clay, iron, crop
    'amount' => 10              // Baseado em nível dos campos
]);
```

### 2. GameTickJob  
```php
// Job principal que orquestra tudo
queue_later(60, 'GameTickJob', [
    'tick_number' => $currentTick
]);
```

### 3. ProcessTroopMovementJob
```php
// Processa movimentações quando chega o horário
queue_push('ProcessTroopMovementJob', [
    'movement_id' => 456
]);
```

---

## 🛡️ Configurações de Segurança

### Rate Limits por Padrão:
- **Login:** 5 tentativas / 5 minutos
- **Registro:** 3 tentativas / 10 minutos  
- **API:** 100 requisições / 1 minuto
- **Páginas:** 60 requisições / 1 minuto

### BlackList Automática:
- **Brute Force:** 5 minutos no primeiro bloqueio
- **DDoS Suspeito:** 1 minuto
- **Excesso Rate Limit:** 30 minutos
- **Violação Grave:** 24 horas

---

## 📊 Monitoramento

### Verificar Status das Filas:
```php
$stats = \App\Queue\Queue::getStats();
/*
[
    'total' => 15,
    'pending' => 8, 
    'processing' => 2,
    'completed' => 4,
    'failed' => 1
]
*/
```

### Verificar IPs Bloqueados:
```php
$info = \App\Middleware\BlackListManager::getBlockInfo('192.168.1.1');
/*
[
    'ip' => '192.168.1.1',
    'reason' => 'Brute force',
    'blocked_at' => '2024-01-15 10:30:00',
    'expires_at' => '2024-01-15 10:35:00', 
    'remaining_minutes' => 3
]
*/
```

---

## 🎯 Exemplos Práticos

### Login com Proteção:
```php
class AuthController {
    public function login($request) {
        $ip = $request->getIp();
        
        // Rate limiting automático via middleware
        
        // Se login falhar, incrementar contador
        if (!$this->validateLogin($request)) {
            // Middleware já cuidou do rate limit
            
            // Se falhar muitas vezes, pode bloquear manualmente
            $attempts = $this->getFailedAttempts($ip);
            if ($attempts >= 10) {
                blacklist_ip($ip, 60, 'Excessive login failures');
            }
            
            return $this->error('Invalid credentials');
        }
        
        // Login sucesso - reset rate limit
        RateLimiter::reset("$ip:login");
        
        return $this->success('Login successful');
    }
}
```

### Game Loop com Filas:
```php
class GameController {
    public function startGameTick() {
        // Job principal que orquestra tudo
        queue_later(60, 'GameTickJob', [
            'tick_number' => time() / 60
        ]);
        
        // Jobs específicos para recursos
        $villages = $this->getAllVillages();
        foreach ($villages as $village) {
            queue_push('IncrementResourcesJob', [
                'village_id' => $village['id'],
                'resource_type' => 'wood',
                'amount' => $this->calculateWoodProduction($village)
            ]);
        }
    }
}
```

---

## 🚀 Próximos Passos

1. **Integrar SecurityMiddleware** ao front controller
2. **Adicionar CSRF** aos formulários existentes  
3. **Criar Jobs** específicos para seu jogo
4. **Configurar cron job** para processar filas
5. **Monitorar** logs e estatísticas

**Sistema pronto para produção!** 🎉

**Performance e segurança garantidas!** 🛡️⚡
