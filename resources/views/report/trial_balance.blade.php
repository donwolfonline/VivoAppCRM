@extends('layouts.admin')
@section('page-title')
    {{__('Trial Balance')}}
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
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Trial Balance')}}</h5>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Trial Balance')}}</li>
@endsection

@section('content')

<div class="col-xl-12">
<div class="card card-body">
    <div class="row d-flex justify-content-end">
            <div class="col-auto ">
                {{ Form::open(array('route' => array('trial.balance'),'method' => 'GET','id'=>'report_trial_balance')) }}
                <div class="all-select-box">
                    <div class="btn-box">
                        {{ Form::label('start_date', __('Start Date'),['class'=>'text-type']) }}
                        {{ Form::date('start_date',$filter['startDateRange'], array('class' => 'month-btn form-control')) }}
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="all-select-box">
                    <div class="btn-box">
                        {{ Form::label('end_date', __('End Date'),['class'=>'text-type']) }}
                        {{ Form::date('end_date',$filter['endDateRange'], array('class' => 'month-btn form-control')) }}
                    </div>
                </div>
            </div>

            <div class="col-auto mt-4">
                <div class="action-btn bg-info ms-2">
                    <a href="#" class="btn btn-sm btn-white btn-icon-only rounded-circle" onclick="document.getElementById('report_trial_balance').submit(); return false;" data-toggle="tooltip" data-original-title="{{__('apply')}}">
                        <span class="btn-inner--icon"><i class="ti ti-search text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('apply')}}"></i></span>
                    </a>
                </div>
                <div class="action-btn bg-danger ms-2">
                    <a href="{{route('trial.balance')}}" class="btn btn-sm btn-white btn-icon-only rounded-circle" data-toggle="tooltip" data-original-title="{{__('Reset')}}">
                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('Reset')}}"></i></span>
                    </a>
                </div>
                <div class="action-btn bg-secondary ms-2">
                    <a href="#" class="btn btn-sm btn-white btn-icon-only rounded-circle" onclick="saveAsPDF()" data-toggle="tooltip" data-original-title="{{__('Download')}}">
                        <span class="btn-inner--icon"><i class="ti ti-download text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('Download')}}"></i></span>
                    </a>
                </div>
            </div>
    </div>
    {{ Form::close() }}
</div>
</div>



    <div id="printableArea">
        <div class="row mt-4">
            <div class="col">
                <input type="hidden" value="{{__('Trial Balance').' '.'Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange']}}" id="filename">
                <div class="card p-4 mb-4">
                    <h6 class="report-text gray-text mb-0">{{__('Report')}} :</h6>
                    <h7 class="report-text mb-0">{{__('Trial Balance Summary')}}</h7>
                </div>
            </div>

            <div class="col">
                <div class="card p-4 mb-4">
                    <h6 class="report-text gray-text mb-0">{{__('Duration')}} :</h6>
                    <h7 class="report-text mb-0">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</h7>
                </div>
            </div>
        </div>
        @if(!empty($account))
            <div class="row mt-4">
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h5 class="report-text gray-text mb-0">{{__('Total Credit')}} :</h5>
                        <h5 class="report-text mb-0">0</h5>
                    </div>
                </div>
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h5 class="report-text gray-text mb-0">{{__('Total Debit')}} :</h5>
                        <h5 class="report-text mb-0">0</h5>
                    </div>
                </div>
            </div>
        @endif
        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card card-fluid">
                <table class="table table-flush">
                    <thead>
                    <tr>
                        <th> {{__('Account Name')}}</th>
                        <th> {{__('Debit Total')}}</th>
                        <th> {{__('Credit Total')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php  $debitTotal=0;$creditTotal=0;@endphp
                    @foreach($journalItem as  $item)

                        <tr>
                            <td>{{$item['name']}}</td>
                            <td>
                                @if($item['netAmount']<0)
                                    @php
                                        $debitTotal+=abs($item['netAmount']);
                                    @endphp
                                    {{\Auth::user()->priceFormat(abs($item['netAmount']))}}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($item['netAmount']>0)
                                    @php
                                        $creditTotal+=$item['netAmount'];
                                    @endphp
                                    {{\Auth::user()->priceFormat($item['netAmount'])}}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <footer>
                        <td class="text-dark">{{__('Total')}}</td>
                        <td  class="text-dark">{{!empty(\Auth::user()->priceFormat($debitTotal))?\Auth::user()->priceFormat($debitTotal):0}}</td>
                        <td  class="text-dark">{{!empty(\Auth::user()->priceFormat($creditTotal))?\Auth::user()->priceFormat($creditTotal):0}} </td>                    </footer>
                </table>
                </div>
            </div>
        </div>
    </div>
@endsection
