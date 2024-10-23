@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Coupon Detail')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Coupon')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('coupon.index')}}">{{__('Coupon')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{$coupon->name}}</li>
@endsection
@section('action-btn')

@endsection
@section('content')
    
    <div class="col-md-12">
    <div class="card">
        <div class="card-body table-border-style">
            <h4 class="my-2">{{$coupon->code}}</h4>
            <div class="table-responsive">
                <table class="table" id="pc-dt-simple">
                    <thead>
                        <tr>
                            <th scope="col">{{__('User')}}</th>
                            <th scope="col">{{__('Date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($userCoupons as $userCoupon)
                            <tr>

                                <td class="budget">{{ !empty($userCoupon->userDetail)?$userCoupon->userDetail->name:'' }} </td>
                                <td>{{ $userCoupon->created_at }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

