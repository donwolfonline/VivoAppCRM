{{ Form::open(['route' => ['invoice.store.receipt', $invoice->id], 'enctype' => 'multipart/form-data']) }}
<div class="row">
    <div class="form-group col-md-6">
        {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}
        {{ Form::number('amount', $invoice->getDue(), ['class' => 'form-control']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}
        {{ Form::date('date', new \DateTime(), ['class' => 'form-control quantity', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('payment_method', __('Payment Method'), ['class' => 'col-form-label']) }}
        {{ Form::select('payment_method', $paymentMethods, null, ['class' => 'form-control multi-select', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('receipt', __('Payment Receipt'), ['class' => 'col-form-label']) }}
        {{ Form::file('receipt', ['class' => 'form-control', 'accept' => '.jpeg,.jpg,.png,.doc,.pdf', 'id' => 'files']) }}
        <img id="image" class="mt-2" style="width:25%;" />
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => '2']) !!}
    </div>

    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
    </div>
</div>

{{ Form::close() }}

<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script>
    if ($(".multi-select").length > 0) {
        $($(".multi-select")).each(function(index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#' + id, {
                    removeItemButton: true,
                }
            );
        });
    }
</script>
<script>
    document.getElementById('files').onchange = function() {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image').src = src
    }
</script>
