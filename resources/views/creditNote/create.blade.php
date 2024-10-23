{{ Form::open(array('url' => 'creditNote')) }}
<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('invoice', __('Invoice'),['class' => 'col-form-label']) }}
        <select class="form-control customer-sel font-style multi-select" id="invoice" name="invoice">
            <option value="">{{__('Select Invoice')}}</option>
            @foreach($invoices as $key=>$invoice)
                <option value="{{$key}}">{{\Auth::user()->invoiceNumberFormat($invoice)}}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('amount', __('Amount'),['class' => 'col-form-label']) }}
        {{ Form::number('amount', null, array('class' => 'form-control','required'=>'required','id'=>'amount')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('date', __('Date'),['class' => 'col-form-label']) }}
        {{ Form::date('date', new \DateTime(), array('class' => 'form-control','required'=>'required')) }}
    </div>
</div>

<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
        {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2']) !!}
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
</div>

{{ Form::close() }}


<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script>
    if ($(".multi-select").length > 0) {
              $( $(".multi-select") ).each(function( index,element ) {
                  var id = $(element).attr('id');
                     var multipleCancelButton = new Choices(
                          '#'+id, {
                              removeItemButton: true,
                          }
                      );
              });
         }
  </script>