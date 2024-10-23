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


        var WorkedHoursChart = (function () {
            var $chart = $('#apex-engagement');

            function init($this) {

                // Options
                var options = {
                    chart: {
                        width: '100%',
                        type: 'bar',
                        zoom: {
                            enabled: false
                        },
                        toolbar: {
                            show: false
                        },
                        shadow: {
                            enabled: false,
                        },
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '30%',
                            endingShape: 'rounded'
                        },
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    series: [{
                        name: '{{__('Earning')}}',
                        data:{!! json_encode($data) !!}
                    }],
                    xaxis: {
                        labels: {
                            // format: 'MMM',
                            style: {
                                colors: PurposeStyle.colors.gray[600],
                                fontSize: '14px',
                                fontFamily: PurposeStyle.fonts.base,
                                cssClass: 'apexcharts-xaxis-label',
                            },
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: true,
                            borderType: 'solid',
                            color: PurposeStyle.colors.gray[300],
                            height: 6,
                            offsetX: 0,
                            offsetY: 0
                        },
                        type: 'datetime',
                        categories:  {!! json_encode($labels) !!},
                    },
                    yaxis: {
                        labels: {
                            style: {
                                color: PurposeStyle.colors.gray[600],
                                fontSize: '12px',
                                fontFamily: PurposeStyle.fonts.base,
                            },
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: true,
                            borderType: 'solid',
                            color: PurposeStyle.colors.gray[300],
                            height: 6,
                            offsetX: 0,
                            offsetY: 0
                        }
                    },
                    fill: {
                        type: 'solid'
                    },
                    markers: {
                        size: 4,
                        opacity: 0.7,
                        strokeColor: "#fff",
                        strokeWidth: 3,
                        hover: {
                            size: 7,
                        }
                    },
                    grid: {
                        borderColor: PurposeStyle.colors.gray[300],
                        strokeDashArray: 5,
                    },
                    dataLabels: {
                        enabled: false
                    }
                }

                // Get data from data attributes
                var dataset = $this.data().dataset,
                    labels = $this.data().labels,
                    color = $this.data().color,
                    height = $this.data().height,
                    type = $this.data().type;

                // Inject synamic properties
                options.colors = [
                    PurposeStyle.colors.theme[color]
                ];

                options.markers.colors = [
                    PurposeStyle.colors.theme[color]
                ];

                options.chart.height = height ? height : 350;

                // Init chart
                var chart = new ApexCharts($this[0], options);

                // Draw chart
                setTimeout(function () {
                    chart.render();
                }, 300);

            }

            // Events

            if ($chart.length) {
                $chart.each(function () {
                    init($(this));
                });
            }

        })();

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
    {{__('Finance Report')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Finance Report')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Finance Report')}}</li>
@endsection
@section('action-btn')

<a href="{{route('finance_report.export')}}" data-bs-toggle="tooltip" title="{{ __('Export') }}" class="btn btn-sm btn-primary">
    <i class="ti ti-file-export"></i>
</a>
    <a href="#" onclick="saveAsPDF();" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" 
    title="{{__('Download')}}" id="download-buttons">
        <i class="ti ti-download"></i>
    </a>
@endsection

@section('content')

     <!-- [ Main Content ] start -->
 
        <div class="col-12">
            <div class=" {{isset($_GET['start_month'])?'show':''}}">
                <div class="card card-body">
                    {{ Form::open(array('route' => array('report.finance'),'method'=>'get')) }}
                    <div class="row filter-css">
                        <div class="col-auto">
                            {{Form::month('start_month',isset($_GET['start_month'])?$_GET['start_month']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="col-auto">
                            {{Form::month('end_month',isset($_GET['end_month'])?$_GET['end_month']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="col-md-2">
                            {{ Form::select('project', $projectList,isset($_GET['project'])?$_GET['project']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
                        </div>
                        <div class="col-md-2">
                            {{ Form::select('client', $clients,isset($_GET['client'])?$_GET['client']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
                        </div>
                        <div class="action-btn bg-info ms-2">
                            <div class="col-auto">
                                <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-bs-toggle="tooltip" 
                                title="{{__('Apply')}}"><i class="ti ti-search text-white"></i></button>
                            </div>
                        </div>
                        <div class="action-btn bg-danger ms-2">
                            <div class="col-auto">
                                <a href="{{route('report.finance')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}" class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white"></i></a>
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
                                <div class="card ">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-primary">
                                            <i class="ti ti-file-invoice"></i>
                                        </div>
                                    
                                        <h6 class="mb-3 mt-4">{{__('Total Invoice')}}</h6>
                                        <h3 class="mb-0">{{\Auth::user()->priceFormat($invoicesTotal)}} </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-info">
                                            <i class="ti ti-click"></i>
                                        </div>
                                        
                                        <h6 class="mb-3 mt-4">{{__('Due Invoice')}}</h6>
                                        <h3 class="mb-0">{{\Auth::user()->priceFormat($invoicesDue)}} </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-warning">
                                            <i class="ti ti-receipt-tax"></i>
                                        </div>
                                        
                                        <h6 class="mb-3 mt-4">{{__('Total Tax')}}</h6>
                                        <h3 class="mb-0">{{\Auth::user()->priceFormat($invoicesTax)}} </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-danger">
                                            <i class="ti ti-discount"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Discount')}}</h6>
                                        <h3 class="mb-0">{{\Auth::user()->priceFormat($invoicesDiscount)}}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-4">
                        <div class="card h-75">
                            <div class="card-body">
                                <input type="hidden" value="{{$filter['project'].' '.__('Project').' '.'Finance Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange'].' '.__('of').' '.$filter['client']}}" id="filename">
                                <h5>{{ __('Finance Report') }}</h5>
                                <div class="row  mt-4">
                                    <div class="col-md-4 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-success">
                                                <i class="ti ti-heart"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Report')}} :</p>
                                                <p class="mb-0 text-success">{{__('Finance Summary')}}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @if($filter['project']!= __('All'))
                                        <div class="col-md-3 col-sm-6 my-3 my-sm-0">
                                            <div class="d-flex align-items-start">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-thumb-up"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Project')}} :</p>
                                                    <p class="mb-0 text-primary">{{$filter['project'] }}</p>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($filter['client']!= __('All'))
                                        <div class="col-md-3 col-sm-6">
                                            <div class="d-flex align-items-start">
                                                <div class="theme-avtar bg-warning">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Client')}} :</p>
                                                    <p class="mb-0 text-warning">{{$filter['client'] }}</p>
                                                
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-6 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-danger">
                                                <i class="ti ti-calendar"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Duration')}} :</p>
                                                <p class="mb-0 text-danger">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</p>
                                                
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
    </div>
    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header card-body table-border-style">
                                <!-- <h5>
                                <div class="card-body table-border-style">
                                   
                                </h5>    -->
                                    <div class="table-responsive">
                                        <table class="table pc-dt-export">
                                            <thead>
                                                <tr>
                                                    <th>{{__('Project/Product')}}</th>
                                                    <th>{{__('Invoice')}}</th>
                                                    <th>{{__('Amount')}}</th>
                                                    <th>{{__('Issue Date')}}</th>
                                                    <th>{{__('Status')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($invoices as $invoice)
                                                    <tr>
                                                        <td>
                                                            @if($invoice->project==0)
                                                                {{__('Product')}}
                                                            @else
                                                                {{!empty($invoice->projects)?$invoice->projects->title:''}}
                                                            @endif
                            
                                                        </td>
                                                        <td>{{\Auth::user()->invoiceNumberFormat($invoice->invoice_id)}}</td>
                                                        <td>{{\Auth::user()->priceFormat($invoice->totalAmt)}}</td>
                                                        <td>{{\Auth::user()->dateFormat($invoice->issue_date)}}</td>
                                                        <td>
                                                            @if($invoice->status == 0)
                                                                <span class="badge rounded-pill fix_badge bg-primary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 1)
                                                                <span class="badge rounded-pill fix_badge bg-warning">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 2)
                                                                <span class="badge rounded-pill fix_badge bg-danger">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 3)
                                                                <span class="badge rounded-pill fix_badge bg-info">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 4)
                                                                <span class="badge rounded-pill fix_badge bg-success">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @endif
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



