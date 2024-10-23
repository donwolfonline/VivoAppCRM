{{ Form::model($item, array('route' => array('item.update', $item->id), 'method' => 'PUT')) }}
@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">
      <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
    data-url="{{ route('generate',['items']) }}" data-title="{{ __('Generate') }}" float-end>
        <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
    </a>
 </div>
 @endif
 
    <div class="form-group col-md-6">
        {{ Form::label('name', __('Item Name'),['class' => 'col-form-label']) }}
        {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('sku', __('SKU'),['class' => 'col-form-label']) }}
        {{ Form::text('sku', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('sale_price', __('Sale Price'),['class' => 'col-form-label']) }}
        {{ Form::number('sale_price', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('purchase_price', __('Purchase Price'),['class' => 'col-form-label']) }}
        {{ Form::number('purchase_price', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('quantity', __('Quantity'),['class' => 'col-form-label']) }}
        {{ Form::number('quantity', null, array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('unit', __('Unit'),['class' => 'col-form-label']) }}
        {{ Form::select('unit', $unit,null, array('class' => 'form-control multi-select','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('tax', __('Tax'),['class'=>'col-form-label']) }}
        {{ Form::select('tax[]', $tax,null, array('class' => 'form-control multi-select','id'=>'choices-multiple','multiple'=>'','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('category', __('Category'),['class' => 'col-form-label']) }}
        {{ Form::select('category', $category,null, array('class' => 'form-control multi-select','required'=>'required')) }}
    </div>
 
    <div class="form-group col-md-6">
        <div class="form-group">
            <label class="d-block">{{__('Type')}}</label>
            <div class="row">
                <div class="form-check col-md-6" > 
                    <input class="form-check-input" style="margin-left:-0.25em;" type="radio" name="type" value="product" @if($item->type == "product") checked="checked" @endif
                        id="flexCheckDefault">
                    <label class="form-check-label" for="flexCheckDefault">
                        {{__('Product')}}
                    </label>
                </div>
                <div class="form-check col-md-6">
                    <input class="form-check-input" type="radio" name="type" value="service" @if($item->type == "service") checked="checked" @endif
                        id="flexCheckChecked">
                    <label class="form-check-label" for="flexCheckChecked">
                        {{__('Service')}}
                    </label>
                </div>

            </div>
        </div>
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
    {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
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

