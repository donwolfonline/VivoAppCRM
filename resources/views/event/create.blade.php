<style type="text/css">
/* Estilo iOS */
.switch__container {
    margin-top: 10px;
    width: 10px;
}

.switch {
    visibility: hidden;
    position: absolute;
    margin-left: -9999px;
}

.switch+label {
    display: block;
    position: relative;
    cursor: pointer;
    outline: none;
    user-select: none;
}

.switch--shadow+label {
    padding: 2px;
    width: 100px;
    height: 40px;
    background-color: #DDDDDD;
    border-radius: 60px;
}

.switch--shadow+label:before,
.switch--shadow+label:after {
    display: block;
    position: absolute;
    top: 1px;
    left: 1px;
    bottom: 1px;
    content: "";
}

.switch--shadow+label:before {
    right: 1px;
    background-color: #F1F1F1;
    border-radius: 60px;
    transition: background 0.4s;
}

.switch--shadow+label:after {
    width: 40px;
    background-color: #fff;
    border-radius: 100%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    transition: all 0.4s;
}

.switch--shadow:checked+label:before {
    background-color: #8CE196;
}

.switch--shadow:checked+label:after {
    transform: translateX(60px);
}
</style>

{{ Form::open(array('url' => 'event')) }}
@php
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
    @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
    <div class="text-end">

        <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip" data-bs-placement="top"
            title="{{ __('Generate') }}" data-url="{{ route('generate',['event']) }}" data-title="{{ __('Generate') }}"
            float-end>
            <span class="btn btn-primary btn-sm"> <i class="fas fa-robot"> {{ __('Generate With AI') }}</span></i>
        </a>
    </div>
    @endif
    <div class="form-group col-md-6">
        {{ Form::label('name', __('Event title'),['class' => 'col-form-label']) }}
        {{ Form::text('name', '', array('class' => 'form-control','required'=>'required','placeholder'=>'Event Title')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('where', __('Where'),['class' => 'col-form-label']) }}
        {{ Form::text('where', '', array('class' => 'form-control','required'=>'required','placeholder'=>'Event Place')) }}
    </div>
    <div class="form-group col-md-6">
        {{Form::label('department',__('Department'),['class' => 'col-form-label'])}}
        {{ Form::select('department[]', $departments,null, array('class' => 'form-control multi-select department','id'=>'choices-multiple','multiple')) }}
    </div>
    <div class="form-group col-md-6">
        {{Form::label('employee',__('Employee'),['class' => 'col-form-label'])}}<br>
        <div class="emp_div">
            {{ Form::select('employee[]', [],null, array('class' => 'employee form-control multi-select','id'=>'choices-multiple1','multiple')) }}
        </div>
        <small class="text-muted">{{__('Department is require for employee selection')}}</small>
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('start_date', __('Start Date'),['class' => 'col-form-label']) }}
        {{ Form::date('start_date', new \DateTime(), array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('start_time', __('Start Time'),['class' => 'col-form-label']) }}
        {{ Form::time('start_time', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('end_date', __('End Date'),['class' => 'col-form-label']) }}
        {{ Form::date('end_date', new \DateTime(), array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('end_time', __('End Time'),['class' => 'col-form-label']) }}
        {{ Form::time('end_time', '', array('class' => 'form-control','required'=>'required')) }}
    </div>

    <div class="form-group col-md-12">
        <label class="form-control-label d-block mb-3">{{__('Status color')}}</label>
        <div class="btn-group btn-group-toggle btn-group-colors event-tag mb-0" data-toggle="buttons">
            <label class="btn bg-info active mr-2 event-color">
                <input type="radio" name="color" value="event-info" autocomplete="off" style="display: none; ">
            </label>
            <label class="btn bg-warning mr-2 event-color">
                <input type="radio" name="color" value="event-warning" autocomplete="off" style="display: none">
            </label>
            <label class="btn bg-danger mr-2 event-color">
                <input type="radio" name="color" value="event-danger" autocomplete="off" style="display: none">
            </label>
            <label class="btn bg-secondary mr-2 event-color">
                <input type="radio" name="color" value="event-secondary" autocomplete="off" style="display: none">
            </label>
            <label class="btn bg-primary mr-2 event-color">
                <input type="radio" name="color" value="event-primary" autocomplete="off" style="display: none">
            </label>
        </div>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
        {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'3','placeholder'=>'Event
        Description']) !!}
    </div>
    @if(!empty(App\Models\Utility::settings()['is_googleCal_enabled']) &&
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
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
    </div>
</div>
{{ Form::close() }}

<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script>
if ($(".multi-select").length > 0) {
    $($(".multi-select")).each(function(index, element) {
        var id = $(element).attr('id');
        console.log(id);
        var multipleCancelButton = new Choices(
            '#' + id, {
                removeItemButton: true,
            }
        );
    });
}
</script>
<script>
// Get all the radio buttons
const radioButtons = document.querySelectorAll('input[type="radio"][name="color"]');

// Function to handle radio button selection
function handleRadioButtonSelection(event) {
    // Remove the 'active' class from all labels
    const labels = document.querySelectorAll('.event-color');
    labels.forEach(label => {
        label.classList.remove('active');
    });

    // Add the 'active' class to the label of the selected radio button
    const selectedLabel = event.target.closest('.event-color');
    if (selectedLabel) {
        selectedLabel.classList.add('active');
    }
}

// Add a click event listener to each radio button
radioButtons.forEach(radioButton => {
    radioButton.addEventListener('click', handleRadioButtonSelection);
});

// Initialize the 'active' class for the initially selected radio button
const initiallySelectedRadioButton = document.querySelector('input[type="radio"][name="color"]:checked');
if (initiallySelectedRadioButton) {
    handleRadioButtonSelection({
        target: initiallySelectedRadioButton
    });
}
</script>