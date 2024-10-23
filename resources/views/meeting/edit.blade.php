{{ Form::model($meeting, array('route' => array('meeting.update', $meeting->id), 'method' => 'PUT')) }}
<div class="row">
    @php 
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
   @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
     <div class="text-end">
        <a href="#" data-size="md" class="btn btn-sm btn-primary" data-ajax-popup-over="true" data-size="md"
            data-title="{{ __('Generate Content Width Ai') }}" data-url="{{ route('generate', ['meeting']) }}"
            data-toggle="tooltip" title="{{ __('Generate') }}">
            <i class="fas fa-robot"> {{ __('Generate With AI') }}</i>
        </a>
    </div>
    @endif 
    <div class="form-group col-md-6">
        {{ Form::label('department', __('Department'),['class' => 'col-form-label']) }}
        {{ Form::select('department', $departments,null, array('class' => 'form-control multi-select')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('designation', __('Designation'),['class' => 'col-form-label']) }}
        {{ Form::select('designation', $designations,null, array('class' => 'form-control multi-select')) }}
    </div>

    <div class="form-group col-md-12">
        {{Form::label('title',__('Title'),['class' => 'col-form-label'])}}
        {{Form::text('title',null,array('class'=>'form-control'))}}
    </div>
    <div class="form-group col-md-6">
        {{Form::label('date',__('Date'),['class' => 'col-form-label'])}}
        {{Form::date('date',null,array('class'=>'form-control'))}}
    </div>
    <div class="form-group col-md-6">
        {{Form::label('time',__('Time'),['class' => 'col-form-label'])}}
        {{Form::time('time',null,array('class'=>'form-control'))}}
    </div>
    <div class="form-group col-md-12">
        <div class="row p-2">
            <div class="col-6">
                {{Form::label('notes',__('Notes'),['class' => 'col-form-label'])}}
            </div>    
            @if (App\Models\Utility::is_chatgpt_enable())
            <div class="col-6 text-end">
                <a data-size="md" class="btn btn-primary btn-icon btn-sm text-white " data-ajax-popup-over="true" id="grammarCheck" data-url="{{ route('grammar',['meeting_edit']) }}"
                    data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                    <i class="ti ti-rotate"></i> <span>{{__('Grammar check with AI')}}</span></a>
            </div>
            @endif
        </div>
        {{Form::textarea('notes',null,array('class'=>'form-control meeting_edit','rows'=>'2'))}}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
</div>

{{ Form::close() }}
@push('pre-purpose-script-page')

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

@endpush