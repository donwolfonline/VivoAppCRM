{{ Form::open(array('url' => 'department')) }}
 
<div class="col-12">
    <div class="form-group">
        {{ Form::label('name', __('Name') ,['class' => 'col-form-label']) }}
        {{ Form::text('name', '', array('class' => 'form-control','required'=>'required','PlaceHolder'=>__('Department'))) }}
    </div>
</div>
<div class="col-12">
    <div class="form-group">
        {{Form::label('branch_id',__('Branch'),['class'=>'form-label'])}}
        {{Form::select('branch_id',$branch,null,array('class'=>'form-control select','placeholder'=>__('Select Branch')))}}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
</div>
</div>

{{ Form::close() }}
