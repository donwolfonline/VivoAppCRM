@extends('layouts.admin')
@push('script-page')
    <script>
        $(document).on('click', '.code', function () {
            var type = $(this).attr('value');
            var ele = $('#'+type+'')
            if (type == 'manual') {
                $('#manual').removeClass('d-none');
                $('#manual').addClass('d-block');
                $('#auto').removeClass('d-block');
                $('#auto').addClass('d-none');
            } else {
                $('#auto').removeClass('d-none');
                $('#auto').addClass('d-block');
                $('#manual').removeClass('d-block');
                $('#manual').addClass('d-none');
            }
        });

        $(document).on('click', '#code-generate', function () {
            var length = 10;
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            $('#auto-code').val(result);
        });
    </script>
@endpush
@section('page-title')
    {{__('Coupon')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Coupon')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Coupon')}}</li>
@endsection
@section('action-btn')
    <a href="#" data-url="{{ route('coupon.create') }}" data-size="lg" data-bs-toggle="modal" data-bs-target="#exampleModal"
      data-bs-whatever="{{__('Create Coupon')}}" data-title="{{__('Create New Coupon')}}" class="btn btn-sm btn-primary btn-icon m-1" data-toggle="tooltip">
        <span class="btn-inner--icon"><i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
    </a>
@endsection
@section('content')
<div class="col-xl-12">
    <div class="card">
        <div class="card-header card-body table-border-style">
            <h5></h5>
            <div class="table-responsive">
                <table class="table" id="pc-dt-simple">
                    <thead>
                        <tr>
                            <th scope="col">{{__('Name')}}</th>
                            <th scope="col">{{__('Code')}}</th>
                            <th scope="col">{{__('Discount (%)')}}</th>
                            <th scope="col">{{__('Limit')}}</th>
                            <th scope="col">{{__('Used')}}</th>
                            <th scope="col">{{__('Action')}}</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($coupons as $coupon)
                            <tr>

                                <td class="budget">{{ $coupon->name }} </td>
                                <td>{{ $coupon->code }}</td>
                                <td>
                                    {{ $coupon->discount }}
                                </td>
                                <td>{{ $coupon->limit }}</td>
                                <td>{{ $coupon->used_coupon() }}</td>
                                <td class="text-right">
                                    <div class="actions ml-3">
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ route('coupon.show',$coupon->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}">
                                                <i class="ti ti-eye text-white"></i>
                                            </a>
                                        </div>
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#!"  data-size="lg" data-url="{{ route('coupon.edit',$coupon->id) }}"
                                                data-bs-toggle="modal" data-bs-target="#exampleModal"  data-bs-whatever="{{__('Edit Coupon')}}" class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                <i class="ti ti-edit text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i>
                                            </a>
                                        </div>
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['coupon.destroy', $coupon->id]]) !!}
                                            <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                            </a>
                                            {!! Form::close() !!}
                                        </div>


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

