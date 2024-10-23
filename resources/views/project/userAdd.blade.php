{{ Form::open(array('route' => array('project.user.add',$id))) }}
<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('user', __('User'),['class' => 'col-form-label']) }}
        {!! Form::select('user[]', $employee, null,array('class' => 'form-control multi-select','required'=>'required')) !!}
    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Add'),array('class'=>'btn  btn-primary'))}}
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