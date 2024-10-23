<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\User;
use App\Models\Utility;
use App\Models\Employee;
use Illuminate\Http\Request;

class TripController extends Controller
{

    public function index()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            if(\Auth::user()->type == 'employee')
            {
                $trips = Trip::where('created_by', '=', \Auth::user()->creatorId())->where('employee_id', '=', \Auth::user()->id)->get();
            }
            else
            {
                $trips = Trip::where('created_by', '=', \Auth::user()->creatorId())->get();
            }

            return view('trip.index', compact('trips'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        $employees = User::where('created_by', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');

        return view('trip.create', compact('employees'));
    }


    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'start_date' => 'required',
                                   'end_date' => 'required|date|after_or_equal:start_date',
                                   'purpose_of_visit' => 'required',
                                   'place_of_visit' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $travel                   = new Trip();
            $travel->employee_id      = $request->employee_id;
            $travel->start_date       = $request->start_date;
            $travel->end_date         = $request->end_date;
            $travel->purpose_of_visit = $request->purpose_of_visit;
            $travel->place_of_visit   = $request->place_of_visit;
            $travel->description      = $request->description;
            $travel->created_by       = \Auth::user()->creatorId();
            $travel->save();

            $employee = Employee::where('user_id',$request->employee_id)->first();
            $setting  = Utility::settings();
            
            if (isset($settings['twilio_trip_create_notification']) && $settings['twilio_trip_create_notification'] == 1) {
                $uArr = [
                    'purpose_of_visit' => $request->purpose_of_visit,
                    'place_of_visit'=> $request->place_of_visit,
                    'start_date'=> $request->start_date,
                    'end_date'=> $request->end_date,
                 ];
                Utility::send_twilio_msg('new_trip', $uArr);
                }
            return redirect()->route('trip.index')->with('success', __('Trip  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit(Trip $trip)
    {
        $employees = User::where('created_by', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');

        return view('trip.edit', compact('trip', 'employees'));
    }


    public function update(Request $request, Trip $trip)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'start_date' => 'required',
                                   'end_date' => 'required',
                                   'purpose_of_visit' => 'required',
                                   'place_of_visit' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $trip->employee_id      = $request->employee_id;
            $trip->start_date       = $request->start_date;
            $trip->end_date         = $request->end_date;
            $trip->purpose_of_visit = $request->purpose_of_visit;
            $trip->place_of_visit   = $request->place_of_visit;
            $trip->description      = $request->description;
            $trip->save();

            return redirect()->route('trip.index')->with('success', __('Trip successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Trip $trip)
    {
        if(\Auth::user()->type == 'company')
        {
            $trip->delete();

            return redirect()->route('trip.index')->with('success', __('Trip successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
