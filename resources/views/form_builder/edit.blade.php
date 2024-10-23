{{ Form::model($formBuilder, ['route' => ['form_builder.update', $formBuilder->id], 'method' => 'PUT']) }}
@php
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
    @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
        <div class="text-end">
            <a href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate', ['form_builder']) }}"
                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
                data-title="{{ __('Generate') }}" float-end>
                <span class="btn btn-primary btn-sm"> <i class="fas fa-robot"> {{ __('Generate With AI') }}</span></i>
            </a>
        </div>
    @endif

    <div class="col-12 form-group">
        {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
    </div>
    <div class="col-12 form-group">
        {{ Form::label('active', __('Active'), ['class' => 'col-form-label ']) }}
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="is_active" value="1" id="on"
                {{ $formBuilder->is_active == 1 ? 'checked' : '' }}>
            <label class="form-check-label" for="on">
                {{ __('On') }}
            </label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="is_active" value="0"
                {{ $formBuilder->is_active == 0 ? 'checked' : '' }} id="off">
            <label class="form-check-label" for="off">
                {{ __('Off') }}
            </label>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{ Form::submit(__('Update'), ['class' => 'btn  btn-primary']) }}
    </div>
</div>
{{ Form::close() }}
