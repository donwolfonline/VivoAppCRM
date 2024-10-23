{{-- @php
$plansettings = App\Models\Utility::plansettings();
@endphp --}}
<div class="row">
    {{-- @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
    <div class="text-end">
       <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
    data-url="{{ route('generate',['lead']) }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
    </div>
    @endif --}}

    {{ Form::model($lead, ['route' => ['lead.discussion.store', $lead->id], 'method' => 'POST']) }}
    <div class="form-label">
        {{ Form::label('comment', __('Message'), ['class' => 'col-form-label']) }}
        {{ Form::textarea('comment', null, ['class' => 'form-control', 'rows' => 3]) }}
    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{ Form::submit(__('Add'), ['class' => 'btn  btn-primary']) }}
    </div>
    {{ Form::close() }}
</div>
