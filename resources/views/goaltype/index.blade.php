@extends('layouts.admin')

@section('page-title')
    {{__('Manage Goal Type')}}
@endsection

@section('action-btn')
    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
    data-bs-target="#exampleModal" data-url="{{ route('goaltype.create') }}"
    data-bs-whatever="{{__('Create New Goal Type')}}"> <span class="text-white"> 
        <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
    </a>

@endsection


@section('title')
    <div class="d-inline-block">
        <h5 class="titleIn h4 d-inline-block font-weight-400 mb-0 ">{{__('Manage Goal Type')}}</h5>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Goal Type')}}</li>
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
                                <th>{{__('Goal Type')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($goaltypes as $goaltype)
                            <tr>
                                <td>{{ $goaltype->name }}</td>
                                <td>

                                    <div class="action-btn bg-info ms-2">
                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal" data-url="{{  route('goaltype.edit',$goaltype->id)}}"
                                            data-bs-whatever="{{__('Edit Goal Type')}}" > <span class="text-white"> <i
                                                    class="ti ti-edit" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                    </div>

                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['goaltype.destroy', $goaltype->id]]) !!}
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
