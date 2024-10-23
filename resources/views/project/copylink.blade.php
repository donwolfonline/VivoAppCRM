@extends('layouts.guest')
@php
     $profile = \App\Models\Utility::get_file('uploads/avatar/');
      $file = \App\Models\Utility::get_file('uploads/files/');
     $feedback = \App\Models\Utility::get_file('uploads/avatar/');

     $result = json_decode($project->copylinksetting);
    // $logo_path = \App\Models\Utility::get_file('/');

    $GetLogo = App\Models\Utility::get_logo();
    
    $currantLang1 = Cookie::get('LANGUAGE');
    
    if (!isset($currantLang1)) {
        $currantLang1 = \App::getLocale();
    }
    \App::setLocale($currantLang1);
    
    $layout_setting = App\Models\Utility::getLayoutsSetting();
    if (!empty($layout_setting['cust_darklayout'])) {
        $cust_darklayout = $layout_setting['cust_darklayout'];
        $company_logo = $layout_setting['company_logo'];
    }
@endphp
@section('title')
    {{ __('Copy Link') }}
@endsection
@section('page-title')
    {{ __('Projects Details') }}
@endsection
@section('action-button')
    <!-- <a href="#" class="btn-primary">
        <select name="language" id="language" class=" btn-primary btn "
            onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
            @foreach (App\Models\Utility::languages() as $language)
                <option class="" @if ($currantLang1 == $language) selected @endif
                    value="{{ route('change_lang_copylink', $language) }}">{{ Str::upper($language) }}
                </option>
            @endforeach
        </select>
    </a> -->
    <a href="#" class="btn-primary">
        <select name="language" id="language" class=" btn-primary btn "
            onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
            @foreach (App\Models\Utility::languages() as $code => $language)
                <option class="" @if ($currantLang1 == $code) selected @endif
                    value="{{ route('change_lang_copylink', $code) }}">{{ Str::upper($language) }}
                </option>
            @endforeach
        </select>
    </a>
@endsection
<style>
    .application .container-application {
        display: flow-root !important;
    }
</style>

@php
    $logo = \App\Models\Utility::get_file('tasks/');
    $logo_path = \App\Models\Utility::get_file('/');
@endphp

@section('content')
    @php
        $project_last_stage = $project->project_last_stage($project->id) ? $project->project_last_stage($project->id)->id : '';
        $total_task = $project->project_total_task($project->id);
        $completed_task = $project->project_complete_task($project->id, $project_last_stage);
        
        $percentage = 0;
        if ($total_task != 0) {
            $percentage = intval(($completed_task / $total_task) * 100);
        }
        
        $label = '';
        if ($percentage <= 15) {
            $label = 'bg-danger';
        } elseif ($percentage > 15 && $percentage <= 33) {
            $label = 'bg-warning';
        } elseif ($percentage > 33 && $percentage <= 70) {
            $label = 'bg-primary';
        } else {
            $label = 'bg-success';
    } @endphp
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            {{-- @dd($result); --}}
                            @if ($result->basic_details == 'on')
                                <a href="#basicDeitals"
                                    class="list-group-item list-group-item-action border-0">{{ __('Basic Details') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if ($result->task == 'on')
                                <a href="#task"
                                    class="list-group-item list-group-item-action border-0">{{ __('Task List') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if ($result->ganttTasks == 'on')
                                <a href="#ganttTasks"
                                    class="list-group-item list-group-item-action border-0">{{ __('Gantt Chart') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                            @if ($result->milestone == 'on')
                                <a href="#milestone"
                                    class="list-group-item list-group-item-action border-0">{{ __('Milestone') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if ($result->note == 'on')
                                <a href="#note"
                                    class="list-group-item list-group-item-action border-0">{{ __('Note') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if ($result->files == 'on')
                                <a href="#files"
                                    class="list-group-item list-group-item-action border-0">{{ __('Files') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if ($result->comments == 'on')
                                <a href="#comments"
                                    class="list-group-item list-group-item-action border-0">{{ __('Comments') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if ($result->feedbacks == 'on')
                                <a href="#feedbacks"
                                    class="list-group-item list-group-item-action border-0">{{ __('Client feedbacks') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                            @if ($result->invoice == 'on')
                                <a href="#invoice"
                                    class="list-group-item list-group-item-action border-0">{{ __('Invoice') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if ($result->timesheet == 'on')
                                <a href="#timesheet"
                                    class="list-group-item list-group-item-action border-0">{{ __('Timesheet') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if ($result->payment == 'on')
                                <a href="#payment"
                                    class="list-group-item list-group-item-action border-0">{{ __('Payment') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if ($result->expenses == 'on')
                                <a href="#expenses"
                                    class="list-group-item list-group-item-action border-0">{{ __('Expenses') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    @if ($result->basic_details == 'on')
                        <div class="col-md-12" id="basicDeitals">
                            <div class="row">
                                <div class="col-xxl-8">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 col-sm-6">
                                                    <h5>{{ __($project->title) }}</h5>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <div class="progress mb-0">
                                                        <div class="progress-bar bg-success"
                                                            style="width: {{ $percentage }}%;"></div>
                                                        <h6 class="mb-0  mt-2">{{ __('Completed') }}: <b>
                                                                {{ $percentage }}%</b></h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-sm-12">
                                                    <p class="text-sm text-muted mb-2">{{ $project->description }}</p>
                                                </div>
                                            </div>

                                            <div class="row  mt-4">
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="d-flex align-items-start">
                                                        <div class="theme-avtar bg-success">
                                                            <i class="ti ti-calendar"></i>
                                                        </div>
                                                        <div class="ms-2">
                                                            <p class="text-muted text-sm mb-0">{{ __('Start Date') }}:
                                                            </p>
                                                            <p class="mb-0 text-success">
                                                                {{ $objUser->dateFormat($project->start_date) }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-6 my-3 my-sm-0">
                                                    <div class="d-flex align-items-start">
                                                        <div class="theme-avtar bg-info">
                                                            <i class="ti ti-calendar-time"></i>
                                                        </div>
                                                        <div class="ms-2">
                                                            <p class="text-muted text-sm mb-0">{{ __('Due Date') }}:</p>
                                                            <p class="mb-0 text-info">
                                                                {{ $objUser->dateFormat($project->due_date) }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="d-flex align-items-start">
                                                        <div class="theme-avtar bg-danger">
                                                            <i class="ti ti-brand-hipchat"></i>
                                                        </div>
                                                        <div class="ms-2">
                                                            <p class="text-muted text-sm mb-0">{{ __('Comments') }}:</p>
                                                            <p class="mb-0 text-danger">
                                                                {{ $project->countTaskComments() }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row  mt-4">
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="d-flex align-items-start">
                                                        <div class="theme-avtar bg-warning">
                                                            <i class="ti ti-user"></i>
                                                        </div>
                                                        <div class="ms-2">
                                                            <p class="text-muted text-sm mb-0">{{ __('Members') }}:</p>
                                                            <p class="mb-0 text-warning">
                                                                {{ count($project->projectUser()) }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-6 my-3 my-sm-0">
                                                    <div class="d-flex align-items-start">
                                                        <div class="theme-avtar bg-dark">
                                                            <i class="ti ti-calendar-event"></i>
                                                        </div>
                                                        <div class="ms-2">
                                                            <p class="text-muted text-sm mb-0">{{ __('Days Left') }}:
                                                            </p>
                                                            <p class="mb-0 text-dark">{{ $daysleft }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">{{ __('Project members') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                @foreach ($project->projectUser() as $user)
                                                    @php
                                                        $totalTask = $project->user_project_total_task($user->project_id, $user->user_id);
                                                    @endphp
                                                    @php $completeTask = $project->user_project_complete_task($user->project_id, $user->user_id, $project->project_last_stage() ? $project->project_last_stage()->id : ''); @endphp
                                                    <div class="list-group-item">
                                                        <div class="row align-items-center">
                                                            <div class="col-auto">
                                                                <!-- Avatar -->
                                                                <a href="#"
                                                                    class="avatar rounded-circle user-group1">
                                                                    <img alt="Image placeholder" class="rounded-circle" width="50" height="50"
                                                                        @if (!empty($user->avatar)) src="{{ $profile . '/' . $user->avatar }}" @else avatar="{{ $user->name }}" @endif>
                                                                </a> 
                                                            </div>
                                                            <div class="col ml-n2">
                                                                <a href="#!"
                                                                    class="d-block h6 mb-0">{{ $user->name }}</a>
                                                                <small>{{ $user->email }}</small>
                                                            </div>

                                                        </div>
                                                    </div>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="theme-avtar bg-success">
                                                        <i class="ti ti-report-money"></i>
                                                    </div>
                                                    <h6 class="mb-3 mt-2">{{ __('Budget') }}</h6>
                                                    <h4 class="mb-0">
                                                        {{ $objUser->priceFormat($project->price) }} </h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="theme-avtar bg-info">
                                                        <i class="ti ti-click"></i>
                                                    </div>
                                                    <h6 class="mb-3 mt-2">{{ __('Expense') }}</h6>
                                                    <h4 class="mb-0">{{ $objUser->priceFormat($totalExpense) }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="theme-avtar bg-warning">
                                                        <i class="ti ti-user-plus"></i>
                                                    </div>
                                                    <h6 class="mb-3 mt-2">{{ __('Client') }}</h6>
                                                    <h6 class="mb-0">
                                                        {{ !empty($project->clients) ? $project->clients->name : '' }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    {{-- basic Details --}}
                    @if ($result->task == 'on')
                        <div class="col-md-12" id="task">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">{{ __('Task List') }}</h5>
                                </div>
                                <div class="scrollbar-inner">
                                    <div class="card-body project-detail-common-box">
                                        @php
                                            $json = [];
                                            foreach ($stages as $stage) {
                                                $json[] = 'task-list-' . $stage->id;
                                            }
                                        @endphp
                                        @foreach ($stages as $stage)
                                            @php $tasks =$stage->tasks($project->id) @endphp
                                            <h4 class="mb-0">{{ $stage->name }}</h4>
                                            <div class="mb-4" id="card-list-1">
                                                @foreach ($tasks as $task)
                                                    <div class="card card-progress border shadow-none draggable-item">
                                                        @if ($task->priority == 'low')
                                                            <div class="progress">
                                                                <div class="progress-bar bg-danger" role="progressbar"
                                                                    style="width: 100%" aria-valuenow="50"
                                                                    aria-valuemin="0" aria-valuemax="50"></div>
                                                            </div>
                                                        @elseif($task->priority == 'medium')
                                                            <div class="progress">
                                                                <div class="progress-bar bg-warning" role="progressbar"
                                                                    style="width: 100%" aria-valuenow="80"
                                                                    aria-valuemin="0" aria-valuemax="80"></div>
                                                            </div>
                                                        @elseif($task->priority == 'high')
                                                            <div class="progress">
                                                                <div class="progress-bar bg-success" role="progressbar"
                                                                    style="width: 100%" aria-valuenow="100"
                                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        @endif


                                                        <div class="card-body row align-items-center">
                                                            <div class="avatar rounded-circle user-group1">
                                                                <span class="avatar avatar-sm rounded-circle mr-2">
                                                                    <img alt="image" data-toggle="tooltip"
                                                                        data-original-title="{{ !empty($task->taskUser) ? $task->taskUser->name : '' }}"
                                                                        @if ($task->taskUser && !empty($task->taskUser->avatar)) src="{{ $profile . '/' . $task->taskUser->avatar }}" @else
                                                            avatar="{{ !empty($task->taskUser) ? $task->taskUser->name : '' }}" @endif
                                                            class="rounded-circle" width="50" height="50">
                                                                </span>
  
                                                                <a href="#" data-size="lg"
                                                                    data-url="{{ route('project.task.show', $task->id) }}"
                                                                    data-ajax-popup="true" data-bs-target="#exampleModal"
                                                                    data-bs-toggle="modal"
                                                                    data-title="{{ __('Task Detail') }}" class="h6 ms-3"
                                                                    data-toggle="tooltip"
                                                                    data-bs-whatever="{{ __('View Task') }}">
                                                                    {{ $task->title }}
                                                                </a>
                                                                <br>
                                                                <span>
                                                                    @if ($task->priority == 'low')
                                                                        <div class="badge bg-success p-2 px-3 rounded">
                                                                            {{ $task->priority }}</div>
                                                                    @elseif($task->priority == 'medium')
                                                                        <div class="badge bg-warning p-2 px-3 rounded">
                                                                            {{ $task->priority }}</div>
                                                                    @elseif($task->priority == 'high')
                                                                        <div class="badge bg-danger p-2 px-3 rounded">
                                                                            {{ $task->priority }}</div>
                                                                    @endif
                                                                </span>
                                                                <div class="actions d-inline-block text-end float-sm-none">
                                                                    <div class="action-item ml-4 ms-5 pt-3">
                                                                        <i class="ti ti-calendar-event"></i>
                                                                        {{ $objUser->dateFormat($task->start_date) }}
                                                                    </div>
                                                                </div>
                                                                <div class="actions d-inline-block text-end float-sm-none">
                                                                    <div class="action-item ml-4 ms-5 pt-3">
                                                                        <i class="ti ti-calendar-event"></i>
                                                                        {{ $objUser->dateFormat($task->due_date) }}
                                                                    </div>
                                                                </div>
                                                                <span class="col-auto" style="margin-left: 500px">

                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                <span class="empty-container" data-placeholder="Empty"></span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($result->ganttTasks == 'on')
                        <div class="col-md-12" id="ganttTasks">
                            <div class="row">
                                <div class="card overflow-hidden">
                                    <div class="card-header">
                                        <div class="float-end">
                                            <a href="#"
                                                class="btn btn-sm btn-info gantt-chart-mode  @if ($duration == 'Quarter Day') active @endif"
                                                data-value="Quarter Day">{{ __('Quarter Day') }}</a>
                                            <a href="#"
                                                class="btn btn-sm btn-info gantt-chart-mode @if ($duration == 'Half Day') active @endif"
                                                data-value="Half Day">{{ __('Half Day') }}</a>
                                            <a href="#"
                                                class="btn btn-sm btn-info gantt-chart-mode @if ($duration == 'Day') active @endif"
                                                data-value="Day">{{ __('Day') }}</a>
                                            <a href="#"
                                                class="btn btn-sm btn-info gantt-chart-mode @if ($duration == 'Week') active @endif"
                                                data-value="Week">{{ __('Week') }}</a>
                                            <a href="#"
                                                class="btn btn-sm btn-info gantt-chart-mode @if ($duration == 'Month') active @endif"
                                                data-value="Month">{{ __('Month') }}</a>
                                        </div>
                                        <h5 class="mb-0">{{ __('Gantt Chart') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <svg id="gantt"></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($result->milestone == 'on')
                        <div class="col-md-12" id="milestone">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ __('Milestones') }} ({{ count($project->milestones) }})</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Cost') }}</th>
                                                    <th>{{ __('Start Date') }}</th>
                                                    <th>{{ __('End Date') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Progress') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                @foreach ($project->milestones as $milestone)
                                                    <tr>
                                                        <td class="Id">
                                                            <a href="#" data-ajax-popup="true"
                                                                data-title="{{ __('Milestones Details') }}"
                                                                data-url="">{{ $milestone->title }}</a>
                                                        </td>
                                                        <td class="mile-text">
                                                            <span>{{ $objUser->priceFormat($milestone->cost) }}</span>
                                                        </td>
                                                        <td class="mile-text">{{ $milestone->start_date }}</td>
                                                        <td class="mile-text">{{ $milestone->due_date }}</td>
                                                        <td class="Due">
                                                            <div class="date-box">{{ ucfirst($milestone->status) }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="progress_wrapper">
                                                                <div class="progress">
                                                                    <div class="progress-bar" role="progressbar"
                                                                        style="width: {{ $milestone->progress }}px;"
                                                                        aria-valuenow="55" aria-valuemin="0"
                                                                        aria-valuemax="100">
                                                                    </div>
                                                                </div>
                                                                <div class="progress_labels">
                                                                    <div class="total_progress">
                                                                        <strong> {{ $milestone->progress }}%</strong>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($result->note == 'on')
                        <div class="col-md-12" id="note">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ __('Notes') }} ({{ count($notes) }})</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table" id="pc-dt-simple">
                                            <thead>
                                                <tr>
                                                    <th scope="col">{{ __('Title') }}</th>
                                                    <th scope="col">{{ __('Description') }}</th>
                                                    <th scope="col">{{ __('Created Date') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($notes as $note)
                                                    <tr>
                                                        <th scope="row">
                                                            <div class="media align-items-center">
                                                                <div class="media-body">
                                                                    <a href="#"
                                                                        class="name h6 mb-0 text-sm">{{ $note->title }}</a><br>
                                                                </div>
                                                            </div>
                                                        </th>
                                                        <td>{{ $note->description }}</td>
                                                        <td>{{ $objUser->dateFormat($note->created_at) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($result->files == 'on')
                        <div class="col-md-12" id="files">
                            <div class="card">
                                <div class="card-header">

                                    <h5 class="mb-0">{{ __('Files') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table" id="pc-dt-simple">
                                            <thead>
                                                <tr>
                                                    <th scope="col">{{ __('#') }}</th>
                                                    <th scope="col">{{ __('Title') }}</th>
                                                    <th scope="col">{{ __('Created Date') }}</th> 
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($files as $file)
                                                    <tr>
                                                        <th scope="row">
                                                            <div class="media align-items-center">
                                                                <div class="media-body avatar rounded-circle user-group1">
                                                                    <a href="#"
                                                                        class="avatar rounded-circle user-group1">
                                                                        <img alt="Image placeholder"
                                                                            src="{{ \App\Models\Utility::get_file('uploads/files/') . '/' . $file->file }}"
                                                                            class="rounded-circle" width="35" height="35">
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </th>
                                                        <td>{{ $file->file }}</td>
                                                        <td>{{ $objUser->dateFormat($file->created_at) }}</td>
                                                        
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($result->comments == 'on')
                        <div class="col-md-12" id="comments">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">{{ __('Comments') }}</h5>
                                </div>
                                <div class="card-body">
                                    @foreach ($comments as $comment)
                                        <div class="media mb-2">
                                            <a class="pr-2" href="#">
                                                <img @if (!empty($comment->commentUser && !empty($comment->commentUser->avatar))) src="{{ $profile . '/' . $comment->commentUser->avatar }}" @else avatar="{{ !empty($comment->commentUser) ? $comment->commentUser->name : '' }}" @endif
                                                    class="rounded-circle" alt="" height="32">
                                            </a>
                                            <div class="media-body">
                                                <h6 class="mt-0 ms-2">
                                                    {{ !empty($comment->commentUser) ? $comment->commentUser->name : '' }}
                                                    <small
                                                        class="text-muted float-right">{{ $comment->created_at }}</small>
                                                </h6>

                                                <p class="text-sm mb-0 ms-2">
                                                    {{ $comment->comment }}
                                                </p>
                                                <div class="text-end">
                                                    @if (!empty($comment->file))
                                                        <div class="action-btn bg-warning ms-2">
                                                            <a href="#" class="like active"
                                                                style="margin-bottom: -13px;">
                                                                <i class="ni ni-cloud-download-95"></i>
                                                                
                                                            </a>
                                                        </div>
                                                    @endif

                                                </div>
                                                @foreach ($comment->subComment as $subComment)
                                                    <div class="media mt-3">
                                                        <a class="pr-2" href="#">
                                                            <img @if (!empty($subComment->commentUser && !empty($subComment->commentUser->avatar))) src="{{ $profile . '/' . $subComment->commentUser->avatar }}" @else avatar="{{ !empty($subComment->commentUser) ? $subComment->commentUser->name : '' }}" @endif
                                                                class="rounded-circle" alt="" height="32">
                                                        </a>
                                                        <div class="media-body">
                                                            <h6 class="mt-0 ms-2">
                                                                {{ !empty($subComment->commentUser) ? $subComment->commentUser->name : '' }}
                                                                <small
                                                                    class="text-muted float-right">{{ $subComment->created_at }}</small>
                                                            </h6>
                                                            <p class="text-sm mb-0 ms-2">
                                                                {{ $subComment->comment }}
                                                            </p>

                                                            @if (!empty($subComment->file))
                                                                <div class="action-btn bg-warning ms-2">
                                                                   
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($result->feedbacks == 'on')
                        <div class="col-md-12" id="feedbacks">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">{{ __('Client Feedback') }}</h5>
                                </div>
                                <div class="card-body">
                                    @foreach ($feedbacks as $feedback)
                                        <div class="media mb-2">
                                            <a class="pr-2" href="#">
                                                <img @if (!empty($feedback->feedbackUser) && !empty($feedback->feedbackUser->avatar)) src="{{ $profile . '/' . $feedback->feedbackUser->avatar }}" @else avatar="{{ !empty($feedback->feedbackUser) ? $feedback->feedbackUser->name : '' }}" @endif
                                                    class="rounded-circle" alt="" height="32">
                                            </a>
                                            <div class="media-body">
                                                <h6 class="mt-0 ms-2">
                                                    {{ !empty($feedback->feedbackUser) ? $feedback->feedbackUser->name : '' }}
                                                    <small
                                                        class="text-muted float-right">{{ $feedback->created_at }}</small>
                                                </h6>

                                                <p class="text-sm mb-0 ms-2">
                                                    {{ $feedback->feedback }}
                                                </p>
                                                @foreach ($feedback->subfeedback as $subfeedback)
                                                    <div class="media mt-3">
                                                        <a class="pr-2" href="#">
                                                            <img @if (!empty($subfeedback->feedbackUser && !empty($subfeedback->feedbackUser->avatar))) src="{{ $profile . '/' . $subfeedback->feedbackUser->avatar }}" @else avatar="{{ !empty($subfeedback->feedbackUser) ? $subfeedback->feedbackUser->name : '' }}" @endif
                                                                class="rounded-circle" alt="" height="32">
                                                        </a>
                                                        <div class="media-body">
                                                            <h6 class="mt-0 ms-2">
                                                                {{ !empty($subfeedback->feedbackUser) ? $subfeedback->feedbackUser->name : '' }}
                                                                <small
                                                                    class="text-muted float-right">{{ $subfeedback->created_at }}</small>
                                                            </h6>
                                                            <p class="text-sm mb-0 ms-2">
                                                                {{ $subfeedback->feedback }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($result->invoice == 'on')
                        <div class="col-md-12" id="invoice">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">{{ __('Invoice') }}</h5>
                                </div>

                                <div class="card-body">
                                    <div class="row notes-list">
                                        @foreach ($invoices as $invoice)
                                            <div class="col-md-6">
                                                <div class="card hover-shadow-lg">
                                                    <div class="card-header border-0">
                                                        <div class="row align-items-center">
                                                            <div class="col-10">
                                                                <h6 class="mb-0">
                                                                    <a
                                                                        href="#">{{ $objUser->invoiceNumberFormat($invoice->invoice_id) }}</a>
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body pt-0">
                                                        <div class="p-3 border border-dashed">
                                                            @if ($invoice->status == 0)
                                                                <span
                                                                    class="badge badge-primary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 1)
                                                                <span
                                                                    class="badge badge-warning">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 2)
                                                                <span
                                                                    class="badge badge-danger">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 3)
                                                                <span
                                                                    class="badge badge-info">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 4)
                                                                <span
                                                                    class="badge badge-success">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @endif
                                                            <div class="row align-items-center mt-3">
                                                                <div class="col-6">
                                                                    <h6 class="mb-0">
                                                                        {{ $objUser->priceFormat($invoice->getTotal()) }}
                                                                    </h6>
                                                                    <span
                                                                        class="text-sm text-muted">{{ __('Total Amount') }}</span>
                                                                </div>
                                                                <div class="col-6">
                                                                    <h6 class="mb-0">
                                                                        {{ $objUser->priceFormat($invoice->getDue()) }}
                                                                    </h6>
                                                                    <span
                                                                        class="text-sm text-muted">{{ __('Due Amount') }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="row align-items-center mt-3">
                                                                <div class="col-6">
                                                                    <h6 class="mb-0">
                                                                        {{ $objUser->dateFormat($invoice->issue_date) }}
                                                                    </h6>
                                                                    <span
                                                                        class="text-sm text-muted">{{ __('Issue Date') }}</span>
                                                                </div>
                                                                <div class="col-6">
                                                                    <h6 class="mb-0">
                                                                        {{ $objUser->dateFormat($invoice->due_date) }}
                                                                    </h6>
                                                                    <span
                                                                        class="text-sm text-muted">{{ __('Due Date') }}</span>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        @if ($objUser->type != 'client')
                                                            @php $client=$invoice->clients @endphp
                                                            <div class="media mt-4 align-items-center">
                                                                <img @if (!empty($client->avatar)) src="{{ $profile . '/' . $client->avatar }}" @else avatar="{{ $invoice->clients->name }}" @endif
                                                                    class="avatar rounded-circle avatar-custom"
                                                                    data-toggle="tooltip"
                                                                    data-original-title="{{ __('Client') }}">
                                                                <div class="media-body pl-3">
                                                                    <div class="text-sm my-0">
                                                                        {{ !empty($invoice->clients) ? $invoice->clients->name : '' }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($result->timesheet == 'on')
                        <div class="col-md-12" id="timesheet">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">{{ __('Timesheet') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table" id="pc-dt-simple">
                                            <thead>
                                                <tr>
                                                    <th scope="col">{{ __('Member') }}</th>
                                                    <th scope="col">{{ __('Task') }}</th>
                                                    <th scope="col">{{ __('Start Date') }}</th>
                                                    <th scope="col">{{ __('Start Time') }}</th>
                                                    <th scope="col">{{ __('End Date') }}</th>
                                                    <th scope="col">{{ __('End Time') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($timesheets as $timesheet)
                                                    <tr>
                                                        <td>{{ !empty($timesheet->users) ? $timesheet->users->name : '-' }}
                                                        </td>
                                                        <td> {{ !empty($timesheet->tasks) ? $timesheet->tasks->title : '-' }}
                                                        </td>
                                                        <td>{{ $objUser->dateFormat($timesheet->start_date) }}</td>
                                                        <td>{{ $objUser->timeFormat($timesheet->start_time) }}</td>
                                                        <td>{{ $objUser->dateFormat($timesheet->end_date) }}</td>
                                                        <td>{{ $objUser->timeFormat($timesheet->end_time) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($result->payment == 'on')
                        <div class="col-md-12" id="payment">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">{{ __('Payment') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table" id="pc-dt-simple">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Transaction ID') }}</th>
                                                    <th>{{ __('Invoice ID') }}</th>
                                                    <th>{{ __('Payment Date') }}</th>
                                                    <th>{{ __('Payment Method') }}</th>
                                                    <th>{{ __('Payment Type') }}</th>
                                                    <th>{{ __('Notes') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($invoices as $invoice)
                                                    @foreach ($invoice->payments as $payment)
                                                        <tr>
                                                            <td>{{ $objUser->invoiceNumberFormat($invoice->invoice_id) }}
                                                            </td>
                                                            <td>{{ $payment->transaction }} </td>
                                                            <td>{{ $objUser->dateFormat($payment->date) }} </td>
                                                            <td>{{ !empty($payment->payments) ? $payment->payments->name : '' }}
                                                            </td>
                                                            <td>{{ $payment->payment_type }} </td>
                                                            <td>{{ $payment->notes }} </td>
                                                            <td> {{ $objUser->priceFormat($payment->amount) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($result->expenses == 'on')
                        <div class="col-md-12" id="expenses">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">{{ __('Expenses') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table" id="pc-dt-simple">
                                            <thead>
                                                <tr>
                                                    <th> {{ __('Date') }}</th>
                                                    <th> {{ __('Amount') }}</th>
                                                    <th> {{ __('User') }}</th>
                                                    <th> {{ __('Attachment') }}</th>
                                                    <th> {{ __('Description') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($project->expenses as $expense)
                                                    <tr class="font-style">
                                                        <td>{{ $objUser->dateFormat($expense->date) }}</td>
                                                        <td>{{ $objUser->priceFormat($expense->amount) }}</td>
                                                        <td>{{ !empty($expense->users) ? $expense->users->name : '' }}
                                                        </td>
                                                        <td>
                                                            @if (!empty($expense->attachment))
                                                                <a href="{{ asset(Storage::url('uploads/attachment/' . $expense->attachment)) }}"
                                                                    target="_blank">{{ $expense->attachment }}</a>
                                                            @else
                                                                --
                                                            @endif
                                                        </td>
                                                        <td>{{ $expense->description }}</td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')

<script>

var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,
        })
        $(".list-group-item").click(function() {
            //console.log(id);
            $('.list-group-item').filter(function() {
               // return this.href == id;
            }).parent().removeClass('text-primary');
        });

</script> 
<script src="{{ asset('assets/js/letter.avatar.js') }}"></script>

    <script>
        LetterAvatar.transform();
 
</script>
    <script>
        // For Sidebar Tabs
        $(document).ready(function() {
             $('.list-group-item').on('click', function() {
                var href = $(this).attr('data-href');
                $('.tabs-card').addClass('d-none');
                $(href).removeClass('d-none');
                $('#tabs .list-group-item').removeClass('text-primary');
                $(this).addClass('text-primary');
            });
        });

        $(document).on('click', '.custom-control-input', function() {
            var pp = $(this).parents('.tabs-card').removeClass('d-none');
        });
    </script>

    {{-- Project Timesheet --}}
    <script>
        function ajaxFilterTimesheetTableView() {
            var mainEle = $('.project-timesheet');
            var notfound = $('.notfound-timesheet');
            var week = parseInt($('#weeknumber').val());
            var project_id = '{{ $project->id }}';
            var isowner = $('.owner-timesheet-status').prop('checked');
            var data = {
                week: week,
                project_id: project_id,
            }
            data.isowner = isowner;


        }

        $(function() {
            ajaxFilterTimesheetTableView();
        });

        $(document).on('click', '.weekly-dates-div .action-item', function() {
            var weeknumber = parseInt($('#weeknumber').val());
            if ($(this).hasClass('previous')) {
                weeknumber--;
                $('#weeknumber').val(weeknumber);
            } else if ($(this).hasClass('next')) {
                weeknumber++;
                $('#weeknumber').val(weeknumber);
            }
            ajaxFilterTimesheetTableView();
        });
    </script>
@endpush
