@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">  <a href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate',['lead stage']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
 </div>
 @endif
 
    {{ Form::open(array('url' => 'leadStage')) }}
<div class="form-group">
    {{ Form::label('name', __('Name') ,['class' => 'col-form-label']) }}
    {{ Form::text('name', '', array('class' => 'form-control','required'=>'required')) }}
</div>
<div class="form-group">
    {{ Form::label('name', __('Pipeline') ,['class' => 'col-form-label']) }}
    {{ Form::select('pipeline_id', $pipelines,null, array('class' => 'form-control','data-toggle="select"','required'=>'required')) }}
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
</div>

{{ Form::close() }}
</div>