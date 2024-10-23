 @extends('layouts.admin')
 @php
     $profile = \App\Models\Utility::get_file('uploads/avatar/');
     $attachment = \App\Models\Utility::get_file('uploads/attachment/');
     // $profile=asset(Storage::url('uploads/avatar'));
     $logo = Utility::GetLogo();

 @endphp
 @push('script-page')
     <script src="https://js.stripe.com/v3/"></script>
     <script src="https://js.paystack.co/v1/inline.js"></script>
     <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
     <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

     <script type="text/javascript">
         @if (\Auth::user()->type != 'company')
             @if (
                 $invoice->getDue() > 0 &&
                     !empty($company_payment_setting) &&
                     $company_payment_setting['is_stripe_enabled'] == 'on' &&
                     !empty($company_payment_setting['stripe_key']) &&
                     !empty($company_payment_setting['stripe_secret']))

                 var stripe = Stripe('{{ $company_payment_setting['stripe_key'] }}');
                 var elements = stripe.elements();

                 // Custom styling can be passed to options when creating an Element.
                 var style = {
                     base: {
                         // Add your base input styles here. For example:
                         fontSize: '14px',
                         color: '#32325d',
                     },
                 };

                 // Create an instance of the card Element.
                 var card = elements.create('card', {
                     style: style
                 });

                 // Add an instance of the card Element into the `card-element` <div>.
                 card.mount('#card-element');

                 // Create a token or display an error when the form is submitted.
                 var form = document.getElementById('payment-form');
                 form.addEventListener('submit', function(event) {
                     event.preventDefault();

                     stripe.createToken(card).then(function(result) {
                         if (result.error) {
                             $("#card-errors").html(result.error.message);
                             toastrs('Error', result.error.message, 'error');
                         } else {
                             // Send the token to your server.
                             stripeTokenHandler(result.token);
                         }
                     });
                 });

                 function stripeTokenHandler(token) {
                     // Insert the token ID into the form so it gets submitted to the server
                     var form = document.getElementById('payment-form');
                     var hiddenInput = document.createElement('input');
                     hiddenInput.setAttribute('type', 'hidden');
                     hiddenInput.setAttribute('name', 'stripeToken');
                     hiddenInput.setAttribute('value', token.id);
                     form.appendChild(hiddenInput);

                     // Submit the form
                     form.submit();
                 }
             @endif
         @endif
     </script>


     <script>
         $('.cp_link').on('click', function() {
             var value = $(this).attr('data-link');
             var $temp = $("<input>");
             $("body").append($temp);
             $temp.val(value).select();
             document.execCommand("copy");
             $temp.remove();
             toastrs('Success', '{{ __('Link Copy on Clipboard') }}', 'success')
         });
         $(document).on("click", ".status_change", function() {
             var invoice_id = $(this).attr('data-invoice');
             var status = $(this).attr('data-id');
             $.ajax({
                 url: '{{ route('invoice.status.change') }}',
                 type: 'GET',
                 data: {
                     invoice_id: invoice_id,
                     status: status,
                     "_token": $('meta[name="csrf-token"]').attr('content')
                 },
                 success: function(data) {
                     location.reload();
                 }
             });
         });

         $(document).on('change', 'select[name=item]', function() {
             var item_id = $(this).val();
             $.ajax({
                 url: '{{ route('invoice.items') }}',
                 type: 'GET',
                 headers: {
                     'X-CSRF-TOKEN': jQuery('#token').val()
                 },
                 data: {
                     'item_id': item_id,
                 },
                 cache: false,
                 success: function(data) {
                     var invoiceItems = JSON.parse(data);

                     $('.price').val(invoiceItems.sale_price);
                     $('.quantity').val(invoiceItems.quantity);
                     $('.discount').val(0);

                     var taxes = '';
                     var tax = [];
                     if (invoiceItems.taxes != '') {
                         for (var i = 0; i < invoiceItems.taxes.length; i++) {
                             taxes += '<span class="mt-1 mr-1 ">' + invoiceItems
                                 .taxes[i].name + ' ' + '(' + invoiceItems.taxes[i].rate + '%)' +
                                 '</span><br>';
                         }
                     } else {
                         taxes = '-';
                     }


                     $('.taxId').val(invoiceItems.tax);
                     $('.tax').html(taxes);


                 }
             });
         });

         $(document).on('click', '.type', function() {
             var obj = $(this).val();

             if (obj == 'milestone') {
                 $('.milestoneTask').removeClass('d-none');
                 $('.milestoneTask').addClass('d-block');
                 $('.title').removeClass('d-block');
                 $('.title').addClass('d-none');

             } else {
                 $('.title').removeClass('d-none');
                 $('.title').addClass('d-block');
                 $('.milestoneTask').removeClass('d-block');
                 $('.milestoneTask').addClass('d-none');
             }
         });

         @if (isset($company_payment_setting['paystack_public_key']))
             $(document).on("click", "#pay_with_paystack", function() {
                 $('#paystack-payment-form').ajaxForm(function(res) {
                     var amount = res.total_price;
                     if (res.flag == 1) {
                         var paystack_callback = "{{ url('/invoice/paystack') }}";

                         var handler = PaystackPop.setup({
                             key: '{{ isset($company_payment_setting['paystack_public_key']) ? $company_payment_setting['paystack_public_key'] : '' }}',
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

                                 window.location.href = paystack_callback + '/' + response
                                     .reference + '/' + '{{ encrypt($invoice->id) }}' +
                                     '?amount=' + amount;
                             },
                             onClose: function() {
                                 alert('window closed');
                             }
                         });
                         handler.openIframe();
                     } else if (res.flag == 2) {
                         toastrs('Error', res.msg, 'msg');
                     } else {
                         toastrs('Error', res.message, 'msg');
                     }

                 }).submit();
             });
         @endif

         @if (isset($company_payment_setting['flutterwave_public_key']))
             //    Flaterwave Payment
             $(document).on("click", "#pay_with_flaterwave", function() {
                 $('#flaterwave-payment-form').ajaxForm(function(res) {

                     if (res.flag == 1) {
                         var amount = res.total_price;
                         var API_publicKey =
                             '{{ isset($company_payment_setting['flutterwave_public_key']) ? $company_payment_setting['flutterwave_public_key'] : '' }}';
                         var nowTim = "{{ date('d-m-Y-h-i-a') }}";
                         // var flutter_callback = "{{ url('/invoice/flaterwave') }}";
                         var x = getpaidSetup({
                             PBFPubKey: API_publicKey,
                             customer_email: '{{ Auth::user()->email }}',
                             amount: res.total_price,
                             currency: '{{ Utility::getValByName('site_currency') }}',
                             txref: nowTim + '__' + Math.floor((Math.random() * 1000000000)) +
                                 'fluttpay_online-' + '{{ date('Y-m-d') }}' + '?amount=' + amount,
                             meta: [{
                                 metaname: "payment_id",
                                 metavalue: "id"
                             }],
                             onclose: function() {},
                             callback: function(response) {
                                 var txref = response.tx.txRef;
                                 if (
                                     response.tx.chargeResponseCode == "00" ||
                                     response.tx.chargeResponseCode == "0"
                                 ) {
                                     window.location.href = '{{ url('invoice/flaterwave') }}' +
                                         '/' +
                                         '{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}' +
                                         '/' + txref;
                                     // window.location.href = flutter_callback + '/' + txref + '/' + '{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}';
                                 } else {
                                     // redirect to a failure page.
                                 }
                                 x
                                     .close(); // use this to close the modal immediately after payment.
                             }
                         });
                     } else if (res.flag == 2) {
                         toastrs('Error', res.msg, 'msg');
                     } else {
                         toastrs('Error', data.message, 'msg');
                     }

                 }).submit();
             });
         @endif

         @if (isset($company_payment_setting['razorpay_public_key']))
             // Razorpay Payment
             $(document).on("click", "#pay_with_razorpay", function() {
                 $('#razorpay-payment-form').ajaxForm(function(res) {
                     if (res.flag == 1) {
                         var amount = res.total_price;
                         var razorPay_callback = '{{ url('/invoice/razorpay') }}';
                         var totalAmount = res.total_price * 100;
                         var coupon_id = res.coupon;
                         var options = {
                             "key": "{{ isset($company_payment_setting['razorpay_public_key']) ? $company_payment_setting['razorpay_public_key'] : '' }}", // your Razorpay Key Id
                             "amount": totalAmount,
                             "name": 'Plan',
                             "currency": '{{ Utility::getValByName('site_currency') }}',
                             "description": "",
                             "handler": function(response) {
                                 window.location.href = razorPay_callback + '/' + response
                                     .razorpay_payment_id + '/' +
                                     '{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}' +
                                     '?amount=' + amount;
                             },
                             "theme": {
                                 "color": "#528FF0"
                             }
                         };
                         var rzp1 = new Razorpay(options);
                         rzp1.open();
                     } else if (res.flag == 2) {
                         toastrs('Error', res.msg, 'msg');
                     } else {
                         toastrs('Error', data.message, 'msg');
                     }

                 }).submit();
             });
         @endif
     </script>
     <!-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"
         integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script> -->
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"
         integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
     </script>
 @endpush
 @section('page-title')
     {{ __('Invoice Detail') }}
 @endsection
 @section('title')
     <div class="d-inline-block">
         <h5 class="mb-0 h4 d-inline-block font-weight-400 ">
             {{ \Auth::user()->invoicenumberFormat($invoice->invoice_id) . ' ' . __('Details') }}</h5>
     </div>
 @endsection
 @section('breadcrumb')
     <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
     <li class="breadcrumb-item"><a href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a></li>
     <li class="breadcrumb-item active" aria-current="page">{{ \Auth::user()->invoicenumberFormat($invoice->invoice_id) }}
     </li>
 @endsection
 @section('action-btn')
     <a href="#" data-size="lg" data-url="{{ route('creditNote.create') }}" data-bs-toggle="modal"
         data-bs-target="#exampleModal" data-bs-whatever="{{ __('Create Payment') }}"
         class="m-1 btn btn-sm btn-primary btn-icon">
         <span class="btn-inner--icon" data-bs-toggle="tooltip" data-bs-title="{{ __('Add Credit Note') }}"><i
                 class="ti ti-plus"></i></span>
         <span class="btn-inner--text">{{ __('Add Credit Note') }}</span>
     </a>

     <a href="#" class="m-1 btn btn-sm btn-primary btn-icon cp_link"
         data-link="{{ route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}"
         data-toggle="tooltip" data-original-title="{{ __('Click to copy invoice link') }}">
         <span class="btn-inner--icon"><i class="ti ti-copy"data-bs-toggle="tooltip"
                 data-bs-original-title="{{ __('Copy') }}"></i></span>
         <span class="btn-inner--text">{{ __('Copy') }}</span>

     </a>
     @if (\Auth::user()->type == 'company')
         <a href="#" data-size="lg" data-url="{{ route('invoice.create.item', $invoice->id) }}"
             data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="{{ __('Add Item') }}"
             class="m-1 btn btn-sm btn-primary btn-icon">
             <span class="btn-inner--icon"><i class="ti ti-plus"></i></span>
             <span class="btn-inner--text">{{ __('Add Item') }}</span>
         </a>


         @if ($invoice->status != 0 && $invoice->status != 5)
             <a href="{{ route('invoice.send', $invoice->id) }}" class="m-1 btn btn-sm btn-primary btn-icon">
                 <span class="btn-inner--icon"><i class="ti ti-send"></i></span>
                 <span class="btn-inner--text">{{ __('Resend') }}</span>
             </a>
         @else
             @if (!empty($invoice->items))
                 <a href="{{ route('invoice.send', $invoice->id) }}" class="m-1 btn btn-sm btn-primary btn-icon">
                     <span class="btn-inner--icon"><i class="ti ti-send"></i></span>
                     <span class="btn-inner--text">{{ __('Send') }}</span>
                 </a>
             @endif
         @endif
         <a href="#" data-size="lg" data-url="{{ route('invoice.create.receipt', $invoice->id) }}"
             data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="{{ __('Add Receipt') }}"
             class="m-1 btn btn-sm btn-primary btn-icon">
             <span class="btn-inner--icon"><i class="ti ti-plus"></i></span>
             <span class="btn-inner--text">{{ __('Add Receipt') }}</span>
         </a>

         <a href="{{ route('invoice.send', $invoice->id) }}" class="m-1 btn btn-sm btn-primary btn-icon ">
             <span class="btn-inner--icon"><i class="ti ti-report-money"></i></span>
             <span class="btn-inner--text">{{ __('Payment Reminder') }}</span>
         </a>
     @endif

     <a href="{{ route('invoice.pdf', \Crypt::encrypt($invoice->id)) }}" target="_blank"
         class="m-1 btn btn-sm btn-primary btn-icon">
         <span class="btn-inner--icon"><i class="ti ti-printer"></i></span>
         <span class="btn-inner--text">{{ __('Print') }}</span>
     </a>

     @if (\Auth::user()->type == 'client')
         @if (
             $invoice->getDue() > 0 &&
                 !empty($company_payment_setting) &&
                 ($company_payment_setting['is_stripe_enabled'] == 'on' ||
                     $company_payment_setting['is_paypal_enabled'] == 'on' ||
                     $company_payment_setting['is_paystack_enabled'] == 'on' ||
                     $company_payment_setting['is_flutterwave_enabled'] == 'on' ||
                     $company_payment_setting['is_razorpay_enabled'] == 'on' ||
                     $company_payment_setting['is_mercado_enabled'] == 'on' ||
                     $company_payment_setting['is_paytm_enabled'] == 'on' ||
                     $company_payment_setting['is_mollie_enabled'] == 'on' ||
                     $company_payment_setting['is_paypal_enabled'] == 'on' ||
                     $company_payment_setting['is_skrill_enabled'] == 'on' ||
                     $company_payment_setting['is_coingate_enabled'] == 'on' ||
                     $company_payment_setting['is_paymentwall_enabled'] == 'on' ||
                     $company_payment_setting['is_toyyibpay_enabled'] == 'on' ||
                     $company_payment_setting['is_payfast_enabled'] == 'on' ||
                     $company_payment_setting['is_iyzipay_enabled'] == 'on' ||
                     $company_payment_setting['is_sspay_enabled'] == 'on' ||
                     $company_payment_setting['is_paytab_enabled'] == 'on' ||
                     $company_payment_setting['is_benefit_enabled'] == 'on' ||
                     $company_payment_setting['is_cashfree_enabled'] == 'on' ||
                     $company_payment_setting['is_aamarpay_enabled'] == 'on' ||
                     $company_payment_setting['is_paytr_enabled'] == 'on' ||
                     $company_payment_setting['is_PaiementPro_enabled'] == 'on' ||
                     $company_payment_setting['is_nepalste_enabled'] == 'on' ||
                     $company_payment_setting['is_CinetPay_enabled'] == 'on' ||
                     $company_payment_setting['is_Fedapay_enabled'] == 'on' ||
                     $company_payment_setting['is_PayHere_enabled'] == 'on'))
             <a href="#" data-bs-toggle="modal" data-bs-target="#paymentModal"
                 class="m-1 btn btn-sm btn-primary btn-icon" type="button">
                 <i class="mr-1 fas fa-coins"></i> {{ __('Pay Now') }}
             </a>
         @endif
     @endif
 @endsection

 @section('filter')
 @endsection
 @section('content')
    <div class="row">
        <!-- [ Invoice ] start -->
        <div class="container">
            <div class="card" id="printTable">
                <div class="card-header">
                    <h4>{{ __('Invoice') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row ">
                        <div class="col-md-8 invoice-contact">
                            <div class="invoice-box row">
                                <div class="col-sm-12">
                                    <table class="table mt-0 table-responsive invoice-table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td><a href="{{ route('invoice.index') }}"><img class="mb-3 img-fluid"
                                                            src="{{ \App\Models\Utility::get_file('uploads/logo/' . $logo) . '?timestamp=' . time() }}"
                                                            alt="Dashboard-kit Logo"></a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>{{ $settings['company_name'] }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    {{ $settings['company_address'] }} <br>
                                                    {{ $settings['company_city'] }}<br>
                                                    {{ $settings['company_state'] }}
                                                    {{ $settings['company_zipcode'] }} <br>
                                                    {{ $settings['company_country'] }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>{{ $settings['company_telephone'] }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="float-end"  style="display:{{ $company_setting['invoice_qrcode'] == 'on' ? 'block' : 'none' }};">
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
                                    <br> {{ !empty($invoice->clientDetail->state) ? $invoice->clientDetail->state : '' }}
                                    {{-- <a class="text-secondary" href="$" target="_top"><span class="__cf_email__"
                                    data-cfemail="6a0e0f07052a0d070b030644090507"> --}}
                                    {{ !empty($invoice->clientDetail->zip_code) ? $invoice->clientDetail->zip_code : '' }}</span>
                                    {{-- </a> --}}
                                    <br>
                                    {{ !empty($invoice->clientDetail->country) ? $invoice->clientDetail->country : '' }}
                                </p><br>
                                <p class="m-0">
                                    {{ !empty($invoice->clientDetail->mobile) ? $invoice->clientDetail->mobile : '' }}</p>
                            </div>
                        @endif
                        <div class="col-sm-4">
                            <h6 class="m-b-20">{{ __('Order Details :') }}</h6>
                            <table class="table mt-0 table-responsive invoice-table invoice-order table-borderless">
                                <tbody>
                                    <tr>
                                        <th>{{ __('Issue Date :') }}</th>
                                        <td>{{ \Auth::user()->dateFormat($invoice->issue_date) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Expiry Date : ') }}</th>
                                        <td>{{ \Auth::user()->dateFormat($invoice->due_date) }}</td>
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
                                {{ \Auth::user()->invoicenumberFormat($invoice->invoice_id) }}
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
                                            <tr>
                                                <td>
                                                    <h6>{{ !empty($item->item) ? $item->item : 'No Item' }}</h6>
                                                    <p>{{ $item->description }}</p>
                                                </td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ \Auth::user()->priceFormat($item->price) }}</td>
                                                <td>
                                                    @if (!empty($item->tax))
                                                        @foreach ($item->itemTax as $tax)
                                                            <a href="#!"
                                                                class="text-sm d-block text-muted">{{ $tax['name'] . ' (' . $tax['rate'] . '%)' }}
                                                                &nbsp;&nbsp;{{ $tax['price'] }}</a>
                                                        @endforeach
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ \Auth::user()->priceFormat($item->discount) }}</td>
                                                <td>{{ \Auth::user()->priceFormat($item->price * $item->quantity) }}</td>
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
                                    @php
                                        $invoiceTotalPrice = $invoice->getTotal();
                                        $invoiceCreditNote = $invoice->creditNotes->sum('amount');
                                        $invoiceGetDue = $invoice->getDue();
                                    @endphp
                                    <tbody>
                                        <tr>
                                            <th>{{ __('Sub Total :') }}</th>
                                            <td>{{ \Auth::user()->priceFormat($invoice->getSubTotal()) }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Discount :') }}</th>
                                            <td>{{ \Auth::user()->priceFormat($invoice->getTotalDiscount()) }}</td>
                                        </tr>
                                        @if (!empty($invoice->taxesData))
                                            @foreach ($invoice->taxesData as $taxName => $taxPrice)
                                                <tr>

                                                    <th>{{ $taxName }}</th>
                                                    <td>{{ \Auth::user()->priceFormat($taxPrice) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        <tr>
                                            <th>{{ __('Total :') }}</th>
                                            <td>{{ \Auth::user()->priceFormat($invoiceTotalPrice) }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Credit Note :') }}</th>
                                            <td>{{ \Auth::user()->priceFormat($invoiceCreditNote) }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Paid :') }}</th>
                                            <td>{{ \Auth::user()->priceFormat($invoiceTotalPrice - $invoiceGetDue - $invoiceCreditNote) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Due :') }}</th>
                                            <td>{{ \Auth::user()->priceFormat($invoiceGetDue) }}</td>
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

     {{-- Receipt summary --}}
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
                            @if (\Auth::user()->type == 'company')
                                <th> {{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @dd($banktransfer); --}}
                        @foreach ($invoice->payments as $payment)
                            <tr>
                                <td>{{ $payment->transaction }} </td>
                                <td>{{ \Auth::user()->dateFormat($payment->date) }} </td>
                                <td>{{ !empty($payment->payments) ? $payment->payments->name : '' }} </td>
                                <td>{{ $payment->payment_type }} </td>
                                <td>{{ $payment->notes }} </td>
                                <td> {{ \Auth::user()->priceFormat($payment->amount) }}</td>
                                <td>
                                    @if ($user_storage >= $plan_storage)
                                    @else
                                        @if (!empty($payment->receipt))
                                            @php
                                                $x = pathinfo($payment->receipt, PATHINFO_FILENAME);
                                                $extension = pathinfo($payment->receipt, PATHINFO_EXTENSION);
                                                $result = str_replace(['#', "'", ';'], '', $payment->receipt);
                                                // dd($result);
                                            @endphp
                                            <a href="{{ route('invoice.receipt', [$x, "$extension"]) }}"
                                                data-toggle="tooltip"
                                                class="btn btn-sm btn-primary btn-icon rounded-pill">
                                                <i class="ti ti-download"></i>
                                            </a>
                                            <a href="{{ $attachment . $x . '.' . $extension }}" target="_blank"
                                                data-toggle="tooltip"
                                                class="btn btn-sm btn-secondary btn-icon rounded-pill">
                                                <i class="ti ti-crosshair" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Preview') }}"></i>
                                            </a>
                                        @else
                                        @endif
                                    @endif
                                    @if (\Auth::user()->type == 'company')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.payment.delete', $invoice->id, $payment->id]]) !!}
                                            <a href="#!" class="mx-3 btn btn-sm align-items-center show_confirm ">
                                                <i class="text-white ti ti-trash"></i>
                                            </a>
                                            {!! Form::close() !!}
                                        </div>
                                    @endif


                                </td>

                            </tr>
                        @endforeach

                        @foreach ($banktransfer as $bank_payment)
                            {{-- @dd($bank_payment); --}}
                            <tr>
                                <td>{{ $bank_payment->order_id }} </td>
                                <td>{{ \Auth::user()->dateFormat($bank_payment->date) }} </td>
                                <td>{{ '-' }} </td>
                                <td> {{ __('Bank Transfer') }} </td>
                                <td>
                                    {{ \Auth::user()->invoicenumberFormat($invoice->invoice_id) }}
                                </td>
                                <td>{{ \Auth::user()->priceFormat($bank_payment->amount) }} </td>
                                <td>
                                    @if ($bank_payment->status == 'Pending' && \Auth::user()->type == 'company')
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                data-url="{{ route('banktransfer.show', $bank_payment->id) }}"
                                                data-bs-whatever="{{ __('Invoice Bank Transfer') }}"> <span
                                                    class="text-white">
                                                    <i class="ti ti-caret-right" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Invoice Bank Transfer') }}"></i></span></a>
                                        </div>
                                    @endif
                                    @if ((\Auth::user()->type == 'company' && $bank_payment->status == 'Pending') || $bank_payment->status == 'Reject')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['invoice.bankpayment.delete', $invoice->id, $bank_payment->id],
                                            ]) !!}
                                            <a href="#!" class="mx-3 btn btn-sm align-items-center show_confirm ">
                                                <i class="text-white ti ti-trash"></i>
                                            </a>
                                            {!! Form::close() !!}
                                        </div>
                                    @elseif(\Auth::user()->type == 'company')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.payment.delete', $invoice->id, $payment->id]]) !!}
                                            <a href="#!" class="mx-3 btn btn-sm align-items-center show_confirm ">
                                                <i class="text-white ti ti-trash"></i>
                                            </a>
                                            {!! Form::close() !!}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Credit Summary --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mb-4 card table-responsive">
                <div class="card-header">
                    <h5>{{ __('Credit Note Summary') }}</h5>
                    <div class="card-body table-responsive">
                        <table class="table" id="">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('Invoice') }}</th>
                                    @if (\Auth::user()->type != 'client')
                                        <th scope="col">{{ __('Client') }}</th>
                                    @endif
                                    <th scope="col">{{ __('Date') }}</th>
                                    <th scope="col">{{ __('Amount') }}</th>
                                    <th scope="col">{{ __('Description') }}</th>
                                    @if (\Auth::user()->type == 'company')
                                        <th scope="col" class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    @if (!empty($invoice->creditNote))
                                        @foreach ($invoice->creditNote as $creditNote)
                                            <tr>
                                                <td>
                                                    <div class="media-body">
                                                        <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                            class="btn btn-outline-primary">
                                                            {{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}</a>
                                                    </div>
                                                </td>
                                                @if (\Auth::user()->type != 'client')
                                                    <td>{{ !empty($invoice->clients) ? $invoice->clients->name : '' }}
                                                    </td>
                                                @endif
                                                <td>{{ Auth::user()->dateFormat($creditNote->date) }}</td>
                                                <td>{{ Auth::user()->priceFormat($creditNote->amount) }}</td>
                                                <td>{{ $creditNote->description }}</td>
                                                <td class="text-right table-actions">
                                                    @if (\Auth::user()->type == 'company')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" data-size="lg"
                                                                data-url="{{ route('creditNote.edit', $creditNote->id) }}"
                                                                data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                                data-bs-whatever="{{ __('Edit Credit Note') }}"
                                                                class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                                <i class="text-white ti ti-edit" data-bs-toggle="tooltip"
                                                                    data-bs-original-title="{{ __('Edit') }}"></i>
                                                            </a>
                                                        </div>
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['creditNote.destroy', $creditNote->id]]) !!}
                                                            <a href="#!"
                                                                class="mx-3 btn btn-sm align-items-center show_confirm ">
                                                                <i class="text-white ti ti-trash"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-original-title="{{ __('Delete') }}"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Payment Modal-->
    @if (\Auth::user()->type == 'client')
        @if ($invoice->getDue() > 0)
            <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog"
                aria-labelledby="paymentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="paymentModalLabel">{{ __('Add Payment') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            @if (
                                !empty($company_payment_setting) &&
                                    ($company_payment_setting['is_stripe_enabled'] == 'on' ||
                                        $company_payment_setting['is_paypal_enabled'] == 'on' ||
                                        $company_payment_setting['is_paystack_enabled'] == 'on' ||
                                        $company_payment_setting['is_flutterwave_enabled'] == 'on' ||
                                        $company_payment_setting['is_razorpay_enabled'] == 'on' ||
                                        $company_payment_setting['is_mercado_enabled'] == 'on' ||
                                        $company_payment_setting['is_paytm_enabled'] == 'on' ||
                                        $company_payment_setting['is_mollie_enabled'] == 'on' ||
                                        $company_payment_setting['is_paypal_enabled'] == 'on' ||
                                        $company_payment_setting['is_skrill_enabled'] == 'on' ||
                                        $company_payment_setting['is_coingate_enabled'] == 'on' ||
                                        $company_payment_setting['is_paymentwall_enabled'] == 'on' ||
                                        $company_payment_setting['is_toyyibpay_enabled'] == 'on' ||
                                        $company_payment_setting['is_payfast_enabled'] == 'on' ||
                                        $company_payment_setting['is_iyzipay_enabled'] == 'on' ||
                                        $company_payment_setting['is_sspay_enabled'] == 'on' ||
                                        $company_payment_setting['is_paytab_enabled'] == 'on' ||
                                        $company_payment_setting['is_benefit_enabled'] == 'on' ||
                                        $company_payment_setting['is_cashfree_enabled'] == 'on' ||
                                        $company_payment_setting['is_aamarpay_enabled'] == 'on' ||
                                        $company_payment_setting['is_paytr_enabled'] == 'on' ||
                                        $company_payment_setting['is_PaiementPro_enabled'] == 'on' ||
                                        $company_payment_setting['is_nepalste_enabled'] == 'on' ||
                                        $company_payment_setting['is_CinetPay_enabled'] == 'on' ||
                                        $company_payment_setting['is_Fedapay_enabled'] == 'on' ||
                                        $company_payment_setting['is_PayHere_enabled'] == 'on'))

                                <ul class="mb-3 nav nav-pills" role="tablist">
                                    @if (isset($company_payment_setting['is_bank_transfer_enabled']) &&
                                            $company_payment_setting['is_bank_transfer_enabled'] == 'on')
                                        @if (isset($company_payment_setting['bank_details']) && !empty($company_payment_setting['bank_details']))
                                            <li class="mb-2 nav-item">
                                                <a href="#banktransfer-payment"
                                                    class="btn btn-outline-primary btn-sm active"
                                                    aria-controls="banktransfer" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false">
                                                    {{ __('BankTransfer') }}
                                                </a>
                                            </li>&nbsp;
                                        @endif
                                    @endif

                                    @if (isset($company_payment_setting['is_stripe_enabled']) == 'on' &&
                                            !empty($company_payment_setting['stripe_key']) &&
                                            !empty($company_payment_setting['stripe_secret']))
                                        <li class="mb-2 nav-item">
                                            <a class="btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#stripe-payment" role="tab" aria-controls="stripe"
                                                aria-selected="true">{{ __('Stripe') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_paypal_enabled']) == 'on' &&
                                            !empty($company_payment_setting['paypal_client_id']) &&
                                            !empty($company_payment_setting['paypal_secret_key']))
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#paypal-payment" role="tab" aria-controls="paypal"
                                                aria-selected="false">{{ __('Paypal') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_paystack_enabled']) == 'on' &&
                                            !empty($company_payment_setting['paystack_public_key']) &&
                                            !empty($company_payment_setting['paystack_secret_key']))
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#paystack-payment" role="tab" aria-controls="paystack"
                                                aria-selected="false">{{ __('Paystack') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_flutterwave_enabled']) &&
                                            $company_payment_setting['is_flutterwave_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#flutterwave-payment" role="tab" aria-controls="flutterwave"
                                                aria-selected="false">{{ __('Flutterwave') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#razorpay-payment" role="tab" aria-controls="razorpay"
                                                aria-selected="false">{{ __('Razorpay') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_mercado_enabled']) && $company_payment_setting['is_mercado_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#mercado-payment" role="tab" aria-controls="mercado"
                                                aria-selected="false">{{ __('Mercado') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#paytm-payment" role="tab" aria-controls="paytm"
                                                aria-selected="false">{{ __('Paytm') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_mollie_enabled']) && $company_payment_setting['is_mollie_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#mollie-payment" role="tab" aria-controls="mollie"
                                                aria-selected="false">{{ __('Mollie') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_skrill_enabled']) && $company_payment_setting['is_skrill_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#skrill-payment" role="tab" aria-controls="skrill"
                                                aria-selected="false">{{ __('Skrill') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_coingate_enabled']) && $company_payment_setting['is_coingate_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#coingate-payment" role="tab" aria-controls="coingate"
                                                aria-selected="false">{{ __('Coingate') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_paymentwall_enabled']) &&
                                            $company_payment_setting['is_paymentwall_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#paymentwall-payment" role="tab" aria-controls="paymentwall"
                                                aria-selected="false">{{ __('PaymentWall') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_toyyibpay_enabled']) && $company_payment_setting['is_toyyibpay_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#toyyibpay-payment" role="tab" aria-controls="toyyibpay"
                                                aria-selected="false">{{ __('Toyyibpay') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_payfast_enabled']) && $company_payment_setting['is_payfast_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#payfast-payment" role="tab" aria-controls="payfast"
                                                aria-selected="false">{{ __('payfast') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_iyzipay_enabled']) && $company_payment_setting['is_iyzipay_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#iyzipay-payment" role="tab" aria-controls="iyzipay"
                                                aria-selected="false">{{ __('Iyzipay') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_sspay_enabled']) && $company_payment_setting['is_sspay_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm" data-bs-toggle="tab"
                                                href="#sspay-payment" role="tab" aria-controls="sspay"
                                                aria-selected="false">{{ __('Sspay') }}</a>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_paytab_enabled']) && $company_payment_setting['is_paytab_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                data-bs-toggle="pill" data-bs-target="#paytab-payment" role="tab"
                                                aria-controls="paytab" type="button"
                                                aria-selected="false">{{ __('PayTab') }}</button>
                                        </li>&nbsp;
                                    @endif


                                    @if (isset($company_payment_setting['is_benefit_enabled']) && $company_payment_setting['is_benefit_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                data-bs-toggle="pill" data-bs-target="#benefit-payment" role="tab"
                                                aria-controls="benefit" type="button"
                                                aria-selected="false">{{ __('Benefit') }}</button>
                                        </li>&nbsp;
                                    @endif

                                    @if (isset($company_payment_setting['is_cashfree_enabled']) && $company_payment_setting['is_cashfree_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                data-bs-toggle="pill" data-bs-target="#cashfree-payment" role="tab"
                                                aria-controls="cashfree" type="button"
                                                aria-selected="false">{{ __('Cashfree') }}</button>
                                        </li>
                                    @endif

                                    @if (isset($company_payment_setting['is_aamarpay_enabled']) && $company_payment_setting['is_aamarpay_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                data-bs-toggle="pill" data-bs-target="#aamarpay-payment" role="tab"
                                                aria-controls="aamarpay" type="button"
                                                aria-selected="false">{{ __('Aamarpay') }}</button>
                                        </li>
                                    @endif

                                    @if (isset($company_payment_setting['is_paytr_enabled']) && $company_payment_setting['is_paytr_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                data-bs-toggle="pill" data-bs-target="#paytr-payment" role="tab"
                                                aria-controls="paytr" type="button"
                                                aria-selected="false">{{ __('PayTr') }}</button>
                                        </li>
                                    @endif

                                    @if (isset($company_payment_setting['is_yookassa_enabled']) && $company_payment_setting['is_yookassa_enabled'] == 'on')
                                        @if (isset($company_payment_setting['is_yookassa_enabled']) &&
                                                !empty($company_payment_setting['is_yookassa_enabled']) &&
                                                (isset($company_payment_setting['yookassa_shop_id']) && !empty($company_payment_setting['yookassa_shop_id'])) &&
                                                (isset($company_payment_setting['yookassa_secret_key']) &&
                                                    !empty($company_payment_setting['yookassa_secret_key'])))
                                            <li class="mb-2 nav-item">
                                                <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                    data-bs-toggle="pill" data-bs-target="#yookassa-payment"
                                                    role="tab" aria-controls="yookassa" type="button"
                                                    aria-selected="false">{{ __('Yookassa') }}</button>
                                            </li>&nbsp;
                                        @endif
                                    @endif

                                    @if (isset($company_payment_setting['is_midtrans_enabled']) && $company_payment_setting['is_midtrans_enabled'] == 'on')
                                        @if (isset($company_payment_setting['is_midtrans_enabled']) &&
                                                !empty($company_payment_setting['is_midtrans_enabled']) &&
                                                (isset($company_payment_setting['midtrans_secret']) && !empty($company_payment_setting['midtrans_secret'])))
                                            <li class="mb-2 nav-item">
                                                <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                    data-bs-toggle="pill" data-bs-target="#midtrans-payment"
                                                    role="tab" aria-controls="midtrans" type="button"
                                                    aria-selected="false">{{ __('Midtrans') }}</button>
                                            </li>&nbsp;
                                        @endif
                                    @endif

                                    @if (isset($company_payment_setting['is_xendit_enabled']) && $company_payment_setting['is_xendit_enabled'] == 'on')
                                        @if (isset($company_payment_setting['is_xendit_enabled']) &&
                                                !empty($company_payment_setting['is_xendit_enabled']) &&
                                                (isset($company_payment_setting['xendit_api_key']) && !empty($company_payment_setting['xendit_api_key'])) &&
                                                (isset($company_payment_setting['xendit_token']) && !empty($company_payment_setting['xendit_token'])))
                                            <li class="mb-2 nav-item">
                                                <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                    data-bs-toggle="pill" data-bs-target="#xendit-payment"
                                                    role="tab" aria-controls="xendit" type="button"
                                                    aria-selected="false">{{ __('Xendit') }}</button>
                                            </li>&nbsp;
                                        @endif
                                    @endif

                                    @if (isset($company_payment_setting['is_PaiementPro_enabled']) &&
                                            $company_payment_setting['is_PaiementPro_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                data-bs-toggle="pill" data-bs-target="#paiementpro-payment"
                                                role="tab" aria-controls="paiementpro" type="button"
                                                aria-selected="false">{{ __('PaiementPro') }}</button>
                                        </li>
                                    @endif

                                    @if (isset($company_payment_setting['is_nepalste_enabled']) && $company_payment_setting['is_nepalste_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <a class="ml-1 btn btn-outline-primary btn-sm me-1" data-bs-toggle="tab"
                                                href="#nepalste-payment" role="tab" aria-controls="nepalste"
                                                aria-selected="false">{{ __('Nepalste') }}</a>
                                        </li>
                                    @endif

                                    @if (isset($company_payment_setting['is_CinetPay_enabled']) && $company_payment_setting['is_CinetPay_enabled'] == 'on')
                                        <li class="mb-2 nav-item">
                                            <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                data-bs-toggle="pill" data-bs-target="#CinetPay-payment" role="tab"
                                                aria-controls="CinetPay" type="button"
                                                aria-selected="false">{{ __('CinetPay') }}</button>
                                        </li>
                                    @endif

                                    @if (isset($company_payment_setting['is_Fedapay_enabled']) && $company_payment_setting['is_Fedapay_enabled'] == 'on')
                                        @if (isset($company_payment_setting['is_Fedapay_enabled']) &&
                                                !empty($company_payment_setting['Fedapay_public_key']) &&
                                                (isset($company_payment_setting['Fedapay_secret_key']) &&
                                                    !empty($company_payment_setting['Fedapay_secret_key'])))
                                            <li class="mb-2 nav-item">
                                                <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                    data-bs-toggle="pill" data-bs-target="#Fedapay-payment"
                                                    role="tab" aria-controls="Fedapay" type="button"
                                                    aria-selected="false">{{ __('Fedapay') }}</button>
                                            </li>&nbsp;
                                        @endif
                                    @endif

                                    @if (isset($company_payment_setting['is_PayHere_enabled']) && $company_payment_setting['is_PayHere_enabled'] == 'on')
                                        @if (isset($company_payment_setting['is_PayHere_enabled']) &&
                                                !empty($company_payment_setting['PayHere_public_id']) &&
                                                (isset($company_payment_setting['PayHere_secret_key']) &&
                                                    !empty($company_payment_setting['PayHere_secret_key'])) &&
                                                !empty($company_payment_setting['PayHere_merchant_id']) &&
                                                (isset($company_payment_setting['PayHere_merchant_secret_key']) &&
                                                    !empty($company_payment_setting['PayHere_merchant_secret_key'])))
                                            <li class="mb-2 nav-item">
                                                <button class="btn btn-outline-primary btn-sm" id="pills-home-tab"
                                                    data-bs-toggle="pill" data-bs-target="#PayHere-payment"
                                                    role="tab" aria-controls="PayHere" type="button"
                                                    aria-selected="false">{{ __('PayHere') }}</button>
                                            </li>&nbsp;
                                        @endif
                                    @endif


                                </ul>
                            @endif

                            <div class="tab-content">
                                @if (isset($company_payment_setting['is_bank_transfer_enabled']) &&
                                        $company_payment_setting['is_bank_transfer_enabled'] == 'on')
                                    @if (isset($company_payment_setting['bank_details']) && !empty($company_payment_setting['bank_details']))
                                        <div class="tab-pane fade active show" id="banktransfer-payment" role="tabpanel"
                                            aria-labelledby="banktransfer-payment-tab">
                                            <div class="card-body">
                                                <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                    id="payment-form"
                                                    action="{{ route('invoice.pay.with.banktransfer') }}"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    {!! isset($company_payment_setting['bank_details']) ? $company_payment_setting['bank_details'] : '' !!}
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
                                                                {{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}</span>
                                                            <div class="form-icon-addon">
                                                                <!-- <span> {{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}</span> -->
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
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                @if (
                                    !empty($company_payment_setting) &&
                                        ($company_payment_setting['is_stripe_enabled'] == 'on' &&
                                            !empty($company_payment_setting['stripe_key']) &&
                                            !empty($company_payment_setting['stripe_secret'])))
                                    <div class="tab-pane fade " id="stripe-payment" role="tabpanel"
                                        aria-labelledby="stripe-payment">
                                        <form method="post"
                                            action="{{ route('client.invoice.payment', $invoice->id) }}"
                                            class="require-validation" id="payment-form">
                                            @csrf
                                            <div class="row">
                                                <div class="col-sm-8">
                                                    <div class="custom-radio">
                                                        <label
                                                            class="font-16 font-weight-bold">{{ __('Credit / Debit Card') }}</label>
                                                    </div>
                                                    <p class="pt-1 mb-0 text-sm">
                                                        {{ __('Safe money transfer using your bank account. We support Mastercard, Visa, Discover and American express.') }}
                                                    </p>
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="card-name-on">{{ __('Name on card') }}</label>
                                                        <input type="text" name="name" id="card-name-on"
                                                            class="form-control required"
                                                            placeholder="{{ \Auth::user()->name }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div id="card-element">

                                                    </div>
                                                    <div id="card-errors" role="alert"></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <br>
                                                    <label for="amount">{{ __('Amount') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend"><span
                                                                class="input-group-text">{{ Utility::getValByName('site_currency') }}</span></span>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="error" style="display: none;">
                                                        <div class='alert-danger alert'>
                                                            {{ __('Please correct the errors and try again.') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3 form-group">
                                                <button class="btn btn-sm btn-primary rounded-pill"
                                                    type="submit">{{ __('Make Payment') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                                @if (
                                    !empty($company_payment_setting) &&
                                        ($company_payment_setting['is_paypal_enabled'] == 'on' &&
                                            !empty($company_payment_setting['paypal_client_id']) &&
                                            !empty($company_payment_setting['paypal_secret_key'])))
                                    <div class="tab-pane fade " id="paypal-payment" role="tabpanel"
                                        aria-labelledby="paypal-payment">
                                        <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                            id="payment-form"
                                            action="{{ route('client.pay.with.paypal', $invoice->id) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="amount">{{ __('Amount') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend"><span
                                                                class="input-group-text">{{ Utility::getValByName('site_currency') }}</span></span>
                                                        <input class="form-control" required="required" min="0"
                                                            name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                        @error('amount')
                                                            <span class="invalid-amount" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3 form-group">
                                                <button class="btn btn-sm btn-primary rounded-pill" name="submit"
                                                    type="submit">{{ __('Make Payment') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif


                                @if (
                                    !empty($company_payment_setting) &&
                                        ($company_payment_setting['is_paystack_enabled'] == 'on' &&
                                            !empty($company_payment_setting['paystack_public_key']) &&
                                            !empty($company_payment_setting['paystack_secret_key'])))
                                    <div class="tab-pane fade " id="paystack-payment" role="tabpanel"
                                        aria-labelledby="paystack-payment">
                                        <form class="w3-container w3-display-middle w3-card-4" method="POST"
                                            id="paystack-payment-form"
                                            action="{{ route('invoice.pay.with.paystack') }}">
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
                                                <input class="btn btn-sm btn-primary rounded-pill" id="pay_with_paystack"
                                                    type="button" value="{{ __('Make Payment') }}">
                                            </div>

                                        </form>
                                    </div>
                                @endif

                                @if (
                                    !empty($company_payment_setting) &&
                                        $company_payment_setting['is_flutterwave_enabled'] == 'on' &&
                                        !empty($company_payment_setting['flutterwave_public_key']) &&
                                        !empty($company_payment_setting['flutterwave_secret_key']))
                                    <div class="tab-pane fade " id="flutterwave-payment" role="tabpanel"
                                        aria-labelledby="flutterwave-payment">

                                        <form role="form" action="{{ route('invoice.pay.with.flaterwave') }}"
                                            method="post" class="require-validation" id="flaterwave-payment-form">
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
                                                <input class="btn btn-sm btn-primary rounded-pill"
                                                    id="pay_with_flaterwave" type="button"
                                                    value="{{ __('Make Payment') }}">
                                            </div>

                                        </form>
                                    </div>
                                @endif

                                @if (isset($company_payment_setting['is_razorpay_enabled']) && $company_payment_setting['is_razorpay_enabled'] == 'on')
                                    <div class="tab-pane fade " id="razorpay-payment" role="tabpanel"
                                        aria-labelledby="razorpay-payment">
                                        <form role="form" action="{{ route('invoice.pay.with.razorpay') }}"
                                            method="post" class="require-validation" id="razorpay-payment-form">
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
                                                <input class="btn btn-sm btn-primary rounded-pill" id="pay_with_razorpay"
                                                    type="button" value="{{ __('Make Payment') }}">
                                            </div>

                                        </form>
                                    </div>
                                @endif

                                @if (isset($company_payment_setting['is_mercado_enabled']) && $company_payment_setting['is_mercado_enabled'] == 'on')
                                    <div class="tab-pane fade " id="mercado-payment" role="tabpanel"
                                        aria-labelledby="mercado-payment">
                                        <form role="form" action="{{ route('invoice.pay.with.mercado') }}"
                                            method="post" class="require-validation" id="mercado-payment-form">
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
                                                <input type="submit" id="pay_with_mercado"
                                                    value="{{ __('Make Payment') }}"
                                                    class="btn btn-sm btn-primary rounded-pill">
                                            </div>

                                        </form>
                                    </div>
                                @endif

                                @if (isset($company_payment_setting['is_paytm_enabled']) && $company_payment_setting['is_paytm_enabled'] == 'on')
                                    <div class="tab-pane fade" id="paytm-payment" role="tabpanel"
                                        aria-labelledby="paytm-payment">
                                        <form role="form" action="{{ route('invoice.pay.with.paytm') }}"
                                            method="post" class="require-validation" id="paytm-payment-form">
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
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="flaterwave_coupon"
                                                        class=" text-dark">{{ __('Mobile Number') }}</label>
                                                    <input type="text" id="mobile" name="mobile"
                                                        class="form-control mobile" data-from="mobile"
                                                        placeholder="{{ __('Enter Mobile Number') }}" required>
                                                </div>
                                            </div>
                                            <div class="mt-3 form-group">
                                                <input type="submit" id="pay_with_paytm"
                                                    value="{{ __('Make Payment') }}"
                                                    class="btn btn-sm btn-primary rounded-pill">
                                            </div>

                                        </form>
                                    </div>
                                @endif

                                @if (isset($company_payment_setting['is_mollie_enabled']) && $company_payment_setting['is_mollie_enabled'] == 'on')
                                    <div class="tab-pane fade " id="mollie-payment" role="tabpanel"
                                        aria-labelledby="mollie-payment">
                                        <form role="form" action="{{ route('invoice.pay.with.mollie') }}"
                                            method="post" class="require-validation" id="mollie-payment-form">
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
                                                <input type="submit" id="pay_with_mollie"
                                                    value="{{ __('Make Payment') }}"
                                                    class="btn btn-sm btn-primary rounded-pill">
                                            </div>

                                        </form>
                                    </div>
                                @endif

                                @if (isset($company_payment_setting['is_skrill_enabled']) && $company_payment_setting['is_skrill_enabled'] == 'on')
                                    <div class="tab-pane fade " id="skrill-payment" role="tabpanel"
                                        aria-labelledby="skrill-payment">
                                        <form role="form" action="{{ route('invoice.pay.with.skrill') }}"
                                            method="post" class="require-validation" id="skrill-payment-form">
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
                                            @php
                                                $skrill_data = [
                                                    'transaction_id' => md5(
                                                        date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id',
                                                    ),
                                                    'user_id' => 'user_id',
                                                    'amount' => 'amount',
                                                    'currency' => 'currency',
                                                ];
                                                session()->put('skrill_data', $skrill_data);

                                            @endphp
                                            <div class="mt-3 form-group">
                                                <input type="submit" id="pay_with_skrill"
                                                    value="{{ __('Make Payment') }}"
                                                    class="btn btn-sm btn-primary rounded-pill">
                                            </div>

                                        </form>
                                    </div>
                                @endif

                                @if (isset($company_payment_setting['is_coingate_enabled']) && $company_payment_setting['is_coingate_enabled'] == 'on')
                                    <div class="tab-pane fade " id="coingate-payment" role="tabpanel"
                                        aria-labelledby="coingate-payment">
                                        <form role="form" action="{{ route('invoice.pay.with.coingate') }}"
                                            method="post" class="require-validation" id="coingate-payment-form">
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
                                                <input type="submit" id="pay_with_coingate"
                                                    value="{{ __('Make Payment') }}"
                                                    class="btn btn-sm btn-primary rounded-pill">
                                            </div>

                                        </form>
                                    </div>
                                @endif

                                @if (
                                    !empty($company_payment_setting) &&
                                        ($company_payment_setting['is_paymentwall_enabled'] == 'on' &&
                                            !empty($company_payment_setting['paymentwall_public_key']) &&
                                            !empty($company_payment_setting['paymentwall_private_key'])))
                                    <div class="tab-pane fade " id="paymentwall-payment" role="tabpanel"
                                        aria-labelledby="paymentwall-payment">
                                        <form class="w3-container w3-display-middle w3-card-4" method="POST"
                                            id="paymentwall-payment-form"
                                            action="{{ route('invoice.paymentwallpayment') }}">
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
                                                <input class="btn btn-sm btn-primary rounded-pill"
                                                    id="pay_with_paymentwall" type="submit"
                                                    value="{{ __('Make Payment') }}">
                                            </div>

                                        </form>
                                    </div>
                                @endif

                                @if (
                                    !empty($company_payment_setting) &&
                                        ($company_payment_setting['is_toyyibpay_enabled'] == 'on' &&
                                            !empty($company_payment_setting['toyyibpay_secret_key']) &&
                                            !empty($company_payment_setting['category_code'])))
                                    <div class="tab-pane fade " id="toyyibpay-payment" role="tabpanel"
                                        aria-labelledby="toyyibpay-payment">
                                        <form class="w3-container w3-display-middle w3-card-4" method="POST"
                                            id="toyyibpay-payment-form"
                                            action="{{ route('invoice.toyyibpaypayment') }}">
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
                                                <input class="btn btn-sm btn-primary rounded-pill"
                                                    id="pay_with_toyyibpay" type="submit"
                                                    value="{{ __('Make Payment') }}">
                                            </div>

                                        </form>
                                    </div>
                                @endif

                                {{-- payfast payment --}}
                                @if (isset($company_payment_setting['is_payfast_enabled']) && $company_payment_setting['is_payfast_enabled'] == 'on')
                                    @if (isset($company_payment_setting['payfast_merchant_id']) &&
                                            !empty($company_payment_setting['payfast_merchant_id']) &&
                                            (isset($company_payment_setting['payfast_merchant_key']) &&
                                                !empty($company_payment_setting['payfast_merchant_key'])))
                                        <div class="tab-pane fade" id="payfast-payment" role="tabpanel"
                                            aria-labelledby="payfast-payment-tab">
                                            @php
                                                $pfHost =
                                                    $company_payment_setting['payfast_mode'] == 'sandbox'
                                                        ? 'sandbox.payfast.co.za'
                                                        : 'www.payfast.co.za';
                                            @endphp
                                            <form action={{ 'https://' . $pfHost . '/eng/process' }} method="post"
                                                class="require-validation" id="payfast-form">
                                                {{-- <form action="{{ route('invoice-pay-with-payfast') }}" method="post" class="require-validation" id="payfast-payment-form"> --}}
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="col-form-lable">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <span class="input-group-prepend"><span
                                                                    class="input-group-text">{{ Utility::getValByName('site_currency') }}</span></span>
                                                            <input type="number" class="form-control input_payfast"
                                                                required min="0" name="amount"
                                                                value="{{ $invoice->getDue() }}" step="0.01"
                                                                max="{{ $invoice->getDue() }}" id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id" id="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="get-payfast-inputs"></div>
                                                <div class="mt-3 text-right col-12 form-group">
                                                    <input type="submit" class="btn btn-sm btn-primary rounded-pill"
                                                        id="pay_with_payfast" value="{{ __('Make Payment') }}">
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                @endif

                                {{-- iyzipay payment start --}}
                                @if (isset($company_payment_setting['is_iyzipay_enabled']) && $company_payment_setting['is_iyzipay_enabled'] == 'on')
                                    @if (isset($company_payment_setting['iyzipay_public_key']) &&
                                            !empty($company_payment_setting['iyzipay_public_key']) &&
                                            (isset($company_payment_setting['iyzipay_secret_key']) &&
                                                !empty($company_payment_setting['iyzipay_secret_key'])))
                                        <div class="tab-pane fade" id="iyzipay-payment" role="tabpanel"
                                            aria-labelledby="iyzipay-payment-tab">
                                            <div class="card-body">
                                                {{-- @dd($invoice->id); --}}
                                                <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                    id="payment-form"
                                                    action="{{ route('client.pay.with.iyzipay', $invoice->id) }}">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label for="amount"
                                                                class="form-control-label">{{ __('Amount') }}</label>
                                                            {{-- <span>{{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}</span> --}}
                                                            <div class="input-group col-md-12">
                                                                <span class="input-group-prepend"><span
                                                                        class="input-group-text">{{ Utility::getValByName('site_currency') }}</span></span>
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
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                @if (
                                    !empty($company_payment_setting) &&
                                        ($company_payment_setting['is_sspay_enabled'] == 'on' &&
                                            !empty($company_payment_setting['sspay_secret_key']) &&
                                            !empty($company_payment_setting['sspay_category_code'])))
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

                                @if (isset($company_payment_setting['is_paytab_enabled']) && $company_payment_setting['is_paytab_enabled'] == 'on')
                                    @if (isset($company_payment_setting['paytab_profile_id']) &&
                                            !empty($company_payment_setting['paytab_profile_id']) &&
                                            (isset($company_payment_setting['paytab_server_key']) &&
                                                !empty($company_payment_setting['paytab_server_key'])) &&
                                            (isset($company_payment_setting['paytab_region']) && !empty($company_payment_setting['paytab_region'])))
                                        <div class="tab-pane fade" id="paytab-payment" role="tabpanel"
                                            aria-labelledby="paytab-payment">

                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form"
                                                action="{{ route('pay.with.paytab', $invoice->id) }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}
                                                            </div>
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
                                        </div>
                                    @endif
                                @endif

                                {{-- benefit --}}

                                @if (isset($company_payment_setting['is_benefit_enabled']) && $company_payment_setting['is_benefit_enabled'] == 'on')
                                    @if (isset($company_payment_setting['benefit_api_key']) &&
                                            !empty($company_payment_setting['benefit_api_key']) &&
                                            (isset($company_payment_setting['benefit_secret_key']) &&
                                                !empty($company_payment_setting['benefit_secret_key'])))
                                        <div class="tab-pane fade" id="benefit-payment" role="tabpanel"
                                            aria-labelledby="benefit-payment">

                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form"
                                                action="{{ route('pay.with.benefit', $invoice->id) }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}
                                                            </div>
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
                                        </div>
                                    @endif
                                @endif

                                {{-- cashfree --}}
                                @if (isset($company_payment_setting['is_cashfree_enabled']) && $company_payment_setting['is_cashfree_enabled'] == 'on')
                                    @if (isset($company_payment_setting['cashfree_api_key']) &&
                                            !empty($company_payment_setting['cashfree_api_key']) &&
                                            (isset($company_payment_setting['cashfree_secret_key']) &&
                                                !empty($company_payment_setting['cashfree_secret_key'])))
                                        <div class="tab-pane fade" id="cashfree-payment" role="tabpanel"
                                            aria-labelledby="cashfree-payment">

                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form"
                                                action="{{ route('pay.with.cashfree', $invoice->id) }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}
                                                            </div>
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
                                        </div>
                                    @endif
                                @endif

                                {{-- aamarpay --}}
                                @if (isset($company_payment_setting['is_aamarpay_enabled']) && $company_payment_setting['is_aamarpay_enabled'] == 'on')
                                    @if (isset($company_payment_setting['aamarpay_store_id']) &&
                                            !empty($company_payment_setting['aamarpay_store_id']) &&
                                            (isset($company_payment_setting['aamarpay_signature_key']) &&
                                                !empty($company_payment_setting['aamarpay_signature_key'])) &&
                                            (isset($company_payment_setting['aamarpay_description']) &&
                                                !empty($company_payment_setting['aamarpay_description'])))
                                        <div class="tab-pane fade" id="aamarpay-payment" role="tabpanel"
                                            aria-labelledby="aamarpay-payment">

                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form"
                                                action="{{ route('pay.with.aamarpay', $invoice->id) }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}
                                                            </div>
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
                                        </div>
                                    @endif
                                @endif

                                {{-- paytr --}}
                                @if (isset($company_payment_setting['is_paytr_enabled']) && $company_payment_setting['is_paytr_enabled'] == 'on')
                                    @if (isset($company_payment_setting['paytr_merchant_id']) &&
                                            !empty($company_payment_setting['paytr_merchant_id']) &&
                                            (isset($company_payment_setting['paytr_merchant_key']) &&
                                                !empty($company_payment_setting['paytr_merchant_key'])) &&
                                            (isset($company_payment_setting['paytr_merchant_salt']) &&
                                                !empty($company_payment_setting['paytr_merchant_salt'])))
                                        <div class="tab-pane fade" id="paytr-payment" role="tabpanel"
                                            aria-labelledby="paytr-payment">

                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form"
                                                action="{{ route('pay.with.paytr', $invoice->id) }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}
                                                            </div>
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
                                        </div>
                                    @endif
                                @endif

                                {{-- yookassa --}}
                                @if (isset($company_payment_setting['is_yookassa_enabled']) && $company_payment_setting['is_yookassa_enabled'] == 'on')
                                    @if (isset($company_payment_setting['is_yookassa_enabled']) &&
                                            !empty($company_payment_setting['is_yookassa_enabled']) &&
                                            (isset($company_payment_setting['yookassa_shop_id']) && !empty($company_payment_setting['yookassa_shop_id'])) &&
                                            (isset($company_payment_setting['yookassa_secret_key']) &&
                                                !empty($company_payment_setting['yookassa_secret_key'])))
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
                                                                {{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}
                                                            </div>
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
                                        </div>
                                    @endif
                                @endif

                                {{-- Midtrans --}}
                                @if (isset($company_payment_setting['is_midtrans_enabled']) && $company_payment_setting['is_midtrans_enabled'] == 'on')
                                    @if (isset($company_payment_setting['is_midtrans_enabled']) &&
                                            !empty($company_payment_setting['is_midtrans_enabled']) &&
                                            (isset($company_payment_setting['midtrans_secret']) && !empty($company_payment_setting['midtrans_secret'])))
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
                                                                {{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}
                                                            </div>
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
                                        </div>
                                    @endif
                                @endif

                                {{-- Xendit --}}
                                @if (isset($company_payment_setting['is_xendit_enabled']) && $company_payment_setting['is_xendit_enabled'] == 'on')
                                    @if (isset($company_payment_setting['is_xendit_enabled']) &&
                                            !empty($company_payment_setting['is_xendit_enabled']) &&
                                            (isset($company_payment_setting['xendit_api_key']) && !empty($company_payment_setting['xendit_api_key'])) &&
                                            (isset($company_payment_setting['xendit_token']) && !empty($company_payment_setting['xendit_token'])))
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
                                                                {{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}
                                                            </div>
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
                                        </div>
                                    @endif
                                @endif


                                {{-- PaiementPro --}}
                                @if (isset($company_payment_setting['is_PaiementPro_enabled']) &&
                                        $company_payment_setting['is_PaiementPro_enabled'] == 'on')
                                    <div class="tab-pane fade" id="paiementpro-payment" role="tabpanel"
                                        aria-labelledby="paiementpro-payment">
                                        <form role="form" action="{{ route('invoice.pay.with.paiementPro') }}"
                                            method="post" class="require-validation" id="paiementpro-payment-form">
                                            @csrf
                                            <input type="hidden" name="invoice_id"
                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}">

                                            <div class="form-group col-md-12">
                                                <label for="amount">{{ __('Amount') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-prepend"><span
                                                            class="input-group-text">{{ Utility::getValByName('site_currency') }}</span></span>
                                                    <input class="form-control" required="required" min="0"
                                                        name="amount" type="number"
                                                        value="{{ $invoice->getDue() }}" min="0"
                                                        step="0.01" max="{{ $invoice->getDue() }}"
                                                        id="amount">

                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="flaterwave_coupon"
                                                        class=" text-dark">{{ __('Mobile Number') }}</label>
                                                    <input type="text" id="mobileno" name="mobileno"
                                                        class="form-control mobileno" data-from="mobileno"
                                                        placeholder="{{ __('Enter Mobile Number') }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="flaterwave_coupon"
                                                        class=" text-dark">{{ __('Channel') }}</label>
                                                    <input type="text" id="Channle" name="Channle"
                                                        class="form-control Channle" data-from="Channle"
                                                        placeholder="{{ __('Enter Channel') }}" required>

                                                </div>
                                            </div>
                                            <div class="mt-3 form-group">
                                                <input type="submit" id="pay_with_PaiementPro"
                                                    value="{{ __('Make Payment') }}"
                                                    class="btn btn-sm btn-primary rounded-pill">
                                            </div>

                                        </form>
                                    </div>
                                @endif


                                @if (isset($company_payment_setting['is_nepalste_enabled']) && $company_payment_setting['is_nepalste_enabled'] == 'on')
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
                                                            class="input-group-text">{{ $company_setting['site_currency'] }}</span></span>
                                                    <input class="form-control" required="required" min="0"
                                                        name="amount" type="number"
                                                        value="{{ $invoice->getDue() }}" min="0"
                                                        step="0.01" max="{{ $invoice->getDue() }}"
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



                                @if (isset($company_payment_setting['is_CinetPay_enabled']) && $company_payment_setting['is_CinetPay_enabled'] == 'on')
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
                                                            class="input-group-text">{{ isset($company_payment_setting['currency_symbol']) ? $company_payment_setting['currency_symbol'] : '$' }}</span></span>
                                                    <input class="form-control" required="required" min="0"
                                                        name="amount" type="number"
                                                        value="{{ $invoice->getDue() }}" min="0"
                                                        step="0.01" max="{{ $invoice->getDue() }}"
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



                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

 @endsection
