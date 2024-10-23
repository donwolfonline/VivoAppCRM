<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{   \App\Models\Utility::getValByName('SITE_RTL') == 'on'?'rtl':''}}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sydney - invoice</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">


    <style type="text/css">
        :root {
            --theme-color: #511378;
            --white: #ffffff;
            --black: #000000;
        }

        body {
            font-family: 'Lato', sans-serif;
        }

        p,
        li,
        ul,
        ol {
            margin: 0;
            padding: 0;
            list-style: none;
            line-height: 1.5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tr th {
            padding: 0.75rem;
            text-align: left;
        }

        table tr td {
            padding: 0.75rem;
            text-align: left;
        }

        table th small {
            display: block;
            font-size: 12px;
        }

        .invoice-preview-main {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: #ffff;
            box-shadow: 0 0 10px #ddd;
        }

        .invoice-logo {
            max-width: 200px;
            width: 100%;
        }

        .invoice-header table td {
            padding: 15px 30px;
        }

        .text-right {
            text-align: right;
        }

        .no-space tr td {
            padding: 0;
        }

        .vertical-align-top td {
            vertical-align: top;
        }

        .view-qrcode {
            max-width: 114px;
            height: 114px;
            margin-left: auto;
            margin-top: 15px;
            background: var(--white);
        }

        .view-qrcode img {
            width: 100%;
            height: 100%;
        }

        .invoice-body {
            padding: 30px 25px 0;
        }

        table.add-border tr {
            border-top: 1px solid var(--theme-color);
        }

        tfoot tr:first-of-type {
            border-bottom: 1px solid var(--theme-color);
        }

        .total-table tr:first-of-type td {
            padding-top: 0;
        }

        .total-table tr:first-of-type {
            border-top: 0;
        }

        .sub-total {
            padding-right: 0;
            padding-left: 0;
        }

        .border-0 {
            border: none !important;
        }

        .invoice-summary td,
        .invoice-summary th {
            font-size: 13px;
            font-weight: 600;
        }

        .total-table td:last-of-type {
            width: 146px;
        }

        .invoice-footer {
            padding: 15px 20px;
        }

        .itm-description td {
            padding-top: 0;
        }
        html[dir="rtl"] table tr td,
        html[dir="rtl"] table tr th{
            text-align: right;
        }
        html[dir="rtl"]  .text-right{
            text-align: left;
        }
        html[dir="rtl"] .view-qrcode{
            margin-left: 0;
            margin-right: auto;
        }
        p:not(:last-of-type){
            margin-bottom: 15px;
        }
        .invoice-summary p{
            margin-bottom: 0;
        }
            .invoice-footer h6 {
            font-size: 45px;
            line-height: 1.2em;
            font-weight: 400;
            margin-top: 15px;
            color: var(--theme-color);
        }
    </style>
</head>

<body>
    <div class="invoice-preview-main" style="border-right:40px solid {{ $color }}">
        <div class="invoice-header">
            <table>
                <tbody>
                    <tr>
                        <td >
                            <h3 style="text-transform: uppercase; font-size: 40px; font-weight: bold; margin-bottom: 10px; color: {{ $color }};">{{ ('INVOICE') }}</h3>
                            {{-- <h3 style="text-transform: uppercase; font-size: 40px; font-weight: bold;  ">{{__('INVOICE') }}</h3> --}}
                            <table class="no-space" style="width: 70%;">
                                <tbody>
                                    <tr>
                                        <td>{{__('Number:') }} </td>
                                        <td class="text-right">{{\App\Models\Utility::invoiceNumberFormat($settings,$invoice->invoice)}}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Issue Date:') }}</td>
                                        <td class="text-right">{{\App\Models\Utility::dateFormat($settings,$invoice->issue_date)}}</td>
                                    </tr>

                                </tbody>
                            </table>
                        </td>
                        <td class="text-right">
                            <img class="invoice-logo" src={{ $img }} alt="">
                        </td>
                     </tr>
                </tbody>
            </table>
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td>
                            <p>
                                @if($settings['company_name']){{$settings['company_name']}}@endif<br>
                                @if($settings['company_address']){{$settings['company_address']}}@endif
                                @if($settings['company_city']) <br> {{$settings['company_city']}}, @endif
                                @if($settings['company_state']){{$settings['company_state']}}@endif
                                @if($settings['company_zipcode']) - {{$settings['company_zipcode']}}@endif
                                @if($settings['company_country']) <br>{{$settings['company_country']}}@endif <br>
                            </p>
                            <p>
                                {{__('Registration Number')}} : {{$settings['registration_number']}} <br>
                                {{__('VAT Number')}} : {{$settings['vat_number']}} <br>
                            </p>
                        </td>
                        <td>
                            <table class="no-space">
                                <tbody>
                                    <tr>
                                        <td colspan="2">
                                            <div class="view-qrcode" style="margin-top: 0; {{ $code == 'on' ?  'display:block' : 'display:none' }}">
                                                <p> {!! DNS2D::getBarcodeHTML(route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)), "QRCODE",2,2) !!}</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table>
                <tbody>
                    <tr>
                        <td>
                            <strong style="margin-bottom: 10px; display:block;">Bill To:</strong>
                            <p> {{!empty($client->company_name)?$client->company_name:''}}<br>
                                {{!empty($client->name)?$client->name:''}}<br>
                                {{!empty($client->email)?$client->email:''}}<br>
                                {{!empty($client->mobile)?$client->mobile:''}}<br>
                                {{!empty($client->address)?$client->address:''}}<br>
                                {{!empty($client->zip)?$client->zip:''}}<br>
                                {{!empty($client->city)?$client->city:'' . ', '}} {{!empty($client->state)?$client->state:'' .', '}},{{!empty($client->country)?$client->country:''}}
                            </p>
                        </td>
                        <td class="text-right">
                            <strong style="margin-bottom: 10px; display:block;">Ship To:</strong>
                            <p> {{!empty($client->company_name)?$client->company_name:''}}<br>
                                {{!empty($client->name)?$client->name:''}}<br>
                                {{!empty($client->email)?$client->email:''}}<br>
                                {{!empty($client->mobile)?$client->mobile:''}}<br>
                                {{!empty($client->address)?$client->address:''}}<br>
                                {{!empty($client->zip)?$client->zip:''}}<br>
                                {{!empty($client->city)?$client->city:'' . ', '}} {{!empty($client->state)?$client->state:'' .', '}},{{!empty($client->country)?$client->country:''}}
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="invoice-body" style="padding-right: 0;">
            <table class="add-border invoice-summary">
                <thead style="background-color: {{$color}};color:{{$font_color}}">
                    <tr>
                        <th>{{__('Item')}}</th>
                        <th>{{__('Quantity')}}</th>
                        <th>{{__('Rate')}}</th>
                        <th>{{__('Tax')}}(%)</th>
                        <th>{{__('Discount')}}</th>
                        <th class="">{{__('Price')}} <small>{{__('before tax & discount')}}</small></th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($invoice->items) && count($invoice->items) > 0)
                    @foreach($invoice->items as $key => $item)
                    <tr>
                        <td>{{$item->name}}</td>
                        <td>{{$item->quantity}}</td>
                        <td>{{\App\Models\Utility::priceFormat($settings,$item->price)}}</td>
                        <td>
                        @foreach($item->itemTax as $taxes)
                            @if(!empty($item->itemTax))
                            <p>
                                <span>{{$taxes['name']}}</span>  <span>({{$taxes['rate']}})</span> <span>{{$taxes['price']}}</span>
                            </p>
                            @else
                            <p>-</p>
                            @endif
                        @endforeach
                        </td>
                        <td>{{($item->discount!=0)?\App\Models\Utility::priceFormat($settings,$item->discount):'-'}}</td>
                        <td>{{\App\Models\Utility::priceFormat($settings,$item->price * $item->quantity)}}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td>{{__('Total')}}</td>
                        <td>{{$invoice->totalQuantity}}</td>
                        <td>{{\App\Models\Utility::priceFormat($settings,$invoice->totalRate)}}</td>
                        <td>{{\App\Models\Utility::priceFormat($settings,$invoice->totalTaxPrice) }}</td>
                        <td>{{ ($invoice->totalDiscount!=0)?\App\Models\Utility::priceFormat($settings,$invoice->totalDiscount):'-'}}</td>
                        <td>{{\App\Models\Utility::priceFormat($settings,$invoice->getSubTotal())}}</td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td colspan="2" class="sub-total">
                            <table class="total-table">
                                @if($invoice->getTotalDiscount())
                                    <tr>
                                        <td>{{__('Discount')}}: </td>
                                        <td>{{\App\Models\Utility::priceFormat($settings,$invoice->getTotalDiscount())}}</td>
                                    </tr>
                                @endif
                                @if(!empty($invoice->taxesData))
                                @foreach($invoice->taxesData as $taxName => $taxPrice)
                                <tr>
                                    <td>{{$taxName}} :</td>
                                    <td>{{ \App\Models\Utility::priceFormat($settings,$taxPrice)  }}</td>
                                </tr>
                                @endforeach
                                @endif
                                 <tr>
                                    <td>{{__('Total')}}:</td>
                                    <td>{{\App\Models\Utility::priceFormat($settings,$invoice->getSubTotal()-$invoice->getTotalDiscount()+$invoice->getTotalTax())}}</td>
                                </tr>
                                <tr>
                                    <td>{{__('Credit Note')}}:</td>
                                    <td>{{\App\Models\Utility::priceFormat($settings,$invoice->getSubTotal()-$invoice->getTotalDiscount()+$invoice->getTotalTax())}}</td>
                                </tr>
                                <tr>
                                    <td>{{__('Paid')}}:</td>
                                    <td>@if (\Auth::check()){{Auth::user()->priceFormat(($invoice->getTotal()-$invoice->getDue()-$invoice->invoiceCreditNote()))}}
                                        @else{{ \App\Models\User::priceFormat(($invoice->getTotal()-$invoice->getDue()-$invoice->invoiceCreditNote()))}}@endif</td>
                                </tr>
                                <tr>
                                    <td>{{__('Due')}}:</td>
                                    <td>@if (\Auth::check()){{Auth::user()->priceFormat($invoice->getDue())}}
                                        @else  {{\App\Models\User::priceFormat($invoice->getDue())}} @endif</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div data-v-f2a183a6="" class="d-body1">
                <p data-v-f2a183a6="">
                    {{$settings['footer_title']}} <br>
                    {{$settings['footer_notes']}}
                </p>
            </div>
            <div data-v-4b3dcb8a="" class="break-25"></div>
            <div class="invoice-footer">
                @if(!isset($preview))
                    @include('invoice.script');
                @endif
                <p>Thanks!</p>
            </div>
        </div>
    </div>

</body>

</html>
