<?php

namespace App\Helpers;

class ValidationHelper {
    /**
     * Validar email
     */
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar URL
     */
    public static function url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validar número inteiro
     */
    public static function integer($value, $min = null, $max = null) {
        $options = [];

        if ($min !== null) {
            $options['min_range'] = $min;
        }
        if ($max !== null) {
            $options['max_range'] = $max;
        }

        return filter_var($value, FILTER_VALIDATE_INT, $options);
    }

    /**
     * Validar número decimal
     */
    public static function decimal($value, $decimals = null) {
        $options = [];

        if ($decimals !== null) {
            $options['decimal'] = ['min_range' => 0, 'max_range' => 9999999999999];
        }

        return filter_var($value, FILTER_VALIDATE_FLOAT, $options);
    }

    /**
     * Validar string
     */
    public static function string($value, ?int $min = null, ?int $max = null): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        // Normalização básica
        $value = trim((string) $value);

        // Validação de tamanho (multibyte safe)
        $length = mb_strlen($value);

        if ($min !== null && $length < $min) {
            return null;
        }

        if ($max !== null && $length > $max) {
            return null;
        }

        return $value;
    }


    /**
     * Validar boolean
     */
    public static function boolean($value) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Validar array
     */
    public static function array($value, bool $required = false): ?array
    {
        if ($required && !is_array($value)) {
            return null;
        }

        if (!$required && $value === null) {
            return [];
        }

        return is_array($value) ? $value : null;
    }

    /**
     * Validar dados com regras
     */
    public static function validate($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $fieldName = $field;

            // Parse regras
            $ruleParts = explode('|', $rule);
            $fieldRules = [];

            foreach ($ruleParts as $rulePart) {
                $fieldRules[] = trim($rulePart);
            }

            // Verificar se campo é obrigatório
            if (in_array('required', $fieldRules) && ($value === null || $value === '')) {
                $errors[$field] = "O campo {$fieldName} é obrigatório";
                continue;
            }

            // Validar tipo específico
            foreach ($fieldRules as $fieldRule) {
                if ($fieldRule === 'email' && !self::email($value)) {
                    $errors[$field] = "O campo {$fieldName} deve ser um email válido";
                }

                if ($fieldRule === 'url' && !self::url($value)) {
                    $errors[$field] = "O campo {$fieldName} deve ser uma URL válida";
                }

                if ($fieldRule === 'integer' && !self::integer($value)) {
                    $errors[$field] = "O campo {$fieldName} deve ser um número inteiro";
                }

                if ($fieldRule === 'decimal' && !self::decimal($value)) {
                    $errors[$field] = "O campo {$fieldName} deve ser um número decimal";
                }

                if ($fieldRule === 'string' && !self::string($value)) {
                    $errors[$field] = "O campo {$fieldName} deve ser uma string válida";
                }

                if ($fieldRule === 'boolean' && !self::boolean($value)) {
                    $errors[$field] = "O campo {$fieldName} deve ser verdadeiro ou falso";
                }

                // Validar tamanho
                if (strpos($fieldRule, 'min:') !== false) {
                    $min = (int) substr($fieldRule, strpos($fieldRule, 'min:') + 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = "O campo {$fieldName} deve ter no mínimo {$min} caracteres";
                    }
                }

                if (strpos($fieldRule, 'max:') !== false) {
                    $max = (int) substr($fieldRule, strpos($fieldRule, 'max:') + 4);
                    if (strlen($value) > $max) {
                        $errors[$field] = "O campo {$fieldName} deve ter no máximo {$max} caracteres";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Sanitizar string
     */
    public static function sanitize($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Limpar string
     */
    public static function clean($string) {
        return trim(preg_replace('/\s+/', ' ', $string));
    }

    /**
     * Gerarar slug
     */
    public static function slug($string) {
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9\s-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Gerarar UUID
     */
    public static function uuid() {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand(0, 0x0fff), mt_rand(0, 0x0fff), mt_rand(0, 0x0fff), mt_rand(0, 0x0fff),
            mt_rand(0, 0x0fff), mt_rand(0, 0x0fff), mt_rand(0, 0x0fff)
        );
    }

    /**
     * Gerarar token seguro
     */
    public static function randomToken($length = 32) {
        $bytes = random_bytes($length);
        return base64_encode($bytes);
    }

    /**
     * Verificar se CPF é válido
     */
    public static function cpf($cpf) {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Validação básica do CPF
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        $cpf = substr($cpf, 0, 9);
        $digits = substr($cpf, 9, 2);
        $check_digit = 0;

        for ($i = 0; $i < 9; $i++) {
            $check_digit += (int) $digits[$i] * (10 - $i);
        }

        $check_digit = ($check_digit % 11) % 10;

        return $check_digit === (int) $digits[1];
    }

    /**
     * Verificar se telefone é válido (formato brasileiro)
     */
    public static function phone($phone) {
        // Remove caracteres não numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Verifica se tem 10 ou 11 dígitos
        if (!in_array(strlen($phone), [10, 11])) {
            return false;
        }

        // Verifica se começa com DDD (telefone celular)
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '55') {
            return true;
        }

        // Verificar se começa com DDD (telefone fixo)
        if (strlen($phone) === 10) {
            $ddd = ['11', '21', '22', '27', '28', '32', '33', '34', '35', '38', '41', '42', '43', '44', '45', '46', '48', '49', '50', '51', '54', '55', '56', '57', '58', '61', '62', '63', '64', '65', '66', '67', '68', '69', '71', '72', '73', '74', '75', '77', '78', '79', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99'];

            return in_array(substr($phone, 0, 2), $ddd);
        }

        return true;
    }
}
