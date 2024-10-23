@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{ __('Meeting') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Meeting') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Meeting') }}</li>
@endsection
@section('action-btn')
    <a href="{{ route('meeting.calendar') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
        data-bs-placement="top" title="Calendar View"> <span class="text-white">
            <i class="ti ti-calendar-event text-white"></i></span>
    </a>

    @if (\Auth::user()->type == 'company'|| \Auth::user()->type == 'employee')
        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
            data-url="{{ route('meeting.create') }}" data-bs-whatever="{{ __('Create New Meeting') }}"
            data-bs-placement="top">
            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
        </a>
    @endif
@endsection

@section('content')
    <div class="col-xl-12">
        <div class=" {{ isset($_GET['department']) ? 'show' : '' }}">
            <div class="card card-body">
                {{ Form::open(['url' => 'meeting', 'method' => 'get']) }}
                <div class="row filter-css">

                    @if (\Auth::user()->type == 'company')
                        <div class="col-md-2">
                            {{ Form::select('department', $departments, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control', 'data-toggle' => 'select']) }}
                        </div>
                        <div class="col-md-2">
                            {{ Form::select('designation', $designations, isset($_GET['designation']) ? $_GET['designation'] : '', ['class' => 'form-control', 'data-toggle' => 'select']) }}
                        </div>
                    @endif

                    <div class="col-auto">
                        {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : new \DateTime(), ['class' => 'form-control']) }}
                    </div>
                    <div class="col-auto">
                        {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : new \DateTime(), ['class' => 'form-control']) }}
                    </div>
                    <div class="action-btn bg-info ms-2">
                        <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                            data-toggle="tooltip" data-title="{{ __('Apply') }}"><i data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('Apply') }}" class="ti ti-search text-white"></i></button>
                    </div>
                    <div class="action-btn bg-danger ms-2">
                        <a href="{{ route('meeting.index') }}" data-toggle="tooltip" data-title="{{ __('Reset') }}"
                            class="mx-3 btn btn-sm d-inline-flex align-items-center">
                            <i data-bs-toggle="tooltip" data-bs-original-title="{{ __('Reset') }}"
                                class="ti ti-trash-off text-white">
                            </i>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>


    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <h5></h5>
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <th>{{ __('title') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Time') }}</th>
                            <th>{{ __('Department') }}</th>
                            <th>{{ __('Designation') }}</th>
                            @if (\Auth::user()->type == 'company')
                                <th class="text-right" width="200px">{{ __('Action') }}</th>
                            @endif
                        </thead>
                        <tbody>
                            @foreach ($meetings as $meeting)
                                <tr>
                                    <td>{{ $meeting->title }}</td>
                                    <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                    <td>{{ \Auth::user()->timeFormat($meeting->time) }}</td>
                                    <td>{{ !empty($meeting->departments) ? $meeting->departments->name : 'All' }}</td>
                                    <td>{{ !empty($meeting->designations) ? $meeting->designations->name : 'All' }}</td>
                                    @if (\Auth::user()->type == 'company')
                                        <td class="text-right">
                                            {{-- <a href="#" class="action-item" data-url="{{ route('meeting.edit',$meeting->id) }}" data-ajax-popup="true" data-title="{{__('Edit Meeting')}}" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                            <i class="far fa-edit"></i>
                                        </a> --}}

                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                    data-url="{{ route('meeting.edit', $meeting->id) }}"
                                                    data-bs-whatever="{{ __('Edit Meeting') }}" data-bs-placement="top"
                                                    title="Edit"> <span class="text-white"> <i class="ti ti-edit"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="
                                                            {{ __('Edit') }}">
                                                        </i>
                                                    </span>
                                                </a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['meeting.destroy', $meeting->id]]) !!}
                                                <a href="#!"
                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm m-2">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Delete') }}">
                                                    </i>
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
