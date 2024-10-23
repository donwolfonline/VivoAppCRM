<style type="text/css">
    /* Estilo iOS */
    .switch__container {
        margin-top: 10px;
        width: 120px;
    }

    .switch {
        visibility: hidden;
        position: absolute;
        margin-left: -9999px;
    }

    .switch+label {
        display: block;
        position: relative;
        cursor: pointer;
        outline: none;
        user-select: none;
    }

    .switch--shadow+label {
        padding: 2px;
        width: 100px;
        height: 40px;
        background-color: #DDDDDD;
        border-radius: 60px;
    }

    .switch--shadow+label:before,
    .switch--shadow+label:after {
        display: block;
        position: absolute;
        top: 1px;
        left: 1px;
        bottom: 1px;
        content: "";
    }

    .switch--shadow+label:before {
        right: 1px;
        background-color: #F1F1F1;
        border-radius: 60px;
        transition: background 0.4s;
    }

    .switch--shadow+label:after {
        width: 40px;
        background-color: #fff;
        border-radius: 100%;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        transition: all 0.4s;
    }

    .switch--shadow:checked+label:before {
        background-color: #8CE196;
    }

    .switch--shadow:checked+label:after {
        transform: translateX(60px);
    }
</style>
@php
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
    @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
        <div class="text-end">
            <a href="#" data-size="md" class="btn btn-sm btn-primary" data-ajax-popup-over="true" data-size="md"
                data-title="{{ __('Generate Content Width Ai') }}" data-url="{{ route('generate', ['holiday']) }}"
                data-toggle="tooltip" title="{{ __('Generate') }}">
                <i class="fas fa-robot"> {{ __('Generate With AI') }}</i>
            </a>
        </div>
    @endif

    {{ Form::open(['url' => 'holiday', 'method' => 'post']) }}
    <div class="form-group">
        {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}
        {{-- {{ Form::date('deadline', \Carbon\Carbon::now()->format('Y-m-d'),['class' => 'form-control']) }} --}}
        {{ Form::date('date', new \DateTime(), ['class' => 'form-control']) }}

    </div>
    <div class="form-group">
        {{ Form::label('occasion', __('Occasion'), ['class' => 'col-form-label']) }}
        {{ Form::text('occasion', null, ['class' => 'form-control']) }}
    </div>
    @if (
        !empty(App\Models\Utility::settings()['is_googleCal_enabled']) &&
            App\Models\Utility::settings()['is_googleCal_enabled'] == 'on')
        <div class="form-group col-md-6">
            {{ Form::label('synchronize_type', __('Synchroniz in Google Calendar ?'), ['class' => 'form-label']) }}
            <div class=" form-switch">
                <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow"
                    value="google_calender">
                <label class="form-check-label" for="switch-shadow"></label>
            </div>
        </div>
    @endif
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
    </div>
    {{ Form::close() }}

</div>
