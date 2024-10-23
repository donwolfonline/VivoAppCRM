
    {{ Form::open(array('route' => array('holiday.import'),'method'=>'post', 'enctype' => "multipart/form-data")) }}
    <div class="col-md-12 mb-2">
        <div class="d-flex align-items-center justify-content-between">
            {{Form::label('file',__('Download sample Holiday CSV file'),['class'=>'form-control-label w-auto m-0'])}}
            <div>
                <a href="{{asset(Storage::url('uploads/sample')).'/sample-holiday.csv'}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-download"></i> {{__('Download')}}
                </a>
            </div>
        </div>
    </div>
        <div class="col-md-12">
            {{Form::label('file',__('Select CSV File:-'),['class'=>'form-control-label'])}}
            <div class="choose-file form-group col-12">
                <label for="file" class="form-control-label">
                    <div>{{__('Choose file here')}}
                    <input type="file" class="form-control" name="file" id="file" data-filename="upload_file" required>
                </div>
                </label>
                <p class="upload_file"></p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
            {{Form::submit(__('Upload'),array('class'=>'btn  btn-primary'))}}
        </div>
    </div>
    {{ Form::close() }}
