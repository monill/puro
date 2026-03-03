<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Validation;
use App\Core\Response;

class RequestValidator
{
    private array $rules;
    private array $messages;
    private array $data;
    private array $errors = [];
    private array $customMessages = [];

    public function __construct(array $rules, array $customMessages = [])
    {
        $this->rules = $rules;
        $this->customMessages = $customMessages;
    }

    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        $validator = new self($rules, $customMessages);
        $validator->setData($data);
        return $validator;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function validate(): bool
    {
        $this->errors = [];
        
        foreach ($this->rules as $field => $fieldRules) {
            $this->validateField($field, $fieldRules);
        }
        
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->validate();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function getErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function getFirstErrors(): array
    {
        $firstErrors = [];
        
        foreach ($this->errors as $field => $errors) {
            $firstErrors[$field] = $errors[0] ?? null;
        }
        
        return $firstErrors;
    }

    public function validated(): array
    {
        if ($this->fails()) {
            throw new \RuntimeException('Validation failed');
        }
        
        return $this->data;
    }

    public function safe(): array
    {
        $safe = $this->data;
        
        foreach ($this->errors as $field => $errors) {
            unset($safe[$field]);
        }
        
        return $safe;
    }

    private function validateField(string $field, array|string $rules): void
    {
        $value = $this->getFieldValue($field);
        $fieldRules = is_array($rules) ? $rules : explode('|', $rules);
        
        foreach ($fieldRules as $rule) {
            if ($this->validateRule($field, $value, $rule) === false) {
                break; // Stop on first error for this field
            }
        }
    }

    private function validateRule(string $field, mixed $value, string $rule): bool
    {
        $parameters = [];
        
        if (str_contains($rule, ':')) {
            [$rule, $parameterString] = explode(':', $rule, 2);
            $parameters = explode(',', $parameterString);
        }

        return match ($rule) {
            'required' => $this->validateRequired($field, $value),
            'nullable' => true, // Always passes, handled by other rules
            'string' => $this->validateString($value),
            'integer' => $this->validateInteger($value),
            'numeric' => $this->validateNumeric($value),
            'float' => $this->validateFloat($value),
            'boolean' => $this->validateBoolean($value),
            'array' => $this->validateArray($value),
            'email' => $this->validateEmail($value),
            'url' => $this->validateUrl($value),
            'ip' => $this->validateIp($value),
            'min' => $this->validateMin($field, $value, $parameters[0] ?? 0),
            'max' => $this->validateMax($field, $value, $parameters[0] ?? 0),
            'between' => $this->validateBetween($field, $value, $parameters[0] ?? 0, $parameters[1] ?? 0),
            'size' => $this->validateSize($field, $value, $parameters[0] ?? 0),
            'alpha' => $this->validateAlpha($value),
            'alpha_num' => $this->validateAlphaNum($value),
            'alpha_dash' => $this->validateAlphaDash($value),
            'regex' => $this->validateRegex($value, $parameters[0] ?? ''),
            'in' => $this->validateIn($value, $parameters),
            'not_in' => $this->validateNotIn($value, $parameters),
            'unique' => $this->validateUnique($field, $value, $parameters[0] ?? '', $parameters[1] ?? 'id'),
            'exists' => $this->validateExists($field, $value, $parameters[0] ?? '', $parameters[1] ?? 'id'),
            'confirmed' => $this->validateConfirmed($field, $value),
            'same' => $this->validateSame($field, $value, $parameters[0] ?? ''),
            'different' => $this->validateDifferent($field, $value, $parameters[0] ?? ''),
            'date' => $this->validateDate($value),
            'date_format' => $this->validateDateFormat($value, $parameters[0] ?? 'Y-m-d'),
            'before' => $this->validateBefore($field, $value, $parameters[0] ?? ''),
            'after' => $this->validateAfter($field, $value, $parameters[0] ?? ''),
            'file' => $this->validateFile($value),
            'image' => $this->validateImage($value),
            'mimes' => $this->validateMimes($value, $parameters),
            'max_file' => $this->validateMaxFile($field, $value, $parameters[0] ?? 0),
            'min_file' => $this->validateMinFile($field, $value, $parameters[0] ?? 0),
            'accepted' => $this->validateAccepted($value),
            default => true,
        };
    }

    private function getFieldValue(string $field): mixed
    {
        $keys = explode('.', $field);
        $value = $this->data;
        
        foreach ($keys as $key) {
            if (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }
        
        return $value;
    }

    private function addError(string $field, string $rule, string $message, array $parameters = []): void
    {
        $this->errors[$field][] = $this->formatMessage($message, $field, $rule, $parameters);
    }

    private function formatMessage(string $message, string $field, string $rule, array $parameters): string
    {
        $customKey = $field . '.' . $rule;
        
        if (isset($this->customMessages[$customKey])) {
            $message = $this->customMessages[$customKey];
        } elseif (isset($this->customMessages[$rule])) {
            $message = $this->customMessages[$rule];
        }

        $message = str_replace(':attribute', $field, $message);
        $message = str_replace(':field', $field, $message);

        foreach ($parameters as $i => $param) {
            $message = str_replace(':' . ($i + 1), $param, $message);
        }

        return $message;
    }

    // Validation methods
    private function validateRequired(string $field, mixed $value): bool
    {
        if (is_null($value)) {
            $this->addError($field, 'required', 'The :attribute field is required.');
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            $this->addError($field, 'required', 'The :attribute field is required.');
            return false;
        }

        if (is_array($value) && empty($value)) {
            $this->addError($field, 'required', 'The :attribute field is required.');
            return false;
        }

        return true;
    }

    private function validateString(mixed $value): bool
    {
        return is_string($value);
    }

    private function validateInteger(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function validateNumeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    private function validateFloat(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    private function validateBoolean(mixed $value): bool
    {
        return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false']);
    }

    private function validateArray(mixed $value): bool
    {
        return is_array($value);
    }

    private function validateEmail(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateUrl(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validateIp(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    private function validateMin(string $field, mixed $value, int $min): bool
    {
        if (is_string($value)) {
            if (mb_strlen($value) < $min) {
                $this->addError($field, 'min', "The :attribute must be at least :min characters.", [$min]);
                return false;
            }
        } elseif (is_numeric($value)) {
            if ($value < $min) {
                $this->addError($field, 'min', "The :attribute must be at least :min.", [$min]);
                return false;
            }
        } elseif (is_array($value)) {
            if (count($value) < $min) {
                $this->addError($field, 'min', "The :attribute must have at least :min items.", [$min]);
                return false;
            }
        }

        return true;
    }

    private function validateMax(string $field, mixed $value, int $max): bool
    {
        if (is_string($value)) {
            if (mb_strlen($value) > $max) {
                $this->addError($field, 'max', "The :attribute may not be greater than :max characters.", [$max]);
                return false;
            }
        } elseif (is_numeric($value)) {
            if ($value > $max) {
                $this->addError($field, 'max', "The :attribute may not be greater than :max.", [$max]);
                return false;
            }
        } elseif (is_array($value)) {
            if (count($value) > $max) {
                $this->addError($field, 'max', "The :attribute may not have more than :max items.", [$max]);
                return false;
            }
        }

        return true;
    }

    private function validateBetween(string $field, mixed $value, int $min, int $max): bool
    {
        if (is_string($value)) {
            $length = mb_strlen($value);
            if ($length < $min || $length > $max) {
                $this->addError($field, 'between', "The :attribute must be between :min and :max characters.", [$min, $max]);
                return false;
            }
        } elseif (is_numeric($value)) {
            if ($value < $min || $value > $max) {
                $this->addError($field, 'between', "The :attribute must be between :min and :max.", [$min, $max]);
                return false;
            }
        }

        return true;
    }

    private function validateSize(string $field, mixed $value, int $size): bool
    {
        if (is_string($value)) {
            if (mb_strlen($value) !== $size) {
                $this->addError($field, 'size', "The :attribute must be :size characters.", [$size]);
                return false;
            }
        } elseif (is_numeric($value)) {
            if ($value != $size) {
                $this->addError($field, 'size', "The :attribute must be :size.", [$size]);
                return false;
            }
        } elseif (is_array($value)) {
            if (count($value) !== $size) {
                $this->addError($field, 'size', "The :attribute must have :size items.", [$size]);
                return false;
            }
        }

        return true;
    }

    private function validateAlpha(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[a-zA-Z]+$/', $value);
    }

    private function validateAlphaNum(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[a-zA-Z0-9]+$/', $value);
    }

    private function validateAlphaDash(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[a-zA-Z0-9_-]+$/', $value);
    }

    private function validateRegex(mixed $value, string $pattern): bool
    {
        return is_string($value) && preg_match($pattern, $value);
    }

    private function validateIn(mixed $value, array $allowed): bool
    {
        return in_array($value, $allowed);
    }

    private function validateNotIn(mixed $value, array $disallowed): bool
    {
        return !in_array($value, $disallowed);
    }

    private function validateUnique(string $field, mixed $value, string $table, string $column = 'id'): bool
    {
        // This would need database access - simplified for now
        return true;
    }

    private function validateExists(string $field, mixed $value, string $table, string $column = 'id'): bool
    {
        // This would need database access - simplified for now
        return true;
    }

    private function validateConfirmed(string $field, mixed $value): bool
    {
        $confirmation = $this->getFieldValue($field . '_confirmation');
        
        if ($value !== $confirmation) {
            $this->addError($field, 'confirmed', 'The :attribute confirmation does not match.');
            return false;
        }

        return true;
    }

    private function validateSame(string $field, mixed $value, string $other): bool
    {
        $otherValue = $this->getFieldValue($other);
        
        if ($value !== $otherValue) {
            $this->addError($field, 'same', "The :attribute and :other must match.", [$other]);
            return false;
        }

        return true;
    }

    private function validateDifferent(string $field, mixed $value, string $other): bool
    {
        $otherValue = $this->getFieldValue($other);
        
        if ($value === $otherValue) {
            $this->addError($field, 'different', "The :attribute and :other must be different.", [$other]);
            return false;
        }

        return true;
    }

    private function validateDate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value;
    }

    private function validateDateFormat(mixed $value, string $format): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $date = \DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value;
    }

    private function validateBefore(string $field, mixed $value, string $date): bool
    {
        $targetDate = \DateTime::createFromFormat('Y-m-d', $date);
        $valueDate = \DateTime::createFromFormat('Y-m-d', $value);
        
        if (!$targetDate || !$valueDate) {
            return true;
        }

        if ($valueDate >= $targetDate) {
            $this->addError($field, 'before', "The :attribute must be a date before :date.", [$date]);
            return false;
        }

        return true;
    }

    private function validateAfter(string $field, mixed $value, string $date): bool
    {
        $targetDate = \DateTime::createFromFormat('Y-m-d', $date);
        $valueDate = \DateTime::createFromFormat('Y-m-d', $value);
        
        if (!$targetDate || !$valueDate) {
            return true;
        }

        if ($valueDate <= $targetDate) {
            $this->addError($field, 'after', "The :attribute must be a date after :date.", [$date]);
            return false;
        }

        return true;
    }

    private function validateFile(mixed $value): bool
    {
        return is_array($value) && isset($value['tmp_name']) && is_uploaded_file($value['tmp_name']);
    }

    private function validateImage(mixed $value): bool
    {
        if (!$this->validateFile($value)) {
            return false;
        }

        $mimeType = $value['type'] ?? '';
        return str_starts_with($mimeType, 'image/');
    }

    private function validateMimes(mixed $value, array $allowed): bool
    {
        if (!$this->validateFile($value)) {
            return false;
        }

        $extension = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));
        return in_array($extension, $allowed);
    }

    private function validateMaxFile(string $field, mixed $value, int $maxSize): bool
    {
        if (!$this->validateFile($value)) {
            return true;
        }

        $size = $value['size'] ?? 0;
        if ($size > $maxSize * 1024) { // Convert KB to bytes
            $this->addError($field, 'max_file', "The :attribute may not be greater than :max kilobytes.", [$maxSize]);
            return false;
        }

        return true;
    }

    private function validateMinFile(string $field, mixed $value, int $minSize): bool
    {
        if (!$this->validateFile($value)) {
            return true;
        }

        $size = $value['size'] ?? 0;
        if ($size < $minSize * 1024) { // Convert KB to bytes
            $this->addError($field, 'min_file', "The :attribute must be at least :min kilobytes.", [$minSize]);
            return false;
        }

        return true;
    }

    private function validateAccepted(mixed $value): bool
    {
        return in_array($value, ['yes', 'on', '1', 1, true, 'true']);
    }

    // Middleware helper
    public static function middleware(array $rules, array $messages = []): callable
    {
        return function($request, $next) use ($rules, $messages) {
            $validator = self::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                $response = new Response();
                return $response->json([
                    'error' => 'Validation failed',
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return $next($request);
        };
    }
}
