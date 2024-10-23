<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Plan;
use App\Exports\EmployeeExport;
use App\Imports\EmployeeImport;
use App\Models\SalaryType;
use App\Models\User;
use App\Models\Utility;
use App\Models\Mail\CommonEmailTemplate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{

    public function index(Request $request)
    {

        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee') {
            $status           =     Employee::$statues;
            $department       =     Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All');
            $designation      =     Designation::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designation->prepend('All', '');
            // $employees        =     User::select('users.*', 'employees.department', 'employees.designation')->leftJoin('employees', 'users.id', '=', 'employees.user_id')->where('type', 'employee')->where('users.created_by', '=', \Auth::user()->creatorId());


            $employees = User::select('users.*', 'employees.department', 'employees.designation', 'departments.name as department_name', 'designations.name as designation_name')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('departments', 'employees.department', '=', 'departments.id')
            ->leftJoin('designations', 'employees.designation', '=', 'designations.id')
            ->where('type', 'employee')
            ->where('users.created_by', '=', \Auth::user()->creatorId());
            // ->get();



            if (!empty($request->department)) {
                $employees->where('employees.department', $request->department);
            }
            if (!empty($request->designation)) {
                $employees->where('employees.designation', $request->designation);
            }
            $employees         =    $employees->get();

            return view('employee.index', compact('status', 'department', 'designation', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        return view('employee.create');
    }


    public function store(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $default_language = Utility::getValByName('default_language');
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:120',
                    'email' => [
                        'required',
                        Rule::unique('users')->where(function ($query) {
                            return $query->where('created_by', \Auth::user()->id);
                        })
                    ],
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $enableLogin = 0;
            if(!empty($request->password_switch) && $request->password_switch == 'on')
            {
                $enableLogin   = 1;
                $validator = \Validator::make(
                    $request->all(), ['password' => 'required|min:6']
                );

                if($validator->fails())
                {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }
            }
            $authUser           =   \Auth::user();
            $creator            =   User::find($authUser->creatorId());
            $totalEmployee      =   $authUser->countEmployees($creator->id);
            $plan               =   Plan::find($creator->plan);


            $userpassword               = $request->input('password');
            if ($totalEmployee < $plan->max_employee || $plan->max_employee == -1) {
                $user                       =   new User();
                $user->name                 =   $request->name;
                $user->email                =   $request->email;
                $user['password'] = !empty($userpassword) ? \Hash::make($userpassword) : null;
                $user->type                 =   'employee';
                $user->lang                 =   !empty($default_language) ? $default_language : 'en';
                $user->created_by           =   \Auth::user()->creatorId();
                $user->email_verified_at    =   date("H:i:s");
                $user->avatar               =   '';
                $user['is_enable_login'] = $enableLogin;
                $user->save();


                if (!empty($user)) {
                    $employee                =  new Employee();
                    $employee->user_id       =  $user->id;
                    $employee->employee_id   =  $this->employeeNumber();
                    $employee->name          =  $user->name;
                    $employee->created_by    =  \Auth::user()->creatorId();
                    $employee->save();
                }

                $uArr = [
                    'email'     =>  $user->email,
                    'password'  =>  $request->password,
                ];

                $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr);


                return redirect()->route('employee.index')->with('success', __('Employee created Successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } else {
                return redirect()->back()->with('error', __('Your employee limit is over, Please upgrade plan.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($id)
    {
        // dump(decrypt($id));
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee') {
            $eId        =   \Crypt::decrypt($id);
            $user       =   User::find($eId);
            $employee   =   Employee::where('user_id', $eId)->first();
            return view('employee.view', compact('user', 'employee'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getdepartment(Request $request)
    {
        if ($request->branch_id == 0) {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($departments);
    }

    public function edit($id)
    {
        $eId        = \Crypt::decrypt($id);
        //Branchges
        $branches = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $branches->prepend('Select Branch', '');
        //Department
        $department = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $department->prepend('Select Department', '');
        //Designation
        $designation = Designation::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $designation->prepend('Select Designation', '');
        //SalaryType
        $salaryType = SalaryType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $salaryType->prepend('Select Type', '');
        $user = User::find($eId);
        $employee = Employee::where('user_id', $eId)->first();
        $employeesId  = \Auth::user()->employeeIdFormat(!empty($employee->employee_id) ? $employee->employee_id : '');
        $departmentData  = Department::where('created_by', \Auth::user()->creatorId())->where('branch_id', $employee->branch_id)->get()->pluck('name', 'id');
        //  dd($departmentData);

        return view('employee.edit', compact('user', 'departmentData', 'employeesId', 'branches', 'employee', 'department', 'designation', 'salaryType'));
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'dob'              =>  'required',
                    'gender'           =>  'required',
                    'address'          =>  'required',
                    'mobile'           =>  'required',
                    'branch_id'        =>  'required',
                    'department_id'    =>  'required',
                    'designation_id'   =>  'required',
                    'joining_date'     =>  'required',
                    'salary_type'      =>  'required',
                    'salary'           =>  'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->name)) {
                $user       = User::find($id);
                $user->name = $request->name;
                $user->save();
            }

            $employee               = Employee::where('user_id', $id)->first();
            $employee->gender       = $request->gender;
            $employee->address      = $request->address;
            $employee->mobile       = $request->mobile;
            $employee->branch_id    =  $request->branch_id;
            $employee->department   = $request->department;
            $employee->designation  = $request->designation;
            $employee->designation  = $request->designation;
            $employee->dob          = date("Y-m-d", strtotime($request->dob));
            $employee->joining_date = date("Y-m-d", strtotime($request->joining_date));
            $employee->exit_date    = !empty($request->exit_date) ? date("Y-m-d", strtotime($request->exit_date)) : '';
            $employee->salary_type  = $request->salary_type;
            $employee->salary       = $request->salary;
            $employee->save();

            return redirect()->route('employee.index')->with(
                'success',
                'Employee successfully updated.'
            );
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function destroy($id)
    {
        if (\Auth::user()->type == 'company') {
            $user = User::find($id);
            $user->delete();
            return redirect()->route('employee.index')->with('success', __('Employee successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function employeeNumber()
    {
        $maxId = \DB::table('employees')->where('created_by', '=', \Auth::user()->creatorId())->max('employee_id');
        for ($i = 1; $i <= $maxId + 1; $i++) {
            $latestExists = Employee::where('created_by', '=', \Auth::user()->creatorId())->where('employee_id', $i)->exists();
            if (!$latestExists) {
                return $i;
            }
        }
    }


    // function employeeNumber()
    // {
    //     $latest = Employee::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
    //     if (!$latest) {
    //         return 1;
    //     }
    //     return $latest->employee_id + 1;
    // }



    public function json(Request $request)
    {
        $designations = Designation::where('department', $request->department_id)->get()->pluck('name', 'id')->toArray();

        return response()->json($designations);
    }

    public function employeePersonalInfoEdit(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'dob' => 'required',
                'gender' => 'required',
                'address' => 'required',
                'mobile' => 'required',
                'emergency_contact' => 'required',
                //'profile' => 'mimes:jpeg,png,jpg|max:20480',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $user = User::find($id);
        if ($request->hasFile('profile')) {
            //storage limit
            $image_size = $request->file('profile')->getSize();
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
            if ($result == 1) {
                $filenameWithExt = $request->file('profile')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('profile')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $settings = Utility::getStorageSetting();
                //$dir        = storage_path('uploads/avatar/');
                if ($settings['storage_setting'] == 'local') {
                    $dir        = 'uploads/avatar/';
                } else {
                    $dir        = 'uploads/avatar';
                }
                $image_path = $dir . $user->avatar;

                if (\File::exists($image_path)) {
                    \File::delete($image_path);
                }
                $path = Utility::upload_file($request, 'profile', $fileNameToStore, $dir, []);

                // if(!file_exists($dir))
                // {
                //     mkdir($dir, 0777, true);
                // }

                // $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);
                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->route('employee.index', \Auth::user()->id)->with('error', __($path['msg']));
                }
            }
        }
        if (!empty($request->name)) {
            //storage limit
            if (!empty($request->file('profile'))) {
                $file_path = 'uploads/files/' . $user->profile;
                // $image_size = $request->file('profile')->getSize();
                $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                if ($result == 1) {
                    Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
                    $user        = User::find($id);
                    $user->name  = $request->name;
                    if (!empty($request->profile)) {
                        $user->avatar = $fileNameToStore;
                    }
                }
            }
            $user->save();
        }

        $employee                       = Employee::where('user_id', $id)->first();
        $employee->gender               = $request->gender;
        $employee->address              = $request->address;
        $employee->name                 = $request->name;
        $employee->mobile               = $request->mobile;
        $employee->emergency_contact    = $request->emergency_contact;
        $employee->dob                  = date("Y-m-d", strtotime($request->dob));
        $employee->save();

        return redirect()->back()->with('success', 'Employee personal information successfully updated.');
    }

    public function employeeCompanyInfoEdit(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'branch_id'         => 'required',
                'department_id'     => 'required',
                'designation_id'    => 'required',
                'joining_date'      => 'required',
                'salary_type'       => 'required',
                'salary'            => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if (!empty($request->name)) {
            $user       = User::find($id);
            $user->name = $request->name;

            $user->save();
        }

        $employee                    =      Employee::where('user_id', $id)->first();
        $employee->branch_id         =      $request->branch_id;
        $employee->department        =      $request->department_id;
        $employee->designation       =      $request->designation_id;
        $employee->joining_date      =      date("Y-m-d", strtotime($request->joining_date));
        $employee->exit_date         =      !empty($request->exit_date) ? date("Y-m-d", strtotime($request->exit_date)) : new \DateTime();
        $employee->salary_type       =      $request->salary_type;
        $employee->salary            =      $request->salary;
        $employee->save();

        return redirect()->back()->with(
            'success',
            'Employee company successfully updated.'
        );
    }

    public function employeeBankInfoEdit(Request $request, $id)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'account_holder_name' => 'required',
                'account_number' => 'required',
                'bank_name' => 'required',
                'bank_identifier_code' => 'required',
                'branch_location' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $employee                       = Employee::where('user_id', $id)->first();
        $employee->account_holder_name  = $request->account_holder_name;
        $employee->account_number       = $request->account_number;
        $employee->bank_name            = $request->bank_name;
        $employee->bank_identifier_code = $request->bank_identifier_code;
        $employee->branch_location      = $request->branch_location;
        $employee->save();

        return redirect()->route('employee.index')->with(
            'success',
            'Employee bank detail successfully updated.'
        );
    }

    public function employeeIdFormat()
    {
        $latest = Employee::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }

    public function export()
    {
        $name = 'employee' . date('Y-m-d i:h:s');
        $data = Excel::download(new EmployeeExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }


    public function importFile()
    {
        return view('employee.import');
    }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $customers          =    (new EmployeeImport())->toArray(request()->file('file'))[0];
        $totalCustomer      =    count($customers) - 1;
        $errorArray         =    [];


        for ($i = 1; $i <= count($customers) - 1; $i++) {
            $customer                           = $customers[$i];
            $customerByEmail                    = User::where('email', $customer[1])->first();
            if (empty($customerByEmail)) {
                $customerData                   = new User();
                $customerData->name             =    $customer[0];
                $customerData->email            =    $customer[1];
                $customerData->password         =    Hash::make($customer[2]);
                $customerData->type             =    'employee';
                $customerData->is_active        =    1;
                $customerData->lang             =    'en';
                $customerData->created_by       =    \Auth::user()->creatorId();
                $customerData->save();
                if (!empty($customerData)) {
                    $employee                   =    new Employee();
                    $employee->user_id          =    $customerData->id;
                    $employee->employee_id      =    $this->employeeNumber();
                    $employee->created_by       =    \Auth::user()->creatorId();
                    $employee->save();
                }
            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalCustomer . ' ' . 'record');

            foreach ($errorArray as $errorData) {
                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }


    public function employeePassword($id)
    {
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);

        $employee = Employee::where('user_id', $eId)->first();

        return view('employee.reset', compact('user', 'employee'));
    }

    public function employeePasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'password' => 'required|confirmed|same:password_confirmation',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user                 = User::where('id', $id)->first();
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return redirect()->route('employee.index')->with(
            'success',
            'Employee Password successfully updated.'
        );
    }
    public function LoginManage($id)
    {
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);
        if($user->is_enable_login == 1)
        {
            $user->is_enable_login = 0;
            $user->save();
            return redirect()->back()->with('success', __('User login disable successfully.'));
        }
        else
        {
            $user->is_enable_login = 1;
            $user->save();
            return redirect()->back()->with('success', __('User login enable successfully.'));
        }
    }
}
