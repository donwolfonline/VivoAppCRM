@extends('layouts.admin')
@push('pre-purpose-css-page-page')
@endpush

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
        // url: $("#path_admin").val() "{{ url('/') }}" + "/holiday/get_holiday_data",
        url: "{{ url('/') }}" + "/holiday/get_holiday_data",
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
                    //slotDuration: '00:10:00',
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
{{ __('Holiday') }}
@endsection
@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Holiday') }}</h5>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ __('Holiday') }}</li>
@endsection
@section('action-btn')

@if (\Auth::user()->type == 'company')
<a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
    data-url="{{ route('holiday.file.import') }}" data-bs-whatever="{{ __('Import holiday CSV file') }}"><span
        class="text-white"> <i class="ti ti-file-import " data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Import holiday CSV file') }}"></i> </span></a>


<a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
    data-url="{{ route('holiday.create') }}" data-bs-whatever="{{ __('Create New Holiday') }}"> <span
        class="text-white">
        <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
</a>
@endif
@endsection
@section('filter')
@endsection
@section('content')
<div class="row">
    <!-- [ sample-page ] start -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 style="width: 150px;">{{ __('Calendar') }}</h5>
                @if (!empty($settings['is_googleCal_enabled']) && $settings['is_googleCal_enabled'] == 'on')
                <div class="form-group">
                    <label for=""></label>
                    <select class="form-control" name="calender_type" id="calender_type"
                        style="float: right;width: 160px;margin-top: -30px;" onchange="get_data()">
                        <option value="google_calender">{{ __('Google Calendar') }}</option>
                        <option value="local_calender" selected="true">{{ __('Local Calendar') }}</option>
                    </select>
                    <input type="hidden" id="path_admin" value="{{ url('/') }}">
                </div>
                @endif
            </div>
            <div class="card-body">
                <div id='calendar' class='calendar local_calender'></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-4">{{ __('Next Holidays') }}</h4>
                <ul class="event-cards list-group list-group-flush mt-3 w-100">

                    @foreach ($holidays_current_month as $holiday)
                    <li class="list-group-item card mb-3">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-arrow-ramp-right"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="m-0">{{ $holiday->occasion }}</h6>
                                        <small class="text-muted">{{ $holiday->date }}</small>

                                    </div>

                                </div>

                            </div>
                            @if (Auth::user()->type == 'super admin' || Auth::user()->type == 'company')
                            <div class="text-end holiday-dlt">
                                <div class="action-btn bg-danger ms-2 text-">
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['holiday.destroy', $holiday->id]])
                                    !!}
                                    <a href="#!" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                        <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Delete') }}"></i>
                                    </a>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                            @endif
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