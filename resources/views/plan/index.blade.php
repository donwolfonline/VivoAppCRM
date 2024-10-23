@extends('layouts.admin')
@php
    $dir = asset(Storage::url('uploads/plan'));
    $admin_payment_setting = Utility::payment_settings();
@endphp
@push('script-page')
@endpush
@section('page-title')
    {{ __('Plan') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Plan') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Plan') }}</li>
@endsection
@section('action-btn')
    @if (\Auth::user()->type == 'super admin')
        <a href="#" data-url="{{ route('plan.create') }}" data-bs-toggle="modal" data-bs-target="#exampleModal"
            data-bs-whatever="{{ __('Create New Plan') }}" data-size="lg" class="btn btn-sm btn-primary btn-icon m-1"
            data-bs-toggle="tooltip" title="{{ __('Create New Plan') }}">
            <span class="btn-inner--icon"><i class="ti ti-plus"></i></span>
        </a>
    @endif
@endsection
@section('content')
    @foreach ($plans as $plan)
        <div class="col-lg-4 col-xl-3 col-md-6 col-sm-6">
            <div class="card price-card price-1 wow animate__fadeInUp " data-wow-delay="0.2s"
                style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
                <div class="card-body">
                    <span class="price-badge bg-primary">{{ $plan->name }}</span>
                    @if (\Auth::user()->type == 'super admin')
                        <div class="row d-flex">
                            <div class="col-6">
                                @if ($plan->price > 0)
                                    <div class="form-check form-switch custom-switch-v1 float-left">
                                        <input type="checkbox" name="plan_active"
                                            class="form-check-input input-primary is_active" value="1"
                                            data-id='{{ $plan->id }}' data-name="{{ __('plan') }}"
                                            {{ $plan->is_active == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="plan_active"></label>
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex col-6 flex-row-reverse m-0 p-0">
                                <div class="action-btn bg-primary ms-2">
                                    <a title="Edit Plan" data-size="lg" href="#"
                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                        data-url="{{ route('plan.edit', $plan->id) }}" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal" data-bs-whatever="{{ __('Edit Plan') }}"
                                        data-size="lg" data-original-title="{{ __('Edit') }}">
                                        <i class="ti ti-edit text-white" data-bs-title="{{ __('Edit Plan') }}"
                                            data-bs-toggle="tooltip">
                                        </i>
                                    </a>
                                </div>
                                @if ($plan->price > 0)
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['plan.destroy', $plan->id]]) !!}
                                        <a href="#!" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                data-bs-original-title="{{ __('delete') }}"></i>
                                        </a>
                                        {!! Form::close() !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if (\Auth::user()->type == 'company' && \Auth::user()->plan == $plan->id)
                        <div class="d-flex flex-row-reverse m-0 p-0 ">
                            <span class="d-flex align-items-center ">
                                <i class="f-10 lh-1 fas fa-circle text-success"></i>
                                <span class="ms-2">{{ __('Active') }}</span>
                            </span>
                        </div>
                    @endif
                    <h3 class=" f-w-600 ">
                        {{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ $plan->price }}<small
                            class="text-sm">{{ \App\Models\Plan::$arrDuration[$plan->duration] }}</small></h3>
                    @if ($plan->description)
                        <p class="mb-0">
                            {{ $plan->description }}<br />
                        </p>
                    @endif
                    <p class="mb-0">
                        {{ __('Free Trial Days:') }} {{ $plan->trial_days ? $plan->trial_days : 0 }}<br />
                    </p>
                    <ul class="list-unstyled my-3">
                        <li>
                            <span class="theme-avtar">
                                <i class="text-primary ti ti-circle-plus"></i></span>
                            {{ $plan->max_employee == '-1' ? __('Unlimited') : $plan->max_employee }} {{ __('Employee') }}
                        </li>
                        <li>
                            <span class="theme-avtar">
                                <i class="text-primary ti ti-circle-plus"></i></span>
                            {{ $plan->max_client == '-1' ? __('Unlimited') : $plan->max_client }} {{ __('Clients') }}
                        </li>
                        <li>
                            <span class="theme-avtar">
                                <i class="text-primary ti ti-circle-plus"></i></span>
                            {{ $plan->storage_limit ? $plan->storage_limit : 0 }} {{ __('MB') }} {{ __('Storage') }}
                        </li>
                        <li>
                            <span class="theme-avtar">
                                <i class="text-primary ti ti-circle-plus"></i></span>
                            @if ($plan->enable_chatgpt == 'on')
                                {{ 'Enable Chat GPT' }}
                            @else
                                <span style="color: red;"> {{ __('Disable Chat GPT') }} </span>
                            @endif
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col-12">
                            @if (\Auth::user()->type == 'company' && \Auth::user()->trial_plan == $plan->id && \Auth::user()->trial_expire_date)
                                <p class="display-total-time mb-0">
                                    {{ __('Plan Trial Expired : ') }}
                                    {{ !empty(\Auth::user()->trial_expire_date) ? \Auth::user()->trial_expire_date : 'lifetime' }}
                                </p>
                            @endif
                            @if (
                                \Auth::user()->plan == $plan->id &&
                                    date('Y-m-d') < \Auth::user()->plan_expire_date &&
                                    \Auth::user()->trial_expire_date == null)
                                <p class="server-plan font-weight-bold text-center mx-sm-5">
                                    {{ __('Expire on ') }}
                                    {{ date('d M Y', strtotime(\Auth::user()->plan_expire_date)) }}
                                </p>
                            @elseif(
                                \Auth::user()->plan == $plan->id &&
                                    !empty(\Auth::user()->plan_expire_date) &&
                                    \Auth::user()->plan_expire_date < date('Y-m-d'))
                                <p class="server-plan font-weight-bold text-center">
                                    {{ __('Expired') }}
                                </p>
                            @elseif(\Auth::user()->plan == $plan->id && !empty(\Auth::user()->plan_expire_date) && \Auth::user()->is_trial_done == 1)
                                <p class="server-plan font-weight-bold text-center mx-sm-5">
                                    {{ __('Current Trial Expire on ') . date('d M Y', strtotime(\Auth::user()->plan_expire_date)) }}
                                </p>
                            @else
                                @if ($plan->id != \Auth::user()->plan && \Auth::user()->type != 'super admin')
                                    <div class="d-flex justify-content-center align-items-center">
                                        @if ($plan->price > 0 && \Auth::user()->trial_plan == 0 && \Auth::user()->plan != $plan->id && $plan->trial == 1)
                                            <a href="{{ route('plan.trial', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                class="btn btn-lg btn-primary btn-icon m-1">{{ __('Free Trial') }}</a>
                                        @endif
                                        @if ($plan->price > 0)
                                            <a href="{{ route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                class="btn btn-primary btn-icon m-1">{{ __('Subscribe') }}</a>
                                        @endif
                                        @if ($plan->id != 1 && \Auth::user()->plan != $plan->id && \Auth::user()->type == 'company')
                                            @if (\Auth::user()->requested_plan != $plan->id)
                                                <a href="{{ route('send.request', [\Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                                    class="btn btn-primary btn-icon m-1"
                                                    data-title="{{ __('Send Request') }}" data-toggle="tooltip">
                                                    <span class="btn-inner--icon"><i
                                                            class="ti ti-arrow-forward-up"></i></span>
                                                </a>
                                            @else
                                                <a href="{{ route('request.cancel', \Auth::user()->id) }}"
                                                    class="btn btn-icon m-1 btn-danger"
                                                    data-title="{{ __('Cancel Request') }}" data-toggle="tooltip">
                                                    <span class="btn-inner-icon"><i class="ti ti-trash"></i></span>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endforeach
@endsection
@push('script-page')
    <script>
        $(document).on("click", ".is_active", function() {
            var id = $(this).attr('data-id');
            var is_active = ($(this).is(':checked')) ? $(this).val() : 0;
            $.ajax({
                url: '{{ route('plan.enable') }}',
                type: 'POST',
                data: {
                    "is_active": is_active,
                    "id": id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    if (data.success) {
                        toastrs('success', data.success);
                    } else {
                        toastrs('error', data.error);
                    }
                }
            });
        });
    </script>
@endpush
