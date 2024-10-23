@extends('layouts.admin')
@php
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
    // $profile=asset(Storage::url('uploads/avatar'));
    $logo = Utility::GetLogo();
@endphp
@push('script-page')
    <script>
        $(document).on("click", ".estimation_id", function () {
            var estimation_id = $(this).attr('data-estimation');
            var status = $(this).attr('data-id');
            $.ajax({
                url: '{{route('estimate.status.change')}}',
                type: 'GET',
                data: {
                    estimation_id: estimation_id,
                    status: status,
                    "_token": $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    location.reload();
                }
            });
        });

        $('.cp_link').on('click', function () {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('Success', '{{__('Link Copy on Clipboard')}}', 'success')
        });
    </script>
@endpush
@section('page-title')
    {{__('Estimate Detail')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{\Auth::user()->estimatenumberFormat($estimate->estimate).' '.__('Details')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('estimate.index')}}">{{__('Estimation')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{\Auth::user()->estimatenumberFormat($estimate->estimate)}}</li>
@endsection
@section('action-btn')
     <a href="#" class="btn btn-sm btn-primary btn-icon m-1 cp_link" data-link="{{route('pay.estimate',\Illuminate\Support\Facades\Crypt::encrypt($estimate->id))}}" data-toggle="tooltip" data-original-title="{{__('Click to copy invoice link')}}">
        <span class="btn-inner--icon"><i class="ti ti-copy"></i></span>
        <span class="btn-inner--text">{{__('Copy')}}</span>
    </a>
    @if($estimate->status==0)
     <a href="{{route('estimate.send',$estimate->id)}}" class="btn btn-sm btn-primary btn-icon m-1">
            <span class="btn-inner--icon"><i class="ti ti-send"></i></span>
            <span class="btn-inner--text">{{__('Send')}}</span>
        </a>
    @else
        <a href="{{route('estimate.send',$estimate->id)}}" class="btn btn-sm btn-primary btn-icon m-1">
            <span class="btn-inner--icon"><i class="fas fa-envelope-open-text"></i></span>
            <span class="btn-inner--text">{{__('Resend')}}</span>
        </a>
    @endif
    <a href="{{route('estimate.pdf',\Crypt::encrypt($estimate->id))}}" target="_blank" class="btn btn-sm btn-primary btn-icon m-1">
        <span class="btn-inner--icon"><i class="ti ti-printer"></i></span>
        <span class="btn-inner--text">{{__('Print')}}</span>
    </a>
@endsection
@section('filter')
@endsection
@section('content')

    <div class="row">
        <!-- [ Invoice ] start -->
        <div class="container">
            <div>
                <div class="card" id="printTable">
                    <div class="card-body">
                        <div class="row ">
                            <div class="col-md-8 invoice-contact">
                                <div class="invoice-box row">
                                    <div class="col-sm-12">
                                        <table class="table mt-0 table-responsive estimate-table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td><a href="{{ route('estimate.index') }}"><img class="img-fluid mb-3"
                                                                src="{{  \App\Models\Utility::get_file('uploads/logo/'.$logo) }}"
                                                                alt="Dashboard-kit Logo"></a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>{{$settings['company_name']}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        {{$settings['company_address']}} <br>
                                                        {{$settings['company_city']}}<br>
                                                        {{$settings['company_state']}}
                                                        {{$settings['company_zipcode']}}<br>
                                                        {{$settings['company_country']}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>{{$settings['company_telephone']}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="float-end">
                                    {!! DNS2D::getBarcodeHTML(route('pay.estimate',\Illuminate\Support\Facades\Crypt::encrypt($estimate->id)), "QRCODE",2,2) !!}
                               </div>
                            </div>
                        </div>

                        <div class="row invoive-info d-print-inline-flex">
                            @if(!empty($estimate->clientDetail))
                            <div class="col-sm-4 invoice-client-info">
                                <h6>{{ __('Invoice To :') }}</h6>
                                <h6 class="m-0">{{!empty($estimate->clientDetail->company_name)?$estimate->clientDetail->company_name:''}}</h6>
                                <p class="m-0 m-t-10">{{!empty($estimate->clientDetail->address_1)?$estimate->clientDetail->address_1:''}} <br> {{!empty($estimate->clientDetail->city)?$estimate->clientDetail->city:''}} <br> {{!empty($estimate->clientDetail->state)?$estimate->clientDetail->state:''}} <a class="text-dark" href="#" target="_top"><span class="__cf_email__"
                                    data-cfemail="6a0e0f07052a0d070b030644090507">{{!empty($estimate->clientDetail->zip_code)?$estimate->clientDetail->zip_code:''}}</span></a><br>{{!empty($estimate->clientDetail->country)?$estimate->clientDetail->country:''}}</p><br>
                                <p class="m-0">{{!empty($estimate->clientDetail->mobile)?$estimate->clientDetail->mobile:''}}</p>
                            </div>
                        @endif
                            <div class="col-sm-4">
                                <h6 class="m-b-20">{{ __('Order Details :') }}</h6>
                                <table class="table table-responsive mt-0 invoice-table invoice-order table-borderless">
                                    <tbody>
                                        <tr class="spaceUnder">
                                            <th>{{ __('Issue Date :') }}</th>
                                            <td>{{\Auth::user()->dateFormat($estimate->issue_date)}}</td>
                                        </tr>
                                        <tr  class="spaceUnderExpiry">
                                            <th>{{ __('Expiry Date : ') }}</th>
                                            <td>{{\Auth::user()->dateFormat($estimate->expiry_date)}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Status : ') }}</th>
                                            <td>
                                                @if($estimate->status == 0)
                                                    <span class="badge rounded-pill fix_badge  bg-primary">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                                                @elseif($estimate->status == 1)
                                                    <span class="badge rounded-pill fix_badge bg-info">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                                                @elseif($estimate->status == 2)
                                                    <span class="badge rounded-pill fix_badge bg-secondary">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                                                @elseif($estimate->status == 3)
                                                    <span class="badge rounded-pill fix_badge bg-danger">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                                                @elseif($estimate->status == 4)
                                                    <span class="badge rounded-pill fix_badge bg-warning">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                                                @elseif($estimate->status == 5)
                                                    <span class="badge rounded-pill fix_badge bg-success">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                                                @endif
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-4">
                                <h6 class="m-b-20">{{ __('Estimate No.') }}</h6>
                                <h6 class="text-uppercase text-primary">{{\Auth::user()->estimatenumberFormat($estimate->estimate)}}
                                </h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="table-responsive mb-4">
                                    <table class="table estimate-detail-table">
                                        <thead>
                                            <tr class="thead-default">
                                                <th>{{__('Item')}}</th>
                                                <th>{{__('Quantity')}}</th>
                                                <th>{{__('Rate')}}</th>
                                                <th>{{__('Tax')}}</th>
                                                @if($estimate->discount_apply==1)
                                                    <th>{{__('Discount')}}</th>
                                                @endif
                                                <th>{{__('Price')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalQuantity=0;
                                                $totalRate=0;
                                                $totalAmount=0;
                                                $totalTaxPrice=0;
                                                $totalDiscount=0;
                                                $taxesData=[];
                                            @endphp
                                            @foreach($estimate->items as $item)
                                            @php
                                                if(!empty($item->tax)){
                                                $taxes=\App\Models\Utility::tax($item->tax);
                                                    $totalQuantity+=$item->quantity;
                                                    $totalRate+=$item->price;
                                                    $totalDiscount+=$item->discount;

                                                    foreach($taxes as $taxe){
                                                        $taxDataPrice=\App\Models\Utility::taxRate($taxe->rate,$item->price,$item->quantity);
                                                        if (array_key_exists($taxe->name,$taxesData))
                                                        {
                                                            $taxesData[$taxe->name] = $taxesData[$taxe->name]+$taxDataPrice;
                                                        }
                                                        else
                                                        {
                                                            $taxesData[$taxe->name] = $taxDataPrice;
                                                        }
                                                    }
                                                }
                                            @endphp
                                                <tr>
                                                    <td>
                                                        <h6>{{!empty($item->items)?$item->items->name:'--'}}</h6>
                                                        <p>{{$item->description}}</p>
                                                    </td>
                                                    <td>{{$item->quantity}}</td>
                                                    <td>{{\Auth::user()->priceFormat($item->price)}}</td>
                                                    <td>
                                                        @if(!empty($item->tax))
                                                            @foreach($taxes as $tax)
                                                                @php
                                                                    $taxPrice=\Utility::taxRate($tax->rate,$item->price,$item->quantity);
                                                                    $totalTaxPrice+=$taxPrice;
                                                                @endphp
                                                                <a href="#!" class="d-block text-sm text-muted">{{$tax->name .' ('.$tax->rate .'%)'}} &nbsp;&nbsp;{{\Auth::user()->priceFormat($taxPrice)}}</a>
                                                            @endforeach
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    @if($estimate->discount_apply==1)
                                                        <td>{{\Auth::user()->priceFormat($item->discount)}} </td>
                                                    @endif
                                                    <td>{{\Auth::user()->priceFormat(($item->price*$item->quantity))}}</td>
                                                    @php
                                                        $totalQuantity+=$item->quantity;
                                                        $totalRate+=$item->price;
                                                        $totalDiscount+=$item->discount;
                                                        $totalAmount+=($item->price*$item->quantity);
                                                    @endphp
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="invoice-total">
                                    <table class="table invoice-table ">
                                        <tbody>
                                            <tr>
                                                <td class="px-0"></td>
                                                <th>{{ __('Sub Total :') }}</th>
                                                <td>{{\Auth::user()->priceFormat($estimate->getSubTotal())}}</td>
                                            </tr>

                                                <tr>

                                                        <td class="px-0"></td>

                                                    <th>{{__('Discount :')}}</th>
                                                    <td>{{\Auth::user()->priceFormat($estimate->getTotalDiscount())}}</td>
                                                </tr>

                                            @if(!empty($taxesData))
                                                @foreach($taxesData as $taxName => $taxPrice)

                                                    <tr>

                                                            <td class="px-0"></td>

                                                        <th>{{$taxName}}</th>
                                                        <td>{{ \Auth::user()->priceFormat($taxPrice) }}</td>
                                                    </tr>

                                                @endforeach
                                            @endif

                                                <tr>

                                                    <td class="px-0"></td>

                                                    <th>{{__('Total :')}}</th>
                                                    <td>{{\Auth::user()->priceFormat($estimate->getTotal())}}</td>
                                                </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Invoice ] end -->
    </div>
@endsection

