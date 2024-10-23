<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\LandingPageSections;
use App\Models\Utility;
use Illuminate\Support\Facades\App;
use Cookie;

class XSS
{
    use \RachidLaasri\LaravelInstaller\Helpers\MigrationsHelper;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        if(Auth::check())
        {
            //$language = Cookie::get('LANGUAGE');
            //dd($language);
            
            // \App::setLocale($language);
            App::setLocale(Auth::user()->lang);
            
            if(Auth::user()->type == 'super admin')
            {
                if(Schema::hasTable('ch_messages'))
                {
                    if(Schema::hasColumn('ch_messages', 'type') == false)
                    {
                        Schema::drop('ch_messages');
                        \DB::table('migrations')->where('migration', 'like', '%ch_messages%')->delete();
                    }
                }
                
                $migrations             = $this->getMigrations();
                $messengerMigration     = Utility::get_messenger_packages_migration();
                $dbMigrations           = $this->getExecutedMigrations();

                $numberOfUpdatesPending = (count($migrations) + $messengerMigration) - count($dbMigrations);                
               
                if($numberOfUpdatesPending > 0)
                  {
                      // run code like seeder only when new migration
                    //   Utility::addNewData();

                      return redirect()->route('LaravelUpdater::welcome');
                  }

                  // $landingdata = LandingPageSections::all()->count();

                  // if($landingdata == 0){
                  //     Utility::add_landing_page_data();
                  // }
              }
          }

       if(\Request::route()->getName() == 'chatify')
        {
            if(!\Auth::check())
            {
                return redirect()->back();
            }

            if(empty(env('CHAT_MODULE')) || Auth::user()->type == 'Super Admin' || Auth::user()->type == 'Client')
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }

        $input = $request->all();
        $request->merge($input);

        return $next($request);
    }
}
