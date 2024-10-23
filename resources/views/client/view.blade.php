@extends('layouts.admin')
@php
$profile=\App\Models\Utility::get_file('uploads/avatar/');
@endphp
@push('css-page')
@endpush
@push('script-page')
@endpush
@section('page-title')
    {{__('Client Detail')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">  {{\Auth::user()->clientIdFormat($client->client_id)}} {{__('Details')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{$user->name}}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{__($user->name)}}</h5>
                </div>

                <div class="card-footer py-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Email')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                    {{$user->email}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Mobile')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                {{$client->mobile}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Address 1')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                <div class="col-6 text-right">{{$client->address_1}}</div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Address 2')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                {{$client->address_2}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('City')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                {{$client->city}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('State')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                {{$client->state}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Country')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                {{$client->country}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Zip Code')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                {{$client->zip_code}}
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- <div class="col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{$user->name}}</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Email')}}</span></dt>
                        <dd class="col-sm-9"><span class="text-md">{{$user->email}}</span></dd>
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Mobile')}}</span></dt>
                        <dd class="col-sm-9"><span class="text-md">{{$client->mobile}}</span></dd>
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Address 1')}} </span></dt>
                        <dd class="col-sm-9"><span class="text-md">{{$client->address_1}}</span></dd>
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Address 2')}} </span></dt>
                        <dd class="col-sm-9"><span class="text-md">{{$client->address_2}}</span></dd>
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('City')}}</span></dt>
                        <dd class="col-sm-9"><span class="text-md">{{$client->city}}</span></dd>
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('State')}}</span></dt>
                        <dd class="col-sm-9"><span class="text-md">{{$client->state}}</span></dd>
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Country')}}</span></dt>
                        <dd class="col-sm-9"><span class="text-md">{{$client->country}}</span></dd>
                        <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{__('Zip Code')}}</span></dt>
                        <dd class="col-sm-9"><span class="text-md">{{$client->zip_code}}</span></dd>
                    </dl>
                </div>
            </div>
        </div> -->
        <div class="col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{__('Company Detail')}}</h5>
                </div>

                <div class="card-footer py-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('ID')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                    {{\Auth::user()->clientIdFormat($client->client_id)}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Company Name')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                    {{$client->company_name}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Web Site')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{$client->website}}" target="_blank">{{$client->website}}</a>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Tax Number')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                    {{$client->tax_number}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Note')}}</span>
                                </div>
                                <div class="col-6 text-right">
                                    {{$client->notes}}
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

