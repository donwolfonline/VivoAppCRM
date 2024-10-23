@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{ __('Order') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Order') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Order') }}</li>
@endsection
@section('action-btn')
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
                                <th scope="col" class="sort" data-sort="name"> {{ __('Order Id') }}</th>
                                <th scope="col" class="sort" data-sort="budget">{{ __('Date') }}</th>
                                <th scope="col" class="sort" data-sort="status">{{ __('Name') }}</th>
                                <th scope="col">{{ __('Plan Name') }}</th>
                                <th scope="col" class="sort" data-sort="completion"> {{ __('Price') }}</th>
                                <th scope="col" class="sort" data-sort="completion"> {{ __('Payment Type') }}</th>
                                <th scope="col" class="sort" data-sort="completion"> {{ __('Status') }}</th>
                                <th scope="col" class="sort" data-sort="completion"> {{ __('Coupon') }}</th>
                                <th scope="col" class="sort" data-sort="completion"> {{ __('Invoice') }}</th>
                                @if (\Auth::user()->type == 'super admin')
                                    <th scope="col" class="sort" data-sort="completion"> {{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->order_id }}</td>
                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                    <td>{{ $order->user_name }}</td>
                                    <td>{{ $order->plan_name }}</td>
                                    <td>{{ env('CURRENCY_SYMBOL') . $order->price }}</td>
                                    <td>{{ $order->payment_type }}</td>
                                    <td>
                                        @if ($order->payment_status == 'succeeded' || $order->payment_status == 'success')
                                            {{-- <i class="mdi mdi-circle text-success"></i> {{ucfirst($order->payment_status)}} --}}
                                            <div class="badge fix_badge bg-success p-2 px-3 rounded">
                                                {{ ucfirst('success') }}</div>
                                        @elseif($order->payment_status == 'Approve')
                                            <div class="badge fix_badge bg-success p-2 px-3 rounded">
                                                {{ ucfirst('Approve') }}</div>
                                        @elseif($order->payment_status == 'Pending')
                                            <div class="badge fix_badge bg-warning p-2 px-3 rounded">
                                                {{ $order->payment_status }}</div>
                                        @else
                                            {{-- <i class="mdi mdi-circle text-danger"></i> {{ucfirst($order->payment_status)}} --}}
                                            <div class="badge fix_badge bg-danger p-2 px-3 rounded">
                                                {{ ucfirst($order->payment_status) }}</div>
                                        @endif
                                    </td>

                                    <td>{{ !empty($order->total_coupon_used) ? (!empty($order->total_coupon_used->coupon_detail) ? $order->total_coupon_used->coupon_detail->code : '-') : '-' }}
                                    </td>

                                    <td class="Id">
                                        @if (!empty($order->receipt) && $order->payment_type == 'Bank Transfer')
                                            <a href="{{ asset('storage/payment_recipt/' . $order->receipt) }}"
                                                class="btn  btn-outline-primary" target="_blank"><i
                                                    class="fas fa-file-invoice"></i> {{ __('Invoice') }}</a>
                                        @elseif($order->payment_type == 'STRIPE')
                                            <a href="{{ $order->receipt }}" class="btn  btn-outline-primary"
                                                target="_blank"><i class="fas fa-file-invoice"></i> {{ __('Invoice') }}</a>
                                        @elseif($order->payment_type == 'Manually Upgrade By Super Admin')
                                            {{ $order->receipt }}</a>
                                        @else
                                            {{ __('-') }}
                                        @endif
                                    </td>
                                    @if (\Auth::user()->type == 'super admin')
                                        <td>
                                            @if ($order->payment_type == 'Bank Transfer' && $order->payment_status == 'Pending')
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="#"
                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                        data-url="{{ route('order.action', $order->id) }}"
                                                        data-bs-whatever="{{ __('Payment Status') }}"> <span
                                                            class="text-white">
                                                            <i class="ti ti-caret-right" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('View') }}"></i></span></a>
                                                </div>
                                            @endif
                                            @php
                                                $user = App\Models\User::find($order->user_id);
                                            @endphp
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['order.destroy', $order->id]]) !!}
                                                <a href="#!"
                                                    class="mx-3 btn d-inline-flex btn-sm d-flex wid-30 hei-30 rounded align-items-center show_confirm">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Delete') }}"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                            @foreach ($userOrders as $userOrder)
                                            @if ($user->plan == $order->plan_id && $order->order_id == $userOrder->order_id && $order->is_refund == 0 && $user->plan != 1)
                                                    <div class="badge bg-warning rounded p-2 px-3 ms-2">
                                                        <a href="{{ route('order.refund', [$order->id, $order->user_id]) }}"
                                                            class="mx-3 align-items-center" data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-original-title="{{ __('Delete') }}">
                                                            <span class ="text-white">{{ __('Refund') }}</span>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
