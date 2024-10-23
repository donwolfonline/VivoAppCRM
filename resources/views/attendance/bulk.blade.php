@extends('layouts.admin')
@push('script-page')
    <script>
        $('#present_all').click(function (event) {
            if (this.checked) {
                $('.present').each(function () {
                    this.checked = true;
                });

                $('.present_check_in').removeClass('d-none');
                $('.present_check_in').addClass('d-block');

            } else {
                $('.present').each(function () {
                    this.checked = false;
                });
                $('.present_check_in').removeClass('d-block');
                $('.present_check_in').addClass('d-none');

            }
        });

        $('.present').click(function (event) {
            var div = $(this).parent().parent().parent().parent().find('.present_check_in');
            if (this.checked) {
                div.removeClass('d-none');
                div.addClass('d-block');

            } else {
                div.removeClass('d-block');
                div.addClass('d-none');
            }

        });


    </script>
    <script>
        // $(document).ready(function () {
        //     $('.daterangepicker').daterangepicker({
        //         format: 'yyyy-mm-dd',
        //         locale: {format: 'YYYY-MM-DD'},
        //     });
        // });
    </script>
@endpush
@section('page-title')
    {{__('Bulk Attendance')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Bulk Attendance')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Bulk Attendance')}}</li>
@endsection
@section('content')
<div class="col-xl-12">
    <div class="row">
        <div class="col-12">
            <div class="{{isset($_GET['department'])?'show':''}}" id="collapseExample">
                <div class="card card-body">
                    {{ Form::open(array('route' => array('bulk.attendance'),'method'=>'get')) }}
                    <div class="row filter-css">
                        <div class="col-auto">
                            {{Form::date('date',isset($_GET['date'])?$_GET['date']:date('Y-m-d'),array('class'=>'form-control'))}}
                        </div>
                        <div class="col-md-2">
                            {{-- @dd($department); --}}
                            {{ Form::select('department', $department,isset($_GET['department'])?$_GET['department']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
                        </div>
                        <div class="action-btn bg-info ms-2">
                            <div class="col-auto">
                                <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-bs-toggle="tooltip" ]
                                title="{{__('Apply')}}"><i class="ti ti-search  text-white"></i></button>
                            </div>
                        </div>
                        <div class="action-btn bg-danger ms-2">
                            <div class="col-auto">
                                <a href="{{route('bulk.attendance')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}"
                                class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white" ></i></a>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>


    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style pb-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="10%">{{__('Employee Id')}}</th>
                                <th>{{__('Employee')}}</th>
                                <th>{{__('Department')}}</th>
                                <th>
                                    <div class="form-group my-auto">
                                        <div class="custom-control">
                                            <input class="form-check-input" type="checkbox" name="present_all" id="present_all" {{ old('remember') ? 'checked' : '' }}>
                                            <span class="custom-control-label" for="present_all"> {{__('Attendance')}}</span>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {{Form::open(array('route'=>array('bulk.attendance'),'method'=>'post'))}}

                                <input type="hidden" value="{{isset($_GET['date'])?$_GET['date']:date('Y-m-d')}}" name="date">
                                <input type="hidden" value="{{isset($_GET['branch'])?$_GET['branch']:''}}" name="branch">
                                <input type="hidden" value="{{isset($_GET['department'])?$_GET['department']:''}}" name="department">
                                @forelse($employees as $employee)
                                    @php

                                        $attendance=$employee->present_status($employee->user_id,isset($_GET['date'])?$_GET['date']:date('Y-m-d'));

                                    @endphp
                                <tr>
                                    <td>
                                        <input type="hidden" value="{{$employee->user_id}}" name="employee_id[]">
                                        {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}
                                    </td>
                                    <td>{{!empty($employee->users)?$employee->users->name:''}}</td>
                                    <td>{{!empty($employee->departments)?$employee->departments->name:''}}</td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <div class="custom-control">
                                                        <input class="form-check-input present" type="checkbox" name="present-{{$employee->user_id}}" id="present{{$employee->user_id}}" {{ (!empty($attendance)&&$attendance->status == 'Present') ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="present{{$employee->user_id}}"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8 present_check_in {{ empty($attendance) ? 'd-none' : '' }} ">
                                                <div class="row">
                                                    <label class="col-auto control-label">{{__('In')}}</label>
                                                    <div class="col-auto">
                                                        <input type="time" class="form-control" name="in-{{$employee->user_id}}" value="{{!empty($attendance) && $attendance->clock_in!='00:00:00' ? $attendance->clock_in : \Utility::getValByName('company_start_time')}}">
                                                    </div>

                                                    <label for="inputValue" class="col-auto control-label">{{__('Out')}}</label>
                                                        <div class="col-auto">
                                                            <input type="time" class="form-control" name="out-{{$employee->user_id}}" value="{{!empty($attendance) &&  $attendance->clock_out !='00:00:00'? $attendance->clock_out : \Utility::getValByName('company_end_time')}}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <div class="col-md-12 text-center">
                                        <div class="mt-3">
                                            <h6>{{__('Record not found')}}</h6>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="attendance-btn ">
                    {{ Form::submit(__('Update'), ['class' => 'btn  btn-primary']) }}
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

@endsection

