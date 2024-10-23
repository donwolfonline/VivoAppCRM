@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Contract')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Contract')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Contract')}}</li>
@endsection

@section('action-btn')
    <a href="{{ route('contract.index') }}" class="btn btn-sm btn-primary btn-icon m-1">
        <i class="ti ti-list text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('List View') }}"></i>
    </a>
    @if(\Auth::user()->type=='company')
    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
    data-bs-target="#exampleModal" data-url="{{ route('contract.create') }}"
    data-bs-whatever="{{__('Create New Contract')}}"> <span class="text-white"> 
        <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
    </a>

     
    @endif
@endsection
@section('filter')
@endsection
@section('content')
    <div class="row">
        @forelse ($contracts as $contract)
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <a href="{{route('contract.show',$contract->id)}}" class="mb-0">{{ $contract->subject}}</a>             
                            </div>
                            @if(\Auth::user()->type=='company')
                                <div class="text-right">
                                    <div class="actions">
                                        <div class="dropdown action-item">
                                            <a href="#" class="action-item" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                                data-bs-target="#exampleModal" data-url="{{ route('contract.edit',$contract->id) }}" class="dropdown-item"
                                                data-bs-whatever="{{__('Edit Contract')}}"  
                                                data-bs-original-title="{{__('Edit Contract')}}"><span class=""> <i
                                                        class="ti ti-edit"></i></span>{{ __('Edit') }}</a>

                                           

                                                {!! Form::open(['method' => 'DELETE', 'route' => ['contract.destroy', $contract->id]]) !!}
                                                    <a href="#!" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm">
                                                        <i class="ti ti-trash" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>{{ __('Delete') }}
                                                    </a>
                                                {!! Form::close() !!}
                                                    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- @dd($contracts , $contract) --}}
                    <div class="card-body py-3 flex-grow-1">

                        <p class="text-sm mb-0">
                            {{ $contract->description}}
                        </p>
                    </div>
                    <div class="card-footer py-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <span class="form-control-label">{{__('Contract Type')}}:</span>
                                    </div>
                                    <div class="col-6 text-right">
                                        <span class="badge bg-primary p-2 px-3 rounded">{{ !empty($contract->types)?$contract->types->name:'' }}</span>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <span class="form-control-label">{{__('Contract Value')}}:</span>
                                    </div>
                                    <div class="col-6 text-right">
                                        <span class="badge bg-primary p-2 px-3 rounded">{{ \Auth::user()->priceFormat($contract->value) }}</span>
                                    </div>
                                </div>
                            </li>
                            @if(\Auth::user()->type!='client')
                                <li class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <span class="form-control-label">{{__('Client')}}:</span>
                                        </div>
                                        <div class="col-6 text-right">
                                            {{ !empty($contract->clients)?$contract->clients->name:'' }}
                                        </div>
                                    </div>
                                </li>
                            @endif
                            <li class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <small>{{__('Start Date')}}:</small>
                                        <div class="h6 mb-0">{{  \Auth::user()->dateFormat($contract->start_date )}}</div>
                                    </div>
                                    <div class="col-6">
                                        <small>{{__('End Date')}}:</small>
                                        <div class="h6 mb-0">{{  \Auth::user()->dateFormat($contract->end_date )}}</div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center pt-5">
                <H3> {{ __('No Contract Found..') }}</H3>
            </div>
        @endforelse
    </div>

@endsection

