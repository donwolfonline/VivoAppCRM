{{Form::open(array('url'=>'goaltracking','method'=>'post'))}}
@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">

    <a href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate',['goal tracking']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
 </div>
 @endif
    <div class="col-md-6">
        <div class="form-group">
            {{Form::label('branch',__('Branch'),['class' => 'col-form-label'])}}
            {{Form::select('branch',$brances,null,array('class'=>'form-control select2','required'=>'required'))}}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {{Form::label('goal_type',__('Goal Type'),['class' => 'col-form-label'])}}
            {{Form::select('goal_type',$goalTypes,null,array('class'=>'form-control select2','required'=>'required'))}}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {{Form::label('start_date',__('Start Date'),['class' => 'col-form-label'])}}
            {{Form::date('start_date',new \DateTime(),array('class' => 'form-control'))}}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {{Form::label('end_date',__('End Date'),['class' => 'col-form-label'])}}
            {{Form::date('end_date',new \DateTime(),array('class' => 'form-control'))}}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {{Form::label('subject',__('Subject'),['class' => 'col-form-label'])}}
            {{Form::text('subject',null,array('class'=>'form-control'))}}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {{Form::label('target_achievement',__('Target Achievement'),['class' => 'col-form-label'])}}
            {{Form::text('target_achievement',null,array('class'=>'form-control'))}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            {{Form::label('description',__('Description'),['class' => 'col-form-label'])}}
            {{Form::textarea('description',null,array('class'=>'form-control'))}}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            {{Form::label('status',__('Status'),['class' => 'col-form-label'])}}
            {{Form::select('status',$status,null,array('class'=>'form-control select2'))}}
        </div>
    </div>

    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
    </div>
</div>
{{Form::close()}}
