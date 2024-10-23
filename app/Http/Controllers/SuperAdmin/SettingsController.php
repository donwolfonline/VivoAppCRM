<?php
// This file use for handle super admin setting page

namespace Modules\PaiementPro\Http\Controllers\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {
        return view('paiementpro::super-admin.settings.index',compact('settings'));
    }


    /**
     * Store a newly created resource in storage.
     */

}
