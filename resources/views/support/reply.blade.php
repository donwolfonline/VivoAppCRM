@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
{{__('Support Reply')}}
@endsection
@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-1">{{__('Support Reply')}}</h5>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item"><a href="{{route('support.index')}}">{{__('Support')}}</a></li>
<li class="breadcrumb-item active" aria-current="page">{{__('Support Reply')}}</li>
@endsection
@section('action-btn')
@endsection
@section('filter')
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="row gy-4">
            <div class="col-lg-6">
                <div class="row">
                    <h5 class="mb-3">{{__('Reply Ticket')}} - <span
                            class="text-success">{{$support->ticket_code}}</span></h5>
                    <div class="card border">
                        <div class="card-body p-0">
                            <div class="p-4 border-bottom">
                                @if($support->priority == 0)
                                <span class="badge bg-primary mb-2">
                                    {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                @elseif($support->priority == 1)
                                <span class="badge bg-info mb-2">
                                    {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                @elseif($support->priority == 2)
                                <span class="badge bg-warning mb-2">
                                    {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                @elseif($support->priority == 3)
                                <span class="badge bg-danger mb-2">
                                    {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                @endif
                                <div class="d-flex justify-content-between align-items-center ">
                                    <h5>{{$support->subject}}</h5>
                                    @if($support->status == 'Open')
                                    <span class="badge bg-light-primary p-2 f-w-600 text-primary rounded">
                                        {{__('Open')}}</span>
                                    @elseif($support->status == 'Close')
                                    <span class="badge bg-light-danger p-2 f-w-600 text-danger rounded">
                                        {{ __('Closed') }}</span>
                                    @elseif($support->status == 'On Hold')
                                    <span class="badge bg-light-warning p-2 f-w-600 text-warning rounded">
                                        {{ __('On Hold') }}</span>
                                    @endif
                                </div>
                                <p class="mb-0">
                                    <b> {{!empty($support->createdBy)?$support->createdBy->name:''}}</b>
                                    .
                                    <span> {{!empty($support->createdBy)?$support->createdBy->email:''}}</span>
                                    .
                                    <span class="text-muted">{{\Auth::user()->dateFormat($support->created_at)}}</span>
                                </p>
                            </div>
                            @if(!empty($support->description))
                            <div class="p-4">
                                <p class="">{{$support->description}}</p>
                                @if(!empty($support->attachment))
                                <h6>{{__('Attachments')}} :</h6>
                                <a href="{{asset(Storage::url('uploads/supports')).'/'.$support->attachment}}"
                                    download="" class="bg-secondary d-inline-flex p-2 rounded text-white "
                                    target="_blank">
                                    <i class="ti ti-download text-white me-2 mt-1" data-bs-toggle="tooltip"></i>

                                    {{ $support->attachment }}
                                </a>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($support->status == 'Open')
                <div class="row">
                    <div class="card">
                        @php
                        $plansettings = App\Models\Utility::plansettings();
                        @endphp
                        @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
                        <div class="text-end">

                            <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="{{ __('Generate') }}"
                                data-url="{{ route('generate',['reply']) }}" data-title="{{ __('Generate') }}"
                                float-end>
                                <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">
                                        {{ __('Generate With AI') }}</span></i>
                            </a>
                        </div>
                        @endif

                        <div class="card-body">
                            <h5 class="mb-3">{{__('Comments')}}</h5>
                            {{ Form::open(array('route' => array('support.reply.answer',$support->id))) }}
                            <textarea class="form-control form-control-light mb-2" name="description"
                                placeholder="Your comment" id="example-textarea" rows="3" required=""></textarea>
                            <div class="text-end">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success w-100"> <i
                                            class="ti ti-circle-plus me-1 mb-0"></i> {{__('Send')}}</button>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-6">
                <h5 class="mb-3">{{__('Replies')}}</h5>
                @foreach($replyes as $reply)
                <div class="card border">
                    <div class="card-header row d-flex align-items-center justify-content-between">
                        <div class="header-right col d-flex align-items-start">
                            <a href="#" class="avatar avatar-sm me-3">
                                <img alt="" class="img-fluid" @if(!empty($reply->users) && !empty($reply->users->avatar))
                                src="{{asset(Storage::url('uploads/avatar/')).'/'.$reply->users->avatar}}" @else
                                src="{{asset(Storage::url('uploads/avatar/')).'/avatar.png'}}" @endif>
                            </a>
                            <h6 class="mb-0">{{!empty($reply->users)?$reply->users->name:''}}
                                <div class="d-block text-muted">{{!empty($reply->users)?$reply->users->email:''}}</div>
                            </h6>
                        </div>
                        <p class="col-auto ms-1 mb-0"> <span
                                class="text-muted">{{$reply->created_at->diffForHumans()}}</span></p>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{$reply->description}}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection