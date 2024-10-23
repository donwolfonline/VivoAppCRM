{{-- @dd($plan); --}}
{{Form::model($plan, array('route' => array('plan.update', $plan->id), 'method' => 'PUT', 'enctype' => "multipart/form-data")) }}
@php
$settings = App\Models\Utility::settings();
@endphp
<div class="row">
    @if (!empty($settings['chatgpt_key']) )
    <div class="text-end">
        <a href="#" data-size="md" class="btn btn-sm btn-primary" data-ajax-popup-over="true" data-size="md"
            data-title="{{ __('Generate') }}" data-url="{{ route('generate', ['plan']) }}" data-toggle="tooltip"
            title="{{ __('Generate') }}">
            <i class="fas fa-robot"> {{ __('Generate With AI') }}</i>
        </a>
    </div>
    @endif
    <div class="form-group col-md-6">
        {{Form::label('name',__('Name'),['class' => 'col-form-label'])}}
        {{Form::text('name',null,array('class'=>'form-control font-style','placeholder'=>__('Enter Plan Name'),'required'=>'required'))}}
    </div>
    @if($plan->price>0)
    <div class="form-group col-md-6">
        {{Form::label('price',__('Price'),['class' => 'col-form-label'])}}
        {{Form::number('price',null,array('class'=>'form-control','placeholder'=>__('Enter Plan Price'),'step'=>'0.01'))}}
    </div>
    @endif
    <div class="form-group col-md-6">
        {{Form::label('max_employee',__('Maximum Employee'),['class' => 'col-form-label'])}}
        {{Form::number('max_employee',null,array('class'=>'form-control','required'=>'required'))}}
        <span class="small">{{__('Note: "-1" for Unlimited')}}</span>
    </div>
    <div class="form-group col-md-6">
        {{Form::label('max_client',__('Maximum Client'),['class' => 'col-form-label'])}}
        {{Form::number('max_client',null,array('class'=>'form-control','required'=>'required'))}}
        <span class="small">{{__('Note: "-1" for Unlimited')}}</span>
    </div>
    @if ($plan->name != "Free Plan")
    <div class="form-group col-md-6">
        {{ Form::label('duration', __('Duration'),['class' => 'col-form-label']) }}
        {!! Form::select('duration', $arrDuration, null,array('class' =>
        'form-control','data-toggle'=>'select','required'=>'required')) !!}
    </div>
    @endif


    <!-- <div class="form-group col-md-6">
        {{Form::label('storage_limit',__('Storage Limit'),['class' => 'col-form-label'])}}
        {{Form::number('storage_limit',null,array('class'=>'form-control','required'=>'required','placeholder'=> 'Storage Limit'))}}
    </div> -->

    <div class="form-group col-md-6">
        <label for="storage_limit" class="form-label">Storage limit</label>
        <div class="input-group">
            <input class="form-control" required="required" name="storage_limit" type="number" value="{{$plan->storage_limit}}"
                id="storage_limit">
            <div class="input-group-append">
                <span class="input-group-text" id="basic-addon2">MB</span>
            </div>
        </div>
    </div>
    @if ($plan->name != "Free Plan")
    <div class="row">
        <div class="col-md-6 mt-3 plan_price_div">
            <label class="form-check-label" for="trial"></label>
            <div class="form-group">
                <label for="trial" class="form-label">{{ __('Trial is enable(on/off)') }}</label>
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="trial" class="form-check-input input-primary pointer" value="1" id="trial"  {{ $plan['trial'] == 1 ? 'checked="checked"' : '' }}>
                    <label class="form-check-label" for="trial"></label>
                </div>
            </div>
        </div>
        <div class="col-md-6  {{ $plan['trial'] == 1 ? 'd-block' : 'd-none' }} plan_div plan_price_div">
            <div class="form-group">
                {{ Form::label('trial_days', __('Trial Days'), ['class' => 'form-label']) }}
                {{ Form::number('trial_days',null, ['class' => 'form-control','placeholder' => __('Enter Trial days'),'step' => '1','min'=>'1']) }}
            </div>
        </div>
    </div>
    @endif
    <div class="form-group col-md-6">
        <div class="form-check form-switch custom-switch-v1">
            <span class="custom-switch-description ml-0 ms-2 text-dark">{{ __('Enable Chatgpt') }}</span>
            <input type="hidden" name="enable_chatgpt" value="off">
            <input type="checkbox" name="enable_chatgpt" class="form-check-input" id="enable_chatgpt"
                {{ $plan->enable_chatgpt == 'on' ? 'checked' : '' }}>
            <label class="form-check-label" for="enable_chatgpt"></label>
        </div>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
        {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'3']) !!}
    </div>

    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
    </div>
</div>
{{ Form::close() }}
<script>
    $(document).on('change', '#is_free_plan', function() {
        var value =  $(this).val();
        PlanLable(value);
    });
    $(document).on('change', '#trial', function() {
        if ($(this).is(':checked')) {
            $('.plan_div').removeClass('d-none');
            $('#trial').attr("required", true);

        } else {
            $('.plan_div').addClass('d-none');
            $('#trial').removeAttr("required");
        }
    });

    $(document).on('keyup mouseup', '#number_of_user', function() {
        var user_counter = parseInt($(this).val());
        if (user_counter == 0  || user_counter < -1)
        {
            $(this).val(1)
        }

    });
    $(document).on('keyup mouseup', '#number_of_workspace', function() {
        var workspace_counter = parseInt($(this).val());
        if (workspace_counter == 0 || workspace_counter < -1)
        {
            $(this).val(1)
        }
    });

    function PlanLable(value){
        if(value == 1){
            $('.plan_price_div').addClass('d-none');
        }
        if(value == 0){
            $('.plan_price_div').removeClass('d-none');
            if ($(".add_lable").find(".text-danger").length === 0) {
                $(".add_lable").append(`<span class="text-danger"> <sup>Paid</sup></span>`);
            }
        }
    }
</script>
