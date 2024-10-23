<?php

namespace App\Models;

use App\Mail\CommonEmailTemplate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use App\Models\Languages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\GoogleCalendar\Event as GoogleEvent;
use Twilio\Rest\Client;

class Utility extends Model
{

    public static $settings_data;
    public static $languages;
    public static $has_Table_languages;

    public static function settings()
    {

        if(self::$settings_data == null)
        {
            $data = DB::table('settings');
            if (\Auth::check()) {
                $userId = \Auth::user();
                if($userId->user_type !="super admin")
                {
                    $data = $data->where('created_by', '=', $userId->id)->where('name',"!=",'comapny_access_token')->where('name',"!=",'comapny_refresh_token');
                }
                else
                {
                    $data = $data->where('created_by', '=', 1);
                }
            }
            else
            {
                $data = DB::table('settings')->where('created_by', '=', 1);
            }
            $data = $data->get();
            if(count($data)<=0)
            {
                $data = DB::table('settings')->where('created_by', '=', 1)->get();
            }

            self::$settings_data = $data;
        }

        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "employee_prefix" => "#EMP",
            "client_prefix" => "#CLT",
            "estimate_prefix" => "#EST",
            "invoice_prefix" => "#INV",

            "contract_prefix" => "#CON",
            "company_name" => "",
            "company_address" => "",
            "company_city" => "",
            "company_state" => "",
            "company_zipcode" => "",
            "company_country" => "",
            "company_telephone" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "footer_title" => "",
            "footer_notes" => "",
            "invoice_template" => "template1",
            "invoice_color" => "ffffff",
            "contract_template" => "template1",
            "contract_color" => "ffffff",
            "estimate_template" => "template1",
            "QR_code" => "off",
            "estimate_color" => "ffffff",
            "invoice_qrcode" => "off",

            "company_start_time" => "09:00",
            "company_end_time" => "18:00",
            "default_language" => "en",
            "enable_stripe" => "",
            "enable_paypal" => "",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "stripe_key" => "",
            "stripe_secret" => "",
            "registration_number" => "",
            "vat_number" => "",
            "footer_link_1" => "Support",
            "footer_value_1" => "#",
            "footer_link_2" => "Terms",
            "footer_value_2" => "#",
            "footer_link_3" => "Privacy",
            "footer_value_3" => "#",
            "display_landing_page" => "on",
            "title_text" => "CRMGo SaaS",
            "journal_prefix" => "#JUR",
            "footer_text" => "CRMGo SaaS",
            "gdpr_cookie" => "",
            "cookie_text" => "",
            "SIGNUP" => "on",
            "cust_theme_bg" => "on",
            "cust_darklayout" => "off",
            "color" => "theme-3",
            "SITE_RTL" => 'off',

            // recaptcha
            "google_recaptcha_version"   =>   '',
            "recaptcha_module" => '',
            "google_recaptcha_key" => '',
            "google_recaptcha_secret" => '',

            "company_logo_light" => "logo-light.png",
            "company_logo_dark" => "logo-dark.png",
            "light_logo" => "logo-light.png",
            "dark_logo" => "logo-dark.png",
            "favicon" => "favicon.png",
            "owner_signature" => "",
            "storage_setting" => "local",
            "local_storage_validation" => "jpeg,jpg,png",
            "local_storage_max_upload_size" => "520000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url" => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
            "google_clender_id" => "",
            "google_calender_json_file" => "",
            "is_googleCal_enabled" => "",
            "email_verificattion" => "on",
            "timezone"=>'',
            //cookie
            'enable_cookie' => 'on',
            'necessary_cookies' => 'on',
            'cookie_logging' => 'on',
            'cookie_title' => 'We use cookies!',
            'cookie_description' => 'Hi, this website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it',
            'strictly_cookie_title' => 'Strictly necessary cookies',
            'strictly_cookie_description' => 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
            'more_information_description' => 'For any queries in relation to our policy on cookies and your choices, please contact us',
            'contactus_url' => '#',
            'chatgpt',
            'chatgpt_model_name',
            'disable_lang' => '',

            // company mail
            'mail_driver' => '',
            'mail_host' => '',
            'mail_port' => '',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => '',
            'mail_from_address' => '',
            'mail_from_name' => '',

            "meta_title" => "",
            "meta_keywords" => "",
            "meta_image" => "",
            "meta_description" => "",
            "meta_desc" => "",
            "SITE_RTL"=>'off',

            //storage
            "storage_setting" => "local",
            "local_storage_validation" => "jpg,jpeg,png",
            "local_storage_max_upload_size" => "250000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url" => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",

        ];

        foreach (self::$settings_data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }


    public static function settingsById($id)
    {
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', $id);
        $data = $data->get();
        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "employee_prefix" => "#EMP",
            "client_prefix" => "#CLT",
            "estimate_prefix" => "#EST",
            "invoice_prefix" => "#INV",
            "contract_prefix" => "#CON",
            "company_name" => "",
            "company_address" => "",
            "company_city" => "",
            "company_state" => "",
            "company_zipcode" => "",
            "company_country" => "",
            "company_telephone" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "footer_title" => "",
            "footer_notes" => "",
            "decimal_number" => "2",
            "invoice_template" => "template1",
            "invoice_color" => "ffffff",
            "contract_template" => "template1",
            "contract_color" => "ffffff",
            "estimate_template" => "template1",
            "QR_code" => "off",
            "invoice_qrcode" => "off",
            "estimate_color" => "ffffff",
            "company_start_time" => "09:00",
            "company_end_time" => "18:00",
            "default_language" => "en",
            "enable_stripe" => "",
            "enable_paypal" => "",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "stripe_key" => "",
            "stripe_secret" => "",
            "registration_number" => "",
            "vat_number" => "",
            "footer_link_1" => "Support",
            "footer_value_1" => "#",
            "footer_link_2" => "Terms",
            "footer_value_2" => "#",
            "footer_link_3" => "Privacy",
            "footer_value_3" => "#",
            "display_landing_page" => "",
            "title_text" => "CRMGo SaaS",
            "footer_text" => "CRMGo SaaS",
            "journal_prefix" => "#JUR",
            "gdpr_cookie" => "",
            "cookie_text" => "",
            "cust_theme_bg" => "",
            "cust_darklayout" => "",
            "color" => "",
            "SITE_RTL" => 'off',
            "owner_signature" => "",
            "storage_setting" => "",
            "local_storage_validation" => "",
            "local_storage_max_upload_size" => "",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url" => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
            "chatgpt_model_name"=>"",

            'mail_driver' => '',
            'mail_host' => '',
            'mail_port' => '',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => '',
            'mail_from_address' => '',
            'mail_from_name' => '',
        ];

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function getStorageSetting()
    {

        $data = DB::table('settings');
        $data = $data->where('created_by', '=', 1);
        $data = $data->get();
        $settings = [
            "storage_setting" => "local",
            "local_storage_validation" => "jpg,jpeg,png",
            "local_storage_max_upload_size" => "250000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url" => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
        ];


        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function languages()
    {

        $languages = Utility::langList();

        if(self::$languages == null)
        {
            if (\Schema::hasTable('languages')) {
                $settings = Utility::settings();
                if (!empty($settings['disable_lang'])) {
                    $disabledlang = explode(',', $settings['disable_lang']);
                    $languages = Languages::whereNotIn('code', $disabledlang)->pluck('fullName', 'code');
                } else {
                    $languages = Languages::pluck('fullName', 'code');
                }
                self::$languages = $languages;
            }
        }else{
            $languages =  self::$languages;
        }

        return $languages;
    }


    public static function getValByName($key)
    {
        $setting = self::settings();
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }
        return $setting[$key];
    }

    public static function getName($key , $id=null)
    {
        $setting = Utility::settingsById($id);
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }

        return $setting[$key];
    }

    public static function setEnvironmentValue(array $values)
    {

        $envFile = app()->environmentFilePath();

        $str = file_get_contents($envFile);
        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}='{$envValue}'\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}='{$envValue}'", $str);
                }
            }
        }
        $str = substr($str, 0, -1);

        $str .= "\n";
        if (!file_put_contents($envFile, $str)) {

            return false;
        }

        return true;
    }

    public static function templateData()
    {
        $arr = [];
        $arr['colors'] = [
            '003580',
            '666666',
            'f2f6fa',
            'f50102',
            'f9b034',
            'fbdd03',
            'c1d82f',
            '37a4e4',
            '8a7966',
            '6a737b',
            '050f2c',
            '0e3666',
            '3baeff',
            '3368e6',
            'b84592',
            'f64f81',
            'f66c5f',
            'fac168',
            '46de98',
            '40c7d0',
            'be0028',
            '2f9f45',
            '371676',
            '52325d',
            '511378',
            '0f3866',
            '48c0b6',
            '297cc0',
            'ffffff',
            '000000',
        ];
        $arr['templates'] = [
            "template1" => "New York",
            "template2" => "Toronto",
            "template3" => "Rio",
            "template4" => "London",
            "template5" => "Istanbul",
            "template6" => "Mumbai",
            "template7" => "Hong Kong",
            "template8" => "Tokyo",
            "template9" => "Sydney",
            "template10" => "Paris",
        ];

        return $arr;
    }

    public static function priceFormat($settings, $price)
    {
        return (($settings['site_currency_symbol_position'] == "pre") ? $settings['site_currency_symbol'] : '') . number_format($price, 2) . (($settings['site_currency_symbol_position'] == "post") ? $settings['site_currency_symbol'] : '');
    }

    public static function currencySymbol($settings)
    {
        return $settings['site_currency_symbol'];
    }

    public static function dateFormat($settings, $date)
    {
        return date($settings['site_date_format'], strtotime($date));
    }

    public static function timeFormat($settings, $time)
    {
        return date($settings['site_time_format'], strtotime($time));
    }

    public static function invoiceNumberFormat($settings, $number)
    {
        $settings = self::settings();
        return $settings["invoice_prefix"] . sprintf("%05d", $number);
    }

    public static function contractNumberFormat($number)
    {

        $settings = self::settings();
        return $settings["contract_prefix"] . sprintf("%05d", $number);
    }

    public static function estimateNumberFormat($settings, $number)
    {
        return $settings["estimate_prefix"] . sprintf("%05d", $number);
    }

    public static function proposalNumberFormat($settings, $number)
    {
        return $settings["proposal_prefix"] . sprintf("%05d", $number);
    }

    public static function billNumberFormat($settings, $number)
    {
        return $settings["bill_prefix"] . sprintf("%05d", $number);
    }

    public static $rates;
    public static $data;

    public static function getTaxData()
    {
        $data = [];
        if(self::$rates == null)
        {
            $rates          =  TaxRate::get();
            self::$rates    =  $rates;
            foreach(self::$rates as $rate)
            {
                $data[$rate->id]['name']        = $rate->name;
                $data[$rate->id]['rate']        = $rate->rate;
                $data[$rate->id]['created_by']  = $rate->created_by;
            }
            self::$data    =  $data;
        }
        return self::$data;
    }

    public static function tax($taxes)
    {
        $taxArr = explode(',', $taxes);
        $taxes = [];
        foreach ($taxArr as $tax) {
            $taxes[] = TaxRate::find($tax);
        }

        return $taxes;
    }

    public static function totalTaxRate($taxes)
    {
        $taxArr = explode(',', $taxes);
        $taxRate = 0;
        foreach ($taxArr as $tax) {
            $tax = TaxRate::find($tax);
            $taxRate += !empty($tax->rate) ? $tax->rate : 0;
        }

        return $taxRate;
    }

    public static function taxRate($taxRate, $price, $quantity)
    {
        return ($taxRate / 100) * ($price * $quantity);
    }

    // get font-color code accourding to bg-color
    public static function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        $rgb = array(
            $r,
            $g,
            $b,
        );

        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }

    public static function getFontColor($color_code)
    {
        $rgb = self::hex2rgb($color_code);
        $R = $G = $B = $C = $L = $color = '';

        $R = (floor($rgb[0]));
        $G = (floor($rgb[1]));
        $B = (floor($rgb[2]));

        $C = [
            $R / 255,
            $G / 255,
            $B / 255,
        ];

        for ($i = 0; $i < count($C); ++$i) {
            if ($C[$i] <= 0.03928) {
                $C[$i] = $C[$i] / 12.92;
            } else {
                $C[$i] = pow(($C[$i] + 0.055) / 1.055, 2.4);
            }
        }

        $L = 0.2126 * $C[0] + 0.7152 * $C[1] + 0.0722 * $C[2];

        if ($L > 0.179) {
            $color = 'black';
        } else {
            $color = 'white';
        }

        return $color;
    }

    public static function delete_directory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!self::delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    public static function get_messenger_packages_migration()
    {
        $totalMigration = 0;
        $messengerPath  = glob(base_path() . '/vendor/munafio/chatify/src/database/migrations' . DIRECTORY_SEPARATOR . '*.php');
        if (!empty($messengerPath)) {
            $messengerMigration = str_replace('.php', '', $messengerPath);
            $totalMigration     = count($messengerMigration);
        }

        return $totalMigration;
    }

    // Email Template Modules Function START
    // Common Function That used to send mail with check all cases


    public static function sendEmailTemplate($emailTemplate, $mailTo, $obj)
    {
        $usr         =       \Auth::user();
        //Remove Current Login user Email don't send mail to them
        if ($usr) {
            if (is_array($mailTo)) {
                unset($mailTo[$usr->id]);
                $mailTo = array_values($mailTo);
            }
        }
        // find template is exist or not in our record
        $template       =        EmailTemplate::where('slug', $emailTemplate)->first();
        if (isset($template) && !empty($template)) {
            // check template is active or not by company
            $is_active = UserEmailTemplate::where('template_id', '=', $template->id)->first();
            if ($is_active->is_active == 1) {
                $settings = self::settings();
                if(\Auth::check()){
                    $setting = self::getSMTPDetails(\Auth::user()->creatorId());
                }
                else {
                    $setting = self::getSMTPDetails(1);
                }

                $isAllEmpty = empty($setting['mail_driver']);

                if($isAllEmpty == 'true'){
                    $setting = self::getSMTPDetails(1);
                }

                // get email content language base
                if ($usr) {
                    $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', $usr->lang)->first();
                } else {
                    $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', 'en')->first();
                }
                $content['from'] = $template->from;
                if (!empty($content->content)) {
                    $content->content = self::replaceVariable($content->content, $obj);
                    // send email
                    $authUser = \Auth::user();
                    try {

                        if(isset($authUser) && \Auth::user()->type == 'company')
                        {
                            config([
                                'mail.driver'           =>  $settings['mail_driver'] ? $settings['mail_driver'] : $setting['mail_driver'],
                                'mail.host'             =>  $settings['mail_host'] ? $settings['mail_host'] : $setting['mail_host'],
                                'mail.port'             =>  $settings['mail_port'] ? $settings['mail_port'] : $setting['mail_port'],
                                'mail.username'         =>  $settings['mail_username'] ? $settings['mail_username'] :$setting['mail_username'],
                                'mail.password'         =>  $settings['mail_password'] ? $settings['mail_password'] : $setting['mail_password'] ,
                                'mail.encryption'       =>  $settings['mail_encryption'] ? $settings['mail_encryption'] : $setting['mail_encryption'],
                                'mail.from.address'     =>  $settings['mail_from_address'] ? $settings['mail_from_address'] : $setting['mail_from_address'],
                                'mail.from.name'        =>  $settings['mail_from_name'] ? $settings['mail_from_name'] : $setting['mail_from_name'],
                            ]);
                        }
                        // else
                        // {
                        //     config([
                        //         'mail.driver'           =>  $settings['mail_driver'],
                        //         'mail.host'             =>  $settings['mail_host'],
                        //         'mail.port'             =>  $settings['mail_port'],
                        //         'mail.username'         =>  $settings['mail_username'],
                        //         'mail.password'         =>  $settings['mail_password'],
                        //         'mail.encryption'       =>  $settings['mail_encryption'],
                        //         'mail.from.address'     =>  $settings['mail_from_address'],
                        //         'mail.from.name'        =>  $settings['mail_from_name'],
                        //     ]);
                        // }
                        Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings));
                    } catch (\Exception $e) {
                        $error = __('E-Mail has been not sent due to SMTP configuration');
                    }
                    if (isset($error)) {
                        $arReturn = [
                            'is_success' => false,
                            'error' => $error,
                        ];
                    } else {
                        $arReturn = [
                            'is_success' => true,
                            'error' => false,
                        ];
                    }
                } else {
                    $arReturn = [
                        'is_success' => false,
                        'error' => __('Mail not send, email is empty'),
                    ];
                }
                return $arReturn;
            } else {
                return [
                    'is_success' => true,
                    'error' => false,
                ];
            }
        }
    }


    // used for replace email variable (parameter 'template_name','id(get particular record by id for data)')
    public static function replaceVariable($content, $obj)
    {
        $arrVariable = [
            '{user_name}',
            '{lead_name}',
            '{lead_email}',
            '{lead_subject}',
            '{lead_pipeline}',
            '{lead_stage}',
            '{deal_name}',
            '{deal_pipeline}',
            '{deal_stage}',
            '{deal_status}',
            '{deal_price}',
            '{estimation_id}',
            '{estimation_client}',
            '{estimation_category}',
            '{estimation_issue_date}',
            '{estimation_expiry_date}',
            '{estimation_status}',
            '{project_title}',
            '{project_category}',
            '{project_price}',
            '{project_client}',
            '{project_assign_user}',
            '{project_start_date}',
            '{project_due_date}',
            '{project_lead}',
            '{project}',
            '{task_title}',
            '{task_stage}',
            '{task_priority}',
            '{task_start_date}',
            '{task_due_date}',
            '{task_assign_user}',
            '{task_description}',
            '{invoice_id}',
            '{invoice_client}',
            '{invoice_issue_date}',
            '{invoice_due_date}',
            '{invoice_status}',
            '{invoice_total}',
            '{invoice_sub_total}',
            '{invoice_due_amount}',
            '{payment_total}',
            '{payment_date}',
            '{credit_note_date}',
            '{credit_amount}',
            '{credit_description}',
            '{support_title}',
            '{assign_user}',
            '{support_priority}',
            '{support_end_date}',
            '{support_description}',
            '{contract_subject}',
            '{contract_client}',
            '{contract_start_date}',
            '{contract_end_date}',
            '{app_name}',
            '{company_name}',
            '{app_url}',
            '{email}',
            '{password}',
            '{contract_value}',
            '{date}',
            '{occasion}',
            '{title}',
            '{event_title}',
            '{department_name}',
            '{start_date}',
            '{end_date}',
            '{task_name}',
            '{project_name}',
            '{cost}',
            '{due_date}',
            '{support_user_name}',
            '{company_policy_name}',
            '{award_name}',
            '{award_date}',
            '{employee_name}',
            '{invoice_number}',
            '{invoice}',
            '{old_status}',
            '{status}',
            '{amount}',
            '{created_by}',
            '{purpose_of_visit}',
            '{place_of_visit}',
            '{estimate}',
            '{item}',
            '{price}',
            '{new_task_stage}',
        ];
        $arrValue = [
            'user_name' => '-',
            'lead_name' => '-',
            'lead_email' => '-',
            'lead_subject' => '-',
            'lead_pipeline' => '-',
            'lead_stage' => '-',
            'deal_name' => '-',
            'deal_pipeline' => '-',
            'deal_stage' => '-',
            'deal_status' => '-',
            'deal_price' => '-',
            'estimation_id' => '-',
            'estimation_client' => '-',
            'estimation_category' => '-',
            'estimation_issue_date' => '-',
            'estimation_expiry_date' => '-',
            'estimation_status' => '-',
            'project_title' => '-',
            'project_category' => '-',
            'project_price' => '-',
            'project_client' => '-',
            'project_assign_user' => '-',
            'project_start_date' => '-',
            'project_due_date' => '-',
            'project_lead' => '-',
            'project' => '-',
            'task_title' => '-',
            'task_priority' => '-',
            'task_start_date' => '-',
            'task_due_date' => '-',
            'task_stage' => '-',
            'task_assign_user' => '-',
            'task_description' => '-',
            'invoice_id' => '-',
            'invoice_client' => '-',
            'invoice_issue_date' => '-',
            'invoice_due_date' => '-',
            'invoice_status' => '-',
            'invoice_total' => '-',
            'invoice_sub_total' => '-',
            'invoice_due_amount' => '-',
            'payment_total' => '-',
            'payment_date' => '-',
            'credit_note_date' => '-',
            'credit_amount' => '-',
            'credit_description' => '-',
            'support_title' => '-',
            'assign_user' => '-',
            'support_priority' => '-',
            'support_end_date' => '-',
            'support_description' => '-',
            'contract_subject' => '-',
            'contract_client' => '-',
            'contract_start_date' => '-',
            'contract_end_date' => '-',
            'app_name' => '-',
            'company_name' => '-',
            'app_url' => '-',
            'email' => '-',
            'password' => '-',
            'contract_value' => '-',
            'date' => '-',
            'occasion' => '-',
            'title' => '-',
            'event_title' => '-',
            'department_name' => '-',
            'start_date' => '-',
            'end_date' => '-',
            'task_name' => '-',
            'project_name' => '-',
            'cost' => '-',
            'due_date' => '-',
            'support_user_name' => '-',
            'company_policy_name' => '-',
            'award_name' => '-',
            'award_date' => '-',
            'employee_name' => '-',
            'invoice_number' => '-',
            'invoice' => '-',
            'old_status' => '-',
            'status' => '-',
            'amount' => '-',
            'created_by' => '-',
            'purpose_of_visit' => '-',
            'place_of_visit' => '-',
            'estimate' => '-',
            'item' => '-',
            'price' => '-',
            'new_task_stage' => '-',
        ];

        foreach ($obj as $key => $val) {
            $arrValue[$key] = $val;
        }

        $settings = Utility::settings();
        $company_name = $settings['company_name'];

        $arrValue['app_name'] = $company_name;
        $arrValue['company_name'] = self::settings()['company_name'];
        $arrValue['app_url'] = '<a href="' . env('APP_URL') . '" target="_blank">' . env('APP_URL') . '</a>';
        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    // Make Entry in email_tempalte_lang table when create new language
    public static function makeEmailLang($lang)
    {
        $template = EmailTemplate::all();
        foreach ($template as $t) {
            $default_lang = EmailTemplateLang::where('parent_id', '=', $t->id)->where('lang', 'LIKE', 'en')->first();
            $emailTemplateLang = new EmailTemplateLang();
            $emailTemplateLang->parent_id = $t->id;
            $emailTemplateLang->lang = $lang;
            $emailTemplateLang->subject = $default_lang->subject;
            $emailTemplateLang->content = $default_lang->content;
            $emailTemplateLang->save();
        }
    }
    // Email Template Modules Function END

    public static function getSuperAdminValByName($key)
    {
        $data = DB::table('settings');
        $data = $data->where('name', '=', $key)->where('created_by', '=', 1);
        $data = $data->first();

        if (!empty($data)) {
            $record = $data->value;
        } else {
            $record = '';
        }

        return $record;
    }

    public static function getAdminCurrency()
    {
        $data = DB::table('admin_payment_settings');
        $data = $data->where('name', '=', 'currency')->where('created_by', '=', 1);
        $data = $data->first();

        if (!empty($data)) {
            $record = $data->value;
        } else {
            $record = 'USD';
        }

        return $record;
    }

    public static function payment_settings()
    {
        $data = DB::table('admin_payment_settings');
        $data->where('created_by', '=', 1);
        $data = $data->get();
        $res = [];
        foreach ($data as $key => $value) {
            $res[$value->name] = $value->value;
        }
        return $res;
    }

    public static function getAdminPaymentSetting()
    {
        $data = \DB::table('admin_payment_settings');
        $settings = [];
        if (\Auth::check()) {
            $user_id = 1;
            $data = $data->where('created_by', '=', $user_id);
        }
        $data = $data->get();
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function set_payment_settings()
    {
        $data = \DB::table('company_payment_settings');

        if (\Auth::check()) {
            $data->where('created_by', '=', \Auth::user()->creatorId());
        } else {
            $data->where('created_by', '=', 1);
        }
        $data = $data->get();
        $res = [];
        foreach ($data as $key => $value) {
            $res[$value->name] = $value->value;
        }

        return $res;
    }

    //========project copylink module Start======
    public static function getcompanySettings($projectID)
    {
        $data = Project::where('id', $projectID)->first();
        // dd($data);
        return $data;
    }
    public static function get_logo()
    {
        $setting = Utility::getAdminPaymentSettings();

        if ($setting['cust_darklayout'] == 'on') {
            return 'logo-dark.png';
        } else {
            return 'logo-light.png';
        }
    }
    public function extraKeyword()
    {
        $keyArr = [
            __('Event Title'),
            __('Department Name'),
            __('Support Priority'),
            __('Support User Name'),
            __('Company Policy Name'),
            __('Award Name'),
            __('Award Date'),
            __('Old status'),
            __('New Status'),
            __('New Task Stages'),
            __('Old Task Stages'),
            __('Contract Name'),
            __('Contract Price'),
            __('Purpose Of Visit'),
            __('New Holiday'),
            __('New Meeting'),
        ];
    }
    public static function getAdminPaymentSettings()
    {
        $data = DB::table('admin_payment_settings');
        $adminSettings = [
            'cust_theme_bg' => 'on',
            'cust_darklayout' => 'off',
            'color' => 'theme-3',

        ];

        $data = $data->get();
        foreach ($data as $row) {
            $adminSettings[$row->name] = $row->value;
        }

        return $adminSettings;
    }

    public static function getCompanyPaymentSetting()
    {
        $data = \DB::table('company_payment_settings');
        $settings = [];
        if (\Auth::check()) {
            $user_id = \Auth::user()->creatorId();
            $data = $data->where('created_by', '=', $user_id);
        }
        // dd($data);
        $data = $data->get();
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }
    public static function getLayoutsSetting()
    {
        $data = DB::table('settings');

        if (\Auth::check()) {
            $data = $data->where('created_by', '=', \Auth::user()->creatorId())->get();

            if (count($data) == 0) {
                $data = DB::table('settings')->where('created_by', '=', 1)->get();
            }
        } else {
            $data = $data->where('created_by', '=', 1)->get();
        }

        $settings = [
            "is_sidebar_transperent" => "on",
            "dark_mode" => "off",
            "cust_darklayout" => "off",

            "theme_color" => "theme-3",
            "SITE_RTL" => "off",
            "company_logo_light" => "",
            "company_logo" => "",
            "company_favicon" => "",
        ];

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }
    //========project copylink module end======

    public static function getCompanyPaymentSettingWithOutAuth($user_id)
    {

        $data = \DB::table('company_payment_settings');
        $settings = [];
        $data = $data->where('created_by', '=', $user_id);
        $data = $data->get();
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function error_res($msg = "", $args = array())
    {
        $msg = $msg == "" ? "error" : $msg;
        $msg_id = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array(
            'flag' => 0,
            'msg' => $msg,
        );

        return $json;
    }

    public static function success_res($msg = "", $args = array())
    {
        $msg = $msg == "" ? "success" : $msg;
        $msg_id = 'success.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array(
            'flag' => 1,
            'msg' => $msg,
        );

        return $json;
    }

    public static $chartOfAccountType = [
        'assets' => 'Assets',
        'liabilities' => 'Liabilities',
        'equity' => 'Equity',
        'income' => 'Income',
        'costs of goods sold' => 'Costs of Goods Sold',
        'expenses' => 'Expenses',

    ];

    public static $chartOfAccountSubType = array(
        "assets" => array(
            '1' => 'Current Asset',
            '2' => 'Inventory Asset',
            '3' => 'Non-current Asset',
        ),
        "liabilities" => array(
            '1' => 'Current Liabilities',
            '2' => 'Long Term Liabilities',
            '3' => 'Share Capital',
            '4' => 'Retained Earnings',
        ),
        "equity" => array(
            '1' => 'Owners Equity',
        ),
        "income" => array(
            '1' => 'Sales Revenue',
            '2' => 'Other Revenue',
        ),
        "costs of goods sold" => array(
            '1' => 'Costs of Goods Sold',
        ),
        "expenses" => array(
            '1' => 'Payroll Expenses',
            '2' => 'General and Administrative expenses',
        ),

    );

    public static function chartOfAccount($type, $subType)
    {
        $accounts =
            [
                "Assets" => array(
                    'Current Asset' => array(
                        [
                            'code' => '1060',
                            'name' => 'Checking Account',
                        ],
                        [
                            'code' => '1065',
                            'name' => 'Petty Cash',
                        ],
                        [
                            'code' => '1200',
                            'name' => 'Account Receivables',
                        ],
                        [
                            'code' => '1205',
                            'name' => 'Allowance for doubtful accounts',
                        ],
                    ),
                    'Inventory Asset' => array(
                        [
                            'code' => '1510',
                            'name' => 'Inventory',
                        ],
                        [
                            'code' => '1520',
                            'name' => 'Stock of Raw Materials',
                        ],
                        [
                            'code' => '1530',
                            'name' => 'Stock of Work In Progress',
                        ],
                        [
                            'code' => '1540',
                            'name' => 'Stock of Finished Goods',
                        ],
                        [
                            'code' => '1550',
                            'name' => 'Goods Received Clearing account',
                        ],

                    ),
                    'Non-current Asset' => array(
                        [
                            'code' => '1810',
                            'name' => 'Land and Buildings',
                        ],
                        [
                            'code' => '1820',
                            'name' => 'Office Furniture and Equipement',
                        ],
                        [
                            'code' => '1825',
                            'name' => 'Accum.depreciation-Furn. and Equip',
                        ],
                        [
                            'code' => '1840',
                            'name' => 'Motor Vehicle',
                        ],
                        [
                            'code' => '1845',
                            'name' => 'Accum.depreciation-Motor Vehicle',
                        ],

                    )
                ),
                "Liabilities" => array(
                    'Current Liabilities' => array(
                        [
                            'code' => '2100',
                            'name' => 'Account Payable',
                        ],
                        [
                            'code' => '2105',
                            'name' => 'Deferred Income',
                        ],
                        [
                            'code' => '2110',
                            'name' => 'Accrued Income Tax-Central',
                        ],
                        [
                            'code' => '2120',
                            'name' => 'Income Tax Payable',
                        ],
                        [
                            'code' => '2130',
                            'name' => 'Accrued Franchise Tax',
                        ],
                        [
                            'code' => '2140',
                            'name' => 'Vat Provision',
                        ],
                        [
                            'code' => '2145',
                            'name' => 'Purchase Tax',
                        ],
                        [
                            'code' => '2150',
                            'name' => 'VAT Pay / Refund',
                        ],
                        [
                            'code' => '2151',
                            'name' => 'Zero Rated',
                        ],
                        [
                            'code' => '2152',
                            'name' => 'Capital import',
                        ],
                        [
                            'code' => '2153',
                            'name' => 'Standard Import',
                        ],
                        [
                            'code' => '2154',
                            'name' => 'Capital Standard',
                        ],
                        [
                            'code' => '2155',
                            'name' => 'Vat Exempt',
                        ],
                        [
                            'code' => '2160',
                            'name' => 'Accrued Use Tax Payable',
                        ],
                        [
                            'code' => '2210',
                            'name' => 'Accrued Wages',
                        ],
                        [
                            'code' => '2220',
                            'name' => 'Accrued Comp Time',
                        ],
                        [
                            'code' => '2230',
                            'name' => 'Accrued Holiday Pay',
                        ],
                        [
                            'code' => '2240',
                            'name' => 'Accrued Vacation Pay',
                        ],
                        [
                            'code' => '2310',
                            'name' => 'Accr. Benefits - Central Provident Fund',
                        ],
                        [
                            'code' => '2320',
                            'name' => 'Accr. Benefits - Stock Purchase',
                        ],
                        [
                            'code' => '2330',
                            'name' => 'Accr. Benefits - Med, Den',
                        ],
                        [
                            'code' => '2340',
                            'name' => 'Accr. Benefits - Payroll Taxes',
                        ],
                        [
                            'code' => '2350',
                            'name' => 'Accr. Benefits - Credit Union',
                        ],
                        [
                            'code' => '2360',
                            'name' => 'Accr. Benefits - Savings Bond',
                        ],
                        [
                            'code' => '2370',
                            'name' => 'Accr. Benefits - Group Insurance',
                        ],
                        [
                            'code' => '2380',
                            'name' => 'Accr. Benefits - Charity Cont.',
                        ],
                    ),
                    'Long Term Liabilities' => array(
                        [
                            'code' => '2620',
                            'name' => 'Bank Loans',
                        ],
                        [
                            'code' => '2680',
                            'name' => 'Loans from Shareholders',
                        ],
                    ),
                    'Share Capital' => array(
                        [
                            'code' => '3350',
                            'name' => 'Common Shares',
                        ],
                    ),
                    'Retained Earnings' => array(
                        [
                            'code' => '3590',
                            'name' => 'Reserves and Surplus',
                        ],
                        [
                            'code' => '3595',
                            'name' => 'Owners Drawings',
                        ],
                    ),
                ),
                "Equity" => array(
                    'Owners Equity' => array(
                        [
                            'code' => '3020',
                            'name' => 'Opening Balances and adjustments',
                        ],
                        [
                            'code' => '3025',
                            'name' => 'Owners Contribution',
                        ],
                        [
                            'code' => '3030',
                            'name' => 'Profit and Loss ( current Year)',
                        ],
                        [
                            'code' => '3035',
                            'name' => 'Retained income',
                        ],
                    ),
                ),
                "Income" => array(
                    'Sales Revenue' => array(
                        [
                            'code' => '4010',
                            'name' => 'Sales Income',
                        ],
                        [
                            'code' => '4020',
                            'name' => 'Service Income',
                        ],
                    ),
                    'Other Revenue' => array(
                        [
                            'code' => '4430',
                            'name' => 'Shipping and Handling',
                        ],
                        [
                            'code' => '4435',
                            'name' => 'Sundry Income',
                        ],
                        [
                            'code' => '4440',
                            'name' => 'Interest Received',
                        ],
                        [
                            'code' => '4450',
                            'name' => 'Foreign Exchange Gain',
                        ],
                        [
                            'code' => '4500',
                            'name' => 'Unallocated Income',
                        ],
                        [
                            'code' => '4510',
                            'name' => 'Discounts Received',
                        ],
                    ),
                ),
                "Costs of Goods Sold" => array(
                    'Costs of Goods Sold' => array(
                        [
                            'code' => '5005',
                            'name' => 'Cost of Sales- On Services',
                        ],
                        [
                            'code' => '5010',
                            'name' => 'Cost of Sales - Purchases',
                        ],
                        [
                            'code' => '5015',
                            'name' => 'Operating Costs',
                        ],
                        [
                            'code' => '5020',
                            'name' => 'Material Usage Varaiance',
                        ],
                        [
                            'code' => '5025',
                            'name' => 'Breakage and Replacement Costs',
                        ],
                        [
                            'code' => '5030',
                            'name' => 'Consumable Materials',
                        ],
                        [
                            'code' => '5035',
                            'name' => 'Sub-contractor Costs',
                        ],
                        [
                            'code' => '5040',
                            'name' => 'Purchase Price Variance',
                        ],
                        [
                            'code' => '5045',
                            'name' => 'Direct Labour - COS',
                        ],
                        [
                            'code' => '5050',
                            'name' => 'Purchases of Materials',
                        ],
                        [
                            'code' => '5060',
                            'name' => 'Discounts Received',
                        ],
                        [
                            'code' => '5100',
                            'name' => 'Freight Costs',
                        ],
                    ),
                ),
                "Expenses" => array(
                    'Payroll Expenses' => array(
                        [
                            'code' => '5410',
                            'name' => 'Salaries and Wages',
                        ],
                        [
                            'code' => '5415',
                            'name' => 'Directors Fees & Remuneration',
                        ],
                        [
                            'code' => '5420',
                            'name' => 'Wages - Overtime',
                        ],
                        [
                            'code' => '5425',
                            'name' => 'Members Salaries',
                        ],
                        [
                            'code' => '5430',
                            'name' => 'UIF Payments',
                        ],
                        [
                            'code' => '5440',
                            'name' => 'Payroll Taxes',
                        ],
                        [
                            'code' => '5450',
                            'name' => 'Workers Compensation ( Coida )',
                        ],
                        [
                            'code' => '5460',
                            'name' => 'Normal Taxation Paid',
                        ],
                        [
                            'code' => '5470',
                            'name' => 'General Benefits',
                        ],
                        [
                            'code' => '5510',
                            'name' => 'Provisional Tax Paid',
                        ],
                        [
                            'code' => '5520',
                            'name' => 'Inc Tax Exp - State',
                        ],
                        [
                            'code' => '5530',
                            'name' => 'Taxes - Real Estate',
                        ],
                        [
                            'code' => '5540',
                            'name' => 'Taxes - Personal Property',
                        ],
                        [
                            'code' => '5550',
                            'name' => 'Taxes - Franchise',
                        ],
                        [
                            'code' => '5560',
                            'name' => 'Taxes - Foreign Withholding',
                        ],
                    ),
                    'General and Administrative expenses' => array(
                        [
                            'code' => '5610',
                            'name' => 'Accounting Fees',
                        ],
                        [
                            'code' => '5615',
                            'name' => 'Advertising and Promotions',
                        ],
                        [
                            'code' => '5620',
                            'name' => 'Bad Debts',
                        ],
                        [
                            'code' => '5625',
                            'name' => 'Courier and Postage',
                        ],
                        [
                            'code' => '5660',
                            'name' => 'Depreciation Expense',
                        ],
                        [
                            'code' => '5685',
                            'name' => 'Insurance Expense',
                        ],
                        [
                            'code' => '5690',
                            'name' => 'Bank Charges',
                        ],
                        [
                            'code' => '5695',
                            'name' => 'Interest Paid',
                        ],
                        [
                            'code' => '5700',
                            'name' => 'Office Expenses - Consumables',
                        ],
                        [
                            'code' => '5705',
                            'name' => 'Printing and Stationary',
                        ],
                        [
                            'code' => '5710',
                            'name' => 'Security Expenses',
                        ],
                        [
                            'code' => '5715',
                            'name' => 'Subscription - Membership Fees',
                        ],
                        [
                            'code' => '5755',
                            'name' => 'Electricity, Gas and Water',
                        ],
                        [
                            'code' => '5760',
                            'name' => 'Rent Paid',
                        ],
                        [
                            'code' => '5765',
                            'name' => 'Repairs and Maintenance',
                        ],
                        [
                            'code' => '5770',
                            'name' => 'Motor Vehicle Expenses',
                        ],
                        [
                            'code' => '5771',
                            'name' => 'Petrol and Oil',
                        ],
                        [
                            'code' => '5775',
                            'name' => 'Equipment Hire - Rental',
                        ],
                        [
                            'code' => '5780',
                            'name' => 'Telephone and Internet',
                        ],
                        [
                            'code' => '5785',
                            'name' => 'Travel and Accommodation',
                        ],
                        [
                            'code' => '5786',
                            'name' => 'Meals and Entertainment',
                        ],
                        [
                            'code' => '5787',
                            'name' => 'Staff Training',
                        ],
                        [
                            'code' => '5790',
                            'name' => 'Utilities',
                        ],
                        [
                            'code' => '5791',
                            'name' => 'Computer Expenses',
                        ],
                        [
                            'code' => '5795',
                            'name' => 'Registrations',
                        ],
                        [
                            'code' => '5800',
                            'name' => 'Licenses',
                        ],
                        [
                            'code' => '5810',
                            'name' => 'Foreign Exchange Loss',
                        ],
                        [
                            'code' => '9990',
                            'name' => 'Profit and Loss',
                        ],
                    ),
                ),
            ];
        return $accounts[$type][$subType];
    }

    // public static $chartOfAccount = array(

    //     [
    //         'code' => '120',
    //         'name' => 'Accounts Receivable',
    //         'type' => 1,
    //         'sub_type' => 1,
    //     ],
    //     [
    //         'code' => '160',
    //         'name' => 'Computer Equipment',
    //         'type' => 1,
    //         'sub_type' => 2,
    //     ],
    //     [
    //         'code' => '150',
    //         'name' => 'Office Equipment',
    //         'type' => 1,
    //         'sub_type' => 2,
    //     ],
    //     [
    //         'code' => '140',
    //         'name' => 'Inventory',
    //         'type' => 1,
    //         'sub_type' => 3,
    //     ],
    //     [
    //         'code' => '857',
    //         'name' => 'Budget - Finance Staff',
    //         'type' => 1,
    //         'sub_type' => 6,
    //     ],
    //     [
    //         'code' => '170',
    //         'name' => 'Accumulated Depreciation',
    //         'type' => 1,
    //         'sub_type' => 7,
    //     ],
    //     [
    //         'code' => '200',
    //         'name' => 'Accounts Payable',
    //         'type' => 2,
    //         'sub_type' => 8,
    //     ],
    //     [
    //         'code' => '205',
    //         'name' => 'Accruals',
    //         'type' => 2,
    //         'sub_type' => 8,
    //     ],
    //     [
    //         'code' => '150',
    //         'name' => 'Office Equipment',
    //         'type' => 2,
    //         'sub_type' => 8,
    //     ],
    //     [
    //         'code' => '855',
    //         'name' => 'Clearing Account',
    //         'type' => 2,
    //         'sub_type' => 8,
    //     ],
    //     [
    //         'code' => '235',
    //         'name' => 'Employee Benefits Payable',
    //         'type' => 2,
    //         'sub_type' => 8,
    //     ],
    //     [
    //         'code' => '236',
    //         'name' => 'Employee Deductions payable',
    //         'type' => 2,
    //         'sub_type' => 8,
    //     ],
    //     [
    //         'code' => '255',
    //         'name' => 'Historical Adjustments',
    //         'type' => 2,
    //         'sub_type' => 8,
    //     ],
    //     [
    //         'code' => '835',
    //         'name' => 'Revenue Received in Advance',
    //         'type' => 2,
    //         'sub_type' => 8,
    //     ],
    //     [
    //         'code' => '260',
    //         'name' => 'Rounding',
    //         'type' => 2,
    //         'sub_type' => 8,
    //     ],
    //     [
    //         'code' => '500',
    //         'name' => 'Costs of Goods Sold',
    //         'type' => 3,
    //         'sub_type' => 11,
    //     ],
    //     [
    //         'code' => '600',
    //         'name' => 'Advertising',
    //         'type' => 3,
    //         'sub_type' => 12,
    //     ],
    //     [
    //         'code' => '644',
    //         'name' => 'Automobile Expenses',
    //         'type' => 3,
    //         'sub_type' => 12,
    //     ],
    //     [
    //         'code' => '684',
    //         'name' => 'Bad Debts',
    //         'type' => 3,
    //         'sub_type' => 12,
    //     ],
    //     [
    //         'code' => '810',
    //         'name' => 'Bank Revaluations',
    //         'type' => 3,
    //         'sub_type' => 12,
    //     ],
    //     [
    //         'code' => '605',
    //         'name' => 'Bank Service Charges',
    //         'type' => 3,
    //         'sub_type' => 12,
    //     ],
    //     [
    //         'code' => '615',
    //         'name' => 'Consulting & Accounting',
    //         'type' => 3,
    //         'sub_type' => 12,
    //     ],
    //     [
    //         'code' => '700',
    //         'name' => 'Depreciation',
    //         'type' => 3,
    //         'sub_type' => 12,
    //     ],
    //     [
    //         'code' => '628',
    //         'name' => 'General Expenses',
    //         'type' => 3,
    //         'sub_type' => 12,
    //     ],
    //     [
    //         'code' => '460',
    //         'name' => 'Interest Income',
    //         'type' => 4,
    //         'sub_type' => 13,
    //     ],
    //     [
    //         'code' => '470',
    //         'name' => 'Other Revenue',
    //         'type' => 4,
    //         'sub_type' => 13,
    //     ],
    //     [
    //         'code' => '475',
    //         'name' => 'Purchase Discount',
    //         'type' => 4,
    //         'sub_type' => 13,
    //     ],
    //     [
    //         'code' => '400',
    //         'name' => 'Sales',
    //         'type' => 4,
    //         'sub_type' => 13,
    //     ],
    //     [
    //         'code' => '330',
    //         'name' => 'Common Stock',
    //         'type' => 5,
    //         'sub_type' => 16,
    //     ],
    //     [
    //         'code' => '300',
    //         'name' => 'Owners Contribution',
    //         'type' => 5,
    //         'sub_type' => 16,
    //     ],
    //     [
    //         'code' => '310',
    //         'name' => 'Owners Draw',
    //         'type' => 5,
    //         'sub_type' => 16,
    //     ],
    //     [
    //         'code' => '320',
    //         'name' => 'Retained Earnings',
    //         'type' => 5,
    //         'sub_type' => 16,
    //     ],
    // );





    // public static function chartOfAccountTypeData($company_id)
    // {
    //     $chartOfAccountTypes = Self::$chartOfAccountType;
    //     foreach ($chartOfAccountTypes as $k => $type) {

    //         $accountType = ChartOfAccountType::create(
    //             [
    //                 'name' => $type,
    //                 'created_by' => $company_id,
    //             ]
    //         );

    //         $chartOfAccountSubTypes = Self::$chartOfAccountSubType;

    //         foreach ($chartOfAccountSubTypes[$k] as $subType) {
    //             ChartOfAccountSubType::create(
    //                 [
    //                     'name' => $subType,
    //                     'type' => $accountType->id,
    //                 ]
    //             );
    //         }
    //     }
    // }


    // public static function chartOfAccountData($user)
    // {
    //     $chartOfAccounts = Self::$chartOfAccount;
    //     foreach ($chartOfAccounts as $account) {
    //         ChartOfAccount::create(
    //             [
    //                 'code' => $account['code'],
    //                 'name' => $account['name'],
    //                 'type' => $account['type'],
    //                 'sub_type' => $account['sub_type'],
    //                 'is_enabled' => 1,
    //                 'created_by' => $user->id,
    //             ]
    //         );
    //     }
    // }

    public static function defaultChartAccountdata($company_id = null)
    {
        // dd($company_id);


        if ($company_id == Null) {

            $companys = User::where('type', 'company')->get();

            foreach ($companys as $company) {
                    $chartOfAccountTypes = Self::$chartOfAccountType;
                    foreach ($chartOfAccountTypes as $k => $type) {
                        //when ChartOfAccountType data empty
                        $check_type = ChartOfAccountType::where('created_by', $company->id)->where('name', $type)->first();
                        if (empty($check_type)) {
                            $accountType = ChartOfAccountType::create(
                                [
                                    'name' => $type,
                                    'created_by' => $company->id,
                                ]
                            );

                            //when ChartOfAccountSubType data empty
                            $chartOfAccountSubTypes = Self::$chartOfAccountSubType;
                            foreach ($chartOfAccountSubTypes[$k] as $subType) {
                                $check_subtype = ChartOfAccountSubType::where('type', $accountType->id)->where('name', $subType)->first();
                                if (empty($check_subtype)) {
                                    $accountSubType = ChartOfAccountSubType::create(
                                        [
                                            'name' => $subType,
                                            'type' => $accountType->id,
                                        ]
                                    );

                                    //when ChartOfAccount data empty
                                    $chartOfAccounts = Utility::chartOfAccount($type, $subType);
                                    foreach ($chartOfAccounts as $chartAccount) {
                                        $check_account = ChartOfAccount::where('created_by', $company->id)->where('type', $accountType->id)
                                            ->where('name', $subType)->where('name', $chartAccount['name'])->first();

                                        if (empty($check_account)) {
                                            ChartOfAccount::create(
                                                [
                                                    'name' => $chartAccount['name'],
                                                    'code' => $chartAccount['code'],
                                                    'type' => $accountType->id,
                                                    'sub_type' => $accountSubType->id,
                                                    'is_enabled' => 1,
                                                    'created_by' => $company->id,

                                                ]
                                            );

                                        }
                                    }

                                }
                            }
                        }


                    }

            }
        }  else {


            $company = User::where('type', 'company')->where('id', $company_id)->first();
            // dd( $company );
            $chartOfAccountTypes = Self::$chartOfAccountType;
            foreach ($chartOfAccountTypes as $k => $type) {
                //when ChartOfAccountType data empty
                $check_type = ChartOfAccountType::where('created_by', $company_id)->where('name', $type)->first();
                if (empty($check_type)) {
                    $accountType = ChartOfAccountType::create(
                        [
                            'name' => $type,
                            'created_by' => $company->id,
                        ]
                    );

                    // dd($accountType);


                    //when ChartOfAccountSubType data empty
                    $chartOfAccountSubTypes = Self::$chartOfAccountSubType;
                    foreach ($chartOfAccountSubTypes[$k] as $subType) {
                        $check_subtype = ChartOfAccountSubType::where('type', $accountType->id)->where('name', $subType)->first();
                        if (empty($check_subtype)) {
                            $accountSubType = ChartOfAccountSubType::create(
                                [
                                    'name' => $subType,
                                    'type' => $accountType->id,
                                ]
                            );

                            //when ChartOfAccount data empty
                            $chartOfAccounts = Utility::chartOfAccount($type, $subType);
                            foreach ($chartOfAccounts as $chartAccount) {
                                $check_account = ChartOfAccount::where('created_by', $company->id)->where('type', $accountType->id)
                                    ->where('name', $subType)->where('name', $chartAccount['name'])->first();

                                if (empty($check_account)) {
                                    ChartOfAccount::create(
                                        [
                                            'name' => $chartAccount['name'],
                                            'code' => $chartAccount['code'],
                                            'type' => $accountType->id,
                                            'sub_type' => $accountSubType->id,
                                            'is_enabled' => 1,
                                            'created_by' => $company->id,

                                        ]
                                    );

                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // get date format
    public static function getDateFormated($date, $time = false)
    {
        if (!empty($date) && $date != '0000-00-00') {
            if ($time == true) {
                return date("d M Y H:i A", strtotime($date));
            } else {
                return date("d M Y", strtotime($date));
            }
        } else {
            return '';
        }
    }

    // get progress bar color
    public static function getProgressColor($percentage)
    {
        $color = '';

        if ($percentage <= 20) {
            $color = 'danger';
        } elseif ($percentage > 20 && $percentage <= 40) {
            $color = 'warning';
        } elseif ($percentage > 40 && $percentage <= 60) {
            $color = 'info';
        } elseif ($percentage > 60 && $percentage <= 80) {
            $color = 'primary';
        } elseif ($percentage >= 80) {
            $color = 'success';
        }

        return $color;
    }

    public static function ownerIdforInvoice($id)
    {
        $user = User::where(['id' => $id])->first();
        if (!is_null($user)) {
            if ($user->type == "company") {
                return $user->id;
            } else {
                $user1 = User::where(['id' => $user->created_by, $user->type => 'company'])->first();
                if (!is_null($user1)) {
                    return $user->id;
                }
            }
        }
        return 0;
    }

    public static function invoice_payment_settings($id)
    {
        $data = [];

        $user = User::where(['id' => $id])->first();
        if (!is_null($user)) {
            $data = DB::table('company_payment_settings');
            $data->where('created_by', '=', $id);
            $data = $data->get();
            //dd($data);
        }

        $res = [];

        foreach ($data as $key => $value) {
            $res[$value->name] = $value->value;
        }

        return $res;
    }
    public static function getNonAuthCompanyPaymentSetting($id)
    {
        $data = \DB::table('company_payment_settings');
        $settings = [];
        $data = $data->where('created_by', '=', $id);
        $data = $data->get();
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function second_to_time($seconds = 0)
    {
        $H = floor($seconds / 3600);
        $i = ($seconds / 60) % 60;
        $s = $seconds % 60;

        $time = sprintf("%02d:%02d:%02d", $H, $i, $s);

        return $time;
    }

    public static function diffance_to_time($start, $end)
    {
        $start = new Carbon($start);
        $end = new Carbon($end);
        $totalDuration = $start->diffInSeconds($end);

        return $totalDuration;
    }
    public static function send_slack_msg($slug, $obj)
    {
        $notification_template = NotificationTemplates::where('slug', $slug)->first();
        if (!empty($notification_template) && !empty($obj)) {
            $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->where('created_by', '=', \Auth::user()->creatorId())->first();
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->first();
            }
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang       = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', 'en')->first();
            }
            // dd($curr_noti_tempLang);
            if (!empty($curr_noti_tempLang) && !empty($curr_noti_tempLang->content)) {
                $msg = self::replaceVariable($curr_noti_tempLang->content, $obj);
            }
            //dd($msg);
        }
        if (isset($msg)) {
            $settings = Utility::settings();
            try {
                if (isset($settings['slack_webhook']) && !empty($settings['slack_webhook'])) {

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $settings['slack_webhook']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text' => $msg]));

                    $headers = array();
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        echo 'Error:' . curl_error($ch);
                    }
                    curl_close($ch);
                }
            } catch (\Exception $e) {
            }
        }
    }
    public static function send_telegram_msg($slug, $obj)
    {
        $notification_template = NotificationTemplates::where('slug', $slug)->first();
        if (!empty($notification_template) && !empty($obj)) {
            $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->where('created_by', '=', \Auth::user()->creatorId())->first();
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->first();
            }
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang       = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', 'en')->first();
            }
            // dd($curr_noti_tempLang);
            if (!empty($curr_noti_tempLang) && !empty($curr_noti_tempLang->content)) {
                $msg = self::replaceVariable($curr_noti_tempLang->content, $obj);
            }
            // dd($msg);
        }
        if (isset($msg)) {
            $settings = Utility::settings();
            try {
                // $msg = $resp;

                // Set your Bot ID and Chat ID.
                $telegrambot = $settings['telegrambot'];
                $telegramchatid = $settings['telegramchatid'];

                // Function call with your own text or variable
                $url = 'https://api.telegram.org/bot' . $telegrambot . '/sendMessage';
                $data = array(
                    'chat_id' => $telegramchatid,
                    'text' => $msg,
                );

                $options = array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => "Content-Type:application/x-www-form-urlencoded\r\n",
                        'content' => http_build_query($data),
                    ),
                );

                $context = stream_context_create($options);

                $result = file_get_contents($url, false, $context);
                $url = $url;
            } catch (\Exception $e) {
            }
        }
    }
    public static function send_twilio_msg($slug, $obj)
    {
        $notification_template = NotificationTemplates::where('slug', $slug)->first();
        if (!empty($notification_template) && !empty($obj)) {
            $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->where('created_by', '=', \Auth::user()->creatorId())->first();
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', \Auth::user()->lang)->first();
            }
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang       = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', 'en')->first();
            }
            // dd($curr_noti_tempLang);
            if (!empty($curr_noti_tempLang) && !empty($curr_noti_tempLang->content)) {
                $msg = self::replaceVariable($curr_noti_tempLang->content, $obj);
            }
            // dd($msg);
        }
        if (isset($msg)) {
            $settings = Utility::settings();
            $account_sid = $settings['twilio_sid'];
            $auth_token = $settings['twilio_token'];
            $twilio_number = $settings['twilio_from'];
            $client = new Client($account_sid, $auth_token);
            $client->messages->create($slug, [
                'from' => $twilio_number,
                'body' => $msg,
            ]);
            //dd('SMS Sent Successfully.');
        }
    }

    public static function total_quantity($type, $quantity, $id)
    {
        $product = Item::find($id);

        $pro_quantity = $product->quantity;

        if ($type == 'minus') {
            $product->quantity = $pro_quantity - $quantity;
        } else {
            $product->quantity = $pro_quantity + $quantity;
        }
        $product->save();
    }

    //add quantity in product stock
    public static function addProductStock($product_id, $quantity, $type, $description, $type_id)
    {

        $stocks = new StockReport();
        $stocks->product_id = $product_id;
        $stocks->quantity = $quantity;
        $stocks->type = $type;
        $stocks->type_id = $type_id;
        $stocks->description = $description;
        $stocks->created_by = \Auth::user()->creatorId();

        $stocks->save();
    }

    public static function color_set()
    {
        if (\Auth::user()) {
            if (\Auth::user()->type == 'company') {
                $user = \Auth::user();
                $setting = DB::table('settings')->where('created_by', $user->id)->pluck('value', 'name')->toArray();
                // dd($setting);
            } else {
                $setting = DB::table('settings')->where('created_by', \Auth::user()->creatorId())->pluck('value', 'name')->toArray();
            }
        } else {
            // $user = User::where('type','owner')->first();
            $user = User::where('type', 'company')->first();
            $setting = DB::table('settings')->where('created_by', $user->id)->pluck('value', 'name')->toArray();
        }
        if (!isset($setting['color'])) {

            $setting = Utility::settings();
        }
        return $setting;
    }

    public static function colorset()
    {
        if (\Auth::user()) {
            if (\Auth::user()->type != 'super admin') {
                $user = \Auth::user();
                $setting = DB::table('settings')->where('created_by', $user->id)->pluck('value', 'name')->toArray();
            } else {
                $setting = DB::table('settings')->where('created_by', \Auth::user()->creatorId())->pluck('value', 'name')->toArray();
            }
        } else {
            // $user = User::where('type','company')->first();
            $user = User::where('type', 'super admin')->first();
            //  dd($user);
            $setting = DB::table('settings')->where('created_by', $user->id)->pluck('value', 'name')->toArray();
        }
        if (!isset($setting['color'])) {
            $setting = Utility::settings();
        }
        return $setting;
    }

    public static function get_superadmin_logo()
    {

        $is_dark_mode = DB::table('settings')->where('created_by', '1')->pluck('value', 'name')->toArray();
        if (!empty($is_dark_mode['cust_darklayout'])) {
            $is_dark_modes = $is_dark_mode['cust_darklayout'];

            if ($is_dark_modes == 'on') {
                return 'logo-light.png';
            } else {
                return 'logo-dark.png';
            }
        } else {
            return 'logo-dark.png';
        }
    }


    public static function GetLogo()
    {
        $setting = Utility::settings();


        if (\Auth::user() && \Auth::user()->type != 'super admin') {

            if ($setting['cust_darklayout'] == 'on') {
                return Utility::getValByName('company_logo_light');
            } else {

                // dd(Utility::getValByName('company_logo_dark'));
                return Utility::getValByName('company_logo_dark');
            }
        } else {

            if ($setting['cust_darklayout'] == 'on') {

                return Utility::getValByName('light_logo');
            } else {
                return Utility::getValByName('dark_logo');
            }
        }
    }
    public static function getFirstSeventhWeekDay($week = null)
    {
        $first_day = $seventh_day = null;

        if (isset($week)) {
            $first_day = Carbon::now()->addWeeks($week)->startOfWeek();
            $seventh_day = Carbon::now()->addWeeks($week)->endOfWeek();
        }

        $dateCollection['first_day'] = $first_day;
        $dateCollection['seventh_day'] = $seventh_day;

        $period = CarbonPeriod::create($first_day, $seventh_day);

        foreach ($period as $key => $dateobj) {
            $dateCollection['datePeriod'][$key] = $dateobj;
        }

        return $dateCollection;
    }

    private static $designationid;
    private static $overallrating;
    private static $competencyCount;

    public static function getTargetrating($designationid, $competencyCount)
    {
        if(self::$designationid !=  $designationid && self::$competencyCount !=  $competencyCount)
        {
            $indicator = Indicator::where('designation', $designationid)->first();

            if (!empty($indicator->rating) && ($competencyCount != 0)) {
                $rating = json_decode($indicator->rating, true);
                $starsum = array_sum($rating);

                $overallrating = $starsum / $competencyCount;
            } else {
                $overallrating = 0;
            }
            self::$overallrating = $overallrating;
            self::$designationid = $designationid;
            self::$competencyCount = $competencyCount;
        }
        else{

            self::$designationid = $designationid;
        }

        return self::$overallrating;
    }


    public static function file_validate()
    {
        try {
            $settings = Utility::getStorageSetting();
            // dd($settings);
            if (!empty($settings['storage_setting'])) {
                if ($settings['storage_setting'] == 'wasabi') {
                    $max_size = !empty($settings['wasabi_max_upload_size']) ? $settings['wasabi_max_upload_size'] : '2048';
                    $mimes =  !empty($settings['wasabi_storage_validation']) ? $settings['wasabi_storage_validation'] : '';
                } else if ($settings['storage_setting'] == 's3') {
                    $max_size = !empty($settings['s3_max_upload_size']) ? $settings['s3_max_upload_size'] : '2048';
                    $mimes =  !empty($settings['s3_storage_validation']) ? $settings['s3_storage_validation'] : '';
                } else {
                    $max_size = !empty($settings['local_storage_max_upload_size']) ? $settings['local_storage_max_upload_size'] : '2048';
                    $mimes =  !empty($settings['local_storage_validation']) ? $settings['local_storage_validation'] : '';
                }
                    $res = [
                        'types'  => $mimes,
                        'max_size'  => $max_size,
                    ];
                    return $res;
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => __('Please set proper configuration for storage.'),
                ];
                return $res;
            }
        } catch (\Exception $e) {
            // dd($e);
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }
    public static function upload_file($request, $key_name, $name, $path, $custom_validation = [])
    {
        // dd($request->all(), $key_name, $name, $path);
        try {
            $settings = Utility::getStorageSetting();



            if (!empty($settings['storage_setting'])) {


                if ($settings['storage_setting'] == 'wasabi') {


                    config(
                        [
                            'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                        ]
                    );

                    $max_size = !empty($settings['wasabi_max_upload_size']) ? $settings['wasabi_max_upload_size'] : '2048';
                    $mimes = !empty($settings['wasabi_storage_validation']) ? $settings['wasabi_storage_validation'] : '';
                } else if ($settings['storage_setting'] == 's3') {

                    config(
                        [
                            'filesystems.disks.s3.key' => $settings['s3_key'],
                            'filesystems.disks.s3.secret' => $settings['s3_secret'],
                            'filesystems.disks.s3.region' => $settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
                            'filesystems.disks.s3.use_path_style_endpoint' => false,
                        ]
                    );
                    $max_size = !empty($settings['s3_max_upload_size']) ? $settings['s3_max_upload_size'] : '2048';
                    $mimes = !empty($settings['s3_storage_validation']) ? $settings['s3_storage_validation'] : '';
                } else {

                    $max_size = !empty($settings['local_storage_max_upload_size']) ? $settings['local_storage_max_upload_size'] : '2048';

                    $mimes = !empty($settings['local_storage_validation']) ? $settings['local_storage_validation'] : '';



                }


                $file = $request->$key_name;

                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {

                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = \Validator::make($request->all(), [
                    $key_name => $validation,
                ]);





                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];
                    return $res;
                } else {

                    $name = $name;

                    if ($settings['storage_setting'] == 'local') {

                        $request->$key_name->move(storage_path($path), $name);
                        $path = $path . $name;

                        // dd($path);
                    } else if ($settings['storage_setting'] == 'wasabi') {

                        $path = \Storage::disk('wasabi')->putFileAs(
                            $path,
                            $file,
                            $name
                        );

                        // $path = $path.$name;

                    } else if ($settings['storage_setting'] == 's3') {

                        $path = \Storage::disk('s3')->putFileAs(
                            $path,
                            $file,
                            $name
                        );
                        // $path = $path.$name;
                        // dd($path);
                    }

                    $res = [
                        'flag' => 1,
                        'msg' => 'success',
                        'url' => $path,
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => __('Please set proper configuration for storage.'),
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }

    public static function get_file($path)
    {
        $settings = Utility::getStorageSetting();

        try {
            if ($settings['storage_setting'] == 'wasabi') {
                config(
                    [
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                    ]
                );
            } elseif ($settings['storage_setting'] == 's3') {
                config(
                    [
                        'filesystems.disks.s3.key' => $settings['s3_key'],
                        'filesystems.disks.s3.secret' => $settings['s3_secret'],
                        'filesystems.disks.s3.region' => $settings['s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
                        'filesystems.disks.s3.use_path_style_endpoint' => false,
                    ]
                );
            }

            return \Storage::disk($settings['storage_setting'])->url($path);
        } catch (\Throwable $th) {
            return '';
        }
    }

    public static function newLangEmailTemp($lang)
    {
        $template = EmailTemplate::all();
        foreach ($template as $t) {
            $default_lang = EmailTemplateLang::where('parent_id', '=', $t->id)->where('lang', 'LIKE', 'en')->first();
            $emailTemplateLang = new EmailTemplateLang();
            $emailTemplateLang->parent_id = $t->id;
            $emailTemplateLang->lang = $lang;
            $emailTemplateLang->subject = $default_lang->subject;
            $emailTemplateLang->content = $default_lang->content;
            $emailTemplateLang->save();
        }
    }

    public static function colorCodeData($type)
    {

        if ($type == 'event') {
            return 1;
        } elseif ($type == 'zoom_meeting') {
            return 2;
        } elseif ($type == 'task') {
            return 3;
        } elseif ($type == 'appointment') {
            return 11;
        } elseif ($type == 'rotas') {
            return 3;
        } elseif ($type == 'holiday') {
            return 4;
        } elseif ($type == 'call') {
            return 10;
        } elseif ($type == 'meeting') {
            return 5;
        } elseif ($type == 'leave') {
            return 6;
        } elseif ($type == 'work_order') {
            return 7;
        } elseif ($type == 'lead') {
            return 7;
        } elseif ($type == 'deal') {
            return 8;
        } elseif ($type == 'interview_schedule') {
            return 9;
        } else {
            return 11;
        }
    }
    public static $colorCode = [
        1 => 'event-warning',
        2 => 'event-secondary',
        3 => 'event-success',
        4 => 'event-warning',
        5 => 'event-danger',
        6 => 'event-dark',
        7 => 'event-black',
        8 => 'event-info',
        9 => 'event-secondary',
        10 => 'event-success',
        11 => 'event-warning',
    ];

    public static function googleCalendarConfig()
    {
        $setting = Utility::settings();
        $path = storage_path($setting['google_calender_json_file']);

        config([
            'google-calendar.default_auth_profile' => 'service_account',
            'google-calendar.auth_profiles.service_account.credentials_json' => $path,
            'google-calendar.auth_profiles.oauth.credentials_json' => $path,
            'google-calendar.auth_profiles.oauth.token_json' => $path,
            'google-calendar.calendar_id' => isset($setting['google_clender_id']) ? $setting['google_clender_id'] : '',
            'google-calendar.user_to_impersonate' => '',

        ]);
    }

    public static function addCalendarData($request, $type)
    {
        Self::googleCalendarConfig();
        $event = new GoogleEvent();
        $event->name = $request->title;
        $event->startDateTime = Carbon::parse($request->start_date);
        $event->endDateTime = Carbon::parse($request->end_date);
        $event->colorId = Self::colorCodeData($type);
        $event->save();
    }

    public static function getCalendarData($type)
    {

        Self::googleCalendarConfig();

        $data = GoogleEvent::get();

        $type = Self::colorCodeData($type);

        $arrayJson = [];

        foreach ($data as $val) {
            $end_date = date_create($val->endDateTime);
            date_add($end_date, date_interval_create_from_date_string("1 days"));

            if ($val->colorId == "$type") {
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => $val->summary,
                    "start" => $val->startDateTime,
                    "end" => date_format($end_date, "Y-m-d H:i:s"),
                    "className" => Self::$colorCode[$type],
                    "allDay" => true,
                ];
            }
        }

        return $arrayJson;
    }
    // cache module
    public static function GetCacheSize()
    {
        $file_size = 0;
        foreach (\File::allFiles(storage_path('/framework')) as $file) {
            $file_size += $file->getSize();
        }
        $file_size = number_format($file_size / 1000000, 4);
        return $file_size;
    }

    // Webhook Settings
    public static function webhookSetting($module, $user_id = null)
    {
        if (!empty($user_id)) {
            $user = User::find($user_id);
        } else {
            $user = \Auth::user();
        }
        $webhook = Webhook_settings::where('module', $module)->where('created_by', '=', $user->id)->first();
        if (!empty($webhook)) {
            $url = $webhook->url;
            $method = $webhook->method;
            $reference_url  = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $data['method'] = $method;
            $data['reference_url'] = $reference_url;
            $data['url'] = $url;
            return $data;
        }
        return false;
    }

    public static function WebhookCall($url = null, $parameter = null, $method = 'POST')
    {
        if (!empty($url) && !empty($parameter)) {
            try {

                $curlHandle = curl_init($url);
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $parameter);
                curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);
                $curlResponse = curl_exec($curlHandle);
                curl_close($curlHandle);
                if (empty($curlResponse)) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        } else {
            return false;
        }
    }

    public static  function get_device_type($user_agent)
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

    // start for (plans) storage limit - for file upload size
    public static function updateStorageLimit($company_id, $image_size)
    {
        $image_size = number_format($image_size / 1048576, 2);
        $user   = User::find($company_id);
        $plan   = Plan::find($user->plan);
        $total_storage = $user->storage_limit + $image_size;

        if ($plan->storage_limit <= $total_storage && $plan->storage_limit != -1) {
            $error = __('Plan storage limit is over so please upgrade the plan.');
            return $error;
        } else {
            $user->storage_limit = $total_storage;
        }

        $user->save();
        return 1;
    }

    public static function changeStorageLimit($company_id, $file_path)
    {
        $files =  \File::glob(storage_path($file_path));
        $fileSize = 0;
        foreach ($files as $file) {
            $fileSize += \File::size($file);
        }

        $image_size = number_format($fileSize / 1048576, 2);
        $user   = User::find($company_id);
        $plan   = Plan::find($user->plan);
        $total_storage = $user->storage_limit - $image_size;
        $user->storage_limit = $total_storage;
        $user->save();

        $status = false;
        foreach ($files as $key => $file) {
            if (\File::exists($file)) {
                $status = \File::delete($file);
            }
        }

        return true;
    }
    // end for (plans) storage limit - for file upload size



    public static function plansettings($user_id = null)
    {
        if (!empty($user_id)) {
            $user = User::where('id', $user_id)->first();
        } elseif (\Auth::check()) {
            $user = \Auth::user();
        }
        if ($user->type != 'company') {
            $user = User::where('id', $user->created_by)->first();
        }
        $plansettings = [
            "enable_chatgpt" => 'off',
        ];
        if ($user != null && $user->plan) {
            $plan = Plan::where('id', $user->plan)->first();

            $plansettings = [
                "enable_chatgpt" => $plan->enable_chatgpt,
            ];
        }
        return $plansettings;
    }

    public static function is_chatgpt_enable($user_id = null)
    {
        if (!empty($user_id)) {
            $user = User::where('id', $user_id)->first();
        } elseif (\Auth::check()) {
            $user = \Auth::user();
        }
        if ($user->type != 'company') {
            $user = User::where('id', $user->created_by)->first();
        }

        $chatgpt = 'off';

        if ($user != null && $user->plan) {
            $plan = Plan::where('id', $user->plan)->first();

            $chatgpt = $plan->enable_chatgpt;
        }
        return $chatgpt;
    }

    //language

    public static function flagOfCountry()
    {
        $arr = [
            'ar' => '🇦🇪 ar',
            "zh" => "🇨🇳 zh",
            'da' => '🇩🇰 ad',
            'de' => '🇩🇪 de',
            'es' => '🇪🇸 es',
            'fr' => '🇫🇷 fr',
            'it'    =>  '🇮🇹 it',
            'ja' => '🇯🇵 ja',
            'he' => '🇮🇱 he',
            'nl' => '🇳🇱 nl',
            'pl' => '🇵🇱 pl',
            'ru' => '🇷🇺 ru',
            'pt' => '🇵🇹 pt',
            'en' => '🇮🇳 en',
            'tr' => '🇹🇷 tr',
            'pt-br' => '🇧🇷 pt-br',
        ];
        return $arr;
    }
    public static function langList()
    {
        $languages = [
            "ar" => "Arabic",
            "zh" => "Chinese",
            "da" => "Danish",
            "de" => "German",
            "en" => "English",
            "es" => "Spanish",
            "fr" => "French",
            "he" => "Hebrew",
            "it" => "Italian",
            "ja" => "Japanese",
            "nl" => "Dutch",
            "pl" => "Polish",
            "pt" => "Portuguese",
            "ru" => "Russian",
            "tr" => "Turkish",
            "pt-br" => "Portuguese(Brazil)"
        ];
        return $languages;
    }
    public static function languagecreate()
    {
        $languages = Utility::langList();
        foreach ($languages as $key => $lang) {
            $languageExist = Languages::where('code', $key)->first();
            if (empty($languageExist)) {
                $language = new Languages();
                $language->code = $key;
                $language->fullName = $lang;
                $language->save();
            }
        }
    }
    public static function langSetting()
    {
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', 1)->get();
        if (count($data) == 0) {
            $data = DB::table('settings')->where('created_by', '=', 1)->get();
        }
        $settings = [];
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static function getSeoSetting()
    {
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', 1);
        $data = $data->get();
        $settings = [
            "meta_keywords" => "",
            "meta_image" => "",
            "meta_description" => "",
        ];
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static function getCookieSetting()
    {
        $data = DB::table('settings');
        if (\Auth::check()) {
            $userId = \Auth::user()->creatorId();
            $data = $data->where('created_by', '=', $userId);
        } else {
            $data = $data->where('created_by', '=', 1);
        }
        $data = $data->get();
        $cookies = [
            'enable_cookie' => 'on',
            'necessary_cookies' => 'on',
            'cookie_logging' => 'on',
            'cookie_title' => 'We use cookies!',
            'cookie_description' => 'Hi, this website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it',
            'strictly_cookie_title' => 'Strictly necessary cookies',
            'strictly_cookie_description' => 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
            'more_information_description' => 'For any queries in relation to our policy on cookies and your choices, please contact us',
            "more_information_title" => "",
            'contactus_url' => '#',
        ];
        foreach ($data as $key => $row) {
            if (in_array($row->name, $cookies)) {
                $cookies[$row->name] = $row->value;
            }
        }
        return $cookies;
    }

    public static function mode_layout()
    {
        $data = DB::table('settings');
        if (\Auth::check()) {
            $data = $data->where('created_by', '=', \Auth::user()->creatorId())->get();
            if (count($data) == 0) {
                $data = DB::table('settings')->where('created_by', '=', 1)->get();
            }
        } else {
            $data->where('created_by', '=', 1);
            $data = $data->get();
        }
        $settings = [
            "cust_darklayout" => "off",
            "cust_theme_bg" => "on",
            "color" => '',
        ];
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static $invoiceProductsData = null;

    public static function getInvoiceProductsData()
    {

        if (self::$invoiceProductsData === null) {
            $taxData = Utility::getTaxData();
            $InvoiceProducts = \DB::table('invoice_products')
                                ->select('invoice',
                                        \DB::raw('SUM(quantity) as total_quantity'),
                                        \DB::raw('SUM(discount) as total_discount'),
                                        \DB::raw('SUM(price * quantity)  as sub_total'),
                                        \DB::raw('GROUP_CONCAT(tax) as tax_values'))
                                ->groupBy('invoice')
                                ->get()
                                ->keyBy('invoice');

            $InvoiceProducts->map(function ($invoice , $taxData) {
                $taxArr = explode(',', $invoice->tax_values);
                $taxes = 0;
                $totalTax = 0;
                foreach ($taxArr as $tax) {
                    $taxes += !empty($taxData[$tax]['rate']) ? $taxData[$tax]['rate'] : 0;
                }
                $totalTax += ($taxes / 100) * ($invoice->sub_total);
                $invoice->total = $invoice->sub_total + $totalTax - $invoice->total_discount;
                return $invoice;
            });

            self::$invoiceProductsData = $InvoiceProducts;
        }

        return self::$invoiceProductsData;
    }

    public static function setCaptchaConfig()
    {
        config([
            'captcha.secret'    => Utility::getSuperAdminValByName('google_recaptcha_secret'),
            'captcha.sitekey'   => Utility::getSuperAdminValByName('google_recaptcha_key') ,
        ]);
    }

    public static function setPusherConfig()
    {
        config([
            'chatify.pusher.key'                => Utility::getSuperAdminValByName('pusher_app_key'),
            'chatify.pusher.secret'             => Utility::getSuperAdminValByName('pusher_app_secret'),
            'chatify.pusher.app_id'             => Utility::getSuperAdminValByName('pusher_app_id'),
            'chatify.pusher.options.cluster'    => Utility::getSuperAdminValByName('pusher_app_cluster'),
        ]);
    }

    public static function setMailConfig()
    {
        config([
                'mail.driver'       => Utility::getValByName('mail_driver'),
                'mail.host'         => Utility::getValByName('mail_host'),
                'mail.port'         => Utility::getValByName('mail_port'),
                'mail.encryption'   => Utility::getValByName('mail_encryption'),
                'mail.username'     => Utility::getValByName('mail_username'),
                'mail.password'     => Utility::getValByName('mail_password'),
                'mail.from.address' => Utility::getValByName('mail_from_address'),
                'mail.from.name'    => Utility::getValByName('mail_from_name'),
        ]);
    }

    public static function getSMTPDetails($user_id)
    {
        $settings = Utility::settingsById($user_id);
        if ($settings) {
            config([
                'mail.default'                   => isset($settings['mail_driver'])       ? $settings['mail_driver']       : '',
                'mail.mailers.smtp.host'         => isset($settings['mail_host'])         ? $settings['mail_host']         : '',
                'mail.mailers.smtp.port'         => isset($settings['mail_port'])         ? $settings['mail_port']         : '',
                'mail.mailers.smtp.encryption'   => isset($settings['mail_encryption'])   ? $settings['mail_encryption']   : '',
                'mail.mailers.smtp.username'     => isset($settings['mail_username'])     ? $settings['mail_username']     : '',
                'mail.mailers.smtp.password'     => isset($settings['mail_password'])     ? $settings['mail_password']     : '',
                'mail.from.address'              => isset($settings['mail_from_address']) ? $settings['mail_from_address'] : '',
                'mail.from.name'                 => isset($settings['mail_from_name'])    ? $settings['mail_from_name']    : '',
            ]);

            return $settings;
        } else {
            return redirect()->back()->with('Email SMTP settings does not configured so please contact to your site admin.');
        }
    }

    public static function emailTemplateLang($lang)
    {

        $defaultTemplate = [
            'new_user' => [
                'subject' => 'New User',
                'lang' => [
                    'en' => '<p>Hello,&nbsp;<br>Welcome to {app_name}.</p><p><b>Email </b>: {email}<br><b>Password</b> : {password}</p><p>{app_url}</p><p>Thanks,<br>{app_name}</p>',
                ],
            ],
            'lead_assigned' => [
                'subject' => 'Lead Assigned',
                'lang' => [

                    'en' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Hello,</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">New Lead has been Assign to you.</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Name : {lead_name}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Email : {lead_email}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Pipeline : {lead_pipeline}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Stage : {lead_stage}</span></p>
                    <p><span style="background-color: #f8f8f8; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures;">Lead Subject: {lead_subject}</span></p>',
                ],
            ],
            'deal_assigned' => [
                'subject' => 'Deal Assigned',
                'lang' => [
                    'en' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hello,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal has been Assign to you.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Deal Name</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Deal Stage</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Deal Status</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Deal Price</span>&nbsp;: {deal_price}</span></p><p></p>',
                ],
            ],
            'estimation_sent' => [
                'subject' => 'Estimation Sent”',
                'lang' => [
                    'en' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Hello,</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">New Estimation has been Assign to you.</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Name: {estimation_id}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Client : {estimation_client}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Category : {estimation_category}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Issue Date : {estimation_issue_date}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Expiry Date : {estimation_expiry_date}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Status : {estimation_status}</span></p>
                     <p>&nbsp;</p>',
                ],
            ],
            'new_project' => [
                'subject' => 'New Project',
                'lang' => [
                    'en' => '<p>Hello&nbsp;{project_client},</p>
                    <p>Hello nice to meet you.</p>
                    <p>New project is assigned to you.</p>
                    <p><br /><strong>Project Title:</strong>&nbsp;{project_title}</p>
                    <p><strong>Project Start Date:</strong>&nbsp;{project_start_date}</p>
                    <p><strong>Project Due Date</strong>:&nbsp;{project_due_date}</p>
                    <p>We are looking forward hearing from you.<br /><br />Kind Regards,<br />{app_name}</p>
                    <p>&nbsp;</p>',
                ],
            ],
            'project_assigned' => [
                'subject' => 'Project Assigned',
                'lang' => [
                    'en' => '<p>Hello&nbsp;{project_assign_user},</p><p>New project is assigned to you.<br><br><strong>Project Title:</strong>&nbsp;{project_title}</p><p><strong>Project Start Date:</strong>&nbsp;{project_start_date}</p><p><b>Project Due Date</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>We are looking forward hearing from you.<br><br>Kind Regards,<br>{app_name}</p><p></p>',
                ],
            ],
            'project_finished' => [
                'subject' => 'Project Finished',
                'lang' => [
                    'en' => '<p><b>Hello</b>&nbsp;{project_client},</p><p>You are receiving this email because project&nbsp;<strong>{project}</strong> has been marked as finished. This project is assigned under your company and we just wanted to keep you up to date.<br></p><p>If you have any questions don\'t hesitate to contact us.<br><br>Kind Regards,<br>{app_name}</p>',
                ],
            ],
            'task_assigned' => [
                'subject' => 'Task Assigned',
                'lang' => [
                    'en' => '<p>Dear {task_assign_user}</p><p>You have been assigned to a new task:</p><p><b>Name</b>: {task_title}<br><b>Start Date</b>: {task_start_date}<br><b>Due date</b>: {task_due_date}<br><b>Priority</b>: {task_priority}<br><br>Kind Regards,<br>{app_name}</p>',
                ],
            ],
            'invoice_sent' => [
                'subject' => 'Invoice Sent',
                'lang' => [
                    'en' => '<p><span style="font-size: 12pt;"><b>Dear</b> {invoice_client}</span><span style="font-size: 12pt;">,</span></p><p><span style="font-size: 12pt;">We have prepared the following invoice for you :#{invoice_id}</span></p><p><span style="font-size: 12pt;"><b>Invoice Status</b> : {invoice_status}</span></p><p>Please Contact us for more information.</p><p><br><b>Kind Regards</b>,<br><span style="font-size: 12pt;">{app_name}</span><br></p>',
                ],
            ],


            'invoice_payment_recorded' => [
                'subject' => 'Invoice Payment Recorded',
                'lang' => [
                    'en' => '<p><span style="font-size: 12pt;"><b>Hello</b>&nbsp;{invoice_client}</span><span style="font-size: 12pt;"><br><br></span><span style="font-size: 12pt;"><br></span>Thank you for the payment. Find the payment details below:<br>-------------------------------------------------<br><b>Amount</b>: {payment_total}<strong><br></strong><b>Date</b>: {payment_date}<strong><br></strong><b>Invoice number</b>: {invoice_id}<span style="font-size: 12pt;"><strong><br></strong></span><span style="font-size: 12pt;"><strong><br></strong></span>-------------------------------------------------<br>We are looking forward working with you.<br><span style="font-size: 12pt;"><b>Kind Regards</b>,<br></span>{app_name}</p>',
                ],
            ],
            'new_credit_note' => [
                'subject' => 'New Credit Note',
                'lang' => [
                    'en' => '<p><b>Dear</b>&nbsp;{invoice_client}</p><p>We have attached the credit note with number #{invoice_id} for your reference.</p><p><b>Date</b>:&nbsp;{credit_note_date}</p><p><b>Total Amount</b>:&nbsp;{credit_amount}</p><p>Please contact us for more information.</p><p><b>Kind Regards</b>,</p>{app_name}',
                ],
            ],
            'new_support_ticket' => [
                'subject' => 'New Support Ticket',
                'lang' => [
                    'en' => '<p><span style="font-size: 12pt;"><b>Hi</b>&nbsp;{assign_user}</span><br><br><span style="font-size: 12pt;">New support ticket has been opened.</span><br><br><span style="font-size: 12pt;"><strong>Title:</strong>&nbsp;{support_title}</span><br><span style="font-size: 12pt;"><strong>Priority:</strong>&nbsp;{support_priority}</span><span style="font-size: 12pt;"><br></span><span style="font-size: 12pt;"><b>End Date</b>: {support_end_date}</span></p><p><br><span style="font-size: 12pt;"><strong>Support message:</strong></span><br><span style="font-size: 12pt;">{support_description}</span><span style="font-size: 12pt;"><br><br><b>Kind Regards</b>,</span><br>{app_name}</p>',
                ],
            ],
            'new_contract' => [
                'subject' => 'New Contract',
                'lang' => [
                    'en' => '<p>&nbsp;</p>
                    <p><strong>Hi</strong> {contract_client}</p>
                    <p><b>Contract Subject</b>&nbsp;: {contract_subject}</p>
                    <p><b>Contract Project</b>&nbsp;: {contract_project}</p>
                    <p><b>Start Date&nbsp;</b>: {contract_start_date}</p>
                    <p><b>End Date&nbsp;</b>: {contract_end_date}</p>
                    <p>Looking forward to hear from you.</p>
                    <p><strong>Kind Regards, </strong></p>
                    <p>{company_name}</p>',
                ],
            ],
        ];

        $email = EmailTemplate::all();

        foreach ($email as $e) {
            foreach ($defaultTemplate[$e->slug]['lang'] as  $content) {
                $emailNoti = EmailTemplateLang::where('parent_id', $e->id)->where('lang', $lang)->count();
                if ($emailNoti == 0) {
                    EmailTemplateLang::create(
                        [
                            'parent_id' => $e->id,
                            'lang' => $lang,
                            'subject' => $defaultTemplate[$e->slug]['subject'],
                            'content' => $content,
                        ]
                    );
                }
            }
        }
    }


    public static function notificationTemplateLangs($lang)
    {

        $defaultTemplate = [
            //New Holiday
            'new_holiday' => [
                'variables' => '{
                    "Date": "date",
                    "Occasion": "occasion"
                    }',
                'lang' => [
                    'en' => 'Date {date} Occasion {occasion}',
                ],
            ],
            //New Meeting
            'new_meeting' => [
                'variables' => '{
                    "Title": "title",
                    "Date": "date"
                    }',
                'lang' => [
                    'en' => 'New Meeting {title} on {date}',
                ],
            ],
            //New Event
            'new_event' => [
                'variables' => '{
                    "Event Title": "event_title",
                    "Department Name": "department_name",
                    "Start Date": "start_date",
                    "End Date": "end_date"
                    }',
                'lang' => [
                    'en' => 'Event Title {event_title} Event Department {department_name} Start Date {start_date} End Date {end_date}',
                ],
            ],
            //New Lead
            'new_lead' => [
                'variables' => '{
                    "Company Name": "user_name",
                    "Lead Name": "lead_name",
                    "Lead Email": "lead_email"
                    }',
                'lang' => [
                    'en' => 'New Lead created by {user_name}',
                ]
            ],
            //lead_to_deal_conversion
            'lead_to_deal_conversion' => [
                'variables' => '{
                    "Company Name": "user_name",
                     "Lead Name": "lead_name",
                    "Lead Email": "lead_email"
                    }',
                'lang' => [
                    'en' => 'Deal converted through lead {lead_name}',
                ]
            ],
            //New Estimate
            'new_estimate' => [
                'variables' => '{
                    "Company Name": "user_name"
                    }',
                'lang' => [
                    'en' => 'New Estimation created by the {user_name}.',
                ]
            ],
            //New Milestone
            'new_milestone' => [
                'variables' => '{
                    "Company Name": "user_name",
                    "Title": "title",
                    "Cost": "cost",
                    "Start Date": "start_date",
                    "Due Date": "due_date"
                    }',
                'lang' => [
                    'en' => 'New Milestone added {title} of Cost {cost} Start Date {start_date} and Due Date {due_date}',
                ]
            ],
            //New support_ticket
            'support_ticket' => [
                'variables' => '{
                    "Support Priority": "support_priority",
                    "Support User Name": "support_user_name"
                    }',
                'lang' => [
                    'en' => 'New Support ticket created of {support_priority} priority for {support_user_name}',
                ]
            ],
            //New Task Comment
            'new_task_comment' => [
                'variables' => '{
                    "Company Name": "user_name",
                    "Task Name": "task_name",
                    "Project Name": "project_name"
                    }',
                'lang' => [
                    'en' => 'New Comment added in task {task_name} of project {project_name} by {user_name}',
                ]
            ],
            //New Company Policy
            'new_company_policy' => [
                'variables' => '{
                    "Company Name": "user_name",
                    "Company Policy Name": "company_policy_name"
                    }',
                'lang' => [
                    'en' => '{company_policy_name} policy created by {user_name}',
                ]
            ],
            //New Award
            'new_award' => [
                'variables' => '{
                    "Award Name": "award_name",
                    "Employee Name": "employee_name",
                    "Award Date": "award_date"
                    }',
                'lang' => [
                    'en' => '{award_name} created for {employee_name} from {award_date}',
                ]
            ],
            //New Project
            'new_project' => [
                'variables' => '{
                    "Company Name": "user_name",
                    "Project Name": "project_name"
                    }',
                'lang' => [
                    'en' => 'New {project_name} project created by {user_name}.',
                ]
            ],
            //New Project status
            'new_project_status' => [
                'variables' => '{
                     "Project Name": "project_name",
                     "Status": "status"

                    }',
                'lang' => [
                    'en' => 'New {project_name} Status Updadated {status} successfully.',
                ]
            ],
            //New Invoice
            'new_invoice' => [
                'variables' => '{
                    "Company Name": "user_name",
                    "Invoice Number": "invoice_number"
                    }',
                'lang' => [
                    'en' => 'New Invoice { invoice_number } created by {user_name}',
                ]
            ],
            'invoice_status' => [
                'variables' => '{
                    "Invoice": "invoice",
                    "Old status": "old_status",
                    "New Status": "status"
                     }',
                'lang' => [

                    'en' => 'Invoice {invoice} status changed from {old_status} to {status}',
                ]
            ],
            'new_deal' => [
                'variables' => '{
                    "Company Name": "user_name",
                    "Deal Name": "deal_name"
                    }',
                'lang' => [
                    'en' => 'New Deal created by {user_name}',
                ]
            ],
            //New Task
            'new_task' => [
                'variables' => '{
                    "Company Name": "user_name",
                    "Task Name": "task_name",
                    "Project Name": "project_name"
                    }',
                'lang' => [
                    'en' => '{task_name} task create for {project_name} project by {user_name}.',
                ]
            ],
            //Task Moved
            'task_moved' => [
                'variables' => '{
                    "Task Title": "task_title",
                    "Old Task Stages": "task_stage",
                    "New Task Stages": "new_task_stage"
                    }',
                'lang' => [
                    'en' => 'Task {task_title} Stage change from {task_stage} to {new_task_stage}',
                ]
            ],
            //Task Moved
            'new_payment' => [
                'variables' => '{
                    "User Name": "user_name",
                    "Amount": "amount",
                    "Created By": "created_by"
                     }',
                'lang' => [
                    'en' => 'New payment of {amount} created for {user_name} Created By {created_by}',
                ]
            ],
            //New Contract
            'new_contract' => [
                'variables' => '{
                    "Company Name": "user_name",
                    "Contract Name": "contract_subject",
                    "Client Name": "contract_client",
                    "Contract Price": "contract_value",
                    "Contract Start Date": "contract_start_date",
                    "Contract End Date": "contract_end_date"
                    }',
                'lang' => [
                    'en' => '{contract_subject} contract created for {contract_client} by {user_name}',
                ]
            ],
            // /leave_status
            'leave_status' => [
                'variables' => '{
                    "Company Name": "user_name",
                    "status": "status"
                    }',
                'lang' => [
                    'en' => 'Leave has been {status} by {user_name}',
                ]
            ],
            //new_trip
            'new_trip' => [
                'variables' => '{
                    "Purpose Of Visit": "purpose_of_visit",
                    "Place Of Visit": "place_of_visit",
                    "Start Date": "start_date",
                    "End Date": "end_date"
                    }',
                'lang' => [
                    'en' => 'New Place of visit at {place_of_visit} for purpose {purpose_of_visit} start from {start_date} to {end_date}',
                ]
            ],
        ];
        $notification = NotificationTemplates::all();
        foreach ($notification as $ntfy) {
            foreach ($defaultTemplate[$ntfy->slug]['lang'] as $content) {
                $emailNoti = NotificationTemplateLangs::where('parent_id', $ntfy->id)->where('lang', $lang)->count();
                if ($emailNoti == 0) {
                    NotificationTemplateLangs::create(
                        [
                            'parent_id' => $ntfy->id,
                            'lang' => $lang,
                            'variables' => $defaultTemplate[$ntfy->slug]['variables'],
                            'content' => $content,
                            'created_by' => 1,
                        ]
                    );
                }
            }
        }
    }
}


