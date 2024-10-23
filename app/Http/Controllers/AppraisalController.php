<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\Competencies;
use App\Models\User;
use App\Models\Branch;
use App\Models\Indicator;
use App\Models\Employee;
use App\Models\PerformanceType;
use Illuminate\Http\Request;

class AppraisalController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            $user = \Auth::user();
            if($user->type == 'employee')
            {
                $employee   = Employee::where('user_id', $user->id)->first();
                $competencyCount = Competencies::where('created_by', '=', $user->creatorId())->count();
                $appraisals = Appraisal::where('appraisals.created_by', '=', \Auth::user()->creatorId())->where('branch', $employee->branch_id)
                ->where('employee', $employee->id)
                ->with('user')->with('branches')->with('employees')
                ->leftJoin('employees', 'appraisals.employee', '=', 'employees.id')
                ->leftJoin('departments', 'employees.department', '=', 'departments.id')
                ->leftJoin('designations', 'employees.designation', '=', 'designations.id')
                ->get();
             }
            else
            {
                $competencyCount = Competencies::where('created_by', '=', $user->creatorId())->count();
                $appraisals = Appraisal::select('appraisals.*','departments.name as department_name', 'designations.name as designation_name', 'designations.id as designation_id')
                ->where('appraisals.created_by', '=', \Auth::user()->creatorId())
                ->with('user')->with('branches')->with('employees')
                ->leftJoin('employees', 'appraisals.employee', '=', 'employees.id')
                ->leftJoin('departments', 'employees.department', '=', 'departments.id')
                ->leftJoin('designations', 'employees.designation', '=', 'designations.id')
                ->get();
             }

             return view('appraisal.index', compact('appraisals','competencyCount'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
 
    public function create()
    {
        $performance       = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get();
        $branches = Branch::where('created_by', '=', \Auth::user()->creatorId())->get();
        return view('appraisal.create', compact('performance','branches'));
     }


    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'branches'   => 'required', 
                                   'employee' => 'required',
                               ]);
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $appraisal                 = new Appraisal();
            $appraisal->branch         = $request->branches;
            $appraisal->employee       = $request->employee;
            $appraisal->appraisal_date = $request->appraisal_date;
            $appraisal->rating         = json_encode($request->rating, true);
            $appraisal->remark         = $request->remark;
            $appraisal->created_by     = \Auth::user()->creatorId();
            $appraisal->save();
            return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully created.'));
        }
    }

    public function show(Appraisal $appraisal)
    {
        $rating = json_decode($appraisal->rating, true);
        $performance_types    = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get();
        $employee = Employee::find($appraisal->employee);
        $indicator = Indicator::where('branch',$employee->branch_id)->where('department',$employee->department)->where('designation',$employee->designation)->first();     
        $ratings = json_decode($indicator->rating, true);
         return view('appraisal.show', compact('appraisal', 'performance_types', 'rating','ratings'));
     }
    public function edit(Appraisal $appraisal)
    {
        $performance_types = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get();
        $employee   = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name','id');
        $employee->prepend('Select Employee', '');
        $branches = Branch::where('created_by', '=', \Auth::user()->creatorId())->get();
        $rating = json_decode($appraisal->rating,true);
        return view('appraisal.edit', compact('branches' ,'employee', 'appraisal', 'performance_types','rating'));
    }

    public function update(Request $request, Appraisal $appraisal)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                    'branches' => 'required',
                    'employees' => 'required',
                    'rating'=> 'required',
                    ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $appraisal->branch         = $request->branches;
            $appraisal->employee            = $request->employees;
            $appraisal->appraisal_date      = $request->appraisal_date;
            $appraisal->rating         = json_encode($request->rating, true);          
            $appraisal->remark              = $request->remark;
            $appraisal->save();

            return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully updated.'));
        }
    }


    public function destroy(Appraisal $appraisal)
    {
        if(\Auth::user()->type == 'company')
        {
            $appraisal->delete();

            return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function getemployee(Request $request)
    {
        // dd($request);
         $data['employee'] = Employee::where('branch_id',$request->branch_id)->where('created_by',\Auth::user()->creatorId())->get();
        return response()->json($data);
     }

    public function empByStar(Request $request)
    {
        // @dd($request->all());
         $employee = Employee::find($request->employee);
        // $employee = Employee::where('employee_id',$request->employee_id)->first(); 
         $indicator = Indicator::where('branch',$employee->branch_id)->where('department',$employee->department)->where('designation',$employee->designation)->first();
 
 
        $ratings = json_decode($indicator->rating, true);
       
        $performance_types = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get();
        
        $viewRender = view('appraisal.star', compact('ratings','performance_types'))->render();
        
        return response()->json(array('success' => true, 'html'=>$viewRender));
  
    }
    public function empByStar1(Request $request)
    {
        // \DB::connection()->enableQueryLog();
        $employee = Employee::where('employee_id',$request->employee)->first(); 
        // dd(\DB::getQueryLog());
        //$employee = Employee::find($request->employee);
        
        $appraisal = Appraisal::find($request->appraisal);
        // dd($appraisal);

        $indicator = Indicator::where('branch',$employee->branch_id)->where('department',$employee->department)->where('designation',$employee->designation)->first();
     
        $ratings = json_decode($indicator->rating, true);
        $rating = json_decode($appraisal->rating,true);
        $performance_types = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get();
        $viewRender = view('appraisal.staredit', compact('ratings','rating','performance_types'))->render();
        // dd($viewRender);
        return response()->json(array('success' => true, 'html'=>$viewRender));
  
    }    
}
