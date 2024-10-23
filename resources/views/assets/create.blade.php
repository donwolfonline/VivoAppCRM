{{ Form::open(['url' => 'account-assets']) }}
<div class="row">
    @php
        $plansettings = App\Models\Utility::plansettings();
    @endphp
    <div class="row">
        @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
            <div class="text-end">
                <a href="#" data-size="md" class="btn btn-sm btn-primary" data-ajax-popup-over="true" data-size="md"
                    data-title="{{ __('Generate Content Width Ai') }}"
                    data-url="{{ route('generate', ['account asset']) }}" data-toggle="tooltip"
                    title="{{ __('Generate') }}">
                    <i class="fas fa-robot"> {{ __('Generate With AI') }}</i>
                </a>
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}
            {{ Form::number('amount', '', ['class' => 'form-control', 'required' => 'required', 'step' => '0.01']) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('purchase_date', __('Purchase Date'), ['class' => 'col-form-label']) }}
            {{ Form::date('purchase_date', new \DateTime(), ['class' => 'form-control']) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('supported_date', __('Support Until'), ['class' => 'col-form-label']) }}
            {{ Form::date('supported_date', new \DateTime(), ['class' => 'form-control']) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', '', ['class' => 'form-control', 'rows' => '3']) }}
        </div>
        <div class="modal-footer">
            <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
            {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
        </div>
    </div>
    {{ Form::close() }}
