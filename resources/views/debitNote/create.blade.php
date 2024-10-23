{{ Form::open(array('url' => 'product')) }}
<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('bill', __('Bill')) }}
        <select class="form-control font-style" required="required" id="owner" name="owner">
            <option value="7">BIL0001</option>
            <option value="8">BIL0002</option>
        </select>
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('amount', __('Amount')) }}
        {{ Form::number('amount', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('date', __('Date')) }}
        {{ Form::text('date', '', array('class' => 'form-control datepicker','required'=>'required')) }}
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description')) }}
        {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2']) !!}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Cancel')}}</button>
    {{Form::submit(__('Create'),array('class'=>'btn btn-primary'))}}
</div>

{{ Form::close() }}
