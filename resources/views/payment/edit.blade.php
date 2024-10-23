{{ Form::model($payment,array('route' => array('payment.update',$payment->id),'method'=>'PUT')) }}
<div class="row">
    <div class="form-group  col-md-6">
        {{ Form::label('date', __('Date'),['class' => 'col-form-label']) }}
        {{ Form::date('date', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('amount', __('Amount'),['class' => 'col-form-label']) }}
        {{ Form::number('amount', null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('payment_method', __('Client'),['class' => 'col-form-label']) }}
        {{ Form::select('payment_method', $paymentMethod,null, array('class' => 'form-control multi-select','required'=>'required')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('client', __('Client'),['class' => 'col-form-label']) }}
        {{ Form::select('client', $clients,null, array('class' => 'form-control multi-select','required'=>'required')) }}
    </div>
    <div class="form-group  col-md-12">
        {{ Form::label('reference', __('Reference'),['class' => 'col-form-label']) }}
        {{ Form::text('reference', null, array('class' => 'form-control')) }}
    </div>

    <div class="form-group  col-md-12">
        {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
        {{ Form::textarea('description', null, array('class' => 'form-control','rows'=>'2')) }}
    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
    </div>
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

