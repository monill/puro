<?php

declare(strict_types=1);

namespace App\Core;

abstract class ApiResource
{
    protected mixed $resource;
    protected array $with = [];
    protected array $additional = [];
    protected static array $defaultIncludes = [];

    public function __construct(mixed $resource)
    {
        $this->resource = $resource;
    }

    public static function make(mixed $resource): static
    {
        return new static($resource);
    }

    public static function collection(array $resources): array
    {
        return array_map(fn($resource) => static::make($resource)->resolve(), $resources);
    }

    public function with(array $includes): static
    {
        $this->with = array_merge($this->with, $includes);
        return $this;
    }

    public function additional(array $data): static
    {
        $this->additional = array_merge($this->additional, $data);
        return $this;
    }

    public function resolve(): array
    {
        $data = $this->toArray($this->resource);
        
        // Apply includes
        foreach ($this->with as $include) {
            $data = array_merge($data, $this->include($include, $this->resource));
        }
        
        // Add additional data
        if (!empty($this->additional)) {
            $data = array_merge($data, $this->additional);
        }
        
        return $data;
    }

    abstract protected function toArray(mixed $resource): array;

    protected function include(string $include, mixed $resource): array
    {
        $method = 'include' . ucfirst($include);
        
        if (method_exists($this, $method)) {
            return [$include => $this->$method($resource)];
        }
        
        return [];
    }

    // Common include methods
    protected function includeTimestamps(mixed $resource): array
    {
        if (!is_object($resource)) {
            return [];
        }

        return [
            'created_at' => $resource->created_at ?? null,
            'updated_at' => $resource->updated_at ?? null,
        ];
    }

    protected function includeDeletedAt(mixed $resource): array
    {
        if (!is_object($resource)) {
            return [];
        }

        return [
            'deleted_at' => $resource->deleted_at ?? null,
        ];
    }

    protected function includeRelations(mixed $resource): array
    {
        if (!is_object($resource)) {
            return [];
        }

        $relations = [];
        
        // Try to get common relationship properties
        $commonRelations = ['user', 'author', 'category', 'tags', 'comments', 'roles'];
        
        foreach ($commonRelations as $relation) {
            if (isset($resource->$relation)) {
                $relations[$relation] = $resource->$relation;
            }
        }
        
        return ['relations' => $relations];
    }

    // JSON API specific methods
    public function toJson(int $options = 0): string
    {
        return json_encode($this->resolve(), $options);
    }

    public function toResponse(int $status = 200): Response
    {
        $response = new Response();
        return $response->json($this->resolve(), $status);
    }

    // Pagination support
    public static function paginate(array $items, int $total, int $perPage, int $currentPage): array
    {
        return [
            'data' => static::collection($items),
            'meta' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'from' => ($currentPage - 1) * $perPage + 1,
                'to' => min($currentPage * $perPage, $total),
                'has_more' => $currentPage < ceil($total / $perPage),
            ],
            'links' => [
                'first' => $currentPage > 1 ? '?page=1' : null,
                'last' => '?page=' . ceil($total / $perPage),
                'prev' => $currentPage > 1 ? '?page=' . ($currentPage - 1) : null,
                'next' => $currentPage < ceil($total / $perPage) ? '?page=' . ($currentPage + 1) : null,
            ],
        ];
    }

    // Error responses
    public static function error(string $message, int $status = 400, array $meta = []): Response
    {
        $response = new Response();
        return $response->json([
            'error' => true,
            'message' => $message,
            'meta' => $meta,
        ], $status);
    }

    public static function validationErrors(array $errors, string $message = 'Validation failed'): Response
    {
        $response = new Response();
        return $response->json([
            'error' => true,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    public static function notFound(string $message = 'Resource not found'): Response
    {
        $response = new Response();
        return $response->json([
            'error' => true,
            'message' => $message,
        ], 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): Response
    {
        $response = new Response();
        return $response->json([
            'error' => true,
            'message' => $message,
        ], 401);
    }

    public static function forbidden(string $message = 'Forbidden'): Response
    {
        $response = new Response();
        return $response->json([
            'error' => true,
            'message' => $message,
        ], 403);
    }

    public static function serverError(string $message = 'Internal server error'): Response
    {
        $response = new Response();
        return $response->json([
            'error' => true,
            'message' => $message,
        ], 500);
    }

    // Success responses
    public static function success(mixed $data = null, string $message = 'Success'): Response
    {
        $response = new Response();
        return $response->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function created(mixed $data = null, string $message = 'Resource created'): Response
    {
        $response = new Response();
        return $response->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 201);
    }

    public static function updated(mixed $data = null, string $message = 'Resource updated'): Response
    {
        $response = new Response();
        return $response->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function deleted(string $message = 'Resource deleted'): Response
    {
        $response = new Response();
        return $response->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    // Utility methods
    protected function formatDate(?string $date): ?string
    {
        return $date ? date('c', strtotime($date)) : null;
    }

    protected function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        return number_format($amount, 2) . ' ' . $currency;
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        
        [$name, $domain] = $parts;
        $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
        
        return $maskedName . '@' . $domain;
    }

    protected function maskPhone(string $phone): string
    {
        // Simple phone masking - adjust based on your format
        if (strlen($phone) <= 4) {
            return $phone;
        }
        
        return substr($phone, 0, 2) . str_repeat('*', strlen($phone) - 4) . substr($phone, -2);
    }

    // Filtering and sorting
    protected function filterFields(array $data, array $fields): array
    {
        return array_intersect_key($data, array_flip($fields));
    }

    protected function exceptFields(array $data, array $fields): array
    {
        return array_diff_key($data, array_flip($fields));
    }

    protected function onlyFields(array $data, array $fields): array
    {
        return array_filter($data, function($key) use ($fields) {
            return in_array($key, $fields);
        }, ARRAY_FILTER_USE_KEY);
    }
}
