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
                $(document).on('submit', '#form-file', function(e) {
             e.preventDefault();
            $.ajax({
                url: $("#form-file").data('url'),
                type: 'POST',
                data: new FormData(this),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {
                    toastrs('Success', '{{ __('File successfully uploaded.') }}', 'success');

                    var html = '<div class="card mb-3 border shadow-none">\n' +
                        '                            <div class="px-3 py-3">\n' +
                        '                                <div class="row align-items-center">\n' +
                        '                                    <div class="col ml-n2">\n' +
                        '                                        <h6 class="text-sm mb-0">\n' +
                        '                                            <a href="#!">' + data.name +
                        '</a>\n' +
                        '                                        </h6>\n' +
                        '                                        <p class="card-text small text-muted">\n' +
                        '                                            ' + data.file_size + '\n' +
                        '                                        </p>\n' +
                        '                                    </div>\n' +
                        '                                    <div class="col-auto actions">\n' +
                        '                                        <a download href="{{ asset(Storage::url('tasks')) }}' +
                        data.file + '" class="action-btn bg-info ms-2  btn btn-sm d-inline-flex align-items-center">\n' +
                        '                                            <i class="ti ti-download text-white"></i>\n' +
                        '                                        </a>\n' +
                        '                                        <a href="#" class="action-btn bg-danger ms-2 btn btn-sm d-inline-flex align-items-center delete-comment-file" data-url="' +
                        data.deleteUrl + '">\n' +
                        '                                            <i class="ti ti-trash"></i>\n' +
                        '                                        </a>\n' +
                        '\n' +
                        '                                    </div>\n' +
                        '                                </div>\n' +
                        '                            </div>\n' +
                        '                        </div>';
                    $("#comments-file").prepend(html);
               
                },
                error: function(data) {
                    data = data.responseJSON;
                    if (data.message) {
                        toastrs('Error', data.message, 'error');
                        $('#file-error').text(data.errors.file[0]).show();
                    } else {
                        toastrs('Error', '{{ __('Some Thing Is Wrong!') }}', 'error');
                    }
                }
            });
        });
        $(document).on("click", ".delete-comment-file", function() {

            if (confirm('Are You Sure ?')) {
                var div = $(this).parent().parent().parent().parent();

                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'JSON',
                    success: function(data) {
                        toastrs('Success', '{{ __('File successfully deleted.') }}', 'success');
                        div.remove();
                    },
                    error: function(data) {
                        data = data.responseJSON;
                        if (data.message) {
                            toastrs('Error', data.message, 'error');
                        } else {
                            toastrs('Error', '{{ __('Some thing is wrong.') }}', 'error');
                        }
                    }
                });
            }
        });
    </script>
@endpush
@section('page-title')
    {{ __('Task Calendar') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Task') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Task Calendar') }}</li>
@endsection
@section('action-btn')

    <a href="{{ route('project.all.task') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="List View" >
        <i class="ti ti-list text-white"></i>
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

    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 style="width: 150px;">{{ __('Calendar') }}</h5>
                    @if (
                        !empty(App\Models\Utility::settings()['is_googleCal_enabled']) &&
                            App\Models\Utility::settings()['is_googleCal_enabled'] == 'on')
                        <div class="form-group">
                            <label for=""></label>
                            <select class="form-control" name="calender_type" id="calender_type"
                                style="float: right;width: 160px;margin-top: -30px;" onchange="get_data()">
                                <option value="google_calender">{{ __('Google Calender') }}</option>
                                <option value="local_calender" selected="true">{{ __('Local Calender') }}</option>
                            </select>
                            <input type="hidden" id="path_admin" value="{{ url('/') }}">
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div id='calendar' class='calendar local_calender'></div>
                </div>
            </div>
            <!-- [ sample-page ] end -->
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-4">{{ __('Next Task') }}</h4>
                    <ul class="event-cards list-group list-group-flush mt-3 w-100">
                    
                        @foreach ($userTask as $event)
                            <li class="list-group-item card mb-3">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class=" align-items-center">
                                            <div class="ms-3">
                                                <h6 class="m-0">{{ $event->title }}</h6>
                                                <small
                                                    class="text-muted">{{ \Auth::user()->dateFormat($event->start_date) . ' ' . \Auth::user()->timeFormat($event->start_time) . ' ' . __('To') . ' ' . \Auth::user()->dateFormat($event->due_date) . ' ' . \Auth::user()->timeFormat($event->due_date) }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script-page')
    
    <script type="text/javascript">
        $(document).ready(function() {
            get_data();
        });

        function get_data() {
            var calender_type = $('#calender_type :selected').val();
            //$('#calendar').removeClass('local_calender');
            $('#calendar').removeClass('google_calender');
            $('#calendar').addClass(calender_type);
            if(calender_type == undefined){
                    calender_type = 'local_calender';
                }
            $.ajax({
                // url: $("#path_admin").val() + "/event/get_event_data",
                url: "{{ url('/') }}" + "/task/get_holiday_data",
                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'calender_type': calender_type
                },
                success: function(data) {
                    // console.log(data);
                    (function() {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                dayGridMonth: "{{ __('Month') }}"
                            },
                            themeSystem: 'bootstrap',
                            slotDuration: '00:10:00',
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                        });

                        calendar.render();
                    })();
                }
            });
        }
    </script>
@endpush