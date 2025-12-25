<?php
class ApiKey extends Model {

    private function generateKey($bytes = 32) {
        return bin2hex(random_bytes($bytes));
    }

    private function generateUniqueKey() {
        do {
            $key = $this->generateKey();
            $stmt = self::$db->prepare("SELECT id FROM api_keys WHERE api_key = ?");
            $stmt->execute([$key]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);
        } while ($exists);

        return $key;
    }

    public function createForUser($userId, $validForSeconds = 86400, $maxRequestsPerHour = 50) {
        $apiKey = $this->generateUniqueKey();

        $stmt = self::$db->prepare("
            INSERT INTO api_keys (user_id, api_key, valid_for_seconds, max_requests_per_hour)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $apiKey, $validForSeconds, $maxRequestsPerHour]);

        return $apiKey;
    }

    public function getForUser($userId) {
        $stmt = self::$db->prepare("SELECT * FROM api_keys WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function extendValidity($keyId, $seconds) {
        // Hämta API-nyckeln först
        $stmt = self::$db->prepare("SELECT created_at FROM api_keys WHERE id = ?");
        $stmt->execute([$keyId]);
        $key = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$key) return;

        // Sekunder som passerat sedan key skapades
        $secondsSinceCreation = time() - strtotime($key['created_at']);

        // Ny giltighetstid ska vara: tiden som gått + 7 dagar
        $newValid = $secondsSinceCreation + $seconds;

        // Uppdatera giltighetstiden
        $stmt = self::$db->prepare("
            UPDATE api_keys
            SET valid_for_seconds = ?
            WHERE id = ?
        ");
        $stmt->execute([$newValid, $keyId]);
    }

    public function findByKey($key) {
        $stmt = self::$db->prepare("SELECT * FROM api_keys WHERE api_key = ? LIMIT 1");
        $stmt->execute([$key]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteKey($keyId) {
        $stmt = self::$db->prepare("DELETE FROM api_keys WHERE id = ?");
        $stmt->execute([$keyId]);
    }
}
