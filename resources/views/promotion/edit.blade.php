{{Form::model($promotion,array('route' => array('promotion.update', $promotion->id), 'method' => 'PUT')) }}
<div class="card-body p-0">
    @php 
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
   @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
     <div class="text-end">
             <a href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate',['promotion']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate') }}" float-end>
            <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
        </a>
     </div>
     @endif
     
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('employee_id', __('Employee'),['class' => 'col-form-label']) }}
            {{ Form::select('employee_id', $employees,null, array('class' => 'form-control multi-select','required'=>'required')) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('designation_id',__('Designation'),['class' => 'col-form-label'])}}
            {{Form::select('designation_id',$designations,null,array('class'=>'form-control multi-select'))}}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('promotion_title',__('Promotion Title'),['class' => 'col-form-label'])}}
            {{Form::text('promotion_title',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('promotion_date',__('Promotion Date'),['class' => 'col-form-label'])}}
            {{Form::date('promotion_date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-lg-12">
            {{Form::label('description',__('Description'),['class' => 'col-form-label'])}}
            {{Form::textarea('description',null,array('class'=>'form-control','rows'=>3,'placeholder'=>__('Enter Description')))}}
        </div>

    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
</div>
{{Form::close()}}

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