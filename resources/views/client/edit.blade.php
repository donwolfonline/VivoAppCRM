@extends('layouts.admin')
@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
@endphp
@push('css-page')
@endpush
@push('script-page')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
@endpush
@section('page-title')
    {{ __('Client Edit') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0"> {{ \Auth::user()->clientIdFormat($client->client_id) }}
            {{ __('Edit') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('client.index') }}">{{ __('Client') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#useradd-1"
                                class="list-group-item list-group-item-action border-0">{{ __('Personal Info') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-2"
                                class="list-group-item list-group-item-action border-0">{{ __('Company Info') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div id="useradd-1" class="card">
                        {{ Form::model($client, ['route' => ['client.personal.update', $client->user_id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        <div class="card-header">
                            <h5>{{ __('Personal Info') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your personal information') }}</small>
                        </div>

                        <div class="card-body">
                            <form>
                                <div class="row mt-3">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                            {{ Form::text('name', $user->name, ['class' => 'form-control font-style', 'placeholder' => 'Enter Name']) }}
                                            @error('name')
                                                <span class="invalid-name" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('mobile', __('Mobile'), ['class' => 'form-label']) }}
                                            {{ Form::text('mobile', $client->mobile, ['class' => 'form-control', 'placeholder' => 'Enter Mobile']) }}
                                            @error('mobile')
                                                <span class="invalid-mobile" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('address_1', __('Address 1'), ['class' => 'form-label']) }}
                                            {{ Form::textarea('address_1', $client->address_1, ['class' => 'form-control', 'rows' => '4', 'placeholder' => 'Enter Address']) }}
                                            @error('address_1')
                                                <span class="invalid-address_1" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('address_2', __('Address 2'), ['class' => 'form-label']) }}
                                            {{ Form::textarea('address_2', $client->address_2, ['class' => 'form-control', 'rows' => '4', 'placeholder' => 'Enter Address']) }}
                                            @error('address_2')
                                                <span class="invalid-address_2" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group ">
                                            {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                            {{ Form::text('city', $client->city, ['class' => 'form-control', 'placeholder' => 'Enter City']) }}
                                            @error('city')
                                                <span class="invalid-city" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                                        {{ Form::text('state', $client->state, ['class' => 'form-control', 'placeholder' => 'Enter State']) }}
                                        @error('state')
                                            <span class="invalid-state" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                                        {{ Form::text('country', $client->country, ['class' => 'form-control', 'placeholder' => 'Enter Country']) }}
                                        @error('country')
                                            <span class="invalid-country" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        {{ Form::label('zip_code', __('Zip Code'), ['class' => 'form-label']) }}
                                        {{ Form::text('zip_code', $client->zip_code, ['class' => 'form-control', 'placeholder' => 'Enter Zip Code']) }}
                                        @error('zip_code')
                                            <span class="invalid-zip_code" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        {{ Form::label('zip_code', __('Avatar'), ['class' => 'form-label']) }}
                                        <div class="card bg-gradient-primary hover-shadow-lg border-0">
                                            <div class="card-body py-3">
                                                <div class="row row-grid align-items-center">
                                                    <div class="col-lg-8">
                                                        <div class="media align-items-center">
                                                            <a href="#" class="avatar avatar-lg rounded-circle mr-3">
                                                                <img @if (!empty($user->avatar)) src="{{ $profile . '/' . $user->avatar }}" @else avatar="{{ $user->name }}" @endif
                                                                    class="avatar  rounded-circle avatar-lg">
                                                            </a>
                                                            <div class="media-body ms-3">
                                                                <h5 class="text-dark mb-2">{{ $user->name }}</h5>
                                                                <div>
                                                                    <div class="input-group">
                                                                        <input type="file" class="form-control"
                                                                            id="file-1" name="profile"
                                                                            aria-describedby="inputGroupFileAddon04"
                                                                            aria-label="Upload"
                                                                            data-multiple-caption="{count} files selected"
                                                                            multiple />
                                                                    </div>


                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        {{ Form::submit(__('Update'), ['class' => 'btn btn-primary d-flex align-items-center']) }}
                                    </div>


                                </div>
                            </form>
                        </div>
                        {{ Form::close() }}
                    </div>
                    <div id="useradd-2" class="card">
                        {{ Form::model($client, ['route' => ['client.update.company', $client->user_id], 'method' => 'post']) }}
                        <div class="card-header">
                            <h5>{{ __('Company Info') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your company information') }}</small>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row mt-3">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            {!! Form::label('clt_id', __('Client ID'), ['class' => 'form-label']) !!}
                                            {!! Form::text('clt_id', \Auth::user()->clientIdFormat($client->client_id), [
                                                'class' => 'form-control',
                                                'readonly',
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('company_name', __('Company Name'), ['class' => 'form-label']) }}
                                            {{ Form::text('company_name', $client->company_name, ['class' => 'form-control', 'placeholder' => 'Enter  Company Name']) }}
                                            @error('company_name')
                                                <span class="invalid-company_name" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('website', __('Website'), ['class' => 'form-label']) }}
                                            {{ Form::text('website', $client->website, ['class' => 'form-control', 'placeholder' => 'Enter Website']) }}
                                            @error('website')
                                                <span class="invalid-website" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('tax_number', __('Tax Number'), ['class' => 'form-label']) }}
                                            {{ Form::text('tax_number', $client->tax_number, ['class' => 'form-control', 'placeholder' => 'Enter Tax Number']) }}
                                            @error('tax_number')
                                                <span class="invalid-tax_number" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong> 
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                                            {{ Form::textarea('notes', $client->notes, ['class' => 'form-control', 'placeholder' => 'Enter Notes', 'rows' => '3']) }}
                                            @error('notes')
                                                <span class="invalid-notes" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        {{ Form::submit(__('Update'), ['class' => 'btn btn-primary d-flex align-items-center']) }}
                                    </div>
                                </div>
                            </form>
                        </div>
                        {{ Form::close() }}
                    </div>

                </div>
            </div>
            <!-- [ sample-page ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
@endsection
