<?php

namespace App\Http;

class Response {
    private $content;
    private $statusCode;
    private $headers;

    public function __construct($content = '', $statusCode = 200, $headers = []) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function make($content = '', $statusCode = 200) {
        return new self($content, $statusCode);
    }

    public static function json($data, $statusCode = 200) {
        $headers = ['Content-Type: application/json'];
        return new self(json_encode($data), $statusCode, $headers);
    }

    public static function redirect($url, $statusCode = 302) {
        $headers = ["Location: {$url}"];
        return new self('', $statusCode, $headers);
    }

    public static function view($template, $data = []) {
        return new self($template, 200, [], $data);
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setHeader($header) {
        $this->headers[] = $header;
        return $this;
    }

    public function send() {
        // Enviar headers
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $header) {
            header($header);
        }
        
        // Enviar conteúdo
        echo $this->content;
        
        exit;
    }

    public function getContent() {
        return $this->content;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getHeaders() {
        return $this->headers;
    }
}
