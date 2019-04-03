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

    function __construct(  $title, $nominal_day, $nominal_month, $mondayisation_mode, $mondayisation_effective_year, $directive, $easter_relative) {
        $this->title = $title;
        $this->nominal_day = $nominal_day;
        $this->nominal_month = $nominal_month;
        $this->mondayisation_mode = $mondayisation_mode;
        $this->mondayisation_effective_year = $mondayisation_effective_year;
        $this->directive = $directive;
        $this->easter_relative = $easter_relative;
    }

    static function weekend() {
        return array(self::SATURDAY, self::SUNDAY);
    }

    static function isWeekend($dt) {
        return in_array(date('N', $dt), self::weekend());
    }

    static function yearStartDate($y) {
        return strtotime("first day of january $y");
    }

    static function DoW($dt) {
        return date('N',$dt);
    }

    static function closest($dt, $dta) {
        $c = null;
        foreach($dta as $d) {
            if ($c == null || abs($dt - $d) < abs($dt - $c)) {
                $c = $d;
            }
        }

        return $c;
    }

    function nominalDate($y) {
        return mktime (0, 0, 0, $this->nominal_month, $this->nominal_day, $y);
    }

    static function easterSunday($year, $format = 'd.m.Y') {
        $easter = new DateTime('@' . easter_date($year));
        $easter->setTimezone(new DateTimeZone('Pacific/Auckland')); //NB: workaround for timezone-related bugs in PHP easter_date
        return strtotime($easter->format($format));
    }

    function occurrence($year) {
        if ($this->directive) {
            $observed = strtotime($this->directive, $this->easter_relative ? self::easterSunday($year) : self::yearStartDate($year));
        } else {
            $observed = $this->nominalDate($year);
        }

        if ($this->mondayisation_mode) {
            if ($this->mondayisation_effective_year < $year) {
                switch ($this->mondayisation_mode) {
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