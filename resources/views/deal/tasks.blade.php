@if(isset($task))
    {{ Form::model($task, array('route' => array('deal.tasks.update', $deal->id, $task->id), 'method' => 'post')) }}
@else
    {{ Form::open(array('route' => ['deal.tasks.store',$deal->id])) }}
@endif
@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">

<a href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate',['deal']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate') }}" float-end>
    <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
</a>
 </div>
 @endif
<div class="form-group">
    {{ Form::label('name', __('Name'),['class' => 'col-form-label']) }}
    {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder'=>'Task Name')) }}
</div>
<div class="row">
    <div class="col-6">
        <div class="form-group">
            {{ Form::label('date', __('Date'),['class' => 'col-form-label']) }}
            {{ Form::date('date', new \DateTime(), array('class' => 'form-control','required'=>'required')) }}
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {{ Form::label('time', __('Time'),['class' => 'col-form-label']) }}
            {{ Form::time('time', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
    </div>
</div>
<div class="form-group">
    {{ Form::label('priority', __('Priority'),['class' => 'col-form-label']) }}
    <select class="form-control multi-select" name="priority" required data-toggle="select">
        @foreach($priorities as $key => $priority)
            <option value="{{$key}}" @if(isset($task) && $task->priority == $key) selected @endif>{{__($priority)}}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    {{ Form::label('status', __('Status'),['class' => 'col-form-label']) }}
    <select class="form-control multi-select" name="status" data-toggle="select" required>
        @foreach($status as $key => $st)
            <option value="{{$key}}" @if(isset($task) && $task->status == $key) selected @endif>{{__($st)}}</option>
        @endforeach
    </select>
</div>

@if(isset($task))
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
</div>
@else
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Add'),array('class'=>'btn  btn-primary'))}}
</div>
@endif
{{ Form::close() }}

<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script>
    if ($(".multi-select").length > 0) {
              $( $(".multi-select") ).each(function( index,element ) {
                  var id = $(element).attr('id');
                     var multipleCancelButton = new Choices(
                          '#'+id, {
                              removeItemButton: true,
                          }
                      );
              });
         }
  </script>