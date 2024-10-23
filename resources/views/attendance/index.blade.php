@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{ __('Attendance') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Attendance') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Attendance') }}</li>
@endsection
@section('action-btn')
    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
        data-url="{{ route('attendance.file.import') }}" data-bs-whatever="{{ __('Import attendance CSV file') }}">
        <span class="text-white"> <i class="ti ti-file-import " data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Import attendance CSV file') }}"></i> </span></a>
@endsection

@section('content')

    <div class="col-xl-12">
        <div class="row">
            <div class="col-12">
                <div class=" {{ isset($_GET['date']) ? 'show' : '' }}" id="collapseExample">
                    <div class="card card-body">
                        {{ Form::open(['url' => 'attendance', 'method' => 'get']) }}
                        <div class="row filter-css">
                            <div class="col-md-2 my-1">
                                {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control']) }}
                            </div>
                            @if (\Auth::user()->type == 'company')
                                <div class="col-md-3 my-1">
                                    {{ Form::select('employee', $employees, isset($_GET['employee']) ? $_GET['employee'] : '', ['class' => 'form-control', 'data-toggle="select"']) }}
                                </div>
                            @endif
                            <div class="action-btn bg-info ms-2">
                                <div class="col-auto my-1">
                                    <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center"
                                        data-bs-toggle="tooltip" title="{{ __('Apply') }}"><i
                                            class="ti ti-search text-white"></i></button>
                                </div>
                            </div>
                            <div class="action-btn bg-danger ms-2">
                                <div class="col-auto my-1">
                                    <a href="{{ route('attendance.index') }}" data-bs-toggle="tooltip"
                                        title="{{ __('Reset') }}" class="mx-3 btn btn-sm d-flex align-items-center"><i
                                            class="ti ti-trash-off text-white"></i></a>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                 @if (session('status'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Employee') }}</th>
                                @endif
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Clock In') }}</th>
                                <th>{{ __('Clock Out') }}</th>
                                <th>{{ __('Late') }}</th>
                                <th>{{ __('Early Leaving') }}</th>
                                <th>{{ __('Overtime') }}</th>
                                @if (\Auth::user()->type == 'company')
                                    <th class="text-right">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendances as $attendance)
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>{{ !empty($attendance->user) ? $attendance->user->name : '' }}</td>
                                    @endif
                                    <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                    <td>{{ $attendance->status }}</td>
                                    <td>{{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00:00' }}
                                    </td>
                                    <td>{{ $attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00:00' }}
                                    </td>
                                    <td>{{ $attendance->late }}</td>
                                    <td>{{ $attendance->early_leaving }}</td>
                                    <td>{{ $attendance->overtime }}</td>
                                    @if (\Auth::user()->type == 'company')
                                        <td class="text-right">
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                    data-url="{{ route('attendance.edit', $attendance->id) }}"
                                                    data-bs-whatever="{{ __('Edit Attendance') }}" title="Edit Attendance">
                                                    <span class="text-white"> <i class="ti ti-edit" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit Attendance') }}"></i></span></a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['attendance.destroy', $attendance->id]]) !!}
                                                <a href="#!"
                                                    class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Delete') }}"></i>
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
