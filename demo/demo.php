<?php

use BigV\Holiday;
use BigV\NZHolidays;

require __DIR__ . "/vendor/autoload.php";

$holidays = new NZHolidays();
for ($year = 2019; $year <= 2021; $year++) {
    echo "$year --------------------------------------------------------------\n";
    foreach ($holidays as $holiday) {
        /* @var $holiday Holiday */
        echo date(DATE_RFC822, $holiday->occurrence($year)) . ' ' . $holiday->title . "\n";
    }
}

echo "-------------------------------------------------------------------\n";

$today = time();
echo date(DATE_RFC822, $today).($holidays->isWorkday($today) ? ' Today is a working day' : ' Today is not a working day')."\n";

$xmas = mktime (0, 0, 0, 12, 25, 2019);
echo date(DATE_RFC822, $xmas).($holidays->isWorkday($xmas) ? ' Xmas is a working day' : ' Xmas is not a working day')."\n";

$saturday = mktime (0, 0, 0, 6, 1, 2019);
echo date(DATE_RFC822, $saturday).($holidays->isWorkday($saturday) ? ' Jun 1 is a working day' : ' Jun 1 is not a working day')."\n";

$today = time();
$target = $holidays->workHoursHence($today, 48);
echo "48 work hours from " . date(DATE_RFC822, $today) . " is " . date(DATE_RFC822, $target) . "\n";