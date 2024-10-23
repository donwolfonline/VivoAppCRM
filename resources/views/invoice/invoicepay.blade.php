@extends('layouts.invoicepayheader')
@php
    $logo = Utility::GetLogo();
    $logos = \App\Models\Utility::get_file('uploads/logo/');
    // $logo = \App\Models\Utility::get_file('uploads/logo/');
    $dark_logo = Utility::getValByName('company_logo_dark');
    $settings = Utility::settings();

@endphp

@section('page-title')
    {{ __('Invoice') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="mb-0 h4 d-inline-block font-weight-400 ">{{ __('Invoice') }} </h5>

    </div>
@endsection

@section('action-btn')
    <a href="{{ route('invoice.pdf', \Crypt::encrypt($invoice->id)) }}" target="_blank"
        class="m-1 btn btn-sm btn-primary btn-icon">
        <span class="btn-inner--icon"><i class="ti ti-printer"></i></span>
        <span class="btn-inner--text">{{ __('Print') }}</span>
    </a>
    @if ($invoice->getDue() > 0)
        <a href="#" data-bs-toggle="modal" data-bs-target="#paymentModal" class="m-1 btn btn-sm btn-primary btn-icon">
            <span class="text-white btn-inner--icon"><i class="fa fa-credit-card"></i></span>
            <span class="text-white btn-inner--text">{{ __(' Pay Now') }}</span>
        </a>
    @endif
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
                                        <table class="table mt-0 table-responsive invoice-table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        @if (Utility::getValByName('cust_darklayout') == 'on')
                                                            <img src="{{ $logos . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-light.png') }}"
                                                                alt="" class="img-fluid" />
                                                        @else
                                                            <img src="{{ $logos . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png') }}"
                                                                alt="" class="img-fluid" />
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>{{ $company_setting['company_name'] }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        {{ $company_setting['company_address'] }} <br>
                                                        {{ $company_setting['company_city'] }}<br>
                                                        {{ $company_setting['company_state'] }}
                                                        {{ $company_setting['company_zipcode'] }} <br>
                                                        {{ $company_setting['company_country'] }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>{{ $company_setting['company_telephone'] }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="float-end"
                                    style="display:{{ $company_setting['invoice_qrcode'] == 'on' ? 'block' : 'none' }};">

                                    {!! DNS2D::getBarcodeHTML(
                                        route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)),
                                        'QRCODE',
                                        2,
                                        2,
                                    ) !!}

                                </div>
                            </div>
                        </div>


                        <div class="row invoive-info d-print-inline-flex">
                            @if (!empty($invoice->clientDetail))
                                <div class="col-sm-4 invoice-client-info">
                                    <h6>{{ __('Invoice To :') }}</h6>
                                    <h6 class="m-0">
                                        {{ !empty($invoice->clientDetail->company_name) ? $invoice->clientDetail->company_name : '' }}
                                    </h6>

                                    <p class="m-0 m-t-10">
                                        {{ !empty($invoice->clientDetail->address_1) ? $invoice->clientDetail->address_1 : '' }}
                                        <br>{{ !empty($invoice->clientDetail->city) ? $invoice->clientDetail->city : '' }}
                                        <br>
                                        {{ !empty($invoice->clientDetail->state) ? $invoice->clientDetail->state : '' }}
                                        <a class="text-secondary" href="$" target="_top"><span class="__cf_email__"
                                                data-cfemail="6a0e0f07052a0d070b030644090507">
                                                {{ !empty($invoice->clientDetail->zip_code) ? $invoice->clientDetail->zip_code : '' }}</span>
                                        </a>
                                        <br>
                                        {{ !empty($invoice->clientDetail->country) ? $invoice->clientDetail->country : '' }}
                                    </p><br>
                                    <p class="m-0">
                                        {{ !empty($invoice->clientDetail->mobile) ? $invoice->clientDetail->mobile : '' }}
                                    </p>
                                </div>
                            @endif
                            <div class="col-sm-4">
                                <h6 class="m-b-20">{{ __('Order Details :') }}</h6>
                                <table class="table mt-0 table-responsive invoice-table invoice-order table-borderless">
                                    <tbody>
                                        <tr>
                                            <th>{{ __('Issue Date :') }}</th>
                                            <td>
                                                @if (\Auth::check())
                                                    {{ \Auth::user()->dateFormat($invoice->issue_date) }}
                                                @else
                                                    {{ \App\Models\User::dateFormat($invoice->issue_date) }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Expiry Date : ') }}</th>
                                            <td>
                                                @if (\Auth::check())
                                                    {{ \Auth::user()->dateFormat($invoice->due_date) }}
                                                @else
                                                    {{ \App\Models\User::dateFormat($invoice->due_date) }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Status : ') }}</th>
                                            <td>
                                                @if ($invoice->status == 0)
                                                    <span
                                                        class="rounded badge bg-primary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 1)
                                                    <span
                                                        class="rounded badge bg-info">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 2)
                                                    <span
                                                        class="rounded badge bg-secondary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 3)
                                                    <span
                                                        class="rounded badge bg-danger">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 4)
                                                    <span
                                                        class="rounded badge bg-warning">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 5)
                                                    <span
                                                        class="rounded badge bg-success">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @endif
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-4">
                                <h6 class="m-b-20">{{ __('Invoice No.') }}</h6>
                                <h6 class="text-uppercase text-primary">
                                    @if (\Auth::check())
                                        {{ \Auth::user()->invoicenumberFormat($invoice->invoice_id) }}
                                    @else
                                        {{ \App\Models\Utility::invoicenumberFormat($settings, $invoice->invoice_id) }}
                                    @endif

                                </h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="mb-4 table-responsive">
                                    <table class="table invoice-detail-table">
                                        <thead>
                                            <tr class="thead-default">
                                                <th>{{ __('Item') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Rate') }}</th>
                                                <th>{{ __('Tax') }}</th>
                                                <th>{{ __('Discount') }}</th>
                                                <th>{{ __('Price') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalQuantity = 0;
                                                $totalRate = 0;
                                                $totalAmount = 0;
                                                $totalTaxPrice = 0;
                                                $totalDiscount = 0;
                                                $taxesData = [];
                                            @endphp
                                            @foreach ($invoice->items as $item)
                                                @php
                                                    if (!empty($item->tax)) {
                                                        $taxes = \Utility::tax($item->tax);
                                                        $totalQuantity += $item->quantity;
                                                        $totalRate += $item->price;
                                                        $totalDiscount += $item->discount;

                                                        foreach ($taxes as $taxe) {
                                                            $taxDataPrice = \Utility::taxRate(
                                                                $taxe->rate,
                                                                $item->price,
                                                                $item->quantity,
                                                            );
                                                            if (array_key_exists($taxe->name, $taxesData)) {
                                                                $taxesData[$taxe->name] =
                                                                    $taxesData[$taxe->name] + $taxDataPrice;
                                                            } else {
                                                                $taxesData[$taxe->name] = $taxDataPrice;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <h6>{{ !empty($item->items) ? $item->items->name : '-' }}</h6>
                                                        <p>{{ $item->description }}</p>
                                                    </td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>
                                                        @if (\Auth::check())
                                                            {{ \Auth::user()->priceFormat($item->price) }}
                                                        @else
                                                            {{ \App\Models\User::priceFormat($item->price) }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (!empty($item->tax))
                                                            @foreach ($taxes as $tax)
                                                                @php
                                                                    $taxPrice = \Utility::taxRate(
                                                                        $tax->rate,
                                                                        $item->price,
                                                                        $item->quantity,
                                                                    );
                                                                    $totalTaxPrice += $taxPrice;
                                                                @endphp
                                                                <a href="#!"
                                                                    class="text-sm d-block text-muted">{{ $tax->name . ' (' . $tax->rate . '%)' }}
                                                                    &nbsp;&nbsp; @if (\Auth::check())
                                                                        {{ \Auth::user()->priceFormat($item->price) }}
                                                                    @else
                                                                        {{ \App\Models\User::priceFormat($item->price) }}
                                                                    @endif
                                                                </a>
                                                            @endforeach
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (\Auth::check())
                                                            {{ \Auth::user()->priceFormat($item->discount) }}
                                                        @else
                                                            {{ \App\Models\User::priceFormat($item->discount) }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (\Auth::check())
                                                            {{ \Auth::user()->priceFormat($item->price * $item->quantity) }}
                                                        @else
                                                            {{ \App\Models\User::priceFormat($item->price * $item->quantity) }}
                                                        @endif
                                                    </td>
                                                    @php
                                                        $totalQuantity += $item->quantity;
                                                        $totalRate += $item->price;
                                                        $totalDiscount += $item->discount;
                                                        $totalAmount += $item->price * $item->quantity;
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
                                                <th>{{ __('Sub Total :') }}</th>
                                                <td>
                                                    @if (\Auth::check())
                                                        {{ \Auth::user()->priceFormat($invoice->getSubTotal()) }}
                                                    @else
                                                        {{ \App\Models\User::priceFormat($invoice->getSubTotal()) }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Discount :') }}</th>
                                                <td>
                                                    @if (\Auth::check())
                                                        {{ \Auth::user()->priceFormat($invoice->getTotalDiscount()) }}
                                                    @else
                                                        {{ \App\Models\User::priceFormat($invoice->getTotalDiscount()) }}
                                                    @endif
                                                </td>
                                            </tr>
                                            @if (!empty($taxesData))
                                                @foreach ($taxesData as $taxName => $taxPrice)
                                                    <tr>

                                                        <th>{{ $taxName }}</th>
                                                        <td>
                                                            @if (\Auth::check())
                                                                {{ \Auth::user()->priceFormat($taxPrice) }}
                                                            @else
                                                                {{ \App\Models\User::priceFormat($taxPrice) }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif

                                            <tr>
                                                <th>{{ __('Total :') }}</th>
                                                <td>
                                                    @if (\Auth::check())
                                                        {{ \Auth::user()->priceFormat($invoice->getTotal()) }}
                                                    @else
                                                        {{ \App\Models\User::priceFormat($invoice->getTotal()) }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Credit Note :') }}</th>
                                                <td>
                                                    @if (\Auth::check())
                                                        {{ \Auth::user()->priceFormat($invoice->invoiceCreditNote()) }}
                                                    @else
                                                        {{ \App\Models\User::priceFormat($invoice->invoiceCreditNote()) }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Paid :') }}</th>
                                                <td>
                                                    @if (\Auth::check())
                                                        {{ \Auth::user()->priceFormat($invoice->getTotal() - $invoice->getDue() - $invoice->invoiceCreditNote()) }}
                                                    @else
                                                        {{ \App\Models\User::priceFormat($invoice->getTotal() - $invoice->getDue() - $invoice->invoiceCreditNote()) }}
                                                    @endif

                                                </td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Due :') }}</th>
                                                <td>
                                                    @if (\Auth::check())
                                                        {{ \Auth::user()->priceFormat($invoice->getDue()) }}
                                                    @else
                                                        {{ \App\Models\User::priceFormat($invoice->getDue()) }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <hr />
                                                    <h5 class="text-primary m-r-10">{{ __('Total :') }}</h5>
                                                </td>
                                                <td>
                                                    <hr />
                                                    <h5 class="text-primary">
                                                        @if (\Auth::check())
                                                            {{ \Auth::user()->priceFormat($invoice->getTotal()) }}
                                                        @else
                                                            {{ \App\Models\User::priceFormat($invoice->getTotal()) }}
                                                        @endif
                                                    </h5>
                                                </td>
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

    <div class="row">
        <div class="col-sm-12">
            <div class="mb-4 card table-responsive">
                <div class="card-header">
                    <h5>{{ __('Receipt Summary') }}</h5>
                    @if ($user_storage >= $plan_storage)
                        <span
                            class="text-danger"><small>{{ __('Your plan storage limit is over , so you can not see customer uploaded payment receipt.') }}</small></span>
                    @endif
                </div>
                <table class="table card-body invoice-detail-table">
                    <thead>
                        <tr class="thead-default">
                            <th>{{ __('Transaction ID') }}</th>
                            <th>{{ __('Payment Date') }}</th>
                            <th>{{ __('Payment Method') }}</th>
                            <th>{{ __('Payment Type') }}</th>
                            <th>{{ __('Note') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Action') }}</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->payments as $payment)
                            <tr>
                                <td>{{ $payment->transaction }} </td>
                                <td>
                                    @if (\Auth::check())
                                        {{ \Auth::user()->dateFormat($payment->date) }}
                                    @else
                                        {{ \App\Models\User::dateFormat($payment->date) }}
                                    @endif
                                </td>
                                <td>{{ !empty($payment->payments) ? $payment->payments->name : '' }} </td>
                                <td>{{ $payment->payment_type }} </td>
                                <td>{{ $payment->notes }} </td>
                                <td>
                                    @if (\Auth::check())
                                        {{ \Auth::user()->priceFormat($payment->amount) }}
                                    @else
                                        {{ \App\Models\User::priceFormat($payment->amount) }}
                                    @endif
                                </td>
                                <td>
                                    @if ($user_storage >= $plan_storage)
                                        --
                                    @else
                                        @if (!empty($payment->receipt))
                                            @php
                                                $x = pathinfo($payment->receipt, PATHINFO_FILENAME);
                                                $extension = pathinfo($payment->receipt, PATHINFO_EXTENSION);
                                                $result = str_replace(['#', "'", ';'], '', $payment->receipt);

                                            @endphp
                                            <a href="{{ route('invoice.receipt', [$x, "$extension"]) }}"
                                                data-toggle="tooltip" class="btn btn-sm btn-primary btn-icon rounded-pill">
                                                <i class="ti ti-download" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Download') }}"></i>
                                            </a>
                                            <a href="{{ asset(Storage::url('uploads/attachment/' . $x . '.' . $extension)) }}"
                                                target="_blank" data-toggle="tooltip"
                                                class="btn btn-sm btn-secondary btn-icon rounded-pill">
                                                <i class="ti ti-crosshair" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Preview') }}"></i>
                                            </a>
                                        @else
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @foreach ($banktransfer as $bank_payment)
                            <tr>
                                <td>{{ $bank_payment->order_id }} </td>
                                <td>{{ \App\Models\Utility::dateFormat($settings, $bank_payment->date) }} </td>
                                <td>{{ '-' }} </td>
                                <td> {{ __('Bank Transfer') }} </td>
                                <td>
                                    {{ \App\Models\Utility::invoiceNumberFormat($settings, $invoice->invoice_id) }}
                                </td>
                                <td>{{ \App\Models\Utility::priceFormat($settings, $bank_payment->amount) }} </td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($invoice->getDue() > 0)

        <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">{{ __('Add Payment') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- <div class="card"> -->
                        <ul class="mb-3 nav nav-pills" role="tablist">
                            @if (isset($payment_setting['is_bank_transfer_enabled']) && $payment_setting['is_bank_transfer_enabled'] == 'on')
                                @if (isset($payment_setting['bank_details']) && !empty($payment_setting['bank_details']))
                                    <li class="mb-2 nav-item">
                                        <a href="#banktransfer-payment" class="btn btn-outline-primary btn-sm active"
                                            aria-controls="banktransfer" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">
                                            {{ __('BankTransfer') }}
                                        </a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_stripe_enabled']) && $payment_setting['is_stripe_enabled'] == 'on')
                                @if (isset($payment_setting['stripe_key']) &&
                                        !empty($payment_setting['stripe_key']) &&
                                        (isset($payment_setting['stripe_secret']) && !empty($payment_setting['stripe_secret'])))
                                    <li class="mb-2 nav-item">
                                        <a href="#stripe-payment" class="ml-1 btn btn-outline-primary btn-sm"
                                            data-bs-toggle="tab" role="tab" aria-selected="false">
                                            {{ __('Stripe') }}
                                        </a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_paypal_enabled']) && $payment_setting['is_paypal_enabled'] == 'on')
                                @if (isset($payment_setting['paypal_client_id']) &&
                                        !empty($payment_setting['paypal_client_id']) &&
                                        (isset($payment_setting['paypal_secret_key']) && !empty($payment_setting['paypal_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#paypal-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="paypal" aria-selected="false">{{ __('Paypal') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_paystack_enabled']) && $payment_setting['is_paystack_enabled'] == 'on')
                                @if (isset($payment_setting['paystack_public_key']) &&
                                        !empty($payment_setting['paystack_public_key']) &&
                                        (isset($payment_setting['paystack_secret_key']) && !empty($payment_setting['paystack_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#paystack-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="paystack" aria-selected="false">{{ __('Paystack') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_flutterwave_enabled']) && $payment_setting['is_flutterwave_enabled'] == 'on')
                                @if (isset($payment_setting['flutterwave_secret_key']) &&
                                        !empty($payment_setting['flutterwave_secret_key']) &&
                                        (isset($payment_setting['flutterwave_public_key']) && !empty($payment_setting['flutterwave_public_key'])))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#flutterwave-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="flutterwave"
                                            aria-selected="false">{{ __('Flutterwave') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_razorpay_enabled']) && $payment_setting['is_razorpay_enabled'] == 'on')
                                @if (isset($payment_setting['razorpay_public_key']) &&
                                        !empty($payment_setting['razorpay_public_key']) &&
                                        (isset($payment_setting['razorpay_secret_key']) && !empty($payment_setting['razorpay_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#razorpay-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="razorpay" aria-selected="false">{{ __('Razorpay') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_mercado_enabled']) && $payment_setting['is_mercado_enabled'] == 'on')
                                @if (isset($payment_setting['mercado_access_token']) && !empty($payment_setting['mercado_access_token']))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#mercado-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="mercado" aria-selected="false">{{ __('Mercado Pago') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_paytm_enabled']) && $payment_setting['is_paytm_enabled'] == 'on')
                                @if (isset($payment_setting['paytm_merchant_id']) &&
                                        !empty($payment_setting['paytm_merchant_id']) &&
                                        (isset($payment_setting['paytm_merchant_key']) && !empty($payment_setting['paytm_merchant_key'])))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#paytm-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="paytm" aria-selected="false">{{ __('Paytm') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_mollie_enabled']) && $payment_setting['is_mollie_enabled'] == 'on')
                                @if (isset($payment_setting['mollie_api_key']) &&
                                        !empty($payment_setting['mollie_api_key']) &&
                                        (isset($payment_setting['mollie_profile_id']) && !empty($payment_setting['mollie_profile_id'])))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#mollie-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="mollie" aria-selected="false">{{ __('Mollie') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_skrill_enabled']) && $payment_setting['is_skrill_enabled'] == 'on')
                                @if (isset($payment_setting['skrill_email']) && !empty($payment_setting['skrill_email']))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#skrill-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="skrill" aria-selected="false">{{ __('Skrill') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_coingate_enabled']) && $payment_setting['is_coingate_enabled'] == 'on')
                                @if (isset($payment_setting['coingate_auth_token']) && !empty($payment_setting['coingate_auth_token']))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#coingate-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="coingate" aria-selected="false">{{ __('CoinGate') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_paymentwall_enabled']) && $payment_setting['is_paymentwall_enabled'] == 'on')
                                @if (isset($payment_setting['paymentwall_public_key']) &&
                                        !empty($payment_setting['paymentwall_public_key']) &&
                                        (isset($payment_setting['paymentwall_private_key']) && !empty($payment_setting['paymentwall_private_key'])))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#paymentwall-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="paymentwall"
                                            aria-selected="false">{{ __('PaymentWall') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_toyyibpay_enabled']) && $payment_setting['is_toyyibpay_enabled'] == 'on')
                                @if (isset($payment_setting['toyyibpay_secret_key']) &&
                                        !empty($payment_setting['toyyibpay_secret_key']) &&
                                        (isset($payment_setting['category_code']) && !empty($payment_setting['category_code'])))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#toyyibpay-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="toyyibpay" aria-selected="false">{{ __('Toyyibpay') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_payfast_enabled']) && $payment_setting['is_payfast_enabled'] == 'on')
                                @if (isset($payment_setting['payfast_merchant_id']) &&
                                        !empty($payment_setting['payfast_merchant_id']) &&
                                        (isset($payment_setting['payfast_merchant_key']) && !empty($payment_setting['payfast_merchant_key'])))
                                    <li class="mb-2 nav-item">
                                        <a href="#payfast-payment" class="ml-1 btn btn-outline-primary btn-sm"
                                            id="pills-payfast-tab" data-bs-toggle="pill" role="tab"
                                            aria-controls="payfast" aria-selected="false">{{ __('Payfast') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_iyzipay_enabled']) && $payment_setting['is_iyzipay_enabled'] == 'on')
                                @if (isset($payment_setting['iyzipay_public_key']) &&
                                        !empty($payment_setting['iyzipay_public_key']) &&
                                        (isset($payment_setting['iyzipay_secret_key']) && !empty($payment_setting['iyzipay_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#iyzipay-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="iyzipay" aria-selected="false">{{ __('Iyzipay') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_sspay_enabled']) && $payment_setting['is_sspay_enabled'] == 'on')
                                @if (isset($payment_setting['sspay_secret_key']) &&
                                        !empty($payment_setting['sspay_secret_key']) &&
                                        (isset($payment_setting['sspay_category_code']) && !empty($payment_setting['sspay_category_code'])))
                                    <li class="mb-2 nav-item">
                                        <a data-bs-toggle="tab" href="#sspay-payment"
                                            class="ml-1 btn btn-outline-primary btn-sm" role="tab"
                                            aria-controls="sspay" aria-selected="false">{{ __('Sspay') }}</a>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_paytab_enabled']) && $payment_setting['is_paytab_enabled'] == 'on')
                                @if (isset($payment_setting['paytab_profile_id']) &&
                                        !empty($payment_setting['paytab_profile_id']) &&
                                        (isset($payment_setting['paytab_server_key']) && !empty($payment_setting['paytab_server_key'])) &&
                                        (isset($payment_setting['paytab_region']) && !empty($payment_setting['paytab_region'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#paytab-payment" role="tab"
                                            aria-controls="paytab" type="button"
                                            aria-selected="false">{{ __('PayTab') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_benefit_enabled']) && $payment_setting['is_benefit_enabled'] == 'on')
                                @if (isset($payment_setting['benefit_api_key']) &&
                                        !empty($payment_setting['benefit_api_key']) &&
                                        (isset($payment_setting['benefit_secret_key']) && !empty($payment_setting['benefit_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#benefit-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('Benefit') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_cashfree_enabled']) && $payment_setting['is_cashfree_enabled'] == 'on')
                                @if (isset($payment_setting['cashfree_api_key']) &&
                                        !empty($payment_setting['cashfree_api_key']) &&
                                        (isset($payment_setting['cashfree_secret_key']) && !empty($payment_setting['cashfree_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#cashfree-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('Cashfree') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_aamarpay_enabled']) && $payment_setting['is_aamarpay_enabled'] == 'on')
                                @if (isset($payment_setting['aamarpay_store_id']) &&
                                        !empty($payment_setting['aamarpay_store_id']) &&
                                        (isset($payment_setting['aamarpay_signature_key']) && !empty($payment_setting['aamarpay_signature_key'])) &&
                                        (isset($payment_setting['aamarpay_description']) && !empty($payment_setting['aamarpay_description'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#aamarpay-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('Aamarpay') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_paytr_enabled']) && $payment_setting['is_paytr_enabled'] == 'on')
                                @if (isset($payment_setting['paytr_merchant_id']) &&
                                        !empty($payment_setting['paytr_merchant_id']) &&
                                        (isset($payment_setting['paytr_merchant_key']) && !empty($payment_setting['paytr_merchant_key'])) &&
                                        (isset($payment_setting['paytr_merchant_salt']) && !empty($payment_setting['paytr_merchant_salt'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#paytr-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('PayTr') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif


                            @if (isset($payment_setting['is_yookassa_enabled']) && $payment_setting['is_yookassa_enabled'] == 'on')
                                @if (isset($payment_setting['is_yookassa_enabled']) &&
                                        !empty($payment_setting['is_yookassa_enabled']) &&
                                        (isset($payment_setting['yookassa_shop_id']) && !empty($payment_setting['yookassa_shop_id'])) &&
                                        (isset($payment_setting['yookassa_secret_key']) && !empty($payment_setting['yookassa_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#yookassa-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('Yookassa') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif
                            @if (isset($payment_setting['is_midtrans_enabled']) && $payment_setting['is_midtrans_enabled'] == 'on')
                                @if (isset($payment_setting['is_midtrans_enabled']) &&
                                        !empty($payment_setting['is_midtrans_enabled']) &&
                                        (isset($payment_setting['midtrans_secret']) && !empty($payment_setting['midtrans_secret'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#midtrans-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('Midtrans') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif
                            @if (isset($payment_setting['is_xendit_enabled']) && $payment_setting['is_xendit_enabled'] == 'on')
                                @if (isset($payment_setting['is_xendit_enabled']) &&
                                        !empty($payment_setting['is_xendit_enabled']) &&
                                        (isset($payment_setting['xendit_api_key']) && !empty($payment_setting['xendit_api_key'])) &&
                                        (isset($payment_setting['xendit_token']) && !empty($payment_setting['xendit_token'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#xendit-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('Xendit') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif


                            @if (isset($payment_setting['is_PaiementPro_enabled']) && $payment_setting['is_PaiementPro_enabled'] == 'on')
                                @if (isset($payment_setting['is_PaiementPro_enabled']) &&
                                        !empty($payment_setting['is_PaiementPro_enabled']) &&
                                        (isset($payment_setting['PaiementPro_merchant_key']) && !empty($payment_setting['PaiementPro_merchant_key'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#PaiementPro-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('PaiementPro') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif




                            @if (isset($payment_setting['is_nepalste_enabled']) && $payment_setting['is_nepalste_enabled'] == 'on')
                                @if (isset($payment_setting['is_nepalste_enabled']) &&
                                        !empty($payment_setting['nepalste_public_key']) &&
                                        (isset($payment_setting['nepalste_secret_key']) && !empty($payment_setting['nepalste_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#nepalste-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('Nepalste') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_CinetPay_enabled']) && $payment_setting['is_CinetPay_enabled'] == 'on')
                                @if (isset($payment_setting['is_CinetPay_enabled']) &&
                                        !empty($payment_setting['CinetPay_api_key']) &&
                                        (isset($payment_setting['CinetPay_secret_key']) && !empty($payment_setting['CinetPay_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#CinetPay-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('CinetPay') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_Fedapay_enabled']) && $payment_setting['is_Fedapay_enabled'] == 'on')
                                @if (isset($payment_setting['is_Fedapay_enabled']) &&
                                        !empty($payment_setting['Fedapay_public_key']) &&
                                        (isset($payment_setting['Fedapay_secret_key']) && !empty($payment_setting['Fedapay_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#Fedapay-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('Fedapay') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif

                            @if (isset($payment_setting['is_PayHere_enabled']) && $payment_setting['is_PayHere_enabled'] == 'on')
                                @if (isset($payment_setting['is_PayHere_enabled']) &&
                                        !empty($payment_setting['PayHere_public_id']) &&
                                        (isset($payment_setting['PayHere_secret_key']) && !empty($payment_setting['PayHere_secret_key'])) &&
                                        !empty($payment_setting['PayHere_merchant_id']) &&
                                        (isset($payment_setting['PayHere_merchant_secret_key']) &&
                                            !empty($payment_setting['PayHere_merchant_secret_key'])))
                                    <li class="mb-2 nav-item">
                                        <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                            data-bs-toggle="pill" data-bs-target="#PayHere-payment" role="tab"
                                            aria-controls="benefit" type="button"
                                            aria-selected="false">{{ __('PayHere') }}</button>
                                    </li>&nbsp;
                                @endif
                            @endif
                        </ul>

                        <div class="tab-content">

                            <div class="tab-pane fade show active" id="banktransfer-payment" role="tabpanel"
                                aria-labelledby="banktransfer-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_bank_transfer_enabled']) && $payment_setting['is_bank_transfer_enabled'] == 'on')
                                        @if (isset($payment_setting['bank_details']) && !empty($payment_setting['bank_details']))
                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form" action="{{ route('invoice.pay.with.banktransfer') }}"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                {!! isset($payment_setting['bank_details']) ? $payment_setting['bank_details'] : '' !!}
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="payment_receipt"
                                                                    class="form-label">{{ __('Payment Receipt :') }}</label>
                                                                <input type="file" name="payment_receipt"
                                                                    class="form-control">
                                                            </div>
                                                            @error('payment_receipt')
                                                                <span class="text-xs invalid-payment_receipt text-danger"
                                                                    role="alert">{{ $messages }}</span>
                                                            @enderror
                                                        </div><br>

                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span> {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                        @error('amount')
                                                            <span class="text-xs invalid-amount text-danger"
                                                                role="alert">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="mt-3 text-right col-12 form-group">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-sm btn-primary rounded-pill">
                                                    </div>
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="tab-pane fade" id="stripe-payment" role="tabpanel"
                                aria-labelledby="stripe-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_stripe_enabled']) && $payment_setting['is_stripe_enabled'] == 'on')
                                        @if (isset($payment_setting['stripe_key']) &&
                                                !empty($payment_setting['stripe_key']) &&
                                                (isset($payment_setting['stripe_secret']) && !empty($payment_setting['stripe_secret'])))
                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form" action="{{ route('invoice.pay.with.stripe') }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span> {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                        @error('amount')
                                                            <span class="text-xs invalid-amount text-danger"
                                                                role="alert">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="mt-3 text-right col-12 form-group">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-sm btn-primary rounded-pill">
                                                    </div>
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="tab-pane fade" id="paypal-payment" role="tabpanel"
                                aria-labelledby="paypal-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_paypal_enabled']) && $payment_setting['is_paypal_enabled'] == 'on')
                                        @if (isset($payment_setting['paypal_client_id']) &&
                                                !empty($payment_setting['paypal_client_id']) &&
                                                (isset($payment_setting['paypal_secret_key']) && !empty($payment_setting['paypal_secret_key'])))
                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form"
                                                action="{{ route('client.pay.with.paypal', $invoice->id) }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                        </div>
                                                        @error('amount')
                                                            <span class="text-xs invalid-amount text-danger"
                                                                role="alert">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="mt-3 text-right col-12 form-group">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-sm btn-primary rounded-pill">
                                                    </div>
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="tab-pane fade" id="paystack-payment" role="tabpanel"
                                aria-labelledby="paystack-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_paystack_enabled']) && $payment_setting['is_paystack_enabled'] == 'on')
                                        @if (isset($payment_setting['paystack_public_key']) &&
                                                !empty($payment_setting['paystack_public_key']) &&
                                                (isset($payment_setting['paystack_secret_key']) && !empty($payment_setting['paystack_secret_key'])))
                                            <form method="post" action="{{ route('invoice.pay.with.paystack') }}"
                                                class="require-validation" id="paystack-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="Email"
                                                            class="form-control-label">{{ __('Email') }}</label>
                                                        <span class="fa fa-envelope"></span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span class="fa fa-envelope"></span> -->
                                                            <input class="form-control" required="required"
                                                                id="paystack_email" name="email" type="email"
                                                                placeholder="Enter Email">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="button" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill"
                                                        id="pay_with_paystack">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="tab-pane fade" id="flutterwave-payment" role="tabpanel"
                                aria-labelledby="flutterwave-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_flutterwave_enabled']) && $payment_setting['is_flutterwave_enabled'] == 'on')
                                        @if (isset($payment_setting['flutterwave_secret_key']) &&
                                                !empty($payment_setting['flutterwave_secret_key']) &&
                                                (isset($payment_setting['flutterwave_public_key']) && !empty($payment_setting['flutterwave_public_key'])))
                                            <form method="post" action="{{ route('invoice.pay.with.flaterwave') }}"
                                                class="require-validation" id="flaterwave-payment-form">
                                                @csrf
                                                <div class="row">

                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="button" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill"
                                                        id="pay_with_flaterwave">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="tab-pane fade" id="razorpay-payment" role="tabpanel"
                                aria-labelledby="razorpay-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_razorpay_enabled']) && $payment_setting['is_razorpay_enabled'] == 'on')
                                        @if (isset($payment_setting['razorpay_public_key']) &&
                                                !empty($payment_setting['razorpay_public_key']) &&
                                                (isset($payment_setting['razorpay_secret_key']) && !empty($payment_setting['razorpay_secret_key'])))
                                            <form method="post" action="{{ route('invoice.pay.with.razorpay') }}"
                                                class="require-validation" id="razorpay-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="Email"
                                                            class="form-control-label">{{ __('Email') }}</label>
                                                        <span class="fa fa-envelope"></span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span class="fa fa-envelope"></span> -->
                                                            <input class="form-control" required="required"
                                                                id="razorpay_email" name="email" type="email"
                                                                placeholder="Enter Email" value="company@wxample.com">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="button" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill"
                                                        id="pay_with_razorpay">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="tab-pane fade" id="mollie-payment" role="tabpanel"
                                aria-labelledby="mollie-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_mollie_enabled']) && $payment_setting['is_mollie_enabled'] == 'on')
                                        @if (isset($payment_setting['mollie_api_key']) &&
                                                !empty($payment_setting['mollie_api_key']) &&
                                                (isset($payment_setting['mollie_profile_id']) && !empty($payment_setting['mollie_profile_id'])))
                                            <form method="post" action="{{ route('invoice.pay.with.mollie') }}"
                                                class="require-validation" id="mollie-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            {{-- mercado payment --}}
                            <div class="tab-pane fade" id="mercado-payment" role="tabpanel"
                                aria-labelledby="mercado-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_mercado_enabled']) && $payment_setting['is_mercado_enabled'] == 'on')
                                        @if (isset($payment_setting['mercado_access_token']) && !empty($payment_setting['mercado_access_token']))
                                            <form method="post" action="{{ route('invoice.pay.with.mercado') }}"
                                                class="require-validation" id="mercado-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            {{-- paytm payment --}}
                            <div class="tab-pane fade" id="paytm-payment" role="tabpanel"
                                aria-labelledby="paytm-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_paytm_enabled']) && $payment_setting['is_paytm_enabled'] == 'on')
                                        @if (isset($payment_setting['paytm_merchant_id']) &&
                                                !empty($payment_setting['paytm_merchant_id']) &&
                                                (isset($payment_setting['paytm_merchant_key']) && !empty($payment_setting['paytm_merchant_key'])))
                                            <form method="post" action="{{ route('invoice.pay.with.paytm') }}"
                                                class="require-validation" id="paytm-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">

                                                        <label for="Email"
                                                            class="form-control-label">{{ __('Email') }}</label>
                                                        <span class="fa fa-envelope"></span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span class="fa fa-envelope"></span> -->
                                                            <input class="form-control" required="required"
                                                                id="paytm_email" name="email" type="email"
                                                                placeholder="Enter Email">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="mobile"
                                                            class="form-control-label text-dark">{{ __('Mobile Number') }}</label>
                                                        <span class="fa fa-phone"></span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span class="fa fa-phone"></span> -->
                                                            <input type="text" id="mobile" name="mobile"
                                                                class="form-control mobile" data-from="mobile"
                                                                placeholder="{{ __('Enter Mobile Number') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">

                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            {{-- skrill payment --}}
                            <div class="tab-pane fade" id="skrill-payment" role="tabpanel"
                                aria-labelledby="skrill-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_skrill_enabled']) && $payment_setting['is_skrill_enabled'] == 'on')
                                        @if (isset($payment_setting['skrill_email']) && !empty($payment_setting['skrill_email']))
                                            <form method="post" action="{{ route('invoice.pay.with.skrill') }}"
                                                class="require-validation" id="skrill-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-6">

                                                        <label for="Name"
                                                            class="form-control-label">{{ __('Name') }}</label>
                                                        <span class="fa fa-user"></span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span class="fa fa-user"></span> -->
                                                            <input class="form-control" required="required"
                                                                id="skrill_name" name="name" type="text"
                                                                placeholder="Enter your name">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-6">

                                                        <label for="Email"
                                                            class="form-control-label">{{ __('Email') }}</label>
                                                        <span class="fa fa-envelope"></span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span class="fa fa-envelope"></span> -->
                                                            <input class="form-control" required="required"
                                                                id="skrill_email" name="email" type="email"
                                                                placeholder="Enter Email">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            {{-- Coingate payment --}}
                            <div class="tab-pane fade" id="coingate-payment" role="tabpanel"
                                aria-labelledby="coingate-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_coingate_enabled']) && $payment_setting['is_coingate_enabled'] == 'on')
                                        @if (isset($payment_setting['coingate_auth_token']) && !empty($payment_setting['coingate_auth_token']))
                                            <form method="post" action="{{ route('invoice.pay.with.coingate') }}"
                                                class="require-validation" id="coingate-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- paymentwall payment --}}
                            <div class="tab-pane fade" id="paymentwall-payment" role="tabpanel"
                                aria-labelledby="paymentwall-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_paymentwall_enabled']) && $payment_setting['is_paymentwall_enabled'] == 'on')
                                        @if (isset($payment_setting['paymentwall_public_key']) &&
                                                !empty($payment_setting['paymentwall_public_key']) &&
                                                (isset($payment_setting['paymentwall_private_key']) && !empty($payment_setting['paymentwall_private_key'])))
                                            <form method="post" action="{{ route('invoice.paymentwallpayment') }}"
                                                class="require-validation" id="paymentwall-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- toyyibpay payment --}}
                            <div class="tab-pane fade" id="toyyibpay-payment" role="tabpanel"
                                aria-labelledby="toyyibpay-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_toyyibpay_enabled']) && $payment_setting['is_toyyibpay_enabled'] == 'on')
                                        @if (isset($payment_setting['toyyibpay_secret_key']) &&
                                                !empty($payment_setting['toyyibpay_secret_key']) &&
                                                (isset($payment_setting['category_code']) && !empty($payment_setting['category_code'])))
                                            <form method="post" action="{{ route('invoice.toyyibpaypayment') }}"
                                                class="require-validation" id="toyyibpay-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- payfast payment start --}}
                            <div class="tab-pane fade" id="payfast-payment" role="tabpanel"
                                aria-labelledby="payfast-payment-tab">
                                @if (isset($payment_setting['is_payfast_enabled']) && $payment_setting['is_payfast_enabled'] == 'on')
                                    @if (isset($payment_setting['payfast_merchant_id']) &&
                                            !empty($payment_setting['payfast_merchant_id']) &&
                                            (isset($payment_setting['payfast_merchant_key']) && !empty($payment_setting['payfast_merchant_key'])))
                                        @php
                                            $pfHost =
                                                $payment_setting['payfast_mode'] == 'sandbox'
                                                    ? 'sandbox.payfast.co.za'
                                                    : 'www.payfast.co.za';

                                        @endphp
                                        <form action={{ 'https://' . $pfHost . '/eng/process' }} method="post"
                                            class="require-validation" id="payfast-form">

                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount"
                                                        class="col-form-lable">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input type="number" class="form-control input_payfast" required
                                                            min="0" name="amount" id="amount"
                                                            value="{{ $invoice->getDue() }}" step="0.01"
                                                            max="{{ $invoice->getDue() }}">
                                                        <input type="hidden" value="{{ $invoice->id }}"
                                                            name="invoice_id" id="invoice_id">
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="get-payfast-inputs"></div>
                                            <div class="mt-3 col-12 form-group text-end">
                                                <input type="submit" value="{{ __('Make Payment') }}"
                                                    class="btn btn-print-invoice btn-primary m-r-10"
                                                    id="pay_with_payfast">
                                            </div>
                                        </form>
                                    @endif
                                @endif
                            </div>

                            {{-- iyzipay payment start --}}
                            <div class="tab-pane fade" id="iyzipay-payment" role="tabpanel"
                                aria-labelledby="iyzipay-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_iyzipay_enabled']) && $payment_setting['is_iyzipay_enabled'] == 'on')
                                        {{-- @dd($invoice->id); --}}
                                        @if (isset($payment_setting['iyzipay_public_key']) &&
                                                !empty($payment_setting['iyzipay_public_key']) &&
                                                (isset($payment_setting['iyzipay_secret_key']) && !empty($payment_setting['iyzipay_secret_key'])))
                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form"
                                                action="{{ route('client.pay.with.iyzipay', $invoice->id) }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                        </div>
                                                        @error('amount')
                                                            <span class="text-xs invalid-amount text-danger"
                                                                role="alert">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="mt-3 text-right col-12 form-group">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-sm btn-primary rounded-pill">
                                                    </div>
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- sspay payment start --}}
                            @if (
                                !empty($payment_setting) &&
                                    ($payment_setting['is_sspay_enabled'] == 'on' &&
                                        !empty($payment_setting['sspay_secret_key']) &&
                                        !empty($payment_setting['sspay_category_code'])))
                                <div class="tab-pane fade " id="sspay-payment" role="tabpanel"
                                    aria-labelledby="sspay-payment">
                                    <form class="w3-container w3-display-middle w3-card-4" method="POST"
                                        id="sspay-payment-form" action="{{ route('invoice.sspaypayment') }}">
                                        @csrf
                                        <input type="hidden" name="invoice_id"
                                            value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}">

                                        <div class="form-group col-md-12">
                                            <label for="amount">{{ __('Amount') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-prepend"><span
                                                        class="input-group-text">{{ Utility::getValByName('site_currency') }}</span></span>
                                                <input class="form-control" required="required" min="0"
                                                    name="amount" type="number" value="{{ $invoice->getDue() }}"
                                                    min="0" step="0.01" max="{{ $invoice->getDue() }}"
                                                    id="amount">

                                            </div>
                                        </div>
                                        <div class="mt-3 form-group">
                                            <input class="btn btn-sm btn-primary rounded-pill" id="pay_with_sspay"
                                                type="submit" value="{{ __('Make Payment') }}">
                                        </div>

                                    </form>
                                </div>
                            @endif

                            <!-- {{-- paytab --}}
                            @if (isset($payment_setting['is_paytab_enabled']) && $payment_setting['is_paytab_enabled'] == 'on')
                                @if (isset($payment_setting['paytab_profile_id']) &&
                                        !empty($payment_setting['paytab_profile_id']) &&
                                        (isset($payment_setting['paytab_server_key']) && !empty($payment_setting['paytab_server_key'])) &&
                                        (isset($payment_setting['paytab_region']) && !empty($payment_setting['paytab_region'])))
                                    <div class="tab-pane fade" id="paytab-payment" role="tabpanel" aria-labelledby="paytab-payment">

                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST" id="payment-form"
                                            action="{{ route('pay.with.benefit', $invoice->id) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount" class="col-form-label">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input class="form-control" required="required" min="0" name="amount"
                                                            type="number" value="{{ $invoice->getDue() }}" min="0" step="0.01"
                                                            max="{{ $invoice->getDue() }}" id="amount">
                                                        <input type="hidden" value="{{ $invoice->id }}" name="invoice_id">
                                                    </div>
                                                    @error('amount')
                                                        <span class="text-xs invalid-amount text-danger" role="alert">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endif -->

                            @if (isset($payment_setting['is_paytab_enabled']) && $payment_setting['is_paytab_enabled'] == 'on')
                                @if (isset($payment_setting['paytab_profile_id']) &&
                                        !empty($payment_setting['paytab_profile_id']) &&
                                        (isset($payment_setting['paytab_server_key']) && !empty($payment_setting['paytab_server_key'])) &&
                                        (isset($payment_setting['paytab_region']) && !empty($payment_setting['paytab_region'])))
                                    <div class="tab-pane fade" id="paytab-payment" role="tabpanel"
                                        aria-labelledby="paytab-payment">

                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                            id="payment-form" action="{{ route('pay.with.paytab', $invoice->id) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount"
                                                        class="col-form-label">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                        <input type="hidden" value="{{ $invoice->id }}"
                                                            name="invoice_id">
                                                    </div>
                                                    @error('amount')
                                                        <span class="text-xs invalid-amount text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </div>
                                        </form>


                                    </div>
                                @endif
                            @endif

                            {{-- benefit --}}

                            @if (isset($payment_setting['is_benefit_enabled']) && $payment_setting['is_benefit_enabled'] == 'on')
                                @if (isset($payment_setting['benefit_api_key']) &&
                                        !empty($payment_setting['benefit_api_key']) &&
                                        (isset($payment_setting['benefit_secret_key']) && !empty($payment_setting['benefit_secret_key'])))
                                    <div class="tab-pane fade" id="benefit-payment" role="tabpanel"
                                        aria-labelledby="benefit-payment">

                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                            id="payment-form" action="{{ route('pay.with.benefit', $invoice->id) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount"
                                                        class="col-form-label">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                        <input type="hidden" value="{{ $invoice->id }}"
                                                            name="invoice_id">
                                                    </div>
                                                    @error('amount')
                                                        <span class="text-xs invalid-amount text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endif

                            {{-- cashfree --}}
                            @if (isset($payment_setting['is_cashfree_enabled']) && $payment_setting['is_cashfree_enabled'] == 'on')
                                @if (isset($payment_setting['cashfree_api_key']) &&
                                        !empty($payment_setting['cashfree_api_key']) &&
                                        (isset($payment_setting['cashfree_secret_key']) && !empty($payment_setting['cashfree_secret_key'])))
                                    <div class="tab-pane fade" id="cashfree-payment" role="tabpanel"
                                        aria-labelledby="cashfree-payment">

                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                            id="payment-form" action="{{ route('pay.with.cashfree', $invoice->id) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount"
                                                        class="col-form-label">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                        <input type="hidden" value="{{ $invoice->id }}"
                                                            name="invoice_id">
                                                    </div>
                                                    @error('amount')
                                                        <span class="text-xs invalid-amount text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endif

                            {{-- aamarpay --}}
                            @if (isset($payment_setting['is_aamarpay_enabled']) && $payment_setting['is_aamarpay_enabled'] == 'on')
                                @if (isset($payment_setting['aamarpay_store_id']) &&
                                        !empty($payment_setting['aamarpay_store_id']) &&
                                        (isset($payment_setting['aamarpay_signature_key']) && !empty($payment_setting['aamarpay_signature_key'])) &&
                                        (isset($payment_setting['aamarpay_description']) && !empty($payment_setting['aamarpay_description'])))
                                    <div class="tab-pane fade" id="aamarpay-payment" role="tabpanel"
                                        aria-labelledby="aamarpay-payment">

                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                            id="payment-form" action="{{ route('pay.with.aamarpay', $invoice->id) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount"
                                                        class="col-form-label">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                        <input type="hidden" value="{{ $invoice->id }}"
                                                            name="invoice_id">
                                                    </div>
                                                    @error('amount')
                                                        <span class="text-xs invalid-amount text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endif

                            {{-- paytr --}}
                            @if (isset($payment_setting['is_paytr_enabled']) && $payment_setting['is_paytr_enabled'] == 'on')
                                @if (isset($payment_setting['paytr_merchant_id']) &&
                                        !empty($payment_setting['paytr_merchant_id']) &&
                                        (isset($payment_setting['paytr_merchant_key']) && !empty($payment_setting['paytr_merchant_key'])) &&
                                        (isset($payment_setting['paytr_merchant_salt']) && !empty($payment_setting['paytr_merchant_salt'])))
                                    <div class="tab-pane fade" id="paytr-payment" role="tabpanel"
                                        aria-labelledby="paytr-payment">

                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                            id="payment-form" action="{{ route('pay.with.paytr', $invoice->id) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount"
                                                        class="col-form-label">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                        <input type="hidden" value="{{ $invoice->id }}"
                                                            name="invoice_id">
                                                    </div>
                                                    @error('amount')
                                                        <span class="text-xs invalid-amount text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endif

                            {{-- yookassa --}}
                            @if (isset($payment_setting['is_yookassa_enabled']) && $payment_setting['is_yookassa_enabled'] == 'on')
                                @if (isset($payment_setting['is_yookassa_enabled']) &&
                                        !empty($payment_setting['is_yookassa_enabled']) &&
                                        (isset($payment_setting['yookassa_shop_id']) && !empty($payment_setting['yookassa_shop_id'])) &&
                                        (isset($payment_setting['yookassa_secret_key']) && !empty($payment_setting['yookassa_secret_key'])))
                                    <div class="tab-pane fade" id="yookassa-payment" role="tabpanel"
                                        aria-labelledby="yookassa-payment">

                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                            id="payment-form"
                                            action="{{ route('invoice.with.yookassa', $invoice->id) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount"
                                                        class="col-form-label">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                        <input type="hidden" value="{{ $invoice->id }}"
                                                            name="invoice_id">
                                                    </div>
                                                    @error('amount')
                                                        <span class="text-xs invalid-amount text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endif

                            {{-- Midtrans --}}
                            @if (isset($payment_setting['is_midtrans_enabled']) && $payment_setting['is_midtrans_enabled'] == 'on')
                                @if (isset($payment_setting['is_midtrans_enabled']) &&
                                        !empty($payment_setting['is_midtrans_enabled']) &&
                                        (isset($payment_setting['midtrans_secret']) && !empty($payment_setting['midtrans_secret'])))
                                    <div class="tab-pane fade" id="midtrans-payment" role="tabpanel"
                                        aria-labelledby="midtrans-payment">
                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                            id="payment-form"
                                            action="{{ route('invoice.with.midtrans', $invoice->id) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount"
                                                        class="col-form-label">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                        <input type="hidden" value="{{ $invoice->id }}"
                                                            name="invoice_id">
                                                    </div>
                                                    @error('amount')
                                                        <span class="text-xs invalid-amount text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endif

                            {{-- Xendit --}}
                            @if (isset($payment_setting['is_xendit_enabled']) && $payment_setting['is_xendit_enabled'] == 'on')
                                @if (isset($payment_setting['is_xendit_enabled']) &&
                                        !empty($payment_setting['is_xendit_enabled']) &&
                                        (isset($payment_setting['xendit_api_key']) && !empty($payment_setting['xendit_api_key'])) &&
                                        (isset($payment_setting['xendit_token']) && !empty($payment_setting['xendit_token'])))
                                    <div class="tab-pane fade" id="xendit-payment" role="tabpanel"
                                        aria-labelledby="xendit-payment">
                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                            id="payment-form"
                                            action="{{ route('invoice.with.xendit', $invoice->id) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount"
                                                        class="col-form-label">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                        <input type="hidden" value="{{ $invoice->id }}"
                                                            name="invoice_id">
                                                    </div>
                                                    @error('amount')
                                                        <span class="text-xs invalid-amount text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endif

                            {{-- PaiementPro --}}
                            <div class="tab-pane fade" id="PaiementPro-payment" role="tabpanel"
                                aria-labelledby="PaiementPro-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_PaiementPro_enabled']) && $payment_setting['is_PaiementPro_enabled'] == 'on')
                                        @if (isset($payment_setting['PaiementPro_merchant_key']) && !empty($payment_setting['PaiementPro_merchant_key']))
                                            <form method="post" action="{{ route('invoice.pay.with.paiementPro') }}"
                                                class="require-validation" id="PaiementPro-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">

                                                        <label for="Channle"
                                                            class="form-control-label">{{ __('Channel') }}</label>
                                                        <div class="form-icon-addon">
                                                            <!-- <span class="fa fa-envelope"></span> -->
                                                            <input class="form-control" required="required"
                                                                id="PaiementPro_Channle" name="Channle" type="Channle"
                                                                placeholder="Enter Channel">
                                                            <small class="text-muted"
                                                                style="color: red">{{ __('EXAMPLE : OMCIV2 , MOMO , CARD , FLOOZ , PAYPAL') }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="mobile"
                                                            class="form-control-label text-dark">{{ __('Mobile Number') }}</label>
                                                        <span class="fa fa-phone"></span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span class="fa fa-phone"></span> -->
                                                            <input type="text" id="mobile" name="mobile"
                                                                class="form-control mobile" data-from="mobile"
                                                                placeholder="{{ __('Enter Mobile Number') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="form-control-label">{{ __('Amount') }}</label>
                                                        <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span>
                                                        <div class="form-icon-addon">
                                                            <!-- <span>{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span> -->
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">

                                                            <input type="hidden"
                                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            @if (isset($payment_setting['is_nepalste_enabled']) && $payment_setting['is_nepalste_enabled'] == 'on')
                                <div class="tab-pane fade" id="nepalste-payment" role="tabpanel"
                                    aria-labelledby="nepalste-payment">
                                    <form role="form" action="{{ route('customer.with.nepalste') }}"
                                        method="post" class="require-validation" id="nepalste-payment-form">
                                        @csrf
                                        <input type="hidden" name="invoice_id"
                                            value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}">
                                        <div class="form-group col-md-12">
                                            <label for="amount">{{ __('Amount') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-prepend"><span
                                                        class="input-group-text">{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span></span>
                                                <input class="form-control" required="required" min="0"
                                                    name="amount" type="number" value="{{ $invoice->getDue() }}"
                                                    min="0" step="0.01" max="{{ $invoice->getDue() }}"
                                                    id="amount">
                                            </div>
                                        </div>
                                        <div class="mt-3 form-group">
                                            <button class="btn btn-primary" name="submit" id="pay_with_nepalste"
                                                type="submit">{{ __('Make Payment') }}</button>
                                        </div>
                                    </form>
                                </div>
                            @endif


                            @if (isset($payment_setting['is_CinetPay_enabled']) && $payment_setting['is_CinetPay_enabled'] == 'on')
                                <div class="tab-pane fade" id="CinetPay-payment" role="tabpanel"
                                    aria-labelledby="CinetPay-payment">
                                    <form role="form" action="{{ route('customer.with.CinetPay') }}"
                                        method="post" class="require-validation" id="CinetPay-payment-form">
                                        @csrf
                                        <input type="hidden" name="invoice_id"
                                            value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}">
                                        <div class="form-group col-md-12">
                                            <label for="amount">{{ __('Amount') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-prepend"><span
                                                        class="input-group-text">{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span></span>
                                                <input class="form-control" required="required" min="0"
                                                    name="amount" type="number" value="{{ $invoice->getDue() }}"
                                                    min="0" step="0.01" max="{{ $invoice->getDue() }}"
                                                    id="amount">
                                            </div>
                                        </div>
                                        <div class="mt-3 form-group">
                                            <button class="btn btn-primary" name="submit" id="pay_with_nepalste"
                                                type="submit">{{ __('Make Payment') }}</button>
                                        </div>
                                    </form>
                                </div>
                            @endif


                            @if (isset($payment_setting['is_Fedapay_enabled']) && $payment_setting['is_Fedapay_enabled'] == 'on')
                                <div class="tab-pane fade" id="Fedapay-payment" role="tabpanel"
                                    aria-labelledby="Fedapay-payment">
                                    <form role="form" action="{{ route('invoice.pay.with.fedapay') }}"
                                        method="post" class="require-validation" id="Fedapay-payment-form">
                                        @csrf
                                        <input type="hidden" name="invoice_id"
                                            value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}">
                                        <div class="form-group col-md-12">
                                            <label for="amount">{{ __('Amount') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-prepend"><span
                                                        class="input-group-text">{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span></span>
                                                <input class="form-control" required="required" min="0"
                                                    name="amount" type="number" value="{{ $invoice->getDue() }}"
                                                    min="0" step="0.01" max="{{ $invoice->getDue() }}"
                                                    id="amount">
                                            </div>
                                        </div>
                                        <div class="mt-3 form-group">
                                            <button class="btn btn-primary" name="submit" id="pay_with_Fedapay"
                                                type="submit">{{ __('Make Payment') }}</button>
                                        </div>
                                    </form>
                                </div>
                            @endif


                            @if (isset($payment_setting['is_PayHere_enabled']) && $payment_setting['is_PayHere_enabled'] == 'on')
                                <div class="tab-pane fade" id="PayHere-payment" role="tabpanel"
                                    aria-labelledby="PayHere-payment">
                                    <form role="form" action="{{ route('invoice.pay.with.payhere') }}"
                                        method="post" class="require-validation" id="PayHere-payment-form">
                                        @csrf
                                        <input type="hidden" name="invoice_id"
                                            value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}">
                                        <div class="form-group col-md-12">
                                            <label for="amount">{{ __('Amount') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-prepend"><span
                                                        class="input-group-text">{{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}</span></span>
                                                <input class="form-control" required="required" min="0"
                                                    name="amount" type="number" value="{{ $invoice->getDue() }}"
                                                    min="0" step="0.01" max="{{ $invoice->getDue() }}"
                                                    id="amount">
                                            </div>
                                        </div>
                                        <div class="mt-3 form-group">
                                            <button class="btn btn-primary" name="submit" id="pay_with_PayHere"
                                                type="submit">{{ __('Make Payment') }}</button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('script-page')
    {{-- Payfast start --}}

    @if (
        $invoice->getDue() > 0 &&
            isset($payment_setting['is_payfast_enabled']) &&
            $payment_setting['is_payfast_enabled'] == 'on')
        <script>
            $(".input_payfast").keyup(function() {

                var invoice_amount = $(this).val();
                //    alert(invoice_amount);
                get_payfast_status(invoice_amount);
            });

            $(document).ready(function() {
                get_payfast_status(amount = 0);

            })

            function get_payfast_status(amount) {
                var invoice_id = $('#invoice_id').val();
                var invoice_amount = amount;
                $.ajax({
                    url: '{{ route('invoice-pay-with-payfast') }}',
                    method: 'POST',
                    data: {
                        'invoice_id': invoice_id,
                        'amount': invoice_amount,
                        '_token': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        if (data.success == true) {
                            $('#get-payfast-inputs').append(data.inputs);
                        } else {
                            show_toastr('Error', data.inputs, 'error');
                        }
                    }
                });
            }
        </script>
    @endif
    {{-- Payfast end --}}

    @if ($invoice->getDue() > 0 && isset($payment_setting['is_stripe_enabled']) && $payment_setting['is_stripe_enabled'] == 'on')
        <?php $stripe_session = Session::get('stripe_session'); ?>
        <?php if(isset($stripe_session) && $stripe_session): ?>
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            var stripe = Stripe('{{ $payment_setting['stripe_key'] }}');
            stripe.redirectToCheckout({
                sessionId: '{{ $stripe_session->id }}',
            }).then((result) => {
                console.log(result);
            });
        </script>
        <?php endif ?>
    @endif

    @if (
        $invoice->getDue() > 0 &&
            isset($payment_setting['is_paystack_enabled']) &&
            $payment_setting['is_paystack_enabled'] == 'on')
        <script src="https://js.paystack.co/v1/inline.js"></script>

        <script type="text/javascript">
            $(document).on("click", "#pay_with_paystack", function() {

                $('#paystack-payment-form').ajaxForm(function(res) {
                    if (res.flag == 1) {
                        var coupon_id = res.coupon;

                        var paystack_callback = "{{ url('/invoice-pay-with-paystack') }}";
                        var order_id = '{{ time() }}';
                        var handler = PaystackPop.setup({
                            key: '{{ $payment_setting['paystack_public_key'] }}',
                            email: res.email,
                            amount: res.total_price * 100,
                            currency: res.currency,
                            ref: 'pay_ref_id' + Math.floor((Math.random() * 1000000000) +
                                1
                            ), // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
                            metadata: {
                                custom_fields: [{
                                    display_name: "Email",
                                    variable_name: "email",
                                    value: res.email,
                                }]
                            },

                            callback: function(response) {
                                console.log(response.reference, order_id);
                                window.location.href = "{{ url('/invoice/paystack') }}/" +
                                    response.reference + "/{{ encrypt($invoice->id) }}";
                            },
                            onClose: function() {
                                alert('window closed');
                            }
                        });
                        handler.openIframe();
                    } else if (res.flag == 2) {

                    } else {
                        toastrs('Error', data.message, 'msg');
                    }

                }).submit();
            });
        </script>
    @endif

    @if (
        $invoice->getDue() > 0 &&
            isset($payment_setting['is_flutterwave_enabled']) &&
            $payment_setting['is_flutterwave_enabled'] == 'on')
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"
            integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
        </script>

        <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>

        <script type="text/javascript">
            //    Flaterwave Payment
            $(document).on("click", "#pay_with_flaterwave", function() {

                $('#flaterwave-payment-form').ajaxForm(function(res) {
                    if (res.flag == 1) {
                        var coupon_id = res.coupon;
                        var amount = res.total_price;
                        var API_publicKey = '';
                        if ("{{ isset($payment_setting['flutterwave_public_key']) }}") {
                            API_publicKey = "{{ $payment_setting['flutterwave_public_key'] }}";
                        }
                        var nowTim = "{{ date('d-m-Y-h-i-a') }}";
                        var flutter_callback = "{{ url('/invoice-pay-with-flaterwave') }}";
                        var x = getpaidSetup({
                            PBFPubKey: API_publicKey,
                            customer_email: res.email,
                            amount: res.total_price,
                            currency: res.currency,
                            txref: nowTim + '__' + Math.floor((Math.random() * 1000000000)) +
                                'fluttpay_online-' + '{{ date('Y - m - d ') }}' + '?amount=' + amount,
                            meta: [{
                                metaname: "payment_id",
                                metavalue: "id"
                            }],
                            onclose: function() {},
                            callback: function(response) {
                                var txref = response.tx.txRef;
                                if (response.tx.chargeResponseCode == "00" || response.tx
                                    .chargeResponseCode == "0") {
                                    window.location.href = '{{ url('invoice / flaterwave ') }}' +
                                        '/' +
                                        '{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}' +
                                        '/' + txref;
                                } else {
                                    // redirect to a failure page.
                                }
                                x.close(); // use this to close the modal immediately after payment.
                            }
                        });
                    } else if (res.flag == 2) {

                    } else {
                        toastrs('Error', data.message, 'msg');
                    }

                }).submit();
            });
        </script>
    @endif
    {{-- razorpay Enable --}}
    @if (
        $invoice->getDue() > 0 &&
            isset($payment_setting['is_razorpay_enabled']) &&
            $payment_setting['is_razorpay_enabled'] == 'on')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

        <script type="text/javascript">
            // Razorpay Payment
            $(document).on("click", "#pay_with_razorpay", function() {
                $('#razorpay-payment-form').ajaxForm(function(res) {

                    if (res.flag == 1) {

                        var razorPay_callback = "{{ url('/invoice-pay-with-razorpay') }}";
                        var totalAmount = res.total_price * 100;
                        var coupon_id = res.coupon;
                        var API_publicKey = '';
                        if ("{{ isset($payment_setting['razorpay_public_key']) }}") {
                            API_publicKey = "{{ $payment_setting['razorpay_public_key'] }}";
                        }
                        var options = {
                            "key": API_publicKey, // your Razorpay Key Id
                            "amount": totalAmount,
                            "name": 'Invoice Payment',
                            "currency": res.currency,
                            "description": "",
                            "handler": function(response) {
                                window.location.href = "{{ url('/invoice/razorpay') }}/" + response
                                    .razorpay_payment_id + "/{{ encrypt($invoice->id) }}";
                            },
                            "theme": {
                                "color": "#528FF0"
                            }
                        };
                        var rzp1 = new Razorpay(options);
                        rzp1.open();
                    } else if (res.flag == 2) {

                    } else {
                        //                                                                                                                             console.log(message);
                        // toastrs('Error', data.message, 'msg');
                    }
                }).submit();
            });
        </script>

        @if (Session::has('success'))
            <script>
                toastrs('{{ __('
                                                                    Success ') }}', '{!! session('
                                                                    success ') !!}',
                    'success');
            </script>
            {{ Session::forget('success') }}
        @endif
        @if (Session::has('error'))
            <script>
                toastrs('{{ __('
                                                                    Error ') }}', '{!! session('
                                                                    error ') !!}',
                    'error');
            </script>
            {{ Session::forget('error') }}
        @endif

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"
            integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
        </script>
    @endif
@endpush
