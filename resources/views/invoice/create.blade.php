{{ Form::open(array('url' => 'invoice')) }}
<div class="row">
    <div class="form-group col-md-6">
        {{ Form::label('issue_date', __('Issue Date'),['class' => 'col-form-label']) }}
        {{ Form::date('issue_date', new \DateTime(), array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('due_date', __('Due Date'),['class' => 'col-form-label']) }}
        {{ Form::date('due_date', new \DateTime(), array('class' => 'form-control','required'=>'required')) }}
    </div>
  
    <div class="form-group col-md-6">
            <label class="d-block col-form-label">{{__('Type')}}</label>
            <div class="row">
                <div class="form-check col-md-6">
                    <div class="custom-control custom-radio">
                        <input type="radio" class="form-check-input type" id="customRadio5" name="type" value="Product" checked="checked">
                        <label class="custom-control-label" for="customRadio5">{{__('Product')}}</label>
                    </div>
                </div>
                <div class="form-check col-md-6">
                    <div class="custom-control custom-radio">
                        <input type="radio" class="form-check-input type" id="customRadio6" name="type" value="Project">
                        <label class="custom-control-label" for="customRadio6">{{__('Project')}}</label>
                    </div>
                </div>
            </div>
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('client', __('Client'),['class' => 'col-form-label']) }}
        {{ Form::select('client', $clients,null, array('class' => 'form-control multi-select','required'=>'required')) }}
    </div>

    <div class="form-group col-md-6 project-field d-none">
        {{ Form::label('project', __('Project'),['class' => 'col-form-label']) }}
        <select class="form-control  user" data-toggle="select" name="project" id="project">
        </select>
    </div>
    <div class="form-group col-md-6 project-field d-none">
        {{ Form::label('tax', __('Tax'),['class' => 'col-form-label']) }}
        {{ Form::select('tax[]', $taxes,null, array('class' => 'form-control multi-select','id'=>'choices-multiple','multiple'=>'')) }}
    </div>
    
</div>

<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
        {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2']) !!}
    </div>
</div>
<div class="modal-footer">
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