<?php
class Router {
    public function route() {

        // === API routing ===
        if (isset($_GET['api'])) {

            $version    = $_GET['version'] ?? 1;
            $controller = $_GET['controller'] ?? 'time';
            $action     = $_GET['action'] ?? 'index';

            // Ladda in API-basklassen
            require_once APP_PATH . "/api/ApiController.php";
            require_once __DIR__ . '/../models/ApiKey.php';
            require_once __DIR__ . '/../models/ApiRequest.php';

            // Ex: controller = time → ApiTimeController
            $controllerName = 'Api' . ucfirst($controller) . 'Controller';
            $file = __DIR__ . '/../api/' . $controllerName . '.php';

            if (!file_exists($file)) {
                http_response_code(404);
                die(json_encode(["error" => "API controller not found"]));
            }

            require $file;

            if (!class_exists($controllerName)) {
                http_response_code(500);
                die(json_encode(["error" => "API controller class missing"]));
            }

            $ctrl = new $controllerName();

            if (!method_exists($ctrl, $action)) {
                http_response_code(404);
                die(json_encode(["error" => "API endpoint not found"]));
            }

            // Kör API-aktionsmetoden
            $ctrl->$action();
            exit;
        }


        // === Vanlig webb routing ===
        $controller = $_GET['controller'] ?? 'user';
        $action     = $_GET['action'] ?? 'login';

        $controllerName = ucfirst($controller) . 'Controller';
        $file = __DIR__ . '/../controllers/' . $controllerName . '.php';

        if (!file_exists($file)) die("Controller not found");

        require $file;
        $ctrl = new $controllerName;

        if (!method_exists($ctrl, $action)) die("Method not found");

        $ctrl->$action();
    }
}