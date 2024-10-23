@extends('layouts.admin')
@section('page-title')
    {{__('Balance Sheet')}}
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
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Balance Sheet')}}</h5>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Balance Sheet')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="{{__('Download')}}" data-original-title="{{__('Download')}}">
            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
        </a>
    </div>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="row mb-5 gy-4">
            <div class="col-xl-6 col-lg-9 col-md-6">
                <div class="welcome-card border bg-light-success p-3 border-success rounded text-dark h-100">
                    <h3 class="mb-3">{{__('Select dates')}}</h3>
                    {{ Form::open(array('route' => array('report.balance.sheet'),'method' => 'GET','id'=>'report_bill_summary')) }}
                    <div class="row gy-2 gx-2">
                        <div class="col-lg-4">
                            <div class="form-group mb-0">
                                {{ Form::label('start_date', __('Start Date'),['class'=>'form-label']) }}
                                <div class="input-group date">
                                    {{ Form::date('start_date',$filter['startDateRange'], array('class' => 'month-btn form-control')) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group mb-0">
                                {{ Form::label('end_date', __('End Date'),['class'=>'form-label']) }}
                                <div class="input-group date">
                                    {{ Form::date('end_date',$filter['endDateRange'], array('class' => 'month-btn form-control')) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 d-flex align-items-end">
                            <a href="#" class="btn btn-primary me-2" onclick="document.getElementById('report_bill_summary').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                <span class="btn-inner--icon"><i data-feather="check-circle" class="me-1"></i></span>
                            </a>
                            <a href="{{route('report.balance.sheet')}}" class="btn btn-danger" data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                <span class="btn-inner--icon"><i data-feather="trash-2"></i></span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <div class="card h-100 shadow-none mb-0">
                    <div class="card-body border rounded p-3">
                        <input type="hidden" value="{{__('Balance Sheet').' '.'Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange']}}" id="filename">
                        <div class="mb-4 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">{{__('Report')}}: <br>
                                <small class="text-muted">{{__('Balance Sheet')}}</small>
                            </h6>
                            <span><i data-feather="arrow-up-right"></i></span>
                        </div>
                        <h6 class="mb-0">{{__('Duration')}}:</h6>
                        <small class="text-muted">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</small>
                    </div>
                </div>
            </div>
            @foreach($chartAccounts as $type => $accounts)
                @php $totalNetAmount=0; @endphp
                @foreach($accounts as  $accountData)
                    @foreach($accountData['account'] as  $account)
                        @php $totalNetAmount+=$account['netAmount']; @endphp
                    @endforeach
                @endforeach
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <div class="card shadow-none mb-0 h-100">
                        <div class="card-body border rounded p-3">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <h6 class="mb-0">{{__('Total'.' '.$type)}}</h6>
                                <span><i data-feather="arrow-up-right"></i></span>
                            </div>
                            <div class="mb-3 d-flex align-items-center justify-content-between">
                                <span class="f-30 f-w-600"> @if($totalNetAmount<0)
                                        {{__('Dr').'. '.\Auth::user()->priceFormat(abs($totalNetAmount))}}
                                    @elseif($totalNetAmount>0)
                                        {{__('Cr').'. '.\Auth::user()->priceFormat($totalNetAmount)}}
                                    @else
                                        {{\Auth::user()->priceFormat(0)}}
                                    @endif
                                </span>
                            </div>
                            <div class="chart-wrapper">
                                <div id="TotalProducts"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

<div id="printableArea">
    <div class="row">
        <div class="col-lg-4 col-md-4">
            <h3>{{__('Select data sheet')}}</h3>
        </div>
        <div class="col-lg-8 col-md-8 d-flex justify-content-end">
            <ul class="nav nav-pills cust-nav   rounded  mb-3" id="pills-tab" role="tablist">
                @php
                    $abc = 1;
                    $xyz = 1;
                @endphp
                @foreach($chartAccounts as $type => $accounts)

                    <li class="nav-item">
                        <a class="nav-link {{$abc == 1 ? 'active' : '' }}" id="{{$type.'-tab'}}" data-bs-toggle="pill" href="{{'#'.$type}}" role="tab" aria-controls="asset" aria-selected="true">{{$type}}</a>
                    </li>
                    @php
                        $abc = 0;
                    @endphp
                @endforeach

            </ul>
        </div>
        <div class="col-12">
            <div class="tab-content" id="pills-tabContent">
                @foreach($chartAccounts as $type => $accounts)
                    <div class="tab-pane fade  {{$xyz == 1 ? 'active show' : '' }}" id="{{$type}}" role="tabpanel" aria-labelledby="{{$type.'-tab'}}">
                        @php
                            $xyz = 0;
                        @endphp
                        <div class="row gy-4">
                            @foreach($accounts as $account)
                                <div class="col-xxl-3 col-lg-4 col-md-6">
                                    <div class="data-wrapper rounded">
                                        <h4>{{$account['subType']}}</h4>
                                        <div class="data-body bg-white list-group">
                                            <div class="list-group-item list-head d-flex justify-content-between p-b-0 ps-0 pe-0">
                                                <span class="f-w-900 border-bottom border-dark ps-3 pe-3 pb-2">{{__('Account')}} <i class="ti ti-arrows-up-down"></i></span>
                                                <span class="text-muted  ps-3 pe-3 pb-2">{{__('Amount')}}</span>
                                            </div>
                                            @php
                                                $totalCredit=0;$totalDebit=0;
                                            @endphp
                                            @foreach($account['account'] as  $record)
                                                @php
                                                    $totalCredit+=$record['totalCredit'];
                                                    $totalDebit+=$record['totalDebit'];
                                                @endphp
                                                <div class="list-group-item  d-flex justify-content-between ">
                                                    <span>{{$record['account_name']}}</span>
                                                    <span>
                                                        @if($record['netAmount']<0)
                                                            {{__('Dr').'. '.\Auth::user()->priceFormat(abs($record['netAmount']))}}
                                                        @elseif($record['netAmount']>0)
                                                            {{__('Cr').'. '.\Auth::user()->priceFormat($record['netAmount'])}}
                                                        @else
                                                            {{\Auth::user()->priceFormat(0)}}
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between bg-success p-3">
                                            <span>{{__('Total').' '.$account['subType']}}</span>
                                            <span>
                                                @php $total= $totalCredit-$totalDebit; @endphp
                                                @if($total<0)
                                                    {{__('Dr').'. '.\Auth::user()->priceFormat(abs($total))}}
                                                @elseif($total>0)
                                                    {{__('Cr').'. '.\Auth::user()->priceFormat($total)}}
                                                @else
                                                    {{\Auth::user()->priceFormat(0)}}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
