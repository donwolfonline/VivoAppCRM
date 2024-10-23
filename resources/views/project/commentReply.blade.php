{{ Form::open(array('route' => array('project.comment.store',$project_id,$comment_id),'enctype'=>"multipart/form-data")) }}
@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
<div class="col text-end">
    <a data-size="md" class="btn btn-primary btn-icon btn-sm text-white " data-ajax-popup-over="true" id="grammarCheck" data-url="{{ route('grammar',['comment_reply']) }}"
        data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
        <i class="ti ti-rotate"></i> <span>{{__('Grammar check with AI')}}</span></a>
</div>
 <div class="col-auto text-end">
      <a href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate',['commentreply']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
 </div>
 @endif
 
    <input type="hidden" name="parent" value="{{$comment_id}}">
    <div class="form-group  col-md-12">
        {{ Form::label('comment', __('Comment'),['class' => 'col-form-label']) }}
        {!! Form::textarea('comment', null, ['class'=>'form-control comment_reply','required','rows'=>'3']) !!}
    </div>
    <div class="form-group  col-md-12">
        {{ Form::label('file', __('File'),['class' => 'col-form-label']) }}
        {{ Form::file('file', array('class' => 'form-control')) }}
    </div>
</div>

<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Post'),array('class'=>'btn  btn-primary'))}}
</div>
{{ Form::close() }}


