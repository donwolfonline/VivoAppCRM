@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{ __('Lead') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Lead') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Lead') }}</li>
@endsection
@section('action-btn')
    <a href="{{ route('lead.index') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Kanban View') }}">
        <i class="ti ti-layout-kanban text-white"></i>
    </a>
    @if (\Auth::user()->type == 'company')
        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
            data-url="{{ route('lead.create') }}" data-bs-whatever="{{ __('Create New Lead') }}"
            data-bs-original-title="{{ __('Create New Lead') }}">
            <i data-bs-toggle="tooltip" title="{{ __('Create') }}" class="ti ti-plus text-white"></i>
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
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Subject') }}</th>
                                <th>{{ __('Stage') }}</th>
                                <th>{{ __('Users') }}</th>
                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                    <th class="text-right">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($leads) > 0)
                                @foreach ($leads as $lead)
                                    <tr>
                                        <td>{{ $lead->name }}</td>
                                        <td>{{ $lead->subject }}</td>
                                        <td>{{ !empty($lead->stage) ? $lead->stage->name : '' }}</td>
                                        <td>
                                            <div class="user-group">
                                                @foreach ($lead->users as $user)
                                                    <img @if (!empty($user->avatar)) src="{{ asset('/storage/uploads/avatar/' . $user->avatar) }}" @else avatar="{{ $user->name }}" @endif
                                                        class="" data-bs-toggle="tooltip"
                                                        title="{{ $user->name }}">
                                                @endforeach
                                            </div>
                                        </td>
                                        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                            <td class="text-right">
                                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="{{ route('lead.show', \Crypt::encrypt($lead->id)) }}"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                            data-url="#" data-bs-whatever="{{ __('View Lead') }}"
                                                            data-bs-toggle="tooltip" title="View Lead"
                                                            data-bs-original-title="{{ __('View Lead') }}"> <span
                                                                class="text-white"> <i class="ti ti-eye"></i></span></a>
                                                    </div>
                                                @endif
                                                @if (\Auth::user()->type == 'company')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                            data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                            data-url="{{ route('lead.edit', $lead->id) }}"
                                                            data-bs-whatever="{{ __('Edit Lead') }}"
                                                            data-bs-toggle="tooltip" title="Edit Lead"
                                                            data-bs-original-title="{{ __('Edit Lead') }}" data-size="lg">
                                                            <span class="text-white"> <i class="ti ti-edit"></i></span></a>
                                                    </div>

                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['lead.destroy', $lead->id],
                                                            'id' => 'delete-form-' . $lead->id,
                                                        ]) !!}
                                                        <a href="#!"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm m-2">
                                                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Delete') }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr class="font-style">
                                    <td colspan="6" class="text-center">{{ __('No data available in table') }}</td>
                                </tr>
                            @endif


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


@endsection
