@extends('layouts.admin')
@php
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
    // $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('css-page')
    <link rel="stylesheet" href="{{asset('css/frappe-gantt.css')}}"/>
@endpush
@push('script-page')
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
    <script src="{{asset('js/frappe-gantt.js')}}"></script>
    <script>

        var tasks = JSON.parse('{!! addslashes(json_encode($ganttTasks)) !!}');

        var gantt = new Gantt('#gantt', tasks, {

            custom_popup_html: function (task) {
                var status_class = 'success';
                if (task.custom_class == 'medium') {
                    status_class = 'info'
                } else if (task.custom_class == 'high') {
                    status_class = 'danger'
                }
                return `<div class="details-container">
                                <div class="title">${task.name} <span class="badge badge-${status_class} float-right">${task.extra.priority}</span></div>
                                <div class="subtitle">

                                    <b>{{ __('Stage')}} : </b> ${task.extra.stage}<br>
                                    <b>{{ __('Duration')}} : </b> ${task.extra.duration}<br>
                                    <b>{{ __('Description')}} : </b> ${task.extra.description}

                                </div>
                            </div>
                          `;
            },
            on_click: function (task) {
            },
            on_date_change: function (task, start, end) {
                task_id = task.id;
                start = moment(start);
                end = moment(end);
                $.ajax({
                    url: "{{route('project.gantt.post',0)}}",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        start: start.format('YYYY-MM-DD HH:mm:ss'),
                        end: end.format('YYYY-MM-DD HH:mm:ss'),
                        task_id: task_id,
                    },
                    type: 'POST',
                    success: function (data) {

                    },
                    error: function (data) {
                        toastrs('Error', '{{ __("Some Thing Is Wrong!")}}', 'error');
                    }
                });
            },
        });

        gantt.change_view_mode('{{$duration}}');



    </script>
@endpush
@section('page-title')
    {{__('Task Gantt Chart')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Task Gantt Chart')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Project')}}</li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Task Gantt Chart')}}</li>
@endsection
@section('action-btn')
    <a href="{{ route('task.calendar') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="Calendar View" >
        <i class="ti ti-calendar text-white"></i>
    </a>
    <a href="{{ route('project.all.task.kanban') }}" class="btn btn-sm btn-primary btn-icon m-1">
        <i  data-bs-toggle="tooltip"  data-bs-original-title="{{__('Kanban View')}}" class="ti ti-layout-kanban"></i>
    </a>
    <a href="{{ route('project.all.task') }}" class="btn btn-sm btn-primary btn-icon m-1">
        <i  data-bs-toggle="tooltip"  data-bs-original-title="{{__('List View')}}" class="ti ti-list"></i>
    </a>

    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" 
    data-bs-target="#exampleModal" data-url="{{ route('project.task.create',0) }}" data-size="lg"
    data-bs-whatever="{{__('Create New Task')}}" >
        <i data-bs-toggle="tooltip"  data-bs-original-title="{{__('Create')}}" class="ti ti-plus text-white"></i>
    </a>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card overflow-hidden ">
                <div class="card-header actions-toolbar">
                    <div class="row justify-content-between align-items-center">
                        <div class="col">
                            <h6 class="d-inline-block mb-0">{{__('Gantt Chart')}}</h6>
                        </div>
                        <div class="float-end">
                            <a href="{{route('project.all.task.gantt.chart','Quarter Day')}}"
                                class="btn btn-sm btn-info gantt-chart-mode  @if ($duration == 'Quarter Day') active @endif"
                                data-value="Quarter Day">{{ __('Quarter Day') }}</a>
                            <a href="{{route('project.all.task.gantt.chart','Half Day')}}"
                                class="btn btn-sm btn-info gantt-chart-mode @if ($duration == 'Half Day') active @endif"
                                data-value="Half Day">{{ __('Half Day') }}</a>
                            <a href="{{route('project.all.task.gantt.chart','Day')}}"
                                class="btn btn-sm btn-info gantt-chart-mode @if ($duration == 'Day') active @endif"
                                data-value="Day">{{ __('Day') }}</a>
                            <a href="{{route('project.all.task.gantt.chart','Week')}}"
                                class="btn btn-sm btn-info gantt-chart-mode @if ($duration == 'Week') active @endif"
                                data-value="Week">{{ __('Week') }}</a>
                            <a href="{{route('project.all.task.gantt.chart','Month')}}"
                                class="btn btn-sm btn-info gantt-chart-mode @if ($duration == 'Month') active @endif"
                                data-value="Month">{{ __('Month') }}</a>
                        </div>


                    </div>
                </div>
                <div class="card-body">
                    <svg id="gantt"></svg>
                </div>
            </div>
        </div>
    </div>
@endsection



