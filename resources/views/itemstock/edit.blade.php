
    {{ Form::model($Item, array('route' => array('itemstock.update', $Item->id), 'method' => 'PUT')) }}
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('Product', __('Product'),['class'=>'form-control-label']) }}<br>
            {{$Item->name}}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('Product', __('SKU'),['class'=>'form-control-label']) }}<br>
            {{$Item->sku}}

        </div>

        <div class="form-group quantity">
            <div class="row">
                <div class="d-flex radio-check">
                    <div class="custom-control custom-radio custom-control-inline col-md-6">
                        <input type="radio" id="plus_quantity" value="Add" name="quantity_type" class="form-check-input" checked="checked">
                        <label class="custom-control-label" for="plus_quantity">{{__('Add Quantity')}}</label>
                    </div>
                
                    <div class="custom-control custom-radio custom-control-inline col-md-6">
                        <input type="radio" id="minus_quantity" value="Less" name="quantity_type" class="form-check-input">
                        <label class="custom-control-label" for="minus_quantity">{{__('Less Quantity')}}</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('quantity', __('Quantity'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Form::number('quantity',"", array('class' => 'form-control','required'=>'required')) }}
        </div>


        <div class="modal-footer pr-0">
            <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
            {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
        </div>
    </div>
    {{ Form::close() }}
