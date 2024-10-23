<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CustomField;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Employee;
use App\Models\LoginDetail;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Mail\CommonEmailTemplate;
use App\Mail\TestMail;
use PHPUnit\Util\Test;
use Lab404\Impersonate\Models\Impersonate;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function index()
    {
        $user = \Auth::user();
        if ($user->type == 'super admin') {
            if ($user->type == 'super admin') {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'company')->get();
            }
            // dd($user);
            $settings = Utility::settings();
            $admin_payment_setting= Utility::getAdminPaymentSettings();
            return view('user.index', compact('users','settings','admin_payment_setting'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        return view('user.create');
    }


    public function store(Request $request)
    {
        $usr = \Auth::user();
        $default_language   = Utility::getValByName('default_language');
        $settings = Utility::settings();
        $referralCode = $this->generateReferralCode();


        $validator          = \Validator::make(
            $request->all(),
            [
                'name'      => 'required',
                'email'     => [
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
        $userpassword               = $request->input('password');


        if($settings['email_verificattion'] == 'on'){
        $user                       =   new User();
        $user['name']               =   $request->name;
        $user['email']              =   $request->email;
        $user['password'] = !empty($userpassword) ? \Hash::make($userpassword) : null;
        $user['type']               =   'company';
        $user['lang']               =   !empty($default_language) ? $default_language : 'en';
        $user['created_by']         =   $usr->creatorId();
        $user['plan']               =   Plan::first()->id;
        $user['avatar']             =   '';
        $user['is_enable_login'] = $enableLogin;
        $user['referral_code'] = $referralCode;

        $user->save();
        $user->userDefaultData();
        }
        else{
        $user                       =   new User();
        $user['name']               =   $request->name;
        $user['email']              =   $request->email;
        $user['password'] = !empty($userpassword) ? \Hash::make($userpassword) : null;
        $user['type']               =   'company';
        $user['lang']               =   !empty($default_language) ? $default_language : 'en';
        $user['created_by']         =   $usr->creatorId();
        $user['email_verified_at']  =   date("H:i:s");
        $user['plan']               =   Plan::first()->id;
        $user['avatar']             =   '';
        $user['is_enable_login'] = $enableLogin;
        $user['referral_code'] = $referralCode;
        $user->save();
        $user->userDefaultData();
        }

        Utility::defaultChartAccountdata($user->id);
        // Utility::chartOfAccountData($user);

        $uArr = [
            'email'     => $request->email,
            'password'  => $request->password,
        ];

        Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr);
        return redirect()->back()->with('success', __('User successfully created.'));

        // $email_verificattion = DB::table('settings')->select('value')->where('name', 'email_verificattion')->frist();

    }
    private function generateReferralCode()
    {
        $referralCode = random_int(100000, 999999); // Generate a random integer between 100000 and 999999 (inclusive)

        // Check if the generated referral code already exists in the database
        while (User::where('referral_code', $referralCode)->exists()) {
            $referralCode = random_int(100000, 999999); // Generate new referral code until unique
        }

        return $referralCode;
    }

    public function show($id)
    {

    }


    public function edit($id)
    {
        $user = User::find($id);

        return view('user.edit', compact('user'));
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->type == 'super admin') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email,' . $id,
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $user            =   User::find($id);
            $user['name']    =   $request->name;
            $user['email']   =   $request->email;
            $user->save();
            return redirect()->back()->with('success', __('User successfully updated.'));
        }
    }

    public function destroy($id)
    {
            $user = User::find($id);
            if ($user) {
                if (\Auth::user()->type == 'super admin') {

                    User::where('created_by', $id)->delete();

                    Client::where('created_by', $id)->delete();

                    Employee::where('created_by', $id)->delete();

                    $user->delete();
                } else {
                    $user->delete();
                }
                return redirect()->route('user.index')->with('success', __('User successfully deleted .'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
    }

    public function profile()
    {
        $userDetail = \Auth::user();

        return view('user.profile', compact('userDetail'));
    }

    public function editprofile(Request $request)
    {
        $userDetail = \Auth::user();


        $user = User::findOrFail($userDetail['id']);
        $this->validate(
            $request,
            [
                'name' => 'required|max:120',
                'email' => 'required|email|unique:users,email,' . $userDetail['id'],
            ]
        );

        if ($user->type == 'client') {
            $this->validate(
                $request,
                [
                    'mobile'        =>   'required',
                    'address_1'     =>   'required',
                    'city'          =>   'required',
                    'state'         =>   'required',
                    'country'       =>   'required',
                    'zip_code'      =>   'required',
                ]
            );
            $client                 =        Client::where('user_id', $user->id)->first();
            $client->mobile         =        $request->mobile;
            $client->address_1      =        $request->address_1;
            $client->address_2      =        $request->address_2;
            $client->city           =        $request->city;
            $client->state          =        $request->state;
            $client->country        =        $request->country;
            $client->zip_code       =        $request->zip_code;
            $client->save();
        }

        if ($request->hasFile('profile')) {
            $filenameWithExt        = $request->file('profile')->getClientOriginalName();
            $filename               = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension              = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore        = $filename . '_' . time() . '.' . $extension;
            $settings               = Utility::getStorageSetting();

            // $dir        = storage_path('uploads/avatar/');
            if ($settings['storage_setting'] == 'local') {
                $dir        = 'uploads/avatar/';
            } else {
                $dir        = 'uploads/avatar';
            }

            $image_path = $dir . $userDetail['avatar'];

            if (File::exists($image_path)) {
                File::delete($image_path);
            }

            $url = '';
            $path = Utility::upload_file($request, 'profile', $fileNameToStore, $dir, []);

            if ($path['flag'] == 1) {
                $url = $path['url'];
            } else {
                return redirect()->route('profile', \Auth::user()->id)->with('error', __($path['msg']));
            }
            // $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);

        }

        if (!empty($request->profile)) {
            $user['avatar'] = $fileNameToStore;
        }
        $user['name']  = $request['name'];
        $user['email'] = $request['email'];
        $user->save();


        return redirect()->back()->with('success', 'Profile successfully updated.');
    }

    public function updatePassword(Request $request)
    {
        if (\Auth::Check()) {
            $request->validate(
                [
                    'current_password' => 'required',
                    'new_password' => 'required|min:6',
                    'confirm_password' => 'required|same:new_password',
                ]
            );
            $objUser          = \Auth::user();
            $request_data     = $request->All();
            $current_password = $objUser->password;
            if (Hash::check($request_data['current_password'], $current_password)) {
                $user_id            = \Auth::User()->id;
                $obj_user           = User::find($user_id);
                $obj_user->password = Hash::make($request_data['new_password']);;
                $obj_user->save();

                return redirect()->route('profile', $objUser->id)->with('success', __('Password successfully updated.'));
            } else {
                return redirect()->route('profile', $objUser->id)->with('error', __('Please enter correct current password.'));
            }
        } else {
            return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
        }
    }

    public function upgradePlan($user_id)
    {
        $user = User::find($user_id);

        $plans = Plan::get();

        return view('user.plan', compact('user', 'plans'));
    }

    public function activePlan($user_id, $plan_id)
    {
        $user               = User::find($user_id);
        $plan               = Plan::find($plan_id);

        if($plan->is_active == 1){
            $assignPlan         = $user->assignPlan($plan_id);
            if ($assignPlan['is_success'] == true && !empty($plan)) {
                $orderID        = strtoupper(str_replace('.', '', uniqid('', true)));
                Order::create(
                    [
                        'order_id'          =>   $orderID,
                        'name'              =>   null,
                        'card_number'       =>   null,
                        'card_exp_month'    =>   null,
                        'card_exp_year'     =>   null,
                        'plan_name'         =>   $plan->name,
                        'plan_id'           =>   $plan->id,
                        'price'             =>   $plan->price,
                        'price_currency'    =>   Utility::getAdminCurrency(),
                        'txn_id'            =>   '',
                        'payment_status'    =>   'succeeded',
                        'receipt'           =>   null,
                        'payment_type'      =>   __('Manually'),
                        'user_id'           =>   $user->id,
                    ]
                );

                return redirect()->back()->with('success', 'Plan successfully upgraded.');
            } else {
                return redirect()->back()->with('error', 'Plan fail to upgrade.');
            }
        } else {
            return redirect()->back()->with('error', 'You are unable to upgrade this plan because it is disabled.');
        }
    }

    public function clientCompanyInfoEdit(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'company_name'  => 'required',
                'website'       => 'required',
                'tax_number'    => 'required',
            ]
        );
        $client               = Client::where('user_id', $id)->first();
        $client->company_name = $request->company_name;
        $client->website      = $request->website;
        $client->tax_number   = $request->tax_number;
        $client->notes        = $request->notes;
        $client->save();

        return redirect()->back()->with('success', 'Company info successfully updated.');
    }

    public function clientPersonalInfoEdit(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'mobile'           => 'required',
                'address_1'        => 'required',
                'city'             => 'required',
                'state'            => 'required',
                'country'          => 'required',
                'zip_code'         => 'required',
                // 'profile'          => 'mimes:jpeg,png,jpg|max:20480',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $client                 = Client::where('user_id', $id)->first();
        $client->mobile         = $request->mobile;
        $client->address_1      = $request->address_1;
        $client->address_2      = $request->address_2;
        $client->city           = $request->city;
        $client->state          = $request->state;
        $client->country        = $request->country;
        $client->zip_code       = $request->zip_code;
        $client->save();

        $user = User::find($id);

        if ($request->hasFile('profile')) {
            //storage limit
            $image_size = $request->file('profile')->getSize();
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
            if ($result == 1) {

                $filenameWithExt        =    $request->file('profile')->getClientOriginalName();
                $filename               =    pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension              =    $request->file('profile')->getClientOriginalExtension();
                $fileNameToStore        =    $filename . '_' . time() . '.' . $extension;
                $settings               =    Utility::getStorageSetting();

                // $dir        = storage_path('uploads/avatar/');
                if ($settings['storage_setting'] == 'local') {
                    $dir        = 'uploads/avatar/';
                } else {
                    $dir        = 'uploads/avatar';
                }
                $image_path = $dir . $user->avatar;

                if (\File::exists($image_path)) {
                    \File::delete($image_path);
                }


                $url = '';
                $path = Utility::upload_file($request, 'profile', $fileNameToStore, $dir, []);
                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->route('profile', \Auth::user()->id)->with('error', __($path['msg']));
                }
            }
            // $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);

        }

        $user       = User::find($id);
        $user->name = $request->name;
        if (!empty($request->profile)) {
            //storage limit
            $file_path = 'uploads/files/' . $user->profile;
            //  $image_size = $request->file('profile')->getSize();
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
            if ($result == 1) {
                Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);

                $user->avatar = $fileNameToStore;
            }
        }
        $user->save();

        return redirect()->back()->with('success', 'Personal info successfully updated.');
    }


    // change mode 'dark or light'
    public function changeMode()
    {
        $usr = Auth::user();
        if ($usr->mode == 'light') {
            $usr->mode      = 'dark';
            $usr->dark_mode = 1;
        } else {
            $usr->mode      = 'light';
            $usr->dark_mode = 0;
        }
        $usr->save();

        return redirect()->back();
    }

    public function userPassword($id)
    {
        // dd($id);
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);

        $user = User::where('id', $eId)->first();
        // dd($user);
        return view('user.reset', compact('user', 'user'));
    }

    public function userPasswordReset(Request $request, $id)
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

        $user = User::where('id', $id)->first();
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return redirect()->route('user.index')->with(
            'success',
            'User Password successfully updated.'
        );
    }

    // start for user login details
    public function userLog(Request $request)
    {
        $filteruser = User::where('type', 'employee')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $filteruser->prepend('Select Employee', '');

        $query = \DB::table('login_details')->join('users', 'login_details.user_id', '=', 'users.id')->select(\DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))->where(['login_details.created_by' => \Auth::user()->id])->where(['users.type' => 'employee']);
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

        return view('user.userlog', compact('userdetails', 'last_login_details', 'filteruser'));
    }

    public function userLogView($id)
    {
        $users = LoginDetail::find($id);

        return view('user.userlogview', compact('users'));
    }

    public function userLogDestroy($id)
    {
        $users = LoginDetail::where('user_id', $id)->delete();
        return redirect()->back()->with('success', 'User successfully deleted.');
    }
    //end for user login details


    public function ExitCompany(Request $request)
    {
        \Auth::user()->leaveImpersonation($request->user());
        return redirect('/dashboard');
    }
    public function CompanyInfo($id)
    {
        $userData = User::where('type','employee')->where('created_by',$id)->selectRaw('COUNT(*) as total_users, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_users, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_users')->first();

        return view('user.company_info',compact('userData'  ,'id'));
    }

    public function UserUnable(Request $request)
    {
        User::where('id', $request->id)->update(['is_disable' => $request->is_disable]);
        $userData = User::where('created_by',$request->company_id)->selectRaw('COUNT(*) as total_users, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_users, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_users')->first();
        if($request->is_disable == 1){


            return response()->json(['success' => __('Successfully Unable.'),'userData' => $userData]);

        }else
        {
            return response()->json(['success' => __('Successfull Disable.'),'userData' => $userData]);
        }
    }

    public function LoginWithCompany(Request $request, User $user,  $id)
    {
        $user =    User::find($id);
        $from =     \Auth::user();
        if ($user && auth()->check()) {
            $manager = app('impersonate');
            $manager->take($from, $user);
            return redirect('dashboard');
        }
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
