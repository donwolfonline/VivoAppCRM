{{ Form::open(['url' => 'user', 'method' => 'post']) }}
<div class="form-group">
    {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }}
    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter User Name'), 'required' => 'required']) }}
</div>
<div class="form-group">
    {{ Form::label('email', __('Email'), ['class' => 'col-form-label']) }}
    {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter User Email'), 'required' => 'required']) }}
</div>
<div class="col-md-5 mb-3 form-group mt-4">
    <label for="password_switch">{{ __('Login is enable') }}</label>
    <div class="form-check form-switch custom-switch-v1 float-end">
        <input type="checkbox" name="password_switch" class="form-check-input input-primary pointer" value="on"
            id="password_switch">
        <label class="form-check-label" for="password_switch"></label>
    </div>
</div>
<div class="form-group col-md-12 ps_div d-none">
    <div class="form-group">
        {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
        {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Company Password'), 'minlength' => '6']) }}
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}
