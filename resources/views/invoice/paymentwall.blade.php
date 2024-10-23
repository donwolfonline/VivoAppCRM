@php
  $invoice = $data['invoice_id'];
  $invoice_id = Crypt::decrypt($invoice);
  // dd($invoice_id);
  $price = $data['amount'];
  $logo=\App\Models\Utility::get_file('uploads/logo/');
  $company_favicon=Utility::getValByName('company_favicon');
@endphp

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> Invoice PaymentWall - {{(Utility::getValByName('title_text')) ? Utility::getValByName('title_text') : config('app.name', 'CRMGo')}}</title>
    <link rel="icon" href="{{$logo.'/'.(isset($company_favicon) && !empty($company_favicon)?$company_favicon:'favicon.png')}}" type="image/png">
</head>

<script src="https://api.paymentwall.com/brick/build/brick-default.1.5.0.min.js"> </script>
<div id="payment-form-container"> </div>
<script>
  var brick = new Brick({
    public_key: '{{ $company_payment_setting['paymentwall_public_key'] }}', // please update it to Brick live key before launch your project
    amount: '{{ $price }}',
    currency: '{{$settings['site_currency'] }}',
    container: 'payment-form-container',
    action: '{{route("invoice.pay.with.paymentwall",[$data["invoice_id"],"amount" => $data["amount"]])}}',
    form: {
      merchant: 'Paymentwall',
      product: '{{Utility::invoiceNumberFormat($settings,$invoice_id)}}',
      pay_button: 'Pay',
      show_zip: true, // show zip code 
      show_cardholder: true // show card holder name 
    }
});

brick.showPaymentForm(function(data) {
      if(data.flag == 1){
        console.log('dsfrserf');
        window.location.href ='{{route("error.invoice.show",[1, 'invoice_id'])}}'.replace('invoice_id',data.invoice);
      }else{
        console.log('22222');
        window.location.href ='{{route("error.invoice.show",[2, 'invoice_id'])}}'.replace('invoice_id',data.invoice);
      }
    }, function(errors) {
      if(errors.flag == 1){
        console.log('xcfdr');
        window.location.href ='{{route("error.invoice.show",[1,'invoice_id'])}}'.replace('invoice_id',errors.invoice);
      }else{
        console.log('11111');
        window.location.href ='{{route("error.invoice.show",[2, 'invoice_id'])}}'.replace('invoice_id',errors.invoice);
      }
      	   
    });
  
</script>