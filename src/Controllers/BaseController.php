<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Views\Template;

class BaseController {
    protected function view($template, $data = []) {
        return new Template($template, $data);
    }

    protected function json($data, $status = 200) {
        return Response::json($data, $status);
    }

    protected function redirect($url, $status = 302) {
        return Response::redirect($url, $status);
    }

    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return $this->redirect($referer);
    }

    protected function validate(Request $request, $rules) {
        $errors = $request->validate($rules);
        
        if (!empty($errors)) {
            if ($request->isAjax()) {
                return $this->json(['errors' => $errors], 422);
            }
            
            // Salvar errors em session e redirecionar de volta
            session_start();
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $request->all();
            
            return $this->back();
        }
        
        return null;
    }

    protected function getErrors() {
        session_start();
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        return $errors;
    }

    protected function getOldInput() {
        session_start();
        $input = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        return $input;
    }

    protected function success($message, $data = []) {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    protected function error($message, $status = 400) {
        return $this->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
