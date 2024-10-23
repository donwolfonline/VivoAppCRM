<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use App\Models\Utility;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
       
        return $request->user()->hasVerifiedEmail()
      
                    ? redirect()->intended(RouteServiceProvider::HOME)
                    : view('auth.verify');
                    

    }
    public function showVerifcation($lang = '')
    {
        if (empty($lang)) {
            $lang = Utility::getValByName('default_language');
        }
        // dd($lang);
        \App::setLocale($lang);
        // dd($lang);
        return view('auth.verify', compact('lang'));
    }
}
