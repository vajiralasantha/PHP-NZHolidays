<?php
namespace BigV;

use DateInterval;
use DatePeriod;
use DateTime;
use SplObjectStorage;

class NZHolidays extends SplObjectStorage {

    /**
     * NZHolidays default constructor.
     */
    public function __construct() {
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

    /**
     * Function to get holiday occurrence of the year
     *
     * @param int $year Year
     *
     * @return array
     *
     * @throws \Exception
     */
    private function occurrence($year) {
        $occurrence = array();
        foreach($this as $holiday) {
            /* @var $holiday Holiday */
            $occurrence[] = $holiday->occurrence($year);
        }

        return $occurrence;
    }

    /**
     * Function to calculate if given date is working date or not.
     *
     * @param int $dateInt Unix timestamp of the date
     *
     * @return bool|null
     */
    public function isWorkday($dateInt) {
        $year = date('Y',$dateInt);
        try {
            $dt = new DateTime('now');
            $dt->setTimestamp($dateInt);
            $dt->setTime(0, 0, 0);

            return ! (Holiday::isWeekend($dt->getTimestamp()) || in_array($dt->getTimestamp(), $this->occurrence($year)));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Function to calculate working hour since a given date.
     *
     * @param int $dateInt Current Unix timestamp
     * @param int $leadTimeHours Number of hours forward
     *
     * @return int Calculated Unix timestamp
     */
    public function workHoursHence($dateInt, $leadTimeHours) {
        $target = $dateInt;
        while ($leadTimeHours > 0) {
            $target = strtotime('+1 hours', $target);
            if ($this->isWorkday($target)) {
                $leadTimeHours--;
            }
        }

        return $target;
    }

    /**
     * Function to calculate working days since a given date.
     *
     * @param int $dateInt Current Unix timestamp
     * @param int $leadTimeDays Number of days forward
     *
     * @return int Calculated Unix timestamp
     */
    public function workDaysHence($dateInt, $leadTimeDays) {
        return $this->workHoursHence($dateInt, $leadTimeDays * 24);
    }

    /**
     * Function to return number of working hours between given two days.
     *
     * @param DateTime $start Start date
     * @param DateTime $end End date
     *
     * @return int Number of hours
     *
     * @throws \Exception
     */
    public function workHoursCount($start, $end) {
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
     * Function to return number of working days between given two days.
     *
     * @param DateTime $start Start date
     * @param DateTime $end End date
     *
     * @return int Number of days
     *
     * @throws \Exception
     */
    public function countWorkDays($start, $end) {
        $oneDay = new DateInterval("P1D");
        $nextDay = new DateTime("@".$end->getTimestamp());
        $nextDay->add($oneDay);
        $days = 0;
        /* Iterate from $start up to $end+1 day, one day in each iteration.
          We add one day to the $end date, because the DatePeriod only iterates up to,
          not including, the end date. */
        foreach (new DatePeriod($start, $oneDay, $nextDay) as $day) {
            if ($this->isWorkday($day->getTimestamp())) {
                $days++;
            }
        }

        return $days;
    }
}