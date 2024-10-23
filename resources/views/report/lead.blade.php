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
        //                 name: '{{__('Lead')}}',
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
        //                 categories:  {!! json_encode($labels) !!},
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
                name: '{{__('Worked hours')}}',
                data:{!! json_encode($data) !!}
            }],
            xaxis: {
                categories: {!! json_encode($labels) !!},
            },
        };
        var chart = new ApexCharts(document.querySelector("#user-chart"), options);
        chart.render();
    })();



        // (function () {
        // var options = {
        //     chart: {
        //         type: 'bar',
        //         height: 140,
        //         zoom: {
        //             enabled: false
        //         },
        //         toolbar: {
        //             show: false,
        //         },
        //     },
        //     dataLabels: {
        //         enabled: false,
        //     },
        //     colors: ["#584ed2"],
        //     plotOptions: {
        //         bar: {
        //             color: '#584ed2',
        //             columnWidth: '20%',
        //         }
        //     },
        //     fill: {
        //         type: 'solid',
        //         opacity: 1,
        //     },
        //     series: [{
        //                 name: '{{__('Worked hours')}}',
        //                 data: {!! json_encode($data) !!}
        //             }],
        //             xaxis: {
        //                 labels: {
        //                     // format: 'MMM',
        //                     style: {
        //                         colors: '#584ed2',
        //                         fontSize: '14px',
        //                         fontFamily: 'sans-serif',
        //                         cssClass: 'apexcharts-xaxis-label',
        //                     },
        //                 },
        //                 axisBorder: {
        //                     show: false
        //                 },
        //                 axisTicks: {
        //                     show: true,
        //                     borderType: 'solid',
        //                     color: '#584ed2',
        //                     height: 6,
        //                     offsetX: 0,
        //                     offsetY: 0
        //                 },
        //                 type: 'datetime',
        //                 categories:  {!! json_encode($labels) !!},
        //             },
        //             yaxis: {
        //                 labels: {
        //                     style: {
        //                         color: '#584ed2',
        //                         fontSize: '12px',
        //                         fontFamily: 'sans-serif',
        //                     },
        //                 },
        //                 axisBorder: {
        //                     show: false
        //                 },
        //                 axisTicks: {
        //                     show: true,
        //                     borderType: 'solid',
        //                     color: '#584ed2',
        //                     height: 6,
        //                     offsetX: 0,
        //                     offsetY: 0
        //                 }
        //             },
        //             grid: {
        //                 borderColor: '#fff',
        //                 padding: {
        //                     bottom: 0,
        //                     left: 10,
        //                 }
        //             },
        //             tooltip: {
        //                 fixed: {
        //                     enabled: false
        //                 },
        //                 x: {
        //                     show: false
        //                 },
        //                 y: {
        //                     title: {
        //                         formatter: function (seriesName) {
        //                             return 'Total Earnings'
        //                         }
        //                     }
        //                 },
        //                 marker: {
        //                     show: false
        //                 }
        //             }
        //         };
        //         var chart = new ApexCharts(document.querySelector("#user-chart"), options);
        //         chart.render();
        //     })();


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
    {{__('Lead Report')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Lead Report')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Lead Report')}}</li>
@endsection
@section('action-btn')
    
<a href="{{ route('lead_report.export') }}" data-bs-toggle="tooltip" title="{{ __('Export') }}" class="btn btn-sm btn-primary">
    <i class="ti ti-file-export"></i>
</a>

    <a href="#" onclick="saveAsPDF();" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="{{__('Download')}}" id="download-buttons">
        <i class="ti ti-download"></i>
    </a>
@endsection

@section('content')

        <div class="col-sm-12">
            <div class=" {{isset($_GET['start_month'])?'show':''}}" >
                <div class="card card-body">
                    {{ Form::open(array('route' => array('report.lead'),'method'=>'get')) }}
                    <div class="row filter-css">
                        <div class="col-auto">
                            {{Form::month('start_month',isset($_GET['start_month'])?$_GET['start_month']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="col-auto">
                            {{Form::month('end_month',isset($_GET['end_month'])?$_GET['end_month']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="col-md-3 my-1">
                            {{ Form::select('users', $users, isset($_GET['users']) ? $_GET['users'] : '', ['class' => 'form-control', 'data-toggle="select"']) }}
                        </div>
                            
  
                        <div class="action-btn bg-info ms-2">
                            <div class="col-auto">
                                <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Apply')}}"><i class="ti ti-search text-white"></i></button>
                            </div>
                        </div>
                        <div class="action-btn bg-danger ms-2">
                            <div class="col-auto">
                                <a href="{{route('report.lead')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}" class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white"></i></a>
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
                            <input type="hidden" value="{{'Lead Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange']}}" id="filname">
                            <div class="row">
                                <div class="col">
                                    {{__('Report')}} : <h6>{{__('Lead Summary')}}</h6>
                                </div>
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
                    <div class="card-body min-height">
                        <div class="chart">
                            <div class="chartjs-size-monitor">
                                <div class="chartjs-size-monitor-expand">
                                    <div class=""></div>
                                </div>
                                <div class="chartjs-size-monitor-shrink">
                                    <div class=""></div>
                                </div>
                            </div>
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
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Date')}}</th>
                                    <th>{{__('Subject')}}</th>
                                    <th>{{__('Stage')}}</th>
                                    <th>{{__('Users')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leads as $lead)
                                    <tr>
                                        <td>{{ $lead->name }}</td>
                                        <td>{{ \Auth::user()->dateFormat($lead->date) }}</td>
                                        <td>{{ $lead->subject }}</td>
                                        <td>{{ !empty($lead->stage)?$lead->stage->name:'' }}</td>
                                        <td>
                                            @foreach($lead->users as $user)
                                                <a href="#" class="btn btn-sm mr-1 p-0 rounded-circle">
                                                    <img alt="image" data-bs-toggle="tooltip" title="{{$user->name}}" @if($user->avatar) src="{{asset('storage/uploads/avatar/'.$user->avatar)}}" @else avatar="{{$user->name}}" @endif class="rounded-circle profile-widget-picture" width="25">
                                                </a>
                                            @endforeach
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



