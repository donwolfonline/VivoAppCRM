{{ Form::open(array('url' => 'noticeBoard')) }}
@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">
      <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" 
    data-url="{{ route('generate',['notice board']) }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
 </div>
 @endif
 
    <div class="form-group col-md-12">
        {{ Form::label('heading', __('Notice Heading') ,['class' => 'col-form-label']) }}
        {{ Form::text('heading', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group row">
        <div class="form-group col-md-12">
            <div class="form-check form-check-inline">
                <input class="form-check-input type" type="radio" name="type" value="Client" 
                    id="customCheckinlh1" checked="checked">
                <label class="form-check-label" for="customCheckinlh1">
                    {{__('To Clients')}}
                </label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input type" type="radio" name="type" value="Employee" 
                    id="customCheckinlh2">
                <label class="form-check-label" for="customCheckinlh2">
                    {{__('To Employees')}}
                </label>
            </div>
        </div>  
    </div>


    <div class="form-group col-md-12 department d-none">
        {{ Form::label('department', __('Department') ,['class' => 'col-form-label']) }}
        {{ Form::select('department', $departments,null, array('class' => 'form-control multi-select')) }}
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('notice_detail', __('Notice Details') ,['class' => 'col-form-label']) }}
        {!! Form::textarea('notice_detail', null, ['class'=>'form-control','rows'=>'3']) !!}
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