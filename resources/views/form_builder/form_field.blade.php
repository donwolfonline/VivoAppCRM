{{ Form::model($formField, array('route' => array('form.bind.store', $form->id))) }}
<div class="row">
    <div class="col-12 pb-3">
        <span class="text-xs"><b>{{__('It will auto convert from response on lead based on below setting. It will not convert old response.')}}</b></span>
    </div>
</div>
<div class="row px-2">
    <div class="col-4">
        <div class="form-group">
            {{ Form::label('active', __('Active'),['class'=>'form-control-label']) }}
        </div>
    </div>
    <div class="col-8">
        <div class="form-check form-check-inline"> 
            <input class="form-check-input lead_radio" type="radio" name="is_lead_active" value="1"
                id="on" {{($form->is_lead_active == 1) ? 'checked' : ''}}>
            <label class="form-check-label" for="on">
                {{__('On')}}
            </label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input lead_radio" type="radio" name="is_lead_active" value="0"
                id="off" {{($form->is_lead_active == 0) ? 'checked' : ''}}>
            <label class="form-check-label" for="off">
                {{__('Off')}}
            </label>
        </div>

      
    </div>
</div>
<div id="lead_activated" class="d-none">
    <div class="row px-2">
        <div class="col-4  m-auto">
            <div class="col-form-label">
                {{ Form::label('subject_id', __('Subject'),['class'=>'form-control-label']) }}
            </div>
        </div>
        <div class="col-8">
            <div class="col-form-label">
                {{ Form::select('subject_id', $types,null, array('class' => 'form-control multi-select')) }}
            </div>
        </div>
        <div class="col-4  m-auto">
            <div class="col-form-label">
                {{ Form::label('name_id', __('Name'),['class'=>'form-control-label']) }}
            </div>
        </div>
        <div class="col-8  m-auto">
            <div class="col-form-label">
                {{ Form::select('name_id', $types,null, array('class' => 'form-control multi-select')) }}
            </div>
        </div>
        <div class="col-4 m-auto">
            <div class="col-form-label">
                {{ Form::label('phone_id', __('Phone'),['class'=>'form-control-label']) }}
            </div>
        </div>
        <div class="col-8">
            <div class="col-form-label">
                {{ Form::select('phone_id', $types,null, array('class' => 'form-control multi-select')) }}
            </div>
        </div>
        <div class="col-4  m-auto">
            {{ Form::label('email_id', __('Email'),['class'=>'form-control-label']) }}
        </div>
        <div class="col-8">
            <div class="col-form-label">
                {{ Form::select('email_id', $types,null, array('class' => 'form-control multi-select')) }}
            </div>
            {{ Form::hidden('form_id',$form->id) }}
            {{ Form::hidden('form_response_id',(!empty($formField)) ? $formField->id : '') }}
        </div>
        <div class="col-4  m-auto">
            {{ Form::label('user_id', __('User'),['class'=>'form-control-label']) }}
        </div>
        <div class="col-8">
            <div class="col-form-label">
                {{ Form::select('user_id', $users,null, array('class' => 'form-control multi-select')) }}
                @if(count($users) == 0)
                    <div class="text-muted text-xs">
                        {{__('Please create new employee')}} <a href="{{ route('employee.index') }}" >{{__('here')}}</a>.
                    </div>
                @endif
            </div>
        </div>
        <div class="col-4 m-auto">
            {{ Form::label('pipeline_id', __('Pipelines'),['class'=>'form-control-label']) }}
        </div>
        <div class="col-8">
            <div class="col-form-label">
                {{ Form::select('pipeline_id', $pipelines,null, array('class' => 'form-control multi-select')) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
</div>
{{ Form::close() }}


<script>
    $(document).ready(function () {
        var lead_active = {{$form->is_lead_active}};
        if (lead_active == 1) {
            $('#lead_activated').removeClass('d-none');
        }
    });
    $(document).on('click', function () {
        $('.lead_radio').on('click', function () {
            var inputValue = $(this).attr("value");
            if (inputValue == 1) {
                $('#lead_activated').removeClass('d-none');
            } else {
                $('#lead_activated').addClass('d-none');
            }
            $('.lead_radio').removeAttr('checked');
            $(this).prop("checked", true);
        })
    });
</script>

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
