<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles;
    use Notifiable, HasApiTokens;
    use Impersonate;


    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'type',
        'avatar',
        'lang',
        'mode',
        'delete_status',
        'plan',
        'lastlogin',
        'is_enable_login',
        'plan_expire_date',
        'requested_plan',
        'created_by',
        'referral_code',
        'used_referral_code',
    ];

    protected $module;
    protected $defaultView;
    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function employeeDetail()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'id');
    }

    public function clientDetail()
    {
        return $this->hasOne('App\Models\Client', 'user_id', 'id');
    }

    public function clientPermission($project_id)
    {
        return ClientPermission::where('client_id', '=', $this->id)->where('project_id', '=', $project_id)->first();
    }


    public function authId()
    {
        return $this->id;
    }

    public function creatorId()
    {
        if ($this->type == 'company' || $this->type == 'super admin') {
            return $this->id;
        } else {
            return $this->created_by;
        }
    }

    public function parentId()
    {
        if ($this->type == 'super admin') {
            return $this->id;
        } else {
            return $this->created_by;
        }
    }

    public function currentLanguage()
    {
        return $this->lang;
    }

    public function employeeIdFormat($number)
    {
        $settings = Utility::settings();
        return $settings["employee_prefix"] . sprintf("%05d", $number);
    }

    public function clientIdFormat($number)
    {
        $settings = Utility::settings();

        return $settings["client_prefix"] . sprintf("%05d", $number);
    }

    public static function priceFormat($price)
    {
        $settings = Utility::settings();
        return (($settings['site_currency_symbol_position'] == "pre") ? $settings['site_currency_symbol'] : '') . number_format($price, 2) . (($settings['site_currency_symbol_position'] == "post") ? $settings['site_currency_symbol'] : '');
    }

    public function currencySymbol()
    {
        $settings = Utility::settings();

        return $settings['site_currency_symbol'];
    }

    public static function dateFormat($date)
    {
        $settings = Utility::settings();

        return date($settings['site_date_format'], strtotime($date));
    }

    public function timeFormat($time)
    {
        $settings = Utility::settings();

        return date($settings['site_time_format'], strtotime($time));
    }

    public static function estimateNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["estimate_prefix"] . sprintf("%05d", $number);
    }

    public static function invoiceNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["invoice_prefix"] . sprintf("%05d", $number);
    }
    public function contractNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["contract_prefix"] . sprintf("%05d", $number);
    }
    public function unread()
    {
        return Messages::where('from', '=', $this->id)->where('is_read', '=', 0)->count();
    }

        public function assignPlan($planID)
        {
            $plan = Plan::find($planID);
            if ($plan) {
                $this->plan = $plan->id;
                if($this->trial_expire_date != null);
                {
                    $this->trial_expire_date = null;
                }
                if ($plan->duration == 'month') {
                    $this->plan_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
                } elseif ($plan->duration == 'year') {
                    $this->plan_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
                } else {
                    $this->plan_expire_date = null;
                }
                $this->save();

                $employees = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->get();
                $clients   = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'client')->get();


                if ($plan->max_employee == -1) {
                    foreach ($employees as $employee) {
                        $employee->is_active = 1;
                        $employee->save();
                    }
                } else {
                    $employeeCount = 0;
                    foreach ($employees as $employee) {
                        $employeeCount++;
                        if ($employeeCount <= $plan->max_employee) {
                            $employee->is_active = 1;
                            $employee->save();
                        } else {
                            $employee->is_active = 0;
                            $employee->save();
                        }
                    }
                }

                if ($plan->max_client == -1) {
                    foreach ($clients as $client) {
                        $client->is_active = 1;
                        $client->save();
                    }
                } else {
                    $clientCount = 0;
                    foreach ($clients as $client) {
                        $clientCount++;
                        if ($clientCount <= $plan->max_client) {
                            $client->is_active = 1;
                            $client->save();
                        } else {
                            $client->is_active = 0;
                            $client->save();
                        }
                    }
                }

                return ['is_success' => true];
            } else {
                return [
                    'is_success' => false,
                    'error' => 'Plan is deleted.',
                ];
            }
        }

    public function countCompany()
    {
        return User::where('type', '=', 'company')->where('created_by', '=', $this->creatorId())->count();
    }

    public function countPaidCompany()
    {
        return User::where('type', '=', 'company')->whereNotIn(
            'plan',
            [
                0,
                1,
            ]
        )->where('created_by', '=', \Auth::user()->id)->count();
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\Invoice', 'client', 'id');
    }

    public function countEmployees($id)
    {
        return User::where('type', 'employee')->where('created_by', $id)->count();
    }

    public function countClients($id)
    {
        return User::where('type', 'client')->where('created_by', $id)->count();
    }

    public function currentPlan()
    {
        return $this->hasOne('App\Models\Plan', 'id', 'plan');
    }

    public function userDefaultData()
    {
        // dd($this);
        $id       = $this->id;
        $pipeline = Pipeline::create(
            [
                'name' => 'Sales',
                'created_by' => $id,
            ]
        );


        // Default Lead Stages
        $lead_stages = [
            'Draft',
            'Sent',
            'Declined',
            'Accepted',
        ];
        foreach ($lead_stages as $k => $lead_stage) {
            LeadStage::create(
                [
                    'name' => $lead_stage,
                    'pipeline_id' => $pipeline->id,
                    'order' => $k,
                    'created_by' => $id,
                ]
            );
        }

        // Default Deal Stages
        $stages = [
            'Initial Contact',
            'Qualification',
            'Meeting',
            'Close',
        ];
        foreach ($stages as $k => $stage) {
            DealStage::create(
                [
                    'name' => $stage,
                    'pipeline_id' => $pipeline->id,
                    'order' => $k,
                    'created_by' => $id,
                ]
            );
        }


        // End Default Lead Stages

        // Label
        $labels = [
            'New Deal' => 'danger',
            'Idea' => 'warning',
            'Appointment' => 'primary',
        ];
        foreach ($labels as $label => $color) {
            Label::create(
                [
                    'name' => $label,
                    'color' => $color,
                    'pipeline_id' => $pipeline->id,
                    'created_by' => $id,
                ]
            );
        }

        // Source
        $sources = [
            'Website',
            'Organic',
            'Call',
            'Social Media',
            'Email Campaign',
        ];
        foreach ($sources as $source) {
            Source::create(
                [
                    'name' => $source,
                    'created_by' => $id,
                ]
            );
        }

        // Payment
        $payments = [
            'Cash',
            'Bank',
        ];
        foreach ($payments as $payment) {
            PaymentMethod::create(
                [
                    'name' => $payment,
                    'created_by' => $id,
                ]
            );
        }

        // Salary Type
        $salaryTypes = [
            'Monthly',
        ];
        foreach ($salaryTypes as $salaryType) {
            SalaryType::create(
                [
                    'name' => $salaryType,
                    'created_by' => $id,
                ]
            );
        }

        // Leave Type
        $leaveTypes = [
            'Casual Leave',
            'Medical Leave',
        ];
        foreach ($leaveTypes as $leaveType) {
            LeaveType::create(
                [
                    'title' => $leaveType,
                    'days' => 12,
                    'created_by' => $id,
                ]
            );
        }

        // Project Stages
        $projectStages = [
            'To Do',
            'In Progress',
            'Bug',
            'Done',
        ];
        $colors        = [
            '#4a7123',
            '#698f24',
            '#bb7c7c',
            '#c8b53c',
        ];
        foreach ($projectStages as $k => $projectStage) {
            ProjectStage::create(
                [
                    'name' => $projectStage,
                    'order' => $k,
                    'color' => $colors[$k],
                    'created_by' => $id,
                ]
            );
        }


        // Make Entry In User_Email_Template
        $allEmail = EmailTemplate::all();
        foreach ($allEmail as $email) {
            UserEmailTemplate::create(
                [
                    'template_id' => $email->id,
                    'user_id' => $id,
                    'is_active' => 1,
                ]
            );
        }
    }

    public function journalNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["journal_prefix"] . sprintf("%05d", $number);
    }


    // For Email template Module

    public function defaultEmail()
    {
        // Email Template
        $emailTemplate = [
            'New User',
            'Lead Assigned',
            'Deal Assigned',
            'Estimation Sent',
            'New Project',
            'Project Assigned',
            'Project Finished',
            'Task Assigned',
            'Invoice Sent',
            'Invoice Payment Recorded',
            'New Credit Note',
            'New Support Ticket',
            'New Contract',
        ];

        foreach ($emailTemplate as $eTemp) {

            EmailTemplate::create(
                [
                    'name' => $eTemp,
                    'from' => env('APP_NAME'),
                    'slug' => strtolower(str_replace(' ', '_', $eTemp)),
                    'created_by' => 1,
                ]
            );
        }

        $defaultTemplate = [
            'new_user' => [
                'subject' => 'New User',
                'lang' => [
                    'ar' => '<p>مرحبا،&nbsp;<br>مرحبا بك في {app_name}.</p><p><b>البريد الإلكتروني </b>: {email}<br><b>كلمه السر</b> : {password}</p><p>{app_url}</p><p>شكر،<br>{app_name}</p>',
                    'da' => '<p>Hej,&nbsp;<br>Velkommen til {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Adgangskode</b> : {password}</p><p>{app_url}</p><p>Tak,<br>{app_name}</p>',
                    'de' => '<p>Hallo,&nbsp;<br>Willkommen zu {app_name}.</p><p><b>Email </b>: {email}<br><b>Passwort</b> : {password}</p><p>{app_url}</p><p>Vielen Dank,<br>{app_name}</p>',
                    'en' => '<p>Hello,&nbsp;<br>Welcome to {app_name}.</p><p><b>Email </b>: {email}<br><b>Password</b> : {password}</p><p>{app_url}</p><p>Thanks,<br>{app_name}</p>',
                    'es' => '<p>Hola,&nbsp;<br>Bienvenido a {app_name}.</p><p><b>Correo electrónico </b>: {email}<br><b>Contraseña</b> : {password}</p><p>{app_url}</p><p>Gracias,<br>{app_name}</p>',
                    'fr' => '<p>Bonjour,&nbsp;<br>Bienvenue à {app_name}.</p><p><b>Email </b>: {email}<br><b>Mot de passe</b> : {password}</p><p>{app_url}</p><p>Merci,<br>{app_name}</p>',
                    'it' => '<p>Ciao,&nbsp;<br>Benvenuto a {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Parola d\'ordine</b> : {password}</p><p>{app_url}</p><p>Grazie,<br>{app_name}</p>',
                    'ja' => '<p>こんにちは、&nbsp;<br>へようこそ {app_name}.</p><p><b>Eメール </b>: {email}<br><b>パスワード</b> : {password}</p><p>{app_url}</p><p>おかげで、<br>{app_name}</p>',
                    'nl' => '<p>Hallo,&nbsp;<br>Welkom bij {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Wachtwoord</b> : {password}</p><p>{app_url}</p><p>Bedankt,<br>{app_name}</p>',
                    'pl' => '<p>Witaj,&nbsp;<br>Witamy w {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Hasło</b> : {password}</p><p>{app_url}</p><p>Dzięki,<br>{app_name}</p>',
                    'ru' => '<p>Привет,&nbsp;<br>Добро пожаловать в {app_name}.</p><p><b>Электронное письмо </b>: {email}<br><b>пароль</b> : {password}</p><p>{app_url}</p><p>Спасибо,<br>{app_name}</p>',
                    'pt' => '<p>Olá,<br>Bem-vindo ao {app_name}.</p><p><b>E-mail </b>: {email}<br><b>Senha</b> : {password}</p><p>{app_url}</p><p>Obrigada,<br>{app_name}</p>',
                    'zh' => "<p>您好，<br>欢迎访问 {app_name}。</p><p><b>电子邮件 </b>: {email}<br><b>密码</b> : {password}</p><p>{app_url}</p><p>谢谢，<br>{app_name}</p>",
                    'he' => '<p>שלום, &nbsp;<br>ברוכים הבאים אל {app_name}.</p><p><b>דואל </b>: {דואל}<br><b>סיסמה</b> : {password}</p><p>{app_url}</p><p>תודה,<br>{app_name}</p>',
                    'tr' => '<p>Merhaba, &nbsp;<br>{ app_name } olanağına hoş geldiniz.</p><p><b>E-posta </b>: { email }<br><b>Parola</b> : { password }</p><p>{ app_url }</p><p>Teşekkürler,<br>{ app_name }</p>',
                    'pt-br' => '<p>Olá, &nbsp;<br>Bem-vindo a {app_name}.</p><p><b>Email </b>: {email}<br><b>Senha</b> : {password}</p><p>{app_url}</p><p>Obrigado,<br>{app_name}</p>',
                ],
            ],
            'lead_assigned' => [
                'subject' => 'Lead Assigned',
                'lang' => [
                    'ar' => '<p>مرحبا</p>
                    <p>، الرصاص الجديد تم تعيين لك.</p>
                    <p>اسم الرصاص : { lead_name }</p>
                    <p>Lead Email : { adlead_email }</p>
                    <p>Lead Pipeline : { lead_bibad }</p>
                    <p>Lead Pالمرحلة : { lead_materge }</p>
                    <p>&nbsp;</p>
                    <p>موضوع الرصاص : { lead_subject }</p>',
                    'da' => '<p>Hallo,</p>
                    <p>New Lead er blevet tilknyt til dig.</p>
                    <p>Fornavn: { lead_name }</p>
                    <p>lead-e-mail: { lead_email }</p>
                    <p>Lead Pipeline: { lead_pipeline }</p>
                    <p>Lead Stage: { lead_stage }</p>
                    <p>Fors&oslash;gsemne: { lead_subject }</p>',
                    'de' => '<p>Hallo, New Lead wurde Ihnen zugeh&ouml;rt.</p>
                    <p>Leitungsname: {lead_name}</p>
                    <p>Lead-E-Mail: {lead_email}</p>
                    <p>Vorlauf-Pipeline: {lead_pipeline}</p>
                    <p>Vorlauf-Stage: {lead_stage}</p>
                    <p>&nbsp;</p>
                    <p>Lead Subject: {lead_subject}</p>',
                    'en' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Hello,</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">New Lead has been Assign to you.</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Name : {lead_name}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Email : {lead_email}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Pipeline : {lead_pipeline}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Stage : {lead_stage}</span></p>
                    <p><span style="background-color: #f8f8f8; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures;">Lead Subject: {lead_subject}</span></p>',
                    'es' => '<p>Hola,</p>
                    <p>New Lead ha sido Assign to you.</p>
                    <p>Nombre principal: {lead_name}</p>
                    <p>Correo electr&oacute;nico principal: {lead_email}</p>
                    <p>Conducto Principal: {lead_pipeline}</p>
                    <p>Etapa Principal: {lead_stage}</p>
                    <p>&nbsp;</p>
                    <p>Asunto principal: {lead_subject}</p>',
                    'fr' => '<p>Bonjour,</p>
                    <p>New Lead vous a &eacute;t&eacute; affect&eacute;.</p>
                    <p>Nom du responsable: { lead_name }</p>
                    <p>Lead Email: { lead_email }</p>
                    <p>Lead Pipeline: { lead_pipeline }</p>
                    <p>Lead Stage: { lead_stage }</p>
                    <p>&nbsp;</p>
                    <p>Objet responsable: { lead_subject }</p>',
                    'it' => '<p>Ciao,</p>
                    <p>New Lead &egrave; stata Assign a te.</p>
                    <p>Lead Name: {lead_name}</p>
                    <p>Lead Email: {lead_email}</p>
                    <p>Lead Pipeline: {lead_pipeline}</p>
                    <p>Lead Stage: {lead_stage}</p>
                    <p>&nbsp;</p>
                    <p>Oggetto principale: {lead_subject}</p>',
                    'ja' => '<p>こんにちは、</p>
                    <p>新しいリードがお客様に割り当てられています。</p>
                    <p>リード名 : {lead_name}</p>
                    <p>リード E メール : {lead_email}</p>
                    <p>リード・パイプライン : &nbsp;{lead_pipeline}</p>
                    <p>リード・ステージ : {lead_stage}</p>
                    <p>&nbsp;</p>
                    <p>リード・サブジェクト: {lead_subject}</p>',
                    'nl' => '<p>Hallo,</p>
                    <p>New Lead is aan u toegewezen.</p>
                    <p>Lead Name: { lead_name }</p>
                    <p>Lead Email: { lead_email }</p>
                    <p>Lead Pipeline: { lead_pipeline }</p>
                    <p>Lead Stage: { lead_stage }</p>
                    <p>&nbsp;</p>
                    <p>Hoofdeonderwerp: { lead_subject }</p>',
                    'pl' => '<p>Witaj,</p>
                    <p>nowy kierownik został przypisany do Ciebie.</p>
                    <p>Nazwa wiodącego: {lead_name }</p>
                    <p>Lead Email: {lead_email }</p>
                    <p>Lead Pipeline: {lead_pipeline }</p>
                    <p>Lead Stage: {lead_stage }</p>
                    <p>&nbsp;</p>
                    <p>Podmiot wiodający: {lead_subject }</p>',
                    'ru' => '<p>Привет,</p>
                    <p>Новый Свинец тебе назначили.</p>
                    <p>Имя ведущего: { lead_name }</p>
                    <p>Ведущий адрес электронной почты: { lead_email }</p>
                    <p>Ведущий конвейер: { lead_pipeline }</p>
                    <p>Ведущий Этап: { lead_stage }</p>
                    <p>&nbsp;</p>
                    <p>Ведущий субъект: { lead_subject }</p>',
                    'pt' => '<p>Ol&aacute;,</p>
                    <p>Nova L&iacute;der tem sido Assign para voc&ecirc;.</p>
                    <p>Nome do L&iacute;der: {lead_name}</p>
                    <p>Lead Email: {lead_email}</p>
                    <p>Lead Pipeline: {lead_pipeline}</p>
                    <p>Lead Stage: {lead_stage}</p>
                    <p>&nbsp;</p>
                    <p>Assunto do lead: {lead_subject}</p>',
                    'zh' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Hello，</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" />sofo style="color: #1d1c1d; font-family: slack-lato， slack-馏分， appleLogo， sans-serif; font-size: 15px; font-variant-ligatures: 公共-结扎 ; 背景色: #f8f8f8;">新商机已分配给您。</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">商机名称 : {lead_name}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">商机电子邮件 : {lead_email}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">商机管道 : {lead_pipeline}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">商机阶段 : {lead_stage}</span></p> <p><span style="background-color: #f8f8f8; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures;">商机主题: {lead_subject}</span></p>',
                    'he' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">שלום,</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span סגנון = " צבע: #1d1c1d; משפחת פונט: Sמחסור-Lato, Slack-Fractions, appleLogo, sans-serif; גודל גופן: 15px; גופן-variant-קשירה: צבע רקע משותף-צבע: #f8f8f8"> {} ביצוע חדש הוקצה לכם.</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">שם ביצוע: {lead_name}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">דואל מוביל: {lead_ייל}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">צינור עופרת: {lead_הצינור}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">שלב ביצוע: {lead_stage}</span></p> <p><span style="background-color: #f8f8f8; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures;">נושא ביצוע: {lead_subject}</span></p>',
                    'tr' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Merhaba,</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style = " color: #1d1c1d; font-family: Smack-Lapo, Seksiks-Frations, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;"> Yeni Lider size Atanır.</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Ön Ad: { lead_name }</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Email: { lead_email }</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Liderlik Boru Hattı: { lead_pipeline }</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Ön Aşama: { lead_stage }</span></p>
                    <p><span style="background-color: #f8f8f8; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures;">Lider Konu: { lead_subject }</span></p>',
                    'pt-br' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Olá,</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style = " color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variante-ligaduras: common-ligatures; background-color: #f8f8f8;"> Nova Lead foi Assign to you.</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Nome do Lead: {lead_name}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Email: {lead_email}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Lead Pipeline: {lead_pipeline}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estágio de Lead: {lead_stage}</span></p> <p><span style="background-color: #f8f8f8; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures;">Sujeito do Lead: {lead_subject}</span></p>',
                ],
            ],
            'deal_assigned' => [
                'subject' => 'Deal Assigned',
                'lang' => [
                    'ar' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">مرحبا،</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">تم تعيين صفقة جديدة لك.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">اسم الصفقة</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">خط أنابيب الصفقة</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">مرحلة الصفقة</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">حالة الصفقة</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">سعر الصفقة</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'da' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hej,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal er blevet tildelt til dig.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Deal Navn</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Deal Fase</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Deal status</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Deal pris</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'de' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hallo,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal wurde Ihnen zugewiesen.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Geschäftsname</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Deal Stage</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Deal Status</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Ausgehandelter Preis</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'en' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hello,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal has been Assign to you.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Deal Name</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Deal Stage</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Deal Status</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Deal Price</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'es' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hola,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal ha sido asignado a usted.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nombre del trato</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Tubería de reparto</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Etapa de reparto</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Estado del acuerdo</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Precio de oferta</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'fr' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Bonjour,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">Le New Deal vous a été attribué.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nom de l\'accord</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Pipeline de transactions</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Étape de l\'opération</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Statut de l\'accord</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Prix ​​de l\'offre</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'it' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Ciao,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal è stato assegnato a te.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nome dell\'affare</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Pipeline di offerte</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Stage Deal</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Stato dell\'affare</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Prezzo dell\'offerta</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'ja' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">こんにちは、</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">新しい取引が割り当てられました。</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">取引名</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">取引パイプライン</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">取引ステージ</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">取引状況</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">取引価格</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'nl' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hallo,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal is aan u toegewezen.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Dealnaam</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Deal Stage</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Dealstatus</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Deal prijs</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'pl' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Witaj,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">Umowa została przeniesiona {deal_old_stage} do&nbsp; {deal_new_stage}.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nazwa oferty</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Etap transakcji</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Status oferty</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Cena oferty</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'ru' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Привет,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">Сделка была перемещена из {deal_old_stage} в&nbsp; {deal_new_stage}.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Название сделки</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Трубопровод сделки</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Этап сделки</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Статус сделки</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Цена сделки</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'pt' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Olá,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">Сделка была перемещена из {deal_old_stage} в&nbsp; {deal_new_stage}.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nome do negócio</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Pipeline de negócios</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Estágio do negócio</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Status da transação</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Preço da oferta</span>&nbsp;: {deal_price}</span></p><p></p>',
                    'zh' => '<p style="line-height: 28px; font-family: Nunito, "Segoe UI", arial; font-size: 14px;"><span style="font-family: sans-serif;">你好，< /span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">新优惠已分配给您。</span></p>< p style="line-height: 28px; font-family: Nunito, "Segoe UI", arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style= "font-weight: BOLDER;">交易名称</span>：{deal_name}<br><span style="font-weight：bolder;">交易渠道</span>：{deal_pipeline}<br><span style="font-weight: BOLDER;">交易阶段</span>：{deal_stage}<br><span style="font-weight：bolder;">交易状态</span>：{deal_status}<br> <span style="font-weight: Bolder;">交易价格</span>：{deal_price}</span></p><p></p>',
                    'he' => '<p style="line-height: 28px; font-family: Nunito, "Segoe UI", arial; font-size: 14px;"><span style="font-family: sans-serif;">שלום,< /span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">דיל חדש הוקצה לך.</span></p>< p style="line-height: 28px; font-family: Nunito, "Segoe UI", arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style= "font-weight: bolder;">שם העסקה</span> : {deal_name}<br><span style="font-weight: bolder;">צינור העסקה</span> : {deal_pipeline}<br><span style="font-weight: bolder;">שלב העסקה</span> : {deal_stage}<br><span style="font-weight: bolder;">סטטוס העסקה</span> : {deal_status}<br> <span style="font-weight: bolder;">מחיר מבצע</span> : {deal_price}</span></p><p></p>',
                    'tr' => '<p style="line-height: 28px; font-family: Nunito, "Segoe UI", arial; font-size: 14px;"><span style="font-family: sans-serif;">Merhaba,< /span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">Size Yeni Anlaşma Atandı.</span></p>< p style="line-height: 28px; font-family: Nunito, "Segoe UI", arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style= "font-weight: bolder;">Anlaşma Adı</span> : {deal_name}<br><span style="font-weight: bolder;">Anlaşma Hattı</span> : {deal_pipeline}<br><span style="font-weight: bolder;">Anlaşma Aşaması</span> : {deal_stage}<br><span style="font-weight: bolder;">Anlaşma Durumu</span> : {deal_status}<br> <span style="font-weight: bolder;">Anlaşma Fiyatı</span> : {deal_price}</span></p><p></p>',
                    'pt-br' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Olá,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">Сделка была перемещена из {deal_old_stage} в&nbsp; {deal_new_stage}.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Nome do negócio</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Pipeline de negócios</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Estágio do negócio</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Status da transação</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Preço da oferta</span>&nbsp;: {deal_price}</span></p><p></p>',
                ],
            ],
            'estimation_sent' => [
                'subject' => 'Estimation Sent”',
                'lang' => [
                    'ar' => '<p>مرحبا ، التقدير الجديد تم تعيينه لك.</p>
                    <p>الاسم التقديري : {estimation_id}</p>
                    <p>تقدير الوحدة التابعة : &nbsp;{estimation_client}</p>
                    <p>تقدير التصنيف : {estimation_category}</p>
                    <p>تاريخ اصدار التقدير : {estimation_issue_date}</p>
                    <p>تاريخ انتهاء الصلاحية : {estimation_expiry_date}</p>
                    <p>حالة التقدير : &nbsp;{estimation_status}</p>',
                    'da' => '<p>Hallo,</p>
                    <p>nyt estimat er blevet tilknyt til dig.</p>
                    <p>Estimatnavn: { estimation_id }</p>
                    <p>estimat: { estimation_client }</p>
                    <p>Estimatkategori: { estimation_category }</p>
                    <p>Estimatudstedelsesdato: { estimation_issue_date }</p>
                    <p>Estimeret udl&oslash;bsdato: { estimation_expiry_date }</p>
                    <p>Estimeringsstatus: { estimation_status }</p>',
                    'de' => '<p>Hallo,</p>
                    <p>New Estimation hat Ihnen zugeh&ouml;rt.</p>
                    <p>Sch&auml;tzname: {estimation_id}</p>
                    <p>Estimation Client: {estimation_client}</p>
                    <p>Sch&auml;tzungskategorie: {estimation_category}</p>
                    <p>Sch&auml;tzung Ausgabedatum: {estimation_issue_date}</p>
                    <p>Absch&auml;tzungstermin: {estimation_expiry_date}</p>
                    <p>Sch&auml;tzungsstatus: {estimation_status}</p>',
                    'en' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Hello,</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">New Estimation has been Assign to you.</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Name: {estimation_id}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Client : {estimation_client}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Category : {estimation_category}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Issue Date : {estimation_issue_date}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Expiry Date : {estimation_expiry_date}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Estimation Status : {estimation_status}</span></p>
                     <p>&nbsp;</p>',
                    'es' => '<p>Hola,</p>
                    <p>New Estimation has been Assign to you.</p>
                    <p>Nombre de estimaci&oacute;n: {estimation_id}</p>
                    <p>Cliente de estimaci&oacute;n: {estimation_client}</p>
                    <p>Categor&iacute;a de estimaci&oacute;n: {estimation_category}</p>
                    <p>Fecha de emisi&oacute;n de estimaci&oacute;n: {estimation_issue_date}</p>
                    <p>Fecha de caducidad de estimaci&oacute;n: {estimation_expiry_date}</p>
                    <p>Estado de estimaci&oacute;n: {estimation_status}</p>',
                    'fr' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-size: 14px; font-family: sans-serif;">Bonjour,</span><br style="font-size: 14px; font-family: sans-serif;"><span style="font-size: 14px; font-family: sans-serif;">Une nouvelle estimation vous a été attribuée.</span><br></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Nom de l\'estimation: {estimation_id}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Estimation Client&nbsp;: {estimation_client}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Catégorie d\'estimation&nbsp;: {estimation_category}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Date d\'émission de l\'estimation&nbsp;: {estimation_issue_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Date d\'expiration de l\'estimation&nbsp;: {estimation_expiry_date}<br>Statut d\'estimation&nbsp;: {estimation_status}</p><p></p>',
                    'it' => '<p>Ciao,</p>
                    <p>Nuova stima &egrave; stata Assign a te.</p>
                    <p>Nome di stima: {estimation_id}</p>
                    <p>Estimation Client: {estimation_client}</p>
                    <p>Stima Categoria: {estimation_category}</p>
                    <p>Stima Issue Date: &nbsp;{estimation_issue_date}</p>
                    <p>Stima Data di scadenza: {estimation_expiry_date}</p>
                    <p>Stato di stima: {estimation_status}</p>',
                    'ja' => '<p>こんにちは、</p>
                    <p>新しい見積もりがお客様に割り当てられています。</p>
                    <p>見積もり名: &nbsp;{estimation_id}</p>
                    <p>見積もりクライアント : {estimation_client}</p>
                    <p>見積もりカテゴリー : {estimation_category}</p>
                    <p>見積もり発行日 : {estimation_issue_date}</p>
                    <p>見積もりの有効期間 : {estimation_expiry_date}</p>
                    <p>エスティマtion_status : {estimation_status}</p>',
                    'nl' => '<p>Hallo,</p>
                    <p>Nieuwe schatting is aan u toegewezen.</p>
                    <p>Schattenaam: { estimation_id }</p>
                    <p>Estimatie Client: { estimation_client }</p>
                    <p>Schatting Categorie: { estimation_categorie }</p>
                    <p>Schattingsdatum: { estimation_issue_date }</p>
                    <p>Schatting Vervaldatum: { estimation_expiry_date }</p>
                    <p>Schattingsstatus: { estimation_status }</p>',
                    'pl' => '<p>Witaj,</p>
                    <p>Nowa estymacja została przypisana do Ciebie.</p>
                    <p>Nazwa oszacowania: {estimation_id}</p>
                    <p>Estymacja klienta: {estimation_client}</p>
                    <p>Kategoria estymacji: {estimation_category}</p>
                    <p>Szacowana data wydania: {estimation_issue_date}</p>
                    <p>Data wygaśnięcia szacowania: {estimation_expiry_date}</p>
                    <p>Status szacowania: {estimation_status}</p>',
                    'ru' => '<p>Здравствуйте,</p>
                    <p>вам назначили новую оценку.</p>
                    <p>Имя оценки: {estimation_id}</p>
                    <p>Клиент оценки: {estimation_client}</p>
                    <p>Категория оценки: {estimation_category}</p>
                    <p>Дата выдачи оценки: &nbsp;{estimation_issue_date}</p>
                    <p>Дата истечения срока действия оценки: {estimation_expiry_date}</p>
                    <p>Состояние оценки: {estimation_status}</p>',
                    'pt' => '<p>Ol&aacute;,</p>
                    <p>Nova Estima&ccedil;&atilde;o tem sido Assign para voc&ecirc;.</p>
                    <p>Estimation Name: {estimation_id}</p>
                    <p>Estimation Client: {estimation_client}</p>
                    <p>Estimation Category: {estimation_category}</p>
                    <p>Estimation Issue Data: {estimation_issue_date}</p>
                    <p>Data de expira&ccedil;&atilde;o da estimativa: {estimation_expiry_date}</p>
                    <p>Status da Estimation: {estimation_status}</p>',
                    'zh' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; 背景颜色: #f8f8f8;">你好，</span><br style="box-sizing:继承;颜色:#1d1c1d;字体系列:Slack-Lato,Slack-Fractions,appleLogo,sans-serif;字体大小: 15px；字体变体连字：通用连字；背景颜色：#f8f8f8；" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; 背景颜色: #f8f8f8;">新的估算已分配给您。</span><br style="box-sizing:继承;颜色:#1d1c1d;font-family:Slack-Lato, Slack-Fractions, appleLogo, sans-serif ；字体大小：15px；字体变体连字：通用连字；背景颜色：#f8f8f8；" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; 背景颜色: #f8f8f8;">估算名称：{estimation_id}</span><br style="box-sizing:继承；颜色：#1d1c1d；字体系列：Slack-Lato、Slack-Fractions、appleLogo、sans-serif；字体-大小：15px；字体变体连字：通用连字；背景颜色：#f8f8f8；” /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; 背景颜色: #f8f8f8;">估算客户端：{estimation_client}</span><br style="box-sizing:继承;颜色:#1d1c1d;font-family:Slack-Lato, Slack-Fractions, appleLogo, sans-serif; 字体-大小：15px；字体变体连字：通用连字；背景颜色：#f8f8f8；” /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; 背景颜色: #f8f8f8;">估算类别：{estimation_category}</span><br style="box-sizing:继承;颜色:#1d1c1d;font-family:Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font -大小：15px；字体变体连字：通用连字；背景颜色：#f8f8f8；” /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; 背景颜色: #f8f8f8;">预计发布日期：{estimation_issue_date}</span><br style="box-sizing:继承;颜色:#1d1c1d;font-family:Slack-Lato, Slack-Fractions, appleLogo, sans-serif;字体大小：15px；字体变体连字：通用连字；背景颜色：#f8f8f8；” /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; 背景颜色: #f8f8f8;">预计到期日期：{estimation_expiry_date}</span><br style="box-sizing:继承;颜色:#1d1c1d;font-family:Slack-Lato, Slack-Fractions, appleLogo, sans-serif;字体大小：15px；字体变体连字：通用连字；背景颜色：#f8f8f8；” /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; 背景颜色: #f8f8f8;">估计状态：{estimation_status}</span></p>
                     <p> </p>',
                    'he' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color : #f8f8f8;">שלום,</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;">אומדן חדש הוקצה לך.</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif ; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;">שם אומדן: {estimation_id}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font -גודל: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;">לקוח אומדן: {estimation_client}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font -גודל: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;">קטגוריית אומדן : {estimation_category}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font -גודל: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;">תאריך הנפקת אומדן: {estimation_issue_date}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;">תאריך תפוגה אומדן: {estimation_expiry_date}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; רקע-צבע: #f8f8f8;">סטטוס אומדן : {estimation_status}</span></p>
                     <p> </p>',
                    'tr' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-bitişik harfler: ortak bitişik harfler; arka plan rengi : #f8f8f8;">Merhaba,</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15 piksel; yazı tipi varyantı bitişik harfler: ortak bitişik harfler; arka plan rengi: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Yeni Tahmin Size Atandı.</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif ; yazı tipi boyutu: 15 piksel; yazı tipi varyantı bitişik harfler: ortak bitişik harfler; arka plan rengi: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Tahmin Adı: {estimation_id}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font -boyut: 15 piksel; yazı tipi varyantı bitişik harfler: ortak bitişik harfler; arka plan rengi: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Tahmin İstemcisi: {estimation_client}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font -boyut: 15 piksel; yazı tipi varyantı bitişik harfler: ortak bitişik harfler; arka plan rengi: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Tahmin Kategorisi : {estimation_category}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font -boyut: 15 piksel; yazı tipi varyantı bitişik harfler: ortak bitişik harfler; arka plan rengi: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Tahmin Yayın Tarihi : {estimation_issue_date}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; yazı tipi boyutu: 15 piksel; yazı tipi varyantı bitişik harfler: ortak bitişik harfler; arka plan rengi: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Tahmin Son Kullanma Tarihi : {estimation_expiry_date}</span><br style="box-sizing: inherit; color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; yazı tipi boyutu: 15 piksel; yazı tipi varyantı bitişik harfler: ortak bitişik harfler; arka plan rengi: #f8f8f8;" /><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: #f8f8f8;">Tahmin Durumu : {estimation_status}</span></p>
                     <p> </p>',
                    'pt-br' => '<p>Ol&aacute;,</p>
                    <p>Nova Estima&ccedil;&atilde;o tem sido Assign para voc&ecirc;.</p>
                    <p>Estimation Name: {estimation_id}</p>
                    <p>Estimation Client: {estimation_client}</p>
                    <p>Estimation Category: {estimation_category}</p>
                    <p>Estimation Issue Data: {estimation_issue_date}</p>
                    <p>Data de expira&ccedil;&atilde;o da estimativa: {estimation_expiry_date}</p>
                    <p>Status da Estimation: {estimation_status}</p>',
                ],
            ],
            'new_project' => [
                'subject' => 'New Project',
                'lang' => [
                    'ar' => '<p>مرحبا&nbsp;{project_client},</p><p>تم تعيين مشروع جديد لك.</p><p>عنوان المشروع<strong>:</strong>&nbsp;{project_title}</p><p>تاريخ بدء المشروع<strong>:</strong>&nbsp;{project_start_date}</p><p>تاريخ استحقاق المشروع:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>نحن نتطلع إلى الاستماع منك.<br><br>أطيب التحيات,<br>{app_name}</p><p></p>',
                    'da' => '<p>Hej&nbsp;{project_client},</p><p>Nyt projekt er tildelt dig.<br><br><b>Projekt titel:</b>&nbsp;{project_title}</p><p><b>Projektets startdato</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Projektets forfaldsdato</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Vi glæder os til at høre fra dig.<br><br><b>Med venlig hilsen</b>,<br>{app_name}</p><p></p>',
                    'de' => '<p>Hallo&nbsp;{project_client},</p><p>Ihnen wird ein neues Projekt zugewiesen.<br><br><b>Projekttitel:</b>&nbsp;{project_title}</p><p><b>Projektstartdatum</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Projektfälligkeit</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Wir freuen uns von Ihnen zu hören.<br><br><b>Mit freundlichen Grüßen</b>,<br>{app_name}</p><p></p>',
                    'en' => '<p>Hello&nbsp;{project_client},</p>
                    <p>Hello nice to meet you.</p>
                    <p>New project is assigned to you.</p>
                    <p><br /><strong>Project Title:</strong>&nbsp;{project_title}</p>
                    <p><strong>Project Start Date:</strong>&nbsp;{project_start_date}</p>
                    <p><strong>Project Due Date</strong>:&nbsp;{project_due_date}</p>
                    <p>We are looking forward hearing from you.<br /><br />Kind Regards,<br />{app_name}</p>
                    <p>&nbsp;</p>',
                    'es' => '<p>Hola&nbsp;{project_client},</p><p>Se te ha asignado un nuevo proyecto.<br><br><b>Título del Proyecto:</b>&nbsp;{project_title}</p><p><b>Fecha de inicio del proyecto</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Fecha de vencimiento del proyecto</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Esperamos tener noticias tuyas.<br><br><b>Saludos cordiales</b>,<br>{app_name}</p><p></p>',
                    'fr' => '<p>Bonjour&nbsp;{project_client},</p><p>Un nouveau projet vous est attribué.<br><br><b>Titre du projet:</b>&nbsp;{project_title}</p><p><b>Date de début du projet</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Date d\'échéance du projet</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Nous sommes impatients de vous entendre.<br><br><b>Sincères amitiés</b>,<br>{app_name}</p><p></p>',
                    'it' => '<p>Ciao&nbsp;{project_client},</p><p>Ti viene assegnato un nuovo progetto.<br><br><b>titolo del progetto:</b>&nbsp;{project_title}</p><p><b>Data di inizio del progetto</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Data di scadenza del progetto</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Non vediamo l\'ora di sentirti.<br><br><b>Cordiali saluti</b>,<br>{app_name}</p><p></p>',
                    'ja' => '<p>こんにちは&nbsp;{project_client},</p><p>新しいプロジェクトがあなたに割り当てられます.<br><br>プロジェクト名<b>:</b>&nbsp;{project_title}</p><p>プロジェクト開始日<strong>:</strong>&nbsp;{project_start_date}</p><p>プロジェクトの期日:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>ご連絡をお待ちしております。.<br><br>敬具,<br>{app_name}</p><p></p>',
                    'nl' => '<p>Hallo&nbsp;{project_client},</p><p>Er is een nieuw project aan u toegewezen.<br><br><b>project titel:</b>&nbsp;{project_title}</p><p><b>Startdatum van het project</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Project vervaldatum</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>We horen graag van je.<br><br><b>Vriendelijke groeten</b>,<br>{app_name}</p><p></p>',
                    'pl' => '<p>cześć&nbsp;{project_client},</p><p>Nowy projekt został Ci przypisany.<br><br><b>Tytuł Projektu:</b>&nbsp;{project_title}</p><p><b>Data rozpoczęcia projektu</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Termin realizacji projektu</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Czekamy na wiadomość od Ciebie.<br><br><b>Z poważaniem</b>,<br>{app_name}</p><p></p>',
                    'ru' => '<p>Здравствуйте&nbsp;{project_client},</p><p>Вам назначен новый проект.<br><br>Название Проекта<b>:</b>&nbsp;{project_title}</p><p>Дата начала проекта<strong>:</strong>&nbsp;{project_start_date}</p><p>Срок выполнения проекта:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Мы с нетерпением ждем вашего ответа.<br><br>С уважением,<br>{app_name}</p><p></p>',
                    'pt' => '<p>Olá&nbsp;{project_client},</p><p>Novo projeto é atribuído a você.<br><br>título do projeto<b>:</b>&nbsp;{project_title}</p><p>Data de início do projeto<strong>:</strong>&nbsp;{project_start_date}</p><p>Data de vencimento do projeto:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Estamos ansiosos para ouvir de você.<br><br>С Atenciosamente,<br>{app_name}</p><p></p>',
                    'zh' => '<p>您好{project_client}，</p>
                    <p>你好，很高兴认识你。</p>
                    <p>已将新项目分配给您。</p>
                    <p><br /><strong>项目标题：</strong> {project_title}</p>
                    <p><strong>项目开始日期：</strong> {project_start_date}</p>
                    <p><strong>项目截止日期</strong>：{project_due_date}</p>
                    <p>我们期待您的回复。<br /><br />亲切的问候，<br />{app_name}</p>
                    <p> </p>',
                    'he' => '<p>שלום {project_client},</p>
                    <p>שלום נעים להכיר.</p>
                    <p>פרויקט חדש הוקצה לך.</p>
                    <p><br /><strong>כותרת הפרויקט:</strong> {project_title}</p>
                    <p><strong>תאריך תחילת הפרויקט:</strong> {project_start_date}</p>
                    <p><strong>תאריך יעד של פרויקט</strong>: {project_due_date}</p>
                    <p>אנו מצפים לשמוע ממך.<br /><br />בברכה,<br />{app_name}</p>
                    <p> </p>',
                    'tr' => '<p>Merhaba {project_client},</p>
                    <p>Merhaba, tanıştığımıza memnun oldum.</p>
                    <p>Size yeni proje atandı.</p>
                    <p><br /><strong>Proje Başlığı:</strong> {project_title}</p>
                    <p><strong>Proje Başlangıç ​​Tarihi:</strong> {project_start_date}</p>
                    <p><strong>Proje Bitiş Tarihi</strong>: {project_due_date}</p>
                    <p>Sizden haber bekliyoruz.<br /><br />Saygılarımızla,<br />{app_name}</p>
                    <p> </p>',
                    'pt-br' => '<p>Olá&nbsp;{project_client},</p><p>Novo projeto é atribuído a você.<br><br>título do projeto<b>:</b>&nbsp;{project_title}</p><p>Data de início do projeto<strong>:</strong>&nbsp;{project_start_date}</p><p>Data de vencimento do projeto:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Estamos ansiosos para ouvir de você.<br><br>С Atenciosamente,<br>{app_name}</p><p></p>',
                ],
            ],
            'project_assigned' => [
                'subject' => 'Project Assigned',
                'lang' => [
                    'ar' => '<p>مرحبا&nbsp;{project_assign_user},</p><p>تم تعيين مشروع جديد لك.</p><p>عنوان المشروع<strong>:</strong>&nbsp;{project_title}</p><p>تاريخ بدء المشروع<strong>:</strong>&nbsp;{project_start_date}</p><p>تاريخ استحقاق المشروع:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>نحن نتطلع إلى الاستماع منك.<br><br>أطيب التحيات,<br>{app_name}</p><p></p>',
                    'da' => '<p>Hej&nbsp;{project_assign_user},</p><p>Nyt projekt er tildelt dig.<br><br><b>Projekt titel:</b>&nbsp;{project_title}</p><p><b>Projektets startdato</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Projektets forfaldsdato</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Vi glæder os til at høre fra dig.<br><br><b>Med venlig hilsen</b>,<br>{app_name}</p><p></p>',
                    'de' => '<p>Hallo&nbsp;{project_assign_user},</p><p>Ihnen wird ein neues Projekt zugewiesen.<br><br><b>Projekttitel:</b>&nbsp;{project_title}</p><p><b>Projektstartdatum</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Projektfälligkeit</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Wir freuen uns von Ihnen zu hören.<br><br><b>Mit freundlichen Grüßen</b>,<br>{app_name}</p><p></p>',
                    'en' => '<p>Hello&nbsp;{project_assign_user},</p><p>New project is assigned to you.<br><br><strong>Project Title:</strong>&nbsp;{project_title}</p><p><strong>Project Start Date:</strong>&nbsp;{project_start_date}</p><p><b>Project Due Date</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>We are looking forward hearing from you.<br><br>Kind Regards,<br>{app_name}</p><p></p>',
                    'es' => '<p>Hola&nbsp;{project_assign_user},</p><p>Se te ha asignado un nuevo proyecto.<br><br><b>Título del Proyecto:</b>&nbsp;{project_title}</p><p><b>Fecha de inicio del proyecto</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Fecha de vencimiento del proyecto</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Esperamos tener noticias tuyas.<br><br><b>Saludos cordiales</b>,<br>{app_name}</p><p></p>',
                    'fr' => '<p>Bonjour&nbsp;{project_assign_user},</p><p>Un nouveau projet vous est attribué.<br><br><b>Titre du projet:</b>&nbsp;{project_title}</p><p><b>Date de début du projet</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Date d\'échéance du projet</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Nous sommes impatients de vous entendre.<br><br><b>Sincères amitiés</b>,<br>{app_name}</p><p></p>',
                    'it' => '<p>Ciao&nbsp;{project_assign_user},</p><p>Ti viene assegnato un nuovo progetto.<br><br><b>titolo del progetto:</b>&nbsp;{project_title}</p><p><b>Data di inizio del progetto</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Data di scadenza del progetto</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Non vediamo l\'ora di sentirti.<br><br><b>Cordiali saluti</b>,<br>{app_name}</p><p></p>',
                    'ja' => '<p>こんにちは&nbsp;{project_assign_user},</p><p>新しいプロジェクトがあなたに割り当てられます.<br><br>プロジェクト名<b>:</b>&nbsp;{project_title}</p><p>プロジェクト開始日<strong>:</strong>&nbsp;{project_start_date}</p><p>プロジェクトの期日:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>ご連絡をお待ちしております。.<br><br>敬具,<br>{app_name}</p><p></p>',
                    'nl' => '<p>Hallo&nbsp;{project_assign_user},</p><p>Er is een nieuw project aan u toegewezen.<br><br><b>project titel:</b>&nbsp;{project_title}</p><p><b>Startdatum van het project</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Project vervaldatum</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>We horen graag van je.<br><br><b>Vriendelijke groeten</b>,<br>{app_name}</p><p></p>',
                    'pl' => '<p>cześć&nbsp;{project_assign_user},</p><p>Nowy projekt został Ci przypisany.<br><br><b>Tytuł Projektu:</b>&nbsp;{project_title}</p><p><b>Data rozpoczęcia projektu</b><strong>:</strong>&nbsp;{project_start_date}</p><p><b>Termin realizacji projektu</b>:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Czekamy na wiadomość od Ciebie.<br><br><b>Z poważaniem</b>,<br>{app_name}</p><p></p>',
                    'ru' => '<p>Здравствуйте&nbsp;{project_assign_user},</p><p>Вам назначен новый проект.<br><br>Название Проекта<b>:</b>&nbsp;{project_title}</p><p>Дата начала проекта<strong>:</strong>&nbsp;{project_start_date}</p><p>Срок выполнения проекта:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Мы с нетерпением ждем вашего ответа.<br><br>С уважением,<br>{app_name}</p><p></p>',
                    'pt' => '<p>Olá&nbsp;{project_assign_user},</p><p>Novo projeto é atribuído a você.<br><br>título do projeto<b>:</b>&nbsp;{project_title}</p><p>Data de início do projeto<strong>:</strong>&nbsp;{project_start_date}</p><p>Data de vencimento do projeto:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Estamos ansiosos para ouvir de você.<br><br>С Atenciosamente,<br>{app_name}</p><p></p>',
                    'zh' => '<p>您好{project_assign_user}，</p><p>已为您分配了新项目。<br><br><strong>项目标题：</strong> {project_title}</p><p><strong >项目开始日期：</strong> {project_start_date}</p><p><b>项目截止日期</b>： {project_due_date}</p><p style="line-height: 28px; font-家人：Nunito，“ segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>我们期待您的回复。< br><br>亲切的问候，<br>{app_name}</p><p></p>',
                    'he' => '<p>שלום {project_assign_user},</p><p>פרויקט חדש הוקצה לך.<br><br><strong>כותרת הפרויקט:</strong> {project_title}</p><p><strong >תאריך תחילת הפרויקט:</strong> {project_start_date}</p><p><b>תאריך יעד של הפרויקט</b>: {project_due_date}</p><p style="line-height: 28px; font- משפחה: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>אנו מצפים לשמוע ממך.< br><br>בברכה,<br>{app_name}</p><p></p>',
                    'tr' => '<p>Merhaba {project_assign_user},</p><p>Size yeni proje atandı.<br><br><strong>Proje Başlığı:</strong> {project_title}</p><p><strong >Proje Başlangıç ​​Tarihi:</strong> {project_start_date}</p><p><b>Proje Bitiş Tarihi</b>: {project_due_date}</p><p style="line-height: 28px; font- aile: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Sizden haber bekliyoruz.< br><br>Saygılarımızla,<br>{app_name}</p><p></p>',
                    'pt-br' => '<p>Olá&nbsp;{project_assign_user},</p><p>Novo projeto é atribuído a você.<br><br>título do projeto<b>:</b>&nbsp;{project_title}</p><p>Data de início do projeto<strong>:</strong>&nbsp;{project_start_date}</p><p>Data de vencimento do projeto:&nbsp;{project_due_date}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""></p><p>Estamos ansiosos para ouvir de você.<br><br>С Atenciosamente,<br>{app_name}</p><p></p>',
                ],
            ],
            'project_finished' => [
                'subject' => 'Project Finished',
                'lang' => [
                    'ar' => '<p>مرحبا&nbsp;{project_client},</p><p>أنت تتلقى هذا البريد الإلكتروني بسبب المشروع&nbsp;<strong>{project}</strong> تم تعليمه على أنه منتهي. تم تعيين هذا المشروع تحت شركتك وأردنا فقط أن نبقيك على اطلاع دائم.<br></p><p>إذا كان لديك أي أسئلة ، فلا تتردد في الاتصال بنا.<br><br>أطيب التحيات,<br>{app_name}</p>',
                    'da' => '<p>Hej&nbsp;{project_client},</p><p>Du modtager denne e-mail, fordi projektet&nbsp;<strong>{project}</strong>er markeret som færdig. Dette projekt er tildelt under din virksomhed, og vi ville bare holde dig opdateret.<br></p><p>Hvis du har spørgsmål, så tøv ikke med at kontakte os.<br><br>Med venlig hilsen,<br>{app_name}</p>',
                    'de' => '<p>Hallo&nbsp;{project_client},</p><p>Sie erhalten diese E-Mail wegen Projekt&nbsp;<strong>{project}</strong> wurde als fertig markiert. Dieses Projekt ist Ihrem Unternehmen zugeordnet und wir wollten Sie nur auf dem Laufenden halten.<br></p><p>Wenn Sie Fragen haben, zögern Sie nicht, uns zu kontaktieren.<br><br>Mit freundlichen Grüßen,<br>{app_name}</p>',
                    'en' => '<p><b>Hello</b>&nbsp;{project_client},</p><p>You are receiving this email because project&nbsp;<strong>{project}</strong> has been marked as finished. This project is assigned under your company and we just wanted to keep you up to date.<br></p><p>If you have any questions don\'t hesitate to contact us.<br><br>Kind Regards,<br>{app_name}</p>',
                    'es' => '<p>Hola&nbsp;{project_client},</p><p>Estás recibiendo este correo electrónico porque proyecto&nbsp;<strong>{project}</strong> ha sido marcado como terminado. Este proyecto está asignado a su empresa y solo queríamos mantenerlo actualizado.<br></p><p>Si tiene alguna pregunta no dude en contactarnos..<br><br>Saludos cordiales,<br>{app_name}</p>',
                    'fr' => '<p>Hola&nbsp;{project_client},</p><p>Estás recibiendo este correo electrónico porque proyecto&nbsp;<strong>{project}</strong> ha sido marcado como terminado. Este proyecto está asignado a su empresa y solo queríamos mantenerlo actualizado.<br></p><p>Si tiene alguna pregunta no dude en contactarnos..<br><br>Saludos cordiales,<br>{app_name}</p>',
                    'it' => '<p>Ciao&nbsp;{project_client},</p><p>Hai ricevuto questa email perché project&nbsp;<strong>{project}</strong>è stato contrassegnato come finito. Questo progetto è assegnato dalla tua azienda e volevamo solo tenerti aggiornato.<br></p>    <p>Se hai domande non esitare a contattarci.<br><br>Cordiali saluti,<br>{app_name}</p>',
                    'ja' => '<p>こんにちは&nbsp;{project_client},</p><p>プロジェクトが原因でこのメールを受信して​​います&nbsp;<strong>{project}</strong> 終了としてマークされています。このプロジェクトはあなたの会社の下で割り当てられており、私たちはあなたを最新の状態に保ちたいと思っていました.<br></p><p>ご不明な点がございましたら、お気軽にお問い合わせください.<br><br>敬具,<br>{app_name}</p>',
                    'nl' => '<p>Hallo&nbsp;{project_client},</p><p>U ontvangt deze e-mail omdat project&nbsp;<strong>{project}</strong> is gemarkeerd als voltooid. Dit project is toegewezen onder uw bedrijf en we wilden u gewoon op de hoogte houden.<br></p><p>Mocht u nog vragen hebben, neem dan gerust contact met ons op.<br><br>Vriendelijke groeten,<br>{app_name}</p>',
                    'pl' => '<p>cześć&nbsp;{project_client},</p><p>Otrzymujesz tę wiadomość e-mail, ponieważ project&nbsp;<strong>{project}</strong>został oznaczony jako zakończony. Ten projekt jest przypisany do Twojej firmy i chcieliśmy tylko, abyś był na bieżąco.<br></p><p>Jeśli masz jakieś pytania, nie wahaj się z nami skontaktować.<br><br>Z poważaniem,<br>{app_name}</p>',
                    'ru' => '<p>Здравствуйте&nbsp;{project_client},</p><p>Вы получили это письмо, потому что проект&nbsp;<strong>{project}</strong> был отмечен как завершенный. Этот проект закреплен за вашей компанией, и мы просто хотели держать вас в курсе..<br></p><p>Если у вас есть вопросы, не стесняйтесь обращаться к нам.<br><br>С уважением,<br>{app_name}</p>',
                    'pt' => '<p>Olá&nbsp;{project_client},</p><p>Você está recebendo este e-mail porque o projeto&nbsp;<strong>{project}</strong> foi marcado como concluído. Este projeto é atribuído sob sua empresa e nós apenas queríamos mantê-lo atualizado..<br></p><p>Se tiver alguma dúvida não hesite em contactar-nos.<br><br>Atenciosamente,<br>{app_name}</p>',
                    'zh' => '<p><b>您好</b> {project_client}，</p><p>您收到这封电子邮件是因为项目 <strong>{project}</strong> 已标记为已完成。该项目由您的公司分配，我们只是想让您了解最新情况。<br></p><p>如果您有任何疑问，请随时与我们联系。<br><br>亲切的问候,<br>{app_name}</p>',
                    'he' => '<p><b>שלום</b> {project_client},</p><p>אתה מקבל אימייל זה מכיוון שהפרויקט <strong>{project}</strong> סומן כסיום. הפרויקט הזה מוקצה תחת החברה שלך ורק רצינו לעדכן אותך.<br></p><p>אם יש לך שאלות, אל תהסס לפנות אלינו.<br><br>בברכה ,<br>{app_name}</p>',
                    'tr' => '<p><b>Merhaba</b> {project_client},</p><p>Bu e-postayı, <strong>{project}</strong> projesi bitti olarak işaretlendiği için alıyorsunuz. Bu proje şirketiniz altında görevlendirildi ve sizi güncel bilgilerden haberdar etmek istedik.<br></p><p>Herhangi bir sorunuz varsa bizimle iletişime geçmekten çekinmeyin.<br><br>Saygılarımızla ,<br>{uygulama_adı}</p>',
                    'pt-br' => '<p>Olá&nbsp;{project_client},</p><p>Você está recebendo este e-mail porque o projeto&nbsp;<strong>{project}</strong> foi marcado como concluído. Este projeto é atribuído sob sua empresa e nós apenas queríamos mantê-lo atualizado..<br></p><p>Se tiver alguma dúvida não hesite em contactar-nos.<br><br>Atenciosamente,<br>{app_name}</p>',
                ],
            ],
            'task_assigned' => [
                'subject' => 'Task Assigned',
                'lang' => [
                    'ar' => '<p>العزيز {task_assign_user}</p><p>لقد تم تكليفك بمهمة جديدة:</p><p>اسم: {task_title}<br>تاريخ البدء: {task_start_date}<br>تاريخ الاستحقاق: {task_due_date}<br>أفضلية: {task_priority}<br><br>أطيب التحيات,<br>{app_name}</p>',
                    'da' => '<p>Kære {task_assign_user}</p><p>Du er blevet tildelt en ny opgave:</p><p><b>Navn</b>: {task_title}<br><b>Start dato</b>: {task_start_date}<br><b>Afleveringsdato</b>: {task_due_date}<br><b>Prioritet</b>: {task_priority}<br><br><b>Kind Regards</b>,<br>{app_name}</p>',
                    'de' => '<p>sehr geehrter {task_assign_user}</p><p>Sie wurden einer neuen Aufgabe zugewiesen:</p><p><b>Name</b>: {task_title}<br><b>Anfangsdatum</b>: {task_start_date}<br><b>Geburtstermin</b>: {task_due_date}<br><b>Priorität</b>: {task_priority}<br><br><b>Mit freundlichen Grüßen</b>,<br>{app_name}</p>',
                    'en' => '<p>Dear {task_assign_user}</p><p>You have been assigned to a new task:</p><p><b>Name</b>: {task_title}<br><b>Start Date</b>: {task_start_date}<br><b>Due date</b>: {task_due_date}<br><b>Priority</b>: {task_priority}<br><br>Kind Regards,<br>{app_name}</p>',
                    'es' => '<p>sehr geehrter {task_assign_user}</p><p>Sie wurden einer neuen Aufgabe zugewiesen:</p><p><b>Name</b>: {task_title}<br><b>Anfangsdatum</b>: {task_start_date}<br><b>Geburtstermin</b>: {task_due_date}<br><b>Priorität</b>: {task_priority}<br><br><b>Mit freundlichen Grüßen</b>,<br>{app_name}</p>',
                    'fr' => '<p>Chère {task_assign_user}</p><p>Vous avez été affecté à une nouvelle tâche:</p><p><b>Nom</b>: {task_title}<br><b>Date de début</b>: {task_start_date}<br><b>Date d\'échéance</b>: {task_due_date}<br><b>Priorité</b>: {task_priority}<br><br><b>Sincères amitiés</b>,<br>{app_name}</p>',
                    'it' => '<p>Cara {task_assign_user}</p><p>Sei stato assegnato a una nuova attività:</p><p><b>Nome</b>: {task_title}<br><b>Data d\'inizio</b>: {task_start_date}<br><b>Scadenza</b>: {task_due_date}<br><b>Priorità</b>: {task_priority}<br><br><b>Cordiali saluti</b>,<br>{app_name}</p>',
                    'ja' => '<p>親愛な {task_assign_user}</p><p>新しいタスクに割り当てられました:</p><p>名前: {task_title}<br>開始日: {task_start_date}<br>期日: {task_due_date}<br>優先: {task_priority}<br><br>敬具,<br>{app_name}</p>',
                    'nl' => '<p>Lieve {task_assign_user}</p><p>U bent aan een nieuwe taak toegewezen:</p><p><b>Naam</b>: {task_title}<br><b>Begin datum</b>: {task_start_date}<br><b>Opleveringsdatum</b>: {task_due_date}<br><b>Prioriteit</b>: {task_priority}<br><br><b>Vriendelijke groeten</b>,<br>{app_name}</p>',
                    'pl' => '<p>Drogi {task_assign_user}</p><p>Zostałeś przydzielony do nowego zadania:</p><p><b>Nazwa</b>: {task_title}<br><b>Data rozpoczęcia</b>: {task_start_date}<br><b>Termin</b>: {task_due_date}<br><b>Priorytet</b>: {task_priority}<br><br>Z poważaniem,<br>{app_name}</p>',
                    'ru' => '<p>дорогая {task_assign_user}</p><p>Вам поручили новую задачу:</p><p><b>имя</b>: {task_title}<br><b>Дата начала</b>: {task_start_date}<br><b>Срок</b>: {task_due_date}<br><b>Приоритет</b>: {task_priority}<br><br><b>С уважением</b>,<br>{app_name}</p>',
                    'pt' => '<p>Querida {task_assign_user}</p><p>Você foi atribuído a uma nova tarefa:</p><p><b>Nome</b>: {task_title}<br><b>Data de início</b>: {task_start_date}<br><b>Data de vencimento</b>: {task_due_date}<br><b>Prioridade</b>: {task_priority}<br><br><b>С Atenciosamente</b>,<br>{app_name}</p>',
                    'zh' => '<p>尊敬的{task_assign_user}</p><p>您已被分配到一项新任务：</p><p><b>名称</b>：{task_title}<br><b>开始日期</b>：{task_start_date}<br><b>截止日期</b>：{task_due_date}<br><b>优先级</b>：{task_priority}<br><br>亲切的问候，<br >{应用名称}</p>',
                    'he' => '<p>{task_assign_user</p><p> היקר הוקצית למשימה חדשה:</p><p><b>שם</b>: {task_title}<br><b>תאריך התחלה </b>: {task_start_date}<br><b>תאריך יעד</b>: {task_due_date}<br><b>עדיפות</b>: {task_priority}<br><br>בברכה,<br >{app_name}</p>',
                    'tr' => '<p>Sayın {task_assign_user}</p><p>Yeni bir göreve atandınız:</p><p><b>Ad</b>: {task_title}<br><b>Başlangıç ​​Tarihi </b>: {task_start_date}<br><b>Son tarih</b>: {task_due_date}<br><b>Öncelik</b>: {task_priority}<br><br>Saygılarımızla,<br >{uygulama_adı}</p>',
                    'pt-br' => '<p>Querida {task_assign_user}</p><p>Você foi atribuído a uma nova tarefa:</p><p><b>Nome</b>: {task_title}<br><b>Data de início</b>: {task_start_date}<br><b>Data de vencimento</b>: {task_due_date}<br><b>Prioridade</b>: {task_priority}<br><br><b>С Atenciosamente</b>,<br>{app_name}</p>',
                ],
            ],
            'invoice_sent' => [
                'subject' => 'Invoice Sent',
                'lang' => [
                    'ar' => 'العزيز<span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span><br><br>لقد قمنا بإعداد الفاتورة التالية من أجلك<span style="font-size: 12pt;">: </span><strong style="font-size: 12pt;">&nbsp;{invoice_id}</strong><br><br>حالة الفاتورة<span style="font-size: 12pt;">: {invoice_status}</span><br><br><br>يرجى الاتصال بنا للحصول على مزيد من المعلومات<span style="font-size: 12pt;">.</span><br><br>أطيب التحيات<span style="font-size: 12pt;">,</span><br>{app_name}',
                    'da' => 'Kære<span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span><br><br>Vi har udarbejdet følgende faktura til dig<span style="font-size: 12pt;">:&nbsp;&nbsp;{invoice_id}</span><br><br>Fakturastatus: {invoice_status}<br><br>Kontakt os for mere information<span style="font-size: 12pt;">.</span><br><br>Med venlig hilsen<span style="font-size: 12pt;">,</span><br>{app_name}',
                    'de' => '<p><b>sehr geehrter</b><span style="font-size: 12pt;">&nbsp;{invoice_client}</span><br><br>Wir haben die folgende Rechnung für Sie vorbereitet<span style="font-size: 12pt;">: {invoice_id}</span><br><br><b>Rechnungsstatus</b><span style="font-size: 12pt;">: {invoice_status}</span></p><p>Bitte kontaktieren Sie uns für weitere Informationen<span style="font-size: 12pt;">.</span><br><br><b>Mit freundlichen Grüßen</b><span style="font-size: 12pt;">,</span><br>{app_name}</p>',
                    'en' => '<p><span style="font-size: 12pt;"><b>Dear</b> {invoice_client}</span><span style="font-size: 12pt;">,</span></p><p><span style="font-size: 12pt;">We have prepared the following invoice for you :#{invoice_id}</span></p><p><span style="font-size: 12pt;"><b>Invoice Status</b> : {invoice_status}</span></p><p>Please Contact us for more information.</p><p><br><b>Kind Regards</b>,<br><span style="font-size: 12pt;">{app_name}</span><br></p>',
                    'es' => '<p><b>Querida</b><span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span></p><p>Hemos preparado la siguiente factura para ti<span style="font-size: 12pt;">:&nbsp;&nbsp;{invoice_id}</span></p><p><b>Estado de la factura</b><span style="font-size: 12pt;">: {invoice_status}</span></p><p>Por favor contáctenos para más información<span style="font-size: 12pt;">.</span></p><p><b>Saludos cordiales</b><span style="font-size: 12pt;">,<br></span>{app_name}</p>',
                    'fr' => '<p><b>Cher</b><span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span></p><p>Nous avons préparé la facture suivante pour vous<span style="font-size: 12pt;">: {invoice_id}</span></p><p><b>État de la facture</b><span style="font-size: 12pt;">: {invoice_status}</span></p><p>Veuillez nous contacter pour plus d\'informations<span style="font-size: 12pt;">.</span></p><p><b>Sincères amitiés</b><span style="font-size: 12pt;">,<br></span>{app_name}</p>',
                    'it' => '<p><b>Caro</b><span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span></p><p>Abbiamo preparato per te la seguente fattura<span style="font-size: 12pt;">:&nbsp;&nbsp;{invoice_id}</span></p><p><b>Stato della fattura</b><span style="font-size: 12pt;">: {invoice_status}</span></p><p>Vi preghiamo di contattarci per ulteriori informazioni<span style="font-size: 12pt;">.</span></p><p><b>Cordiali saluti</b><span style="font-size: 12pt;">,<br></span>{app_name}</p>',
                    'ja' => '親愛な<span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span><br><br>以下の請求書をご用意しております。<span style="font-size: 12pt;">: {invoice_client}</span><br><br>請求書のステータス<span style="font-size: 12pt;">: {invoice_status}</span><br><br>詳しくはお問い合わせください<span style="font-size: 12pt;">.</span><br><br>敬具<span style="font-size: 12pt;">,</span><br>{app_name}',
                    'nl' => '<p><b>Lieve</b><span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span></p><p>We hebben de volgende factuur voor u opgesteld<span style="font-size: 12pt;">: {invoice_id}</span></p><p><b>Factuurstatus</b><span style="font-size: 12pt;">: {invoice_status}</span></p><p>Voor meer informatie kunt u contact met ons opnemen<span style="font-size: 12pt;">.</span></p><p><b>Vriendelijke groeten</b><span style="font-size: 12pt;">,<br></span>{app_name}</p>',
                    'pl' => '<p><b>Drogi</b><span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span></p><p>Przygotowaliśmy dla Ciebie następującą fakturę<span style="font-size: 12pt;">: {invoice_id}</span></p><p><b>Status faktury</b><span style="font-size: 12pt;">: {invoice_status}</span></p><p>Skontaktuj się z nami, aby uzyskać więcej informacji<span style="font-size: 12pt;">.</span></p><p><b>Z poważaniem</b><span style="font-size: 12pt;"><b>,</b><br></span>{app_name}</p>',
                    'ru' => '<p><b>дорогая</b><span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span></p><p>Мы подготовили для вас следующий счет<span style="font-size: 12pt;">: {invoice_id}</span></p><p><b>Статус счета</b><span style="font-size: 12pt;">: {invoice_status}</span></p><p>Пожалуйста, свяжитесь с нами для получения дополнительной информации<span style="font-size: 12pt;">.</span></p><p><b>С уважением</b><span style="font-size: 12pt;">,<br></span>{app_name}</p>',
                    'pt' => '<p><b>Querida</b><span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span></p><p>Preparamos a seguinte fatura para você<span style="font-size: 12pt;">: {invoice_id}</span></p><p><b>Status da fatura</b><span style="font-size: 12pt;">: {invoice_status}</span></p><p>Entre em contato conosco para mais informações.<span style="font-size: 12pt;">.</span></p><p><b>Atenciosamente</b><span style="font-size: 12pt;">,<br></span>{app_name}</p>',
                    'zh' => '<p><span style="font-size: 12pt;"><b>亲爱的</b> {invoice_client}</span><span style="font-size: 12pt;">，</span>< /p><p><span style="font-size: 12pt;">我们为您准备了以下发票：#{invoice_id}</span></p><p><span style="font-尺寸：12pt;"><b>发票状态</b>：{invoice_status}</span></p><p>请联系我们了解更多信息。</p><p><br><b>亲切的问候</b>，<br><span style="font-size: 12pt;">{app_name}</span><br></p>',
                    'he' => '<p><span style="font-size: 12pt;"><b>יקירי</b> {invoice_client}</span><span style="font-size: 12pt;">,</span>< /p><p><span style="font-size: 12pt;">הכנו עבורך את החשבונית הבאה:#{invoice_id}</span></p><p><span style="font- size: 12pt;"><b>סטטוס חשבונית</b> : {invoice_status}</span></p><p>אנא צור איתנו קשר לקבלת מידע נוסף.</p><p><br><b> בברכה</b>,<br><span style="font-size: 12pt;">{app_name}</span><br></p>',
                    'tr' => '<p><span style="font-size: 12pt;"><b>Sayın</b> {invoice_client}</span><span style="font-size: 12pt;">,</span>< /p><p><span style="font-size: 12pt;">Sizin için aşağıdaki faturayı hazırladık :#{invoice_id}</span></p><p><span style="font- size: 12pt;"><b>Fatura Durumu</b> : {invoice_status}</span></p><p>Daha fazla bilgi için lütfen bizimle iletişime geçin.</p><p><br><b> Saygılarımızla</b>,<br><span style="font-size: 12pt;">{app_name}</span><br></p>',
                    'pt-br' => '<p><b>Querida</b><span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;">,</span></p><p>Preparamos a seguinte fatura para você<span style="font-size: 12pt;">: {invoice_id}</span></p><p><b>Status da fatura</b><span style="font-size: 12pt;">: {invoice_status}</span></p><p>Entre em contato conosco para mais informações.<span style="font-size: 12pt;">.</span></p><p><b>Atenciosamente</b><span style="font-size: 12pt;">,<br></span>{app_name}</p>',
                ],
            ],


            'invoice_payment_recorded' => [
                'subject' => 'Invoice Payment Recorded',
                'lang' => [
                    'ar' => '<p>مرحبا<span style="font-size: 12pt;">&nbsp;{invoice_client}</span><span style="font-size: 12pt;"><br><br></span><span style="font-size: 12pt;"><br></span>شكرا على الدفع. ابحث عن تفاصيل الدفع أدناه:<br>-------------------------------------------------<br>كمية: {payment_total}<strong><br></strong>تاريخ:&nbsp; {payment_date<strong><br></strong>رقم الفاتورة: {invoice_id}<span style="font-size: 12pt;"><strong><br><br></strong></span><span style="font-size: 12pt;"><strong><br></strong></span>-------------------------------------------------<br>نحن نتطلع إلى العمل معك.<br>أطيب التحيات<span style="font-size: 12pt;">,</span><br>{app_name}</p>',
                    'da' => '<p></p><p></p><h4><blockquote class="blockquote"><p><b>Hej</b><span style="font-size: 12pt;">&nbsp;{invoice_client},</span><span style="font-size: 12pt;"><br></span></p></blockquote></h4><p></p><p></p><p>Tak for betalingen. Find betalingsoplysningerne nedenfor:<br>-------------------------------------------------<br></p><p><b>Beløb</b>: {payment_total}<strong><br></strong></p><p><b>Dato</b>: {payment_date}<strong><br></strong></p><p><b>Faktura nummer</b>: {invoice_id}<span style="font-size: 12pt;"><strong><br><br></strong></span><span style="font-size: 12pt;"><strong><br></strong></span>-------------------------------------------------<br></p><p>Vi ser frem til at arbejde sammen med dig.<br></p><p><b>Med venlig hilsen</b><span style="font-size: 12pt;">,<br></span>{app_name}</p>',
                    'de' => '<p><b>Hallo</b>&nbsp;{invoice_client}<br><br><br></p><p>Vielen Dank für die Zahlung. Hier finden Sie die Zahlungsdetails:<br>-------------------------------------------------<br></p><p><b>Menge</b>: {payment_total}<br></p><p><b>Datum</b>: {payment_date}<br></p><p><b>Rechnungsnummer</b>: {invoice_id}<br><br><br>-------------------------------------------------<br></p><p>Wir freuen uns auf die Zusammenarbeit mit Ihnen.<br></p><p><b>Mit freundlichen Grüßen</b>,<br>{app_name}</p>',
                    'en' => '<p><span style="font-size: 12pt;"><b>Hello</b>&nbsp;{invoice_client}</span><span style="font-size: 12pt;"><br><br></span><span style="font-size: 12pt;"><br></span>Thank you for the payment. Find the payment details below:<br>-------------------------------------------------<br><b>Amount</b>: {payment_total}<strong><br></strong><b>Date</b>: {payment_date}<strong><br></strong><b>Invoice number</b>: {invoice_id}<span style="font-size: 12pt;"><strong><br></strong></span><span style="font-size: 12pt;"><strong><br></strong></span>-------------------------------------------------<br>We are looking forward working with you.<br><span style="font-size: 12pt;"><b>Kind Regards</b>,<br></span>{app_name}</p>',
                    'es' => '<p><b>Hola</b>&nbsp;{invoice_client}<br><br><br></p><p>Gracias por el pago. Encuentre los detalles de pago a continuación:<br>-------------------------------------------------<br></p><p><b>Cantidad</b>:&nbsp; {payment_total}<br></p><p><b>Fecha</b>: {payment_date}<br></p><p><b>Número de factura</b>: {invoice_id}<br><br><br>-------------------------------------------------<br></p><p>Esperamos trabajar con usted.<br></p><p><b>Saludos cordiales</b>,<br>{invoice_id}</p>',
                    'fr' => '<p><b>Bonjour</b>&nbsp;{invoice_client},<br><br><br></p><p>Merci pour le paiement. Trouvez les détails de paiement ci-dessous:<br>-------------------------------------------------<br></p><p><b>Montant</b>: {payment_total}<br></p><p><b>Date</b>: {payment_date}<br></p><p><b>Numéro de facture</b>: {invoice_id}<br><br><br>-------------------------------------------------<br></p><p>Nous sommes impatients de travailler avec vous.<br></p><p><b>Sincères amitiés</b>,<br>{app_name}</p>',
                    'it' => '<p><b>Ciao</b>&nbsp;{invoice_client},<br><br><br></p><p>Grazie per il pagamento. Trova i dettagli di pagamento di seguito:<br>-------------------------------------------------<br></p><p><b>Quantità</b>: {payment_total}<br></p><p><b>Data</b>: {payment_date}<br></p><p><b>Numero di fattura</b>: {invoice_id}<br><br><br>-------------------------------------------------<br></p><p>Non vediamo l\'ora di lavorare con te.<br></p><p><b>Cordiali saluti</b>,</p><p>{app_name}</p>',
                    'ja' => '<p>こんにちは {invoice_client}<br><br><br></p><pre class="tw-data-text tw-text-large XcVN5d tw-ta" data-placeholder="Translation" id="tw-target-text" dir="ltr" style="unicode-bidi: isolate; line-height: 32px; border: none; padding: 2px 0.14em 2px 0px; position: relative; margin-top: -2px; margin-bottom: -2px; resize: none; overflow: hidden; width: 277px; overflow-wrap: break-word;"><span lang="ja" style="">お支払いいただきありがとうございます。以下で支払いの詳細を確認してください。</span></pre><p>-------------------------------------------------<br></p><p>量: {payment_total}<br></p><p>日付: {payment_date}<br></p><p>請求書番号: {invoice_id}<br><br><br>-------------------------------------------------<br></p><p>どうぞよろしくお願いいたします.<br></p><p>敬具,<br>{app_name}</p>',
                    'nl' => '<p><b>Hallo</b>&nbsp;{invoice_client},<br><br><br></p><p>Bedankt voor de betaling. Vind de betalingsgegevens hieronder:<br>-------------------------------------------------<br></p><p><b>Bedrag</b>: {payment_total}<br></p><p><b>Datum</b>: {payment_date}<br></p><p><b>Factuurnummer</b>: {invoice_id}<br><br><br>-------------------------------------------------<br></p><p>We kijken er naar uit om met u samen te werken.<br></p><p><b>Vriendelijke groeten</b>,<br>{app_name}</p>',
                    'pl' => '<p><b>cześć</b>&nbsp; {invoice_client},<br><br><br></p><p>Dziękuję za wpłatę. Znajdź szczegóły płatności poniżej:<br>-------------------------------------------------<br></p><p><b>Ilość</b>: {payment_total}<br></p><p><b>Data</b>: {payment_date}<br></p><p><b>Numer faktury</b>: {invoice_id}<br><br><br>-------------------------------------------------<br></p><p>Cieszymy się na współpracę z Tobą.<br></p><p><b>Z poważaniem</b>,<br>{app_name}</p>',
                    'ru' => '<p><b>Здравствуйте</b>&nbsp;{invoice_client},<br></p><p>Спасибо за оплату. Найдите информацию о платеже ниже:<br>-------------------------------------------------<br></p><p><b>Количество</b>: {payment_total}<br></p><p><b>Дата</b>: {payment_date}<br></p><p><b>Номер счета</b>: {invoice_id}<br><br><br>-------------------------------------------------<br></p><p>Будем рады сотрудничеству с вами.<br></p><p><b>С уважением</b>,<br>{app_name}</p>',
                    'pt' => '<p><b>Olá</b>&nbsp;{invoice_client},<br></p><p>Obrigado pelo pagamento. Encontre os detalhes de pagamento abaixo:<br>-------------------------------------------------<br></p><p><b>Montante</b>: {payment_total}<br></p><p><b>Encontro</b>: {payment_date}<br></p><p><b>Número da fatura</b>: {invoice_id}<br><br><br>-------------------------------------------------<br></p><p>Estamos ansiosos para trabalhar com você.<br></p><p><b>Atenciosamente</b>,<br>{app_name}</p>',
                    'zh' => '<p><span style="font-size: 12pt;"><b>您好</b> {invoice_client}</span><span style="font-size: 12pt;"><br><br> </span><span style="font-size: 12pt;"><br></span>感谢您的付款。查找以下付款详细信息：<br>---------------------------------------------------- --------<br><b>金额</b>：{ payment_total}<strong><br></strong><b>日期</b>：{ payment_date}<strong><br ></strong><b>发票号码</b>：{invoice_id}<span style="font-size: 12pt;"><strong><br></strong></span><span style="字体大小：12pt;"><strong><br></strong></span>---------------------------- ---------------------------------<br>我们期待与您合作。<br><span style="font-size: 12pt;"><b >亲切的问候</b>，<br></span>{app_name}</p>',
                    'he' => '<p><span style="font-size: 12pt;"><b>שלום</b> {invoice_client}</span><span style="font-size: 12pt;"><br><br> </span><span style="font-size: 12pt;"><br></span>תודה על התשלום. מצא את פרטי התשלום למטה:<br>---------------------------------------- --------<br><b>סכום</b>: {payment_total}<strong><br></strong><b>תאריך</b>: {payment_date}<strong><br ></strong><b>מספר חשבונית</b>: {invoice_id}<span style="font-size: 12pt;"><strong><br></strong></span><span style=" font-size: 12pt;"><strong><br></strong></span>-------------------------------- ----------------------<br>אנו מצפים לעבוד איתך.<br><span style="font-size: 12pt;"><b >בברכה</b>,<br></span>{app_name}</p>',
                    'tr' => '<p><span style="font-size: 12pt;"><b>Merhaba</b> {invoice_client}</span><span style="font-size: 12pt;"><br><br> </span><span style="font-size: 12pt;"><br></span>Ödeme için teşekkür ederiz. Aşağıdaki ödeme ayrıntılarını bulun:<br>----------------------------------------- --------<br><b>Tutar</b>: {payment_total}<strong><br></strong><b>Tarih</b>: {payment_date}<strong><br ></strong><b>Fatura numarası</b>: {invoice_id}<span style="font-size: 12pt;"><strong><br></strong></span><span style=" yazı tipi boyutu: 12pt;"><strong><br></strong></span>---------------------------- ---------------------<br>Sizinle çalışmak için sabırsızlanıyoruz.<br><span style="font-size: 12pt;"><b >Saygılarımla</b>,<br></span>{app_name}</p>',
                    'pt-br' => '<p><b>Olá</b>&nbsp;{invoice_client},<br></p><p>Obrigado pelo pagamento. Encontre os detalhes de pagamento abaixo:<br>-------------------------------------------------<br></p><p><b>Montante</b>: {payment_total}<br></p><p><b>Encontro</b>: {payment_date}<br></p><p><b>Número da fatura</b>: {invoice_id}<br><br><br>-------------------------------------------------<br></p><p>Estamos ansiosos para trabalhar com você.<br></p><p><b>Atenciosamente</b>,<br>{app_name}</p>',
                ],
            ],
            'new_credit_note' => [
                'subject' => 'New Credit Note',
                'lang' => [
                    'ar' => '<p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">العزيز</span>&nbsp;{invoice_client}</p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">لقد أرفقنا إشعار الائتمان </span><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">للرجوع إليه</span><span style="font-size: 1rem;">&nbsp; #{invoice_id}&nbsp;</span><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">بالرقم</span><span style="font-size: 1rem;">.</span></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">تاريخ:</span>&nbsp;{credit_note_date}</p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">المبلغ الإجمالي</span>:&nbsp;{credit_amount}</p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">يرجى الاتصال بنا للحصول </span><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">على مزيد من المعلومات.</span><span style="font-size: 1rem;">.</span></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">أطيب التحيات،</span>,</p>{app_name}',
                    'da' => '<p><b>Kære</b>&nbsp;{invoice_client}</p><p>Vi har vedhæftet kreditnotaen med nummer&nbsp;#{invoice_id}&nbsp;til din reference.</p><p><b>Dato</b>:&nbsp;{credit_note_date}</p><p><b>Total beløb</b>:&nbsp;{credit_amount}</p><pre class="tw-data-text tw-text-large XcVN5d tw-ta" data-placeholder="Translation" id="tw-target-text" dir="ltr" style="unicode-bidi: isolate; line-height: 36px; border: none; padding: 2px 0.14em 2px 0px; position: relative; margin-top: -2px; margin-bottom: -2px; resize: none; overflow: hidden; width: 277px; overflow-wrap: break-word;"><span lang="da">Kontakt os for mere information</span></pre><p><b>Med venlig hilsen</b>,</p>{app_name}',
                    'de' => '<p><b>sehr geehrter</b>&nbsp;{invoice_client}</p><p>Wir haben die Gutschrift mit der Nummer&nbsp;#{invoice_id}&nbsp;als Referenz beigefügt.</p><p><b>Datum</b>:&nbsp;{credit_note_date}</p><p><b>Gesamtsumme</b>:&nbsp;{credit_amount}</p><p>Bitte kontaktieren Sie uns für weitere Informationen.</p><p><b>Mit freundlichen</b>,</p>{app_name}',
                    'en' => '<p><b>Dear</b>&nbsp;{invoice_client}</p><p>We have attached the credit note with number #{invoice_id} for your reference.</p><p><b>Date</b>:&nbsp;{credit_note_date}</p><p><b>Total Amount</b>:&nbsp;{credit_amount}</p><p>Please contact us for more information.</p><p><b>Kind Regards</b>,</p>{app_name}',
                    'es' => '<p><b>querido</b>&nbsp;{invoice_client}</p><p>Hemos adjuntado la nota de crédito con el número &nbsp;#{invoice_id}&nbsp;para su referencia.</p><p><b>Date</b>:&nbsp;{credit_note_date}</p><p><b>Cantidad total</b>:&nbsp;{credit_amount}</p><p>Por favor contáctenos para más información.</p><p><b>Saludos cordiales</b>,</p>{app_name}',
                    'fr' => '<p><b>chère</b>&nbsp;{invoice_client}</p><p>Nous avons joint la note de crédit avec le numéro #{invoice_id}&nbsp;pour votre référence.</p><p><b>Date</b>:&nbsp;{credit_note_date}</p><p><b>Montant total</b>:&nbsp;{credit_amount}</p><p>Veuillez nous contacter pour plus d\'informations.</p><p><b>Sincères amitiés</b>,</p>{app_name}',
                    'it' => '<p><b>caro</b>&nbsp;{invoice_client}</p><p>Abbiamo allegato la nota di credito con il numero&nbsp;#{invoice_id}&nbsp;come riferimento.</p><p><b>Data</b>:&nbsp;{credit_note_date}</p><p><b>Importo totale</b>:&nbsp;{credit_amount}</p><p>Vi preghiamo di contattarci per ulteriori informazioni.</p><p><b>Cordiali saluti</b>,</p>{app_name}',
                    'ja' => '<p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; white-space: pre-wrap;">親愛な</span>&nbsp;{invoice_client}</p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; white-space: pre-wrap;">参考までに番号番号付きのクレジットノートを添付しました</span>.</p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; white-space: pre-wrap;">日付</span>:&nbsp;{credit_note_date}</p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; white-space: pre-wrap;">合計金額</span>:&nbsp;{credit_amount}</p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; white-space: pre-wrap;">詳しくはお問い合わせください</span></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; white-space: pre-wrap;"><br></span></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; white-space: pre-wrap;">敬具、</span><span style="font-size: 1rem;">,</span><br></p>{app_name}',
                    'nl' => '<p><b>Lieve</b>&nbsp;{invoice_client}</p><p>Ter referentie hebben we de creditnota met nummer # bijgevoegd.</p><p><b>Datum</b>:&nbsp;{credit_note_date}</p><p><b>Totaalbedrag</b>:&nbsp;{credit_amount}</p><p>Voor meer informatie kunt u contact met ons opnemen.</p><p><b>Vriendelijke groeten</b>,</p>{app_name}',
                    'pl' => '<p><b>Drogi</b>&nbsp;{invoice_client}</p><p>W celach informacyjnych załączyliśmy notę ​​kredytową z numerem # invoice_id.</p><p><b>Data</b>:&nbsp;{credit_note_date}</p><p><b>Całkowita kwota</b>:&nbsp;{credit_amount}</p><p>Skontaktuj się z nami, aby uzyskać więcej informacji.</p><p><b>Z poważaniem</b>,</p>{app_name}',
                    'ru' => '<p><b>дорогая</b>&nbsp;{invoice_client}</p><p>Мы приложили кредит-ноту под номером&nbsp;#{invoice_id}&nbsp;для вашей справки.</p><p><b>Дата</b>:&nbsp;{credit_note_date}</p><p><b>Общее количество</b>:&nbsp;{credit_amount}</p><p>Пожалуйста, свяжитесь с нами для получения дополнительной информации.</p><p><b>С уважением</b>,</p>{app_name}',
                    'pt' => '<p><b>Querida</b>&nbsp;{invoice_client}</p><p>Anexamos a nota de crédito com o número&nbsp;#{invoice_id}&nbsp;para sua referência.</p><p><b>Encontro</b>:&nbsp;{credit_note_date}</p><p><b>Valor total</b>:&nbsp;{credit_amount}</p><p>Entre em contato conosco para mais informações.</p><p><b>Atenciosamente</b>,</p>{app_name}',
                    'zh' => '<p><b>亲爱的</b> {invoice_client}</p><p>我们已附上编号为 #{invoice_id} 的贷方票据供您参考。</p><p><b>日期</ b>：{credit_note_date}</p><p><b>总金额</b>：{credit_amount}</p><p>请联系我们了解更多信息。</p><p><b>亲切的问候</b>，</p>{app_name}',
                    'he' => '<p><b>יקירי</b> {invoice_client}</p><p>צירפנו את תעודת האשראי עם המספר #{invoice_id} לעיונך.</p><p><b>תאריך</p> b>: {credit_note_date}</p><p><b>סכום כולל</b>: {credit_amount}</p><p>אנא צור איתנו קשר לקבלת מידע נוסף.</p><p><b> בברכה</b>,</p>{app_name}',
                    'tr' => '<p><b>Sayın</b> {invoice_client}</p><p>Referans olması için #{invoice_id} numaralı alacak notunu ekledik.</p><p><b>Tarih</p><b> b>: {credit_note_date}</p><p><b>Toplam Tutar</b>: {credit_amount}</p><p>Daha fazla bilgi için lütfen bizimle iletişime geçin.</p><p><b> Saygılarımızla</b>,</p>{app_name}',
                    'pt-br' => '<p><b>Querida</b>&nbsp;{invoice_client}</p><p>Anexamos a nota de crédito com o número&nbsp;#{invoice_id}&nbsp;para sua referência.</p><p><b>Encontro</b>:&nbsp;{credit_note_date}</p><p><b>Valor total</b>:&nbsp;{credit_amount}</p><p>Entre em contato conosco para mais informações.</p><p><b>Atenciosamente</b>,</p>{app_name}',
                ],
            ],
            'new_support_ticket' => [
                'subject' => 'New Support Ticket',
                'lang' => [
                    'ar' => '<p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">مرحبا</span><span style="font-size: 12pt;">&nbsp;{assign_user}</span><br><br></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">تم فتح تذكرة دعم جديدة.</span><span style="font-size: 12pt;">.</span><br><br></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">عنوان</span><span style="font-size: 12pt;"><strong>:</strong>&nbsp;{support_title}</span><br></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">أفضلية</span><span style="font-size: 12pt;"><strong>:</strong>&nbsp;{support_priority}</span><span style="font-size: 12pt;"><br></span></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">تاريخ الانتهاء</span><span style="font-size: 12pt;">: {support_end_date}</span></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">رسالة دعم</span><span style="font-size: 12pt;"><strong>:</strong></span><br><span style="font-size: 12pt;">{support_description}</span><span style="font-size: 12pt;"><br><br></span></p><p><span style="background-color: rgb(248, 249, 250); color: rgb(34, 34, 34); font-family: inherit; font-size: 24px; text-align: right; white-space: pre-wrap;">أطيب التحيات،</span><span style="font-size: 12pt;">,</span><br>{app_name}</p>',
                    'da' => '<p><b>Hej</b>&nbsp;{assign_user}<br><br></p><p>Ny supportbillet er blevet åbnet.<br><br></p><p><b>Titel</b>: {support_title}<br></p><p><b>Prioritet</b>: {support_priority}<br></p><p><b>Slutdato</b>: {support_end_date}</p><p><br></p><p><b>Supportmeddelelse</b>:<br>{support_description}<br><br></p><p><b>Med venlig hilsen</b>,<br>{app_name}</p>',
                    'de' => '<p><b>Hallo</b>&nbsp;{assign_user}<br><br></p><p>Neues Support-Ticket wurde eröffnet.<br><br></p><p><b>Titel</b>: {support_title}<br></p><p><b>Priorität</b>: {support_priority}<br></p><p><b>Endtermin</b>: {support_end_date}</p><p><br></p><p><b>Support-Nachricht</b>:<br>{support_description}<br><br></p><p><b>Mit freundlichen Grüßen</b>,<br>{app_name}</p>',
                    'en' => '<p><span style="font-size: 12pt;"><b>Hi</b>&nbsp;{assign_user}</span><br><br><span style="font-size: 12pt;">New support ticket has been opened.</span><br><br><span style="font-size: 12pt;"><strong>Title:</strong>&nbsp;{support_title}</span><br><span style="font-size: 12pt;"><strong>Priority:</strong>&nbsp;{support_priority}</span><span style="font-size: 12pt;"><br></span><span style="font-size: 12pt;"><b>End Date</b>: {support_end_date}</span></p><p><br><span style="font-size: 12pt;"><strong>Support message:</strong></span><br><span style="font-size: 12pt;">{support_description}</span><span style="font-size: 12pt;"><br><br><b>Kind Regards</b>,</span><br>{app_name}</p>',
                    'es' => '<p><b>Hola</b>&nbsp;{assign_user}<br><br></p><p>Se ha abierto un nuevo ticket de soporte.<br><br></p><p><b>Título</b>: {support_title}<br></p><p><b>Prioridad</b>: {support_priority}<br></p><p><b>Fecha final</b>: {support_end_date}</p><p><br></p><p><b>Mensaje de apoyo</b>:<br>{support_description}<br><br></p><p><b>Saludos cordiales</b>,<br>{app_name}</p>',
                    'fr' => '<p><b>salut</b>&nbsp;{assign_user}<br><br></p><p>Un nouveau ticket d\'assistance a été ouvert.<br><br></p><p><b>Titre</b>: {support_title}<br></p><p><b>Priorité</b>: {support_priority}<br></p><p><b>Date de fin</b>: {support_end_date}</p><p><b>Message d\'assistance</b>:<br>{support_description}<br><br></p><p><b>Sincères amitiés</b>,<br>{app_name}</p>',
                    'it' => '<p><b>Ciao</b>&nbsp;{assign_user},<br><br></p><p>È stato aperto un nuovo ticket di supporto.<br><br></p><p><b>Titolo</b>: {support_title}<br></p><p><b>Priorità</b>: {support_priority}<br></p><p><b>Data di fine</b>: {support_end_date}</p><p><br></p><p><b>Messaggio di supporto</b>:<br>{support_description}</p><p><b>Cordiali saluti</b>,<br>{app_name}</p>',
                    'ja' => '<p>こんにちは {assign_user}<br><br></p><p>新しいサポートチケットがオープンしました。.<br><br></p><p>題名: {support_title}<br></p><p>優先: {support_priority}<br></p><p>終了日: {support_end_date}</p><p><br></p><p>サポートメッセージ:<br>{support_description}<br><br></p><div class="tw-ta-container hide-focus-ring tw-lfl focus-visible" id="tw-target-text-container" tabindex="0" data-focus-visible-added="" style="overflow: hidden; position: relative; outline: 0px;"><pre class="tw-data-text tw-text-large XcVN5d tw-ta" data-placeholder="Translation" id="tw-target-text" dir="ltr" style="unicode-bidi: isolate; line-height: 32px; border: none; padding: 2px 0.14em 2px 0px; position: relative; margin-top: -2px; margin-bottom: -2px; resize: none; overflow: hidden; width: 277px; overflow-wrap: break-word;"><span lang="ja">敬具、</span>,</pre></div><p>{app_name}</p>',
                    'nl' => '<p><b>Hoi</b>&nbsp;{assign_user}<br><br></p><p>Er is een nieuw supportticket geopend.<br><br></p><p><b>Titel</b>: {support_title}<br></p><p><b>Prioriteit</b>: {support_priority}<br></p><p><b>Einddatum</b>: {support_end_date}</p><p><br></p><p><b>Ondersteuningsbericht</b>:<br>{support_description}<br><br></p><p><b>Vriendelijke groeten</b>,<br>{app_name}</p>',
                    'pl' => '<p><b>cześć</b>&nbsp;{assign_user}<br><br></p><p>Nowe zgłoszenie do pomocy technicznej zostało otwarte.<br><br></p><p><b>Tytuł</b>: {support_title}<br></p><p><b>Priorytet</b>: {support_priority}<br></p><p><b>Data końcowa</b>: {support_end_date}</p><p><br></p><p><b>Wiadomość pomocy</b>:<br>{support_description}<br><br></p><p><b>Z poważaniem</b>,<br>{app_name}</p>',
                    'ru' => '<p><b>Здравствуй</b>&nbsp;{assign_user}<br><br></p><p>Открыта новая заявка в службу поддержки.<br><br></p><p><b>заглавие</b>: {support_title}<br></p><p><b>Приоритет</b>: {support_priority}<br></p><p><b>Дата окончания</b>: {support_end_date}</p><p><br></p><p><b>Сообщение поддержки</b>:<br>{support_description}<br><br></p><p><b>С уважением</b>,<br>{app_name}</p>',
                    'pt' => '<p><b>Oi</b>&nbsp;{assign_user}<br><br></p><p>ОNovo ticket de suporte foi aberto.<br><br></p><p><b>Título</b>: {support_title}<br></p><p><b>Prioridade</b>: {support_priority}<br></p><p><b>Data final</b>: {support_end_date}</p><p><br></p><p><b>Mensagem de suporte</b>:<br>{support_description}<br><br></p><p><b>С Atenciosamente</b>,<br>{app_name}</p>',
                    'zh' => '<p><span style="font-size: 12pt;"><b>嗨</b> {assign_user}</span><br><br><span style="font-size: 12pt;">新的支持请求已打开。</span><br><br><span style="font-size: 12pt;"><strong>标题：</strong> {support_title}</span><br>< span style="font-size: 12pt;"><strong>优先级：</strong> {support_priority}</span><span style="font-size: 12pt;"><br></span><span style="font-size: 12pt;"><b>结束日期</b>：{support_end_date}</span></p><p><br><span style="font-size: 12pt;" ><strong>支持消息：</strong></span><br><span style="font-size: 12pt;">{support_description}</span><span style="font-size: 12pt;" ><br><br><b>亲切的问候</b>，</span><br>{app_name}</p>',
                    'he' => '<p><span style="font-size: 12pt;"><b>היי</b> {assign_user}</span><br><br><span style="font-size: 12pt;"> כרטיס תמיכה חדש נפתח.</span><br><br><span style="font-size: 12pt;"><strong>כותרת:</strong> {support_title}</span><br>< span style="font-size: 12pt;"><strong>עדיפות:</strong> {support_priority}</span><span style="font-size: 12pt;"><br></span><span style="font-size: 12pt;"><b>תאריך סיום</b>: {support_end_date}</span></p><p><br><span style="font-size: 12pt;" ><strong>הודעת תמיכה:</strong></span><br><span style="font-size: 12pt;">{support_description}</span><span style="font-size: 12pt;" ><br><br><b>בברכה</b>,</span><br>{app_name}</p>',
                    'tr' => '<p><span style="font-size: 12pt;"><b>Merhaba</b> {assign_user}</span><br><br><span style="font-size: 12pt;"> Yeni destek bileti açıldı.</span><br><br><span style="font-size: 12pt;"><strong>Başlık:</strong> {support_title}</span><br>< span style="font-size: 12pt;"><strong>Öncelik:</strong> {support_priority}</span><span style="font-size: 12pt;"><br></span><span style="font-size: 12pt;"><b>Bitiş Tarihi</b>: {support_end_date}</span></p><p><br><span style="font-size: 12pt;" ><strong>Destek mesajı:</strong></span><br><span style="font-size: 12pt;">{support_description}</span><span style="font-size: 12pt;" ><br><br><b>Saygılarımızla</b>,</span><br>{app_name}</p>',
                    'pt-br' => '<p><b>Oi</b>&nbsp;{assign_user}<br><br></p><p>ОNovo ticket de suporte foi aberto.<br><br></p><p><b>Título</b>: {support_title}<br></p><p><b>Prioridade</b>: {support_priority}<br></p><p><b>Data final</b>: {support_end_date}</p><p><br></p><p><b>Mensagem de suporte</b>:<br>{support_description}<br><br></p><p><b>С Atenciosamente</b>,<br>{app_name}</p>',
                ],
            ],
            'new_contract' => [
                'subject' => 'New Contract',
                'lang' => [
                    'ar' => '<p>&nbsp;</p>
                    <p><b>مرحبا</b> { contract_client }</p>
                    <p><b>موضوع العقد</b> : { contract_subject }</p>
                    <p><b>مشروع العقد </b>: { contract_project }</p>
                    <p><b>تاريخ البدء</b> : { contract_start_date }</p>
                    <p><b>تاريخ الانتهاء</b> : { contract_end_date }</p>
                    <p>. أتطلع لسماع منك</p>
                    <p><b>Regards نوع ،</b></p>
                    <p>{ company_name }</p>',
                    'da' => '<p>&nbsp;</p>
                    <p><b>Hej </b>{ contract_client }</p>
                    <p><b>Kontraktemne :&nbsp;</b>{ contract_subject }</p>
                    <p><b>Kontrakt-projekt :&nbsp;</b>{ contract_project }</p>
                    <p><b>Startdato&nbsp;</b>: { contract_start_date }</p>
                    <p><b>Slutdato&nbsp;</b>: { contract_end_date }</p>
                    <p>Jeg glæder mig til at høre fra dig.</p>
                    <p><b>Kind Hilds,</b></p>
                    <p>{ company_name }</p><p></p>',
                    'de' => '<p>&nbsp;</p>
                    <p><b>Hi</b> {contract_client}</p>
                    <p>&nbsp;<b style="font-family: var(--bs-body-font-family); text-align: var(--bs-body-text-align);">Vertragsgegenstand :</b><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);"> {contract_subject}</span></p>
                    <p><b>Vertragsprojekt :&nbsp;</b>{contract_project}</p>
                    <p><b>Startdatum&nbsp;</b>: {contract_start_date}</p>
                    <p><b>Enddatum&nbsp;</b>: {contract_end_date}</p>
                    <p>Freuen Sie sich auf das Hören von Ihnen.</p>
                    <p><b>Gütige Grüße,</b></p>
                    <p>{company_name}</p>',
                    'en' => '<p>&nbsp;</p>
                    <p><strong>Hi</strong> {contract_client}</p>
                    <p><b>Contract Subject</b>&nbsp;: {contract_subject}</p>
                    <p><b>Contract Project</b>&nbsp;: {contract_project}</p>
                    <p><b>Start Date&nbsp;</b>: {contract_start_date}</p>
                    <p><b>End Date&nbsp;</b>: {contract_end_date}</p>
                    <p>Looking forward to hear from you.</p>
                    <p><strong>Kind Regards, </strong></p>
                    <p>{company_name}</p>',
                    'es' => '<p><b>Hi </b>{contract_client} </p><p><span style="text-align: var(--bs-body-text-align);"><b>asunto del contrato</b></span><b>&nbsp;:</b> {contract_subject}</p><p><b>contrato proyecto </b>: {<span style="font-family: var(--bs-body-font-family); font-size: var(--bs-body-font-size); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">contract_project</span><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">}</span></p><p> </p><p><b>Start Date :</b> {contract_start_date} </p><p><b>Fecha de finalización :</b> {contract_end_date} </p><p>Con ganas de escuchar de usted. </p><p><b>Regards de tipo, </b></p><p>{contract_name}</p>',
                    'fr' => '<p><b>Bonjour</b> { contract_client }</p>
                    <p><b>Objet du contrat :</b> { contract_subject } </p><p><span style="text-align: var(--bs-body-text-align);"><b>contrat projet :</b></span>&nbsp;{ contract_project } </p><p><b>Date de début&nbsp;</b>: { contract_start_date } </p><p><b>Date de fin&nbsp;</b>: { contract_end_date } </p><p>Regard sur lavenir.</p>
                    <p><b>Sincères amitiés,</b></p>
                    <p>{ nom_entreprise }</p>',
                    'it' => '<p>&nbsp;</p>
                    <p>Ciao {contract_client}</p>
                    <p><b>Oggetto contratto :&nbsp;</b>{contract_subject} </p><p><b>Contract Project :</b> {contract_project} </p><p><b>Data di inizio</b>: {contract_start_date} </p><p><b>Data di fine</b>: {contract_end_date} </p><p>Non vedo lora di sentirti<br></p>
                    <p><b>Kind Regards,</b></p>
                    <p>{company_name}</p>',
                    'ja' => '<p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">こんにちは {contract_client}</span><br></p>
                    <p><b>契約件名&nbsp;</b>: {contract subject}</p>
                    <p><b>契約プロジェクト :</b> {contract_project}</p>
                    <p><b>開始日</b>: {contract_start_date}</p>
                    <p>&nbsp;<b style="font-family: var(--bs-body-font-family); text-align: var(--bs-body-text-align);">終了日</b><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">: {contract_end_date}</span></p><p><span style="text-align: var(--bs-body-text-align);">あなたから聞いて楽しみにして</span></p><p><span style="text-align: var(--bs-body-text-align);"><b>敬具、</b><br></span></p>
                    <p>{ company_name}</p>',
                    'nl' => '<p>&nbsp;</p>
                    <p><b>Hallo</b> { contract_client }</p>
                    <p><b>Contractonderwerp</b> : { contract_subject } </p><p><b>Contractproject</b> : { contract_project } </p><p><b>Begindatum</b> : { contract_start_date } </p><p><b>Einddatum&nbsp;</b>: { contract_end_date } </p><p>Naar voren komen om van u te horen.</p><p><b>Met vriendelijke groeten</b>,<br></p>
                    <p>{ bedrijfsnaam }</p>',
                    'pl' => '<p>&nbsp;</p>
                    <p><b>Witaj</b> {contract_client }</p>
                    <p><b>Temat umowy :&nbsp;</b>{contract_subject } </p><p><b>Projekt kontraktu</b>&nbsp;: {contract_project } </p><p><b>Data rozpoczęcia&nbsp;</b>: {contract_start_date } </p><p><b>Data zakończenia&nbsp;</b>: {contract_end_date } </p><p>Z niecierżną datą i z niecierżką na Ciebie.</p>
                    <p><b>W Odniesieniu Do Rodzaju,</b></p>
                    <p>{company_name }</p>',
                    'ru' => '<p></p>
                    <p><b>Здравствуйте</b> { contract_client }</p>
                    <p><b>Субъект договора :</b> { contract_subject } </p><p><b>Проект договора</b>: { contract_project } </p><p><b>Начальная дата </b>: { contract_start_date } </p><p><b>Конечная дата </b>: { contract_end_date } </p><p>нетерпением ожидаю услышать от вас.</p>
                    <p><b>Привет.</b></p>
                    <p>{ company_name }</p>',
                    'pt' => '<p>&nbsp;</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Olá</b></span>&nbsp;{contract_client}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Assunto do Contrato</b></span>&nbsp;: {contract_subject}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Projeto de contrato&nbsp;</b></span>: {contract_project}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Data de início</b></span><b>&nbsp;</b>: {contract_start_date}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Data final</b></span><b>&nbsp;</b>: {contract_end_date}</p>
                    <p>Ansioso para ouvir de você.</p>
                    <p><b>Atenciosamente,</b><br></p>
                    <p>{company_name}</p>',
                    'zh' => '<p> </p>
                    <p><strong>嗨</strong> {contract_client}</p>
                    <p><b>合同主题</b>：{contract_subject}</p>
                    <p><b>合同项目</b>：{contract_project}</p>
                    <p><b>开始日期</b>：{contract_start_date}</p>
                    <p><b>结束日期</b>：{contract_end_date}</p>
                    <p>期待您的来信。</p>
                    <p><strong>亲切的问候，</strong></p>
                    <p>{公司名称}</p>',
                    'he' => '<p> </p>
                    <p><strong>היי</strong> {contract_client}</p>
                    <p><b>נושא החוזה</b> : {contract_subject}</p>
                    <p><b>פרויקט חוזה</b> : {contract_project}</p>
                    <p><b>תאריך התחלה </b>: {contract_start_date}</p>
                    <p><b>תאריך סיום </b>: {contract_end_date}</p>
                    <p>מצפה לשמוע ממך.</p>
                    <p><strong>בברכה, </strong></p>
                    <p>{company_name}</p>',
                    'tr' => '<p> </p>
                    <p><strong>Merhaba</strong> {contract_client}</p>
                    <p><b>Sözleşme Konusu</b> : {contract_subject}</p>
                    <p><b>Sözleşme Projesi</b> : {contract_project}</p>
                    <p><b>Başlangıç ​​Tarihi </b>: {contract_start_date}</p>
                    <p><b>Bitiş Tarihi </b>: {contract_end_date}</p>
                    <p>Sizden haber bekliyorum.</p>
                    <p><strong>Saygılarımızla, </strong></p>
                    <p>{şirket_adı</p>',
                    'pt-br' => '<p>&nbsp;</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Olá</b></span>&nbsp;{contract_client}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Assunto do Contrato</b></span>&nbsp;: {contract_subject}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Projeto de contrato&nbsp;</b></span>: {contract_project}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Data de início</b></span><b>&nbsp;</b>: {contract_start_date}</p>
                    <p><span style="text-align: var(--bs-body-text-align);"><b>Data final</b></span><b>&nbsp;</b>: {contract_end_date}</p>
                    <p>Ansioso para ouvir de você.</p>
                    <p><b>Atenciosamente,</b><br></p>
                    <p>{company_name}</p>',
                ],
            ],
        ];

        $email = EmailTemplate::all();

        foreach ($email as $e) {

            foreach ($defaultTemplate[$e->slug]['lang'] as $lang => $content) {
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


    public static function userDefualtView($request)
    {
        $userId      = \Auth::user()->id;
        $defaultView = UserDefualtView::where('module', $request->module)->where('user_id', $userId)->first();

        if (empty($defaultView)) {
            $userView = new UserDefualtView();
        } else {
            $userView = $defaultView;
        }

        $userView->module  = $request->module;
        $userView->route   = $request->route;
        $userView->view    = $request->view;
        $userView->user_id = $userId;
        $userView->save();
    }

    public function getDefualtViewRouteByModule($module)
    {
        if($this->module != $module)
        {
            $userId      = \Auth::user()->id;
            $defaultView = UserDefualtView::select('route')->where('module', $module)->where('user_id', $userId)->first();
            $this->defaultView = $defaultView ;
            $this->module = $module ;

        }else{
            $defaultView = $this->defaultView ;
        }

        return !empty($defaultView) ? $defaultView->route : '';
    }

    public function isUser()
    {
        return $this->type === 'user' ? 1 : 0;
    }
}
