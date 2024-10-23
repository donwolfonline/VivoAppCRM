{{ Form::open(['url' => 'coupon', 'method' => 'post']) }}
@php
    $settings = App\Models\Utility::settings();
@endphp
<div class="row">
    @if (!empty($settings['chatgpt_key']))
        <div class="text-end">
            <a href="#" data-size="md" class="btn btn-sm btn-primary" data-ajax-popup-over="true" data-size="md"
                data-title="{{ __('Generate') }}" data-url="{{ route('generate', ['coupon']) }}" data-toggle="tooltip"
                title="{{ __('Generate') }}">
                <i class="fas fa-robot"> {{ __('Generate With AI') }}</i>
            </a>
        </div>
    @endif
    <div class="form-group col-md-12">
        {{ Form::label('name', __('Name')) }}
        {{ Form::text('name', null, ['class' => 'form-control font-style', 'required' => 'required']) }}
    </div>

    <div class="form-group col-md-6">
        {{ Form::label('discount', __('Discount')) }}
        {{ Form::number('discount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01']) }}
        <span class="small">{{ __('Note: Discount in Percentage') }}</span>
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('limit', __('Limit')) }}
        {{ Form::number('limit', null, ['class' => 'form-control', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('code', __('Code'), ['class' => 'col-form-label']) }}
        <div class="d-flex radio-check">
            <div class="form-check m-1">
                <input type="radio" id="manual_code" value="manual" name="icon-input" class="form-check-input code"
                    checked="checked">
                <label class="form-check-label" for="manual_code">{{ __('Manual') }}</label>
            </div>
            <div class="form-check m-1">
                <input type="radio" id="auto_code" value="auto" name="icon-input" class="form-check-input code">
                <label class="form-check-label" for="auto_code">{{ __('Auto Generate') }}</label>
            </div>
        </div>
    </div>

    <div class="form-group col-md-12 d-block" id="manual">
        <input class="form-control font-uppercase" name="manualCode" type="text" placeholder="Enter Code">
    </div>
    <div class="form-group col-md-12 d-none" id="auto">
        <div class="row">
            <div class="input-group">
                {{ Form::text('autoCode', null, ['class' => 'form-control', 'id' => 'auto-code', 'placeholder' => 'Generate Code']) }}
                <button class="btn btn-outline-secondary" type="button" id="code-generate"><i
                        class="fa fa-history pr-1"></i>{{ __(' Generate') }}</button>
            </div>
        </div>
    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
    </div>
</div>
{{ Form::close() }}
