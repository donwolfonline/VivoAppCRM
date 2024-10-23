{{ Form::open(['url' => 'leave']) }}
<div class="row">
    @php
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
   @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
     <div class="text-end">
        <a href="#" data-size="md" class="btn btn-sm btn-primary" data-ajax-popup-over="true" data-size="md"
            data-title="{{ __('Generate Content Width Ai') }}" data-url="{{ route('generate', ['leave']) }}"
            data-toggle="tooltip" title="{{ __('Generate') }}">
            <i class="fas fa-robot"> {{ __('Generate With AI') }}</i>
        </a>
    </div>
    @endif
        @if (\Auth::user()->type == 'company')
        <div class="form-group col-md-12">
            {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
            <div class="form-icon-user">
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control multi-select', 'id' => 'employee_id']) }}
            </div>
        </div>
    @endif
    <div class="form-group col-md-12">
        {{ Form::label('leave_type', __('Leave Type'), ['class' => 'col-form-label']) }}
        <select name="leave_type" id="leave_type" class="form-control" data-toggle="select" required>
            @foreach ($leaveTypes as $leave)
                <option value="{{ $leave->id }}">{{ $leave->title }} (<p class="float-right pr-5">{{ $leave->days }}
                    </p>)</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
        {{-- {{ Form::date('start_date', \Carbon\Carbon::now()->format('Y-m-d'),['class' => 'form-control']) }} --}}
        {{ Form::date('start_date', new \DateTime(), ['class' => 'form-control', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
        {{-- {{ Form::date('end_date', \Carbon\Carbon::now()->format('Y-m-d'),['class' => 'form-control']) }} --}}
        {{ Form::date('end_date', new \DateTime(), ['class' => 'form-control', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'col-form-label']) }}
        {{ Form::textarea('leave_reason', null, ['class' => 'form-control', 'rows' => '3', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('remark', __('Remark'), ['class' => 'col-form-label']) }}
        {{ Form::textarea('remark', null, ['class' => 'form-control', 'rows' => '3', 'required' => 'required']) }}
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
</div>
<div class="modal-footer">
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
