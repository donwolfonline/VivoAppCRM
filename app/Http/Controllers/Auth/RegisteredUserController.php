<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use App\Events\VerifyReCaptchaTokenEvent;
class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function create()
    {
        if (Utility::getValByName('SIGNUP') == 'on') {
            return view('auth.register');
        } else {
            return abort('404', 'Page Not Found');
        }
    }


    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function generateReferralCode()
{
    $referralCode = random_int(100000, 999999); // Generate a random integer between 100000 and 999999 (inclusive)

    // Check if the generated referral code already exists in the database
    while (User::where('referral_code', $referralCode)->exists()) {
        $referralCode = random_int(100000, 999999); // Generate new referral code until unique
    }

    return $referralCode;
}

    public function store(Request $request)
    {

        $referralCode = $this->generateReferralCode();

        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' =>  'required',
            'terms' => 'required',
        ]);

        // if (Utility::getValByName('recaptcha_module') == 'yes') {
        //     $validation['g-recaptcha-response'] = 'required|captcha';
        // } else {
        //     $validation = [];
        // }
        // $this->validate($request, $validation);
        //ReCpatcha
        $settings = Utility::settings();
        $validation = [];

        if(isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'yes')
        {
            if($settings['google_recaptcha_version'] == 'v2-checkbox'){
                $validation['g-recaptcha-response'] = 'required';
            }
            elseif($settings['google_recaptcha_version'] == 'v3'){
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

        if (Utility::getValByName('email_verificattion') == 'off') {


            $uArr = [
                'email'     => $request->email,
                'password'  => $request->password,
            ];

            $default_language = Utility::getValByName('default_language');
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => 'company',
                'lang' => !empty($default_language) ? $default_language : 'en',
                'plan' => Plan::first()->id,
                'created_by' => 1,
                'referral_code'=>$referralCode,
                'used_referral_code'=>$request->used_referral_code,
            ]);
            $user->email_verified_at = date("H:i:s");
            $user->save();
            // Auth::login($user);
            $user->userDefaultData();

            Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr, $user);
            // return redirect()->route('login')->with('success', __('User successfully created.'));

            // event(new Registered($user));
            //return redirect()->route('dashboard')->with('error', 'Your plan expired limit is over, please upgrade your plan');
            //  return $user;

            // return redirect(RouteServiceProvider::HOME);
            Auth::login($user);
            return redirect(RouteServiceProvider::HOME);
        } else {

            $default_language = Utility::getValByName('default_language');
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => 'company',
                'lang' => !empty($default_language) ? $default_language : 'en',
                'plan' => Plan::first()->id,
                'created_by' => 1,
                'referral_code'=>$referralCode,
                'used_referral_code'=>$request->referral_code,
            ]);

            // $user->userDefaultData();
            // Auth::login($user);
            // event(new Registered($user));

            Utility::getSMTPDetails(1);

            if (empty($lang)) {
                $lang = Utility::getValByName('default_language');
            }
            \App::setLocale($lang);
            Auth::login($user);
            try {
                event(new Registered($user));
                $user->userDefaultData();
            } catch (\Exception $e) {
                $user->delete();
                return redirect('/register/lang?')->with('status', __('Email SMTP settings does not configure so please contact to your site admin.'));
            }

            return redirect(RouteServiceProvider::HOME);

        }
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => ['required', 'confirmed', Rules\Password::defaults()],
    //     ]);

    //     if (Utility::getValByName('email_verificattion') == 'off') {

    //         $date = date("Y-m-d H:i:s");
    //     } else {
    //         $date = null;
    //     }


    //     if (Utility::getValByName('recaptcha_module') == 'yes') {
    //         $validation['g-recaptcha-response'] = 'required';
    //     } else {
    //         $validation = [];
    //     }
    //     $this->validate($request, $validation);
    //     $slug = str_replace(' ', '-', strtolower($request->name));

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'email_verified_at' => $date,
    //         'password' => Hash::make($request->password),
    //         'slug' => $slug,
    //         'type' => 'Admin',
    //         'lang' => Utility::getValByName('default_language'),
    //         'parent' => 1,
    //     ]);
    //     Utility::getSMTPDetails(1);
    //     $adminRole = Role::findByName('Admin');
    //     $user->assignRole($adminRole);
    //     $user->userDefaultData();
    //     $user->assignPlan(1);
    //     $userDefaultData = Utility::addCustomeField($user->id);
    //     $userDefaultData = Utility::userDefaultData();
    //     $user->$userDefaultData;
    //     $user->userDefaultDataRegister($user->id);



    //     if (Utility::getValByName('email_verificattion') == 'on') {
    //         try {
    //             event(new Registered($user));

    //             Auth::login($user);


    //         } catch (\Exception $e) {
    //             $user->delete();
    //             return redirect('/register/lang?')->with('status', __('Email SMTP settings does not configure so please contact to your site admin.'));
    //         }

    //     } else {
    //         $uArr = [
    //             'email' => $user->email,
    //             'password' => $request->password,
    //         ];

    //         Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr, $user->id);
    //         Auth::login($user);
    //         return redirect(RouteServiceProvider::HOME);
    //     }

    //     if (empty($lang)) {
    //         $lang = Utility::getValByName('default_language');
    //     }

    //     \App::setLocale($lang);

    //     return view('auth.verify', compact('lang'));

    //     // return view('auth.verify-email', compact('lang'));

    // }
    // Register Form

    public function showemailform($lang = '')
    {
        if (empty($lang)) {
            $lang = Utility::getValByName('default_language');
        }
        // dd($lang);
        \App::setLocale($lang);
        return view('auth.register', compact('lang'));
    }
}
