@extends('layouts.admin')
@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    // $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('script-page')
@endpush
@section('page-title')
    {{ __('User') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('User') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('User') }}</li>
@endsection
@section('action-btn')
{{-- @dd($admin_payment_setting) --}}
@if (
                    $admin_payment_setting['is_manually_enabled'] == 'on' ||
                        $admin_payment_setting['is_bank_transfer_enabled'] == 'on' ||
                        $admin_payment_setting['is_stripe_enabled'] == 'on' ||
                        $admin_payment_setting['is_paypal_enabled'] == 'on' ||
                        $admin_payment_setting['is_paystack_enabled'] == 'on' ||
                        $admin_payment_setting['is_flutterwave_enabled'] == 'on' ||
                        $admin_payment_setting['is_razorpay_enabled'] == 'on' ||
                        $admin_payment_setting['is_mercado_enabled'] == 'on' ||
                        $admin_payment_setting['is_paytm_enabled'] == 'on' ||
                        $admin_payment_setting['is_mollie_enabled'] == 'on' ||
                        $admin_payment_setting['is_skrill_enabled'] == 'on' ||
                        $admin_payment_setting['is_coingate_enabled'] == 'on' ||
                        $admin_payment_setting['is_paymentwall_enabled'] == 'on' ||
                        $admin_payment_setting['is_toyyibpay_enabled'] == 'on' ||
                        $admin_payment_setting['is_payfast_enabled'] == 'on' ||
                        $admin_payment_setting['is_iyzipay_enabled'] == 'on' ||
                        $admin_payment_setting['is_sspay_enabled'] == 'on' ||
                        $admin_payment_setting['is_paytab_enabled'] == 'on' ||
                        $admin_payment_setting['is_benefit_enabled'] == 'on' ||
                        $admin_payment_setting['is_cashfree_enabled'] == 'on' ||
                        $admin_payment_setting['is_aamarpay_enabled'] == 'on' ||
                        $admin_payment_setting['is_paytr_enabled'] == 'on' ||
                        $admin_payment_setting['is_yookassa_enabled'] == 'on' ||
                        $admin_payment_setting['is_midtrans_enabled'] == 'on' ||
                        $admin_payment_setting['is_PaiementPro_enabled'] == 'on' ||
                        $admin_payment_setting['is_CinetPay_enabled'] == 'on' ||
                        $admin_payment_setting['is_nepalste_enabled'] == 'on'||
                        $admin_payment_setting['is_Fedapay_enabled'] == 'on' ||
                        $admin_payment_setting['is_xendit_enabled'] == 'on' ||
                        $admin_payment_setting['is_PayHere_enabled'] == 'on')
                  <a href="#" data-url="{{ route('user.create') }}" data-size="md" data-bs-whatever="{{ __('Create New User') }}"
                  class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
                  data-bs-whatever="{{ __('Create New User') }}">
                  <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
              </a>
                @endif


@endsection
@section('content')
    <div class="row">
        @foreach ($users as $user)
            <div class="col-lg-3 col-sm-6">
                <div class="card hover-shadow-lg">
                    <div class="card-body text-center">
                        <div class="row">
                            <div class="text-end">
                                <div class="actions">
                                    <div class="dropdown action-item">
                                        <a href="#" class="action-item " data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i></a>
                                        <div class="dropdown-menu dropdown-menu-right">

                                            <a href="#" data-url="{{ route('user.edit', $user->id) }}"
                                                class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                data-bs-whatever="{{ __('Edit User') }}">
                                                <i class="ti ti-edit"> </i> {{ __('Edit') }}</a>


                                            <a href="#" class="dropdown-item"
                                                data-url="{{ route('plan.upgrade', $user->id) }}" data-bs-toggle="modal"
                                                data-bs-target="#exampleModal" data-bs-whatever="{{ __('Upgrade Plan') }}">
                                                <i class="ti ti-trophy"></i> {{ __('Upgrade Plan') }}</a>


                                            <a href="{{ route('login.with.company', $user->id) }}" class="dropdown-item"
                                                data-bs-toggle="tooltip"
                                                data-bs-original-title="{{ __('Login As Company') }}"> <i
                                                    class="ti ti-replace"></i> {{ __('Login As Company') }} </a>

                                            <a href="#"
                                                data-url="{{ route('user.reset', \Crypt::encrypt($user->id)) }}"
                                                data-bs-toggle="modal" data-bs-target="#exampleModal" class="dropdown-item"
                                                data-bs-whatever="{{ __('Reset Password') }}">
                                                <i class="ti ti-lock"> </i> {{ __('Reset Password') }}
                                            </a>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['user.destroy', $user->id]]) !!}
                                            <a href="#!" class=" show_confirm dropdown-item">
                                                <i class="ti ti-trash"></i>{{ __('Delete') }}
                                            </a>
                                            {!! Form::close() !!}
                                            @if ($user->is_enable_login == 1)
                                                <a href="{{ route('user.login', \Crypt::encrypt($user->id)) }}"
                                                    class="dropdown-item">
                                                    <i class="ti ti-road-sign"></i>
                                                    <span class="text-danger"> {{ __('Login Disable') }}</span>
                                                </a>
                                            @elseif ($user->is_enable_login == 0 && $user->password == null)
                                                <a href="#"
                                                    data-url="{{ route('user.reset', \Crypt::encrypt($user->id)) }}"
                                                    data-ajax-popup="true" data-size="md" class="dropdown-item login_enable"
                                                    data-title="{{ __('New Password') }}" class="dropdown-item">
                                                    <i class="ti ti-road-sign"></i>
                                                    <span class="text-success"> {{ __('Login Enable') }}</span>
                                                </a>
                                            @else
                                                <a href="{{ route('user.login', \Crypt::encrypt($user->id)) }}"
                                                    class="dropdown-item">
                                                    <i class="ti ti-road-sign"></i>
                                                    <span class="text-success"> {{ __('Login Enable') }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="avatar-parent-child">


                                <img alt="{{ $user->name }}"
                                    src="{{ !empty($user->avatar) ? $profile . $user->avatar : $profile . 'avatar.png' }}"
                                    class=" wid-30 rounded-circle avatar-lg" alt="image" width="100px">
                            </div>
                        </div>


                        <h5 class="h6 mt-4 mb-2"> {{ $user->name }}</h5>
                        <a href="#" class="d-block text-sm text-muted "> {{ $user->email }}</a>

                        <div class="col-12 text-center Id ">
                            <a href="#" data-url="{{ route('company.info', $user->id) }}" data-size="lg"
                                data-ajax-popup="true" class="btn btn-outline-primary mt-3"
                                data-title="{{ __('Company Info') }}">{{ __('AdminHub') }}</a>
                        </div>
                    </div>
                    <div class="card-body border-top">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-6 text-center">
                                <span class="d-block h4 mb-0">{{ $user->countEmployees($user->id) }}</span>
                                <span class="d-block text-sm text-muted">{{ __('Employees') }}</span>
                            </div>
                            <div class="col-6 text-center">
                                <span class="d-block h4 mb-0">{{ $user->countClients($user->id) }}</span>
                                <span class="d-block text-sm text-muted">{{ __('Clients') }}</span>
                            </div>
                            <div class="col-5 text-center pt-3">
                                <span
                                    class="d-block h5 mb-0">{{ !empty($user->currentPlan) ? $user->currentPlan->name : __('Free') }}</span>
                                <span class="d-block text-sm text-muted">{{ __('Plan') }}</span>
                            </div>
                            <div class="col-7 text-center pt-3">
                                <span
                                    class="d-block h5 mb-0">{{ !empty($user->plan_expire_date) ? \Auth::user()->dateFormat($user->plan_expire_date) : 'Lifetime' }}</span>
                                <span class="d-block text-sm text-muted">{{ __('Plan Expired') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-md-3">
            <a href="#" class="btn-addnew-project " data-bs-toggle="modal" data-bs-target="#exampleModal"
                data-url="{{ route('user.create') }}" data-size="lg" data-bs-whatever="{{ __('Create New User') }}">
                <div class="bg-primary proj-add-icon">
                    <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
                </div>
                <h6 class="mt-4 mb-2">{{ __('New User') }}</h6>
                <p class="text-muted text-center">{{ __('Click here to add New User') }}</p>
            </a>
        </div>
    </div>
@endsection
@push('script-page')
    <script>
        $(document).on('change', '#password_switch', function() {
            if ($(this).is(':checked')) {
                $('.ps_div').removeClass('d-none');
                $('#password').attr("required", true);

            } else {
                $('.ps_div').addClass('d-none');
                $('#password').val(null);
                $('#password').removeAttr("required");
            }
        });
        $(document).on('click', '.login_enable', function() {
            setTimeout(function() {
                $('.modal-body').append($('<input>', {
                    type: 'hidden',
                    val: 'true',
                    name: 'login_enable'
                }));
            }, 2000);
        });
    </script>
@endpush
