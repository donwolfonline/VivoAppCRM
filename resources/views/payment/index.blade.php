@extends('layouts.admin')
@php
        $attachment=\App\Models\Utility::get_file('uploads/attachment/');
@endphp
@push('script-page')
@endpush
@section('page-title')
    {{__('Payment')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Payment')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Payment')}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company')
        <a href="#" data-size="lg" data-url="{{ route('payment.create') }}" data-bs-toggle="modal"
        data-bs-target="#exampleModal" data-bs-whatever="{{ __('Create Payment') }}"
         class="btn btn-sm btn-primary btn-icon m-1">
            <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
        </a>
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
                                <th scope="col">{{__('Date')}}</th>
                                <th scope="col">{{__('Payment')}}</th>
                                @if(\Auth::user()->type!='client')
                                    <th scope="col">{{__('Client')}}</th>
                                @endif
                                <th scope="col">{{__('Reference')}}</th>
                                <th scope="col">{{__('Description')}}</th>
                                <th scope="col">{{__('Attachment')}}</th>
                                @if(\Auth::user()->type=='company')
                                    <th scope="col" class="text-right">{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td>{{\Auth::user()->dateFormat($payment->date)}}</td>
                                    <!-- <td>{{  Auth::user()->priceFormat($payment->amount)}}</td> -->
                                    <td scope="row">
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <a href="#" class="name  mb-1 text-md">{{  Auth::user()->priceFormat($payment->amount)}}</a><br>
                                                <span class="text-capitalize badge bg-info rounded-pill badge-sm">
                                                {{  !empty($payment->paymentMethods)?$payment->paymentMethods->name:'-'}}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    @if(\Auth::user()->type!='client')
                                        <td>{{  (!empty($payment->clients)?$payment->clients->name:'-')}}</td>
                                    @endif
                                    <td>{{  $payment->reference}}</td>
                                    <td>{{  $payment->description}}</td>
                                    <td>
                                        @if(!empty($payment->receipt))
                                        @php
                                            $x = pathinfo($payment->receipt, PATHINFO_FILENAME);
                                            $extension = pathinfo($payment->receipt, PATHINFO_EXTENSION);
                                            $result = str_replace(array("#", "'", ";"), '', $payment->receipt);

                                        @endphp
                                        <a  href="{{ route('payment.receipt' , [$x,"$extension"]) }}"  data-toggle="tooltip" class="btn btn-sm btn-primary btn-icon rounded-pill">
                                            <i class="ti ti-download" data-bs-toggle="tooltip" data-bs-original-title="{{__('Download')}}"></i>
                                        </a>
                                        <a  href="{{  $attachment.$x.'.'.$extension }}"  target="_blank" data-toggle="tooltip" class="btn btn-sm btn-secondary btn-icon rounded-pill">
                                            <i class="ti ti-crosshair" data-bs-toggle="tooltip" data-bs-original-title="{{__('Preview')}}"></i>
                                        </a>

                                        @else
                                            -
                                        @endif
                                    </td>
                                    @if(\Auth::user()->type=='company')
                                        <td class="text-right">
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#"  data-size="lg" data-url="{{ route('payment.edit',$payment->id) }}"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-whatever="{{__('Edit ')}}">
                                                    <i class="ti ti-edit text-white"  data-bs-toggle="tooltip" data-bs-original-title="{{__('Edit')}}"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['payment.destroy', $payment->id]]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm align-items-center show_confirm ">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('Delete')}}"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>

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

