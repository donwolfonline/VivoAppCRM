@extends('layouts.admin')
@php
$profile = asset(Storage::url('uploads/avatar'));
@endphp
@push('script-page')
    <script>
        $(document).on("click", ".status", function() {
            var status = $(this).attr('data-id');
            var url = $(this).attr('data-url');

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    status: status,
                    "_token": $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    $('#change-project-status').submit();
                    location.reload();
                }
            });
        });
    </script>
    <script>


      

        $(document).on('change', '#project', function() {
            var project_id = $(this).val();

            $.ajax({
                url: '{{ route('project.getMilestone') }}',
                type: 'POST',
                data: {
                    "project_id": project_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#milestone_id').empty();
                    $('#milestone_id').append('<option value="0"> -- </option>');
                    $.each(data, function(key, value) {
                        $('#milestone_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });

            $.ajax({
                url: '{{ route('project.getUser') }}',
                type: 'POST',
                data: {
                    "project_id": project_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#assign_to').empty();
                    $.each(data, function(key, value) {
                        $('#assign_to').append('<option value="' + key + '">' + value +
                            '</option>');
                    });

                }
            });

        });
    </script>
@endpush
@section('page-title')
    {{ __('Task') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Task') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Task') }}</li>
@endsection
@section('action-btn')

    <a href="{{ route('task.calendar') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="Calendar View" >
        <i class="ti ti-calendar text-white"></i>
    </a>

    <a href="{{ route('project.all.task.gantt.chart') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="{{ __('Gantt Chart') }}">
        <i class="ti ti-chart-bar text-white"></i>
    </a>

    <a href="{{ route('project.all.task.kanban') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="{{ __('Task Kanban') }}">
        <i class="ti ti-layout-kanban text-white"></i>
    </a>
    <a href="#" data-size="lg" data-url="{{ route('project.task.create', 0) }}" data-bs-toggle="modal" data-bs-whatever="{{__('Create New Task')}}"
    data-bs-target="#exampleModal" title="{{ __('Create New Task') }}" class="btn btn-sm btn-primary btn-icon m-1">
        <i class="ti ti-plus text-white" data-bs-toggle="tooltip"  data-bs-original-title="{{__('Create')}}"></i>
    </a>
@endsection

@section('content')

<div class="col-xl-12">
    <div class=" {{ isset($_GET['status']) ? 'show' : '' }}" >
        <div class="card card-body">
            {{ Form::open([ 'method' => 'get']) }}
            <div class="row filter-css">
                @if (\Auth::user()->type == 'company')
                    <div class="col-md-3">
                        {{ Form::select('project', $projectList, !empty($_GET['project']) ? $_GET['project'] : '', ['class' => 'form-control','data-toggle' => 'select']) }}
                    </div>
                @endif
                <div class="col-md-2">
                    <select class="form-control" data-toggle="select" name="status">
                        <option value="">{{ __('Select status') }}</option>
                        @foreach ($stageList as $k => $val)
                            <option value="{{ $k }}"
                                {{ isset($_GET['status']) && $_GET['status'] == $k ? 'selected' : '' }}>
                                {{ $val }} </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" data-toggle="select" name="priority">
                        <option value="">{{ __('Select priority') }}</option>
                        @foreach ($priority as $val)
                            <option value="{{ $val }}"
                                {{ isset($_GET['priority']) && $_GET['priority'] == $val ? 'selected' : '' }}>
                                {{ $val }} </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    {{Form::date('due_date',isset($_GET['due_date']) ? $_GET['due_date'] : new \DateTime(),array('class'=>'form-control'))}}
                </div>
                <div class="action-btn bg-info ms-2 col-auto">
                    <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center"
                    data-bs-toggle="tooltip" data-title="{{ __('Apply') }}"><i
                            class="ti ti-search text-white"></i></button>
                </div>
                <div class="action-btn bg-danger ms-2 col-auto">
                    <a href="{{ route('project.all.task') }}" data-toggle="tooltip"
                        data-title="{{ __('Reset') }}"
                        class="mx-3 btn btn-sm d-flex align-items-center"><i
                            class="ti ti-trash-off text-white"></i></a>
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
                                <th scope="col" class="sort">{{ __('Project') }}</th>
                                <th scope="col" class="sort">{{ __('Title') }}</th>
                                <th scope="col" class="sort">{{ __('Start date') }}</th>
                                <th scope="col" class="sort">{{ __('Due date') }}</th>
                                <th scope="col" class="sort">{{ __('Assigned to') }}</th>
                                <th scope="col" class="sort">{{ __('Priority') }}</th>
                                <th scope="col" class="sort">{{ __('Status') }}</th>
                                <th scope="col" class="sort text-end">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($projects as $project)
                                @php
                                    if (empty($_GET['status']) && empty($_GET['priority']) && empty($_GET['due_date'])) {
                                        $tasks = $project->tasks;
                                    } else {
                                        $tasks = $project->taskFilter($_GET['status'], $_GET['priority'], $_GET['due_date']);
                                    }
                                    
                                @endphp

                                @foreach ($tasks as $task)
                                    <tr>
                                        <td> {{ $project->title }}</td>
                                        <td>{{ $task->title }}</td>
                                        <td> {{ \Auth::user()->dateFormat($task->start_date) }}</td>
                                        <td> {{ \Auth::user()->dateFormat($task->due_date) }}</td>
                                        <td> {{ !empty($task->taskUser) ? $task->taskUser->name : '-' }}</td>
                                        <td>
                                            @if ($task->priority == 'low')
                                                <div class="badge fix_badge bg-success p-2 px-3 rounded"> {{ $task->priority }}</div>
                                            @elseif($task->priority == 'medium')
                                                <div class="badge fix_badge bg-warning p-2 px-3 rounded"> {{ $task->priority }}</div>
                                            @elseif($task->priority == 'high')
                                                <div class="badge fix_badge bg-danger p-2 px-3 rounded"> {{ $task->priority }}</div>
                                            @endif
                                        </td>
                                        <td> {{ !empty($task->stages) ? $task->stages->name : '-' }}</td>
                                        <td class="text-end">
                                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="#" data-size="lg" data-url="{{ route('project.task.show', $task->id) }}"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal" title="{{ __('Task Detail') }}"
                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip"
                                                    data-bs-whatever="{{__('View Task')}}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            @endif
                                            @if (\Auth::user()->type == 'company')
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" data-size="lg" data-url="{{ route('project.task.edit', $task->id) }}"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal" title="{{ __('Edit Task') }}"
                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center" data-toggle="tooltip" data-bs-whatever="{{__('Edit Task')}}"
                                                    data-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-edit text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                            <span class="">
                                                    {!! Form::open(['method' => 'POST', 'route' => ['project.task.destroy', $task->id],'id'=>'task-delete-form-'.$task->id]) !!}
                                                    @method('DELETE')
                                                    <a href="#!" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-toggle="tooltip" title='Delete'>
                                                        <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </span>
                   
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
