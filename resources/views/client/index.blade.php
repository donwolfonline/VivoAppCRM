@extends('layouts.admin')
@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    // $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('script-page')
@endpush
@section('page-title')
    {{ __('Client') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Client') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Client') }}</li>
@endsection
@section('action-btn')
    @if (\Auth::user()->type == 'company')
        <a href="{{ route('client.clientlog') }}" class="btn btn-primary btn-sm {{ Request::segment(1) == 'user' }}"
            data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Client Logs History') }}"><i
                class="ti ti-user-check"></i>
        </a>
    @endif
    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
        data-url="{{ route('client.file.import') }}" data-bs-whatever="{{ __('Import CSV file') }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Import item CSV file') }}"> <span class="text-white">
            <i class="ti ti-file-import" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Import') }}"></i> </a>

    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
        data-url="{{ route('client.create') }}" data-bs-whatever="{{ __('Create New Client') }}"> <i
            class="ti ti-plus text-white" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Create') }}"></i></span></a>
@endsection
@section('content')
    <div class="row">
        @foreach ($clients as $client)
            <div class="col-md-3 col-sm-4">
                <div class="card hover-shadow-lg">
                    <div class="card-header border-0 pb-0 pt-2 px-3">
                        <div class="row">
                            <div class="col-6  text-right">
                                <span
                                    class="badge bg-primary p-2 px-3 rounded">{{ \Auth::user()->clientIdFormat(!empty($client->clientDetail) ? $client->clientDetail->client_id : '') }}</span>
                            </div>
                            <div class="col-6  text-end">
                                <div class="actions">
                                    @if ($client->is_active == 1)
                                        <div class="dropdown action-item">
                                            <a href="#" class="action-item " data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i></a>
                                            <div class="dropdown-menu dropdown-menu-right">

                                                <a href="{{ route('client.edit', $client->id) }}" class="dropdown-item"
                                                    data-bs-whatever="{{ __('Edit Client') }}">
                                                    <i class="ti ti-edit"> </i> {{ __('Edit') }}</a>


                                                <a href="{{ route('client.show', \Crypt::encrypt($client->id)) }}"
                                                    class="dropdown-item" data-bs-whatever="{{ __('View Client') }}">
                                                    <i class="ti ti-eye"></i> {{ __('View') }}</a>


                                                <a href="#"
                                                    data-url="{{ route('client.reset', \Crypt::encrypt($client->id)) }}"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                    class="dropdown-item" data-bs-whatever="{{ __('Reset Password') }}">
                                                    <i class="ti ti-lock"> </i> {{ __('Reset Password') }}
                                                </a>
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['client.destroy', $client->id]]) !!}
                                                <a href="#!" class=" show_confirm dropdown-item">
                                                    <i class="ti ti-trash"></i>{{ __('Delete') }}
                                                </a>
                                                {!! Form::close() !!}
                                                @if ($client->is_enable_login == 1)
                                                    <a href="{{ route('client.login', \Crypt::encrypt($client->id)) }}"
                                                        class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-danger"> {{ __('Login Disable') }}</span>
                                                    </a>
                                                @elseif ($client->is_enable_login == 0 && $client->password == null)
                                                    <a href="#"
                                                        data-url="{{ route('client.reset', \Crypt::encrypt($client->id)) }}"
                                                        data-ajax-popup="true" data-size="md"
                                                        class="dropdown-item login_enable"
                                                        data-title="{{ __('New Password') }}" class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-success"> {{ __('Login Enable') }}</span>
                                                    </a>
                                                @else
                                                    <a href="{{ route('client.login', \Crypt::encrypt($client->id)) }}"
                                                        class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-success"> {{ __('Login Enable') }}</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="actions d-flex justify-content-between px-4">
                                            <a href="#" data-toggle="tooltip"
                                                data-original-title="{{ __('Lock') }}"
                                                class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                <i class="fas fa-lock"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body user-card text-center client-box">
                        <div class="avatar-parent-child">
                            <img @if (!empty($client->avatar)) src="{{ $profile . '/' . $client->avatar }}" @else avatar="{{ $client->name }}" @endif
                                class="avatar  rounded-circle avatar-lg" style="width:80px;">

                        </div>
                        <h5 class="h6 mt-4 mb-0">{{ $client->name }}</h5>
                        <a href="#" class=" text-sm text-muted">{{ $client->email }}</a>

                    </div>

                    @if ($client->lastlogin)
                        <div class="row justify-content-between align-items-center mt-2">
                            <div class="col text-center">
                                <span class="d-block h6 mb-2" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Last Login') }}">{{ $client->lastlogin }}</span>

                            </div>
                        </div>
                    @else
                        <div class="row justify-content-between align-items-center mt-4">
                            <div class="col text-center">
                                <span class="d-block h6 mb-2" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Last Login') }}">{{ $client->lastlogin }}</span>

                            </div>
                        </div>
                    @endif


                </div>
            </div>
        @endforeach

        <div class="col-xl-3 col-lg-4 col-sm-6">
            <a href="#" class="btn-addnew-project " data-bs-toggle="modal" data-bs-target="#exampleModal"
                data-url="{{ route('client.create') }}" data-size="lg"
                data-bs-whatever="{{ __('Create New Client') }}">
                <div class="bg-primary proj-add-icon">
                    <i class="ti ti-plus"></i>
                </div>
                <h6 class="mt-4 mb-2">{{ __('New Client') }}</h6>
                <p class="text-muted text-center">{{ __('Click here to add new client') }}</p>
            </a>
        </div>
    </div>
@endsection
@push('script-page')
    <script>
        $(document).on('change', '#password_switch', function() {
            if ($(this).is(':checked')) {
                $('.ps_div').removeClass('d-none');
                $('#password').attr("required", true);

            } else {
                $('.ps_div').addClass('d-none');
                $('#password').val(null);
                $('#password').removeAttr("required");
            }
        });
        $(document).on('click', '.login_enable', function() {
            setTimeout(function() {
                $('.modal-body').append($('<input>', {
                    type: 'hidden',
                    val: 'true',
                    name: 'login_enable'
                }));
            }, 2000);
        });
    </script>
@endpush
