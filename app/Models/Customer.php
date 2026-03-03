<?php

declare(strict_types=1);

namespace App\Models;

class Customer
{
    public ?int $id;
    public string $name;
    public string $email;
    public string $password;
    public ?string $phone;
    public ?string $address;
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
        $this->phone = $data['phone'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->remember_token = $data['remember_token'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public static function findByEmail(string $email): ?self
    {
        // Simulação - substitua com consulta real ao banco
        $customers = self::all();
        
        foreach ($customers as $customer) {
            if ($customer->email === $email) {
                return $customer;
            }
        }
        
        return null;
    }

    public static function find(int $id): ?self
    {
        // Simulação - substitua com consulta real ao banco
        $customers = self::all();
        
        foreach ($customers as $customer) {
            if ($customer->id === $id) {
                return $customer;
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
                'name' => 'Customer User',
                'email' => 'customer@example.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'phone' => '(11) 99999-9999',
                'address' => 'Rua Example, 123',
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

    public function isCustomer(): bool
    {
        return true; // Lógica específica para customer
    }

    public function can(string $ability): bool
    {
        // Implemente suas permissões aqui
        $customerPermissions = [
            'view_profile',
            'update_profile',
            'view_orders',
            'create_order',
        ];
        
        return in_array($ability, $customerPermissions);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
