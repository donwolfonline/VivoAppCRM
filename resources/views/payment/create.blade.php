{{ Form::open(array('url' => array('payment'),"enctype" => "multipart/form-data")) }}
<div class="row">
    <div class="form-group  col-md-6">
        {{ Form::label('date', __('Date'),['class' => 'col-form-label']) }}
        {{ Form::date('date',new \DateTime(), array('class' => 'form-control')) }}

    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('amount', __('Amount'),['class' => 'col-form-label']) }}
        {{ Form::number('amount', '', array('class' => 'form-control','step'=>'0.01', 'required' => 'required')) }}

    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('payment_method', __('Payment Method'),['class' => 'col-form-label']) }}
        {{ Form::select('payment_method', $paymentMethod,null, array('class' => 'form-control multi-select')) }}

    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('client', __('Client'),['class' => 'col-form-label']) }}
        <!-- {{ Form::select('client', $clients,null, array('class' => 'form-control multi-select')) }} -->
        <select name="client" id="client" class="form-control multi-select required" required>
            <option value="" selected disabled>{{ __('Select Client') }}</option>
            @foreach($clients as $key => $value)
                <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>

    </div>
    <div class="form-group  col-md-12">
        {{ Form::label('reference', __('Reference'),['class' => 'col-form-label']) }}
        {{ Form::text('reference', '', array('class' => 'form-control','required' => 'required')) }}

    </div>
    <div class="form-group col-md-12">
        {{ Form::label('receipt', __('Payment Receipt'),['class' => 'col-form-label']) }}
        {{ Form::file('receipt', array('class' => 'form-control file-validate', 'data-filename' => "upload_file",'accept'=>'.jpeg,.jpg,.png,.doc,.pdf','id'=>'files', 'required' => 'required')) }}
        <span id="" class="file-error text-danger"></span>
        {{-- <img id="image" style="width:25%;"/> --}}
        <p class="upload_file"></p>
        <img id="image" class="mt-2" src="{{asset(Storage::url('uploads/logo/logo-dark.png'))}}" style="width:25%;"/>
    </div>
  
    <div class="form-group  col-md-12">
        {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
        {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>'2')) }}

    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
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
