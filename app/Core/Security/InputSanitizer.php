<?php

declare(strict_types=1);

namespace App\Core\Security;

class InputSanitizer
{
    private static array $config = [
        'encoding' => 'UTF-8',
        'strip_tags' => true,
        'remove_comments' => true,
        'normalize_whitespace' => true,
        'allowed_tags' => [],
        'allowed_attributes' => [],
        'max_length' => null,
        'custom_filters' => [],
    ];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function clean(string $input): string
    {
        if (empty($input)) {
            return $input;
        }

        // Convert to UTF-8
        $input = self::convertToUtf8($input);

        // Remove comments
        if (self::$config['remove_comments']) {
            $input = self::removeComments($input);
        }

        // Strip HTML tags
        if (self::$config['strip_tags']) {
            $input = self::stripTags($input);
        }

        // Normalize whitespace
        if (self::$config['normalize_whitespace']) {
            $input = self::normalizeWhitespace($input);
        }

        // Apply custom filters
        $input = self::applyCustomFilters($input);

        // Check max length
        if (self::$config['max_length'] && strlen($input) > self::$config['max_length']) {
            $input = substr($input, 0, self::$config['max_length']);
        }

        return $input;
    }

    public static function escape(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, self::$config['encoding']);
    }

    public static function sanitizeEmail(string $email): string
    {
        $email = strtolower(trim($email));
        $email = self::clean($email);
        
        // Remove any characters that aren't valid in email addresses
        $email = preg_replace('/[^a-z0-9@._-]/', '', $email);
        
        return $email;
    }

    public static function sanitizeUrl(string $url): string
    {
        $url = trim($url);
        
        // Remove dangerous characters
        $url = preg_replace('/[<>"\']/', '', $url);
        
        // Ensure URL has a scheme
        if (!preg_match('/^https?:\/\//', $url) && !preg_match('/^\/\//', $url)) {
            $url = 'https://' . $url;
        }
        
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    public static function sanitizePhone(string $phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Remove country code if present (assuming Brazilian numbers)
        if (strlen($phone) === 13 && str_starts_with($phone, '55')) {
            $phone = substr($phone, 2);
        }
        
        return $phone;
    }

    public static function sanitizeCpf(string $cpf): string
    {
        // Remove all non-digit characters
        $cpf = preg_replace('/\D/', '', $cpf);
        
        // Ensure 11 digits
        $cpf = str_pad($cpf, 11, '0', STR_PAD_RIGHT);
        
        return substr($cpf, 0, 11);
    }

    public static function sanitizeCnpj(string $cnpj): string
    {
        // Remove all non-digit characters
        $cnpj = preg_replace('/\D/', '', $cnpj);
        
        // Ensure 14 digits
        $cnpj = str_pad($cnpj, 14, '0', STR_PAD_RIGHT);
        
        return substr($cnpj, 0, 14);
    }

    public static function sanitizeNumeric(string $input): string
    {
        return preg_replace('/[^0-9.-]/', '', $input);
    }

    public static function sanitizeAlpha(string $input): string
    {
        return preg_replace('/[^a-zA-Z]/', '', $input);
    }

    public static function sanitizeAlphaNumeric(string $input): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', $input);
    }

    public static function sanitizeSlug(string $input): string
    {
        $input = self::clean($input);
        $input = strtolower($input);
        
        // Replace spaces and special characters with hyphens
        $input = preg_replace('/[^a-z0-9]+/', '-', $input);
        
        // Remove leading/trailing hyphens
        $input = trim($input, '-');
        
        return $input;
    }

    public static function sanitizeFilename(string $filename): string
    {
        $filename = self::clean($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[<>:"\/\\|?*]/', '', $filename);
        
        // Replace spaces with underscores
        $filename = preg_replace('/\s+/', '_', $filename);
        
        // Remove leading/trailing underscores
        $filename = trim($filename, '_');
        
        return $filename;
    }

    public static function sanitizeHtml(string $html): string
    {
        // Basic HTML sanitization
        $html = self::clean($html);
        
        // Allow specific tags if configured
        if (!empty(self::$config['allowed_tags'])) {
            $allowedTags = '<' . implode('><', self::$config['allowed_tags']) . '>';
            $html = strip_tags($html, $allowedTags);
        }
        
        return $html;
    }

    public static function sanitizeJson(string $json): string
    {
        $json = self::clean($json);
        
        // Remove any dangerous characters
        $json = preg_replace('/[<>"\']/', '', $json);
        
        return $json;
    }

    public static function sanitizeXml(string $xml): string
    {
        $xml = self::clean($xml);
        
        // Remove XML comments
        $xml = preg_replace('/<!--.*?-->/', '', $xml);
        
        return $xml;
    }

    public static function sanitizeSql(string $sql): string
    {
        // Basic SQL injection prevention
        $sql = self::clean($sql);
        
        // Remove dangerous SQL keywords
        $dangerousKeywords = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'CREATE', 'ALTER'];
        foreach ($dangerousKeywords as $keyword) {
            $sql = preg_replace('/\b' . $keyword . '\b/i', '', $sql);
        }
        
        return $sql;
    }

    public static function sanitizeArray(array $data): array
    {
        return array_map([self::class, 'clean'], $data);
    }

    public static function sanitizeObject(object $object): object
    {
        $reflection = new \ReflectionClass($object);
        $properties = $reflection->getProperties();
        
        foreach ($properties as $property) {
            if ($property->isPublic()) {
                $value = $property->getValue($object);
                if (is_string($value)) {
                    $property->setValue($object, self::clean($value));
                }
            }
        }
        
        return $object;
    }

    public static function sanitizeUpload(array $file): array
    {
        if (!isset($file['name'])) {
            return $file;
        }
        
        // Sanitize filename
        $file['name'] = self::sanitizeFilename($file['name']);
        
        // Validate file type
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException('File type not allowed');
        }
        
        return $file;
    }

    public static function validateEmail(string $email): bool
    {
        $email = self::sanitizeEmail($email);
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateUrl(string $url): bool
    {
        $url = self::sanitizeUrl($url);
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function validateCpf(string $cpf): bool
    {
        $cpf = self::sanitizeCpf($cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Check if all digits are the same
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        
        // Calculate first digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if ((int) $cpf[9] !== $digit1) {
            return false;
        }
        
        // Calculate second digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return (int) $cpf[10] === $digit2;
    }

    public static function validateCnpj(string $cnpj): bool
    {
        $cnpj = self::sanitizeCnpj($cnpj);
        
        if (strlen($cnpj) !== 14) {
            return false;
        }
        
        // Check if all digits are the same
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }
        
        // Calculate first digit
        $sum = 0;
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if ((int) $cnpj[12] !== $digit1) {
            return false;
        }
        
        // Calculate second digit
        $sum = 0;
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return (int) $cnpj[13] === $digit2;
    }

    public static function mask(string $input, string $type): string
    {
        switch ($type) {
            case 'email':
                return self::maskEmail($input);
            case 'phone':
                return self::maskPhone($input);
            case 'cpf':
                return self::maskCpf($input);
            case 'cnpj':
                return self::maskCnpj($input);
            case 'credit_card':
                return self::maskCreditCard($input);
            default:
                return $input;
        }
    }

    private static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        
        [$name, $domain] = $parts;
        $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
        
        return $maskedName . '@' . $domain;
    }

    private static function maskPhone(string $phone): string
    {
        if (strlen($phone) <= 4) {
            return $phone;
        }
        
        return substr($phone, 0, 2) . str_repeat('*', strlen($phone) - 4) . substr($phone, -2);
    }

    private static function maskCpf(string $cpf): string
    {
        if (strlen($cpf) !== 11) {
            return $cpf;
        }
        
        return substr($cpf, 0, 3) . '.' . str_repeat('*', 3) . '.' . str_repeat('*', 3) . '-' . substr($cpf, -2);
    }

    private static function maskCnpj(string $cnpj): string
    {
        if (strlen($cnpj) !== 14) {
            return $cnpj;
        }
        
        return substr($cnpj, 0, 2) . '.' . str_repeat('*', 3) . '.' . str_repeat('*', 3) . '/' . str_repeat('*', 4) . '-' . substr($cnpj, -2);
    }

    private static function maskCreditCard(string $card): string
    {
        if (strlen($card) < 4) {
            return $card;
        }
        
        return str_repeat('*', strlen($card) - 4) . substr($card, -4);
    }

    private static function convertToUtf8(string $input): string
    {
        if (mb_check_encoding($input, self::$config['encoding'])) {
            return $input;
        }
        
        return mb_convert_encoding($input, self::$config['encoding'], 'auto');
    }

    private static function removeComments(string $input): string
    {
        // Remove HTML comments
        $input = preg_replace('/<!--.*?-->/', '', $input);
        
        // Remove CSS comments
        $input = preg_replace('/\/\*.*?\*\//', '', $input);
        
        // Remove JavaScript comments
        $input = preg_replace('/\/\/.*$/m', '', $input);
        
        return $input;
    }

    private static function stripTags(string $input): string
    {
        if (empty(self::$config['allowed_tags'])) {
            return strip_tags($input);
        }
        
        $allowedTags = '<' . implode('><', self::$config['allowed_tags']) . '>';
        return strip_tags($input, $allowedTags);
    }

    private static function normalizeWhitespace(string $input): string
    {
        // Replace multiple spaces with single space
        $input = preg_replace('/\s+/', ' ', $input);
        
        // Trim leading/trailing whitespace
        return trim($input);
    }

    private static function applyCustomFilters(string $input): string
    {
        foreach (self::$config['custom_filters'] as $filter) {
            $input = $filter($input);
        }
        
        return $input;
    }

    public static function addCustomFilter(callable $filter): void
    {
        self::$config['custom_filters'][] = $filter;
    }

    public static function removeCustomFilter(callable $filter): void
    {
        self::$config['custom_filters'] = array_filter(
            self::$config['custom_filters'],
            fn($f) => $f !== $filter
        );
    }

    public static function clearCustomFilters(): void
    {
        self::$config['custom_filters'] = [];
    }

    public static function getConfig(): array
    {
        return self::$config;
    }

    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    public static function resetConfig(): void
    {
        self::$config = [
            'encoding' => 'UTF-8',
            'strip_tags' => true,
            'remove_comments' => true,
            'normalize_whitespace' => true,
            'allowed_tags' => [],
            'allowed_attributes' => [],
            'max_length' => null,
            'custom_filters' => [],
        ];
    }
}
