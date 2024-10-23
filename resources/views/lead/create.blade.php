{{ Form::open(['url' => 'lead']) }}
@php
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
    @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
        <div class="text-end">
            <a href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate', ['lead']) }}"
                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
                data-title="{{ __('Generate') }}" float-end>
                <span class="btn btn-primary btn-sm">
                    <i class="fas fa-robot"> {{ __('Generate With AI') }} </i>
                </span>
            </a>
        </div>
    @endif

    <div class="form-group col-md-6">
        {{ Form::label('subject', __('Subject'), ['class' => 'col-form-label']) }}
        {{ Form::text('subject', null, ['class' => 'form-control', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('user_id', __('Employee'), ['class' => 'col-form-label']) }}
        {{ Form::select('user_id', $employees, '', ['class' => 'form-control multi-select', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('email', __('Email'), ['class' => 'col-form-label']) }}
        {{ Form::text('email', null, ['class' => 'form-control', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('phone_no', __('Phone No'), ['class' => 'col-form-label']) }}
        {{ Form::text('phone_no', null, ['class' => 'form-control', 'required' => 'required']) }}
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
</div>

{{ Form::close() }}


<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>

<script>
    if ($(".multi-select").length > 0) {
        $($(".multi-select")).each(function(index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#' + id, {
                    removeItemButton: true,
                }
            );
        });
    }
</script>
