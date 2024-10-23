{{ Form::model($timesheet, array('route' => array('project.timesheet.update', [$project->id,$timesheet->id]), 'method' => 'post')) }}
@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">
      <a href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate',['time sheet']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
 </div>
 @endif
 
    <div class="form-group  col-md-12">
        {{ Form::label('employee', __('User'),['class' => 'col-form-label']) }}
        <select class="form-control" required="required" name="employee" data-toggle="select">
            @foreach($users as $user)
                @if(!empty($user->projectUsers))
                    <option value="{{$user->projectUsers->id}}" {{($timesheet->employee==$user->projectUsers->id)?'selected':''}}>{{$user->projectUsers->name}}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('start_date', __('Start Date'),['class' => 'col-form-label']) }}
        {{ Form::date('start_date', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('start_time', __('Start Time'),['class' => 'col-form-label']) }}
        {{ Form::time('start_time', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('end_date', __('End Date'),['class' => 'col-form-label']) }}
        {{ Form::date('end_date', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('end_time', __('End Time'),['class' => 'col-form-label']) }}
        {{ Form::time('end_time', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group  col-md-12">
        {{ Form::label('task_id', __('Task'),['class' => 'col-form-label']) }}
        <select class="form-control"  name="task_id" data-toggle="select">
            <option value="0">{{__('-')}}</option>
            @foreach($tasks as $task)
                @if(!empty($task))
                    <option value="{{$task->id}}" {{($timesheet->task_id==$task->id)?'selected':''}}>{{$task->title}}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>


<div class="row">
    <div class="form-group  col-md-12">
        <div class="row p-2">
            <div class="col-6">
                {{ Form::label('notes', __('Notes'),['class' => 'col-form-label']) }}
            </div>    
            @if (App\Models\Utility::is_chatgpt_enable())
            <div class="col-6 text-end">
                <a data-size="md" class="btn btn-primary btn-icon btn-sm text-white " data-ajax-popup-over="true" id="grammarCheck" data-url="{{ route('grammar',['timesheet_edit']) }}"
                    data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                    <i class="ti ti-rotate"></i> <span>{{__('Grammar check with AI')}}</span></a>
            </div>
            @endif
        </div>
        {!! Form::textarea('notes', null, ['class'=>'form-control timesheet_edit','rows'=>'2']) !!}
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
</div>
{{ Form::close() }}


