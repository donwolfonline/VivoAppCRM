{{ Form::model($expense,array('route' => array('expense.update',$expense->id),'method'=>'PUT','enctype' => "multipart/form-data")) }}
<div class="row">
    <div class="form-group  col-md-6">
        {{ Form::label('date', __('Date'),['class' => 'col-form-label']) }}
        {{ Form::date('date', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('amount', __('Amount'),['class' => 'col-form-label']) }}
        {{ Form::number('amount', null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('user', __('User'),['class' => 'col-form-label']) }}
        {{ Form::select('user', $users,null, array('class' => 'form-control multi-select')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('project', __('Project'),['class' => 'col-form-label']) }}
        {{ Form::select('project', $projects,null, array('class' => 'form-control multi-select')) }}
    </div>
    <div class="form-group  col-md-12">
        {{ Form::label('attachment', __('Attachment'),['class' => 'col-form-label']) }}
        {{ Form::file('attachment', array('class' => 'form-control','accept'=>'.jpeg,.jpg,.png,.doc,.pdf','id'=>'files')) }}
        
        <img src="{{(!empty($expense->attachment))?  \App\Models\Utility::get_file('uploads/attachment/'.$expense->attachment): asset(url("custom/img/news/img01.jpg"))}}" class="mt-2" id="image" style="width:10%;">
    </div>
    <div class="form-group  col-md-12">
        {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
        {{ Form::textarea('description', null, array('class' => 'form-control','rows'=>'2')) }}
    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
    </div>
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
  <script>
    document.getElementById('files').onchange = function () {
    var src = URL.createObjectURL(this.files[0])
    document.getElementById('image').src = src
    }
</script>