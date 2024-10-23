{{Form::open(array('route'=>'webhook.store','method'=>'post'))}}
    <div>
        <div class="row">
            <div class="col-md-12 form-group">
                {{Form::label('module',__('Module'),['class'=>'col-form-label']) }}
                <select name="module" id="module" class="form-control">
                    @foreach ($webhook as $key=>$value)
                        <option value="{{ $key }}" >{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 form-group">
                {{Form::label('url',__('URL'),['class'=>'col-form-label'])}}
                {{Form::url('url',null,array('class'=>'form-control','placeholder'=>__('Enter Webhook URL'),'required'=>'required'))}}
            </div>
            <div class="col-md-12 form-group">
                {{Form::label('method',__('Method'),['class'=>'col-form-label'])}}
                <select name="method" id="method" class="form-control">
                   @foreach ($method as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                   @endforeach
                </select>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-secondary btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary ms-2">
    </div>
{{Form::close()}}
