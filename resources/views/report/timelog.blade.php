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

(function () {
        var options = {
            chart: {
                height: 150,
                type: 'bar',
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    columnWidth: '30%',
                    borderRadius: 10,
                    dataLabels: {
                        position: 'top',
                    },
                }
            },
            colors: ["#51459d"],
            dataLabels: {
                enabled: false,
            },
            stroke: {
                show: true,
                width: 1,
                colors: ['#fff']
            },
            grid: {
                strokeDashArray: 4,
            },
            series: [{
                data: {!! json_encode($data) !!}
            }],
            xaxis: {
                categories: {!! json_encode($labels) !!},
                // categories: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            },
        };
        var chart = new ApexCharts(document.querySelector("#user-chart"), options);
        chart.render();
    })();

        // var WorkedHoursChart = (function () {
        //     var $chart = $('#apex-engagement');

        //     function init($this) {

        //         // Options
        //         var options = {
        //             chart: {
        //                 width: '100%',
        //                 type: 'bar',
        //                 zoom: {
        //                     enabled: false
        //                 },
        //                 toolbar: {
        //                     show: false
        //                 },
        //                 shadow: {
        //                     enabled: false,
        //                 },
        //             },
        //             plotOptions: {
        //                 bar: {
        //                     horizontal: false,
        //                     columnWidth: '30%',
        //                     endingShape: 'rounded'
        //                 },
        //             },
        //             stroke: {
        //                 show: true,
        //                 width: 2,
        //                 colors: ['transparent']
        //             },
        //             series: [{
        //                 name: '{{__('Worked hours')}}',
        //                 data: {!! json_encode($data) !!}
        //             }],
        //             xaxis: {
        //                 labels: {
        //                     // format: 'MMM',
        //                     style: {
        //                         colors: PurposeStyle.colors.gray[600],
        //                         fontSize: '14px',
        //                         fontFamily: PurposeStyle.fonts.base,
        //                         cssClass: 'apexcharts-xaxis-label',
        //                     },
        //                 },
        //                 axisBorder: {
        //                     show: false
        //                 },
        //                 axisTicks: {
        //                     show: true,
        //                     borderType: 'solid',
        //                     color: PurposeStyle.colors.gray[300],
        //                     height: 6,
        //                     offsetX: 0,
        //                     offsetY: 0
        //                 },
        //                 type: 'datetime',
        //                 categories: {!! json_encode($labels) !!},
        //             },
        //             yaxis: {
        //                 labels: {
        //                     style: {
        //                         color: PurposeStyle.colors.gray[600],
        //                         fontSize: '12px',
        //                         fontFamily: PurposeStyle.fonts.base,
        //                     },
        //                 },
        //                 axisBorder: {
        //                     show: false
        //                 },
        //                 axisTicks: {
        //                     show: true,
        //                     borderType: 'solid',
        //                     color: PurposeStyle.colors.gray[300],
        //                     height: 6,
        //                     offsetX: 0,
        //                     offsetY: 0
        //                 }
        //             },
        //             fill: {
        //                 type: 'solid'
        //             },
        //             markers: {
        //                 size: 4,
        //                 opacity: 0.7,
        //                 strokeColor: "#fff",
        //                 strokeWidth: 3,
        //                 hover: {
        //                     size: 7,
        //                 }
        //             },
        //             grid: {
        //                 borderColor: PurposeStyle.colors.gray[300],
        //                 strokeDashArray: 5,
        //             },
        //             dataLabels: {
        //                 enabled: false
        //             }
        //         }

        //         // Get data from data attributes
        //         var dataset = $this.data().dataset,
        //             labels = $this.data().labels,
        //             color = $this.data().color,
        //             height = $this.data().height,
        //             type = $this.data().type;

        //         // Inject synamic properties
        //         options.colors = [
        //             PurposeStyle.colors.theme[color]
        //         ];

        //         options.markers.colors = [
        //             PurposeStyle.colors.theme[color]
        //         ];

        //         options.chart.height = height ? height : 350;

        //         // Init chart
        //         var chart = new ApexCharts($this[0], options);

        //         // Draw chart
        //         setTimeout(function () {
        //             chart.render();
        //         }, 300);

        //     }

        //     // Events

        //     if ($chart.length) {
        //         $chart.each(function () {
        //             init($(this));
        //         });
        //     }

        // })();

     
    </script>
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


        // $(document).ready(function () {
        //     var filename = $('#filename').val();
        //     $('#reportTable').DataTable({
        //         dom: 'Bfrtip',
        //         buttons: [
        //             {
        //                 extend: 'excelHtml5',
        //                 title: filename
        //             }, {
        //                 extend: 'csvHtml5',
        //                 title: filename
        //             }, {
        //                 extend: 'pdfHtml5',
        //                 title: filename
        //             },
        //         ],
        //         language: dataTabelLang
        //     });
        // });


    </script>
@endpush
@section('page-title')
    {{__('Timelog Report')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Timelog Report')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Timelog Report')}}</li>
@endsection
@section('action-btn')
<a href="{{ route('timelog_report.export') }}" data-bs-toggle="tooltip" title="{{ __('Export') }}" class="btn btn-sm btn-primary">
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
                    {{ Form::open(array('route' => array('report.timelog'),'method'=>'get')) }}
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
                            {{ Form::select('task', $tasks,isset($_GET['task'])?$_GET['task']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
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
                                <a href="{{route('report.timelog')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}" class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white"></i></a>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
   

    <div id="printableArea">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-wrap">
                        <div class="card-body">
                            <input type="hidden" value="{{$filter['project'].' '.__('Project').' '.' '.'Timesheet Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange'].' '.__('of').' '.$filter['employee']}}" id="filename">
                            <div class="row">
                                <div class="col">
                                    {{__('Report')}} : <h6>{{__('Timelog Summary')}}</h6>
                                </div>
                                @if($filter['project']!= __('All'))
                                    <div class="col">
                                        {{__('Project')}} : <h6>{{$filter['project'] }}</h6>
                                    </div>
                                @endif
                                @if($filter['task']!= __('All'))
                                    <div class="col">
                                        {{__('Task')}} : <h6>{{$filter['task'] }}</h6>
                                    </div>
                                @endif
                                @if($filter['employee']!= __('All'))
                                    <div class="col">
                                        {{__('Employee')}} : <h6>{{$filter['employee'] }}</h6>
                                    </div>
                                @endif
                                <div class="col">
                                    {{__('Duration')}} : <h6>{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="scrollbar-inner">
                            <div id="user-chart" data-color="primary" data-height="280"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <!-- <h5> -->
                <!-- <div class="card-body table-border-style"> -->
                   
                <!-- </h5>    -->
                    <div class="table-responsive">
                        <table class="table pc-dt-export">
                            <thead>
                                <tr>
                                    <th>{{__('Task')}}</th>
                                    <th>{{__('Start Time')}}</th>
                                    <th>{{__('End Time')}}</th>
                                    <th>{{__('Total Hours')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timesheets as $timesheet)
                                    @php
                                        $t1 = strtotime($timesheet->end_date.' '.$timesheet->end_time);
                                        $t2 = strtotime( $timesheet->start_date.' '.$timesheet->start_time );
                                        $diff = $t1 - $t2;
                                        $hours = $diff / ( 60 * 60 );
                                    @endphp
                                    <tr>
                                        <td>{{!empty($timesheet->tasks)?$timesheet->tasks->title:'--'}}</td>
                                        <td>{{$timesheet->start_date.' '.$timesheet->start_time}}</td>
                                        <td>{{$timesheet->end_date.' '.$timesheet->end_time}}</td>
                                        <td>{{number_format($hours,2)}}</td>
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



