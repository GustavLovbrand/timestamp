<?php
class TimestampEvent extends Model {

    public function getLastEvent($userId) {
        $stmt = self::$db->prepare("
            SELECT * FROM timestamp_events 
            WHERE user_id = ? 
            ORDER BY event_time DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addEvent($userId, $type) {
        $stmt = self::$db->prepare("
            INSERT INTO timestamp_events (user_id, event_type)
            VALUES (?, ?)
        ");
        return $stmt->execute([$userId, $type]);
    }

    public function addManualEvent($userId, $eventType, $datetime)
    {
        $stmt = self::$db->prepare("
            INSERT INTO timestamp_events (user_id, event_type, event_time, is_manual)
            VALUES (?, ?, ?, 1)
        ");
        return $stmt->execute([$userId, $eventType, $datetime]);
    }

    public function getAllEvents($userId) {
        $stmt = self::$db->prepare("
            SELECT * FROM timestamp_events
            WHERE user_id = ?
            ORDER BY event_time ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateSessions($events) {
        $sessions = [];
        $start = null;

        foreach ($events as $event) {
            if ($event['event_type'] === 'IN') {
                $start = strtotime($event['event_time']);
            }
            elseif ($event['event_type'] === 'OUT' && $start !== null) {
                $end = strtotime($event['event_time']);
                $sessions[] = $end - $start;
                $start = null;
            }
        }

        return $sessions; // array of seconds
    }

    public function detectInvalidEvents(array $events, $maxSessionHours = 24): array
    {
        $invalid = [];

        $pendingIn = null; // index of waiting IN event
        $maxSeconds = $maxSessionHours * 3600;

        for ($i = 0; $i < count($events); $i++) {

            $curr = $events[$i];

            // --- RULE 1: Double IN/OUT ---
            if ($i > 0) {
                if ($curr['event_type'] === $events[$i - 1]['event_type']) {
                    $events[$i]['invalid'] = "Dubbla '{$curr['event_type']}' i rad";
                    $events[$i - 1]['invalid'] = "Dubbla '{$curr['event_type']}' i rad";
                }
            }

            // --- RULE 3: OUT utan pending IN ---
            if ($curr['event_type'] === 'OUT' && $pendingIn === null) {
                $events[$i]['invalid'] = "OUT utan matchande IN";
                continue;
            }

            // --- IN event ---
            if ($curr['event_type'] === 'IN') {
                $pendingIn = $i;
                continue;
            }

            // --- OUT event: matchar en IN ---
            if ($curr['event_type'] === 'OUT' && $pendingIn !== null) {

                $inEvent = $events[$pendingIn];
                $seconds = strtotime($curr['event_time']) - strtotime($inEvent['event_time']);

                // RULE 4: Session för lång
                if ($seconds > $maxSeconds) {
                    $events[$i]['invalid'] = "Session över {$maxSessionHours}h";
                    $events[$pendingIn]['invalid'] = "Session över {$maxSessionHours}h";
                }

                // Match klar
                $pendingIn = null;
            }
        }

        // --- RULE 2: IN utan OUT ---
        if ($pendingIn !== null) {
            $events[$pendingIn]['invalid'] = "IN utan matchande OUT";
        }

        return $events;
    }

    /**
     * Get timestamp history for a user (used by API).
     *
     * @param int         $userId  User ID
     * @param int         $limit   Max number of entries to return
     * @param int         $offset  Offset for pagination
     * @param string|null $from    Optional start date-time (YYYY-MM-DD or full datetime)
     * @param string|null $to      Optional end date-time   (YYYY-MM-DD or full datetime)
     *
     * @return array Array of timestamp_events rows
     */
    public function getHistory($userId, $limit = 50, $offset = 0, $from = null, $to = null)
    {
        // Säkerställ att limit/offset verkligen är int
        $limit  = (int)$limit;
        $offset = (int)$offset;

        if ($limit <= 0) {
            $limit = 50;
        }

        if ($offset < 0) {
            $offset = 0;
        }

        $sql = "SELECT id, user_id, event_type, event_time, is_manual
                FROM timestamp_events
                WHERE user_id = ?";
        $params = [$userId];

        if ($from) {
            if (strlen($from) === 10) {
                $from .= ' 00:00:00';
            }
            $sql .= " AND event_time >= ?";
            $params[] = $from;
        }

        if ($to) {
            if (strlen($to) === 10) {
                $to .= ' 23:59:59';
            }
            $sql .= " AND event_time <= ?";
            $params[] = $to;
        }

        // Här stoppar vi in LIMIT/OFFSET direkt efter att vi castat dem
        $sql .= " ORDER BY event_time DESC
                LIMIT $limit OFFSET $offset";

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}