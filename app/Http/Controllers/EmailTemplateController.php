<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\UserEmailTemplate;
use App\Models\Utility;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{

    public function index()
    {
        $usr = \Auth::user();

        if ($usr->type == 'super admin' || $usr->type == 'company') {
            $EmailTemplates = EmailTemplate::all();

            return view('email_templates.index', compact('EmailTemplates'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->type == 'super admin') {
            return view('email_templates.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        $usr = \Auth::user();

        if (\Auth::user()->type == 'super admin') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $EmailTemplate             = new EmailTemplate();
            $EmailTemplate->name       = $request->name;
            $EmailTemplate->slug       = strtolower(str_replace(' ', '_', $request->name));
            $EmailTemplate->from       = env('APP_NAME');
            $EmailTemplate->created_by = $usr->id;
            $EmailTemplate->save();

            return redirect()->route('email_template.index')->with('success', __('Email Template succsesfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(EmailTemplate $emailTemplate)
    {
        //
    }


    public function edit(EmailTemplate $emailTemplate)
    {
        //
    }

    public function update(Request $request, $id)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'from' => 'required',
                'subject' => 'required',
                'content' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $emailTemplate       = EmailTemplate::where('id', $id)->first();
        $emailTemplate->from = $request->from;
        $emailTemplate->save();

        $emailLangTemplate = EmailTemplateLang::where('parent_id', '=', $id)->where('lang', '=', $request->lang)->first();

        // if record not found then create new record else update it.
        if (empty($emailLangTemplate)) {
            $emailLangTemplate            = new EmailTemplateLang();
            $emailLangTemplate->parent_id = $id;
            $emailLangTemplate->lang      = $request['lang'];
            $emailLangTemplate->subject   = $request['subject'];
            $emailLangTemplate->content   = $request['content'];
            $emailLangTemplate->save();
        } else {
            $emailLangTemplate->subject = $request['subject'];
            $emailLangTemplate->content = $request['content'];
            $emailLangTemplate->save();
        }

        return redirect()->route(
            'manage.email.language',
            [
                $emailTemplate->id,
                $request->lang,
            ]
        )->with('success', __('Email Template successfully updated.'));
    }


    public function destroy(EmailTemplate $emailTemplate)
    {
        //
    }

    // Used For View Email Template Language Wise
    public function manageEmailLang($id, $lang = 'en')
    {
        if (\Auth::user()->type == 'super admin') {
            $languages         = Utility::languages();
            // $emailTemplate     = EmailTemplate::first();
            // $currEmailTempLang = EmailTemplateLang::where('lang', $lang)->first();
            $currEmailTempLang = EmailTemplateLang::where('parent_id', '=', $id)->where('lang', $lang)->first();
            if (!isset($currEmailTempLang) || empty($currEmailTempLang)) {
                $currEmailTempLang       = EmailTemplateLang::where('parent_id', '=', $id)->where('lang', 'en')->first();

                $currEmailTempLang->lang = $lang;
            }

            if (\Auth::user()->type == 'super admin') {
                $emailTemplate     = EmailTemplate::where('id', '=', $id)->first();
            } else {

                $settings         = Utility::settings();
                $emailTemplate     = $settings['company_name'];
            }
            $EmailTemplates = EmailTemplate::all();


            return view('email_templates.show', compact('emailTemplate', 'languages', 'currEmailTempLang', 'EmailTemplates'));
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    // Used For Store Email Template Language Wise
    public function storeEmailLang(Request $request, $id)
    {
        if (\Auth::user()->type == 'super admin') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'subject' => 'required',
                    'content' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $emailLangTemplate = EmailTemplateLang::where('parent_id', '=', $id)->where('lang', '=', $request->lang)->first();

            // if record not found then create new record else update it.
            if (empty($emailLangTemplate)) {
                $emailLangTemplate            = new EmailTemplateLang();
                $emailLangTemplate->parent_id = $id;
                $emailLangTemplate->lang      = $request['lang'];
                $emailLangTemplate->subject   = $request['subject'];
                $emailLangTemplate->content   = $request['content'];
                $emailLangTemplate->save();
            } else {
                $emailLangTemplate->subject = $request['subject'];
                $emailLangTemplate->content = $request['content'];

                $emailLangTemplate->save();
            }

            return redirect()->route(
                'manage.email.language',
                [
                    $id,
                    $request->lang,
                ]
            )->with('success', __('Email Template Detail successfully updated.'));
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    // Used For Update Status Company Wise.
    public function updateStatus(Request $request)
    {
        $post = $request->all();
        unset($post['_token']);
        $usr = \Auth::user();
        if ($usr->type == 'super admin' || $usr->type == 'company') {
            UserEmailTemplate::where('user_id', $usr->id)->update(['is_active' => 0]);
            foreach ($post as $key => $value) {
                $UserEmailTemplate  = UserEmailTemplate::where('user_id', $usr->id)->where('template_id', $key)->first();
                $UserEmailTemplate->is_active = $value;
                $UserEmailTemplate->save();
            }
            return redirect()->back()->with('success', __('Status successfully updated!'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    // public function updateStatus(Request $request)
    // {
    //    // return redirect()->back()->with('error', __('This operation is not perform due to demo mode.'));

    //         $post = $request->all();
    //         unset($post['_token']);
    //         $usr = \Auth::user();

    //     if($usr->type == 'super admin' || $usr->type == 'company')
    //     {
    //         $UserEmailTemplate  = UserEmailTemplate::where('user_id', '=', $usr->id)->get()->pluck('id');

    //         $emailTemplateStatus=[];
    //         foreach($UserEmailTemplate as $UserEmailTemplateId){
    //             $emailTemplateStatus[$UserEmailTemplateId]= $post[$UserEmailTemplateId] ?? 0;
    //         }

    //         foreach ($emailTemplateStatus as $key => $value) {
    //             $user_email = UserEmailTemplate::where('id', '=', $key)->where('user_id', '=', $usr->id)->first();
    //             $user_email->is_active = $value;
    //             $user_email->save();
    //         }

    //         return redirect()->back()->with('success', __('Status successfully updated!'));

    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Permission Denied.'));

    //     }
    // }

}
