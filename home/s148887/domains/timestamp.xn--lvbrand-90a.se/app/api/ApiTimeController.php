<?php
//require __DIR__ . '/ApiController.php';
//require __DIR__ . '/../models/TimestampEvent.php';

class ApiTimeController extends ApiController {

    /**
     * @route GET /api/v1/time/clockin
     * @desc Clock in the authenticated user.
     *
     * @auth Requires valid API key.
     *
     * @returns JSON:
     * {
     *   "status": "ok",
     *   "message": "Clocked in"
     * }
     *
     * @errors
     *  - 400: Already clocked in
     */
    public function clockin() {

        // Validera API-nyckel (rate-limit + auth)
        $key = $this->authenticate();
        $userId = $key['user_id'];

        $model = new TimestampEvent();
        $last = $model->getLastEvent($userId);

        if ($last && $last['event_type'] === 'IN') {
            $this->json(["error" => "Already clocked in"], 400);
        }

        $model->addEvent($userId, "IN");

        $this->json([
            "status"  => "ok",
            "message" => "Clocked in"
        ]);
    }

    /**
     * @route GET /api/v1/time/clockout
     * @desc Clock out the authenticated user.
     *
     * @auth Requires valid API key.
     *
     * @returns JSON:
     * {
     *   "status": "ok",
     *   "message": "Clocked out"
     * }
     *
     * @errors
     *  - 400: Not clocked in
     */
    public function clockout() {

        $key = $this->authenticate();
        $userId = $key['user_id'];

        $model = new TimestampEvent();
        $last = $model->getLastEvent($userId);

        if (!$last || $last['event_type'] === 'OUT') {
            $this->json(["error" => "Not clocked in"], 400);
        }

        $model->addEvent($userId, "OUT");

        $this->json([
            "status"  => "ok",
            "message" => "Clocked out"
        ]);
    }

    /**
     * @route GET /api/v1/time/history
     * @desc Retrieve timestamp history for the authenticated user.
     *
     * @auth Requires valid API key.
     *
     * @params
     *  - limit (int, optional): Max results to return. Default: 50
     *  - offset (int, optional): Pagination offset. Default: 0
     *  - from (string, optional): Start date or datetime (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
     *  - to (string, optional): End date or datetime (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
     *
     * @returns JSON:
     * {
     *   "status": "ok",
     *   "count": <int>,
     *   "limit": <int>,
     *   "offset": <int>,
     *   "data": [
     *     {
     *       "id": <int>,
     *       "user_id": <int>,
     *       "event_type": "IN" | "OUT",
     *       "event_time": "YYYY-MM-DD HH:MM:SS",
     *       "is_manual": 0 | 1
     *     },
     *     ...
     *   ]
     * }
     *
     * @errors
     *  - none
     */
    public function history()
    {
        $auth = $this->authenticate();
        $userId = $auth['user_id'];

        $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        $from = $_GET['from'] ?? null;
        $to   = $_GET['to'] ?? null;

        $eventModel = new TimestampEvent();
        $history = $eventModel->getHistory($userId, $limit, $offset, $from, $to);

        return $this->json([
            "status" => "ok",
            "count"  => count($history),
            "limit"  => $limit,
            "offset" => $offset,
            "data"   => $history
        ]);
    }

    /**
     * @route GET /api/v1/time/status
     * @desc Returns the current clock-in status for the authenticated user.
     *
     * @auth Requires valid API key.
     *
     * @returns JSON:
     * {
     *   "status": "IN" | "OUT",
     *   "event_time": "YYYY-MM-DD HH:MM:SS",
     *   "is_manual": 0 | 1,
     *   "seconds_since": <int|null>
     * }
     *
     * @errors
     *  - none
     */
    public function status()
    {
        // Authenticate API key
        $auth = $this->authenticate();
        $userId = $auth['user_id'];

        // Load latest timestamp event
        $eventModel = new TimestampEvent();
        $last = $eventModel->getLastEvent($userId);

        // No timestamps exist for user yet
        if (!$last) {
            return $this->json([
                "status"        => "OUT",
                "event_time"    => null,
                "is_manual"     => null,
                "seconds_since" => null
            ]);
        }

        // Determine IN/OUT status
        $status = $last['event_type'];

        // Calculate seconds since event
        $eventTime = strtotime($last['event_time']);
        $now = time();
        $secondsSince = $now - $eventTime;

        // If OUT, duration since event is not meaningful
        if ($status === "OUT") {
            $secondsSince = null;
        }

        return $this->json([
            "status"        => $status,
            "event_time"    => $last['event_time'],
            "is_manual"     => (int)$last['is_manual'],
            "seconds_since" => $secondsSince
        ]);
    }
}