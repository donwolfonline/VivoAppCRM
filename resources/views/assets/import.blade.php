
    {{ Form::open(array('route' => array('assets.import'),'method'=>'post', 'enctype' => "multipart/form-data")) }}
    <div class="col-md-12 mb-2">
        <div class="d-flex align-items-center justify-content-between">
            {{Form::label('file',__('Download sample customer CSV file'),['class' => 'col-form-label'])}}
            <div>
                <a href="{{asset(Storage::url('uploads/sample')).'/sample-asset.csv'}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-download"></i> {{__('Download')}}
                </a>
            </div>
        </div>
    </div>
        <div class="col-md-12">
            {{Form::label('file',__('Select CSV File'),['class' => 'col-form-label'])}}
            <div class="choose-file form-group">
                <label for="file" class="form-control-label">
                    <input type="file" class="form-control" name="file" id="file" data-filename="upload_file" required>
                </label>
                <p class="upload_file"></p>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-dismiss="modal">
            <input type="submit" value="{{__('Upload')}}" class="btn  btn-primary">
        </div>
    </div>
