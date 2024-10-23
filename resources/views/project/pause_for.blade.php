{{ Form::open(array('data-url' => route('project.task.timer.pause.store',$task->id),'id'=>'pauseTaskForm')) }}
<div class="row">
    <div class="form-group  col-md-12">
        {{ Form::label('pause_for', __('Pause For'),['class' => 'col-form-label']) }}
        {{ Form::text('pause_for',null, array('class' => 'form-control','id'=>'pause_for','required'=>'required')) }}
    </div>
</div>
<div class="row">
    <div class="col-md-12 text-end">
        <button type="button" class="btn btn-primary" data-id="{{$task->id}}" id="pause_now">{{ __('Pause Now')}}</button>
    </div>
</div>
{{ Form::close() }}


