@extends('layouts.admin')
@php
$profile = asset(Storage::url('uploads/avatar'));
@endphp
@push('css-page')
<link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.min.css') }}">
@endpush
@push('script-page')
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<!-- <script src="{{ asset('js/datatable/dataTables.buttons.min.js') }}"></script> -->
<script src="{{ asset('js/datatable/jszip.min.js') }}"></script>
<script src="{{ asset('js/datatable/pdfmake.min.js') }}"></script>
<script src="{{ asset('js/datatable/vfs_fonts.js') }}"></script>
<!-- <script src="{{ asset('js/datatable/buttons.html5.min.js') }}"></script> -->

<script>
    var filename = $('#filename').val();

    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: filename,
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A2'
            }
        };
        html2pdf().set(opt).from(element).save();

    }


    // $(document).ready(function() {
    //     var filename = $('#filename').val();
    //     $('#reportTable').DataTable({
    //         dom: 'Bfrtip',
    //         buttons: [{
    //             extend: 'excelHtml5',
    //             title: filename
    //         }, {
    //             extend: 'csvHtml5',
    //             title: filename
    //         }, {
    //             extend: 'pdfHtml5',
    //             title: filename
    //         }, ],
    //         language: dataTableLang
    //     });
    // });
</script>
<script>
    $('input[name="type"]:radio').on('change', function(e) {
        var type = $(this).val();
        if (type == 'monthly') {
            $('.month').addClass('d-block');
            $('.month').removeClass('d-none');
            $('.year').addClass('d-none');
            $('.year').removeClass('d-block');
        } else {
            $('.year').addClass('d-block');
            $('.year').removeClass('d-none');
            $('.month').addClass('d-none');
            $('.month').removeClass('d-block');
        }
    });

    $('input[name="type"]:radio:checked').trigger('change');
</script>
@endpush
@section('page-title')
{{ __('Leave Report') }}
@endsection
@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Leave Report') }}</h5>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ __('Leave Report') }}</li>
@endsection
@section('action-btn')

<a href="{{ route('leave_report.export') }}" data-bs-toggle="tooltip" title="{{ __('Export') }}" class="btn btn-sm btn-primary">
    <i class="ti ti-file-export"></i>
</a>

<a href="#" onclick="saveAsPDF();" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="{{ __('Download') }}" id="download-buttons">
    <i class="ti ti-download"></i>
</a>
@endsection

@section('content')

<!-- [ Main Content ] start -->

<div class="col-12">
    <div class=" {{ isset($_GET['type']) ? 'show' : '' }}">
        <div class="card card-body">
            {{ Form::open(['route' => ['report.leave'], 'method' => 'get']) }}
            <div class="row filter-css">
                <div class="col-auto">
                    <div class="row">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons" aria-label="Basic radio toggle button group">
                            <label class="btn btn-secondary month-label {{ isset($_GET['type']) && $_GET['type'] == 'monthly' ? 'active' : '' }}">
                                <input type="radio" name="type" value="monthly" class="btn-check monthly" {{ isset($_GET['type']) && $_GET['type'] == 'monthly' ? 'checked' : '' }}>
                                {{ __('Monthly') }}
                            </label>

                            <label class="btn btn-secondary year-label {{ isset($_GET['type']) && $_GET['type'] == 'yearly' ? 'active' : '' }}">
                                <input type="radio" name="type" value="yearly" class="btn-check yearly" {{ isset($_GET['type']) && $_GET['type'] == 'yearly' ? 'checked' : '' }}>
                                {{ __('Yearly') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-auto month">
                    {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'form-control']) }}
                </div>
                <div class="col-md-2 year d-none">
                    <select class="form-control" data-toggle="select" id="year" name="year" tabindex="-1" aria-hidden="true">
                        @for ($filterYear['starting_year']; $filterYear['starting_year'] <= $filterYear['ending_year']; $filterYear['starting_year']++) <option {{ isset($_GET['year']) && $_GET['year'] == $filterYear['starting_year'] ? 'selected' : '' }} {{ !isset($_GET['year']) && date('Y') == $filterYear['starting_year'] ? 'selected' : '' }} value="{{ $filterYear['starting_year'] }}">{{ $filterYear['starting_year'] }}
                            </option>
                            @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    {{ Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control','data-toggle' => 'select']) }}
                </div>
                <div class="action-btn bg-info ms-2">
                    <div class="col-auto">
                        <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Apply') }}"><i class="ti ti-search text-white"></i></button>
                    </div>
                </div>
                <div class="action-btn bg-danger ms-2">
                    <div class="col-auto">
                        <a href="{{ route('report.leave') }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}" class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white"></i></a>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>


<div id="printableArea">
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xxl-6">
                    <div class="row">
                        <div class="col-lg-4 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="theme-avtar bg-primary">
                                        <i class="ti ti-user-plus"></i>
                                    </div>
                                    <h6 class="mb-3 mt-4">{{ __('Approves Leaves') }}</h6>
                                    <h3 class="mb-0">{{ $filter['totalApproved'] }} </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-click"></i>
                                    </div>
                                    <h6 class="mb-3 mt-4">{{ __('Rejected Leaves') }}</h6>
                                    <h3 class="mb-0">{{ $filter['totalReject'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="theme-avtar bg-warning">
                                        <i class="ti ti-report-money"></i>
                                    </div>
                                    <h6 class="mb-3 mt-4">{{ __('Pending Leaves') }}</h6>
                                    <h3 class="mb-0">{{ $filter['totalPending'] }} </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6">
                    <div class="card">
                        <div class="card-body">
                            <input type="hidden" value="{{ $filterYear['dateYearRange'] .' ' .$filterYear['type'] .' ' .__('Leave Report of') .' ' .$filterYear['department'] .' ' .'Department' }}" id="filename">
                            <h5>{{ __('Leave Report') }}</h5>
                            <div class="row  mt-4">
                                <div class="col-md-4 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar bg-success">
                                            <i class="ti ti-heart"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">{{ __('Report') }} :</p>
                                            <p class="mb-0 text-success">{{ __('Leave Summary') }}</p>

                                        </div>
                                    </div>
                                </div>
                                @if ($filterYear['department'] != 'All')
                                <div class="col-md-4 col-sm-6 my-3 my-sm-0">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar bg-primary">
                                            <i class="ti ti-thumb-up"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">{{ __('Department') }} :</p>
                                            <p class="mb-0 text-primary">{{ $filterYear['department'] }}</p>

                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-4 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar bg-danger">
                                            <i class="ti ti-calendar"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0"> {{ __('Duration') }} :</p>
                                            <p class="mb-0 text-danger">{{ $filterYear['dateYearRange'] }}</p>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
         
            </div>
        </div>
        <!-- [ sample-page ] end -->
    </div>
    <!-- [ Main Content ] end -->
</div>
<div class="col-xl-12">
                    <div class="card">
                        <div class="card-header card-body table-border-style">
                            <!-- <h5> -->
                            <!-- <div class="card-body table-border-style"> -->
                            <!-- <button class="btn btn-light-primary btn-sm csv" style="margin-bottom: 10px;">{{ __('Export CSV') }}</button> -->
                            <!-- <button
                                            class="btn btn-light-primary btn-sm xlsx">{{ __('Export Excel') }}</button>
                                        <button class="btn btn-light-primary btn-sm pdf">{{ __('Export PDF') }}</button> -->
                            <!-- </h5> -->
                            <div class="table-responsive">
                                <table class="table pc-dt-export">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee ID') }}</th>
                                            <th>{{ __('Employee') }}</th>
                                            <th>{{ __('Approved Leaves') }}</th>
                                            <th>{{ __('Rejected Leaves') }}</th>
                                            <th>{{ __('Pending Leaves') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($leaves as $leave)
                                        <tr>
                                            <td>{{ \Auth::user()->employeeIdFormat($leave['employee_id']) }}</td>
                                            <td>{{ $leave['employee'] }}</td>

                                            <td>
                                                <a href="#!" data-url="{{ route('report.employee.leave', [$leave['id'],'Approve',isset($_GET['type']) ? $_GET['type'] : 'no',isset($_GET['month']) ? $_GET['month'] : date('Y-m'),isset($_GET['year']) ? $_GET['year'] : date('Y')]) }}" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="{{ __('Approved Leave Detail') }}" data-title="{{ __('Approved Leave Detail') }}" data-size='lg' data-toggle="tooltip" data-original-title="{{ __('View') }}">
                                                    <span class="badge bg-success p-2 px-3 rounded">{{ $leave['approved'] }}</span>
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="#!" data-url="{{ route('report.employee.leave', [$leave['id'],'Reject',isset($_GET['type']) ? $_GET['type'] : 'no',isset($_GET['month']) ? $_GET['month'] : date('Y-m'),isset($_GET['year']) ? $_GET['year'] : date('Y')]) }}" class="table-action table-action-delete" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="{{ __('Rejected Leave Detail') }}" data-size='lg' data-toggle="tooltip" data-original-title="{{ __('View') }}">
                                                    <span class="badge bg-danger p-2 px-3 rounded">{{ $leave['reject'] }}</span>
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="#!" data-url="{{ route('report.employee.leave', [$leave['id'],'Pending',isset($_GET['type']) ? $_GET['type'] : 'no',isset($_GET['month']) ? $_GET['month'] : date('Y-m'),isset($_GET['year']) ? $_GET['year'] : date('Y')]) }}" class="table-action table-action-delete" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="{{ __('ending Leave Detail') }}" data-size='lg' data-toggle="tooltip" data-original-title="{{ __('View') }}">
                                                    <span class="badge bg-warning p-2 px-3 rounded">{{ $leave['pending'] }}</span>
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
</div>
@endsection