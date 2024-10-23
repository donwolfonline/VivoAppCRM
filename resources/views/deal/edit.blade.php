{{ Form::model($deal, array('route' => array('deal.update', $deal->id), 'method' => 'PUT')) }}
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
        {{ Form::number('price',null, array('class' => 'form-control','min'=>0)) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('pipeline_id', __('Pipeline'),['class' => 'col-form-label']) }}
        {{ Form::select('pipeline_id', $pipelines,null, array('class' => 'form-control multi-select ','required'=>'required','id'=>'Pipeline')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('stage_id', __('Stage'),['class' => 'col-form-label']) }}
        {{ Form::select('stage_id', [''=>__('Select Stages')],null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('sources', __('Sources'),['class' => 'col-form-label']) }}
        {{ Form::select('sources[]', $sources,null, array('class' => 'form-control multi-select','id'=>'choices-multiple','multiple'=>'','required'=>'required')) }}

    </div>
    <div class="form-group col-md-6">
        {{ Form::label('products', __('Items'),['class' => 'col-form-label']) }}
        {{ Form::select('products[]', $products,null, array('class' => 'form-control multi-select','id'=>'choices-multiple1','multiple'=>'','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('phone_no', __('Phone No'),['class' => 'col-form-label']) }}
        {{ Form::text('phone_no', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('notes', __('Notes'),['class' => 'col-form-label']) }}
        {{ Form::textarea('notes',null, array('class' => 'form-control','rows'=>'3')) }}
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
</div>

{{ Form::close() }}

<script>


    var stage_id = '{{$deal->stage_id}}';

    $(document).ready(function () {
        $("#exampleModal select[name=pipeline_id]").trigger('change');
    });

    // Trigger the change event when the modal is shown
    $('#exampleModal').on('shown.bs.modal', function () {
        $("#exampleModal select[name=pipeline_id]").trigger("change");
    });
    
    $(document).on("change", "#exampleModal select[name=pipeline_id]", function () {
        $.ajax({
            url: '{{route('dealStage.json')}}',
            data: {pipeline_id: $(this).val(), _token: $('meta[name="csrf-token"]').attr('content')},
            type: 'POST',
            success: function (data) {
                $("#stage_id").html('<option value="" selected="selected">{{__('Select Deal Stages')}}</option>');
                $.each(data, function (key, data) {
                    var select = '';
                    if (key == '{{ $deal->stage_id }}') {
                        select = 'selected';
                    }

                    $("#stage_id").append('<option value="' + key + '" ' + select + '>' + data + '</option>');
                });
                $("#stage_id").val(stage_id);
            }
        })
    });
</script>

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
