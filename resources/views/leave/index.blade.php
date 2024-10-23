@extends('layouts.admin')
@push('script-page')
    <script>
        $(document).on('change', '#employee_id', function() {

            var employee_id = $(this).val();

            $.ajax({
                url: '{{ route('leave.jsoncount') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    // "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#leave_type').empty();
                    $('#leave_type').append('<option value="">{{ __('Select Leave Type') }}</option>');

                    $.each(data, function(key, value) {

                        if (value.total_leave >= value.days) {
                            $('#leave_type').append('<option value="' + value.id +
                                '" disabled>' + value.title + '&nbsp(' + value.total_leave +
                                '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type').append('<option value="' + value.id + '">' + value
                                .title + '&nbsp(' + value.total_leave + '/' + value.days +
                                ')</option>');
                        }
                    });

                }
            });
        });
    </script>
@endpush
@section('page-title')
    {{ __('Leave') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Leave') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Leave') }}</li>
@endsection

@section('action-btn')
    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        <a href="{{ route('leave.calendar') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
            title="Calendar View">
            <i class="ti ti-calendar text-white"></i>
        </a>

        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
            data-url="{{ route('leave.create') }}" data-bs-whatever="{{ __('Create New Leave') }}" data-size="lg">
            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
        </a>
    @endif
@endsection

@section('content')
    <div class="">
        <div class="col-xl-12">
            <div class=" {{ isset($_GET['employee']) ? 'show' : '' }}">
                <div class="card card-body">
                    {{ Form::open(['url' => 'leave', 'method' => 'get']) }}
                    <div class="row filter-css">
                        @if (\Auth::user()->type == 'company')
                            <div class="col-md-3">
                                {{ Form::select('employee', $employees, isset($_GET['employee']) ? $_GET['employee'] : '', ['class' => 'form-control', 'data-toggle' => 'select']) }}
                            </div>
                        @endif
                        <div class="col-auto">
                            {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : new \DateTime(), ['class' => 'form-control']) }}
                        </div>
                        <div class="col-auto">
                            {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : new \DateTime(), ['class' => 'form-control']) }}
                        </div>
                        <div class="action-btn bg-info ms-2">
                            <div class="col-auto my-1">
                                <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center"
                                    data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                    <i class="ti ti-search text-white"></i>
                                </button>
                            </div>
                        </div>
                        <!-- <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary btn-icon-only" data-toggle="tooltip" data-title="{{ __('Apply') }}"><i class="ti ti-search"></i></button></div> -->
                        <div class="action-btn bg-danger ms-2">
                            <div class="col-auto my-1">
                                <a href="{{ route('leave.index') }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                    class="mx-3 btn btn-sm d-flex align-items-center">
                                    <i class="ti ti-trash-off text-white"></i>
                                </a>
                            </div>
                        </div>
                        <!-- <div class="col-auto"><a href="{{ route('leave.index') }}" data-toggle="tooltip" data-title="{{ __('Reset') }}" class="btn btn-sm btn-danger btn-icon-only"><i class="ti ti-trash"></i></a></div> -->
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>


    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <!-- <h5></h5> -->
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Employee') }}</th>
                                @endif
                                <th>{{ __('Leave Type') }}</th>
                                <th>{{ __('Applied On') }}</th>
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                <th>{{ __('Total Days') }}</th>
                                <th>{{ __('Leave Reason') }}</th>
                                <th>{{ __('status') }}</th>
                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                    <th class="text-right" width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            {{-- @dd($leaves); --}}
                            @foreach ($leaves as $leave)
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>{{ !empty($leave->user) ? $leave->user->name : '' }}</td>
                                    @endif
                                    <td>{{ !empty($leave->leaveType) ? $leave->leaveType->title : '' }}</td>
                                    <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
                                    @php
                                        $startDate = new \DateTime($leave->start_date);
                                        $endDate = new \DateTime($leave->end_date);
                                        $total_leave_day = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
                                        $total_leave_days = $total_leave_day + 1;
                                    @endphp
                                    <td>{{ $total_leave_days }}</td>
                                    <td>{{ $leave->leave_reason }}</td>
                                    <td>
                                        @if ($leave->status == 'Pending')
                                            <div class="badge fix_badge bg-warning p-2 px-3 rounded">
                                                {{ $leave->status }}
                                            </div>
                                        @elseif($leave->status == 'Approve')
                                            <div class="badge fix_badge bg-success p-2 px-3 rounded">
                                                {{ $leave->status }}
                                            </div>
                                        @else($leave->status=="Reject")
                                            <div class="badge fix_badge bg-danger p-2 px-3 rounded">
                                                {{ $leave->status }}
                                            </div>
                                        @endif
                                    </td>
                                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                        <div class="row ">
                                            <td class="">
                                                @if (\Auth::user()->type == 'company')
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                            data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                            data-url="{{ route('leave.action', $leave->id) }}"
                                                            data-bs-whatever="{{ __('View Leave') }}">
                                                            <span class="text-white">
                                                                <i class="ti ti-eye" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}"></i>
                                                            </span>
                                                        </a>
                                                    </div>
                                                @endif

                                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                            data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                            data-url="{{ route('leave.edit', $leave->id) }}"
                                                            data-bs-whatever="{{ __('Edit Leave') }}" data-size="lg"> <span
                                                                class="text-white"> <i class="ti ti-edit"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-original-title="{{ __('Edit') }}"></i></span>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['leave.destroy', $leave->id]]) !!}
                                                        <a href="#!"
                                                            class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Delete') }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endif
                                            </td>
                                        </div>
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
