<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class BirthdateControllerTest extends TestCase
{
    public function test_BirthdateController_index()
    {
        $response = $this->call('GET', '/birthdays');

        $this->assertEquals(200, $response->status());
    }

    public function test_BirthdateController_store()
    {
        $this->json('POST', 'birthdays', [
            'name' => 'James Hilton',
            'timezone' => 'America/Los_Angeles',
            'birthdate' => '1985-07-30'
        ])->seeJson([
            'status' => 'success'
        ]);

        $this->json('POST', 'birthdays', [
            'name' => 'James Hilton',
            'timezone' => 'America/LosAngeles',
            'birthdate' => '1985-07-30'
        ])->seeJsonEquals([
            'timezone' => ['The timezone must be a valid timezone.']
        ]);

        $this->json('POST', 'birthdays', [
            'name' => 'James Hilton',
            'timezone' => 'America/Los_Angeles',
            'birthdate' => '85-07-30'
        ])->seeJsonEquals([
            'birthdate' => ['The birthdate does not match the format Y-m-d.']
        ]);
    }
}
