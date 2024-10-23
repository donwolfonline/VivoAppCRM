@extends('layouts.admin')
@section('page-title')
    {{__('Ledger Summary')}}
@endsection
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
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Ledger Summary')}}</h5>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Ledger Summary')}}</li>
@endsection

@section('content')

<div class="col-xl-12">
            <div class=" {{isset($_GET['project'])?'show':''}}" >
                <div class="card card-body">
                {{ Form::open(array('route' => array('report.ledger'),'method' => 'GET','id'=>'report_ledger')) }}
            <div class="row filter-css">
                <div class="col-md-3">
                    {{ Form::label('start_date', __('Start Date'),['class'=>'text-type']) }}
                    {{ Form::date('start_date',$filter['startDateRange'], array('class' => 'month-btn form-control')) }}
                </div>
                <div class="col-md-3">
                    <div class="btn-box">
                        {{ Form::label('end_date', __('End Date'),['class'=>'text-type']) }}
                        {{ Form::date('end_date',$filter['endDateRange'], array('class' => 'month-btn form-control')) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="all-select-box">
                        <div class="btn-box">
                            {{ Form::label('account', __('Account'),['class'=>'text-type']) }}
                            {{ Form::select('account',$accounts,isset($_GET['account'])?$_GET['account']:'', array('class' => 'form-control select2')) }}
                        </div>
                    </div>
                </div>
                <div class="col-auto mt-4">
                    <div class="action-btn bg-info ms-2">
                        <div class="col-auto">
                            <a href="#" class="mx-3 btn btn-sm d-flex align-items-center" onclick="document.getElementById('report_ledger').submit(); return false;" data-toggle="tooltip" data-original-title="{{__('apply')}}">
                                <span class="btn-inner--icon"><i class="ti ti-search text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('apply')}}" ></i></span>
                            </a>
                        </div>
                    </div>
                    <div class="action-btn bg-danger ms-2">
                        <div class="col-auto">
                            <a href="{{route('report.ledger')}}" class="mx-3 btn btn-sm d-flex align-items-center" data-toggle="tooltip" data-original-title="{{__('Reset')}}">
                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('Reset')}}"></i></span>
                            </a>
                        </div>
                    </div>
                    <div class="action-btn bg-secondary ms-2">
                        <div class="col-auto">
                            <a href="#" class="mx-3 btn btn-sm d-flex align-items-center" onclick="saveAsPDF()" data-toggle="tooltip" data-original-title="{{__('Download')}}">
                                <span class="btn-inner--icon"><i class="ti ti-download text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('Download')}}"></i></span>
                            </a>
                        </div>
                    </div>
                </div>
 
            </div>
{{ Form::close() }}
                </div>
            </div>
        </div>


  
    <div class="col-xl-12">
            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-xxl-7">
                            <div class="row">
                                <div class="col-lg-3 dashboard-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-home"></i>
                                            </div>
                                            <!-- <p class="text-muted text-sm mt-4 mb-2">Statistics</p> -->
                                            <h6 class="mb-3">{{__('Account Name')}} :</h6>
                                            <h6 class="mb-0">{{$account->name}}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 dashboard-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-info">
                                                <i class="ti ti-code"></i>
                                            </div>
                                            <!-- <p class="text-muted text-sm mt-4 mb-2">Statistics</p> -->
                                            <h6 class="mb-3">{{__('Account Code')}} :</h6>
                                            <h3 class="mb-0">{{$account->code}} </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 dashboard-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-secondary">
                                                <i class="ti ti-click"></i>
                                            </div>
                                            <!-- <p class="text-muted text-sm mt-4 mb-2">Statistics</p> -->
                                            <h6 class="mb-3">{{__('Total Debit')}} :</h6>
                                            <h3 class="mb-0">{{\Auth::user()->priceFormat($filter['debit'])}} </h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 dashboard-card  ">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-warning">
                                                <i class="ti ti-report-money"></i>
                                            </div>
                                            <!-- <p class="text-muted text-sm mt-4 mb-2">Statistics</p> -->
                                            <h6 class="mb-3">{{__('Total Credit')}} :</h6>
                                            <h3 class="mb-0">{{\Auth::user()->priceFormat($filter['credit'])}}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-5">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="mb-0 ">{{ __('Report') }}</h3>
                                    <small class="">{{ __('Track and find all the details about our Ledger Summary, your stats and revenues.') }}</small>
                                    <div class="row my-4">
                                        <div class="col">
                                            <input type="hidden" value="{{__('Ledger').' '.'Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange']}}" id="filename">
                                            <div class="p-2">
                                                <h6 class="report-text gray-text mb-0">{{__('Report')}} :</h6>
                                                <h7 class="report-text mb-0">{{__('Ledger Summary')}}</h7>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <h6 class="report-text gray-text mb-0">{{__('Duration')}} :</h6>
                                            <h7 class="report-text mb-0">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</h7>
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
                        <h5></h5>
                        <div class="table-responsive">
                            <table class="table" id="">
                                <thead>
                                    <tr>
                                        <th> #</th>
                                        <th> {{__('Transaction Date')}}</th>
                                        <th> {{__('Create At')}}</th>
                                        <th> {{__('Description')}}</th>
                                        <th> {{__('Debit')}}</th>
                                        <th> {{__('Credit')}}</th>
                                        <th> {{__('Balance')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $balance=0;$debit=0;$credit=0; @endphp
                                    @foreach($journalItems as  $item)
                                        <tr>
                                            <td class="Id">
                                                <a href="{{ route('journal-entry.show',$item->journal) }}">{{ AUth::user()->journalNumberFormat($item->journal_id) }}</a>
                                            </td>

                                            <td>{{\Auth::user()->dateFormat($item->transaction_date)}}</td>
                                            <td>{{\Auth::user()->dateFormat($item->created_at)}}</td>
                                            <td>{{!empty($item->description)?$item->description:'-'}}</td>
                                            <td>{{\Auth::user()->priceFormat($item->debit)}}</td>
                                            <td>{{\Auth::user()->priceFormat($item->credit)}}</td>
                                            <td>
                                                @if($item->debit>0)
                                                    @php $debit+=$item->debit @endphp
                                                @else
                                                    @php $credit+=$item->credit @endphp
                                                @endif

                                                @php $balance= $credit-$debit @endphp
                                                @if($balance>0)
                                                    {{__('Cr').'. '.\Auth::user()->priceFormat($balance)}}

                                                @elseif($balance<0)
                                                    {{__('Dr').'. '.\Auth::user()->priceFormat(abs($balance))}}
                                                @else
                                                    {{\Auth::user()->priceFormat(0)}}
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

@endsection


