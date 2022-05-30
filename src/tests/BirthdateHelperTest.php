<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Helpers\BirthdateHelper;

class BirthdateHelperTest extends TestCase
{
    /**
     * @dataProvider provider_getReadableIntervalBetweenNowAndNextBirthday
     */
    public function test_getReadableIntervalBetweenNowAndNextBirthday($now, $nowTz, $birthdate, $birthdateTz, $expected)
    {
        $birthdateHelper = new BirthdateHelper;
        $birthdateHelper->setDates($now, $nowTz, $birthdate, $birthdateTz);
        $interval = $birthdateHelper->getIntervalBetweenNowAndNextBirthday();
        $this->assertEquals($expected, $birthdateHelper->getReadableInterval($interval));
    }

    public function provider_getReadableIntervalBetweenNowAndNextBirthday()
    {
        return [
            "birthday later in year" => [
                "2022-05-29 03:20:00", "America/Los_Angeles",
                "1933-05-04 00:00:00", "America/Los_Angeles",
                "11 months, 4 days"
            ],

            "birthday tomorrow" => [
                "2022-05-29 03:20:00", "America/Los_Angeles",
                "1986-05-30 00:00:00", "America/Los_Angeles",
                "20 hours"
            ]
        ];
    }

    /**
     * @dataProvider provider_getReadableIntervalBetweenNowAndEndOfNight
     */
    public function test_getReadableIntervalBetweenNowAndEndOfNight($now, $nowTz, $birthdate, $birthdateTz, $expected)
    {
        $birthdateHelper = new BirthdateHelper;
        $birthdateHelper->setDates($now, $nowTz, $birthdate, $birthdateTz);
        $interval = $birthdateHelper->getIntervalBetweenNowAndEndOfNight();
        $this->assertEquals($expected, $birthdateHelper->getReadableInterval($interval));
    }

    public function provider_getReadableIntervalBetweenNowAndEndOfNight()
    {
        return [
            "early morning" => [
                "2022-05-29 03:20:00", "America/Los_Angeles",
                "1986-05-29 00:00:00", "America/Los_Angeles",
                "20 hours"
            ],

            "within 1 and 2 hours left" => [
                "2022-05-29 22:20:00", "America/Los_Angeles",
                "1986-05-29 00:00:00", "America/Los_Angeles",
                "1 hour"
            ],

            "less than an hour left" => [
                "2022-05-29 23:20:00", "America/Los_Angeles",
                "1986-05-29 00:00:00", "America/Los_Angeles",
                "Less than an hour"
            ],
        ];
    }

    /**
     * @dataProvider provider_scenarios
     */
    public function test_scenarios($now, $nowTz, $birthdate, $birthdateTz, $nextBirthday, $age, $isBirthday)
    {
        $birthdateHelper = new BirthdateHelper;
        $birthdateHelper->setDates($now, $nowTz, $birthdate, $birthdateTz);
        $this->assertEquals($nextBirthday, $birthdateHelper->getNextBirthdayDatetime());
        $this->assertEquals($age, $birthdateHelper->getAge());
        $this->assertEquals($isBirthday, $birthdateHelper->getIsBirthday());
    }

    public function provider_scenarios()
    {
        return [
            "birthday later in year" => [
                "2022-05-28 08:00:00", "America/Los_Angeles", // now and client's timezone
                "1985-07-30 00:00:00", "America/Los_Angeles", // birthday and the user's believed timezone
                "2022-07-30", 36, false], // next birthday, current age, is it their birthday today?

            "birthday earlier in year" => [
                "2022-12-28 08:00:00", "America/Los_Angeles",
                "1985-07-30 00:00:00", "America/Los_Angeles",
                "2023-07-30", 37, false],

            "birthday same day" => [
                "2022-07-30 08:00:00", "America/Los_Angeles",
                "1985-07-30 00:00:00", "America/Los_Angeles",
                "2023-07-30", 37, true],

            "birthday in an hour" => [
                "2022-07-29 23:00:00", "America/Los_Angeles",
                "1985-07-30 00:00:00", "America/Los_Angeles",
                "2022-07-30", 36, false],

            "birthday an hour ago" => [
                "2022-07-30 01:00:00", "America/Los_Angeles",
                "1985-07-30 00:00:00", "America/Los_Angeles",
                "2023-07-30", 37, true],

            // leap day doesn't exist this year but we still want to reward them
            // at the time that would normally be their birthday so we pretend
            // that march 1st is their birthday.
            "birthday on leap day tomorrow" => [
                "2022-02-28 08:00:00", "America/Los_Angeles",
                "2000-02-29 00:00:00", "America/Los_Angeles",
                "2022-03-01", 21, false],

            "birthday on leap day yesterday" => [
                "2022-03-01 08:00:00", "America/Los_Angeles",
                "2000-02-29 00:00:00", "America/Los_Angeles",
                "2023-03-01", 22, true],

            // leap year and leap birthday.
            "birthday tomorrow on leap day and year" => [
                "2024-02-28 08:00:00", "America/Los_Angeles",
                "2000-02-29 00:00:00", "America/Los_Angeles",
                "2024-02-29", 23, false],

            "birthday yesterday on leap day and leap year" => [
                "2024-03-01 08:00:00", "America/Los_Angeles",
                "2000-02-29 00:00:00", "America/Los_Angeles",
                "2025-02-29", 24, false],

            // in new york it's already 2am on the birthday
            "birthday in an hour future timezone" => [
                "2022-07-29 23:00:00", "America/Los_Angeles",
                "1985-07-30 00:00:00", "America/New_York",
                "2023-07-30", 37, true],

            // in los angeles it's still 8pm and not their birthday yet
            "birthday in an hour past timezone" => [
                "2022-07-29 23:00:00", "America/New_York",
                "1985-07-30 00:00:00", "America/Los_Angeles",
                "2022-07-30", 36, false],

            // in new york it's 4am and on their birthday
            "birthday an hour ago future timezone" => [
                "2022-07-30 01:00:00", "America/Los_Angeles",
                "1985-07-30 00:00:00", "America/New_York",
                "2023-07-30", 37, true],

            // in los angeles it's still 10pm the previous night, so it's not their birthday yet
            "birthday an hour ago past timezone" => [
                "2022-07-30 01:00:00", "America/New_York",
                "1985-07-30 00:00:00", "America/Los_Angeles",
                "2022-07-30", 36, false],
        ];
    }
}
