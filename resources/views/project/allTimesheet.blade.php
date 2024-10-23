@extends('layouts.admin')
@php
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
    // $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('script-page')
    <script>

        $(document).on('change', 'select[name=project]', function () {
            var project_id = $(this).val();
            getTask(project_id);
            getUser(project_id);
        });
        $(document).on('change', '#project_id', function () {
            var project_id = $(this).val();
            getProjectTask(project_id);
            getProjectUser(project_id);
        });

        function getTask(project_id) {
            $.ajax({
                url: '{{route('project.getTask')}}',
                type: 'POST',
                data: {
                    "project_id": project_id, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#task').empty();
                    $('#task').append('<option value="">{{__('Select Task')}}</option>');
                    $.each(data, function (key, value) {
                        $('#task').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        }

        function getUser(project_id) {
            $.ajax({
                url: '{{route('project.getUser')}}',
                type: 'POST',
                data: {
                    "project_id": project_id, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {

                    $('#user').empty();
                    $('#user').append('<option value="">{{__('Select User')}}</option>');
                    $.each(data, function (key, value) {

                        $('#user').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        }

        function getProjectTask(project_id) {
            $.ajax({
                url: '{{route('project.getTask')}}',
                type: 'POST',
                data: {
                    "project_id": project_id, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#task_id').empty();
                    $('#task_id').append('<option value="">--</option>');
                    $.each(data, function (key, value) {
                        $('#task_id').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        }

        function getProjectUser(project_id) {
            $.ajax({
                url: '{{route('project.getUser')}}',
                type: 'POST',
                data: {
                    "project_id": project_id, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#users').empty();
                    $.each(data, function (key, value) {
                        $('#users').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        }

        $("select[name=project]").trigger("change");
    </script>
@endpush
@section('page-title')
    {{__('Timesheet')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Timesheet')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Timesheet')}}</li>
@endsection
@section('action-btn')
    <a href="#" data-size="lg" data-url="{{ route('project.timesheet.create',0) }}" data-bs-toggle="modal"
    data-bs-target="#exampleModal" data-bs-whatever="{{__('Create New Timesheet')}}" class="btn btn-sm btn-primary btn-icon m-1">
        <i class="ti ti-plus" data-bs-toggle="tooltip"  data-bs-original-title="{{__('Create')}}"></i>
    </a>
@endsection

@section('content')

        <div class="col-xl-12">
            <div class=" {{isset($_GET['project'])?'show':''}}" >
                <div class="card card-body">
                    {{ Form::open(array('route' => array('project.all.timesheet'),'method'=>'get')) }}
                    <div class="row filter-css">
                        @if(\Auth::user()->type=='employee' ||\Auth::user()->type=='company')
                            <div class="col-md-3">
                                {{ Form::select('project', $projectList,!empty($_GET['project'])?$_GET['project']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
                            </div>
                        @endif
                        <div class="col-md-2">
                            <select class="form-control" data-toggle="select" name="task" id="task">
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" data-toggle="select" name="user" id="user">
                            </select>
                        </div>
                        <div class="col-auto">
                            {{Form::date('start_date',isset($_GET['start_date'])?$_GET['start_date']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="col-auto">
                            {{Form::date('end_date',isset($_GET['end_date'])?$_GET['end_date']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="action-btn bg-info ms-2 col-auto mt-2">
                            <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-toggle="tooltip" data-title="{{__('Apply')}}"><i data-bs-toggle="tooltip" data-bs-original-title="{{__('Apply')}}" class="ti ti-search text-white"></i></button>
                        </div>
                        <div class="action-btn bg-danger ms-2 col-auto mt-2">
                            <a href="{{route('project.all.timesheet')}}" data-toggle="tooltip" data-title="{{__('Reset')}}" class="mx-3 btn btn-sm d-flex align-items-center"><i data-bs-toggle="tooltip" data-bs-original-title="{{__('Reset')}}" class="ti ti-trash-off text-white"></i></a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>


    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <!-- <h5></h5> -->
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th scope="col" class="sort">{{__('Project')}}</th>
                                <th scope="col" class="sort">{{__('Task')}}</th>
                                <th scope="col" class="sort">{{__('User')}}</th>
                                <th scope="col" class="sort">{{__('Start Date')}}</th>
                                <th scope="col" class="sort">{{__('Start Time')}}</th>
                                <th scope="col" class="sort">{{__('End Date')}}</th>
                                <th scope="col" class="sort">{{__('End Time')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timesheet as $log)
                                <tr>
                                    <td> {{!empty($log->projects)?$log->projects->title:''}}</td>
                                    <td>{{!empty($log->tasks)?$log->tasks->title:'-'}}</td>
                                    <td> {{!empty($log->users)?$log->users->name:''}}</td>
                                    <td> {{\Auth::user()->dateFormat($log->start_date)}}</td>
                                    <td> {{\Auth::user()->timeFormat($log->start_time)}}</td>
                                    <td> {{($log->end_date!='0000-00-00') ?\Auth::user()->dateFormat($log->end_date):'-'}}</td>
                                    <td> {{($log->end_date!='0000-00-00') ?\Auth::user()->timeFormat($log->end_time):'-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection



