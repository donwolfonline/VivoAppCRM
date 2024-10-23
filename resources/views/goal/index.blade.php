@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Goal')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Goal')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Goal')}}</li>
@endsection

@section('action-btn')
    @if(\Auth::user()->type=='company')
    
    <a href="{{route('goal.export')}}" class="btn btn-sm btn-primary btn-icon m-1" title="{{__('Export goal CSV file')}}" data-bs-toggle="tooltip">
        <i class="ti ti-file-export"></i>
    </a>
    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
        data-bs-target="#exampleModal" data-url="{{ route('goal.create') }}"
        data-bs-whatever="{{__('Create New Goal')}}"> <span class="text-white"> 
            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
    </a>
       
    @endif
@endsection

@section('content')
    <div class="row">
        @foreach($goals as $goal)
            <div class="col-md-3">
                <div class="card card-fluid">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col ml-md-n2">
                                <a href="#!" class="d-block h6 mb-0">{{$goal->name}}</a>
                            </div>

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h6 mb-0">{{\Auth::user()->dateFormat($goal->from)}}</span>
                                <span class="d-block text-sm">{{__('From')}}</span>
                            </div>
                            <div class="col-auto text-right">
                                <span class="h6 mb-0">{{\Auth::user()->dateFormat($goal->to)}}</span>
                                <span class="d-block text-sm">{{__('To')}}</span>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col">
                                <span class="h6 mb-0">{{\Auth::user()->priceFormat($goal->amount)}}</span>
                                <span class="d-block text-sm">{{__('Amount')}}</span>
                            </div>
                            <div class="col-auto text-end">
                                <span class="h6 mb-0">{{$goal->display==1 ? __('Yes') :__('No')}}</span>
                                <span class="d-block text-sm">{{__('Display on dashboard')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="badge bg-primary fix_badge p-2 px-3 rounded" data-bs-toggle="tooltip" title="{{__('Goal Type')}}">{{ __(\App\Models\Goal::$goalType[$goal->goal_type]) }}</span>
                            </div>
                            <div class="col-6 text-end">
                                <div class="actions">
                                    <div class="dropdown action-item" >
                                        <a href="#" class="action-item" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal" data-url="{{ route('goal.edit',$goal->id) }}"
                                            data-bs-whatever="{{__('Edit Goal')}}" >
                                            <i class="ti ti-edit"></i>  {{__('Edit')}}</a>
                                            

                                            {!! Form::open(['method' => 'DELETE', 'route' => ['goal.destroy', $goal->id]]) !!}
                                            <a href="#!" class=" show_confirm dropdown-item">
                                                <i class="ti ti-trash"></i>{{ __('Delete') }}
                                            </a>
                                            {!! Form::close() !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="card text-center">
            <div class="pt-10 card-body">
                <span> {{ __('No Entry Found') }} </span> 
             </div>
        </div>
    </div>
@endsection

