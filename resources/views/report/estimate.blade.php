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
    {{__('Estimate Report')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Estimate Report')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Estimate Report')}}</li>
@endsection
@section('action-btn')
<a href="{{route('estimate_report.export')}}" data-bs-toggle="tooltip" title="{{ __('Export') }}" class="btn btn-sm btn-primary">
    <i class="ti ti-file-export"></i>
</a>
    <a href="#" onclick="saveAsPDF();" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="{{__('Download')}}" id="download-buttons">
        <i class="ti ti-download"></i>
    </a>
@endsection

@section('content')
      <!-- [ Main Content ] start -->

        <div class="col-sm-12">
            <div class=" {{isset($_GET['start_month'])?'show':''}}" >
                <div class="card card-body">
                    {{ Form::open(array('route' => array('report.estimate'),'method'=>'get')) }}
                    <div class="row filter-css">
                        <div class="col-auto">
                            {{Form::month('start_month',isset($_GET['start_month'])?$_GET['start_month']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="col-auto">
                            {{Form::month('end_month',isset($_GET['end_month'])?$_GET['end_month']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" data-toggle="select" name="status">
                                <option value="">{{__('Select Status')}}</option>
                                @foreach($status as $k=>$val)
                                    <option value="{{$k}}" {{(isset($_GET['status']) && !empty($_GET['status']) && $_GET['status']==$k)?'selected':''}}> {{$val}} </option>
                                @endforeach
                            </select>
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
                                <a href="{{route('report.estimate')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}" 
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
                    <div class="col-xxl-6">
                        <div class="row">
                            <div class="col-lg-4 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-primary">
                                            <i class="ti ti-user-plus"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Estimation')}}</h6>
                                        <h3 class="mb-0">{{\Auth::user()->priceFormat($totalEstimation)}} </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-info">
                                            <i class="ti ti-receipt-tax"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Tax')}}</h6>
                                        <h3 class="mb-0">{{\Auth::user()->priceFormat($totalTax)}}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-warning">
                                            <i class="ti ti-discount-2"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Discount')}}</h6>
                                        <h3 class="mb-0">{{\Auth::user()->priceFormat($totalDiscount)}} </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6">
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" value="{{$filter['status'].' '.__('Status').' '.'Estimation Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange'].' '.__('of').' '.$filter['client']}}" id="filename">
                                <h5>{{ __('Estimation Report') }}</h5>
                                <div class="row  mt-4">
                                    <div class="col-md-4 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-success">
                                                <i class="ti ti-heart"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Report')}} :</p>
                                                <p class="mb-0 text-success">{{__('Estimation Summary')}}</p>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    @if($filter['client']!= __('All'))
                                        <div class="col-md-3 col-sm-6">
                                            <div class="d-flex align-items-start">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Client')}} :</p>
                                                    <p class="mb-0 text-primary">{{$filter['client'] }}</p>
                                                
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($filter['status']!= __('All'))
                                        <div class="col-md-3 col-sm-6">
                                            <div class="d-flex align-items-start">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-thumb-up"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Status')}} :</p>
                                                    <p class="mb-0 text-primary">{{$filter['status'] }}</p>
                                                
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
                                                <p class="text-muted text-sm mb-0"> {{__('Duration')}} :</p>
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
                                <!-- <h5> -->
                                <!-- <div class="card-body table-border-style"> -->
                                 
                                <!-- </h5>    -->
                                    <div class="table-responsive">
                                        <table class="table pc-dt-export">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{__('Client')}}</th>
                                                    <th>{{__('Issue Date')}}</th>
                                                    <th>{{__('Send Date')}}</th>
                                                    <th>{{__('Expiry Date')}}</th>
                                                    <th>{{__('Sub Total')}}</th>
                                                    <th>{{__('Total Tax')}}</th>
                                                    <th>{{__('Discount')}}</th>
                                                    <th>{{__('Total')}}</th>
                                                    <th>{{__('Status')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($estimates as $estimate)
                                                    <tr>
                                                        <td>{{\Auth::user()->estimateNumberFormat($estimate->estimate)}}</td>
                                                        <td>{{!empty($estimate->clients)?$estimate->clients->name:''}}</td>
                                                        <td>{{\Auth::user()->dateFormat($estimate->issue_date)}}</td>
                                                        <td>{{\Auth::user()->dateFormat($estimate->send_date)}}</td>
                                                        <td>{{\Auth::user()->dateFormat($estimate->expiry_date)}}</td>
                                                        <td>{{\Auth::user()->priceFormat($estimate->getSubTotal())}}</td>
                                                        <td>{{\Auth::user()->priceFormat($estimate->getTotalTax())}}</td>
                                                        <td>{{\Auth::user()->priceFormat($estimate->getTotalDiscount())}}</td>
                                                        <td>{{\Auth::user()->priceFormat($estimate->getTotal())}}</td>
                                                        <td>
                                                            @if($estimate->status == 0)
                                                                <span class="badge bg-primary">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                                                            @elseif($estimate->status == 1)
                                                                <span class="badge bg-info">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                                                            @elseif($estimate->status == 2)
                                                                <span class="badge bg-success">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                                                            @elseif($estimate->status == 3)
                                                                <span class="badge bg-warning">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                                                            @elseif($estimate->status == 4)
                                                                <span class="badge bg-danger">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
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



