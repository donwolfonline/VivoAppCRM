@extends('layouts.admin')
@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    $file = \App\Models\Utility::get_file('uploads/files/');
    $feedback = \App\Models\Utility::get_file('uploads/avatar/');

    // $profile = asset(Storage::url('uploads/avatar/'));

@endphp
@section('page-title')
    {{ __('Project Detail') }}
@endsection

@push('pre-purpose-css-page')
    <link rel="stylesheet" href="{{ asset('css/frappe-gantt.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}">
@endpush

@push('pre-purpose-script-page')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>

    <script>
        const month_names = {
            "en": [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ],
            "en": [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ],
        };
    </script>
    <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
    <script src="{{ asset('js/frappe-gantt.js') }}"></script>
    <script>
        ! function(a) {
            "use strict";
            var t = function() {
                this.$body = a("body")
            };
            t.prototype.init = function() {
                a('[data-plugin="dragula"]').each(function() {
                    var t = a(this).data("containers"),
                        n = [];
                    if (t)
                        for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]);
                    else n = [a(this)[0]];
                    var r = a(this).data("handleclass");
                    r ? dragula(n, {
                        moves: function(a, t, n) {
                            return n.classList.contains(r)
                        }
                    }) : dragula(n).on('drop', function(el, target, source, sibling) {

                        var order = [];
                        $("#" + target.id + " > div").each(function() {
                            order[$(this).index()] = $(this).attr('data-id');
                        });

                        var id = $(el).attr('data-id');

                        var old_status = $("#" + source.id).data('status');
                        var new_status = $("#" + target.id).data('status');
                        var stage_id = $(target).attr('data-id');

                        $("#" + source.id).parent().find('.count').text($("#" + source.id + " > div")
                            .length);
                        $("#" + target.id).parent().find('.count').text($("#" + target.id + " > div")
                            .length);

                        $.ajax({
                            url: '{{ route('project.task.order') }}',
                            type: 'POST',
                            data: {
                                task_id: id,
                                stage_id: stage_id,
                                order: order,
                                old_status: old_status,
                                new_status: new_status,
                                "_token": $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(data) {
                                toastrs('Success', 'Task successfully updated', 'success');
                            },
                            error: function(data) {
                                data = data.responseJSON;
                                toastrs('{{ __('Error') }}', data.error, 'error')
                            }
                        });
                    });
                })
            }, a.Dragula = new t, a.Dragula.Constructor = t
        }(window.jQuery),
        function(a) {
            "use strict";

            a.Dragula.init()

        }(window.jQuery);
    </script>

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
        var tasks = JSON.parse('{!! addslashes(json_encode($ganttTasks)) !!}');

        var gantt = new Gantt('#gantt', tasks, {

            custom_popup_html: function(task) {
                var status_class = 'success';
                if (task.custom_class == 'medium') {
                    status_class = 'info'
                } else if (task.custom_class == 'high') {
                    status_class = 'danger'
                }
                return `<div class="details-container">
                                <div class="title">${task.name} <span class="badge badge-${status_class} float-right">${task.extra.priority}</span></div>
                                <div class="subtitle">

                                    <b>{{ __('Stage') }} : </b> ${task.extra.stage}<br>
                                    <b>{{ __('Duration') }} : </b> ${task.extra.duration}<br>
                                    <b>{{ __('Description') }} : </b> ${task.extra.description}

                                </div>
                            </div>
                          `;
            },
            on_click: function(task) {},
            on_date_change: function(task, start, end) {
                task_id = task.id;
                start = moment(start);
                end = moment(end);
                $.ajax({
                    url: "{{ route('project.gantt.post', $project->id) }}",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        start: start.format('YYYY-MM-DD HH:mm:ss'),
                        end: end.format('YYYY-MM-DD HH:mm:ss'),
                        task_id: task_id,
                    },
                    type: 'POST',
                    success: function(data) {

                    },

                    error: function(data) {
                        toastrs('Error', '{{ __('Some Thing Is Wrong!') }}', 'error');
                    }
                });
            },
        });

        gantt.change_view_mode('Week');

        $(document).on("click", ".gantt-chart-mode", function() {

            var mode = $(this).data('value');
            $('.gantt-chart-mode').removeClass('active');
            $(this).addClass('active');
            gantt.change_view_mode(mode)
        });
    </script>

    <script>
        $('.cp_link').on('click', function() {
            // console.log("hii");
            var value = $(this).attr('data-link');
            var $temp = $("<input>");

            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('Success', '{{ __('Link Copy on Clipboard1') }}', 'success');
        });
    </script>
@endpush
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Project Detail') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('project.index') }}">{{ __('Project') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $project->title }}</li>
@endsection
@section('action-btn')

    <div class="col-auto d-flex">
        @if (\Auth::user()->type == 'company')
            <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                <a href="#" data-size="md" data-url="{{ route('project.copylink.setting.create', $project->id) }}"
                    data-bs-target="#exampleModal" data-bs-toggle="modal" data-bs-toggle="tooltip"
                    data-bs-title="{{ __('Shared Project Settings') }}" class="btn btn-sm btn-primary btn-icon-only m-1"
                    data-bs-whatever="{{ __('Shared Project Settings') }}">
                    <i class="ti ti-share"></i>
                </a>
            </p>
        @endif

        @if (\Auth::user()->type == 'company')
            <a href="{{ route('project.edit', \Crypt::encrypt($project->id)) }}" class="btn btn-sm btn-info  btn-icon m-1"
                data-bs-whatever="{{ __('Edit Project') }}" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Edit') }}"> <span class="text-white"> <i
                        class="ti ti-edit"></i></span></a>


            {!! Form::open(['method' => 'DELETE', 'route' => ['project.destroy', $project->id]]) !!}
            <a href="#!" class="btn btn-sm btn-danger btn-icon m-1 show_confirm">
                <i class="ti ti-trash text-white"></i>
            </a>
            {!! Form::close() !!}
        @endif
        @if (\Auth::user()->type == 'company')
            @if ($projectStatus)
                <div class="btn-group">
                    <button class="btn btn-sm bg-primary text-white btn-icon rounded-pill dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ \App\Models\Project::$projectStatus[$project->status] }}
                    </button>
                    <div class="dropdown-menu">
                        @foreach ($projectStatus as $k => $status)
                            <a class="dropdown-item status" data-id="{{ $k }}"
                                data-url="{{ route('project.status', $project->id) }}"
                                href="#">{{ $status }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#useradd-1"
                                class="list-group-item list-group-item-action border-0">{{ __('Overview') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i> </div>
                            </a>
                            <a href="#useradd-2"
                                class="list-group-item list-group-item-action border-0">{{ __('Task List') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-3"
                                class="list-group-item list-group-item-action border-0">{{ __('Task Kanban') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-4"
                                class="list-group-item list-group-item-action border-0">{{ __('Gantt Chart') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-5"
                                class="list-group-item list-group-item-action border-0">{{ __('Milestone') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-6"
                                class="list-group-item list-group-item-action border-0">{{ __('Notes') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-7"
                                class="list-group-item list-group-item-action border-0">{{ __('Files') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-8"
                                class="list-group-item list-group-item-action border-0">{{ __('Comments') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-9"
                                class="list-group-item list-group-item-action border-0">{{ __('Client Feedback') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
                                <a href="#useradd-10"
                                    class="list-group-item list-group-item-action border-0">{{ __('Invoice') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                <a href="#useradd-11"
                                    class="list-group-item list-group-item-action border-0">{{ __('Timesheets') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
                                <a href="#useradd-12"
                                    class="list-group-item list-group-item-action border-0">{{ __('Payment') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                            @if (\Auth::user()->type == 'company')
                                <a href="#useradd-13"
                                    class="list-group-item list-group-item-action border-0">{{ __('Expense') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>


                <div class="col-xl-9">
                    @php
                        $percentages = 0;
                        $total = count($project->tasks);
                        if ($total != 0) {
                            $percentages = round($project->completedTask() / ($total / 100));
                        }
                    @endphp

                    <div id="useradd-1">
                        <div class="row">
                            <div class="col-xxl-8">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-6">
                                                <h5>{{ $project->title }}</h5>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <div class="progress mb-0">
                                                    <div class="progress-bar bg-success"
                                                        style="width: {{ $percentages }}%;"></div>
                                                    <h6 class="mb-0  mt-2">{{ __('Completed') }}: <b>
                                                            {{ $percentages }}%</b></h6>
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
                                                        <p class="text-muted text-sm mb-0">{{ __('Start Date') }}:</p>
                                                        <p class="mb-0 text-success">
                                                            {{ \Auth::user()->dateFormat($project->start_date) }}</p>
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
                                                            {{ \Auth::user()->dateFormat($project->due_date) }}</p>
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
                                                        <p class="mb-0 text-danger">{{ count($comments) }}</p>
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
                                                        <p class="mb-0 text-warning">{{ count($projectUsers) }}
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
                                                        <p class="text-muted text-sm mb-0">{{ __('Days Left') }}:</p>
                                                        <p class="mb-0 text-dark">{{ $daysleft }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <div class="float-end">
                                            @if (\Auth::user()->type == 'company')
                                                <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                                    <a href="#" class="btn btn-sm btn-primary btn-icon m-1"
                                                        data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                        data-url="{{ route('project.user', $project->id) }}"
                                                        data-bs-whatever="{{ __('Add User') }}"> <span
                                                            class="text-white">
                                                            <i class="ti ti-plus" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Add') }}"></i></span>
                                                    </a>
                                                </p>
                                            @endif
                                        </div>
                                        <h5 class="mb-0">{{ __('Project members') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            @foreach ($projectUsers as $user)
                                                <div class="list-group-item">
                                                    <div class="row align-items-center">
                                                        <div class="col-auto">
                                                            <!-- Avatar -->
                                                            <a href="#" class="avatar rounded-circle user-group1">
                                                                <img alt="Image placeholder" class=""
                                                                    @if (!empty($user->avatar)) src="{{ $profile . '/' . $user->avatar }}" @else  avatar="{{ $user->name }}" @endif>
                                                            </a>
                                                        </div>
                                                        <div class="col ml-n2">
                                                            <a href="#!"
                                                                class="d-block h6 mb-0">{{ $user->name }}</a>
                                                            <small>{{ $user->email }}</small>
                                                        </div>
                                                        <div class="col-auto">
                                                            <div class="action-btn bg-warning ms-2">
                                                                <a href="{{ route('employee.show', \Crypt::encrypt($user->user_id)) }}"
                                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                    data-toggle="tooltip"
                                                                    data-original-title="{{ __('View') }}">
                                                                    <i class="ti ti-eye text-white"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-original-title="{{ __('View') }}"></i>
                                                                </a>
                                                            </div>
                                                            @if (\Auth::user()->type == 'company')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['project.user.destroy', $project->id, $user->user_id]]) !!}
                                                                    <a href="#!"
                                                                        class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                                                        <i class="ti ti-trash text-white"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-original-title="{{ __('Delete') }}"></i>
                                                                    </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endif


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
                                                    {{ \Auth::user()->priceFormat($project->price) }} </h4>
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
                                                <h4 class="mb-0">{{ \Auth::user()->priceFormat($totalExpense) }}
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
                                                    {{ !empty($project->clients) ? $project->clients->name : '' }} </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>

                    <!--Task List-->
                    <div id="useradd-2">

                        <div class="card">
                            <div class="card-header">
                                <div class="float-end">
                                    @if (\Auth::user()->type == 'company')
                                        <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                            <a href="#" class="btn btn-sm btn-primary btn-icon m-1"
                                                data-bs-toggle="modal" data-size="lg" data-bs-target="#exampleModal"
                                                data-url="{{ route('project.task.create', $project->id) }}"
                                                data-bs-whatever="{{ __('Create New Task') }}"> <span class="text-white">
                                                    <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Create') }}"></i></span>
                                            </a>
                                        </p>
                                    @endif
                                </div>
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
                                        @php $tasks =$stage->allTask @endphp
                                        <h4 class="mb-0">{{ $stage->name }}</h4>
                                        <div class="mb-4" id="card-list-1">
                                            @foreach ($tasks as $task)
                                                <div class="card card-progress border shadow-none draggable-item">
                                                    @if ($task->priority == 'low')
                                                        <div class="progress">
                                                            <div class="progress-bar bg-danger" role="progressbar"
                                                                style="width: 100%" aria-valuenow="50" aria-valuemin="0"
                                                                aria-valuemax="50"></div>
                                                        </div>
                                                    @elseif($task->priority == 'medium')
                                                        <div class="progress">
                                                            <div class="progress-bar bg-warning" role="progressbar"
                                                                style="width: 100%" aria-valuenow="80" aria-valuemin="0"
                                                                aria-valuemax="80"></div>
                                                        </div>
                                                    @elseif($task->priority == 'high')
                                                        <div class="progress">
                                                            <div class="progress-bar bg-success" role="progressbar"
                                                                style="width: 100%" aria-valuenow="100" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </div>
                                                    @endif


                                                    <div class="card-body row align-items-center">
                                                        <div class="user-group1">
                                                            <span class="avatar avatar-sm rounded-circle mr-2">
                                                                <img alt="image" data-toggle="tooltip"
                                                                    data-original-title="{{ !empty($task->taskUser) ? $task->taskUser->name : '' }}"
                                                                    @if ($task->taskUser && !empty($task->taskUser->avatar)) src="{{ $profile . '/' . $task->taskUser->avatar }}" @else avatar="{{ !empty($task->taskUser) ? $task->taskUser->name : '' }}" @endif
                                                                    class="">
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
                                                                    {{ \Auth::user()->dateFormat($task->start_date) }}
                                                                </div>
                                                            </div>
                                                            <div class="actions d-inline-block text-end float-sm-none">
                                                                <div class="action-item ml-4 ms-5 pt-3">
                                                                    <i class="ti ti-calendar-event"></i>
                                                                    {{ \Auth::user()->dateFormat($task->due_date) }}
                                                                </div>
                                                            </div>
                                                            <span class="col-auto" style="margin-left: 500px">
                                                                @if (\Auth::user()->type == 'company')
                                                                    <div class="action-btn bg-info ms-2">
                                                                        <a href="#"
                                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                            data-size="lg"
                                                                            data-bs-whatever="{{ __('Edit Task') }}"
                                                                            data-url="{{ route('project.task.edit', $task->id) }}"
                                                                            data-bs-target="#exampleModal"
                                                                            data-bs-toggle="modal"> <span
                                                                                class="text-white"> <i class="ti ti-edit"
                                                                                    data-bs-toggle="tooltip"
                                                                                    data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                                                    </div>
                                                                @endif
                                                                <div class="action-btn bg-warning ms-2">
                                                                    <a href="#"class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                        data-bs-target="#exampleModal"
                                                                        data-bs-toggle="modal"
                                                                        data-url="{{ route('project.task.show', $task->id) }}"
                                                                        data-bs-whatever="{{ __('View Task') }}"
                                                                        data-size="lg"> <span class="text-white">
                                                                            <i class="ti ti-eye" data-bs-toggle="tooltip"
                                                                                data-bs-original-title="{{ __('View') }}">
                                                                            </i>
                                                                        </span>
                                                                    </a>
                                                                </div>

                                                                @if (\Auth::user()->type == 'company')
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['project.task.destroy', $task->id]]) !!}
                                                                        <a href="#!"
                                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm m-2">
                                                                            <i class="ti ti-trash text-white"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-original-title="{{ __('Delete') }}"></i>
                                                                        </a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endif
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

                    <!--Task Kanban-->
                    <div id="useradd-3">
                        <div class="card">
                            <div class="card-header">
                                <div class="float-end">
                                    @if (\Auth::user()->type == 'company')
                                        <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                            <a href="#" class="btn btn-sm btn-primary btn-icon m-1"
                                                data-bs-toggle="modal" data-size="lg" data-bs-target="#exampleModal"
                                                data-url="{{ route('project.task.create', $project->id) }}"
                                                data-bs-whatever="{{ __('Create New Task') }}"> <span
                                                    class="text-white">
                                                    <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Create') }}"></i></span>
                                            </a>
                                        </p>
                                    @endif
                                </div>
                                <h5 class="mb-0">{{ __('Task') }}</h5>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">

                                    @php
                                        $json = [];
                                        foreach ($stages as $stage) {
                                            $json[] = 'kanban-blacklist-' . $stage->id;
                                        }
                                    @endphp

                                    <div class="row kanban-wrapper horizontal-scroll-cards kanban-board"
                                        data-containers='{!! json_encode($json) !!}' data-plugin="dragula">
                                        @foreach ($stages as $stage)
                                            @php $tasks =$stage->tasks($project->id) @endphp
                                            <div class="col-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <div class="float-end">
                                                            <button class="btn btn-sm btn-primary btn-icon task-header">
                                                                <span class="count text-white">{{ count($tasks) }}</span>
                                                            </button>
                                                        </div>
                                                        <h4 class="mb-0">{{ $stage->name }}</h4>
                                                    </div>
                                                    <div class="card-body kanban-box"
                                                        id="kanban-blacklist-{{ $stage->id }}"
                                                        data-id="{{ $stage->id }}">
                                                        @foreach ($tasks as $task)
                                                            <div class="card" data-id="{{ $task->id }}">
                                                                <div class="pt-3 ps-1">
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
                                                                    <div
                                                                        class="card-header border-0 pb-0 position-relative">
                                                                        <h5>
                                                                            <a href="#"
                                                                                data-url="{{ route('project.task.show', $task->id) }}"
                                                                                data-toggle="modal"
                                                                                data-bs-target="#exampleModal"
                                                                                data-ajax-popup="true"
                                                                                data-bs-whatever="{{ __('View Task Details') }}"
                                                                                data-bs-toggle="modal" title
                                                                                data-toggle="tooltip" data-size="lg"
                                                                                data-bs-original-title="{{ __('Task Detail') }}">{{ $task->title }}</a>
                                                                        </h5>

                                                                        <div class="card-header-right">
                                                                            <div class="btn-group card-option">
                                                                                <div
                                                                                    class="dropdown-menu dropdown-menu-end">
                                                                                    @if (\Auth::user()->type == 'company')
                                                                                        <a href="#!"
                                                                                            class="dropdown-item"
                                                                                            data-size="lg"
                                                                                            data-url="{{ route('project.task.edit', $task->id) }}"
                                                                                            data-bs-toggle="modal"
                                                                                            data-bs-target="#exampleModal"
                                                                                            data-bs-whatever="{{ __('Edit Task') }}">
                                                                                            <i class="ti ti-edit"></i>
                                                                                            <span>{{ __('Edit') }}</span>
                                                                                        </a>
                                                                                    @endif

                                                                                    @if (\Auth::user()->type == 'company')
                                                                                        <a href="#!"
                                                                                            class="dropdown-item"
                                                                                            data-size="lg"
                                                                                            data-url="{{ route('project.task.show', $task->id) }}"
                                                                                            data-bs-toggle="modal"
                                                                                            data-bs-target="#exampleModal"
                                                                                            data-bs-whatever="{{ __('View') }}">
                                                                                            <i class="ti ti-eye"></i>
                                                                                            <span>{{ __('View') }}</span>
                                                                                        </a>
                                                                                    @endif

                                                                                    @if (\Auth::user()->type == 'company')
                                                                                        <span class="dropdown-item">
                                                                                            {!! Form::open([
                                                                                                'method' => 'DELETE',
                                                                                                'route' => ['project.task.destroy', $task->id],
                                                                                                'id' => 'delete-form-' . $task->id,
                                                                                            ]) !!}
                                                                                            <a href="#!"
                                                                                                class=" show_confirm">
                                                                                                <i
                                                                                                    class="ti ti-trash"></i>{{ __('Delete') }}
                                                                                            </a>
                                                                                            {!! Form::close() !!}
                                                                                        </span>
                                                                                    @endif

                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card-body">

                                                                        <div
                                                                            class="d-flex align-items-center justify-content-between">
                                                                            <ul class="list-inline mb-3 ms-1">
                                                                                <li
                                                                                    class="list-inline-item d-inline-flex align-items-center">
                                                                                    <i
                                                                                        class="f-16 text-primary ti ti-calendar-stats"></i>{{ \Auth::user()->dateFormat($task->start_date) }}
                                                                                </li>
                                                                                <li
                                                                                    class="list-inline-item d-inline-flex align-items-center">
                                                                                    <i
                                                                                        class="f-16 text-primary ti ti-calendar-stats ms-2"></i>{{ \Auth::user()->dateFormat($task->due_date) }}
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                        <div
                                                                            class="d-flex align-items-center justify-content-between">
                                                                            <ul class="list-inline mb-0 mt-3">
                                                                                <li
                                                                                    class="list-inline-item d-inline-flex align-items-center">
                                                                                    <i
                                                                                        class="f-16 text-primary ti ti-discount"></i>
                                                                                    {{ $task->taskCompleteCheckListCount() }}/{{ $task->taskTotalCheckListCount() }}
                                                                                </li>
                                                                                <li class="list-inline-item d-inline-flex align-items-center "
                                                                                    style="
                                                                                        margin-left: 130px;
                                                                                    ">
                                                                                    <div class="user-group">
                                                                                        <a href="#"
                                                                                            class="avatar rounded-circle avatar-sm text-end"
                                                                                            data-original-title="{{ !empty($task->taskUser) ? $task->taskUser->name : '' }}"
                                                                                            data-toggle="tooltip">
                                                                                            <img @if ($task->taskUser && !empty($task->taskUser->avatar)) src="{{ $profile . '/' . $task->taskUser->avatar }}" @else avatar="{{ !empty($task->taskUser) ? $task->taskUser->name : '' }}" @endif
                                                                                                class="">
                                                                                        </a>

                                                                                    </div>
                                                                                </li>
                                                                            </ul>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- [ sample-page ] end -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Gantt Chart-->
                    <div id="useradd-4">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card overflow-hidden ">
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
                    </div>


                    <!--Milestone-->
                    <div id="useradd-5">
                        <div class="card">
                            <div class="card-header">
                                <div class="float-end">
                                    @if (\Auth::user()->type == 'company')
                                        <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                            <a href="#" class="btn btn-sm btn-primary btn-icon m-1"
                                                data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                data-url="{{ route('project.milestone.create', $project->id) }}"
                                                data-bs-whatever="{{ __('Create New Milestone') }}"> <span
                                                    class="text-white">
                                                    <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Create') }}"></i></span>
                                            </a>
                                        </p>
                                    @endif
                                </div>
                                <h5 class="mb-0">{{ __('Milestone') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="pc-dt-simple">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('Title') }}</th>
                                                <th scope="col">{{ __('Description') }}</th>
                                                <th scope="col">{{ __('Start Date') }}</th>
                                                <th scope="col">{{ __('Due Date') }}</th>
                                                <th scope="col">{{ __('Cost') }}</th>
                                                <th scope="col">{{ __('Progress') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                @if (\Auth::user()->type == 'company')
                                                    <th scope="col" class="text-right">{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($milestones as $milestone)
                                                <tr>
                                                    <th scope="row">
                                                        <div class="media align-items-center">
                                                            <div class="media-body">
                                                                <a href="#"
                                                                    class="name h6 mb-0 text-sm">{{ $milestone->title }}</a><br>
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <td>{{ $milestone->description }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($milestone->start_date) }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($milestone->due_date) }}</td>
                                                    <td>{{ \Auth::user()->priceFormat($milestone->cost) }}</td>
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

                                                    <td><span
                                                            class="badge fix_badges bg-info p-2 px-3 rounded">{{ $milestone->status }}</span>
                                                    </td>

                                                    @if (\Auth::user()->type == 'company')
                                                        <td class="text-right">
                                                            <div class="action-btn bg-info ms-2">
                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                                    data-url="{{ route('project.milestone.edit', $milestone->id) }}"
                                                                    data-bs-whatever="{{ __('Edit Milestone') }}"> <span
                                                                        class="text-white"> <i class="ti ti-edit"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                                            </div>

                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['project.milestone.destroy', $milestone->id]]) !!}
                                                                <a href="#!"
                                                                    class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                                                    <i class="ti ti-trash text-white"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-original-title="{{ __('Delete') }}"></i>
                                                                </a>
                                                                {!! Form::close() !!}


                                                            </div>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Notes-->
                    <div id="useradd-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="float-end">
                                    @if (\Auth::user()->type == 'company')
                                        <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                            <a href="#" class="btn btn-sm btn-primary btn-icon m-1"
                                                data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                data-url="{{ route('project.note.create', $project->id) }}"
                                                data-bs-whatever="{{ __('Create New Notes') }}"> <span
                                                    class="text-white">
                                                    <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Create') }}"></i></span>
                                            </a>
                                        </p>
                                    @endif
                                </div>
                                <h5 class="mb-0">{{ __('Notes') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="pc-dt-simple">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('Title') }}</th>
                                                <th scope="col">{{ __('Description') }}</th>
                                                <th scope="col">{{ __('Created Date') }}</th>
                                                @if (\Auth::user()->type == 'company')
                                                    <th scope="col" class="text-end">{{ __('Action') }}</th>
                                                @endif
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
                                                    <td>{{ \Auth::user()->dateFormat($note->created_at) }}</td>
                                                    @if (\Auth::user()->type == 'company')
                                                        <td class="text-end">
                                                            <div class="action-btn bg-info ms-2">
                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                                    data-url="{{ route('project.note.edit', [$project->id, $note->id]) }}"
                                                                    data-bs-whatever="{{ __('Edit Notes') }}"> <span
                                                                        class="text-white"> <i class="ti ti-edit"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                                            </div>

                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['project.note.destroy', $project->id, $note->id]]) !!}
                                                                <a href="#!"
                                                                    class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                                    <i class="ti ti-trash text-white"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-original-title="{{ __('Delete') }}"></i>
                                                                </a>
                                                                {!! Form::close() !!}


                                                            </div>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Files-->
                    <div id="useradd-7">
                        <div class="card">
                            <div class="card-header">
                                <div class="float-end">
                                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
                                        <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                            <a href="#" class="btn btn-sm btn-primary btn-icon m-1"
                                                data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                data-url="{{ route('project.file.create', $project->id) }}"
                                                data-bs-whatever="{{ __('Create New Files') }}"> <span
                                                    class="text-white">
                                                    <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Create') }}"></i></span>
                                            </a>
                                        </p>
                                    @endif
                                </div>
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
                                                <th scope="col" class="text-end">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($files as $file)
                                                @php

                                                @endphp
                                                <tr>
                                                    <th scope="row">
                                                        <div class="media align-items-center">
                                                            <div class="media-body user-group1">
                                                                <img alt="Image placeholder"
                                                                    src="{{ \App\Models\Utility::get_file('uploads/files/') . '/' . $file->file }}"
                                                                    class=""><br>
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <td>{{ $file->file }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($file->created_at) }}</td>
                                                    <td class="text-end">
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="{{ \App\Models\Utility::get_file('uploads/files/') . '/' . $file->file }}"
                                                                class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                download="">
                                                                <i data-bs-toggle="tooltip"
                                                                    data-bs-original-title="{{ __('Download') }}"
                                                                    class="ti ti-arrow-bar-to-down text-white"></i>
                                                            </a>
                                                        </div>
                                                        <div class="action-btn bg-secondary ms-2">
                                                            <a href="{{ \App\Models\Utility::get_file('uploads/files/') . '/' . $file->file }}"
                                                                class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                data-bs-toggle="tooltip" target="_blank"
                                                                data-original-title="{{ __('Preview') }}">
                                                                <i class="ti ti-crosshair text-white"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-original-title="{{ __('Preview') }}"></i>
                                                            </a>
                                                        </div>
                                                        @if (\Auth::user()->type == 'company')
                                                            <div class="action-btn bg-info ms-2">
                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                                    data-url="{{ route('project.file.edit', [$project->id, $file->id]) }}"
                                                                    data-bs-whatever="{{ __('Edit Files') }}"> <span
                                                                        class="text-white"> <i class="ti ti-edit"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                                            </div>

                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['project.file.destroy', $project->id, $file->id]]) !!}
                                                                <a href="#!"
                                                                    class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                                                    <i class="ti ti-trash text-white"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-original-title="{{ __('Delete') }}"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Comments-->
                    <div id="useradd-8">
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
                                                <small class="text-muted float-right">{{ $comment->created_at }}</small>
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
                                                            <a href="{{ \App\Models\Utility::get_file('uploads/files') . '/' . $comment->file }}"
                                                                download=""
                                                                class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                                                <i class="ti ti-download text-white"></i> </a>
                                                        </a>
                                                    </div>
                                                @endif
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#"
                                                        data-url="{{ route('project.comment.reply', [$project->id, $comment->id]) }}"
                                                        data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                        data-bs-whatever="{{ __('Create Comment Reply') }}"
                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        data-bs-toggle="tooltip" title="{{ __('Reply') }}">
                                                        <i class="ti ti-send text-white"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            @foreach ($comment->subComment as $subComment)
                                                <div class="media mt-3">
                                                    <a class="pr-2" href="#">
                                                        <img @if (!empty($subComment->commentUser && !empty($subComment->commentUser->avatar))) src="{{ $profile . '/' . $subComment->commentUser->avatar }}" @else  avatar="{{ !empty($subComment->commentUser) ? $subComment->commentUser->name : '' }}" @endif
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
                                                                <a href="{{ \App\Models\Utility::get_file('uploads/files') . '/' . $subComment->file }}"
                                                                    download="" data-bs-toggle="tooptip"
                                                                    title="{{ __('Download') }}"
                                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center"><i
                                                                        class="ti ti-download text-white"></i></a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <div class="border rounded mt-4">

                                    {{ Form::open(['route' => ['project.comment.store', $project->id], 'enctype' => 'multipart/form-data']) }}
                                    <textarea rows="3" class="form-control border-0 resize-none project_comment" name="comment"
                                        placeholder="Your comment..." required></textarea>
                                    <div class="p-2 bg-light d-flex justify-content-between align-items-center">
                                        <div class="col-8">
                                            <input type="file" class="form-control" name="file" id="file">
                                        </div>
                                        @if (App\Models\Utility::is_chatgpt_enable())
                                            <div class="">
                                                <a data-size="md" class="btn btn-primary btn-icon btn-sm text-white "
                                                    data-ajax-popup-over="true" id="grammarCheck"
                                                    data-url="{{ route('grammar', ['project_comment']) }}"
                                                    data-bs-placement="top"
                                                    data-title="{{ __('Grammar check with AI') }}">
                                                    <i class="ti ti-rotate"></i>
                                                    <span>{{ __('Grammar check with AI') }}</span></a>
                                            </div>
                                        @endif
                                        <button type="submit" class="btn btn-primary"><i
                                                class='uil uil-message mr-1'></i>{{ __('Post') }}</button>
                                    </div>
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Client Feedback-->
                    <div id="useradd-9">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Client Feedback') }}</h5>
                            </div>
                            <div class="card-body">
                                @foreach ($feedbacks as $feedback)
                                    <div class="media mb-2">
                                        <a class="pr-2" href="#">
                                            <img @if (!empty($feedback->feedbackUser) && !empty($feedback->feedbackUser->avatar)) src="{{ $profile . '/' . $feedback->feedbackUser->avatar }}" @else  avatar="{{ !empty($feedback->feedbackUser) ? $feedback->feedbackUser->name : '' }}" @endif
                                                class="rounded-circle" alt="" height="32">
                                        </a>
                                        <div class="media-body">
                                            <h6 class="mt-0 ms-2">
                                                {{ !empty($feedback->feedbackUser) ? $feedback->feedbackUser->name : '' }}
                                                <small class="text-muted float-right">{{ $feedback->created_at }}</small>
                                            </h6>

                                            <p class="text-sm mb-0 ms-2">
                                                {{ $feedback->feedback }}
                                            </p>
                                            <div class="text-end">
                                                @if (!empty($feedback->file))
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="{{ \App\Models\Utility::get_file('uploads/files') . '/' . $feedback->file }}"
                                                            download=""
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center like active"
                                                            data-bs-toggle="tooltip" title="{{ __('Download') }}"> <i
                                                                class="ti ti-download text-white"></i> </a>

                                                    </div>
                                                @endif
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#"
                                                        data-url="{{ route('project.client.feedback.reply', [$project->id, $feedback->id]) }}"
                                                        data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                        data-bs-whatever="{{ __('Create feedback Reply') }}"
                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                        <i class="ti ti-send text-white" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Reply') }}"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            @foreach ($feedback->subfeedback as $subfeedback)
                                                <div class="media mt-3">
                                                    <a class="pr-2" href="#">
                                                        <img @if (!empty($subfeedback->feedbackUser && !empty($subfeedback->feedbackUser->avatar))) src="{{ $profile . '/' . $subfeedback->feedbackUser->avatar }}" @else  avatar="{{ !empty($subfeedback->feedbackUser) ? $subfeedback->feedbackUser->name : '' }}" @endif
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

                                                        @if (!empty($subfeedback->file))
                                                            <div class="action-btn bg-warning ms-2">
                                                                <a href="{{ \App\Models\Utility::get_file('uploads/files') . '/' . $subfeedback->file }}"
                                                                    download="" data-bs-toggle="tooptip"
                                                                    title="{{ __('Download') }}"
                                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center"><i
                                                                        class="ti ti-download text-white"></i></a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <div class="border rounded mt-4">

                                    {{ Form::open(['route' => ['project.client.feedback.store', $project->id], 'enctype' => 'multipart/form-data']) }}
                                    <textarea rows="3" class="form-control border-0 resize-none" name="feedback" placeholder="Your feedback..."
                                        required></textarea>
                                    <div class="p-2 bg-light d-flex justify-content-between align-items-center">
                                        <div class="col-8">
                                            <input type="file" class="form-control" name="file" id="file">
                                        </div>
                                        <button type="submit" class="btn btn-primary"><i
                                                class='uil uil-message mr-1'></i>{{ __('Post') }}</button>
                                    </div>
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Invoice-->
                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
                        <div id="useradd-10">
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
                                                                        href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}">{{ \Auth::user()->invoiceNumberFormat($invoice->invoice_id) }}</a>
                                                                </h6>
                                                            </div>
                                                            <div class="col-2 text-end">
                                                                <div class="actions">
                                                                    <div class="dropdown">
                                                                        <a href="#" class="action-item"
                                                                            data-bs-toggle="dropdown"><i
                                                                                class="fas fa-ellipsis-v"></i></a>
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
                                                                                <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                                    class="dropdown-item">
                                                                                    <i
                                                                                        class="ti ti-eye"></i>{{ __('View') }}
                                                                                </a>
                                                                            @endif

                                                                            @if (\Auth::user()->type == 'company')
                                                                                <a href="#!"
                                                                                    data-url="{{ route('invoice.edit', $invoice->id) }}"
                                                                                    class="dropdown-item"
                                                                                    data-toggle="tooltip"
                                                                                    data-original-title="{{ __('Edit') }}"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#exampleModal"
                                                                                    data-bs-whatever="{{ __('Edit Invoice') }}">
                                                                                    <i
                                                                                        class="ti ti-edit"></i>{{ __('Edit') }}
                                                                                </a>


                                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id]]) !!}
                                                                                <a href="#!"
                                                                                    class=" show_confirm dropdown-item">
                                                                                    <i
                                                                                        class="ti ti-trash"></i>{{ __('Delete') }}
                                                                                </a>
                                                                                {!! Form::close() !!}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
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
                                                                        {{ \Auth::user()->priceFormat($invoice->getTotal()) }}
                                                                    </h6>
                                                                    <span
                                                                        class="text-sm text-muted">{{ __('Total Amount') }}</span>
                                                                </div>
                                                                <div class="col-6">
                                                                    <h6 class="mb-0">
                                                                        {{ \Auth::user()->priceFormat($invoice->getDue()) }}
                                                                    </h6>
                                                                    <span
                                                                        class="text-sm text-muted">{{ __('Due Amount') }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="row align-items-center mt-3">
                                                                <div class="col-6">
                                                                    <h6 class="mb-0">
                                                                        {{ \Auth::user()->dateFormat($invoice->issue_date) }}
                                                                    </h6>
                                                                    <span
                                                                        class="text-sm text-muted">{{ __('Issue Date') }}</span>
                                                                </div>
                                                                <div class="col-6">
                                                                    <h6 class="mb-0">
                                                                        {{ \Auth::user()->dateFormat($invoice->due_date) }}
                                                                    </h6>
                                                                    <span
                                                                        class="text-sm text-muted">{{ __('Due Date') }}</span>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        @if (\Auth::user()->type != 'client')
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

                    <!--Timesheets-->
                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                        <div id="useradd-11">
                            <div class="card">
                                <div class="card-header">
                                    <div class="float-end">
                                        @if (\Auth::user()->type == 'company')
                                            <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                                <a href="#" class="btn btn-sm btn-primary btn-icon m-1"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                    data-url="{{ route('project.timesheet.create', $project->id) }}"
                                                    data-bs-whatever="{{ __('Create New Timesheet') }}"> <span
                                                        class="text-white">
                                                        <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Create') }}"></i></span>
                                                </a>
                                            </p>
                                        @endif
                                    </div>
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
                                                    <th scope="col">{{ __('Notes') }}</th>
                                                    @if (\Auth::user()->type == 'company')
                                                        <th scope="col" class="text-end">{{ __('Action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($timesheets as $timesheet)
                                                    <tr>
                                                        <td>{{ !empty($timesheet->users) ? $timesheet->users->name : '-' }}
                                                        </td>
                                                        <td> {{ !empty($timesheet->tasks) ? $timesheet->tasks->title : '-' }}
                                                        </td>
                                                        <td>{{ \Auth::user()->dateFormat($timesheet->start_date) }}</td>
                                                        <td>{{ \Auth::user()->timeFormat($timesheet->start_time) }}</td>
                                                        <td>{{ \Auth::user()->dateFormat($timesheet->end_date) }}</td>
                                                        <td>{{ \Auth::user()->timeFormat($timesheet->end_time) }}</td>
                                                        <td>
                                                            <div class="action-btn bg-warning ms-2">
                                                                <a href="#"
                                                                    data-url="{{ route('project.timesheet.note', [$project->id, $timesheet->id]) }}"
                                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Timesheet Notes') }}"
                                                                    data-bs-whatever="{{ __('Timesheet Notes') }}"
                                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                                    <i class="ti ti-brand-hipchat text-white"></i></a>
                                                            </div>
                                                        </td>
                                                        @if (\Auth::user()->type == 'company')
                                                            <td class="table-actions text-end">
                                                                <div class="action-btn bg-info ms-2">
                                                                    <a href="#"
                                                                        data-url="{{ route('project.timesheet.edit', [$project->id, $timesheet->id]) }}"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#exampleModal"
                                                                        title="{{ __('Edit Timesheet') }}"
                                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                                        <i class="ti ti-edit text-white"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-original-title="{{ __('Edit') }}"></i>
                                                                    </a>
                                                                </div>

                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['project.timesheet.destroy', $project->id, $timesheet->id]]) !!}
                                                                    <a href="#!"
                                                                        class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                                        <i class="ti ti-trash text-white"></i>
                                                                    </a>
                                                                    {!! Form::close() !!}

                                                                </div>

                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!--payments-->
                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
                        <div id="useradd-12">
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
                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($invoices as $invoice)
                                                    @foreach ($invoice->payments as $payment)
                                                        <tr>
                                                            <td>{{ \Auth::user()->invoiceNumberFormat($invoice->invoice_id) }}
                                                            </td>
                                                            <td>{{ $payment->transaction }} </td>
                                                            <td>{{ \Auth::user()->dateFormat($payment->date) }} </td>
                                                            <td>{{ !empty($payment->payments) ? $payment->payments->name : '' }}
                                                            </td>
                                                            <td>{{ $payment->payment_type }} </td>
                                                            <td>{{ $payment->notes }} </td>
                                                            <td> {{ \Auth::user()->priceFormat($payment->amount) }}</td>
                                                            <td width="7%" class="text-end">
                                                                <div class="action-btn bg-warning ms-2">
                                                                    <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('View') }}">
                                                                        <i class="ti ti-eye text-white"></i>
                                                                    </a>
                                                                </div>
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
                    @endif

                    <!--Expense-->
                    @if (\Auth::user()->type == 'company')
                        <div id="useradd-13">
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
                                                        <td>{{ Auth::user()->dateFormat($expense->date) }}</td>
                                                        <td>{{ Auth::user()->priceFormat($expense->amount) }}</td>
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
