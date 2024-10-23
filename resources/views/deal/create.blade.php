 
{{ Form::open(array('url' => 'deal')) }}
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
 
    <div class="form-group col-md-6">
        {{ Form::label('name', __('Deal Name'),['class' => 'col-form-label']) }}
        {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('price', __('Price'),['class' => 'col-form-label']) }}
        {{ Form::number('price', 0, array('class' => 'form-control','min'=>0)) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('clients', __('Clients'),['class' => 'col-form-label']) }}
        {{ Form::select('clients[]', $clients,null, array('class' => 'form-control multi-select','id'=>'choices-multiple','multiple'=>'','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('phone_no', __('Phone No'),['class' => 'col-form-label']) }}
        {{ Form::tel('phone_no', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
</div>
<div class="modal-footer pr-0">
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
