<?php

namespace App\Helpers;

class BirthdateHelper
{
    private $nowDatetime;
    private $nowTimezone;
    private $nowDateTimeObject;
    private $birthdateDatetime;
    private $birthdateTimezone;
    private $birthdateDateTimeObject;

    public function setDates($nowDatetime, $nowTimezone, $birthdateDatetime, $birthdateTimezone)
    {
        $this->nowDatetime = $nowDatetime;
        $this->nowTimezone = $nowTimezone;
        $this->nowDateTimeObject = new \DateTime($nowDatetime, new \DateTimeZone($nowTimezone));
        $this->nowDateTimeObject->setTimezone(new \DateTimeZone($birthdateTimezone));

        $this->birthdateDatetime = $birthdateDatetime;
        $this->birthdateTimezone = $birthdateTimezone;
        $this->birthdateDateTimeObject = new \DateTime($birthdateDatetime, new \DateTimeZone($birthdateTimezone));
    }

    public function getAge()
    {
        return $this->nowDateTimeObject->diff($this->birthdateDateTimeObject)->y;
    }

    public function getBirthdayMonthDay()
    {
        // if leap day and not a leap year, treat their birthday as the next day.
        $monthDay = $this->birthdateDateTimeObject->format('m-d');
        $isLeapYear = $this->nowDateTimeObject->format('Y') % 4 === 0;
        return ($monthDay == "02-29" && !$isLeapYear) ? "03-01" : $monthDay;
    }

    public function getIsBirthday()
    {
        return $this->nowDateTimeObject->format("m-d") == $this->getBirthdayMonthDay();
    }

    public function getNextBirthdayDatetime()
    {
        $currentYear = $this->nowDateTimeObject->format('Y');
        $nowMonthDay = $this->nowDateTimeObject->format("m-d");
        $birthdayMonthDay = $this->getBirthdayMonthDay();
        if ($nowMonthDay >= $birthdayMonthDay) $currentYear++;
        return $currentYear . "-" . $birthdayMonthDay;
    }

    public function getIntervalBetweenNowAndNextBirthday()
    {
        $nextDatetime = $this->getNextBirthdayDatetime();
        $nextBirthday = new \DateTime($nextDatetime, new \DateTimeZone($this->birthdateTimezone));
        return $this->nowDateTimeObject->diff($nextBirthday);
    }

    public function getIntervalBetweenNowAndEndOfNight()
    {
        $currentYear = $this->nowDateTimeObject->format('Y');
        $endOfNight = clone $this->birthdateDateTimeObject;
        $month = $endOfNight->format('m');
        $day = $endOfNight->format('d');
        $endOfNight->setDate($currentYear, $month, $day);
        $endOfNight->setTime(23, 59, 59);
        return $this->nowDateTimeObject->diff($endOfNight);
    }

    public function getReadableInterval($interval)
    {
        $string = [];

        if ($m = $interval->m) {
            $string[] = $m . ($m > 1 ? " months" : " month");
        }

        if ($d = $interval->d) {
            $string[] = $d . ($d > 1 ? " days" : " day");
        }

        if (empty($string)) {
            return ($h = $interval->h)
                ? $h . ($h > 1 ? " hours" : " hour")
                : "Less than an hour";
        }

        return implode(", ", $string);
    }

    public function getIntervalMessage()
    {
        $interval = ($isBirthday = $this->getIsBirthday())
            ? $this->getIntervalBetweenNowAndEndOfNight()
            : $this->getIntervalBetweenNowAndNextBirthday();

        $readableInterval = $this->getReadableInterval($interval);

        return $isBirthday
            ? $this->getAge() . " years old today ($readableInterval remaining in " . $this->birthdateTimezone . ")"
            : $this->getAge()+1 . " years old in $readableInterval in " . $this->birthdateTimezone;
    }

    public function getActiveThenNextBirthdaySortValue()
    {
        $nextBirthdayInterval = $this->getIntervalBetweenNowAndNextBirthday();

        return $this->getIsBirthday()
            ? abs($nextBirthdayInterval->format("%h%i")) * .001 // active birthdays on top
            : $nextBirthdayInterval->format("%a%h");
    }
}
