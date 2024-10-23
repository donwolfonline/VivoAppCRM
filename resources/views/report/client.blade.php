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
    {{__('Client Report')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Client Report')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Client Report')}}</li>
@endsection
@section('action-btn')
<a href="{{route('client_report.export')}}" data-bs-toggle="tooltip" title="{{ __('Export') }}" class="btn btn-sm btn-primary">
    <i class="ti ti-file-export"></i>
</a>
    <a href="#" onclick="saveAsPDF();" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="{{__('Download')}}" id="download-buttons">
        <i class="ti ti-download"></i>
    </a>
@endsection

@section('content')

        <div class="col-12">
            <div class=" {{isset($_GET['start_month'])?'show':''}}" >
                <div class="card card-body">
                    {{ Form::open(array('route' => array('report.client'),'method'=>'get')) }}
                    <div class="row filter-css">
                        <div class="col-auto">
                            {{Form::month('start_month',isset($_GET['start_month'])?$_GET['start_month']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="col-auto">
                            {{Form::month('end_month',isset($_GET['end_month'])?$_GET['end_month']:'',array('class'=>'form-control'))}}
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
                                <a href="{{route('report.client')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}" 
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
                    <div class="col-xl-12">
                        <div class="card h-75">
                            <div class="card-body">
                                <input type="hidden" value="{{$filter['client'].' '.__('Client').' '.'Client Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange']}}" id="filename">
                                <h5>{{ __('Client Report') }}</h5>
                                <div class="row mt-4 mb-4">
                                    <div class="col-md-2 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-heart"></i>
                                            </div>
                                            <div class="ms-2 ">
                                                <p class="text-muted text-sm mb-0">{{__('Report')}} :</p>
                                                <p class="mb-0 text-primary">{{__('Client Summary')}}</p>
                                            
                                            </div>
                                        </div>
                                    </div>
                                    @if($filter['client']!= __('All'))
                                        <div class="col-md-4 col-sm-6 my-3 my-sm-0">
                                            <div class="d-flex align-items-start">
                                                <div class="theme-avtar bg-warning">
                                                    <i class="ti ti-users"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Client')}} : </p>
                                                    <p class="mb-0 text-warning">{{$filter['client'] }}</p>
                                                    
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
                                                <p class="text-muted text-sm mb-0">{{__('Duration')}} :</p>
                                                <p class="mb-0 text-danger">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-lg-2 col-6">
                                <div class="card">
                                    <div class="card-body min-vh-90">
                                        <div class="theme-avtar bg-primary">
                                            <i class="ti ti-file-invoice"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Invoice')}}</h6>
                                        <h6 class="mb-3">{{\Auth::user()->priceFormat($clientTotalInvoice)}}</h6>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-dark">
                                            <i class="ti ti-report-money"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Amount')}}</h6>
                                        <h6 class="mb-3">{{\Auth::user()->priceFormat($clientTotalAmount)}}</h6>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-warning">
                                            <i class="ti ti-receipt-tax"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Tax')}}</h6>
                                        <h6 class="mb-3">{{\Auth::user()->priceFormat($clientTotalTax)}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-danger">
                                            <i class="ti ti-discount-2"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Discount')}}</h6>
                                        <h6 class="mb-3">{{\Auth::user()->priceFormat($clientTotalDiscount)}}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-info">
                                            <i class="ti ti-click"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Paid')}}</h6>
                                        <h6 class="mb-3">{{\Auth::user()->priceFormat($clientTotalPaid)}}</h6>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-secondary">
                                            <i class="ti ti-calendar-time"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Due')}}</h6>
                                        <h6 class="mb-3">{{\Auth::user()->priceFormat($clientTotalDue)}}</h6>
                                        
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
                                                    <th>{{__('Client')}}</th>
                                                    <th>{{__('Total Invoice')}}</th>
                                                    <th>{{__('Amount')}}</th>
                                                    <th>{{__('Total Tax')}}</th>
                                                    <th>{{__('Total Discount')}}</th>
                                                    <th>{{__('Total Paid')}}</th>
                                                    <th>{{__('Total Due')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($clientReport as $client)
                                                    <tr>
                                                        <td>{{$client['client']}}</td>
                                                        <td>{{$client['totalInvoice']}}</td>
                                                        <td>{{\Auth::user()->priceFormat($client['totalAmount'])}}</td>
                                                        <td>{{\Auth::user()->priceFormat($client['totalTax'])}}</td>
                                                        <td>{{\Auth::user()->priceFormat($client['totalDiscount'])}}</td>
                                                        <td>{{\Auth::user()->priceFormat($client['totalPaid'])}}</td>
                                                        <td>{{\Auth::user()->priceFormat($client['totalDue'])}}</td>
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



