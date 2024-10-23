@if ($project_id == 0)
    {{ Form::open(['route' => ['project.task.store', 0]]) }}
@else
    {{ Form::open(['route' => ['project.task.store', $project_id]]) }}
@endif
@php
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
    @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
        <div class="text-end">
            <a href="#" data-size="lg" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['project task']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Generate') }}" data-title="{{ __('Generate') }}" float-end>
                <span class="btn btn-primary btn-sm"> <i class="fas fa-robot"> {{ __('Generate With AI') }}</span></i>
            </a>
        </div>
    @endif

    @if ($project_id == 0)
        <div class="form-group col-md-12">
            {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Task Title')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('project', __('Project'), ['class' => 'col-form-label']) }}
            {{ Form::select('project', $projects, '', ['class' => 'form-control multi-select', 'required' => 'required']) }}
        </div>
    @else
        <div class="form-group col-md-6">
            {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Task Title')]) }}
        </div>
    @endif
    <div class="form-group col-md-6">
        {{ Form::label('priority', __('Priority'), ['class' => 'col-form-label']) }}
        {{ Form::select('priority', $priority, '', ['class' => 'form-control multi-select', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
        {{ Form::date('start_date', new \DateTime(), ['class' => 'form-control']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('due_date', __('Due Date'), ['class' => 'col-form-label']) }}
        {{ Form::date('due_date', new \DateTime(), ['class' => 'form-control']) }}
    </div>
    @if ($project_id == 0)
        <div class="form-group col-md-6">
            {{ Form::label('assign_to', __('Assign To'), ['class' => 'col-form-label']) }}
            <select class="form-control" name="assign_to" id="assign_to" data-toggle="select" required>

            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('milestone_id', __('Milestone'), ['class' => 'col-form-label']) }}
            <select class="form-control" name="milestone_id" id="milestone_id" data-toggle="select">

            </select>
        </div>
    @else
        <div class="form-group col-md-6">
            {{ Form::label('assign_to', __('Assign To'), ['class' => 'col-form-label']) }}
            {!! Form::select('assign_to', $users, null, ['class' => 'form-control multi-select', 'required' => 'required']) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('milestone_id', __('Milestone'), ['class' => 'col-form-label']) }}
            {!! Form::select('milestone_id', $milestones, null, ['class' => 'form-control multi-select']) !!}
        </div>
    @endif
    <div class="form-group  col-md-6">
        {{ Form::label('hours', __('Estimation Hours'), ['class' => 'col-form-label']) }}
        {{ Form::number('hours', null, ['class' => 'form-control', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
        {{ Form::textarea('description', '', ['class' => 'form-control', 'rows' => '3']) }}
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
