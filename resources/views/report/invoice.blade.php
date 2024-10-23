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
            var dataTableLang = {
            paginate: {previous: "<i class='fas fa-angle-left'>", next: "<i class='fas fa-angle-right'>"},
            lengthMenu: "{{__('Show')}} _MENU_ {{__('entries')}}",
            zeroRecords: "{{__('No data available in table.')}}",
            info: "{{__('Showing')}} _START_ {{__('to')}} _END_ {{__('of')}} _TOTAL_ {{__('entries')}}",
            infoEmpty: "{{ __('Showing 0 to 0 of 0 entries') }}",
            infoFiltered: "{{ __('(filtered from _MAX_ total entries)') }}",
            search: "{{__('Search:')}}",
            thousands: ",",
            loadingRecords: "{{ __('Loading...') }}",
            processing: "{{ __('Processing...') }}"
        }
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
    {{__('Invoice Report')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Invoice Report')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>

    <li class="breadcrumb-item active" aria-current="page">{{__('Invoice Report')}}</li>
@endsection
@section('action-btn')

    <a href="{{ route('invoice_report.export') }}" data-bs-toggle="tooltip" title="{{ __('Export') }}" class="btn btn-sm btn-primary">
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
                    {{ Form::open(array('route' => array('report.invoice'),'method'=>'get')) }}
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

                        <div class="col-auto">
                            <button type="submit" class="btn btn-xs btn-primary btn-icon-only rounded-circle" data-toggle="tooltip" data-title="{{__('Apply')}}"><i class="ti ti-search"></i></button>
                        </div>
                        <div class="col-auto">
                            <a href="{{route('report.invoice')}}" data-toggle="tooltip" data-title="{{__('Reset')}}" class="btn btn-xs btn-danger btn-icon-only rounded-circle"><i class="ti ti-trash"></i></a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

    <div id="printableArea" >
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-wrap">
                        <div class="card-body">
                            <input type="hidden" value="{{$filter['status'].' '.__('Status').' '.'Invoice Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange'].' '.__('of').' '.$filter['client']}}" id="filename">
                            <div class="row">
                                <div class="col">
                                    {{__('Report')}} : <h6>{{__('Invoice Summary')}}</h6>
                                </div>
                                @if($filter['client']!= __('All'))
                                    <div class="col">
                                        {{__('Client')}} : <h6>{{$filter['client'] }}</h6>
                                    </div>
                                @endif
                                @if($filter['status']!= __('All'))
                                    <div class="col">
                                        {{__('Status')}} : <h6>{{$filter['status'] }}</h6>
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
            <div class="col-xl-3 col-md-6">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <h6 class="card-title text-muted mb-0">{{__('Total Invoice')}}</h6>
                                        <span class="h6 font-weight-bold mb-0">{{\Auth::user()->priceFormat($totalInvoice)}}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <h6 class="card-title text-muted mb-0">{{__('Total Due')}}</h6>
                                        <span class="h6 font-weight-bold mb-0">{{\Auth::user()->priceFormat($totalDue)}}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <h6 class="card-title text-muted mb-0">{{__('Total Tax')}}</h6>
                                        <span class="h6 font-weight-bold mb-0">{{\Auth::user()->priceFormat($totalTax)}}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <h6 class="card-title text-muted mb-0">{{__('Total Discount')}}</h6>
                                        <span class="h6 font-weight-bold mb-0">{{\Auth::user()->priceFormat($totalDiscount)}}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table align-items-center pc-dt-export">
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
                    @foreach($invoices as $invoice)
                        <tr>
                            <td>{{\Auth::user()->invoiceNumberFormat($invoice->invoice_id)}}</td>
                            <td>{{!empty($invoice->clients)?$invoice->clients->name:''}}</td>
                            <td>{{\Auth::user()->dateFormat($invoice->issue_date)}}</td>
                            <td>{{\Auth::user()->dateFormat($invoice->send_date)}}</td>
                            <td>{{\Auth::user()->dateFormat($invoice->due_date)}}</td>
                            <td>{{\Auth::user()->priceFormat($invoice->getSubTotal())}}</td>
                            <td>{{\Auth::user()->priceFormat($invoice->getTotalTax())}}</td>
                            <td>{{\Auth::user()->priceFormat($invoice->getTotalDiscount())}}</td>
                            <td>{{\Auth::user()->priceFormat($invoice->getTotal())}}</td>
                            <td>
                                @if($invoice->status == 0)
                                    <span class="badge badge-primary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                @elseif($invoice->status == 1)
                                    <span class="badge badge-info">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                @elseif($invoice->status == 2)
                                    <span class="badge badge-success">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                @elseif($invoice->status == 3)
                                    <span class="badge badge-warning">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                @elseif($invoice->status == 4)
                                    <span class="badge badge-danger">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div> --}}
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-12">
                <div class="collapse {{isset($_GET['start_month'])?'show':''}}" id="collapseExample">
                    <div class="card card-body">
                        {{ Form::open(array('route' => array('report.invoice'),'method'=>'get')) }}
                        <div class="row filter-css">
                            <div class="col-auto">
                                {{Form::month('start_month',isset($_GET['start_month'])?$_GET['start_month']:'',array('class'=>'form-control'))}}
                            </div>
                            <div class="col-auto">
                                {{Form::month('end_month',isset($_GET['end_month'])?$_GET['end_month']:'',array('class'=>'form-control'))}}
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" data-toggle="select" name="status">
                                    <option value="">{{__('All')}}</option>
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
                                    <a href="{{route('report.invoice')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}" 
                                    class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white"></i></a>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
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
                            <div class="col-lg-3 col-6 dashboard-card">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-primary">
                                            <i class="ti ti-user-plus"></i>
                                        </div>
                                        <h6 class="mb-3 mt-3">{{__('Total Invoice')}}</h6>
                                        <h4 class="mb-0">{{\Auth::user()->priceFormat($totalInvoice)}} </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6 dashboard-card">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-info">
                                            <i class="ti ti-receipt-tax"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Due')}}</h6>
                                        <h4 class="mb-0">{{\Auth::user()->priceFormat($totalDue)}}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6 dashboard-card">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-warning">
                                            <i class="ti ti-discount-2"></i>
                                        </div>
                                        <h6 class="mb-3 mt-4">{{__('Total Tax')}}</h6>
                                        <h4 class="mb-0">{{\Auth::user()->priceFormat($totalTax)}} </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6 dashboard-card">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="theme-avtar bg-secondary">
                                            <i class="ti ti-discount-2"></i>
                                        </div>
                                        <h6 class="mb-3 mt-3">{{__('Total Discount')}}</h6>
                                        <h4 class="mb-0">{{\Auth::user()->priceFormat($totalDiscount)}} </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6">
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" value="{{$filter['status'].' '.__('Status').' '.'Estimation Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange'].' '.__('of').' '.$filter['client']}}" id="filename">
                                <h5>{{ __('Invoice Report') }}</h5>
                                <div class="row  mt-4">
                                    <div class="col-md-4 col-sm-6">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avtar bg-success">
                                                <i class="ti ti-heart"></i>
                                            </div>
                                            <div class="ms-2">
                                                <p class="text-muted text-sm mb-0">{{__('Report')}} :</p>
                                                <p class="mb-0 text-success">{{__('Invoice Summary')}}</p>
                                                
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
                                                <div class="theme-avtar bg-warning">
                                                    <i class="ti ti-thumb-up"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Status')}} :</p>
                                                    <p class="mb-0 text-warning">{{$filter['status'] }}</p>
                                                
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
                                                @foreach($invoices as $invoice)
                                                    <tr>
                                                        <td>{{\Auth::user()->invoiceNumberFormat($invoice->invoice_id)}}</td>
                                                        <td>{{!empty($invoice->clients)?$invoice->clients->name:''}}</td>
                                                        <td>{{\Auth::user()->dateFormat($invoice->issue_date)}}</td>
                                                        <td>{{\Auth::user()->dateFormat($invoice->send_date)}}</td>
                                                        <td>{{\Auth::user()->dateFormat($invoice->due_date)}}</td>
                                                        <td>{{\Auth::user()->priceFormat($invoice->getSubTotal())}}</td>
                                                        <td>{{\Auth::user()->priceFormat($invoice->getTotalTax())}}</td>
                                                        <td>{{\Auth::user()->priceFormat($invoice->getTotalDiscount())}}</td>
                                                        <td>{{\Auth::user()->priceFormat($invoice->getTotal())}}</td>
                                                        <td>
                                                            @if($invoice->status == 0)
                                                                <span class="badge bg-primary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 1)
                                                                <span class="badge bg-info">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 2)
                                                                <span class="badge bg-success">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 3)
                                                                <span class="badge bg-warning">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                            @elseif($invoice->status == 4)
                                                                <span class="badge bg-danger">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
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
                </div>
                <!-- [ sample-page ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

@endsection



