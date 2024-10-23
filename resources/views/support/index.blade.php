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
        <h5 class="h4 d-inline-block font-weight-400 mb-1 ">{{__('Support')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Support')}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company' || \Auth::user()->type=='client' || \Auth::user()->type=='employee')

    <a href="{{ route('support.grid') }}" class="btn btn-sm btn-primary btn-icon m-1">
        <i class="ti ti-layout-grid text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Grid View') }}"></i>
    </a>
@if(\Auth::user()->type!=='client')
    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
        data-bs-target="#exampleModal" data-url="{{ route('support.create') }}" data-size="lg"
        data-bs-whatever="{{__('Create New Support')}}"> <span class="text-white"> 
            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
        </a>
@endif
    @endif
@endsection
@section('filter')

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
                                <th scope="col">{{__('Created By')}}</th>
                                <th scope="col">{{__('Ticket')}}</th>
                                <th scope="col">{{__('Code')}}</th>
                                <th scope="col">{{__('Attachment')}}</th>
                                <th scope="col">{{__('Created At')}}</th>
                                <th scope="col" class="text-right">{{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supports as $support)
                            <tr>
                                <th scope="row">
                                    <div class="media align-items-center">
                                        <div>
                                            <div class="user-group1">
                                                <img alt="" class="avatar rounded-circle me-2" 
                                                @if(!empty($support->createdBy) && !empty($support->createdBy->avatar)) 
                                                src="{{(\App\Models\Utility::get_file('uploads/avatar')).'/'.$support->createdBy->avatar}}" 
                                                @else  avatar="{{!empty($support->createdBy->name)?$support->createdBy->name:''}}" @endif>
                                                @if($support->replyUnread()>0)
                                                    <span class="avatar-child avatar-badge bg-success"></span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="media-body ml-4">
                                            {{!empty($support->createdBy)?$support->createdBy->name:''}}
                                        </div>
                                    </div>
                                </th>
                                <th scope="row">
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <a href="{{ route('support.reply',\Crypt::encrypt($support->id)) }}" class="name h6 mb-0 text-sm">{{$support->subject}}</a><br>
                                            @if($support->priority == 0)
                                                <span data-bs-toggle="tooltip" data-bs-original-title="{{__('Priority')}}" class="text-capitalize fix_badge badge bg-primary p-2 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                            @elseif($support->priority == 1)
                                                <span data-bs-toggle="tooltip" data-bs-original-title="{{__('Priority')}}" class="text-capitalize fix_badge badge bg-info p-2 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                            @elseif($support->priority == 2)
                                                <span data-bs-toggle="tooltip" data-bs-original-title="{{__('Priority')}}" class="text-capitalize fix_badge badge bg-warning p-2 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                            @elseif($support->priority == 3)
                                                <span data-bs-toggle="tooltip" data-bs-original-title="{{__('Priority')}}" class="text-capitalize fix_badge badge bg-danger p-2 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </th>
                                <td>{{$support->ticket_code}}</td>
                                <td>
                                    @if(!empty($support->attachment))
                                    @php
                                        $x = pathinfo($support->attachment, PATHINFO_FILENAME);
                                        $extension = pathinfo($support->attachment, PATHINFO_EXTENSION);
                                        $result = str_replace(array("#", "'", ";"), '', $support->receipt);
                                        
                                    @endphp
                                        @php
                                        $supports=\App\Models\Utility::get_file('uploads/supports/');
                                        @endphp
                                
                                    <a  href="{{ route('support.receipt' , [$x,"$extension"]) }}"  data-toggle="tooltip" class="btn btn-sm btn-primary btn-icon rounded-pill">
                                        <i class="ti ti-download" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Download') }}"></i>
                                    </a>

                                    <a  href="{{$supports.$x.'.'.$extension}}" target="_blank"  data-toggle="tooltip" class="btn btn-sm btn-secondary btn-icon rounded-pill">
                                        <i class="ti ti-crosshair" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i>
                                    </a>
                                    @else
                                        -
                                    @endif
                                </td>
        
                                <td>{{\Auth::user()->dateFormat($support->created_at)}}</td>
        
        
                                <td class="text-right">
                                    <div class="actions ml-3">
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ route('support.reply',\Crypt::encrypt($support->id)) }}" data-title="{{__('Support Reply')}}"
                                                 class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-original-title="{{__('Reply')}}">
                                                <i class="fas fa-reply text-white"></i>
                                            </a>
                                        </div>
                                        @if(\Auth::user()->id==$support->ticket_created)
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                                data-bs-target="#exampleModal" data-url="{{ route('support.edit',$support->id) }}" data-size="lg"
                                                data-bs-whatever="{{__('Edit Support')}}"> <span class="text-white"> <i
                                                        class="ti ti-edit" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['support.destroy', $support->id]]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm align-items-center show_confirm ">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

