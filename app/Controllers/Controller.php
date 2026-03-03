<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;

abstract class Controller
{
    /**
     * Render a view.
     */
    protected function view(string $template, array $data = []): Response
    {
        return new Response(View::make($template, $data));
    }

    /**
     * Return JSON response.
     */
    protected function json(mixed $data, int $status = 200): Response
    {
        return (new Response())->json($data, $status);
    }

    /**
     * Redirect to another URL.
     */
    protected function redirect(string $url, int $status = 302): Response
    {
        return (new Response())->redirect($url, $status);
    }

    /**
     * Redirect back to previous page.
     */
    protected function back(): Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return $this->redirect($referer);
    }

    /**
     * Validate request data.
     */
    protected function validate(Request $request, array $rules): array
    {
        $validator = \App\Core\RequestValidator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $validator->errors()));
        }

        return $validator->validated();
    }

    /**
     * Get authenticated user.
     */
    protected function user(): ?object
    {
        return \App\Core\AuthManager::user();
    }

    /**
     * Check if user is authenticated.
     */
    protected function auth(): bool
    {
        return \App\Core\AuthManager::check();
    }

    /**
     * Get request instance.
     */
    protected function request(): Request
    {
        return Request::getInstance();
    }
}
