<?php

namespace App\Views;

class Template
{
    private $template;
    private $data;
    private $layout;

    public function __construct($template, $data = [], $layout = 'default')
    {
        $this->template = $template;
        $this->data = $data;
        $this->layout = $layout;
    }

    public function render()
    {
        // Extrair variáveis para o template
        extract($this->data);

        // Capturar conteúdo do template
        ob_start();
        $templatePath = template_path($this->template);

        if (!file_exists($templatePath)) {
            throw new \Exception("Template {$this->template} não encontrado");
        }

        include $templatePath;
        $content = ob_get_clean();

        // Se tiver layout, renderizar com layout
        if ($this->layout) {
            ob_start();
            $layoutPath = layout_path($this->layout);

            if (file_exists($layoutPath)) {
                include $layoutPath;
                $content = ob_get_clean();
            }
        }

        return $content;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function with($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function layout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    // Helper methods
    public function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function url($path = '')
    {
        $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    public function asset($path)
    {
        return $this->url('assets/' . $path);
    }

    public function route($name, $params = [])
    {
        // Implementar sistema de rotas nomeadas se necessário
        return '#';
    }

    public function old($key, $default = '')
    {
        session_start();
        $old = $_SESSION['old_input'] ?? [];
        return $this->escape($old[$key] ?? $default);
    }

    public function error($field)
    {
        session_start();
        $errors = $_SESSION['errors'] ?? [];
        return $errors[$field] ?? '';
    }

    public function hasError($field)
    {
        session_start();
        $errors = $_SESSION['errors'] ?? [];
        return isset($errors[$field]);
    }

    public function formatNumber($number)
    {
        return number_format($number, 0, ',', '.');
    }

    public function formatDate($date, $format = 'd/m/Y H:i')
    {
        if (!$date) return '';
        return date($format, strtotime($date));
    }

    public function timeAgo($date)
    {
        if (!$date) return '';

        $time = strtotime($date);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return "agora";
        } elseif ($diff < 3600) {
            return floor($diff / 60) . " min atrás";
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . " horas atrás";
        } else {
            return floor($diff / 86400) . " dias atrás";
        }
    }

    public function truncate($text, $length = 100, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }
}
