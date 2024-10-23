@extends('layouts.admin')
@php
$profile=\App\Models\Utility::get_file('uploads/avatar/');
// $profile=asset(Storage::url('uploads/avatar'));
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


    $(document).ready(function() {
        var filename = $('#filename').val();
        $('.pc-dt-export').DataTable({
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                title: filename
            }, {
                extend: 'csvHtml5',
                title: filename
            }, {
                extend: 'pdfHtml5',
                title: filename
            }, ],

        });
    });
</script>
@endpush
@section('page-title')
{{__('Task Report')}}
@endsection
@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Task Report')}}</h5>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item active" aria-current="page">{{__('Task Report')}}</li>
@endsection
@section('action-btn')


<a href="{{ route('task_report.export') }}" data-bs-toggle="tooltip" title="{{ __('Export') }}" class="btn btn-sm btn-primary">
    <i class="ti ti-file-export"></i>
</a>


<a href="#" onclick="saveAsPDF();" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="{{__('Download')}}" id="download-buttons">
    <i class="ti ti-download"></i>
</a>
@endsection

@section('content')


<div class="col-12">
    <div class=" {{isset($_GET['start_date'])?'show':''}}">
        <div class="card card-body">
            {{ Form::open(array('route' => array('report.task'),'method'=>'get')) }}
            <div class="row filter-css">
                <div class="col-auto">
                    {{Form::date('start_date',isset($_GET['start_date'])?$_GET['start_date']:'',array('class'=>'form-control'))}}
                </div>
                <div class="col-auto">
                    {{Form::date('end_date',isset($_GET['end_date'])?$_GET['end_date']:'',array('class'=>'form-control'))}}
                </div>
                <div class="col-md-2">
                    {{ Form::select('project', $projectList,isset($_GET['project'])?$_GET['project']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
                </div>
                <div class="col-md-2">
                    {{ Form::select('employee', $employees,isset($_GET['employee'])?$_GET['employee']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
                </div>
                <div class="action-btn bg-info ms-2">
                    <div class="col-auto">
                        <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Apply')}}"><i class="ti ti-search text-white"></i></button>
                    </div>
                </div>
                <div class="action-btn bg-danger ms-2">
                    <div class="col-auto">
                        <a href="{{route('report.task')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}" class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white"></i></a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <div id="printableArea">
        <div class="row">
            <!-- [ sample-page ] start -->
            <div class="col-sm-12 ">
                <div class="row">
                    <div class="col-xxl-6">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ __('Report') }}</h5>
                                <input type="hidden" value="{{$filter['project'].' '.__('Project').' '.'Task Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange'].' '.__('of').' '.$filter['employee']}}" id="filename">
                                <div class="row  mt-4">
                                    <div class="col-md-4 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-success">
                                                <i class="ti ti-heart"></i>
                                            </div>
                                            <div class="ms-2">
                                                <h6 class="text-muted text-sm mb-0"> {{__('Report')}} :</h6>
                                                <p class="mb-0 text-success">{{__('Task Summary')}}</small>

                                            </div>
                                        </div>
                                    </div>
                                    @if($filter['project']!= __('All'))
                                    <div class="col-md-4 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-warning">
                                                <i class="ti ti-triangle-square-circle"></i>
                                            </div>
                                            <div class="ms-2">
                                                <h6 class="text-muted text-sm mb-0">{{__('Project')}} :</h6>
                                                <p class="mb-0 text-warning">{{$filter['project'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($filter['employee']!= __('All'))
                                    <div class="col-md-4 col-sm-6 my-3 my-sm-0">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-thumb-up"></i>
                                            </div>
                                            <div class="ms-2">
                                                <h6 class="text-muted text-sm mb-0">{{__('Employee')}} :</h6>
                                                <p class="mb-0 text-primary">{{$filter['employee'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-md-4 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-danger">
                                                <i class="ti ti-thumb-down"></i>
                                            </div>
                                            <div class="ms-2">
                                                <h6 class="text-muted text-sm mb-0"> {{__('Duration')}} :</h6>
                                                <p class="mb-0 text-danger">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ __('Task Data Progress') }}</h5>
                                <div class="row  mt-4">
                                    <div class="progress">
                                        @foreach($stages as $stage)
                                        <div class="progress-bar" role="progressbar" style="width: {{$stage['percentage']}}%;background-color: {{$stage['color']}}" aria-valuenow="{{$stage['percentage']}}" aria-valuemin="0" aria-valuemax="100"></div>
                                        @endforeach
                                    </div>
                                    @foreach($stages as $stage)
                                    <div class="col-md-3">
                                        <span class="text-lg" style="color: {{$stage['color']}}">●</span>
                                        <p class="text-muted text-sm mb-0">{{$stage['total']}} {{$stage['stage']}} (<a href="#" class="text-sm text-muted">{{$stage['percentage']}}%</a>)</p>

                                    </div>
                                    @endforeach

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
    <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header card-body table-border-style">

                            <!-- <div class="card-body table-border-style"> -->


                            <div class="table-responsive">
                                <table class="table pc-dt-export">
                                    <thead>
                                        <tr>
                                            <th>{{__('Project')}}</th>
                                            <th>{{__('Title')}}</th>
                                            <th>{{__('Assign To')}}</th>
                                            <th>{{__('Start Date')}}</th>
                                            <th>{{__('Due Date')}}</th>
                                            <th>{{__('Status')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($projects as $project)
                                        @foreach($project->tasksFilter() as $task)
                                        <tr>
                                            <td>{{$project->title}}</td>
                                            <td>{{$task->title}}</td>
                                            <td>{{!empty($task->taskUser)?$task->taskUser->name:''}}</td>
                                            <td>{{\Auth::user()->dateFormat($task->start_date)}}</td>
                                            <td>{{\Auth::user()->dateFormat($task->due_date)}}</td>
                                            <td>{{!empty($task->stages)?$task->stages->name:''}}</td>
                                        </tr>
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
    <!-- [ Main Content ] end -->
</div>
</div>

@endsection