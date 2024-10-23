{{ Form::open(array('route' => array('project.client.feedback.store',$project_id,$comment_id),'enctype'=>"multipart/form-data")) }}
@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">
       <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" 
    data-url="{{ route('generate',['clientfeedback']) }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
 </div>
 @endif
 
    <input type="hidden" name="parent" value="{{$comment_id}}">
    <div class="form-group  col-md-12">
        {{ Form::label('feedback', __('Feedback'),['class' => 'col-form-label']) }}
        {!! Form::textarea('feedback', null, ['class'=>'form-control','rows'=>'3']) !!}
    </div>
    <div class="form-group  col-md-12">
        {{ Form::label('file', __('File'),['class' => 'col-form-label']) }}
        {{ Form::file('file', null, array('class' => 'form-control')) }}
    </div>
</div>

<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Post'),array('class'=>'btn  btn-primary'))}}
</div>
{{ Form::close() }}


