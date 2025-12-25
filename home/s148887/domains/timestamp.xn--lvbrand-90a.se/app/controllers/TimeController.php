<?php
require __DIR__ . '/../models/TimestampEvent.php';

class TimeController extends Controller {

    public function dashboard() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $model = new TimestampEvent();
        $last = $model->getLastEvent($_SESSION['user_id']);

        // Sätter $isClockedIn = true/false beroende på senaste event
        $isClockedIn = $last && $last['event_type'] === 'IN';

        $this->view("dashboard", compact("isClockedIn", "last"));
    }

    public function clockIn() {
        if (!isset($_SESSION['user_id'])) exit;

        $model = new TimestampEvent();
        $last = $model->getLastEvent($_SESSION['user_id']);

        // Förhindra dubbel-in
        if ($last && $last['event_type'] === 'IN') {
            header("Location: index.php?controller=time&action=dashboard");
            exit;
        }

        $model->addEvent($_SESSION['user_id'], 'IN');
        header("Location: index.php?controller=time&action=dashboard");
    }

    public function clockOut() {
        if (!isset($_SESSION['user_id'])) exit;

        $model = new TimestampEvent();
        $last = $model->getLastEvent($_SESSION['user_id']);

        // Förhindra utstämpling utan instämpling först
        if (!$last || $last['event_type'] === 'OUT') {
            header("Location: index.php?controller=time&action=dashboard");
            exit;
        }

        $model->addEvent($_SESSION['user_id'], 'OUT');
        header("Location: index.php?controller=time&action=dashboard");
    }

    public function manual() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $this->view("manual_timestamp");
    }

    public function manualSave() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $type = $_POST['type'];
            $date = $_POST['date'];
            $time = $_POST['time'];
            $datetime = $date . ' ' . $time . ':00';

            if (!in_array($type, ['IN', 'OUT'])) {
                die("Felaktig stämplingstyp");
            }

            if (!strtotime($datetime)) {
                die("Felaktigt datum eller tid");
            }

            $model = new TimestampEvent();
            $model->addManualEvent($_SESSION['user_id'], $type, $datetime);
        }

        header("Location: index.php?controller=time&action=history");
    }

    public function history() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $model = new TimestampEvent();

        // Alla events
        $events = $model->getAllEvents($_SESSION['user_id']);

        // Markera ogiltiga
        $events = $model->detectInvalidEvents($events);

        // Sessions (räknas via IN→OUT)
        $sessions = $model->calculateSessions($events);

        // Statistik
        $totalSeconds = array_sum($sessions);
        $totalHours = round($totalSeconds / 3600, 2);
        $numSessions = count($sessions);
        $average = $numSessions > 0 ? round(($totalSeconds / $numSessions) / 3600, 2) : 0;

        $this->view("history", compact("events", "totalHours", "numSessions", "average"));
    }
}