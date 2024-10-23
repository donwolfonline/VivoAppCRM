{{ Form::model($leave, array('route' => array('leave.update', $leave->id), 'method' => 'PUT')) }}
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
    @if(\Auth::user()->type=='company')
        <div class="form-group col-md-12">
            {{ Form::label('employee_id', __('Employee'),['class' => 'col-form-label']) }}
            {{ Form::select('employee_id', $employees,null, array('class' => 'form-control multi-select','required'=>'required')) }}
        </div>
    @endif
    <div class="form-group col-md-12">
        {{Form::label('leave_type',__('Leave Type'),['class' => 'col-form-label'])}}
        <select name="leave_type" id="leave_type" class="form-control" data-toggle="select" required>
            @foreach($leaveTypes as $type)
                <option value="{{ $type->id }}" {{ $leave->leave_type == $type->id ? 'selected' : '' }}>{{ $type->title }} (<p class="float-right pr-5">{{ $type->days }}</p>)</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        {{Form::label('start_date',__('Start Date'),['class' => 'col-form-label'])}}
        {{Form::date('start_date',null,array('class'=>'form-control','required'=>'required'))}}
    </div>
    <div class="form-group col-md-6">
        {{Form::label('end_date',__('End Date'),['class' => 'col-form-label'])}}
        {{Form::date('end_date',null,array('class'=>'form-control','required'=>'required'))}}
    </div>
    <div class="form-group col-md-12">
        {{Form::label('leave_reason',__('Leave Reason'),['class' => 'col-form-label'])}}
        {{Form::textarea('leave_reason',null,array('class'=>'form-control','rows'=>'3','required'=>'required'))}}
    </div>
    <div class="form-group col-md-12">
        {{Form::label('remark',__('Remark'),['class' => 'col-form-label'])}}
        {{Form::textarea('remark',null,array('class'=>'form-control','rows'=>'3'))}}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
</div>

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