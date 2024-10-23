{{Form::model($documentUpload,array('route' => array('document-upload.update', $documentUpload->id), 'method' => 'PUT','enctype' => "multipart/form-data")) }}
{{-- <div class="card-body p-0"> --}}
    @php
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
   @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
     <div class="text-end">
                <a href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate',['document']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate') }}" float-end>
            <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
        </a>
     </div>
     @endif

        <div class="form-group col-md-12">
            {{Form::label('name',__('Name'),['class' => 'col-form-label'])}}
            {{Form::text('name',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-12">
                {{Form::label('document',__('Document'),['class' => 'col-form-label'])}}
                {{Form::file('document',array('class'=>'form-control file-validate','id'=>'files', 'data-filename' => 'upload_file'))}}
                <span id="" class="file-error text-danger"></span>
                <p class="upload_file"></p>
                @if (isset($documentUpload->document))
                    <img id="image" class="mt-2" src="{{\App\Models\Utility::get_file('uploads/documentUpload/'.$documentUpload->document)}}" style="width:25%;"/>
                @else
                    <img id="image" class="mt-2" src="{{asset(Storage::url('uploads/logo/dark-logo.png'))}}" style="width:25%;"/>
                @endif
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
            {{ Form::textarea('description',null, array('class' => 'form-control','rows'=>3)) }}
        </div>
    </div>
{{-- </div> --}}
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
</div>
{{Form::close()}}

<script>
    document.getElementById('files').onchange = function () {
    var src = URL.createObjectURL(this.files[0])
    document.getElementById('image').src = src
    }
</script>
