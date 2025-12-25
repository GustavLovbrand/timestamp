<?php
require __DIR__ . '/../models/ApiKey.php';

class ApikeyController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $model = new ApiKey();
        $apiKey = $model->getForUser($_SESSION['user_id']);

        $expiresAt = null;
        $remainingSeconds = null;

        if ($apiKey) {
            // created_at -> timestamp
            $created = strtotime($apiKey['created_at']);

            // expiration timestamp = created_at + valid_for_seconds
            $expiresTs = $created + $apiKey['valid_for_seconds'];

            $expiresAt = date("Y-m-d H:i:s", $expiresTs);

            // remaining time
            $remainingSeconds = $expiresTs - time();
            if ($remainingSeconds < 0) {
                $remainingSeconds = 0;
            }
        }

        $this->view("apikey_index", compact("apiKey", "expiresAt", "remainingSeconds"));
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) exit;

        $model = new ApiKey();
        // Nyckel giltig i 7 dagar
        $model->createForUser($_SESSION['user_id'], 7 * 24 * 3600);

        header("Location: index.php?controller=apikey&action=index");
    }

    public function extend() {
        if (!isset($_SESSION['user_id'])) exit;

        $model = new ApiKey();
        $apiKey = $model->getForUser($_SESSION['user_id']);

        if ($apiKey) {
            $model->extendValidity($apiKey['id'], 7 * 24 * 3600);
        }

        header("Location: index.php?controller=apikey&action=index");
    }

    public function delete() {
        if (!isset($_SESSION['user_id'])) exit;

        $model = new ApiKey();
        $apiKey = $model->getForUser($_SESSION['user_id']);

        if ($apiKey) {
            $model->deleteKey($apiKey['id']);
        }

        header("Location: index.php?controller=apikey&action=index");
    }
}
