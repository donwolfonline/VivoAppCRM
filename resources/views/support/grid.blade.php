@extends('layouts.admin')
@php
$profile=\App\Models\Utility::get_file('uploads/avatar/');
@endphp
@push('script-page')
@endpush
@section('page-title')
{{__('Support')}}
@endsection
@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Support')}}</h5>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item active" aria-current="page">{{__('Support')}}</li>
@endsection
@section('action-btn')
@if(\Auth::user()->type=='company' || \Auth::user()->type=='client' || \Auth::user()->type=='employee')
<a href="{{ route('support.index') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
    title="{{ __('List View') }}">
    <i class="ti ti-list text-white"></i>
</a>
@if(\Auth::user()->type!=='client')
<a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
    data-url="{{ route('support.create') }}" data-bs-whatever="{{__('Create New Support')}}" data-bs-toggle="tooltip"
    title="{{ __('Create New Support') }}" data-bs-original-title="{{__('Create New Support')}}"> <span
        class="text-white">
        <i class="ti ti-plus text-white"></i></span>
</a>
@endif
@endif

@endsection
@section('filter')
@endsection
@section('content')
<div class="row">
    @forelse ($supports as $support)
    <div class="col-md-3">
        <div class="card card-fluid">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-lg-5 avatar-parent-child">
                        <img alt="" width="100%" class="avatar rounded-circle me-2" @if(!empty($support->createdBy) &&
                        !empty($support->createdBy->avatar))
                        src="{{ $profile . $support->createdBy->avatar}}"
                        @else avatar="{{!empty($support->createdBy->name)?$support->createdBy->name:''}}" @endif>
                        @if($support->replyUnread()>0)
                        <span class="avatar-child avatar-badge bg-success"></span>
                        @endif
                    </div>
                    <div class="col-lg-7">
                        <a href="#!"
                            class="d-block h6 mb-0">{{!empty($support->createdBy)?$support->createdBy->name:''}}</a>
                        <small class="d-block text-muted">{{$support->subject}}</small>
                    </div>


                </div>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col text-center">
                        <span class="h6 mb-0">{{$support->ticket_code}}</span>
                        <span class="d-block text-sm">{{__('Code')}}</span>
                    </div>
                    <div class="col text-center">
                        <span class="h6 mb-0">
                            @if($support->priority == 0)
                            <span class="text-capitalize fix_badge badge bg-primary rounded-pill badge-sm">
                                {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                            @elseif($support->priority == 1)
                            <span class="text-capitalize fix_badge badge bg-info rounded-pill badge-sm">
                                {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                            @elseif($support->priority == 2)
                            <span class="text-capitalize fix_badge badge bg-warning rounded-pill badge-sm">
                                {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                            @elseif($support->priority == 3)
                            <span class="text-capitalize fix_badge badge bg-danger rounded-pill badge-sm">
                                {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                            @endif
                        </span>
                        <span class="d-block text-sm">{{__('Priority')}}</span>
                    </div>
                    <div class="col text-center">
                        <span class="h6 mb-0">
                            @if(!empty($support->attachment))
                            @php
                            $x = pathinfo($support->attachment, PATHINFO_FILENAME);
                            $extension = pathinfo($support->attachment, PATHINFO_EXTENSION);
                            $result = str_replace(array("#", "'", ";"), '', $support->receipt);

                            @endphp
                            <a href="{{ route('support.receipt' , [$x,"$extension"]) }}" data-toggle="tooltip"
                                class="btn btn-sm mb-2 btn-primary btn-icon rounded-pill">
                                <i class="ti ti-download" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Download') }}"></i>
                            </a>
                            <a href="{{asset(Storage::url('uploads/supports/'.$x.'.'.$extension))}}" target="_blank"
                                data-toggle="tooltip" class="btn btn-sm mb-2 btn-secondary ms btn-icon rounded-pill">
                                <i class="ti ti-crosshair" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Preview') }}"></i>
                            </a>
                            @else
                            -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="actions ">
                    <a href="#" data-toggle="tooltip"
                        data-title="{{__('Created Date')}}">{{\Auth::user()->dateFormat($support->created_at)}}</a>
                    <div class="action-btn bg-warning ms-2">
                        <a href="{{ route('support.reply',\Crypt::encrypt($support->id)) }}"
                            data-title="{{__('Support Reply')}}"
                            class="mx-3 btn btn-sm d-inline-flex align-items-center" data-toggle="tooltip"
                            data-original-title="{{__('Reply')}}">
                            <i class="fas fa-reply text-white"></i>
                        </a>
                    </div>
                    @if(\Auth::user()->id==$support->ticket_created)
                    <div class="action-btn bg-info ms-2">
                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                            data-bs-target="#exampleModal" data-url="{{ route('support.edit',$support->id) }}"
                            data-bs-whatever="{{__('Edit Support')}}" data-bs-toggle="tooltip"
                            title="{{ __('Edit Support') }}" data-bs-original-title="{{__('Edit Support')}}"> <span
                                class="text-white"> <i class="ti ti-edit"></i></span></a>
                    </div>

                    <div class="action-btn bg-danger ms-2">
                        <!-- <form method="POST" action="{{ route('support.destroy', $support->id) }}">
                                        @csrf
                                        <input name="_method" type="hidden" value="DELETE">
                                        <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-toggle="tooltip"
                                        title='Delete'>
                                        <span class="text-white"> <i
                                            class="ti ti-trash"></i></span>
                                        </button>
                                    </form> -->
                        {!! Form::open(['method' => 'DELETE', 'route' => ['support.destroy', $support->id]]) !!}
                        <a href="#!" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                            <i data-bs-toggle="tooltip" data-bs-original-title="{{__('Delete')}}"
                                class="ti ti-trash text-white"></i>
                        </a>
                        {!! Form::close() !!}
                    </div>

                    @endif
                </div>

            </div>
        </div>
    </div>
    @empty
    <div class="container col-md-12">
        <div class="row">
            <div class="card">
                <div class="card-body text-center">
                    <h6>{{ __('No entries found') }}</h6>
                </div>
            </div>
        </div>
    </div>
    @endforelse
</div>

@endsection