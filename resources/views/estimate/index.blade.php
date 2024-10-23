@extends('layouts.admin')
@php
$profile=\App\Models\Utility::get_file('uploads/avatar/');
// $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('script-page')
@endpush
@section('page-title')
{{__('Estimation')}}
@endsection
@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Estimation')}}</h5>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item active" aria-current="page">{{__('Estimation')}}</li>
@endsection
@section('action-btn')
@if(\Auth::user()->type=='company')
<a href="{{ route('estimate.create') }}" class="btn btn-sm btn-primary btn-icon m-1"
    data-bs-whatever="{{__('Create New Estimate')}}">
    <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
</a>
@endif
@endsection

@section('content')
<div class="col-12">
    <div class=" {{isset($_GET['start_date'])?'show':''}}">
        <div class="card card-body">
            {{ Form::open(array('url' => 'estimate','method'=>'get')) }}
            <div class="row filter-css">
                <div class="col-md-2">
                    <select class="form-control" name="status" data-toggle="select">
                        <option value="">{{__('Select Status')}}</option>
                        @foreach($status as $k=>$val)
                        <option value="{{$k}}" {{(isset($_GET['start_date']) && $_GET['status']==$k)?'selected':''}}>
                            {{$val}} </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    {{Form::date('start_date',isset($_GET['start_date'])?$_GET['start_date']: new \DateTime() ,array('class'=>'form-control'))}}
                </div>
                <div class="col-auto">
                    {{Form::date('end_date',isset($_GET['end_date'])?$_GET['end_date']: new \DateTime() ,array('class'=>'form-control'))}}
                </div>
                <div class="action-btn bg-info ms-2">
                    <div class="col-auto">
                        <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-toggle="tooltip"
                            data-title="{{__('Apply')}}"><i data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('Apply') }}" class="ti ti-search text-white"></i></button>
                    </div>
                </div>
                <div class="action-btn bg-danger ms-2">
                    <div class="col-auto">
                        <a href="{{route('estimate.index')}}" data-toggle="tooltip" data-title="{{__('Reset')}}"
                            class="mx-3 btn btn-sm d-flex align-items-center"><i data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('reset') }}" class="ti ti-trash-off text-white"></i></a>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

<div class="row">
    @foreach($estimates as $estimate)
    <div class="col-lg-4">
        <div class="card hover-shadow-lg">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col-10">
                        <h6 class="mb-0">
                            <a
                                href="{{ route('estimate.show',\Crypt::encrypt($estimate->id)) }}">{{\Auth::user()->estimateNumberFormat($estimate->estimate)}}</a>
                        </h6>
                    </div>
                    <div class="col-2 text-right">
                        <div class="actions">
                            <div class="dropdown">
                                <a href="#" class="action-item" data-bs-toggle="dropdown"><i
                                        class="ti ti-dots-vertical"></i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @if(\Auth::user()->type=='company')
                                    <a href="{{ route('estimate.edit',\Crypt::encrypt($estimate->id)) }}"
                                        class="dropdown-item">
                                        <i class="ti ti-edit"></i> {{__('Edit')}}
                                    </a>

                                    {!! Form::open(['method' => 'DELETE', 'route' => ['estimate.destroy',
                                    $estimate->id]]) !!}
                                    <a href="#!" class="dropdown-item show_confirm ">
                                        <i class="ti ti-trash"></i> {{__('Delete')}}
                                    </a>
                                    {!! Form::close() !!}


                                    @endif
                                    @if(\Auth::user()->type=='company' || \Auth::user()->type=='client')
                                    <a href="{{ route('estimate.show',\Crypt::encrypt($estimate->id)) }}"
                                        class="dropdown-item">
                                        <i class="ti ti-eye"></i> {{__('View')}}
                                    </a>
                                    @endif
                                    @if(\Auth::user()->type=='company')
                                    @if($estimate->is_convert==0)
                                    <a href="{{ route('estimate.convert',$estimate->id) }}" class="dropdown-item">
                                        <i class="ti ti-refresh"></i> {{__('Convert to Invoice')}}
                                    </a>
                                    @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="p-3 border border-dashed">

                    @if($estimate->status == 0)
                    <span
                        class="badge bg-primary p-2 px-3 rounded ">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                    @elseif($estimate->status == 1)
                    <span
                        class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                    @elseif($estimate->status == 2)
                    <span
                        class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                    @elseif($estimate->status == 3)
                    <span
                        class="badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                    @elseif($estimate->status == 4)
                    <span
                        class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Estimate::$statues[$estimate->status]) }}</span>
                    @endif

                    <div class="row align-items-center mt-3">
                        <div class="col-6">
                            <h6 class="mb-0">{{\Auth::user()->dateFormat($estimate->issue_date)}}</h6>
                            <span class="text-sm text-muted">{{__('Issue Date')}}</span>
                        </div>
                        <div class="col-6">
                            <h6 class="mb-0">{{\Auth::user()->dateFormat($estimate->expiry_date)}}</h6>
                            <span class="text-sm text-muted">{{__('Expiry Date')}}</span>
                        </div>
                    </div>
                </div>
                @if(\Auth::user()->type != 'client')
                @php $client=$estimate->clients @endphp
                <div class="media mt-4 align-items-center">
                    <img @if(!empty($client->avatar)) src="{{$profile.'/'.$client->avatar}}" @else
                    avatar="{{$estimate->clients->name}}" @endif class="avatar rounded-circle avatar-custom"
                    data-toggle="tooltip" data-original-title="{{__('Client')}}">
                    <div class="media-body ps-3">
                        <div class="text-sm my-0">{{!empty($estimate->clients)?$estimate->clients->name:''}}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@if(count($estimates) <= 0) <div class="container col-md-12">
    <div class="row">
        <div class="card">
            <div class="card-body text-center">
                <h6>{{ __('No entries found') }}</h6>
            </div>
        </div>
    </div>
    </div>
    @endif
    @endsection