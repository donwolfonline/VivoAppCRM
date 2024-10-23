@extends('layouts.admin')

@push('pre-purpose-script-page')
<script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>



<script type="text/javascript">
    $(document).ready(function() {
        get_data();
    });

    function get_data() {
        var calender_type = $('#calender_type :selected').val();
        $('#calendar').removeClass('local_calender');
        $('#calendar').removeClass('google_calender');
        $('#calendar').addClass(calender_type);
        if (calender_type == undefined) {
            calender_type = 'local_calender';
        }
        $.ajax({
            url: "{{ url('/') }}" + "/meeting/get_holiday_data",
            method: "POST",
            data: {
                "_token": "{{ csrf_token() }}",
                'calender_type': calender_type
            },
            success: function(data) {
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
                        // slotDuration: '00:10:00',
                        navLinks: true,
                        droppable: true,
                        selectable: true,
                        selectMirror: true,
                        editable: true,
                        dayMaxEvents: true,
                        handleWindowResize: true,
                        height: 'auto',
                        timeFormat: 'H(:mm)',
                        events: data,
                    });
                    calendar.render();
                })();
            }
        });
    }
</script>
@endpush
@section('page-title')
{{ __('Meeting') }}
@endsection
@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Meeting') }}</h5>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ __('HR') }}</li>
<li class="breadcrumb-item active" aria-current="page">{{ __('Meeting') }}</li>
@endsection
@section('action-btn')
<a href="{{ route('meeting.index') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
    data-bs-placement="top" title="List View"> <span class="text-white">
        <i class="ti ti-list text-white"></i></span>
</a>

@if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
<a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
    data-url="{{ route('meeting.create') }}" data-bs-whatever="{{ __('Create New Meeting') }}"> <span
        class="text-white">
        <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Create') }}"></i></span>
</a>
@endif

@endsection
@section('content')
<div class="row">
    <!-- [ sample-page ] start -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Calendar') }}

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
                </h5>
            </div>
      
            <div class="card-body">
                <div id='calendar' class='calendar local_calender'></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">

        <div class="card">
            <div class="card-body">
                <h4 class="mb-4">{{ __('Current Month Meeting') }}</h4>
                <ul class="event-cards list-group list-group-flush mt-3 w-100">

                    @foreach ($meeting_current_month as $meeting)
                    
                    <li class="list-group-item card mb-3">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-arrow-ramp-right"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="m-0">{{ $meeting->title }}</h6>
                                        <small class="text-muted">{{ $meeting->date }}</small>
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
    <!-- [ sample-page ] end -->
</div>
@endsection
