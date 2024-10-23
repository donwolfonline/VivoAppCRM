<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use App\Models\Webhook_settings;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use Termwind\Components\Dd;

class SettingController extends Controller
{
    public function index()
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'super admin') {
            $settings                = Utility::settings();

            $timezones               = config('timezones');
            $admin_payment_setting   = Utility::getAdminPaymentSetting();
            $company_payment_setting = Utility::getCompanyPaymentSetting();
            $data = Webhook_settings::where('created_by', \Auth::user()->id)->get();
            return view('settings.index', compact('settings', 'timezones', 'admin_payment_setting', 'company_payment_setting', 'data'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function saveBusinessSettings(Request $request)
    {
        $user = \Auth::user();
        if (\Auth::user()->type == 'super admin') {

            if ($request->logo) {
                $lightlogoName = 'logo-dark.png';


                $dir = 'uploads/logo/';
                $validation = [
                    'mimes:' . 'png',
                    'max:' . '20480',
                ];

                $path = Utility::upload_file($request, 'logo', $lightlogoName, $dir, $validation);

                if ($path['flag'] == 1) {
                    $logo = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }
            }

            if ($request->white_logo) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'white_logo' => 'image|mimes:png|max:20480',
                    ]
                );

                // $request->white_logo->storeAs('uploads/logo', 'logo-light.png');
                $lightlogoName = 'logo-light.png';

                $dir = 'uploads/logo/';

                $validation = [
                    'mimes:' . 'png',
                    'max:' . '20480',
                ];
                $path = Utility::upload_file($request, 'white_logo', $lightlogoName, $dir, $validation);
                if ($path['flag'] == 1) {
                    $white_logo = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }
            }

            if ($request->favicon) {

                $favicon = 'favicon.png';
                // $path    = $request->file('favicon')->storeAs('uploads/logo/', $favicon);
                $dir = 'uploads/logo/';
                $validation = [
                    'mimes:' . 'png',
                    'max:' . '20480',
                ];
                $path = Utility::upload_file($request, 'favicon', $favicon, $dir, $validation);
                if ($path['flag'] == 1) {

                    $favicon = $favicon;
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $favicon,
                        'favicon',
                        \Auth::user()->creatorId(),
                    ]
                );
            }


            $request->user = \Auth::user()->id;
            if (!empty($request->title_text) || !empty($request->footer_text) || !empty($request->default_language) || !empty($request->display_landing_page) || !empty($request->gdpr_cookie) || !empty($request->color) || !empty($request->cust_theme_bg) || !empty($request->cust_darklayout)) {
                $post = $request->all();

                if (!isset($request->gdpr_cookie)) {
                    $post['gdpr_cookie'] = 'off';
                }
                if (!isset($request->display_landing_page)) {
                    $post['display_landing_page'] = 'off';
                }
                if (!isset($request->SIGNUP)) {
                    $post['SIGNUP'] = 'off';
                }
                if (!isset($request->email_verificattion)) {
                    $post['email_verificattion'] = 'off';
                }

                if (!isset($request->cust_theme_bg)) {
                    $post['cust_theme_bg'] = 'off';
                }

                if (!isset($request->cust_darklayout)) {
                    $post['cust_darklayout'] = 'off';
                }

                if(isset($request->color) && $request->color_flag == 'false')
                {
                    $post['color'] = $request->color;
                }
                else
                {
                    $post['color'] = $request->custom_color;
                }

                $SITE_RTL = $request->has('SITE_RTL') ? $request->SITE_RTL : 'off';
                $post['SITE_RTL'] = $SITE_RTL;

                unset($post['_token'], $post['logo'], $post['small_logo'], $post['favicon']);


                $settings = Utility::settings();


                foreach ($post as $key => $data) {
                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $data,
                            $key,
                            \Auth::user()->creatorId(),
                        ]
                    );
                }
            }
        } else if (\Auth::user()->type == 'company') {;
            if ($request->company_logo_dark) {
                $logoName     = $user->id . '-logo-dark.png';
                $dir = 'uploads/logo/';
                $validation = [
                    'mimes:' . 'png',
                    'max:' . '20480',
                ];

                $path = Utility::upload_file($request, 'company_logo_dark', $logoName, $dir, $validation);

                if ($path['flag'] == 1) {
                    $company_logo_dark = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $logoName,
                        'company_logo_dark',
                        \Auth::user()->creatorId(),
                    ]
                );
            }
            if ($request->company_logo_light) {


                $logoName     = $user->id . '-logo-light.png';

                $dir = 'uploads/logo/';

                $validation = [
                    'mimes:' . 'png',
                    'max:' . '20480',
                ];
                $path = Utility::upload_file($request, 'company_logo_light', $logoName, $dir, $validation);
                if ($path['flag'] == 1) {
                    $company_logo_light = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                // $path         = $request->file('company_logo_light')->storeAs('uploads/logo/', $logoName);
                $company_logo = !empty($request->company_logo_light) ? $logoName : 'logo-light.png';

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $logoName,
                        'company_logo_light',
                        \Auth::user()->creatorId(),
                    ]
                );
            }


            if ($request->company_favicon) {
                // $validator = \Validator::make(
                //     $request->all(), [
                //         'company_favicon' => 'image|mimes:png|max:20480',
                //     ]
                // );

                $favicon = $user->id . '_favicon.png';


                $dir = 'uploads/logo/';

                $validation = [
                    'mimes:' . 'png',
                    'max:' . '20480',
                ];
                $path = Utility::upload_file($request, 'company_favicon', $favicon, $dir, $validation);
                if ($path['flag'] == 1) {
                    $company_favicon = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }


                // $path    = $request->file('company_favicon')->storeAs('uploads/logo/', $favicon);

                $company_favicon = !empty($request->favicon) ? $favicon : 'favicon.png';

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $favicon,
                        'company_favicon',
                        \Auth::user()->creatorId(),
                    ]
                );
            }




            // $request->user = \Auth::user()->id;
            if (!empty($request->title_text) || !empty($request->cust_theme_bg) || !empty($request->cust_darklayout) || !empty($request->color)) {
                $post = $request->all();
                // dd($post);
                $SITE_RTL = $request->has('SITE_RTL') ? $request->SITE_RTL : 'off';
                $post['SITE_RTL'] = $SITE_RTL;


                if (!isset($request->cust_theme_bg)) {
                    $post['cust_theme_bg'] = 'off';
                }

                if (!isset($request->cust_darklayout)) {
                    $post['cust_darklayout'] = 'off';
                }

                if(isset($request->color) && $request->color_flag == 'false')
                {
                    $post['color'] = $request->color;
                }
                else
                {
                    $post['color'] = $request->custom_color;
                }


                unset($post['_token'], $post['company_logo_light'], $post['company_logo_dark'], $post['company_favicon']);
                // dd($post);

                foreach ($post as $key => $data) {
                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $data,
                            $key,
                            \Auth::user()->creatorId(),
                        ]
                    );
                }
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        return redirect()->back()->with('success', 'Brand setting successfully saved.');
    }

    public function saveCompanySettings(Request $request)
    {


        if (\Auth::user()->type == 'company') {

            $request->validate(
                [
                    'company_name' => 'required|string',
                    'company_email' => 'required',
                    'company_email_from_name' => 'required|string',
                    'company_address' => 'required',
                    'company_city' => 'required',
                    'company_state' => 'required',
                    'company_zipcode' => 'required',
                    'company_country' => 'required',
                    'company_telephone' => 'required',
                    'timezone' => 'required',
                    'registration_number' => 'required|string',
                ]
            );
            $post = $request->all();
            unset($post['_token']);

            $settings = Utility::settings();

            foreach ($post as $key => $data) {
                if (in_array($key, array_keys($settings)) && $data !== null) {
                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $data,
                            $key,
                            \Auth::user()->creatorId(),
                        ]
                    );
                }
            }



            // $arrEnv = [
            //     'TIMEZONE' => $request->timezone,
            // ];

            // $request->user = \Auth::user()->id;
            // Artisan::call('config:cache');
            // Artisan::call('config:clear');

            // Utility::setEnvironmentValue($arrEnv);

            return redirect()->back()->with('success', __('Setting successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function saveEmailSettings(Request $request)
    {
        $request->validate(
            [
                'mail_driver'           => 'required|string|max:50',
                'mail_host'             => 'required|string|max:50',
                'mail_port'             => 'required|string|max:50',
                'mail_username'         => 'required|string|max:50',
                'mail_password'         => 'required|string|max:50',
                'mail_encryption'       => 'required|string|max:50',
                'mail_from_address'     => 'required|string|max:50',
                'mail_from_name'        => 'required|string|max:50',
            ]
        );
        $post = $request->all();
        unset($post['_token']);
        $settings = Utility::settings();

        foreach ($post as $key => $data) {
            if (in_array($key, array_keys($settings)) && $data !== null) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->creatorId(),
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Setting successfully updated.'));
    }


    public function saveSystemSettings(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $request->validate(
                [
                    'site_currency' => 'required',
                ]
            );
            $post = $request->all();
            unset($post['_token']);

            $settings = Utility::settings();


            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`)
                    values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->creatorId(),
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }

            return redirect()->back()->with('success', __('Setting successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function timeTracker(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $request->validate(
                [
                    'interval_time' => 'required',
                ]
            );
            $post = $request->all();
            unset($post['_token']);

            $settings = Utility::settings();

            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`)
                    values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->creatorId(),
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }

            return redirect()->back()->with('success', __('Time Tracker successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function savePusherSettings(Request $request)
    {
        if (\Auth::user()->type == 'super admin') {
            $request->validate(
                [
                    'pusher_app_id' => 'required',
                    'pusher_app_key' => 'required',
                    'pusher_app_secret' => 'required',
                    'pusher_app_cluster' => 'required',
                ]
            );

            try{

                $post = $request->all();
                unset($post['_token']);
                $settings = Utility::settings();

                foreach ($post as $key => $data) {
                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`)
                        values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $data,
                            $key,
                            \Auth::user()->creatorId(),
                            date('Y-m-d H:i:s'),
                            date('Y-m-d H:i:s'),
                        ]
                    );
                }

                return redirect()->back()->with('success', __('Pusher successfully updated.'));
            }
            catch(\Exception $e)
            {
                return redirect()->back()->with('error', 'Something went wrong.');
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function savePaymentSettings(Request $request)
    {

        if (\Auth::user()->type == 'super admin') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'currency' => 'required|string|max:255',
                    'currency_symbol' => 'required|string|max:255',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // $arrEnv = [
            //     'CURRENCY_SYMBOL' => $request->currency_symbol,
            //     'CURRENCY' => $request->currency,
            // ];


            // Utility::setEnvironmentValue($arrEnv);

            self::adminPaymentSettings($request);

            // Artisan::call('config:cache');
            // Artisan::call('config:clear');

            return redirect()->back()->with('success', __('Payment setting successfully saved.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function saveCompanyPaymentSettings(Request $request)
    {


        //Bank Transfer
        if (isset($request->is_bank_transfer_enabled) && $request->is_bank_transfer_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'bank_details' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // dd($request->is_bank_transfer_enabled);
            $post['is_bank_transfer_enabled'] = $request->is_bank_transfer_enabled;
            $post['bank_details'] = $request->bank_details;
        } else {
            $post['is_bank_transfer_enabled'] = 'off';
        }

        if (isset($request->is_stripe_enabled) && $request->is_stripe_enabled == 'on') {

            $request->validate(
                [
                    'stripe_key' => 'required|string|max:255',
                    'stripe_secret' => 'required|string|max:255',
                ]
            );

            $post['is_stripe_enabled'] = $request->is_stripe_enabled;
            $post['stripe_secret']     = $request->stripe_secret;
            $post['stripe_key']        = $request->stripe_key;
        } else {
            $post['is_stripe_enabled'] = 'off';
        }

        if (isset($request->is_paypal_enabled) && $request->is_paypal_enabled == 'on') {
            $request->validate(
                [
                    'paypal_mode' => 'required',
                    'paypal_client_id' => 'required',
                    'paypal_secret_key' => 'required',
                ]
            );

            $post['is_paypal_enabled'] = $request->is_paypal_enabled;
            $post['paypal_mode']       = $request->paypal_mode;
            $post['paypal_client_id']  = $request->paypal_client_id;
            $post['paypal_secret_key'] = $request->paypal_secret_key;
        } else {
            $post['is_paypal_enabled'] = 'off';
        }

        if (isset($request->is_paystack_enabled) && $request->is_paystack_enabled == 'on') {
            $request->validate(
                [
                    'paystack_public_key' => 'required|string',
                    'paystack_secret_key' => 'required|string',
                ]
            );
            $post['is_paystack_enabled'] = $request->is_paystack_enabled;
            $post['paystack_public_key'] = $request->paystack_public_key;
            $post['paystack_secret_key'] = $request->paystack_secret_key;
        } else {
            $post['is_paystack_enabled'] = 'off';
        }

        if (isset($request->is_flutterwave_enabled) && $request->is_flutterwave_enabled == 'on') {
            $request->validate(
                [
                    'flutterwave_public_key' => 'required|string',
                    'flutterwave_secret_key' => 'required|string',
                ]
            );
            $post['is_flutterwave_enabled'] = $request->is_flutterwave_enabled;
            $post['flutterwave_public_key'] = $request->flutterwave_public_key;
            $post['flutterwave_secret_key'] = $request->flutterwave_secret_key;
        } else {
            $post['is_flutterwave_enabled'] = 'off';
        }

        if (isset($request->is_razorpay_enabled) && $request->is_razorpay_enabled == 'on') {
            $request->validate(
                [
                    'razorpay_public_key' => 'required|string',
                    'razorpay_secret_key' => 'required|string',
                ]
            );
            $post['is_razorpay_enabled'] = $request->is_razorpay_enabled;
            $post['razorpay_public_key'] = $request->razorpay_public_key;
            $post['razorpay_secret_key'] = $request->razorpay_secret_key;
        } else {
            $post['is_razorpay_enabled'] = 'off';
        }

        if (isset($request->is_mercado_enabled) && $request->is_mercado_enabled == 'on') {
            $request->validate(
                [
                    'mercado_access_token' => 'required|string',
                ]
            );
            $post['is_mercado_enabled'] = $request->is_mercado_enabled;
            $post['mercado_access_token']     = $request->mercado_access_token;
            $post['mercado_mode'] = $request->mercado_mode;
        } else {
            $post['is_mercado_enabled'] = 'off';
        }

        if (isset($request->is_paytm_enabled) && $request->is_paytm_enabled == 'on') {
            $request->validate(
                [
                    'paytm_mode' => 'required',
                    'paytm_merchant_id' => 'required|string',
                    'paytm_merchant_key' => 'required|string',
                    'paytm_industry_type' => 'required|string',
                ]
            );
            $post['is_paytm_enabled']    = $request->is_paytm_enabled;
            $post['paytm_mode']          = $request->paytm_mode;
            $post['paytm_merchant_id']   = $request->paytm_merchant_id;
            $post['paytm_merchant_key']  = $request->paytm_merchant_key;
            $post['paytm_industry_type'] = $request->paytm_industry_type;
        } else {
            $post['is_paytm_enabled'] = 'off';
        }
        if (isset($request->is_mollie_enabled) && $request->is_mollie_enabled == 'on') {
            $request->validate(
                [
                    'mollie_api_key' => 'required|string',
                    'mollie_profile_id' => 'required|string',
                    'mollie_partner_id' => 'required',
                ]
            );
            $post['is_mollie_enabled'] = $request->is_mollie_enabled;
            $post['mollie_api_key']    = $request->mollie_api_key;
            $post['mollie_profile_id'] = $request->mollie_profile_id;
            $post['mollie_partner_id'] = $request->mollie_partner_id;
        } else {
            $post['is_mollie_enabled'] = 'off';
        }

        if (isset($request->is_skrill_enabled) && $request->is_skrill_enabled == 'on') {
            $request->validate(
                [
                    'skrill_email' => 'required|email',
                ]
            );
            $post['is_skrill_enabled'] = $request->is_skrill_enabled;
            $post['skrill_email']      = $request->skrill_email;
        } else {
            $post['is_skrill_enabled'] = 'off';
        }

        if (isset($request->is_coingate_enabled) && $request->is_coingate_enabled == 'on') {
            $request->validate(
                [
                    'coingate_mode' => 'required|string',
                    'coingate_auth_token' => 'required|string',
                ]
            );

            $post['is_coingate_enabled'] = $request->is_coingate_enabled;
            $post['coingate_mode']       = $request->coingate_mode;
            $post['coingate_auth_token'] = $request->coingate_auth_token;
        } else {
            $post['is_coingate_enabled'] = 'off';
        }

        if (isset($request->is_paymentwall_enabled) && $request->is_paymentwall_enabled == 'on') {
            $request->validate(
                [
                    'paymentwall_public_key' => 'required|string',
                    'paymentwall_private_key' => 'required|string',
                ]
            );
            $post['is_paymentwall_enabled'] = $request->is_paymentwall_enabled;

            $post['paymentwall_public_key'] = $request->paymentwall_public_key;
            $post['paymentwall_private_key'] = $request->paymentwall_private_key;
        } else {
            $post['is_paymentwall_enabled'] = 'off';
        }

        //toyyibpay
        if (isset($request->is_toyyibpay_enabled) && $request->is_toyyibpay_enabled == 'on') {
            $request->validate(
                [
                    'toyyibpay_secret_key' => 'required|string|max:255',
                    'category_code' => 'required|string|max:255',
                ]
            );

            $post['is_toyyibpay_enabled'] = $request->is_toyyibpay_enabled;
            $post['category_code']     = $request->category_code;
            $post['toyyibpay_secret_key']        = $request->toyyibpay_secret_key;
        } else {
            $post['is_toyyibpay_enabled'] = 'off';
        }

        //payfast
        if (isset($request->is_payfast_enabled) && $request->is_payfast_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'payfast_mode' => 'required',
                    'payfast_merchant_id' => 'required|string',
                    'payfast_merchant_key' => 'required|string',
                    'payfast_signature' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_payfast_enabled'] = $request->is_payfast_enabled;
            $post['payfast_mode'] = $request->payfast_mode;
            $post['payfast_merchant_id'] = $request->payfast_merchant_id;
            $post['payfast_merchant_key'] = $request->payfast_merchant_key;
            $post['payfast_signature'] = $request->payfast_signature;
        } else {
            $post['is_payfast_enabled'] = 'off';
        }

        // iyzipay
        if (isset($request->is_iyzipay_enabled) && $request->is_iyzipay_enabled == 'on') {
            $request->validate(
                [
                    'iyzipay_mode' => 'required',
                    'iyzipay_public_key' => 'required',
                    'iyzipay_secret_key' => 'required',
                ]
            );

            $post['is_iyzipay_enabled'] = $request->is_iyzipay_enabled;
            $post['iyzipay_mode']       = $request->iyzipay_mode;
            $post['iyzipay_public_key']  = $request->iyzipay_public_key;
            $post['iyzipay_secret_key'] = $request->iyzipay_secret_key;
        } else {
            $post['is_iyzipay_enabled'] = 'off';
        }

        //sspay
        if (isset($request->is_sspay_enabled) && $request->is_sspay_enabled == 'on') {
            $request->validate(
                [
                    'sspay_secret_key' => 'required|string|max:255',
                    'sspay_category_code' => 'required|string|max:255',
                ]
            );

            $post['is_sspay_enabled'] = $request->is_sspay_enabled;
            $post['sspay_category_code']     = $request->sspay_category_code;
            $post['sspay_secret_key']        = $request->sspay_secret_key;
        } else {
            $post['is_sspay_enabled'] = 'off';
        }

        //paytab
        if (isset($request->is_paytab_enabled) && $request->is_paytab_enabled == 'on') {
            $validator = Validator::make(
                $request->all(),
                [
                    'is_paytab_enabled' => 'required',
                    'paytab_profile_id' => 'required|string',
                    'paytab_server_key' => 'required|string',
                    'paytab_region' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $post['is_paytab_enabled'] = $request->is_paytab_enabled;
            $post['paytab_profile_id'] = $request->paytab_profile_id;
            $post['paytab_server_key'] = $request->paytab_server_key;
            $post['paytab_region'] = $request->paytab_region;
        } else {
            $post['is_paytab_enabled'] = 'off';
        }

        //banefit
        if (isset($request->is_benefit_enabled) && $request->is_benefit_enabled == 'on') {

            $validator = Validator::make(
                $request->all(),
                [
                    'is_benefit_enabled' => 'required',
                    'benefit_api_key' => 'required|string',
                    'benefit_secret_key' => 'required|string',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_benefit_enabled'] = $request->is_benefit_enabled;
            $post['benefit_api_key'] = $request->benefit_api_key;
            $post['benefit_secret_key'] = $request->benefit_secret_key;
        } else {
            $post['is_benefit_enabled'] = 'off';
        }

        //cashfree
        if (isset($request->is_cashfree_enabled) && $request->is_cashfree_enabled == 'on') {
            $validator = Validator::make(
                $request->all(),
                [
                    'is_cashfree_enabled' => 'required',
                    'cashfree_api_key' => 'required|string',
                    'cashfree_secret_key' => 'required|string',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_cashfree_enabled'] = $request->is_cashfree_enabled;
            $post['cashfree_api_key'] = $request->cashfree_api_key;
            $post['cashfree_secret_key'] = $request->cashfree_secret_key;
        } else {
            $post['is_cashfree_enabled'] = 'off';
        }

        //aamarpay
        if (isset($request->is_aamarpay_enabled) && $request->is_aamarpay_enabled == 'on') {
            $validator = Validator::make(
                $request->all(),
                [
                    'is_aamarpay_enabled' => 'required',
                    'aamarpay_store_id' => 'required|string',
                    'aamarpay_signature_key' => 'required|string',
                    'aamarpay_description' => 'required|string',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_aamarpay_enabled'] = $request->is_aamarpay_enabled;
            $post['aamarpay_store_id'] = $request->aamarpay_store_id;
            $post['aamarpay_signature_key'] = $request->aamarpay_signature_key;
            $post['aamarpay_description'] = $request->aamarpay_description;
        } else {
            $post['is_aamarpay_enabled'] = 'off';
        }

         //paytr
        if (isset($request->is_paytr_enabled) && $request->is_paytr_enabled == 'on') {
                        $validator = Validator::make(
                            $request->all(),
                            [
                                'is_paytr_enabled' => 'required',
                                'paytr_merchant_id' => 'required|string',
                                'paytr_merchant_key' => 'required|string',
                                'paytr_merchant_salt' => 'required|string',

                            ]
                        );

                        if ($validator->fails()) {
                            $messages = $validator->getMessageBag();

                            return redirect()->back()->with('error', $messages->first());
                        }

                        $post['is_paytr_enabled'] = $request->is_paytr_enabled;
                        $post['paytr_merchant_id'] = $request->paytr_merchant_id;
                        $post['paytr_merchant_key'] = $request->paytr_merchant_key;
                        $post['paytr_merchant_salt'] = $request->paytr_merchant_salt;
                    } else {
                        $post['is_paytr_enabled'] = 'off';
                    }

         //PaiementPro
            if (isset($request->is_PaiementPro_enabled) && $request->is_PaiementPro_enabled == 'on') {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'is_PaiementPro_enabled' => 'required',
                        'PaiementPro_merchant_key' => 'required|string'
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $post['is_PaiementPro_enabled'] = $request->is_PaiementPro_enabled;

                $post['PaiementPro_merchant_key'] = $request->PaiementPro_merchant_key;
            } else {
                $post['is_PaiementPro_enabled'] = 'off';
            }


                     //CinetPay
                     if (isset($request->is_CinetPay_enabled) && $request->is_CinetPay_enabled == 'on') {
                        $request->validate(
                            [
                                'is_CinetPay_enabled' => 'required',
                                'CinetPay_site_id'=>'required',
                                'CinetPay_api_key' => 'required|string',
                                'CinetPay_secret_key'=>'required|string'
                            ]
                        );



                        if ($validator->fails()) {
                            $messages = $validator->getMessageBag();

                            return redirect()->back()->with('error', $messages->first());
                        }

                        $post['is_CinetPay_enabled'] = $request->is_CinetPay_enabled;
                        $post['CinetPay_site_id'] = $request->CinetPay_site_id;
                        $post['CinetPay_secret_key'] = $request->CinetPay_secret_key;
                        $post['CinetPay_api_key'] = $request->CinetPay_api_key;
                    } else {
                        $post['is_CinetPay_enabled'] = 'off';
                    }



            if (isset($request->is_nepalste_enabled) && $request->is_nepalste_enabled == 'on') {
                $request->validate(
                    [
                        'nepalste_public_key' => 'required|string',
                        'nepalste_secret_key' => 'required|string',

                    ]
                );
                $post['is_nepalste_enabled'] = $request->is_nepalste_enabled;
                $post['nepalste_public_key'] = $request->nepalste_public_key;
                $post['nepalste_secret_key'] = $request->nepalste_secret_key;
                $post['nepalste_mode'] = $request->nepalste_mode;
            } else {
                $post['is_nepalste_enabled'] = 'off';
            }


   //fedapay


   if (isset($request->is_Fedapay_enabled) && $request->is_Fedapay_enabled == 'on') {
    $request->validate(
        [
            'Fedapay_public_key' => 'required|string',
            'Fedapay_secret_key' => 'required|string',
            'Fedapay_mode'=> 'required',
        ]

    );
    $post['is_Fedapay_enabled'] = $request->is_Fedapay_enabled;
    $post['Fedapay_public_key'] = $request->Fedapay_public_key;
    $post['Fedapay_secret_key'] = $request->Fedapay_secret_key;
    $post['Fedapay_mode'] = $request->Fedapay_mode;
} else {
    $post['is_Fedapay_enabled'] = 'off';
}


        // PayHere Payment

        if (isset($request->is_PayHere_enabled) && $request->is_PayHere_enabled == 'on') {
            $request->validate(
                [
                    'PayHere_public_id' => 'required',
                    'PayHere_secret_key' => 'required|string',
                    "PayHere_merchant_id"=>"required",
                    'PayHere_mode'=> 'required',
                    'PayHere_merchant_secret_key'=>"required",
                ]

            );
            $post['is_PayHere_enabled'] = $request->is_PayHere_enabled;
            $post['PayHere_public_id'] = $request->PayHere_public_id;
            $post['PayHere_secret_key'] = $request->PayHere_secret_key;
            $post['PayHere_merchant_id'] = $request->PayHere_merchant_id;
            $post['PayHere_merchant_secret_key'] = $request->PayHere_merchant_secret_key;
            $post['PayHere_mode'] = $request->PayHere_mode;
        } else {
            $post['is_PayHere_enabled'] = 'off';
        }



        if(isset($request->is_yookassa_enabled) && $request->is_yookassa_enabled == 'on'){
            $validator = Validator::make(
                $request->all(),
                [
                    'yookassa_shop_id'      => 'required|string',
                    'yookassa_secret_key'  => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_yookassa_enabled']    = $request->is_yookassa_enabled;
            $post['yookassa_shop_id']       = $request->yookassa_shop_id;
            $post['yookassa_secret_key']   = $request->yookassa_secret_key;
        } else {
            $post['is_yookassa_enabled'] = 'off';
        }

        if(isset($request->is_midtrans_enabled) && $request->is_midtrans_enabled == 'on'){
            $validator = Validator::make(
                $request->all(),
                [
                    'midtrans_secret' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $post['midtrans_mode']           = $request->midtrans_mode;
            $post['is_midtrans_enabled']    = $request->is_midtrans_enabled;
            $post['midtrans_secret']        = $request->midtrans_secret;
        } else {
            $post['is_midtrans_enabled'] = 'off';
        }

        if(isset($request->is_xendit_enabled) && $request->is_xendit_enabled == 'on'){
            $validator = Validator::make(
                $request->all(),
                [
                    'xendit_api_key'    => 'required|string',
                    'xendit_token'      => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_xendit_enabled']  = $request->is_xendit_enabled;
            $post['xendit_api_key']     = $request->xendit_api_key;
            $post['xendit_token']       = $request->xendit_token;
        } else {
            $post['is_xendit_enabled'] = 'off';
        }
// dd($post);

        foreach ($post as $key => $data) {

            $arr = [
                $data,
                $key,
                \Auth::user()->id,
            ];
            \DB::insert(
                'insert into company_payment_settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                $arr
            );
        }

        return redirect()->back()->with('success', __('Payment setting successfully updated.'));
    }

    public function testMail(Request $request)
    {
        $user = \Auth::user();
        $data                      = [];
        $data['mail_driver']       = $request->mail_driver;
        $data['mail_host']         = $request->mail_host;
        $data['mail_port']         = $request->mail_port;
        $data['mail_username']     = $request->mail_username;
        $data['mail_password']     = $request->mail_password;
        $data['mail_encryption']   = $request->mail_encryption;
        $data['mail_from_address'] = $request->mail_from_address;
        $data['mail_from_name']    = $request->mail_from_name;

        return view('settings.test_mail', compact('data'));
    }

    public function testSendMail(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'mail_driver' => 'required',
                'mail_host' => 'required',
                'mail_port' => 'required',
                'mail_username' => 'required',
                'mail_password' => 'required',
                'mail_from_address' => 'required',
                'mail_from_name' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            // return response()->json(
            //     [
            //         'is_success' => false,
            //         'message' => $messages->first()
            //     ]
            // );
            return redirect()->back()->with('error', $messages->first());
        }

        try {
            config(
                [
                    'mail.driver' => $request->mail_driver,
                    'mail.host' => $request->mail_host,
                    'mail.port' => $request->mail_port,
                    'mail.encryption' => $request->mail_encryption,
                    'mail.username' => $request->mail_username,
                    'mail.password' => $request->mail_password,
                    'mail.from.address' => $request->mail_from_address,
                    'mail.from.name' => $request->mail_from_name,
                ]
            );
            Mail::to($request->email)->send(new TestMail());
        } catch (\Exception $e) {
            return response()->json(
                [
                    'is_success' => false,
                    'message' => $e->getMessage(),
                ]
            );
        }

        return response()->json(
            [
                'is_success' => true,
                'message' => __('Email send Successfully'),
            ]
        );
    }
    public function adminPaymentSettings($request)
    {

        $post['currency_symbol']    = $request->currency_symbol;
        $post['currency']           = $request->currency;

        //stripe
        if (isset($request->is_stripe_enabled) && $request->is_stripe_enabled == 'on') {
            $request->validate(
                [
                    'stripe_key' => 'required|string|max:255',
                    'stripe_secret' => 'required|string|max:255',
                ]
            );
            $post['is_stripe_enabled'] = $request->is_stripe_enabled;
            $post['stripe_secret']     = $request->stripe_secret;
            $post['stripe_key']        = $request->stripe_key;
        } else {
            $post['is_stripe_enabled'] = 'off';
        }

        //paypal
        if (isset($request->is_paypal_enabled) && $request->is_paypal_enabled == 'on') {
            $request->validate(
                [
                    'paypal_mode' => 'required',
                    'paypal_client_id' => 'required',
                    'paypal_secret_key' => 'required',
                ]
            );
            $post['is_paypal_enabled'] = $request->is_paypal_enabled;
            $post['paypal_mode']       = $request->paypal_mode;
            $post['paypal_client_id']  = $request->paypal_client_id;
            $post['paypal_secret_key'] = $request->paypal_secret_key;
        } else {
            $post['is_paypal_enabled'] = 'off';
        }

        //paystack
        if (isset($request->is_paystack_enabled) && $request->is_paystack_enabled == 'on') {
            $request->validate(
                [
                    'paystack_public_key' => 'required|string',
                    'paystack_secret_key' => 'required|string',
                ]
            );
            $post['is_paystack_enabled'] = $request->is_paystack_enabled;
            $post['paystack_public_key'] = $request->paystack_public_key;
            $post['paystack_secret_key'] = $request->paystack_secret_key;
        } else {
            $post['is_paystack_enabled'] = 'off';
        }

        //flutterwave
        if (isset($request->is_flutterwave_enabled) && $request->is_flutterwave_enabled == 'on') {
            $request->validate(
                [
                    'flutterwave_public_key' => 'required|string',
                    'flutterwave_secret_key' => 'required|string',
                ]
            );
            $post['is_flutterwave_enabled'] = $request->is_flutterwave_enabled;
            $post['flutterwave_public_key'] = $request->flutterwave_public_key;
            $post['flutterwave_secret_key'] = $request->flutterwave_secret_key;
        } else {
            $post['is_flutterwave_enabled'] = 'off';
        }

        //razorpay
        if (isset($request->is_razorpay_enabled) && $request->is_razorpay_enabled == 'on') {
            $request->validate(
                [
                    'razorpay_public_key' => 'required|string',
                    'razorpay_secret_key' => 'required|string',
                ]
            );
            $post['is_razorpay_enabled'] = $request->is_razorpay_enabled;
            $post['razorpay_public_key'] = $request->razorpay_public_key;
            $post['razorpay_secret_key'] = $request->razorpay_secret_key;
        } else {
            $post['is_razorpay_enabled'] = 'off';
        }

        //mercado
        if (isset($request->is_mercado_enabled) && $request->is_mercado_enabled == 'on') {
            $request->validate(
                [
                    'mercado_access_token' => 'required|string',
                ]
            );
            $post['is_mercado_enabled'] = $request->is_mercado_enabled;
            $post['mercado_access_token']     = $request->mercado_access_token;
            $post['mercado_mode'] = $request->mercado_mode;
        } else {
            $post['is_mercado_enabled'] = 'off';
        }

        //paytm
        if (isset($request->is_paytm_enabled) && $request->is_paytm_enabled == 'on') {
            $request->validate(
                [
                    'paytm_mode' => 'required',
                    'paytm_merchant_id' => 'required|string',
                    'paytm_merchant_key' => 'required|string',
                    'paytm_industry_type' => 'required|string',
                ]
            );
            $post['is_paytm_enabled']    = $request->is_paytm_enabled;
            $post['paytm_mode']          = $request->paytm_mode;
            $post['paytm_merchant_id']   = $request->paytm_merchant_id;
            $post['paytm_merchant_key']  = $request->paytm_merchant_key;
            $post['paytm_industry_type'] = $request->paytm_industry_type;
        } else {
            $post['is_paytm_enabled'] = 'off';
        }

        //mollie
        if (isset($request->is_mollie_enabled) && $request->is_mollie_enabled == 'on') {
            $request->validate(
                [
                    'mollie_api_key' => 'required|string',
                    'mollie_profile_id' => 'required|string',
                    'mollie_partner_id' => 'required',
                ]
            );
            $post['is_mollie_enabled'] = $request->is_mollie_enabled;
            $post['mollie_api_key']    = $request->mollie_api_key;
            $post['mollie_profile_id'] = $request->mollie_profile_id;
            $post['mollie_partner_id'] = $request->mollie_partner_id;
        } else {
            $post['is_mollie_enabled'] = 'off';
        }
        //skrill
        if (isset($request->is_skrill_enabled) && $request->is_skrill_enabled == 'on') {
            $request->validate(
                [
                    'skrill_email' => 'required|email',
                ]
            );
            $post['is_skrill_enabled'] = $request->is_skrill_enabled;
            $post['skrill_email']      = $request->skrill_email;
        } else {
            $post['is_skrill_enabled'] = 'off';
        }

        //coingate
        if (isset($request->is_coingate_enabled) && $request->is_coingate_enabled == 'on') {
            $request->validate(
                [
                    'coingate_mode' => 'required|string',
                    'coingate_auth_token' => 'required|string',
                ]
            );

            $post['is_coingate_enabled'] = $request->is_coingate_enabled;
            $post['coingate_mode']       = $request->coingate_mode;
            $post['coingate_auth_token'] = $request->coingate_auth_token;
        } else {
            $post['is_coingate_enabled'] = 'off';
        }

        //paymentwall
        if (isset($request->is_paymentwall_enabled) && $request->is_paymentwall_enabled == 'on') {
            $request->validate(
                [
                    'paymentwall_public_key' => 'required|string',
                    'paymentwall_private_key' => 'required|string',
                ]
            );
            $post['is_paymentwall_enabled'] = $request->is_paymentwall_enabled;
            $post['paymentwall_public_key'] = $request->paymentwall_public_key;
            $post['paymentwall_private_key'] = $request->paymentwall_private_key;
        } else {
            $post['is_paymentwall_enabled'] = 'off';
        }
        //toyyibpay
        if (isset($request->is_toyyibpay_enabled) && $request->is_toyyibpay_enabled == 'on') {
            $request->validate(
                [
                    'toyyibpay_secret_key' => 'required|string|max:255',
                    'category_code' => 'required|string|max:255',
                ]
            );

            $post['is_toyyibpay_enabled'] = $request->is_toyyibpay_enabled;
            $post['category_code']     = $request->category_code;
            $post['toyyibpay_secret_key']        = $request->toyyibpay_secret_key;
        } else {
            $post['is_toyyibpay_enabled'] = 'off';
        }
        //payfast
        if (isset($request->is_payfast_enabled) && $request->is_payfast_enabled == 'on') {
            $validator = Validator::make(
                $request->all(),
                [
                    'payfast_mode' => 'required',
                    'payfast_merchant_id' => 'required|string',
                    'payfast_merchant_key' => 'required|string',
                    'payfast_signature' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // dd($request->is_payfast_enabled);
            $post['is_payfast_enabled'] = $request->is_payfast_enabled;
            $post['payfast_mode'] = $request->payfast_mode;
            $post['payfast_merchant_id'] = $request->payfast_merchant_id;
            $post['payfast_merchant_key'] = $request->payfast_merchant_key;
            $post['payfast_signature'] = $request->payfast_signature;
        } else {
            $post['is_payfast_enabled'] = 'off';
        }
        //Bank Transfer
        if (isset($request->is_bank_transfer_enabled) && $request->is_bank_transfer_enabled == 'on') {
            $validator = Validator::make(
                $request->all(),
                [
                    'bank_details' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // dd($request->is_bank_transfer_enabled);
            $post['is_bank_transfer_enabled'] = $request->is_bank_transfer_enabled;
            $post['bank_details'] = $request->bank_details;
        } else {
            $post['is_bank_transfer_enabled'] = 'off';
        }

        //Manually
        if (isset($request->is_manually_enabled) && $request->is_manually_enabled == 'on') {
            // dd($request->is_manually_enabled);
            $post['is_manually_enabled'] = $request->is_manually_enabled;
        } else {
            $post['is_manually_enabled'] = 'off';
        }
        //Iyzipay
        if (isset($request->is_iyzipay_enabled) && $request->is_iyzipay_enabled == 'on') {
            $request->validate(
                [
                    'iyzipay_mode' => 'required',
                    'iyzipay_public_key' => 'required|string',
                    'iyzipay_secret_key' => 'required|string',
                ]
            );

            $post['is_iyzipay_enabled']      = $request->is_iyzipay_enabled;
            $post['iyzipay_mode']            = $request->iyzipay_mode;
            $post['iyzipay_public_key']     = $request->iyzipay_public_key;
            $post['iyzipay_secret_key']    = $request->iyzipay_secret_key;
        } else {
            $post['is_iyzipay_enabled'] = 'off';
        }

        //sspay
        if (isset($request->is_sspay_enabled) && $request->is_sspay_enabled == 'on') {

            $request->validate(
                [
                    'sspay_secret_key' => 'required|string|max:255',
                    'sspay_category_code' => 'required|string|max:255',
                ]
            );

            $post['is_sspay_enabled'] = $request->is_sspay_enabled;
            $post['sspay_category_code']     = $request->sspay_category_code;
            $post['sspay_secret_key']        = $request->sspay_secret_key;
        } else {
            $post['is_sspay_enabled'] = 'off';
        }

        //paytab
        if (isset($request->is_paytab_enabled) && $request->is_paytab_enabled == 'on') {
            $validator = Validator::make(
                $request->all(),
                [
                    'is_paytab_enabled' => 'required',
                    'paytab_profile_id' => 'required|string',
                    'paytab_server_key' => 'required|string',
                    'paytab_region' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paytab_enabled'] = $request->is_paytab_enabled;
            $post['paytab_profile_id'] = $request->paytab_profile_id;
            $post['paytab_server_key'] = $request->paytab_server_key;
            $post['paytab_region'] = $request->paytab_region;
        } else {
            $post['is_paytab_enabled'] = 'off';
        }

        //banefit
        if (isset($request->is_benefit_enabled) && $request->is_benefit_enabled == 'on') {

            $validator = Validator::make(
                $request->all(),
                [
                    'is_benefit_enabled' => 'required',
                    'benefit_api_key' => 'required|string',
                    'benefit_secret_key' => 'required|string',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_benefit_enabled'] = $request->is_benefit_enabled;
            $post['benefit_api_key'] = $request->benefit_api_key;
            $post['benefit_secret_key'] = $request->benefit_secret_key;
        } else {
            $post['is_benefit_enabled'] = 'off';
        }

        //cashfree
        if (isset($request->is_cashfree_enabled) && $request->is_cashfree_enabled == 'on') {
            $validator = Validator::make(
                $request->all(),
                [
                    'is_cashfree_enabled' => 'required',
                    'cashfree_api_key' => 'required|string',
                    'cashfree_secret_key' => 'required|string',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_cashfree_enabled'] = $request->is_cashfree_enabled;
            $post['cashfree_api_key'] = $request->cashfree_api_key;
            $post['cashfree_secret_key'] = $request->cashfree_secret_key;
        } else {
            $post['is_cashfree_enabled'] = 'off';
        }

        //aamarpay
        if (isset($request->is_aamarpay_enabled) && $request->is_aamarpay_enabled == 'on') {
            $validator = Validator::make(
                $request->all(),
                [
                    'is_aamarpay_enabled' => 'required',
                    'aamarpay_store_id' => 'required|string',
                    'aamarpay_signature_key' => 'required|string',
                    'aamarpay_description' => 'required|string',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_aamarpay_enabled'] = $request->is_aamarpay_enabled;
            $post['aamarpay_store_id'] = $request->aamarpay_store_id;
            $post['aamarpay_signature_key'] = $request->aamarpay_signature_key;
            $post['aamarpay_description'] = $request->aamarpay_description;
        } else {
            $post['is_aamarpay_enabled'] = 'off';
        }

        //paytr
        if (isset($request->is_paytr_enabled) && $request->is_paytr_enabled == 'on') {
            $validator = Validator::make(
                $request->all(),
                [
                    'is_paytr_enabled' => 'required',
                    'paytr_merchant_id' => 'required|string',
                    'paytr_merchant_key' => 'required|string',
                    'paytr_merchant_salt' => 'required|string',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paytr_enabled'] = $request->is_paytr_enabled;
            $post['paytr_merchant_id'] = $request->paytr_merchant_id;
            $post['paytr_merchant_key'] = $request->paytr_merchant_key;
            $post['paytr_merchant_salt'] = $request->paytr_merchant_salt;
        } else {
            $post['is_paytr_enabled'] = 'off';
        }

        //yookassa
        if(isset($request->is_yookassa_enabled) && $request->is_yookassa_enabled == 'on'){
            $validator = Validator::make(
                $request->all(),
                [
                    'yookassa_shop_id'      => 'required|string',
                    'yookassa_secret_key'  => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_yookassa_enabled']    = $request->is_yookassa_enabled;
            $post['yookassa_shop_id']       = $request->yookassa_shop_id;
            $post['yookassa_secret_key']   = $request->yookassa_secret_key;
        } else {
            $post['is_yookassa_enabled'] = 'off';
        }

        //midtrans
        if(isset($request->is_midtrans_enabled) && $request->is_midtrans_enabled == 'on'){
            $validator = Validator::make(
                $request->all(),
                [
                    'midtrans_secret' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $post['midtrans_mode']           = $request->midtrans_mode;
            $post['is_midtrans_enabled']    = $request->is_midtrans_enabled;
            $post['midtrans_secret']        = $request->midtrans_secret;
        } else {
            $post['is_midtrans_enabled'] = 'off';
        }

        //xendit
        if(isset($request->is_xendit_enabled) && $request->is_xendit_enabled == 'on'){
            $validator = Validator::make(
                $request->all(),
                [
                    'xendit_api_key'    => 'required|string',
                    'xendit_token'      => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_xendit_enabled']  = $request->is_xendit_enabled;
            $post['xendit_api_key']     = $request->xendit_api_key;
            $post['xendit_token']       = $request->xendit_token;
        } else {
            $post['is_xendit_enabled'] = 'off';
        }
          //PaiementPro
          if (isset($request->is_PaiementPro_enabled) && $request->is_PaiementPro_enabled == 'on') {
            $validator = Validator::make(
                $request->all(),
                [
                    'is_PaiementPro_enabled' => 'required',
                    'PaiementPro_merchant_key' => 'required|string',


                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_PaiementPro_enabled'] = $request->is_PaiementPro_enabled;

            $post['PaiementPro_merchant_key'] = $request->PaiementPro_merchant_key;

        } else {
            $post['is_PaiementPro_enabled'] = 'off';
        }



                  //CinetPay
                  if (isset($request->is_CinetPay_enabled) && $request->is_CinetPay_enabled == 'on') {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'is_CinetPay_enabled' => 'required',
                            'CinetPay_site_id'=>'required',
                                'CinetPay_api_key' => 'required|string',
                                'CinetPay_seceret_key'=>'required|string'


                        ]
                    );

                    if ($validator->fails()) {
                        $messages = $validator->getMessageBag();

                        return redirect()->back()->with('error', $messages->first());
                    }

                    $post['is_CinetPay_enabled'] = $request->is_CinetPay_enabled;
                    $post['CinetPay_api_key'] = $request->CinetPay_api_key;
                    $post['CinetPay_site_id'] = $request->CinetPay_site_id;
                    $post['CinetPay_seceret_key'] = $request->CinetPay_seceret_key;

                } else {
                    $post['is_CinetPay_enabled'] = 'off';
                }
                // dd($request);

// FEDA PAY
                if (isset($request->is_Fedapay_enabled) && $request->is_Fedapay_enabled == 'on') {
                    $request->validate(
                        [
                            'Fedapay_public_key' => 'required|string',
                            'Fedapay_secret_key' => 'required|string',
                            'Fedapay_mode'=>'required',
                        ]
                    );
                    $post['is_Fedapay_enabled'] = $request->is_Fedapay_enabled;
                    $post['Fedapay_public_key'] = $request->Fedapay_public_key;
                    $post['Fedapay_secret_key'] = $request->Fedapay_secret_key;
                    $post['Fedapay_mode'] = $request->Fedapay_mode;
                } else {
                    $post['is_Fedapay_enabled'] = 'off';
                }


                // PayHere Payment

                if (isset($request->is_PayHere_enabled) && $request->is_PayHere_enabled == 'on') {
                    $request->validate(
                        [
                            'PayHere_public_id' => 'required',
                            'PayHere_secret_key' => 'required|string',
                            "PayHere_merchant_id"=>"required",
                            'PayHere_mode'=> 'required',
                            'PayHere_merchant_secret_key'=>"required",
                        ]

                    );
                    $post['is_PayHere_enabled'] = $request->is_PayHere_enabled;
                    $post['PayHere_public_id'] = $request->PayHere_public_id;
                    $post['PayHere_secret_key'] = $request->PayHere_secret_key;
                    $post['PayHere_merchant_id'] = $request->PayHere_merchant_id;
                    $post['PayHere_merchant_secret_key'] = $request->PayHere_merchant_secret_key;
                    $post['PayHere_mode'] = $request->PayHere_mode;
                } else {
                    $post['is_PayHere_enabled'] = 'off';
                }





        if (isset($request->is_nepalste_enabled) && $request->is_nepalste_enabled == 'on') {
            $request->validate(
                [
                    'nepalste_public_key' => 'required|string',
                    'nepalste_secret_key' => 'required|string',
                ]
            );
            $post['is_nepalste_enabled'] = $request->is_nepalste_enabled;
            $post['nepalste_public_key'] = $request->nepalste_public_key;
            $post['nepalste_secret_key'] = $request->nepalste_secret_key;
            $post['nepalste_mode'] = $request->nepalste_mode;
        } else {
            $post['is_nepalste_enabled'] = 'off';
        }

            // dd($post);
        foreach ($post as $key => $data) {
            $arr = [
                $data,
                $key,
                \Auth::user()->id,
            ];
            \DB::insert(
                'insert into admin_payment_settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                $arr
            );
        }
        return redirect()->back()->with('success', __('Payment setting successfully updated.'));
    }

    public function saveZoomSettings(Request $request)
    {
        $post = $request->all();

        unset($post['_token']);
        $created_by = \Auth::user()->creatorId();

        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,                                                                                                                                                                              $key,                                                                                                                                                                               $created_by,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s'),
                ]
            );
        }
        return redirect()->back()->with('success', __('Setting added successfully saved.'));
    }

    public function slack(Request $request)
    {
        $post = [];
        $post['slack_webhook'] = $request->input('slack_webhook');
        $post['holiday_create_notification'] = $request->has('holiday_create_notification') ? $request->input('holiday_create_notification') : 0;
        $post['meeting_create_notification'] = $request->has('meeting_create_notification') ? $request->input('meeting_create_notification') : 0;
        $post['company_policy_create_notification'] = $request->has('company_policy_create_notification') ? $request->input('company_policy_create_notification') : 0;
        $post['award_create_notification'] = $request->has('award_create_notification') ? $request->input('award_create_notification') : 0;
        $post['lead_create_notification'] = $request->has('lead_create_notification') ? $request->input('lead_create_notification') : 0;
        $post['deal_create_notification'] = $request->has('deal_create_notification') ? $request->input('deal_create_notification') : 0;
        $post['convert_lead_to_deal_notification'] = $request->has('convert_lead_to_deal_notification') ? $request->input('convert_lead_to_deal_notification') : 0;
        $post['estimation_create_notification'] = $request->has('estimation_create_notification') ? $request->input('estimation_create_notification') : 0;
        $post['project_create_notification'] = $request->has('project_create_notification') ? $request->input('project_create_notification') : 0;
        $post['project_status_updated_notification'] = $request->has('project_status_updated_notification') ? $request->input('project_status_updated_notification') : 0;
        $post['task_create_notification'] = $request->has('task_create_notification') ? $request->input('task_create_notification') : 0;
        $post['task_move_notification'] = $request->has('task_move_notification') ? $request->input('task_move_notification') : 0;
        $post['task_comment_notification'] = $request->has('task_comment_notification') ? $request->input('task_comment_notification') : 0;
        $post['milestone_create_notification'] = $request->has('milestone_create_notification') ? $request->input('milestone_create_notification') : 0;
        $post['invoice_create_notification'] = $request->has('invoice_create_notification') ? $request->input('invoice_create_notification') : 0;
        $post['invoice_status_updated_notification'] = $request->has('invoice_status_updated_notification') ? $request->input('invoice_status_updated_notification') : 0;
        $post['payment_create_notification'] = $request->has('payment_create_notification') ? $request->input('payment_create_notification') : 0;
        $post['contract_create_notification'] = $request->has('contract_create_notification') ? $request->input('contract_create_notification') : 0;
        $post['support_create_notification'] = $request->has('support_create_notification') ? $request->input('support_create_notification') : 0;
        $post['event_create_notification'] = $request->has('event_create_notification') ? $request->input('event_create_notification') : 0;
        $created_by = \Auth::user()->creatorId();
        if (isset($post) && !empty($post) && count($post) > 0) {
            $created_at = $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {
                \DB::insert(
                    'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                    [
                        $data,                                                                                                                                                                                                             $key,                                                                                                                                                                                                                      $created_by,
                        $created_at,
                        $updated_at,
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Settings updated successfully.'));
    }
    public function telegram(Request $request)
    {
        $post = [];
        $post['telegrambot'] = $request->input('telegrambot');
        $post['telegramchatid'] = $request->input('telegramchatid');
        $post['telegram_holiday_create_notification'] = $request->has('telegram_holiday_create_notification') ? $request->input('telegram_holiday_create_notification') : 0;
        $post['telegram_meeting_create_notification'] = $request->has('telegram_meeting_create_notification') ? $request->input('telegram_meeting_create_notification') : 0;
        $post['telegram_company_policy_create_notification'] = $request->has('telegram_company_policy_create_notification') ? $request->input('telegram_company_policy_create_notification') : 0;
        $post['telegram_award_create_notification'] = $request->has('telegram_award_create_notification') ? $request->input('telegram_award_create_notification') : 0;
        $post['telegram_lead_create_notification'] = $request->has('telegram_lead_create_notification') ? $request->input('telegram_lead_create_notification') : 0;
        $post['telegram_deal_create_notification'] = $request->has('telegram_deal_create_notification') ? $request->input('telegram_deal_create_notification') : 0;
        $post['telegram_convert_lead_to_deal_notification'] = $request->has('telegram_convert_lead_to_deal_notification') ? $request->input('telegram_convert_lead_to_deal_notification') : 0;
        $post['telegram_estimation_create_notification'] = $request->has('telegram_estimation_create_notification') ? $request->input('telegram_estimation_create_notification') : 0;
        $post['telegram_project_create_notification'] = $request->has('telegram_project_create_notification') ? $request->input('telegram_project_create_notification') : 0;
        $post['telegram_project_status_updated_notification'] = $request->has('telegram_project_status_updated_notification') ? $request->input('telegram_project_status_updated_notification') : 0;
        $post['telegram_task_create_notification'] = $request->has('telegram_task_create_notification') ? $request->input('telegram_task_create_notification') : 0;
        $post['telegram_task_move_notification'] = $request->has('telegram_task_move_notification') ? $request->input('telegram_task_move_notification') : 0;
        $post['telegram_task_comment_notification'] = $request->has('telegram_task_comment_notification') ? $request->input('telegram_task_comment_notification') : 0;
        $post['telegram_milestone_create_notification'] = $request->has('telegram_milestone_create_notification') ? $request->input('telegram_milestone_create_notification') : 0;
        $post['telegram_invoice_create_notification'] = $request->has('telegram_invoice_create_notification') ? $request->input('telegram_invoice_create_notification') : 0;
        $post['telegram_invoice_status_updated_notification'] = $request->has('telegram_invoice_status_updated_notification') ? $request->input('telegram_invoice_status_updated_notification') : 0;
        $post['telegram_payment_create_notification'] = $request->has('telegram_payment_create_notification') ? $request->input('telegram_payment_create_notification') : 0;
        $post['telegram_contract_create_notification'] = $request->has('telegram_contract_create_notification') ? $request->input('telegram_contract_create_notification') : 0;
        $post['telegram_support_create_notification'] = $request->has('telegram_support_create_notification') ? $request->input('telegram_support_create_notification') : 0;
        $post['telegram_event_create_notification'] = $request->has('telegram_event_create_notification') ? $request->input('telegram_event_create_notification') : 0;

        $created_by = \Auth::user()->creatorId();
        if (isset($post) && !empty($post) && count($post) > 0) {
            $created_at = $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        $created_by,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Setting added successfully saved.'));
    }
    public function twilio(Request $request)
    {

        $post = [];
        $post['twilio_sid'] = $request->input('twilio_sid');
        $post['twilio_token'] = $request->input('twilio_token');
        $post['twilio_from'] = $request->input('twilio_from');
        $post['twilio_leave_approve_reject_notification'] = $request->has('twilio_leave_approve_reject_notification') ? $request->input('twilio_leave_approve_reject_notification') : 0;
        $post['twilio_award_create_notification'] = $request->has('twilio_award_create_notification') ? $request->input('twilio_award_create_notification') : 0;
        $post['twilio_trip_create_notification'] = $request->has('twilio_trip_create_notification') ? $request->input('twilio_trip_create_notification') : 0;
        $post['twilio_ticket_create_notification'] = $request->has('twilio_ticket_create_notification') ? $request->input('twilio_ticket_create_notification') : 0;
        $post['twilio_event_create_notification'] = $request->has('twilio_event_create_notification') ? $request->input('twilio_event_create_notification') : 0;
        $post['twilio_project_create_notification'] = $request->has('twilio_project_create_notification') ? $request->input('twilio_project_create_notification') : 0;
        $post['twilio_task_create_notification'] = $request->has('twilio_task_create_notification') ? $request->input('twilio_task_create_notification') : 0;
        $post['twilio_contract_create_notification'] = $request->has('twilio_contract_create_notification') ? $request->input('twilio_contract_create_notification') : 0;
        $post['twilio_invoice_create_notification'] = $request->has('twilio_invoice_create_notification') ? $request->input('twilio_invoice_create_notification') : 0;
        $post['twilio_invoice_payment_create_notification'] = $request->has('twilio_invoice_payment_create_notification') ? $request->input('twilio_invoice_payment_create_notification') : 0;
        $post['twilio_payment_create_notification'] = $request->has('twilio_payment_create_notification') ? $request->input('twilio_payment_create_notification') : 0;

        $created_by = \Auth::user()->creatorId();
        if (isset($post) && !empty($post) && count($post) > 0) {
            $created_at = $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        $created_by,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Setting added successfully saved.'));
    }

    public function recaptchaSettingStore(Request $request)
    {
        $user = \Auth::user();
        $rules = [];
        if ($request->recaptcha_module == 'yes') {
            $rules['google_recaptcha_key']      = 'required|string|max:50';
            $rules['google_recaptcha_secret']   = 'required|string|max:50';
            $rules['google_recaptcha_version']  = 'required';
        }
        $validator = \Validator::make(
            $request->all(),
            $rules
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        if (isset($request->recaptcha_module) && $request->recaptcha_module == 'yes') {

            $post['recaptcha_module']           = $request->recaptcha_module;
            $post['google_recaptcha_key']       = $request->google_recaptcha_key;
            $post['google_recaptcha_secret']    = $request->google_recaptcha_secret;
            $post['google_recaptcha_version']   = $request->google_recaptcha_version;
        } else {
            $post['recaptcha_module'] = 'no';
            $post['google_recaptcha_key'] = $request->google_recaptcha_key;
            $post['google_recaptcha_secret'] = $request->google_recaptcha_secret;
            $post['google_recaptcha_version'] = $request->google_recaptcha_version;
        }

        $created_by = \Auth::user()->creatorId();
        $created_at = $updated_at = date('Y-m-d H:i:s');

        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`)
                values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    $created_by,
                    $created_at,
                    $updated_at,
                ]
            );
        }

        return redirect()->back()->with('success', __('Recaptcha Settings updated successfully'));
    }

    public function storageSettingStore(Request $request)
    {
        // dd($request);
        if (isset($request->storage_setting) && $request->storage_setting == 'local') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'local_storage_validation' => 'required',
                    'local_storage_max_upload_size' => 'required',
                    ]
                );
                $request->validate(
                    [

                        'local_storage_validation' => 'required',
                        'local_storage_max_upload_size' => 'required',

                        ]
                    );
                    $post['storage_setting'] = $request->storage_setting;
                    $local_storage_validation = implode(',', $request->local_storage_validation);
                    $post['local_storage_validation'] = $local_storage_validation;
                    $post['local_storage_max_upload_size'] = $request->local_storage_max_upload_size;

        }

        if (isset($request->storage_setting) && $request->storage_setting == 's3') {

            $request->validate(
                [
                    's3_key'                  => 'required',
                    's3_secret'               => 'required',
                    's3_region'               => 'required',
                    's3_bucket'               => 'required',
                    's3_url'                  => 'required',
                    's3_endpoint'             => 'required',
                    's3_max_upload_size'      => 'required',
                    's3_storage_validation'   => 'required',
                ]
            );
            $post['storage_setting']            = $request->storage_setting;
            $post['s3_key']                     = $request->s3_key;
            $post['s3_secret']                  = $request->s3_secret;
            $post['s3_region']                  = $request->s3_region;
            $post['s3_bucket']                  = $request->s3_bucket;
            $post['s3_url']                     = $request->s3_url;
            $post['s3_endpoint']                = $request->s3_endpoint;
            $post['s3_max_upload_size']         = $request->s3_max_upload_size;
            $s3_storage_validation              = implode(',', $request->s3_storage_validation);
            $post['s3_storage_validation']      = $s3_storage_validation;

        }

        if (isset($request->storage_setting) && $request->storage_setting == 'wasabi') {
            $request->validate(
                [
                    'wasabi_key'                    => 'required',
                    'wasabi_secret'                 => 'required',
                    'wasabi_region'                 => 'required',
                    'wasabi_bucket'                 => 'required',
                    'wasabi_url'                    => 'required',
                    'wasabi_root'                   => 'required',
                    'wasabi_max_upload_size'        => 'required',
                    'wasabi_storage_validation'     => 'required',
                ]
            );
            $post['storage_setting']            = $request->storage_setting;
            $post['wasabi_key']                 = $request->wasabi_key;
            $post['wasabi_secret']              = $request->wasabi_secret;
            $post['wasabi_region']              = $request->wasabi_region;
            $post['wasabi_bucket']              = $request->wasabi_bucket;
            $post['wasabi_url']                 = $request->wasabi_url;
            $post['wasabi_root']                = $request->wasabi_root;
            $post['wasabi_max_upload_size']     = $request->wasabi_max_upload_size;
            $wasabi_storage_validation          = implode(',', $request->wasabi_storage_validation);
            $post['wasabi_storage_validation']  = $wasabi_storage_validation;
        }


        foreach ($post as $key => $data) {

            $arr = [
                $data,
                $key,
                \Auth::user()->id,
            ];

            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                $arr
            );
        }
        return redirect()->back()->with('success', 'Storage setting successfully updated.');
    }

    public function saveGoogleCalenderSettings(Request $request)
    {

        if ($request->is_googleCal_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'google_calender_json_file' => 'required',
                    'google_clender_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('settings')->with('error', $messages->first());
            }
        }
        if ($request->google_calender_json_file) {
            $dir       = storage_path() . '/' . md5(time());
            if (!is_dir($dir)) {
                File::makeDirectory($dir, $mode = 0777, true, true);
            }
            $file_name = $request->google_calender_json_file->getClientOriginalName();
            $file_path =  md5(time()) . "/" . $request->google_calender_json_file->getClientOriginalExtension();

            $file = $request->file('google_calender_json_file');
            $file->move($dir, $file_path);
            $post['google_calender_json_file']            = $file_path;
        }
        if ($request->google_clender_id) {
            $post['google_clender_id']            = $request->google_clender_id;
            $post['is_googleCal_enabled']            = $request->is_googleCal_enabled;

            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->id,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', 'Google Calendar setting successfully updated.');
    }
    public function saveseo(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'meta_keyword' => 'required|string',
                'meta_description' => 'required|string',
                'meta_image' => 'required|file',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $dir = storage_path() . '/' . 'meta';
        if (!is_dir($dir)) {
            \File::makeDirectory($dir, $mode = 0777, true, true);
        }
        $file_name = $request->meta_image->getClientOriginalName();
        $file_path = $request->meta_image->getClientOriginalName();
        $file = $request->file('meta_image');
        $file->move($dir, $file_path);

        $post['meta_keyword'] = $request->meta_keyword;
        $post['meta_description'] = $request->meta_description;
        $post['meta_image'] = $file_path;

        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    \Auth::user()->id,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s'),
                ]
            );
        }
        return redirect()->back()->with('success', 'SEO setting successfully save.');
    }

    //webhook Settings
    public function webhooksettings(Request $request)
    {
        $webhook = Webhook_settings::$module;
        $method = Webhook_settings::$method;
        return view('settings.webhook', compact('webhook', 'method'));
    }

    public function webhookstore(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'module' => 'required',
                'url' => 'required|url',
                'method' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('settings')->with('error', $messages->first());
        }
        $webhook = new Webhook_settings();
        $webhook->module = $request->module;
        $webhook->url = $request->url;
        $webhook->method = $request->method;
        $webhook->created_by = \Auth::user()->id;
        $webhook->save();
        return redirect()->back()->with('success', 'Webbook Setting Created Successfully.');
    }

    public function editwebhook($id, Request $request)
    {
        $data = Webhook_settings::find($id);
        $webhook = Webhook_settings::$module;
        $method = Webhook_settings::$method;
        return view('settings.editwebhook', compact('webhook', 'method', 'data'));
    }

    public function updatewebhook(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'module' => 'required',
                'url' => 'required|url',
                'method' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->route('settings')->with('error', $messages->first());
        }

        $webhook = Webhook_settings::find($id);
        $webhook->module = $request->module;
        $webhook->url = $request->url;
        $webhook->method = $request->method;
        $webhook->created_by = \Auth::user()->id;
        $webhook->update();

        return redirect()->back()->with('success', 'Webhook Updated Successfully.');
    }

    public function webhookdestroy($id)
    {
        $webhook = Webhook_settings::find($id);
        $webhook->delete();

        return redirect()->back()->with('success', 'Webhook Deleted Successfully.');
    }

    public function WebhookResponse(Request $request)
    {
        // $user = User::where('email',$request['email'])->first();
        // if(empty($user))
        // {
        //     User::create([
        //         'name' => $request['name'],
        //         'email' => $request['email'],
        //         'password' => Hash::make($request['password']),
        //     ]);
        // }

        \Log::debug('*******************************************************************************');
        \Log::debug($request->all());
        \Log::debug('*******************************************************************************');
    }

    //cookie settings
    public function saveCookieSettings(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'cookie_title' => 'required',
                'cookie_description' => 'required',
                'strictly_cookie_title' => 'required',
                'strictly_cookie_description' => 'required',
                'more_information_description' => 'required',
                'contactus_url' => 'required',
            ]
        );

        $post = $request->all();

        unset($post['_token']);

        if ($request->enable_cookie) {
            $post['enable_cookie'] = 'on';
        } else {
            $post['enable_cookie'] = 'off';
        }
        if ($request->cookie_logging) {
            $post['cookie_logging'] = 'on';
        } else {
            $post['cookie_logging'] = 'off';
        }

        $post['cookie_title']            = $request->cookie_title;
        $post['cookie_description']            = $request->cookie_description;
        $post['strictly_cookie_title']            = $request->strictly_cookie_title;
        $post['strictly_cookie_description']            = $request->strictly_cookie_description;
        $post['more_information_description']            = $request->more_information_description;
        $post['contactus_url']            = $request->contactus_url;
        $settings = Utility::settings();
        foreach ($post as $key => $data) {

            if (in_array($key, array_keys($settings))) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->creatorId(),
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', 'Cookie setting successfully saved.');
    }

    public function CookieConsent(Request $request)
    {
        if ($request['cookie']) {
            $settings = Utility::settings();

            if ($settings['enable_cookie'] == "on" && $settings['cookie_logging'] == "on") {

                $allowed_levels = ['necessary', 'analytics', 'targeting'];
                $levels = array_filter($request['cookie'], function ($level) use ($allowed_levels) {
                    return in_array($level, $allowed_levels);
                });
                $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
                // Generate new CSV line
                $browser_name = $whichbrowser->browser->name ?? null;
                $os_name = $whichbrowser->os->name ?? null;
                $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
                $device_type = Utility::get_device_type($_SERVER['HTTP_USER_AGENT']);

                //  $ip = $_SERVER['REMOTE_ADDR'];
                $ip = '49.36.83.154';
                $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));


                $date = (new \DateTime())->format('Y-m-d');
                $time = (new \DateTime())->format('H:i:s') . ' UTC';


                $new_line = implode(',', [
                    $ip, $date, $time, json_encode($request['cookie']), $device_type, $browser_language, $browser_name, $os_name,
                    isset($query) ? $query['country'] : '', isset($query) ? $query['region'] : '', isset($query) ? $query['regionName'] : '', isset($query) ? $query['city'] : '', isset($query) ? $query['zip'] : '', isset($query) ? $query['lat'] : '', isset($query) ? $query['lon'] : ''
                ]);

                if (!file_exists(storage_path() . '/uploads/sample/data.csv')) {

                    $first_line = 'IP,Date,Time,Accepted cookies,Device type,Browser language,Browser name,OS Name,Country,Region,RegionName,City,Zipcode,Lat,Lon';
                    file_put_contents(storage_path() . '/uploads/sample/data.csv', $first_line . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
                file_put_contents(storage_path() . '/uploads/sample/data.csv', $new_line . PHP_EOL, FILE_APPEND | LOCK_EX);

                return response()->json('success');
            }
            return response()->json('error');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function chatgptkey(Request $request)
    {
        if (\Auth::user()->type == 'super admin') {
            $user = \Auth::user();
            if (!empty($request->chatgpt_key)) {
                $post = $request->all();
                $post['chatgpt_key'] = $request->chatgpt_key;

                unset($post['_token']);
                $post['chatgpt_model_name']            = $request->chatgpt_model_name;
                $post['chatgpt_key']            = $request->chatgpt_key;
                $settings = Utility::settings();
                foreach ($post as $key => $data) {

                    // if (in_array($key, array_keys($settings))) {
                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $data,
                            $key,
                            \Auth::user()->creatorId(),
                            date('Y-m-d H:i:s'),
                            date('Y-m-d H:i:s'),
                        ]
                    );
                    // }
                }
            }
            return redirect()->back()->with('success', __('Chatgpt key successfully saved.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
