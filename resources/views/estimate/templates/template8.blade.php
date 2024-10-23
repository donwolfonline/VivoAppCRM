@php
$logo = Utility::GetLogo();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{   \App\Models\Utility::getValByName('SITE_RTL') == 'on'?'rtl':''}}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>New York - estimate</title>
        <link
            href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
            rel="stylesheet">

        <style type="text/css">
            :root {
                --theme-color: #003580;
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

            .estimate-preview-main {
                max-width: 700px;
                width: 100%;
                margin: 0 auto;
                background: #ffff;
                box-shadow: 0 0 10px #ddd;
            }

            .estimate-logo {
                max-width: 200px;
                width: 100%;
            }

            .estimate-header table td {
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

            .estimate-body {
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

            .estimate-summary td,
            .estimate-summary th {
                font-size: 13px;
                font-weight: 600;
            }

            .total-table td:last-of-type {
                width: 146px;
            }

            .estimate-footer {
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
            .estimate-summary p{
                margin-bottom: 0;
            }
        </style>
    </head>

    <body>
        <div class="estimate-preview-main">
            <div class="estimate-header">
                <table>
                    <tbody>
                        <tr style="border-bottom:1px solid {{ $color }};">
                            <td>
                                <img class="estimate-logo"
                                    src={{ $img }}
                                    alt="">
                            </td>
                            <td class="text-right">
                                <h3 style="text-transform: uppercase; font-size: 40px; font-weight: bold; color: {{ $color }};">{{ ('estimate') }}</h3>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="vertical-align-top">
                    <tbody>
                        <tr>
                            <td >
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
                            <td >
                                <table class="no-space">
                                    <tbody>
                                        <tr>
                                            <td colspan="2" >
                                                <div class="view-qrcode" style="margin-top: 0; margin-bottom: 15px; {{ $code == 'on' ?  'display:block' : 'display:none' }}">
                                                    <p> {!! DNS2D::getBarcodeHTML(route('pay.estimate',\Illuminate\Support\Facades\Crypt::encrypt($estimate->id)), "QRCODE",2,2) !!}</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{__('Number:') }} </td>
                                            <td class="text-right">{{\App\Models\Utility::estimateNumberFormat($settings,$estimate->estimate)}}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Issue Date:') }}</td>
                                            <td class="text-right">{{\App\Models\Utility::dateFormat($settings,$estimate->issue_date)}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="estimate-body">
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
                <table class="add-border estimate-summary" style="margin-top: 30px;">
                    <thead style="background-color: {{$color}};color:{{$font_color}} ">
                        <tr>
                            <th>{{__('Item')}}</th>
                            <th>{{__('Quantity')}}</th>
                            <th>{{__('Rate')}}</th>
                            <th>{{__('Tax')}}(%)</th>
                            <th>@if($estimate->discount_apply==1)
                                    <div class="d-table-th w-2">{{__('Discount')}}</div>
                                @endif
                            </th>
                            <th class="">{{__('Price')}} <small>{{__('before tax & discount')}}</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($estimate->items) && count($estimate->items) > 0)
                        @foreach($estimate->items as $key => $item)
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
                            <td>@if($estimate->discount_apply==1)
                                    {{($item->discount!=0)?\App\Models\Utility::priceFormat($settings,$item->discount):'-'}}
                                @endif
                            </td>
                            <td>{{\App\Models\Utility::priceFormat($settings,$item->price * $item->quantity)}}</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>{{__('Total')}}</td>
                            <td>{{$estimate->totalQuantity}}</td>
                            <td>{{\App\Models\Utility::priceFormat($settings,$estimate->totalRate)}}</td>
                            <td>{{\App\Models\Utility::priceFormat($settings,$estimate->totalTaxPrice) }}</td>
                            <td>@if($estimate->discount_apply==1)
                                    {{\App\Models\Utility::priceFormat($settings,$estimate->totalDiscount)}}
                                    @else
                                    --
                                    @endif
                            </td>
                            <td>
                                {{\App\Models\Utility::priceFormat($settings,$estimate->getSubTotal())}}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td colspan="2" class="sub-total">
                                <table class="total-table">
                                    @if($estimate->discount_apply==1)
                                        @if($estimate->getTotalDiscount())
                                            <tr>
                                                <td>{{__('Discount')}}: </td>
                                                <td>{{\App\Models\Utility::priceFormat($settings,$estimate->getTotalDiscount())}}</td>
                                            </tr>
                                        @endif
                                    @endif
                                    @if(!empty($estimate->taxesData))
                                        @foreach($estimate->taxesData as $taxName => $taxPrice)
                                        <tr>
                                            <td>{{$taxName}} :</td>
                                            <td>{{ \App\Models\Utility::priceFormat($settings,$taxPrice)  }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                     <tr>
                                        <td>{{__('Total')}}:</td>
                                        <td>
                                            {{\App\Models\Utility::priceFormat($settings,$estimate->getSubTotal()-$estimate->getTotalDiscount()+$estimate->getTotalTax())}}
                                        </td>
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
                <div class="estimate-footer">
                    @if(!isset($preview))
                        @include('estimate.script');
                    @endif
                    <p>Thanks!</p>
                </div>
            </div>
        </div>

    </body>

</html>
