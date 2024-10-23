<?php

namespace App\Http\Controllers;

use App\Imports\HolidayImport;
use App\Models\Holiday as localHoliday;
use App\Models\Utility;
use Illuminate\Http\Request;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class HolidayController extends Controller
{

    public function index(Request $request)
    {
        $usr   =  \Auth::user();
        if ($usr->type == 'company' || $usr->type == 'employee') {

            $transdate        =  date('Y-m-d', time());
            $holidays         =  localHoliday::where('created_by', $usr->creatorId());

            if (!empty($request->start_date)) {
                $holidays->where('date', '>=', $request->start_date);
            }
            if (!empty($request->end_date)) {
                $holidays->where('date', '<=', $request->end_date);
            }

            $holidays                   =   $holidays->get();
            $arrHolidays                =   [];
            $holidays_current_month     =   localHoliday::whereMonth('date', date('m'))->whereYear('date', date('Y'))->where('created_by', $usr->creatorId())->get(['id', 'occasion', 'date']);

            // dd($holidays_current_month);
            foreach ($holidays as $holiday) {

                $arr['id']              =      $holiday['id'];
                $arr['title']           =      $holiday['occasion'];
                $arr['start']           =      $holiday['date'];
                $arr['className']       =      'event-primary';
                $arr['url']             =      route('holiday.edit', $holiday['id']);
                $arrHolidays[]          =      $arr;
            }
            $arrHolidays     = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrHolidays)));
            $settings        = Utility::settings();

            return view('holiday.index', compact('holidays', 'arrHolidays', 'transdate', 'holidays_current_month', 'settings'));
        }
    }

    public function create()
    {
        if (\Auth::user()->type == 'company') {
            return view('holiday.create');
        }
    }

    public function store(Request $request)
    {

        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'occasion' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $holiday = new localHoliday();
            $holiday->date = $request->date;
            $holiday->occasion = $request->occasion;
            $holiday->created_by = \Auth::user()->creatorId();
            $holiday->save();

            //==Slack Notificaton
            $settings = Utility::settings();

            // if (isset($settings['holiday_create_notification']) && $settings['holiday_create_notification'] == 1) {
            //     $msg = $request->occasion . ' ' . __("holiday on") . ' ' . $request->date . '.';
            //     Utility::send_slack_msg($msg);
            // }

            if (isset($settings['holiday_create_notification']) && $settings['holiday_create_notification'] == 1) {
                $uArr = [
                    'date' => $holiday->date,
                    'occasion' => $holiday->occasion,
                ];
                Utility::send_slack_msg('new_holiday', $uArr);
            }

            if (isset($settings['telegram_holiday_create_notification']) && $settings['holiday_create_notification'] == 1) {
                $uArr = [
                    'date' => $holiday->date,
                    'occasion' => $holiday->occasion,
                ];
                Utility::send_telegram_msg('new_holiday', $uArr);
            }

            // if (isset($settings['telegram_holiday_create_notification']) && $settings['telegram_holiday_create_notification'] == 1) {
            //     $resp = $request->occasion . ' ' . __("holiday on") . ' ' . $request->date . '.';
            //     Utility::send_telegram_msg($resp);
            // }
            if ($request->get('synchronize_type') == 'google_calender') {
                $type = 'holiday';
                $request1 = new GoogleEvent();
                $request1->title = $request->occasion;
                $request1->start_date = $request->date;
                $request1->end_date = $request->date;
                Utility::addCalendarData($request1, $type);
            }

            //webhook
            $module = "New Holiday";

            $webhook = Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($holiday);

                // 1 parameter is URL , 2  (Holiday Data) parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    return redirect()->back()->with('success', __('Holiday successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('Holiday call failed.'));
                }
            }


            return redirect()->route('holiday.index')->with(
                'success',
                'Holiday successfully created.'
            );
        }
    }

    public function show(Holiday $holiday)
    {
    }

    public function edit(localHoliday $holiday)
    {
            return view('holiday.edit', compact('holiday'));
    }

    public function update(Request $request, localHoliday $holiday)
    {

        

        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date'      => 'required',
                    'occasion'  => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $holiday->date      = $request->date;
            $holiday->occasion  = $request->occasion;
            $holiday->save();

            return redirect()->route('holiday.index')->with(
                'success',
                'Holiday successfully updated.'
            );
        }
    }

    public function destroy(localHoliday $holiday)
    {
        if (\Auth::user()->type == 'company') {
            $holiday->delete();

            return redirect()->route('holiday.index')->with(
                'success',
                'Holiday successfully deleted.'
            );
        }
    }

    public function importFile()
    {
        return view('holiday.import');
    }

    public function import(Request $request)
    {

        $rules = [
            'file' => 'required|mimes:csv,txt',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $holiday = (new HolidayImport())->toArray(request()->file('file'))[0];

        $totalholiday   = count($holiday) - 1;
        $errorArray     = [];
        for ($i = 1; $i <= count($holiday) - 1; $i++) {

            $customer   = $holiday[$i];
            $customerByEmail = localHoliday::where('date', $customer[1])->first();
            if (!empty($customerByEmail)) {
                $holidayData = $customerByEmail;
            } else {
                $holidayData = new localHoliday();
            }

            $holidayData->date          =   $customer[0];
            $holidayData->occasion      =   $customer[1];
            $holidayData->created_by    =   \Auth::user()->creatorId();

            if (empty($holidayData)) {
                $errorArray[] = $holidayData;
            } else {
                $holidayData->save();
            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg'] = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg'] = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalholiday . ' ' . 'record');

            foreach ($errorArray as $errorData) {

                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }

    public function get_holiday_data(Request $request)
    {

        $arrayJson = [];

        if ($request->get('calender_type') == 'google_calender') {

            $type = 'holiday';
            $arrayJson = Utility::getCalendarData($type);
        } else {
            $usr              =  \Auth::user();
            $localHolidays    =  localHoliday::where('created_by', $usr->creatorId())->get();

            foreach ($localHolidays as $val) {
                $end_date   =   date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id"            =>    $val->id,
                    "title"         =>    $val->occasion,
                    "start"         =>    $val->date,
                    "end"           =>    $val->date,
                    "className"     =>    'event-primary',
                    "textColor"     =>    '#FFF',
                    "allDay"        =>    true,
                    "url"           =>    route('holiday.edit', $val['id']),
                ];
            }
        }

        return $arrayJson;
    }
}
