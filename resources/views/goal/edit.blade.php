{{ Form::model($goal,array('route' => array('goal.update',$goal->id),'method'=>'PUT')) }}
@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">
    <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
    data-url="{{ route('generate',['goal']) }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
 </div>
 @endif
    <div class="form-group col-md-12">
        {{ Form::label('name', __('Name'),['class' => 'col-form-label']) }}
        {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('amount', __('Amount'),['class' => 'col-form-label']) }}
        {{ Form::number('amount', null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('goal_type', __('Type'),['class' => 'col-form-label']) }}
        {{ Form::select('goal_type',$types,null, array('class' => 'form-control multi-select','required'=>'required')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('from', __('From'),['class' => 'col-form-label']) }}
        {{ Form::month('from',null, array('class' => 'form-control')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('tp', __('To'),['class' => 'col-form-label']) }}
        {{ Form::month('to',null, array('class' => 'form-control')) }}
    </div>

    <div class="form-group col-md-12">
        <input class="form-check-input" name="display" type="checkbox" value="" id="display" {{$goal->display==1?'checked':''}}>
        <label class="form-check-label" for="display">
            {{__('Display On Dashboard')}}
        </label>
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