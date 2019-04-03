<?php
namespace BigV;

use DateInterval;
use DatePeriod;
use DateTime;
use SplObjectStorage;

class NZHolidays extends SplObjectStorage {

    function __construct() {
        $this->attach(new Holiday('New Years Day', 1, 1, Holiday::TUESDAYISED, null, null, false));
        $this->attach(new Holiday('Day After New Years Day',2, 1, Holiday::MONDAYISED, null,null,false));
        $this->attach(new Holiday('Waitangi Day', 6, 2, Holiday::MONDAYISED, 2014, null,false));
        $this->attach(new Holiday('Good Friday', null, null, null, null, 'last Friday', true));
        $this->attach(new Holiday('Easter Monday', null, null, null, null, 'next Monday', true));
        $this->attach(new Holiday('Anzac Day',25, 4, Holiday::MONDAYISED, 2014, null, false));
        $this->attach(new Holiday('Queens Birthday', null, null, null, null, 'first monday of june', false));
        $this->attach(new Holiday('Labour Day',null,null,null,null,'fourth monday of october', false));
        $this->attach(new Holiday('Christmas Day', 25, 12, Holiday::TUESDAYISED, null, null, false));
        $this->attach(new Holiday('Boxing Day', 26, 12, Holiday::MONDAYISED, null, null, false));

        /* Only consider AK Provincial as most ISPs are in Auckland */
        $this->attach(new Holiday('Auckland Provincial', 29, 1, Holiday::MONDAY_CLOSEST, null, null, false));

    }

    function occurrence($year) {
        $occurrence = array();
        foreach($this as $holiday) {
            /* @var $holiday Holiday */
            $occurrence[] = $holiday->occurrence($year);
        }
        return $occurrence;
    }

    function isWorkday($dateInt) {
        $year = date('Y',$dateInt);
        $dt = new DateTime('now');
        $dt->setTimestamp($dateInt);
        $dt->setTime(0, 0, 0);

        return ! (Holiday::isWeekend($dt->getTimestamp()) || in_array($dt->getTimestamp(), $this->occurrence($year)));
    }

    function workHoursHence($dateInt, $leadTimeHours) {
        $target = $dateInt;
        while ($leadTimeHours > 0) {
            $target = strtotime('+1 hours', $target);
            if ($this->isWorkday($target)) {
                $leadTimeHours--;
            }
        }

        return $target;
    }

    function workDaysHence($dateInt, $leadTimeDays) {
        return $this->workHoursHence($dateInt, $leadTimeDays*24);
    }

    /**
     * returns number of working hours between given two days
     *
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return int
     */
    function workHoursCount($start, $end) {
        $oneHour = new DateInterval("PT1H");
        $hours = 0;
        $nextHour = new DateTime("@".$end->getTimestamp());
        $nextHour->add($oneHour);
        foreach (new DatePeriod($start, $oneHour, $nextHour) as $day) {
            if ($this->isWorkday($day->getTimestamp())) {/* holiday */
                $hours++;
            }
        }

        return $hours;
    }

    /**
     * returns number of working days between given two days
     *
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return int
     */
    function workDaysCount($start, $end) {
        $oneday = new DateInterval("P1D");
        $nextDay = new DateTime("@".$end->getTimestamp());
        $nextDay->add($oneday);
        $days = 0;
        /* Iterate from $start up to $end+1 day, one day in each iteration.
          We add one day to the $end date, because the DatePeriod only iterates up to,
          not including, the end date. */
        foreach (new DatePeriod($start, $oneday, $nextDay) as $day) {
            if ($this->isWorkday($day->getTimestamp())) {
                $days++;
            }
        }

        return $days;
    }
}