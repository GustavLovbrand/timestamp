<?php
require __DIR__ . '/../models/User.php';

class UserController extends Controller {

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new User();
            $user = $userModel->findByUsername($_POST['username']);

            if ($user && password_verify($_POST['password'], $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: index.php?controller=time&action=dashboard");
                exit;
            }

            $error = "Fel användarnamn eller lösenord";
        }

        $this->view("login", ["error" => $error ?? null]);
    }

    public function logout() {
        session_unset();      // töm session
        session_destroy();    // avsluta session
        header("Location: index.php?controller=user&action=login");
        exit;
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new User();
            $userModel->create($_POST['username'], $_POST['password']);
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $this->view("register");
    }

    public function createApiKey() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        require __DIR__ . '/../models/ApiKey.php';
        $model = new ApiKey();

        // 7 dagar giltig, max 50 requests per timme
        $apiKey = $model->createForUser($_SESSION['user_id'], 7 * 24 * 3600, 50);

        // Skicka till en view som visar nyckeln EN gång
        $this->view("api_key_created", compact("apiKey"));
    }
}