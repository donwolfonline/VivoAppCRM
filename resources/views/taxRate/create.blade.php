{{ Form::open(array('url' => 'taxRate')) }}
<div class="form-group">
    {{ Form::label('name', __('Name') ,['class' => 'col-form-label']) }}
    {{ Form::text('name', '', array('class' => 'form-control','required'=>'required')) }}
</div>
<div class="form-group">
    {{ Form::label('rate', __('Rate') ,['class' => 'col-form-label']) }}
    {{ Form::number('rate', '', array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
</div>
{{ Form::close() }}
