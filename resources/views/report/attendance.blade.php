@extends('layouts.admin')
@php
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
    // $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>

        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A2'}
            };
            html2pdf().set(opt).from(element).save();

        }

    </script>
@endpush
@section('page-title')
    {{__('Attendance Report')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Attendance Report')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Attendance Report')}}</li>
@endsection
@section('action-btn')


    <a href="{{route('report.attendance.monthly',[isset($_GET['month'])?$_GET['month']:date('Y-m'),isset($_GET['department'])?$_GET['department']:0])}}" class="btn btn-sm btn-primary btn-icon m-1" >
        <span class="btn-inner--icon"><i class="ti ti-download" data-bs-toggle="tooltip" data-bs-original-title="{{__('Download')}}"></i></span>
    </a>
@endsection

@section('content')


        <div class="col-sm-12">
            <div class=" {{isset($_GET['month'])?'show':''}}" >
                <div class="card card-body">
                    {{ Form::open(array('route' => array('report.attendance'),'method'=>'get')) }}
                    <div class="row filter-css">
                        <div class="col-auto">
                            {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'form-control'))}}
                        </div>
                        <div class="col-md-2">
                            {{ Form::select('department', $department,isset($_GET['department'])?$_GET['department']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
                        </div>
                        <div class="action-btn bg-info ms-2">
                            <div class="col-auto">
                                <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-bs-toggle="tooltip" 
                                title="{{__('Apply')}}"><i class="ti ti-search text-white"></i></button>
                            </div>
                        </div>
                        <div class="action-btn bg-danger ms-2">
                            <div class="col-auto">
                                <a href="{{route('report.attendance')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}" 
                                class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white"></i></a>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
  
    
    <div id="printableArea" >
        <div class="row">
            <!-- [ sample-page ] start -->
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-xxl-8">
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-primary">
                                            <i class="ti ti-bookmarks"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{__('Attendance')}}</p>
                                        <h6 class="mb-1">{{__('Total Present')}} : {{$data['totalPresent']}}</h6>
                                        <h6 class="mb-3">{{__('Total leave')}} : {{$data['totalLeave']}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-info">
                                            <i class="ti ti-click"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{__('Overtime')}}</p>
                                        <h6 class="mb-3">{{__('Total overtime in hours')}} : {{number_format($data['totalOvertime'],2)}}</h6>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-warning">
                                            <i class="ti ti-report-money"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{__('Early Leave')}}</p>
                                        <h6 class="mb-3">{{__('Total early leave in hours')}} : {{number_format($data['totalEarlyLeave'],2)}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-danger">
                                            <i class="ti ti-thumb-down"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{__('Employee Late')}}</p>
                                        <h6 class="mb-3">{{__('Total late in hours')}} : {{number_format($data['totalLate'],2)}}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-4">
                        <div class="card h-80">
                            <div class="card-body">
                                <h5>{{ __('Report') }}</h5>
                                <div class="row mt-4">
                                    <div class="col-md-5 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-heart"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Report')}} :</p>
                                                <p class="mb-0 text-primary">{{__('Attendance Summary')}}</p>
                                            
                                            </div>
                                        </div>
                                    </div>
                                    @if($data['department']!='All')
                                        <div class="col-md-4 col-sm-6 my-3 my-sm-0">
                                            <div class="d-flex align-items-start">
                                                <div class="theme-avtar bg-warning">
                                                    <i class="ti ti-thumb-up"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Department')}} :</p>
                                                    <p class="mb-0 text-warning">{{$data['department'] }}</p>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-5 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-danger">
                                                <i class="ti ti-calendar"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Duration')}} :</p>
                                                <p class="mb-0 text-danger">{{$data['curMonth']}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Attendance Report') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="active">{{__('Name')}}</th>
                                                @foreach($dates as $date)
                                                    <th>{{$date}}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($employeesAttendance as $attendance)
                                                <tr>
                                                    <td>{{$attendance['name']}}</td>
                                                    @foreach($attendance['status'] as $status)
                                                        <td>
                                                            @if($status=='P')
                                                                <i class="badge bg-success">{{__('P')}}</i>
                                                            @elseif($status=='L')
                                                                <i class="badge bg-danger">{{__('L')}}</i>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ sample-page ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

@endsection



