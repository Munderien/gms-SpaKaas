<?php
class getDataCalendar
{
    protected $db;
    public static $afspraakId;
    public static $gebruikerId;
    public static $lodgeId;
    public static $titel;
    public static $startDatumTijd;
    public static $eindDatumTijd;
    public static $status;
    public static $toelichting;
    public static $aantalMensen;

    public function __construct()
    {
        try {
            $this->db = new PDO("mysql:host=localhost;dbname=dms-spakaas;", "root", "");
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    public function getData()
    {
        //session_start();

        // Test data
        $_SESSION['gebruikerid'] = 1;
        $_SESSION['rol'] = 3; // 0 = klant, 1, baliemedewerkers, 2 = monteur, 3 = manager

        $userId = $_SESSION['gebruikerid'];
        $role = $_SESSION['rol'];

        $params = [];

        if ($role === 3) { // should be 3
            $sql = "SELECT a.*, lt.naam AS lodgetype
                    FROM afspraak a
                    INNER JOIN lodgetype lt ON lt.typeid = a.lodgeid";

            if (!empty($lodgeType) && $lodgeType !== 'all') {
                $sql .= " WHERE lt.naam = :lodgetype";
                $params[':lodgetype'] = $lodgeType;
            }
        } else {
            $sql = "SELECT a.*, lt.naam AS lodgetype
                    FROM afspraak a
                    INNER JOIN lodgetype lt ON lt.typeid = a.lodgeid
                    WHERE a.gebruikerid = :gebruikerid";
            $params[':gebruikerid'] = $userId;

            if (!empty($lodgeType) && $lodgeType !== 'all') {
                $sql .= " AND lt.naam = :lodgetype";
                $params[':lodgetype'] = $lodgeType;
            }
        }

        $v = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $paramType = $key === ':gebruikerid' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $v->bindValue($key, $value, $paramType);
        }

        $v->execute();

        return $v->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLodgeTypes()
    {
        $stmt = $this->db->prepare("SELECT naam FROM lodgetype ORDER BY naam ASC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

class Calendar
{
    /*
        taak: laat de events in de database zien in de calender
        eerst globaal daarna gepersonaliseerd
    */
    private $active_year, $active_month, $active_day, $active_hour, $active_minute;
    private $events = [];

    // Takes the current time
    public function __construct($date = null)
    {
        $this->active_year = $date != null ? date('Y', strtotime($date)) : date('Y');
        $this->active_month = $date != null ? date('m', strtotime($date)) : date('m');
        $this->active_day = $date != null ? date('d', strtotime($date)) : date('d');
        $this->active_hour = $date != null ? date('h', strtotime($date)) : date('h');
        $this->active_minute = $date != null ? date('i', strtotime($date)) : date('i');
    }

    // Adds everything to the event from the database + some default values

    public function addEvent($afspraakId, $gebruikerId, $lodgeId, $titel, $startDatumTijd, $eindDatumTijd, $status = false, $toelichting, $prioriteit = 'low', $aantalMensen, $color = ' ', $days = 1)
    {
        $color = $color ? ' ' . $color : $color;
        //$this->events[] = [$txt, $date, $time, $days, $color];
        $this->events[] = [$afspraakId, $gebruikerId, $lodgeId, $titel, $startDatumTijd, $eindDatumTijd, $status, $toelichting, $aantalMensen, $prioriteit, $aantalMensen, $days];
    }

    public function __toString()
    {
        // takes current days of the month and some before and after the months
        $numdays = date('t', strtotime($this->active_minute . '-' . $this->active_hour . '-' . $this->active_day . '-' . $this->active_month . '-' . $this->active_year));
        $num_days_last_month = date('j', strtotime('last day of previous month', $numdays));
        $days = [0 => 'Sun', 1 => 'Mon', 2 => "Tue", 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        $First_Day_Of_Week = array_search(date('D', strtotime($this->active_year . '-' . $this->active_month . '-1')), $days);

        $html = '<div class="calendar">';
        $html .= '<div class="header">';
        $html .= '<div class="month-year">';
        $html .= date('F Y', strtotime($this->active_year . '-' . $this->active_month . '-' . $this->active_day . '-' . $this->active_hour . '-' . $this->active_minute));
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="days">';

        // add everyday of the months 
        foreach ($days as $day) {
            $html .= '
            <div class="day_name">
                    ' . $day . '
                </div>
            ';
        }
        // adds some days from last month to fill the first week
        for ($i = $First_Day_Of_Week; $i  > 0; $i--) {
            $html .= '
            <div class="day_num ignore">
                    ' . ($num_days_last_month - $i + 1) . '
                </div>
            ';
        }

        for ($i = 1; $i <= $numdays; $i++) {
            $selected = '';
            if ($i == $this->active_day) {
                // By the $selected, there was 'selected first' this was changed due to the current day bug 
                $selected = ' current_Day';
            }
            $html .= '<div class="day_num' . $selected . '">';

            // Adds every event to the day that matches the date
            $html .= '<span>' . $i . '</span>';
            foreach ($this->events as $event) {
                $eventDate   = date('Y-m-d', strtotime($event[4])); // event date
                $eventDateLast = date('Y-m-d', strtotime($event[5])); // event date from last
                $currentDate = date('Y-m-d', strtotime($this->active_year . '-' . $this->active_month . '-' . $i)); // calendar day

                // the if prevents it from showing all events on all days
                if ($currentDate >= $eventDate && $currentDate <= $eventDateLast) {
                    $descriptionTrimmed = (mb_strlen($event[8]) > 20) ? mb_substr($event[8], 0, 10) . '...' : $event[8]; 
                    $priorityClass = 'priority-' . strtolower($event[9]);
                    // afspraakId, titel, toelichting, begintijd, eindtijd
                    $html .= "<a href='Planneritem.php?id={$event[0]}'>
                    <div class='event {$priorityClass}'>
                        {$event[3]}, {$descriptionTrimmed}<br> 
                        {$event[4]} | {$event[5]}
                    </div>
                  </a>";
                }
            }
            $html .= '</div>';
        }
        // adds some days from next month to fill the last week
        for ($i = 1; $i <= (42 - $numdays - max($First_Day_Of_Week, 0)); $i++) {
            $html .= '
                <div class="day_num ignore">
                    ' . $i . '
                </div>
            ';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    public function oneMonthBack($date)
    {
        $timestamp = strtotime('-1 month', strtotime($date));
        $this->active_year = date('Y', $timestamp);
        $this->active_month = date('m', $timestamp);
        $this->active_day = date('d', $timestamp);
        $this->active_hour = date('h', $timestamp);
        $this->active_minute = date('i', $timestamp);
    }
    public function oneMonthForward($date) 
    {
        $timestamp = strtotime('+1 month', strtotime($date));
        $this->active_year = date('Y', $timestamp);
        $this->active_month = date('m', $timestamp);
        $this->active_day = date('d', $timestamp);
        $this->active_hour = date('h', $timestamp);
        $this->active_minute = date('i', $timestamp);
    }
}
?>