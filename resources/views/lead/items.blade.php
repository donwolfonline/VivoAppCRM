{{ Form::model($lead, array('route' => array('lead.items.update', $lead->id), 'method' => 'post')) }}
<div class="form-group">
    {{ Form::label('name', __('Items'),['class' => 'col-form-label']) }}
    {{ Form::select('items[]', $products,false, array('class' => 'form-control multi-select','id'=>'choices-multiple','data-toggle="select"','multiple'=>'','required'=>'required')) }}
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Add'),array('class'=>'btn  btn-primary'))}}
</div>
{{ Form::close() }}

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

