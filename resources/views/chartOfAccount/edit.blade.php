    {{ Form::model($chartOfAccount, array('route' => array('chart-of-account.update', $chartOfAccount->id), 'method' => 'PUT')) }}
    @php 
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
   @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
     <div class="text-end">
             <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
        data-url="{{ route('generate',['chart of account']) }}" data-title="{{ __('Generate') }}" float-end>
            <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
        </a>
     </div>
     @endif
     
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Name'),['class' => 'col-form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('code', __('Code'),['class' => 'col-form-label']) }}
            {{ Form::text('code', null, array('class' => 'form-control','required'=>'required')) }}
        </div>

        <div class="form-group col-md-6">
            <input class="form-check-input" type="checkbox" value="enable" class="email-template-checkbox" id="is_enabled" name="is_enabled" {{$chartOfAccount->is_enabled==1?'checked':''}}>
            <label class="form-check-label" for="is_enabled">
                {{Form::label('is_enabled',__('Is Enabled'),array('class'=>'form-control-label')) }}
            </label>
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
            {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2']) !!}
        </div>

    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
    </div>
    {{ Form::close() }}


