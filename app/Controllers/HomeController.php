<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class HomeController extends Controller
{
    /**
     * Show the home page.
     */
    public function index(Request $request): Response
    {
        return $this->view('welcome', [
            'framework' => 'Minimal PHP Framework',
            'version' => '1.0.0'
        ]);
    }

    /**
     * Show hello page with name parameter.
     */
    public function hello(Request $request): Response
    {
        $name = $request->getRouteParam('name', 'World');
        
        return $this->view('hello', [
            'name' => $name
        ]);
    }

    /**
     * Return JSON API response.
     */
    public function api(Request $request): Response
    {
        return $this->json([
            'message' => 'Hello from JSON API',
            'framework' => 'Minimal PHP Framework',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Show documentation page.
     */
    public function docs(Request $request): Response
    {
        return $this->view('docs');
    }

    /**
     * Show examples page.
     */
    public function examples(Request $request): Response
    {
        return $this->view('examples');
    }
}
