<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private string $content;
    private int $statusCode;
    private array $headers;
    private string $version;

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->version = '1.1';
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setStatusCode(int $statusCode, ?string $text = null): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function removeHeader(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    public function json(array $data, int $statusCode = 200, array $headers = []): self
    {
        $this->content = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $this->statusCode = $statusCode;
        $this->headers = array_merge([
            'Content-Type' => 'application/json; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
        ], $headers);
        
        return $this;
    }

    public function redirect(string $url, int $statusCode = 302): self
    {
        $this->statusCode = $statusCode;
        $this->setHeader('Location', $url);
        return $this;
    }

    public function view(string $template, array $data = [], int $statusCode = 200): self
    {
        $this->statusCode = $statusCode;
        
        if (!function_exists('renderView')) {
            throw new \RuntimeException('View rendering function not available');
        }
        
        $this->content = renderView($template, $data);
        $this->setHeader('Content-Type', 'text/html; charset=UTF-8');
        
        return $this;
    }

    public function file(string $filePath, ?string $fileName = null, ?string $mimeType = null): self
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $fileName = $fileName ?? basename($filePath);
        $mimeType = $mimeType ?? $this->getMimeType($filePath);

        $this->content = file_get_contents($filePath);
        $this->setHeader('Content-Type', $mimeType);
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->setHeader('Content-Length', (string) strlen($this->content));
        $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->setHeader('Pragma', 'no-cache');

        return $this;
    }

    public function download(string $filePath, ?string $fileName = null): self
    {
        return $this->file($filePath, $fileName);
    }

    private function getMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'txt' => 'text/plain',
            'html' => 'text/html',
            'htm' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'svg' => 'image/svg+xml',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    public function setCookie(string $name, string $value, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true): self
    {
        $this->headers['Set-Cookie'] = sprintf(
            '%s=%s; expires=%s; path=%s%s%s%s',
            $name,
            rawurlencode($value),
            $expires > 0 ? gmdate('D, d-M-Y H:i:s T', $expires) : '',
            $path,
            $domain ? '; domain=' . $domain : '',
            $secure ? '; secure' : '',
            $httpOnly ? '; httponly' : ''
        );
        
        return $this;
    }

    public function removeCookie(string $name, string $path = '/', string $domain = ''): self
    {
        return $this->setCookie($name, '', time() - 3600, $path, $domain);
    }

    public function setCache(int $maxAge = 3600, bool $public = true): self
    {
        $control = $public ? 'public' : 'private';
        $this->setHeader('Cache-Control', "{$control}, max-age={$maxAge}");
        $this->setHeader('Expires', gmdate('D, d M Y H:i:s T', time() + $maxAge));
        
        return $this;
    }

    public function setNoCache(): self
    {
        $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->setHeader('Pragma', 'no-cache');
        $this->setHeader('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT');
        
        return $this;
    }

    public function send(): void
    {
        if (headers_sent()) {
            throw new \RuntimeException('Headers already sent');
        }

        $this->sendHeaders();
        $this->sendContent();
    }

    private function sendHeaders(): void
    {
        if (php_sapi_name() === 'cli') {
            return;
        }

        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->getStatusText($this->statusCode)));

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }
    }

    private function sendContent(): void
    {
        echo $this->content;
    }

    private function getStatusText(int $statusCode): string
    {
        $statusTexts = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Reserved for WebDAV advanced collections expired proposal',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];

        return $statusTexts[$statusCode] ?? 'Unknown Status';
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
