<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginDetail;
use Carbon\Carbon;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Utility;
use App\Models\User;
use App\Models\Plan;
use App\Models\Plan as ModelsPlan;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Events\VerifyReCaptchaTokenEvent;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function __construct()
    {
        if (!file_exists(storage_path() . "/installed")) {
            header('location:install');
            die;
        }
    }

    /*protected function authenticated(Request $request, $user)
    {
        if($user->delete_status == 1)
        {
            auth()->logout();
        }

        return redirect('/check');
    }*/

    public function store(LoginRequest $request)
    {
        $user = User::where('email',$request->email)->first();

        if($user != null)
        {
            $companyUser = User::where('id' , $user->created_by)->first();
        }

        if(($user != null && $user->is_enable_login == 0 || (isset($companyUser) && $companyUser != null) && $companyUser->is_enable_login == 0)  && $user->type != 'super admin')
        {
            return redirect()->back()->with('error', __('Your Account is disable from company.'));
        }



        //ReCpatcha
        $settings = Utility::settings();
        $validation = [];
        if(isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'yes')
        {
            if($settings['google_recaptcha_version'] == 'v2-checkbox'){
                $validation['g-recaptcha-response'] = 'required';
            }
            elseif($settings['google_recaptcha_version'] == 'v3')
            {

                // dd($request);
                $result = event(new VerifyReCaptchaTokenEvent($request));
                if (!isset($result[0]['status']) || $result[0]['status'] != true) {
                    $key = 'g-recaptcha-response';
                    $request->merge([$key => null]); // Set the key to null

                    $validation['g-recaptcha-response'] = 'required';
                }
            }else{
                $validation = [];
            }
        }else{
            $validation = [];
        }
        $this->validate($request, $validation);


        // if (Utility::getValByName('recaptcha_module') == 'yes') {
        //     $validation['g-recaptcha-response'] = 'required|captcha';
        // } else {
        //     $validation = [];
        // }

        // $this->validate($request, $validation);

        $request->authenticate();
        $request->session()->regenerate();
        $user = Auth::user();
        if ($user->is_active == 0) {
            auth()->logout();
        }

        if ($user->is_active == 1) {
            $user->active_status = 1;
            // $user->save();
        }

        if($user->is_disable == 0 && \Auth::user()->type != 'super admin'){
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your Account is disable, please contact your Administrator.');
        }

        if ($user->type == 'company') {
            $free_plan = Plan::where('price', '=', '0.0')->first();
            $plan      = Plan::find($user->plan);

            if ($user->plan != $free_plan->id) {
                if (date('Y-m-d') > $user->plan_expire_date && ucfirst($plan->duration) != 'Lifetime') {
                    $user->plan             = $free_plan->id;
                    $user->plan_expire_date = null;
                    $user->save();

                    $clients   = User::where('type', 'client')->where('created_by', '=', \Auth::user()->creatorId())->get();
                    $employees = User::where('type', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();

                    if ($free_plan->max_client == -1) {
                        foreach ($clients as $client) {
                            $client->is_active = 1;
                            $client->save();
                        }
                    } else {
                        $clientCount = 0;
                        foreach ($clients as $client) {
                            $clientCount++;
                            if ($clientCount <= $free_plan->max_client) {
                                $client->is_active = 1;
                                $client->save();
                            } else {
                                $client->is_active = 0;
                                $client->save();
                            }
                        }
                    }


                    if ($free_plan->max_employee == -1) {
                        foreach ($employees as $employee) {
                            $employee->is_active = 1;
                            $employee->save();
                        }
                    } else {
                        $employeeCount = 0;
                        foreach ($employees as $employee) {
                            $employeeCount++;
                            if ($employeeCount <= $free_plan->max_employee) {
                                $employee->is_active = 1;
                                $employee->save();
                            } else {
                                $employee->is_active = 0;
                                $employee->save();
                            }
                        }
                    }
                    if ($user->trial_expire_date != null) {
                        if (\Auth::user()->trial_expire_date > date('Y-m-d')) {
                            $user->assignPlan(1);

                            return redirect()->intended(RouteServiceProvider::HOME)->with('error', __('Your Trial plan Expired.'));
                        }
                    }
                    return redirect()->route('dashboard')->with('error', 'Your plan expired limit is over, please upgrade your plan');
                }
            }
        }

        // Update Last Login Time
        $user->update(
            [
                'last_login_at' => Carbon::now()->toDateTimeString(),
            ]
        );

        //start for user log
        if ($user->type != 'company' && $user->type != 'super admin') {


            $ip = '49.36.83.154'; // This is static ip address
            // $ip = $_SERVER['REMOTE_ADDR']; // your ip address here


            $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));
            $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
            if ($whichbrowser->device->type == 'bot') {
                return;
            }
            $referrer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;
            /* Detect extra details about the user */
            $query['browser_name'] = $whichbrowser->browser->name ?? null;
            $query['os_name'] = $whichbrowser->os->name ?? null;
            $query['browser_language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $query['device_type'] = get_device_type($_SERVER['HTTP_USER_AGENT']);
            $query['referrer_host'] = !empty($referrer['host']);
            $query['referrer_path'] = !empty($referrer['path']);


            isset($query['timezone']) ? date_default_timezone_set($query['timezone']) : '';


            $json = json_encode($query);

            $login_detail = new LoginDetail();
            $login_detail->user_id = Auth::user()->id;
            $login_detail->ip = $ip;
            $login_detail->date = date('Y-m-d H:i:s');
            $login_detail->Details = $json;
            $login_detail->created_by = \Auth::user()->creatorId();
            $login_detail->save();
        }
        //end for user log

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function showLoginForm($lang = '')
    {
        if (empty($lang)) {
            $lang = Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.login', compact('lang'));
    }



    public function showLinkRequestForm($lang = '')
    {
        if (empty($lang)) {
            $lang = Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.passwords.email', compact('lang'));
        /*return view('auth.passwords.email', compact('lang'));*/
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $usr = \Auth::user();
        $usr->active_status = 0;
        $usr->save();
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
//for user log
if (!function_exists('get_device_type')) {
    function get_device_type($user_agent)
    {
        $mobile_regex = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tablet_regex = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';
        if (preg_match_all($mobile_regex, $user_agent)) {
            return 'mobile';
        } else {
            if (preg_match_all($tablet_regex, $user_agent)) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }
    }
}
