@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Items')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Items')}}</h5>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Items')}}</li>
@endsection


@section('action-btn')
    @if(\Auth::user()->type=='company')
        <a href="{{ route('item.grid') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="{{ __('Grid View') }}" >
            <i class="ti ti-layout-grid text-white"></i>
        </a>

        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
        data-bs-target="#exampleModal" data-url="{{ route('item.file.import') }}"
        data-bs-whatever="{{__('Import item CSV file')}}"> <span class="text-white">
            <i class="ti ti-file-import" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Import') }}"></i></span>
        </a>

        <a href="{{route('item.export')}}" class="btn btn-sm btn-primary btn-icon m-1" data-title="{{__('Export item CSV file')}}"
         data-bs-toggle="tooltip" data-bs-original-title="{{__('Export')}}">
            <i class="ti ti-file-export"></i>
        </a>

        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
        data-bs-target="#exampleModal" data-url="{{ route('item.create') }}" data-size="lg"
        data-bs-whatever="{{__('Create New Item')}}" > <span class="text-white">
            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
        </a>


    @endif
@endsection
@section('filter')

@endsection
@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <!-- <h5></h5> -->
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th scope="col">{{__('Item')}}</th>
                                <th scope="col">{{__('Category')}}</th>
                                <th scope="col">{{__('Quantity')}}</th>
                                <th scope="col">{{__('Sale Price')}}</th>
                                <th scope="col">{{__('Purchase Price')}}</th>
                                <th scope="col">{{__('Tax')}}</th>
                                <th scope="col">{{__('Unit')}}</th>
                                <th scope="col">{{__('Description')}}</th>
                                @if(\Auth::user()->type=='company')
                                    <th scope="col" class="text-right">{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <th scope="row">
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <a href="#" class="name h6 mb-0 text-sm">{{ $item->name}}</a><br>
                                                <span class="text-capitalize badge bg-{{$item->type=='product' ? 'success':'danger'}} primary p-1 px-3 rounded" data-bs-toggle="tooltip" title="{{__('Type')}}">
                                                    {{ $item->type }}
                                                </span>
                                                <span class="ml-2 badge bg-info p-1 px-3 rounded" data-bs-toggle="tooltip" title="{{__('SKU')}}">
                                                {{ $item->sku }}
                                                </span>
                                            </div>
                                        </div>
                                    </th>
                                    <td>{{ !empty($item->categories)?$item->categories->name:'--' }}</td>
                                    <td>{{ !empty($item->quantity)?$item->quantity:'--'}}</td>
                                    <td>{{ \Auth::user()->priceFormat($item->sale_price) }}</td>
                                    <td>{{  \Auth::user()->priceFormat($item->purchase_price )}}</td>
                                    <td>
                                        @if(!empty($item->tax))
                                            @foreach(explode(',', $item->tax) as $tax)
                                                {{ !empty($getTaxData[$tax])?$getTaxData[$tax]['name']:'--'  }} <br>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ !empty($item->units)?$item->units->name:'--' }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-right">
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal" data-url="{{ route('item.edit',$item->id) }}" data-size="lg"
                                            data-bs-whatever="{{__('Edit Item   ')}}"> <span class="text-white"> <i
                                                    class="ti ti-edit" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                        </div>

                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['item.destroy', $item->id]]) !!}
                                            <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                            </a>
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

