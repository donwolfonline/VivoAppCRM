{{ Form::open(array('url' => 'contract')) }}
@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">
     <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
    data-url="{{ route('generate',['contract']) }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
 </div>
 @endif
 
    <div class="form-group col-md-12">
        {{ Form::label('subject', __('Subject'),['class' => 'col-form-label']) }}
        {{ Form::text('subject', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('client', __('Client'),['class'=>'form-label'])}}
        {{ Form::select('client', $clients, null, ['class' => 'form-control select client_select', 'id' => 'client_select']) }}
    </div>

    <div class="form-group col-md-6" >
        {{ Form::label('projects', __('Projects'),['class'=>'form-label'])}}
        <select class="form-control select project_select" id="project_id" name="project_id" >
            <option value="">{{__('Select Project')}}</option>
        </select>
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('type', __('Contract Type'),['class' => 'col-form-label']) }}
        {{ Form::select('type', $contractTypes,null, array('class' => 'form-control ','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('value', __('Contract Value'),['class' => 'col-form-label']) }}
        {{ Form::number('value', '', array('class' => 'form-control','required'=>'required','stage'=>'0.01')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('start_date', __('Start Date'),['class' => 'col-form-label']) }}
        {{ Form::date('start_date', new \DateTime(), array('class' => 'form-control','required'=>'required')) }}
           
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('end_date', __('End Date'),['class' => 'col-form-label']) }}
        {{ Form::date('end_date', new \DateTime(), array('class' => 'form-control','required'=>'required')) }}
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
        {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'3']) !!}
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

<script type="text/javascript">

        $( ".client_select" ).change(function() {
            
            var client_id = $(this).val();
            getparent(client_id);
        });
        
        function getparent(bid) {

        $.ajax({
            url: `{{ url('contract/clients/select')}}/${bid}`,
            type: 'GET',
            success: function (data) {
                console.log(data);
                $("#project_id").html('');
            $('#project_id').append('<select class="form-control" id="project_id" name="project_id[]"  ></select>');
                //var sdfdsfd = JSON.parse(data);
                $.each(data, function (i, item) {
                    //console.log(item.name);
                    $('#project_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                });

                // var multipleCancelButton = new Choices('#project_id', {
                //     removeItemButton: true,
                // });

                if (data == '') {
                    $('#project_id').empty();
                }
            }
});
}

</script>
