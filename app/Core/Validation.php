<?php

declare(strict_types=1);

namespace App\Core;

class Validation
{
    private array $data;
    private array $rules;
    private array $messages = [];
    private array $errors = [];
    private array $customMessages = [];
    private static array $defaultMessages = [
        'required' => 'The :attribute field is required.',
        'email' => 'The :attribute must be a valid email address.',
        'min' => 'The :attribute must be at least :min characters.',
        'max' => 'The :attribute may not be greater than :max characters.',
        'between' => 'The :attribute must be between :min and :max characters.',
        'size' => 'The :attribute must be :size characters.',
        'numeric' => 'The :attribute must be a number.',
        'integer' => 'The :attribute must be an integer.',
        'float' => 'The :attribute must be a float.',
        'alpha' => 'The :attribute may only contain letters.',
        'alpha_num' => 'The :attribute may only contain letters and numbers.',
        'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
        'url' => 'The :attribute must be a valid URL.',
        'ip' => 'The :attribute must be a valid IP address.',
        'regex' => 'The :attribute format is invalid.',
        'unique' => 'The :attribute has already been taken.',
        'exists' => 'The selected :attribute is invalid.',
        'confirmed' => 'The :attribute confirmation does not match.',
        'same' => 'The :attribute and :other must match.',
        'different' => 'The :attribute and :other must be different.',
        'in' => 'The selected :attribute is invalid.',
        'not_in' => 'The selected :attribute is invalid.',
        'date' => 'The :attribute is not a valid date.',
        'date_format' => 'The :attribute does not match the format :format.',
        'before' => 'The :attribute must be a date before :date.',
        'after' => 'The :attribute must be a date after :date.',
        'file' => 'The :attribute must be a file.',
        'image' => 'The :attribute must be an image.',
        'mimes' => 'The :attribute must be a file of type: :values.',
        'max_file' => 'The :attribute may not be greater than :max kilobytes.',
        'min_file' => 'The :attribute must be at least :min kilobytes.',
    ];

    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
    }

    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        return new self($data, $rules, $customMessages);
    }

    public function validate(): bool
    {
        foreach ($this->rules as $attribute => $rules) {
            $value = $this->getValue($attribute);
            $attributeRules = $this->parseRules($rules);
            
            foreach ($attributeRules as $rule => $parameters) {
                if (!$this->validateRule($attribute, $value, $rule, $parameters)) {
                    $this->addError($attribute, $rule, $parameters);
                    break;
                }
            }
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

    public function getFirstError(string $attribute): ?string
    {
        return $this->errors[$attribute][0] ?? null;
    }

    public function hasError(string $attribute): bool
    {
        return isset($this->errors[$attribute]);
    }

    private function getValue(string $attribute): mixed
    {
        $keys = explode('.', $attribute);
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

    private function parseRules(string|array $rules): array
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        
        $parsed = [];
        
        foreach ($rules as $rule) {
            if (str_contains($rule, ':')) {
                [$rule, $parameters] = explode(':', $rule, 2);
                $parameters = explode(',', $parameters);
            } else {
                $parameters = [];
            }
            
            $parsed[$rule] = array_map('trim', $parameters);
        }
        
        return $parsed;
    }

    private function validateRule(string $attribute, mixed $value, string $rule, array $parameters): bool
    {
        return match ($rule) {
            'required' => $this->validateRequired($value),
            'email' => $this->validateEmail($value),
            'min' => $this->validateMin($value, (int)($parameters[0] ?? 0)),
            'max' => $this->validateMax($value, (int)($parameters[0] ?? 0)),
            'between' => $this->validateBetween($value, (int)($parameters[0] ?? 0), (int)($parameters[1] ?? 0)),
            'size' => $this->validateSize($value, (int)($parameters[0] ?? 0)),
            'numeric' => $this->validateNumeric($value),
            'integer' => $this->validateInteger($value),
            'float' => $this->validateFloat($value),
            'alpha' => $this->validateAlpha($value),
            'alpha_num' => $this->validateAlphaNum($value),
            'alpha_dash' => $this->validateAlphaDash($value),
            'url' => $this->validateUrl($value),
            'ip' => $this->validateIp($value),
            'regex' => $this->validateRegex($value, $parameters[0] ?? ''),
            'confirmed' => $this->validateConfirmed($attribute, $value),
            'same' => $this->validateSame($attribute, $value, $parameters[0] ?? ''),
            'different' => $this->validateDifferent($attribute, $value, $parameters[0] ?? ''),
            'in' => $this->validateIn($value, $parameters),
            'not_in' => $this->validateNotIn($value, $parameters),
            'date' => $this->validateDate($value),
            'date_format' => $this->validateDateFormat($value, $parameters[0] ?? ''),
            'before' => $this->validateBefore($value, $parameters[0] ?? ''),
            'after' => $this->validateAfter($value, $parameters[0] ?? ''),
            'file' => $this->validateFile($value),
            'image' => $this->validateImage($value),
            'mimes' => $this->validateMimes($value, $parameters),
            'max_file' => $this->validateMaxFile($value, (int)($parameters[0] ?? 0)),
            'min_file' => $this->validateMinFile($value, (int)($parameters[0] ?? 0)),
            default => true,
        };
    }

    private function validateRequired(mixed $value): bool
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_array($value) && empty($value)) {
            return false;
        }
        
        return true;
    }

    private function validateEmail(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin(mixed $value, int $min): bool
    {
        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        } elseif (is_numeric($value)) {
            return $value >= $min;
        } elseif (is_array($value)) {
            return count($value) >= $min;
        }
        
        return false;
    }

    private function validateMax(mixed $value, int $max): bool
    {
        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        } elseif (is_numeric($value)) {
            return $value <= $max;
        } elseif (is_array($value)) {
            return count($value) <= $max;
        }
        
        return false;
    }

    private function validateBetween(mixed $value, int $min, int $max): bool
    {
        return $this->validateMin($value, $min) && $this->validateMax($value, $max);
    }

    private function validateSize(mixed $value, int $size): bool
    {
        if (is_string($value)) {
            return mb_strlen($value) === $size;
        } elseif (is_numeric($value)) {
            return $value == $size;
        } elseif (is_array($value)) {
            return count($value) === $size;
        }
        
        return false;
    }

    private function validateNumeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    private function validateInteger(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function validateFloat(mixed $value): bool
    {
        return is_float($value) || filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
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

    private function validateUrl(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validateIp(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    private function validateRegex(mixed $value, string $pattern): bool
    {
        return is_string($value) && preg_match($pattern, $value);
    }

    private function validateConfirmed(string $attribute, mixed $value): bool
    {
        $confirmation = $this->getValue($attribute . '_confirmation');
        return $value === $confirmation;
    }

    private function validateSame(string $attribute, mixed $value, string $other): bool
    {
        $otherValue = $this->getValue($other);
        return $value === $otherValue;
    }

    private function validateDifferent(string $attribute, mixed $value, string $other): bool
    {
        $otherValue = $this->getValue($other);
        return $value !== $otherValue;
    }

    private function validateIn(mixed $value, array $values): bool
    {
        return in_array($value, $values);
    }

    private function validateNotIn(mixed $value, array $values): bool
    {
        return !in_array($value, $values);
    }

    private function validateDate(mixed $value): bool
    {
        if ($value instanceof \DateTime) {
            return true;
        }
        
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

    private function validateBefore(mixed $value, string $date): bool
    {
        $valueDate = $this->parseDate($value);
        $compareDate = $this->parseDate($date);
        
        return $valueDate && $compareDate && $valueDate < $compareDate;
    }

    private function validateAfter(mixed $value, string $date): bool
    {
        $valueDate = $this->parseDate($value);
        $compareDate = $this->parseDate($date);
        
        return $valueDate && $compareDate && $valueDate > $compareDate;
    }

    private function parseDate(mixed $value): ?\DateTime
    {
        if ($value instanceof \DateTime) {
            return $value;
        }
        
        if (!is_string($value)) {
            return null;
        }
        
        try {
            return new \DateTime($value);
        } catch (\Exception $e) {
            return null;
        }
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

    private function validateMimes(mixed $value, array $mimes): bool
    {
        if (!$this->validateFile($value)) {
            return false;
        }
        
        $extension = strtolower(pathinfo($value['name'] ?? '', PATHINFO_EXTENSION));
        return in_array($extension, $mimes);
    }

    private function validateMaxFile(mixed $value, int $max): bool
    {
        if (!$this->validateFile($value)) {
            return false;
        }
        
        $size = $value['size'] ?? 0;
        return $size <= ($max * 1024);
    }

    private function validateMinFile(mixed $value, int $min): bool
    {
        if (!$this->validateFile($value)) {
            return false;
        }
        
        $size = $value['size'] ?? 0;
        return $size >= ($min * 1024);
    }

    private function addError(string $attribute, string $rule, array $parameters): void
    {
        $message = $this->getMessage($attribute, $rule, $parameters);
        $this->errors[$attribute][] = $message;
    }

    private function getMessage(string $attribute, string $rule, array $parameters): string
    {
        $key = "{$attribute}.{$rule}";
        
        if (isset($this->customMessages[$key])) {
            return $this->replacePlaceholders($this->customMessages[$key], $attribute, $rule, $parameters);
        }
        
        if (isset($this->customMessages[$rule])) {
            return $this->replacePlaceholders($this->customMessages[$rule], $attribute, $rule, $parameters);
        }
        
        if (isset(self::$defaultMessages[$rule])) {
            return $this->replacePlaceholders(self::$defaultMessages[$rule], $attribute, $rule, $parameters);
        }
        
        return "The {$attribute} field is invalid.";
    }

    private function replacePlaceholders(string $message, string $attribute, string $rule, array $parameters): string
    {
        $message = str_replace(':attribute', $this->getAttributeName($attribute), $message);
        
        foreach ($parameters as $i => $parameter) {
            $message = str_replace(':' . $this->getParameterName($rule, $i), $parameter, $message);
        }
        
        if ($rule === 'mimes') {
            $message = str_replace(':values', implode(', ', $parameters), $message);
        }
        
        return $message;
    }

    private function getAttributeName(string $attribute): string
    {
        return ucwords(str_replace('_', ' ', $attribute));
    }

    private function getParameterName(string $rule, int $index): string
    {
        $parameterMap = [
            'min' => 'min',
            'max' => 'max',
            'between' => $index === 0 ? 'min' : 'max',
            'size' => 'size',
            'date_format' => 'format',
            'before' => 'date',
            'after' => 'date',
            'same' => 'other',
            'different' => 'other',
            'max_file' => 'max',
            'min_file' => 'min',
        ];
        
        return $parameterMap[$rule][$index] ?? $index;
    }

    public static function extend(string $rule, callable $callback, ?string $message = null): void
    {
        self::$defaultMessages[$rule] = $message ?? "The :attribute field is invalid.";
    }
}
