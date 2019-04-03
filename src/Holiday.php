<?php

namespace BigV;

use DateTime;
use DateTimeZone;

class Holiday {

    const MONDAYISED = 1;
    const TUESDAYISED = 2;
    const MONDAY_CLOSEST = 3;

    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $nominalDay;

    /**
     * @var int
     */
    public $nominalMonth;

    /**
     * @var int
     */
    public $mondayisationMode;

    /**
     * @var null|int
     */
    public $mondayisationEffectiveYear;

    /**
     * @var null|string
     */
    public $directive;

    /**
     * @var bool
     */
    public $easterRelative;

    /**
     * Holiday default constructor.
     *
     * @param $title
     * @param $nominalDay
     * @param $nominalMonth
     * @param $mondayisationMode
     * @param $mondayisationEffectiveYear
     * @param $directive
     * @param $easterRelative
     */
    public function __construct($title, $nominalDay, $nominalMonth, $mondayisationMode, $mondayisationEffectiveYear, $directive, $easterRelative) {
        $this->title = $title;
        $this->nominalDay = $nominalDay;
        $this->nominalMonth = $nominalMonth;
        $this->mondayisationMode = $mondayisationMode;
        $this->mondayisationEffectiveYear = $mondayisationEffectiveYear;
        $this->directive = $directive;
        $this->easterRelative = $easterRelative;
    }

    /**
     * Function to get weekend day number of the week.
     *
     * @return array
     */
    public static function getWeekend() {
        return array(self::SATURDAY, self::SUNDAY);
    }

    /**
     * Function to see if a given date is weekend.
     *
     * @param int $date Unix timestamp of the date
     *
     * @return bool
     */
    public static function isWeekend($date) {
        return in_array(date('N', $date), self::getWeekend());
    }

    /**
     * Function to determine the 1st day of a given year.
     *
     * @param int $year Year
     *
     * @return false|int
     */
    public static function yearStartDate($year) {
        return strtotime("first day of january $year");
    }

    /**
     * Function to get the day number of the week.
     *
     * @param int $date Unix timestamp of the date
     *
     * @return false|string
     */
    public static function dayOfTheWeek($date) {
        return date('N', $date);
    }

    /**
     * @param int $date
     * @param array $dates
     *
     * @return null|int
     */
    static function closest($date, $dates) {
        $c = null;
        foreach($dates as $d) {
            if ($c == null || abs($date - $d) < abs($date - $c)) {
                $c = $d;
            }
        }

        return $c;
    }

    /**
     * Function to get the nominal date of a given year.
     *
     * @param int $year Year
     *
     * @return false|int
     */
    public function nominalDate($year) {
        return mktime (0, 0, 0, $this->nominalMonth, $this->nominalDay, $year);
    }

    /**
     * @param int $year year
     * @param string $format Date format
     *
     * @return false|int
     *
     * @throws \Exception
     */
    public static function easterSunday($year, $format = 'd.m.Y') {
        $easter = new DateTime('@' . easter_date($year));
        $easter->setTimezone(new DateTimeZone('Pacific/Auckland')); //NB: workaround for timezone-related bugs in PHP easter_date
        return strtotime($easter->format($format));
    }

    /**
     * Function to get holiday occurrence of the year
     *
     * @param int $year Year
     *
     * @return false|int|null
     *
     * @throws \Exception
     */
    public function occurrence($year) {
        if ($this->directive) {
            $observed = strtotime($this->directive, $this->easterRelative ? self::easterSunday($year) : self::yearStartDate($year));
        } else {
            $observed = $this->nominalDate($year);
        }

        if ($this->mondayisationMode) {
            if ($this->mondayisationEffectiveYear < $year) {
                switch ($this->mondayisationMode) {
                    case self::MONDAYISED:
                        if (self::isWeekend($observed)) {
                            $observed = strtotime('next monday', $observed);
                        }
                        break;
                    case self::TUESDAYISED:
                        if (self::isWeekend($observed)) {
                            $observed = strtotime('next tuesday', $observed);
                        }
                        break;
                    case self::MONDAY_CLOSEST:
                        $observed = self::closest($observed, array(strtotime('next monday', $observed), strtotime('this monday', $observed), strtotime('last monday', $observed)));
                        break;
                    default:
                        pass;
                        break;
                }
            }
        }

        return $observed;
    }
}