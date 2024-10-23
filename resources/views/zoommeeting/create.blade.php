<style type="text/css">
    /* Estilo iOS */
    .switch__container {
        margin-top: 10px;
        width: 120px;
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

{{ Form::open(['route' => 'zoommeeting.store', 'id' => 'store-user', 'method' => 'post']) }}
@php
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">
       <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
    data-url="{{ route('generate',['zoom meeting']) }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
 </div>
 @endif
    <div class="form-group col-md-12">
        {{ Form::label('title', __('Topic')) }}
        {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Meeting Title'), 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('projects', __('Projects')) }}
        {{ Form::select('project_id', $project, null, ['class' => 'form-control project_select project_id', 'placeholder' => __('Select Project')]) }}
    </div>

    <div class="form-group col-md-6">
        {{ Form::label('employee', __('Employee'), ['class' => '']) }}
        {!! Form::select('employee', [], null, ['class' => 'form-control  employee_select', 'required' => 'required']) !!}
        <!-- {{ Form::select('employee[]', [], null, ['class' => 'form-control multi-select employee_select', 'id' => 'choices-multiple', 'multiple' => '', 'required' => 'required']) }} -->
    </div>

    <!-- <div class="form-group col-md-6">
        {{ Form::label('employee', __('Employee')) }}
        {{ Form::select('employee[]', [], null, ['class' => 'form-control multi-select employee_select', 'id' => 'choices-multiple', 'multiple' => '', 'required' => 'required']) }}
    </div> -->

    <div class="form-group col-6">
        {{ Form::label('datetime', __('Start Date / Time')) }}
        {{ Form::datetimeLocal('start_date', new \DateTime(), ['class' => 'form-control date', 'placeholder' => __('Select Date/Time'), 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('duration', __('Duration')) }}
        {{ Form::number('duration', null, ['class' => 'form-control', 'placeholder' => __('Enter Duration'), 'required' => 'required']) }}
    </div>

    <div class="form-group col-md-6">
        {{ Form::label('password', __('Password (Optional)')) }}
        {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Password')]) }}
    </div>
    <div class="form-group col-md-12">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="client_id" id="client_id" class="form-check-input custom-checkbox taskCheck">
            <label for="display">{{ __('Invite Client For Zoom Meeting') }}</label>
        </div>
    </div>
    @if(!empty(App\Models\Utility::settings()['is_googleCal_enabled']) && App\Models\Utility::settings()['is_googleCal_enabled'] == 'on')
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
    <button class="btn btn-primary " type="submit" style="margin-bottom: 10px;"
        id="create-client">{{ __('Create') }}<span class="spinner" style="display: none;"><i
                class="fa fa-spinner fa-spin"></i></span>
    </button>
</div>
{{ Form::close() }} 
<script type="text/javascript">

    function ddatetime_range() {
        $('.date').daterangepicker({
            "singleDatePicker": true,
            "timePicker": true,
            "autoApply": false,
            "locale": {
                "format": 'YYYY-MM-DD H:mm'
            },
            "timePicker24Hour": true,
        }, function(start, end, label) {
            $('.start_date').val(start.format('YYYY-MM-DD H:mm'));
        });
    }

    $(document).on('change', '.project_select', function() {
        var project_id = $(this).val();
        getparent(project_id);
    });

    function getparent(bid) {
        $.ajax({
            url: `{{ url('zoom/project/select') }}/${bid}`,
            type: 'GET',
            success: function(data) {
                $('.employee_select').empty();
                $.each(data, function(i, item) {
                    $('.employee_select').append('<option value="' + item.id + '">' + item.name +
                        '</option>');
                });
                if (data == '') {
                    $('.employee_select').empty();
                }
            }
        });
    }
</script>

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
