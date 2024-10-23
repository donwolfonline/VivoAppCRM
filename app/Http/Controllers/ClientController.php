<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Plan;
use App\Models\User;
use App\Models\LoginDetail;
use App\Models\Utility;
use App\Imports\ClientImport;
use App\Exports\ClientExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{

    public function index()
    {
        if (\Auth::user()->type == 'company') {
            $status  = Client::$statues;
            $clients = User::where('type', 'client')->where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('client.index', compact('status', 'clients'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->type == 'company') {
            return view('client.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $default_language = Utility::getValByName('default_language');
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:120',
                    // 'password' => 'required|min:6',
                    'email' => [
                        'required',
                        Rule::unique('users')->where(function ($query) {
                            return $query->where('created_by', \Auth::user()->id);
                        })
                    ],
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $enableLogin = 0;
            if(!empty($request->password_switch) && $request->password_switch == 'on')
            {
                $enableLogin   = 1;
                $validator = \Validator::make(
                    $request->all(), ['password' => 'required|min:6']
                );
    
                if($validator->fails())
                {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }
            }

            $authUser    = \Auth::user();
            $creator     = User::find($authUser->creatorId());
            $totalClient = $authUser->countClients($creator->id);
            $plan        = Plan::find($creator->plan);

            $userpassword               = $request->input('password');

            if ($totalClient < $plan->max_client || $plan->max_client == -1) {
                $user             = new User();
                $user->name       = $request->name;
                $user->email      = $request->email;
                $user['password'] = !empty($userpassword) ? \Hash::make($userpassword) : null;
                $user->type       = 'client';
                $user->lang       = !empty($default_language) ? $default_language : 'en';;
                $user->created_by = \Auth::user()->creatorId();
                $user->email_verified_at = date("H:i:s");
                $user->avatar     = '';
                $user['is_enable_login'] = $enableLogin;
                $user->save();

                if (!empty($user)) {
                    $client             = new Client();
                    $client->user_id    = $user->id;
                    $client->client_id  = $this->clientNumber();
                    $client->created_by = \Auth::user()->creatorId();
                    $client->save();
                }

                $uArr = [
                    'email' => $user->email,
                    'password' => $request->password,
                ];

                $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr);

                return redirect()->route('client.index')->with('success', __('Client successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } else {
                return redirect()->back()->with('error', __('Your client limit is over, Please upgrade plan.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            $cId  = \Crypt::decrypt($id);
            $user = User::find($cId);

            $client = Client::where('user_id', $cId)->first();

            return view('client.view', compact('user', 'client'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($id)
    {
        if (\Auth::user()->type == 'company') {
            $user   = User::find($id);
            $client = Client::where('user_id', $id)->first();


            return view('client.edit', compact('user', 'client'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, $id)
    {

        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'mobile'        =>   'required',
                    'company_name'  =>   'required',
                    'address_1'     =>   'required',
                    'city'          =>   'required',
                    'state'         =>   'required',
                    'country'       =>   'required',
                    'zip_code'      =>   'required',

                ]
            );/*  */

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->name)) {
                $user       = User::find($id);
                $user->name = $request->name;
                $user->save();
            }

            $client               = Client::where('user_id', $user->id)->first();
            $client->company_name = $request->company_name;
            $client->mobile       = $request->mobile;
            $client->address_1    = $request->address_1;
            $client->address_2    = !empty($request->address_2) ? $request->address_2 : '';
            $client->tax_number   = !empty($request->tax_number) ? $request->tax_number : '';
            $client->website      = !empty($request->website) ? $request->website : '';
            $client->city         = $request->city;
            $client->state        = $request->state;
            $client->country      = $request->country;
            $client->zip_code     = $request->zip_code;
            $client->notes        = !empty($request->notes) ? $request->notes : '';
            $client->save();

            return redirect()->route('client.index')->with('success', 'Client successfully updated.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->type == 'company') {
            $user = User::find($id);
            $user->delete();

            $client = Client::where('user_id', $id)->first();
            $client->delete();

            return redirect()->route('client.index')->with('success', __('Client successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function clientNumber()
    {
        $latest = Client::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->client_id + 1;
    }

    public function importFile()
    {
        return view('client.import');
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

        $customers          =   (new ClientImport())->toArray(request()->file('file'))[0];
        $totalCustomer      =   count($customers) - 1;
        $errorArray         =   [];

        for ($i = 1; $i <= count($customers) - 1; $i++) {
            $customer = $customers[$i];
            $customerByEmail = User::where('email', $customer[1])->first();

            if (!empty($customerByEmail)) {
                $customerData = $customerByEmail;
            } else {
                $customerData = new User();
                $customerData->name = $customer[0];
                $customerData->email = $customer[1];
                $customerData->password = Hash::make($customer[2]);
                $customerData->type = 'client';
                $customerData->is_active = 1;
                $customerData->lang = 'en';
                $customerData->created_by = \Auth::user()->creatorId();
                $customerData->save();
            }
            if (!empty($customerData)) {
                $client = new Client();
                $client->user_id = $customerData->id;
                $client->client_id = $this->clientNumber();
                $client->created_by = \Auth::user()->creatorId();
                $client->save();
            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalCustomer . ' ' . 'record');


            foreach ($errorArray as $errorData) {

                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }

    public function clientPassword($id)
    {
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);

        $client = Client::where('user_id', $eId)->first();

        return view('client.reset', compact('user', 'client'));
    }

    public function clientPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'password' => 'required|confirmed|same:password_confirmation',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user                 = User::where('id', $id)->first();
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return redirect()->route('client.index')->with(
            'success',
            'Client Password successfully updated.'
        );
    }
    //start for client login details
    public function clientLog(Request $request)
    {
        $filteruser = User::where('type', 'client')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $filteruser->prepend('Select Client', '');
        $query = \DB::table('login_details')->join('users', 'login_details.user_id', '=', 'users.id')->select(\DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))->where(['login_details.created_by' => \Auth::user()->id])->where(['users.type' => 'client']);
        if (!empty($request->month)) {
            $query->whereMonth('date', date('m', strtotime($request->month)));
            $query->whereYear('date', date('Y', strtotime($request->month)));
        } else {
            $query->whereMonth('date', date('m'));
            $query->whereYear('date', date('Y'));
        }

        if (!empty($request->users)) {
            $query->where('user_id', '=', $request->users);
        }
        $userdetails = $query->get();

        $last_login_details = LoginDetail::where('created_by', \Auth::user()->creatorId())->get();

        return view('client.clientlog', compact('userdetails', 'last_login_details', 'filteruser'));
    }
    public function LoginManage($id)
    {
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);
        if($user->is_enable_login == 1)
        {
            $user->is_enable_login = 0;
            $user->save();
            return redirect()->back()->with('success', __('User login disable successfully.'));
        }
        else
        {
            $user->is_enable_login = 1;
            $user->save();
            return redirect()->back()->with('success', __('User login enable successfully.'));
        }
    }
}
