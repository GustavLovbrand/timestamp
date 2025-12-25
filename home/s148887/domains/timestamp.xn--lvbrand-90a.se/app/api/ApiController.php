<?php
//require __DIR__ . '/../models/ApiKey.php';
//require __DIR__ . '/../models/ApiRequest.php';

class ApiController {

    protected function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function authenticate() {

        $keyString = null;

        // 1. Query param
        if (isset($_GET['api_key'])) {
            $keyString = $_GET['api_key'];
        }

        // 2. Header: api_key
        if (!$keyString && isset($_SERVER['HTTP_API_KEY'])) {
            $keyString = $_SERVER['HTTP_API_KEY'];
        }

        // 3. Header: apikey (för curl som saknar underscore)
        if (!$keyString && isset($_SERVER['HTTP_APIKEY'])) {
            $keyString = $_SERVER['HTTP_APIKEY'];
        }

        if (!$keyString) {
            $this->json(["error" => "Missing API key"], 401);
        }
        
        $apiKeyModel = new ApiKey();
        $key = $apiKeyModel->findByKey($keyString);

        if (!$key) {
            $this->json(["error" => "Invalid API key"], 401);
        }

        // Status check
        if ($key['status'] !== 'active') {
            $this->json(["error" => "API key disabled"], 403);
        }

        // Expiration check
        $created = strtotime($key['created_at']);
        $expires = $created + $key['valid_for_seconds'];

        if (time() > $expires) {
            $this->json(["error" => "API key expired"], 403);
        }

        // Rate limit: count requests past hour
        $apiReqModel = new ApiRequest();
        $count = $apiReqModel->countLastHour($key['id']);

        if ($count >= $key['max_requests_per_hour']) {
            $this->json(["error" => "Rate limit exceeded"], 429);
        }

        // Log request
        $apiReqModel->log($key['id'], $_GET['endpoint'] ?? "unknown");

        return $key; // return validated key data
    }
}
