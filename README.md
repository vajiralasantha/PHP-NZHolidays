# PHP-NZHolidays
A light weight PHP class that can calculate public holidays in New Zealand.

Usage:

Install via composer

```"vajiral/php-php-nzholidays": "1.0.0"```

In your PHP file

```php
<?php

use BigV\Holiday;
use BigV\NZHolidays;

require __DIR__ . "/../vendor/autoload.php";

$holidays = new NZHolidays();
for ($year=2013; $year <= 2018; $year++) {
     echo "$year --------------------------------------------------------------\n";
     foreach ($holidays as $holiday) {
         /* @var $holiday Holiday */
         echo date(DATE_RFC822, $holiday->occurrence($year)) . ' ' . $holiday->title . "\n";
     }
}

$today = time();
echo date(DATE_RFC822, $today).($holidays->isWorkday($today) ? ' today is a work day' : ' today is not a work day')."\n";

$xmas = mktime (0, 0, 0, 12, 25, 2013);
echo date(DATE_RFC822, $xmas).($holidays->isWorkday($xmas) ? ' xmas is a work day' : ' xmas is not a work day')."\n";

$saturday = mktime (0, 0, 0, 6, 1, 2013);
echo date(DATE_RFC822, $saturday).($holidays->isWorkday($saturday) ? ' Jun 1 is a work day' : ' Jun 1 is not a work day')."\n";

$today = time();
$target = $holidays->workHoursHence($today, 2);
echo "48 work hours from ".date(DATE_RFC822, $today)." is ".date(DATE_RFC822, $target)."\n";
```