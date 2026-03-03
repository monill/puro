<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use App\Core\Log;

abstract class Seeder
{
    protected PDO $connection;
    protected array $data = [];

    public function __construct()
    {
        $this->connection = Connection::getDefault();
    }

    abstract public function run(): void;

    public function seed(): bool
    {
        try {
            $this->run();
            Log::info('Seeder executed successfully', ['seeder' => static::class]);
            return true;
        } catch (\Exception $e) {
            Log::error('Seeder failed', [
                'seeder' => static::class,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function insert(string $table, array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        $columns = array_keys($data[0]);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";
        
        $statement = $this->connection->prepare($sql);
        $inserted = 0;

        foreach ($data as $row) {
            if ($statement->execute(array_values($row))) {
                $inserted++;
            }
        }

        return $inserted;
    }

    protected function insertOrIgnore(string $table, array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        $columns = array_keys($data[0]);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT IGNORE INTO {$table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";
        
        $statement = $this->connection->prepare($sql);
        $inserted = 0;

        foreach ($data as $row) {
            if ($statement->execute(array_values($row))) {
                $inserted++;
            }
        }

        return $inserted;
    }

    protected function truncate(string $table): void
    {
        $sql = "TRUNCATE TABLE {$table}";
        $this->connection->exec($sql);
    }

    protected function delete(string $table, array $conditions = []): int
    {
        $sql = "DELETE FROM {$table}";
        $bindings = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "{$column} = ?";
                $bindings[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($bindings);

        return $statement->rowCount();
    }

    protected function call(string $seederClass): void
    {
        if (class_exists($seederClass)) {
            $seeder = new $seederClass();
            $seeder->seed();
        }
    }

    protected function createUsers(int $count = 10): array
    {
        $users = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $users[] = [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        
        return $users;
    }

    protected function createCustomers(int $count = 10): array
    {
        $customers = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $customers[] = [
                'name' => "Customer {$i}",
                'email' => "customer{$i}@example.com",
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'phone' => "(11) 9" . str_pad((string)$i, 8, '0', STR_PAD_LEFT),
                'address' => "Address {$i}, Street Example",
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        
        return $customers;
    }

    protected function createProducts(int $count = 20): array
    {
        $products = [];
        $categories = ['Electronics', 'Clothing', 'Books', 'Home', 'Sports'];
        
        for ($i = 1; $i <= $count; $i++) {
            $products[] = [
                'name' => "Product {$i}",
                'description' => "Description for product {$i}",
                'price' => rand(10, 1000) + (rand(0, 99) / 100),
                'category' => $categories[array_rand($categories)],
                'stock' => rand(0, 100),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        
        return $products;
    }

    protected function createOrders(int $count = 50): array
    {
        $orders = [];
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        for ($i = 1; $i <= $count; $i++) {
            $orders[] = [
                'customer_id' => rand(1, 10),
                'total' => rand(50, 500) + (rand(0, 99) / 100),
                'status' => $statuses[array_rand($statuses)],
                'order_date' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        
        return $orders;
    }

    protected function faker(): \Faker\Generator
    {
        if (!class_exists('\Faker\Generator')) {
            throw new \RuntimeException('Faker library not found. Please install fzaninotto/faker');
        }
        
        return \Faker\Factory::create();
    }

    protected function randomDate(string $startDate = '-1 year', string $endDate = 'now'): string
    {
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        $random = mt_rand($start, $end);
        
        return date('Y-m-d H:i:s', $random);
    }

    protected function randomElement(array $array): mixed
    {
        return $array[array_rand($array)];
    }

    protected function randomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }

    protected function randomNumber(int $min = 0, int $max = 100): int
    {
        return rand($min, $max);
    }

    protected function randomFloat(float $min = 0.0, float $max = 100.0): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    protected function randomEmail(): string
    {
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'example.com', 'test.com'];
        $username = $this->randomString(8);
        $domain = $this->randomElement($domains);
        
        return "{$username}@{$domain}";
    }

    protected function randomPhone(): string
    {
        $ddd = ['11', '21', '31', '41', '51', '61', '71', '81', '91'];
        $selectedDdd = $this->randomElement($ddd);
        $number = '9' . $this->randomString(8);
        
        return "({$selectedDdd}) {$number}";
    }

    protected function randomCpf(): string
    {
        $numbers = [];
        
        for ($i = 0; $i < 9; $i++) {
            $numbers[] = $this->randomNumber(0, 9);
        }
        
        // Calculate first digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $numbers[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $numbers[] = $remainder < 2 ? 0 : 11 - $remainder;
        
        // Calculate second digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $numbers[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $numbers[] = $remainder < 2 ? 0 : 11 - $remainder;
        
        return implode('', $numbers);
    }

    protected function randomCnpj(): string
    {
        $numbers = [];
        
        // Generate first 8 digits
        for ($i = 0; $i < 8; $i++) {
            $numbers[] = $this->randomNumber(0, 9);
        }
        
        // Add 0001 (branch)
        $numbers = array_merge($numbers, [0, 0, 0, 1]);
        
        // Calculate first digit
        $sum = 0;
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $sum += $numbers[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $numbers[] = $remainder < 2 ? 0 : 11 - $remainder;
        
        // Calculate second digit
        $sum = 0;
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $sum += $numbers[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $numbers[] = $remainder < 2 ? 0 : 11 - $remainder;
        
        return implode('', $numbers);
    }

    public static function runAll(string $seedersPath): array
    {
        $seeders = static::getAllSeeders($seedersPath);
        $executed = [];
        $failed = [];

        foreach ($seeders as $seeder) {
            try {
                require_once $seedersPath . '/' . $seeder . '.php';
                
                if (class_exists($seeder)) {
                    $instance = new $seeder();
                    $instance->seed();
                    $executed[] = $seeder;
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'seeder' => $seeder,
                    'error' => $e->getMessage()
                ];
                Log::error('Seeder failed', [
                    'seeder' => $seeder,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'executed' => $executed,
            'failed' => $failed
        ];
    }

    private static function getAllSeeders(string $seedersPath): array
    {
        if (!is_dir($seedersPath)) {
            return [];
        }

        $files = glob($seedersPath . '/*Seeder.php');
        $seeders = [];

        foreach ($files as $file) {
            $seeder = pathinfo($file, PATHINFO_FILENAME);
            $seeders[] = $seeder;
        }

        return $seeders;
    }
}
