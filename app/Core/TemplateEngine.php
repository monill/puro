<?php

declare(strict_types=1);

namespace App\Core;

class TemplateEngine
{
    private string $template;
    private array $data = [];
    private array $compiled = [];
    private static array $cache = [];

    public function __construct(string $template)
    {
        $this->template = $template;
    }

    public function with(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function render(array $data = []): string
    {
        $this->data = array_merge($this->data, $data);
        
        if (isset(self::$cache[$this->template])) {
            return $this->executeTemplate(self::$cache[$this->template]);
        }

        $compiled = $this->compileTemplate($this->template);
        self::$cache[$this->template] = $compiled;
        
        return $this->executeTemplate($compiled);
    }

    private function compileTemplate(string $template): string
    {
        $pattern = '/\{\{\s*(.+?)\s*\}\}/';
        $compiled = preg_replace_callback($pattern, [$this, 'compileDirective'], $template);
        
        return $compiled;
    }

    private function compileDirective(array $matches): string
    {
        $directive = trim($matches[1]);
        
        if (str_starts_with($directive, 'if ')) {
            return $this->compileIf($directive);
        } elseif (str_starts_with($directive, 'elseif ')) {
            return $this->compileElseIf($directive);
        } elseif ($directive === 'else') {
            return '<?php else: ?>';
        } elseif ($directive === 'endif') {
            return '<?php endif; ?>';
        } elseif (str_starts_with($directive, 'foreach ')) {
            return $this->compileForeach($directive);
        } elseif ($directive === 'endforeach') {
            return '<?php endforeach; ?>';
        } elseif (str_starts_with($directive, 'for ')) {
            return $this->compileFor($directive);
        } elseif ($directive === 'endfor') {
            return '<?php endfor; ?>';
        } elseif (str_starts_with($directive, 'include ')) {
            return $this->compileInclude($directive);
        } elseif (str_starts_with($directive, 'section ')) {
            return $this->compileSection($directive);
        } elseif ($directive === 'endsection') {
            return '<?php ob_end_clean(); ?>';
        } elseif (str_starts_with($directive, 'yield ')) {
            return $this->compileYield($directive);
        } elseif (str_starts_with($directive, 'extends ')) {
            return $this->compileExtends($directive);
        } elseif (str_starts_with($directive, 'csrf')) {
            return '<?php echo \App\Core\View::csrf(); ?>';
        } elseif (str_starts_with($directive, 'method ')) {
            return $this->compileMethod($directive);
        } elseif (str_starts_with($directive, 'auth')) {
            return '<?php if (\App\Core\View::auth()): ?>';
        } elseif ($directive === 'endauth') {
            return '<?php endif; ?>';
        } elseif (str_starts_with($directive, 'guest')) {
            return '<?php if (\App\Core\View::guest()): ?>';
        } elseif ($directive === 'endguest') {
            return '<?php endif; ?>';
        } elseif (str_starts_with($directive, 'can ')) {
            return $this->compileCan($directive);
        } elseif ($directive === 'endcan') {
            return '<?php endif; ?>';
        } elseif (str_starts_with($directive, 'cannot ')) {
            return $this->compileCannot($directive);
        } elseif ($directive === 'endcannot') {
            return '<?php endif; ?>';
        } else {
            return '<?php echo ' . $this->compileVariable($directive) . '; ?>';
        }
    }

    private function compileIf(string $directive): string
    {
        $condition = substr($directive, 3);
        $condition = $this->compileCondition($condition);
        return "<?php if ({$condition}): ?>";
    }

    private function compileElseIf(string $directive): string
    {
        $condition = substr($directive, 7);
        $condition = $this->compileCondition($condition);
        return "<?php elseif ({$condition}): ?>";
    }

    private function compileCondition(string $condition): string
    {
        $condition = preg_replace('/\b(\w+)\s*==\s*(\w+)\b/', '$1 == $2', $condition);
        $condition = preg_replace('/\b(\w+)\s*!=\s*(\w+)\b/', '$1 != $2', $condition);
        $condition = preg_replace('/\b(\w+)\s*>\s*(\w+)\b/', '$1 > $2', $condition);
        $condition = preg_replace('/\b(\w+)\s*<\s*(\w+)\b/', '$1 < $2', $condition);
        $condition = preg_replace('/\b(\w+)\s*>=\s*(\w+)\b/', '$1 >= $2', $condition);
        $condition = preg_replace('/\b(\w+)\s*<=\s*(\w+)\b/', '$1 <= $2', $condition);
        
        return $this->compileVariable($condition);
    }

    private function compileForeach(string $directive): string
    {
        $expression = substr($directive, 8);
        preg_match('/\$(\w+)\s+as\s+\$(\w+)/', $expression, $matches);
        
        if (count($matches) === 3) {
            $array = $matches[1];
            $item = $matches[2];
            return "<?php foreach (\${$array} as \${$item}): ?>";
        }
        
        return '<?php foreach ($expression): ?>';
    }

    private function compileFor(string $directive): string
    {
        $expression = substr($directive, 4);
        return "<?php for ({$expression}): ?>";
    }

    private function compileInclude(string $directive): string
    {
        $template = trim(substr($directive, 8));
        $template = $this->compileVariable($template);
        return "<?php echo \$__engine->make({$template}); ?>";
    }

    private function compileSection(string $directive): string
    {
        $name = trim(substr($directive, 8));
        $name = $this->compileVariable($name);
        return "<?php ob_start(); \$__sections[{$name}] = ob_get_clean(); ?>";
    }

    private function compileYield(string $directive): string
    {
        $name = trim(substr($directive, 6));
        $name = $this->compileVariable($name);
        return "<?php echo \$__sections[{$name}] ?? ''; ?>";
    }

    private function compileExtends(string $directive): string
    {
        $parent = trim(substr($directive, 8));
        $parent = $this->compileVariable($parent);
        return "<?php \$__parent = {$parent}; ?>";
    }

    private function compileMethod(string $directive): string
    {
        $method = trim(substr($directive, 7));
        return "<?php echo '<input type=\"hidden\" name=\"_method\" value=\"{$method}\">'; ?>";
    }

    private function compileCan(string $directive): string
    {
        $ability = trim(substr($directive, 4));
        $ability = $this->compileVariable($ability);
        return "<?php if (\App\Core\View::can({$ability})): ?>";
    }

    private function compileCannot(string $directive): string
    {
        $ability = trim(substr($directive, 7));
        $ability = $this->compileVariable($ability);
        return "<?php if (\App\Core\View::cannot({$ability})): ?>";
    }

    private function compileVariable(string $variable): string
    {
        $variable = preg_replace('/\$(\w+)/', '$__data[\'$1\']', $variable);
        $variable = preg_replace('/\$(\w+)\.(\w+)/', '$__data[\'$1\'][\'$2\']', $variable);
        
        return $variable;
    }

    public function make(string $template, array $data = []): string
    {
        $engine = new self($template);
        return $engine->with($data)->render();
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }

    public static function getCache(): array
    {
        return self::$cache;
    }
}
