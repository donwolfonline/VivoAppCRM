@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Award')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Award')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Award')}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company')
    <a href="{{route('award.export')}}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-original-title="{{__('Export award CSV file')}}" data-bs-toggle="tooltip">
        <i class="ti ti-file-export"></i>
    </a>

    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
    data-bs-target="#exampleModal" data-url="{{ route('award.create') }}"
    data-bs-whatever="{{__('Create New Award')}}"> <span class="text-white">
        <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
    </a>
    @endif

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
                                @if(\Auth::user()->type=='company')
                                    <th>{{__('Employee')}}</th>
                                @endif
                                <th>{{__('Award Type')}}</th>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Gift')}}</th>
                                <th>{{__('Description')}}</th>
                                @if(\Auth::user()->type=='company')
                                    <th class="text-right">{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($awards as $award)
                                <tr>
                                    @if(\Auth::user()->type=='company')
                                        <td>{{!empty( $award->employee)? $award->employee->name:'--' }}</td>
                                    @endif
                                    <td>{{ !empty($award->awardType)?$award->awardType->name:'--' }}</td>
                                    <td>{{  \Auth::user()->dateFormat($award->date )}}</td>
                                    <td>{{ $award->gift }}</td>
                                    <td>{{ $award->description }}</td>
                                    @if(\Auth::user()->type=='company')
                                        <td class="text-right">
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                                data-bs-target="#exampleModal" data-url="{{ route('award.edit',$award->id) }}"
                                                data-bs-whatever="{{__('Edit Award')}}"  title="Edit Award"
                                                data-bs-original-title="{{__('Edit Award')}}"> <span class="text-white"> <i
                                                        class="ti ti-edit" data-bs-toggle="tooltip" data-bs-original-title="{{__('Edit Award')}}"></i></span></a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['award.destroy', $award->id]]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('Delete')}}"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

