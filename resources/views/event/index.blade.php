@extends('layouts.admin')
@push('script-page')
    <script>
        $(document).on('change', '.department', function() {
            var department_id = $(this).val();
            getEmployee(department_id);
        });

        function getEmployee(department_id) {

            $.ajax({
                url: '{{ route('event.employee') }}',
                type: 'POST',
                data: {
                    "department": department_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {


                    $('.employee').remove();
                    var emp_selct = `<select class="employee form-control multi-select" id="choices-multiple1" multiple="" required="required" name="employee[]">
                    </select>`;
                    $('.emp_div').html(emp_selct);

                    $('.employee').append('<option value="0"> {{ __('All') }} </option>');
                    $.each(data, function(key, value) {
                        $('.employee').append('<option value="' + key + '">' + value + '</option>');
                    });
                    new Choices('#choices-multiple1', {
                        removeItemButton: true,
                    });

                }
            });
        }
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            get_data();
        });

        function get_data() {
            var calender_type = $('#calender_type :selected').val();
            $('#calendar').removeClass('local_calender');
            $('#calendar').removeClass('google_calender');
            $('#calendar').addClass(calender_type);
            if(calender_type == undefined){
                    calender_type = 'local_calender';
                }
            $.ajax({
                // url: $("#path_admin").val() + "/event/get_event_data",
                url: "{{ url('/') }}" + "/event/get_event_data",
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
@section('page-title')
    {{ __('Event') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{ __('Event') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Event') }}</li>
@endsection
@section('action-btn')
    @if (\Auth::user()->type == 'company') 
        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
            data-url="{{ route('event.create') }}" data-size="lg" data-bs-whatever="{{ __('Create New Event') }}"> <span
                class="text-white"><i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Create') }}"></i></span></a>
    @endif
@endsection
@section('filter')
@endsection
@section('content')
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-lg-8">
            <div class="card">
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
            </div>

            <!-- [ sample-page ] end -->
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-4">{{ __('Next events') }}</h4>
                    <ul class="event-cards list-group list-group-flush mt-3 w-100">
                        @foreach ($events_current_month as $event)
                            <li class="list-group-item card mb-3">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class=" align-items-center">
                                            <div class="ms-3">
                                                <h6 class="m-0">{{ $event->name }}</h6>
                                                <small
                                                    class="text-muted">{{ \Auth::user()->dateFormat($event->start_date) . ' ' . \Auth::user()->timeFormat($event->start_time) . ' ' . __('To') . ' ' . \Auth::user()->dateFormat($event->end_date) . ' ' . \Auth::user()->timeFormat($event->end_time) }}</small>
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
