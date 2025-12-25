<?php
class ApiRequest extends Model {

    public function countLastHour($apiKeyId) {
        $stmt = self::$db->prepare("
            SELECT COUNT(*) FROM api_requests
            WHERE api_key_id = ?
              AND request_time >= NOW() - INTERVAL 1 HOUR
        ");
        $stmt->execute([$apiKeyId]);
        return $stmt->fetchColumn();
    }

    public function log($apiKeyId, $endpoint, $success = 1, $code = 200) {
        $stmt = self::$db->prepare("
            INSERT INTO api_requests (api_key_id, endpoint, ip_address, user_agent, success, response_code)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $apiKeyId,
            $endpoint,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $success,
            $code
        ]);
    }
}
