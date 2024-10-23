@extends('layouts.admin')
@push('pre-purpose-script-page')

@endpush
@section('page-title')
    {{__('Deal')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Deal')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Deal')}}</li>
@endsection
@section('action-btn')
    <a href="{{ route('deal.index') }}" class="btn btn-sm btn-primary btn-icon m-1">
        <i data-bs-toggle="tooltip" data-bs-original-title="{{ __('Kanban View') }}" class="ti ti-layout-kanban text-white"></i>
    </a>

    @if(\Auth::user()->type=='company')
    <a href="#" data-url="{{ route('deal.create') }}" data-size="lg" data-bs-toggle="modal"  data-bs-target="#exampleModal"
         class="btn btn-sm btn-primary btn-icon m-1"  data-bs-whatever="{{ __('Create New Deal') }}">
        <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
        </a>
    @endif
@endsection
@section('content')
<div class="col-lg-3 col-md-6">
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mb-3 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <div class="theme-avtar bg-warning">
                            <i class="ti ti-shopping-cart"></i>
                        </div>
                        <div class="ms-3">
                            <!-- <small class="text-muted">{{ __('Statistics') }}</small> -->
                            <h6 class="m-0">{{ __('Total Deals') }}</h6>
                            <h4 class="m-0">{{ $cnt_deal['total'] }}</h4>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-auto text-end">
                </div> -->
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mb-3 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <div class="theme-avtar bg-success">
                            <i class="ti ti-users"></i>
                        </div>
                        <div class="ms-3">
                            <!-- <small class="text-muted">{{ __('Statistics') }}</small> -->
                            <h6 class="m-0">{{__('This Month  Deals')}}</h6>
                            <h4 class="m-0">{{ $cnt_deal['this_month'] }}</h4>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-auto text-end">
                </div> -->
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-12">
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mb-3 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <div class="theme-avtar bg-danger">
                            <i class="ti ti-report-money"></i>
                        </div>
                        <div class="ms-3">
                            <!-- <small class="text-muted">{{ __('Statistics') }}</small> -->
                            <h6 class="m-0">{{__('This Week Deals')}}</h6>
                            <h4 class="m-0">{{ $cnt_deal['this_week'] }}</h4>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-auto text-end">
                </div> -->
            </div>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-12">
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mb-3 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <div class="theme-avtar bg-info">
                            <i class="ti ti-report-money"></i>
                        </div>
                        <div class="ms-3">
                            <!-- <small class="text-muted">{{ __('Statistics') }}</small> -->
                            <h6 class="m-0">{{__('Last 30 Days Deals')}}</h6>
                            <h4 class="m-0">{{ $cnt_deal['last_30days'] }}</h4>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-auto text-end">
                </div> -->
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
                                <th>{{__('Name')}}</th>
                                <th>{{__('Price')}}</th>
                                <th>{{__('Stage')}}</th>
                                <th>{{__('Tasks')}}</th>
                                <th>{{__('Users')}}</th>
                                <th class="text-end">{{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($deals) > 0)
                                @foreach ($deals as $deal)
                                    <tr>
                                        <td>{{ $deal->name }}</td>
                                        <td>{{\Auth::user()->priceFormat($deal->price)}}</td>
                                        <td>{{ !empty($deal->stage)?$deal->stage->name:'' }}</td>
                                        <td>{{count($deal->tasks)}}/{{count($deal->complete_tasks)}}</td>
                                        <td>
                                            <div class="user-group1">
                                                @foreach($deal->users as $user)
                                                    <a href="#" class="" data-bs-original-title="{{$user->name}}" data-bs-toggle="tooltip">
                                                        <img @if(!empty($user->avatar)) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else avatar="{{$user->name}}" @endif class="">
                                                    </a>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @if(\Auth::user()->type=='company' || \Auth::user()->type=='client')
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('deal.show',\Crypt::encrypt($deal->id)) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-toggle="tooltip" data-original-title="{{__('View')}}">
                                                    <i class="ti ti-eye text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}"></i>
                                                </a>
                                            </div>
                                            @endif
                                            @if(\Auth::user()->type=='company')
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" data-size="lg" data-url="{{ route('deal.edit',$deal->id) }}" data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal"  data-title="{{__('Create New Deal')}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                                    <i class="ti ti-edit text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i>
                                                </a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['deal.destroy', $deal->id]]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm m-2">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                            @endif
                                        </td>
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

