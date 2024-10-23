{{ Form::open(array('url' => 'meeting')) }}
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
    @endif    <div class="form-group col-md-6">
        {{ Form::label('department', __('Department'),['class' => 'col-form-label']) }}
        {{ Form::select('department', $departments,'', array('class' => 'form-control multi-select')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('designation', __('Designation'),['class' => 'col-form-label']) }}
        {{ Form::select('designation', $designations ,'', array('class' => 'form-control multi-select')) }}
    </div>

    <div class="form-group col-md-12">
        {{Form::label('title',__('Title'),['class' => 'col-form-label'])}}
        {{Form::text('title',null,array('class'=>'form-control'))}}
    </div>
    <div class="form-group col-md-6">
        {{Form::label('date',__('Date'),['class' => 'col-form-label'])}}
        {{Form::date('date', new \DateTime(),array('class'=>'form-control'))}}
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
                <a data-size="md" class="btn btn-primary btn-icon btn-sm text-white " data-ajax-popup-over="true" id="grammarCheck" data-url="{{ route('grammar',['meeting_create']) }}"
                    data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                    <i class="ti ti-rotate"></i> <span>{{__('Grammar check with AI')}}</span></a>
            </div>
            @endif
        </div>
        {{Form::textarea('notes',null,array('class'=>'form-control meeting_create','rows'=>'2'))}}
    </div>
    @if (
        !empty(App\Models\Utility::settings()['is_googleCal_enabled']) &&
            App\Models\Utility::settings()['is_googleCal_enabled'] == 'on')
        <div class="form-group col-md-12">
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
    {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
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