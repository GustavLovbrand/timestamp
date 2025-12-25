<?php

class Auth {

    // Längden på en session
    private static $timeout = 30 * 60;

    public static function handle() {

        // Bind session till User Agent (skydd mot hijacking)
        self::validateFingerprint();

        // Timeout
        self::validateActivityTimeout();
    }

    private static function validateFingerprint() {

        $currentFingerprint = md5($_SERVER['HTTP_USER_AGENT']);

        if (!isset($_SESSION['fingerprint'])) {
            // Första gången – sätt fingerprint
            $_SESSION['fingerprint'] = $currentFingerprint;
        } else {
            // Fingerprint matchar inte → logga ut
            if ($_SESSION['fingerprint'] !== $currentFingerprint) {
                self::forceLogout();
            }
        }
    }

    private static function validateActivityTimeout() {

        if (!isset($_SESSION['user_id'])) {
            // Ingen inloggad → hoppa över timeout
            return;
        }

        if (isset($_SESSION['last_activity'])) {

            // Har det gått för lång tid?
            if ((time() - $_SESSION['last_activity']) > self::$timeout) {
                self::forceLogout(true);
            }
        }

        // Uppdatera senaste aktivitet
        $_SESSION['last_activity'] = time();
    }

    public static function forceLogout($timeout = false) {
        session_unset();
        session_destroy();

        if ($timeout) {
            header("Location: index.php?controller=user&action=login&timeout=1");
        } else {
            header("Location: index.php?controller=user&action=login");
        }

        exit;
    }
}