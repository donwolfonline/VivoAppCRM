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
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js')}}"></script>
    <script>
       
    (function () {
        var options = {
            chart: {
                height: 150,
                type: 'area',
                toolbar: {
                    show: false,
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 2,
                curve: 'smooth'
            },
            series: [{
                name: "{{__('Income')}}",
                data: {!! json_encode($incomeData) !!}
            }, {
                name: "{{__('Expense')}}",
                data: {!! json_encode($expenseData) !!}
            }],
            xaxis: {
                categories: {!! json_encode($labels) !!},
            },
            colors: ['#ffa21d', '#FF3A6E'],

            grid: {
                strokeDashArray: 4,
            },
            legend: {
                show: false,
            },
            // markers: {
            //     size: 4,
            //     colors: ['#ffa21d', '#FF3A6E'],
            //     opacity: 0.9,
            //     strokeWidth: 2,
            //     hover: {
            //         size: 7,
            //     }
            // },
            yaxis: {
                tickAmount: 3,
                
            }
        };
        var chart = new ApexCharts(document.querySelector("#traffic-chart"), options);
        chart.render();
    })();
    (function () {
        var options = {
            chart: {
                type: 'bar',
                height: 140,
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false,
                },
            },
            dataLabels: {
                enabled: false,
            },
            colors: ["#fff"],
            plotOptions: {
                bar: {
                    color: '#fff',
                    columnWidth: '20%',
                }
            },
            fill: {
                type: 'solid',
                opacity: 1,
            },
            series: [{
                data: [25, 66, 41, 89, 63, 25, 44, 12, 36, 9, 25, 44, 12]
            }],
            xaxis: {
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false,
                },
                crosshairs: {
                    width: 0
                },
                labels: {
                    show: false,
                },
            },
            yaxis: {
                tickAmount: 3,
                labels: {
                    style: {
                        colors: "#fff"
                    }
                },
            },
            grid: {
                borderColor: '#ffffff00',
                padding: {
                    bottom: 0,
                    left: 10,
                }
            },
            tooltip: {
                fixed: {
                    enabled: false
                },
                x: {
                    show: false
                },
                y: {
                    title: {
                        formatter: function (seriesName) {
                            return 'Total Earnings'
                        }
                    }
                },
                marker: {
                    show: false
                }
            }
        };
       // var chart = new ApexCharts(document.querySelector("#user-chart"), options);
        //chart.render();
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
        //         language: dataTableLang
        //     });
        // });


    </script>
@endpush
@section('page-title')
    {{__('Income Vs Expense Report')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Income Vs Expense Report')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a>
    <li class="breadcrumb-item active" aria-current="page">{{__('Income Vs Expense Report')}}</li>
@endsection
@section('action-btn')

<a href="#" onclick="saveAsPDF();" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="{{__('Download')}}" id="download-buttons">
    <i class="ti ti-download"></i>
</a>
@endsection

@section('content')

<div class="col-sm-12">
    <div class=" {{isset($_GET['start_month'])?'show':''}}" >
        <div class="card card-body">
            {{ Form::open(array('route' => array('report.income.expense'),'method'=>'get')) }}
            <div class="row filter-css">
                <div class="col-auto">
                    {{Form::month('start_month',isset($_GET['start_month'])?$_GET['start_month']:'',array('class'=>'form-control'))}}
                </div>
                <div class="col-auto">
                    {{Form::month('end_month',isset($_GET['end_month'])?$_GET['end_month']:'',array('class'=>'form-control'))}}
                </div>
                <div class="col-auto action-btn bg-info ms-2">
                    <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Apply')}}"><i class="ti ti-search text-white"></i></button>
                </div>
                <div class="col-auto action-btn bg-danger ms-2">
                    <a href="{{route('report.income.expense')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}" class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white"></i></a>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

    <div id="printableArea" >
        <div class="row">
           
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
                                        <h6 class="mb-3 mt-4">{{__('Total Income')}}</h6>
                                        <h3 class="mb-0">{{\Auth::user()->priceFormat($incomeCount)}} </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-info">
                                            <i class="ti ti-receipt-tax"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Expense')}}</h6>
                                        <h3 class="mb-0">{{\Auth::user()->priceFormat($expenseCount)}}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-warning">
                                            <i class="ti ti-discount-2"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Net Profit')}}</h6>
                                        <h3 class="mb-0">{{\Auth::user()->priceFormat($incomeCount-$expenseCount)}} </h3>
                                    </div>
                                </div>
                            </div>
                           
                        </div>
                    </div>
                    <div class="col-xxl-6">
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" value="{{'Income Vs Expense Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange']}}" id="filename">
                                <h5>{{__('Income Vs Expense Summary')}}</h5>
                                <div class="row  mt-4">
                                    <div class="col-md-6 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-success">
                                                <i class="ti ti-heart"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Report')}} :</p>
                                                <p class="mb-0 text-success">{{__('Income Vs Expense Summary')}}</p>
                                                
                                            </div>
                                        </div>
                                    </div>
                                   
                                    
                                    <div class="col-md-5 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-danger">
                                                <i class="ti ti-calendar"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0"> {{__('Duration')}} :</p>
                                                <p class="mb-0 text-danger">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</p>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{__('Income Vs Expense Summary')}}</h5>
                                </div>
                                <div class="card-body">
                                    <div id="traffic-chart"></div>
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



