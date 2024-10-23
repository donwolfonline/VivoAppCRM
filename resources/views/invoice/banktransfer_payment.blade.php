{{-- {{ Form::model($invoice_banktransfer, array('route' => array('payment.approval', $invoice_banktransfer->id), 'method' => 'get')) }} --}}
{{Form::open(array('url'=>'','method'=>'get'))}}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label" for="order_id"
                class="form-label">{{ __('Order Id') }}</label><br>
        </div>
        <div class="col-md-6 form-group">
            {!! $invoice_banktransfer->order_id !!}
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label" for="invoice_name"
                class="form-label">{{ __('Invoice Name') }}</label><br>
        </div>
        <div class="col-md-6 form-group">
            {!! $user->invoiceNumberFormat($invoice->invoice_id) !!}
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label" for="amount"
                class="form-label">{{ __('Amount') }}</label><br>
        </div>
        <div class="col-md-6 form-group">
            {!! $invoice_banktransfer->amount !!}
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label" for="payment_type"
                class="form-label">{{ __('Payment Type') }}</label><br>
        </div>
        <div class="col-md-6 form-group">
            {{ __('Bank Transfer') }}
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="form-label" for="payment_status"
                class="form-label">{{ __('Payment Status') }}</label><br>
        </div>
        <div class="col-md-6 form-group">
            {!! $invoice_banktransfer->status !!}

        </div>
    </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label class="form-label" for="bank_details"
                    class="form-label">{{ __('Bank Details : ') }}</label><br>
            </div>
            <div class="col-md-6 form-group">
                {!! isset($company_payment_settings['bank_details']) ? $company_payment_settings['bank_details'] : '' !!}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label class="form-label" for="receipt"
                    class="form-label">{{ __('Receipt') }}</label><br>
            </div>
            <div class="col-md-6 form-group">
                <a href="{{ asset('storage/invoice_payment_receipt/'.$invoice_banktransfer->receipt) }}" class="btn btn-primary btn-sm" download>
                    <i class="ti ti-download text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Download') }}"></i></a>
                {{-- {!! $order->receipt !!} --}}
            </div>
        </div>
</div>
<div class="modal-footer">
    {{-- {{ Form::hidden('payment_approval',null,['class' => 'payment_approval']) }} --}}

    <a href="{{ route('bankpaymenttransfer.approve', [$invoice_banktransfer->id, 1]) }}" class="btn btn-success">{{ __('Approval') }}</a>
    <a href="{{ route('bankpaymenttransfer.reject', [$invoice_banktransfer->id, 0]) }}" class="btn btn-danger">{{ __('Reject') }}</a>

    {{-- <button type="submit" class="btn  btn-primary approvepayment_request_button">{{__('Approval')}}</button>
    <button type="submit" value="reject" class="btn  btn-danger denypayment_request_button" data-bs-dismiss="modal">{{__('Reject')}}</button> --}}

</div>

{{ Form::close() }}

