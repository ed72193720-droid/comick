<?php

namespace App\Controllers;

abstract class BaseController {
    protected function render($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . "/../Views/{$view}.php";
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new \Exception("View not found: {$view}");
        }
    }
    
    protected function redirect($location) {
        header("Location: {$location}");
        exit();
    }
    
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
