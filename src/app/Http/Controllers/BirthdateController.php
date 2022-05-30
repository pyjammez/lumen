<?php

namespace App\Http\Controllers;

use App\Models\Birthdate;
use Illuminate\Http\Request;
use App\Helpers\BirthdateHelper;

class BirthdateController extends Controller
{
    public function index(Request $request, BirthdateHelper $birthdateHelper) {
        $clientTimezone = $request->input('timezone', 'America/Los_Angeles');
        $nowDatetime = $request->input('datetime', date("Y-m-d H:i:s"));
        $birthdates = [];

        foreach (Birthdate::all() as $row) {
            $birthdateHelper->setDates($nowDatetime, $clientTimezone, $row->birthdate . " 00:00:00", $row->timezone);
            $isBirthday = $birthdateHelper->getIsBirthday();
            $sortValue = $birthdateHelper->getActiveThenNextBirthdaySortValue();
            $message = $row->name . " is " . $birthdateHelper->getIntervalMessage();
            $birthdates[] = [
                "name" => $row->name,
                "birthdate" => $row->birthdate,
                "timezone" => $row->timezone,
                "isBirthday" => $isBirthday,
                "message" => $message,
                "sort" => $sortValue
            ];
        }

        array_multisort(array_column($birthdates, 'sort'), SORT_ASC, $birthdates);
        return response()->json($birthdates);
    }

    public function store(Request $request) {
        $this->validate($request, [
            'name' => 'required',
            'birthdate' => 'required|date_format:Y-m-d',
            'timezone' => 'required|timezone'
        ]);

        $bday = new Birthdate();
        $bday->name = $request->name;
        $bday->birthdate = $request->birthdate;
        $bday->timezone = $request->timezone;
        $bday->save();

        return response()->json([
            'status' => 'success',
            'data' => $bday
        ]);
    }
}
