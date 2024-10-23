@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{ __('Contract') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{ __('Contract') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Contract') }}</li>
@endsection

@section('action-btn')
    <a href="{{ route('contract.grid') }}" class="btn btn-sm btn-primary btn-icon m-1">
        <i class="ti ti-layout-grid text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Grid View') }}"> </i>
    </a>

    @if (\Auth::user()->type == 'company')
        <a href="#" class="btn btn-sm btn-primary btn-icon m-1 getclienee" data-bs-toggle="modal"
            data-bs-target="#exampleModal" data-url="{{ route('contract.create') }}" data-size="lg"
            data-bs-whatever="{{ __('Create New Contract') }}"> <span class="text-white">
                <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Create') }}"></i></span>
        </a>
    @endif
@endsection

@section('filter')
@endsection

@section('content')
    <div class="col-xl-3 col-6">
        <div class="card comp-card">
            <div class="card-body" style="min-height: 143px;">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-b-20">{{ __('Total Contracts') }}</h6>
                        <h3 class="text-primary">{{ $cnt_contract['total'] }}</h3>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-handshake bg-success text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-6">
        <div class="card comp-card">
            <div class="card-body" style="min-height: 143px;">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-b-20">{{ __('This Month Total Contracts') }}</h6>
                        <h3 class="text-info">{{ $cnt_contract['this_month'] }}</h3>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-handshake bg-info text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-6">
        <div class="card comp-card">
            <div class="card-body" style="min-height: 143px;">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-b-20">{{ __('This Week Total Contracts') }}</h6>
                        <h3 class="text-warning">{{ $cnt_contract['this_week'] }}</h3>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-handshake bg-warning text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-6">
        <div class="card comp-card">
            <div class="card-body" style="min-height: 143px;">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-b-20">{{ __('Last 30 Days Total Contracts') }}</h6>
                        <h3 class="text-danger">{{ $cnt_contract['last_30days'] }}</h3>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-handshake bg-danger text-white"></i>
                    </div>
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
                                <th scope="col">{{ __('#') }}</th>
                                <th scope="col">{{ __('Subject') }}</th>
                                <th scope="col">{{ __('Project') }}</th>
                                @if (\Auth::user()->type != 'client')
                                    <th scope="col">{{ __('Client') }}</th>
                                @endif
                                <th scope="col">{{ __('Type') }}</th>
                                <th scope="col">{{ __('Value') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                                <th scope="col">{{ __('Start Date') }}</th>
                                <th scope="col">{{ __('End Date') }}</th>
                                <th scope="col" class="text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contracts as $contract)
                                <tr class="font-style">
                                    <td>
                                        <a href="{{ route('contract.show', $contract->id) }}"
                                            class="btn btn-outline-primary">{{ \Auth::user()->contractNumberFormat($contract->id) }}</a>
                                    </td>
                                    <td>{{ $contract->subject }}</td>
                                    <td>{{ !empty($contract->projects) ? $contract->projects->title : '' }}</td>
                                    @if (\Auth::user()->type != 'client')
                                        <td>{{ !empty($contract->clients) ? $contract->clients->name : '' }}</td>
                                    @endif
                                    <td>{{ !empty($contract->types) ? $contract->types->name : '' }}</td>
                                    <td>{{ \Auth::user()->priceFormat($contract->value) }}</td>
                                    <!-- <td>
                                                    @if ($contract->status == 'Start')
    <div class="badge bg-primary p-2 px-3 rounded status-badde3">{{ __('Start') }}</div>
@elseif($contract->status == 'Close')
    <div class="badge bg-danger p-2 px-3 rounded status-badde3">{{ __('Close') }}</div>
    @endif
                                                </td> -->
                                    <td>
                                        @if ($contract->status == 'accept')
                                            <span
                                                class="status_badge badge bg-primary  p-2 px-3 rounded">{{ __('Accept') }}</span>
                                        @elseif($contract->status == 'decline')
                                            <span
                                                class="status_badge badge bg-danger p-2 px-3 rounded">{{ __('Decline') }}</span>
                                        @elseif($contract->status == 'pending')
                                            <span
                                                class="status_badge badge bg-warning p-2 px-3 rounded">{{ __('Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ \Auth::user()->dateFormat($contract->start_date) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($contract->end_date) }}</td>




                                    <td class="action text-right">
                                        @if (\Auth::user()->type == 'company')
                                            @if ($contract->status == 'accept')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#"
                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        data-bs-toggle="modal" data-bs-target="#exampleModal" data-size="lg"
                                                        data-url="{{ route('contract.copy', $contract->id) }}"
                                                        data-bs-whatever="{{ __('Duplicate') }}"> <span class="text-white">
                                                            <i class="ti ti-copy text-white" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Duplicate') }}"></i></span></a>
                                                </div>
                                            @endif
                                        @endif
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ route('contract.show', $contract->id) }}"
                                                class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}">
                                                <span class="text-white"> <i class="ti ti-eye"></i></span></a>
                                        </div>
                                        @if (\Auth::user()->type == 'company')
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal" data-size="lg"
                                                    data-url="{{ route('contract.edit', $contract->id) }}"
                                                    data-bs-whatever="{{ __('Edit Contract') }}"> <span
                                                        class="text-white"> <i class="ti ti-edit"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">

                                                {!! Form::open(['method' => 'DELETE', 'route' => ['contract.destroy', $contract->id]]) !!}
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
