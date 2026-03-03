<?php

declare(strict_types=1);

namespace App\Models;

class User
{
    public ?int $id;
    public string $name;
    public string $email;
    public string $password;
    public ?string $remember_token;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->remember_token = $data['remember_token'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public static function findByEmail(string $email): ?self
    {
        // Simulação - substitua com consulta real ao banco
        $users = self::all();
        
        foreach ($users as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }
        
        return null;
    }

    public static function find(int $id): ?self
    {
        // Simulação - substitua com consulta real ao banco
        $users = self::all();
        
        foreach ($users as $user) {
            if ($user->id === $id) {
                return $user;
            }
        }
        
        return null;
    }

    public static function all(): array
    {
        // Simulação - substitua com consulta real ao banco
        return [
            new self([
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]),
        ];
    }

    public function save(): bool
    {
        // Simulação - implemente save real ao banco
        $this->updated_at = date('Y-m-d H:i:s');
        
        if (!$this->id) {
            $this->id = rand(1, 1000);
            $this->created_at = date('Y-m-d H:i:s');
        }
        
        return true;
    }

    public function setRememberToken(string $token): void
    {
        $this->remember_token = $token;
        $this->save();
    }

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function isAdmin(): bool
    {
        return true; // Lógica específica para admin
    }

    public function can(string $ability): bool
    {
        // Implemente suas permissões aqui
        return $this->isAdmin();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
