@extends('layouts.admin')
@section('page-title')
    {{ __('Referral Program') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Referral Program') }}</li>
@endsection

@php
    $logo = \App\Models\Utility::get_file('uploads/logo');
    $settings = \Modules\LandingPage\Entities\LandingPageSetting::settings();
@endphp

@push('css-page')
    <link rel="stylesheet" href=" {{ Module::asset('LandingPage:Resources/assets/css/summernote/summernote-bs4.css') }}" />
@endpush
@push('script-page')
    <script src="{{ Module::asset('LandingPage:Resources/assets/js/plugins/summernote-bs4.js') }}" referrerpolicy="origin">
    </script>
@endpush


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __(' Referral Program') }}</li>
@endsection


@section('content')
    <style>
        .disabled {
            pointer-events: none;
            opacity: 0.5;
        }
    </style>
    @if (Auth::user()->type == 'super admin')
        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-xl-3">
                        <div class="card sticky-top" style="top:30px">
                            <div class="list-group list-group-flush" id="useradd-sidenav">

                                <a href="#transaction" data-target="transaction"
                                    class="list-group-item list-group-item-action border-0 menu-btn active">{{ __('Transaction') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>

                                <a href="#payout_req" data-target="payout_req"
                                    class="list-group-item list-group-item-action border-0 menu-btn">{{ __('Payout Request') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>

                                {{-- <a href="{{ route('referral.setting') }}" --}}
                                <a href="#setting" data-target="setting"
                                    class="list-group-item list-group-item-action border-0 menu-btn">{{ __('Settings') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-9">
                        <div class="card menu-section" id="transaction">
                            {{ Form::model(null, ['route' => ['landingpage.store'], 'method' => 'POST']) }}
                            @csrf

                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('Transaction') }}</h5>
                                    </div>

                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">

                                    <table class="table" id="">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('#') }}</th>
                                                <th scope="col">{{ __('COMPANY NAME') }}</th>
                                                <th scope="col">{{ __('REFERRAL COMPANY NAME') }}</th>
                                                <th scope="col">{{ __('PLAN NAME') }}</th>
                                                <th scope="col">{{ __('PLAN PRICE') }}</th>
                                                <th scope="col">{{ __('COMMISSION (%)') }}</th>
                                                <th scope="col">{{ __('COMMISSION AMOUNT') }}</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($transaction as $transaction)
                                                <tr>

                                                    <td class="budget">{{ $transaction->id }}</td>
                                                    <td>{{ $transaction->RefcompanyName->name ?? '' }}</td>
                                                    <td>{{ $transaction->company_name }} </td>
                                                    <td>{{ $transaction->plane_name }}</td>
                                                    <td>{{ $transaction->plan_price }}</td>
                                                    <td>{{ $transaction->commission }}</td>
                                                    <td>{{ $transaction->commission_amount }}</td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                            </div>


                            {{ Form::close() }}
                        </div>

                        <div class="card menu-section d-none" id="payout_req">



                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <h5 class="mb-2">{{ __('Payout Request') }}</h5>
                                    </div>

                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">

                                    <table class="table" id="">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('#') }}</th>
                                                <th scope="col">{{ __('COMPANY NAME') }}</th>
                                                <th scope="col">{{ __('REQUEST DATE') }}</th>
                                                <th scope="col">{{ __('REQUEST AMOUNT') }}</th>
                                                <th scope="col">{{ __('ACTION') }}</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($payouts as $payouts)
                                                <tr>
                                                    <td>{{ $payouts->id }}</td>
                                                    <td class="budget">{{ $payouts->company_name }} </td>
                                                    <td>{{ $payouts->date }}</td>
                                                    <td>
                                                        {{ $payouts->amount }}
                                                    </td>

                                                    <td class="text-right">
                                                        <div class="actions ml-3">
                                                            <form action="{{ route('referral_store.status') }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $payouts->id }}">
                                                                <button type="submit" class="btn btn-sm btn-success"
                                                                    name="status" value="accept"><i class="ti ti-check"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-original-title="{{ __('Accept') }}">
                                                                    </i></button>

                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                    name="status" value="reject"><i class="ti ti-x"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-original-title="{{ __('Reject') }}">
                                                                    </i></button>
                                                            </form>
                                                        </div>

                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                            </div>



                        </div>


                        <div class="card menu-section d-none" id="setting">
                            {{ Form::model(null, ['route' => ['setting.store'], 'method' => 'POST']) }}
                            {{-- <form action="{{route('setting.store')}}" method="post"></form> --}}
                            @csrf

                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-11">
                                        <h5 class="mb-2">{{ __('Settings') }}</h5>
                                    </div>
                                    <div class="col-1">
                                        <span class="me-2">{{ __('Enable') }}:</span>
                                        <div class="form-check form-switch custom-switch-v1">
                                            <input type="hidden" name="Reffral_enabled" value="off">
                                            <input type="checkbox" class="form-check-input input-primary"
                                                id="referral_status" name="Reffral_enabled"
                                                {{ isset($referralProgram['Reffral_enabled']) && $referralProgram['Reffral_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                        </div>
                                    </div>
                                </div>




                            </div>

                            <div class="card-body" id="targetDiv">
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Form::label('Comission percentage (%)', __('Comission percentage (%)'), ['class' => 'form-label']) }}
                                            {{ Form::text('commission', $referralProgram ? $referralProgram->commission : '', ['class' => 'form-control', 'placeholder' => __(' Enter Comission percentage (%)')]) }}

                                        </div>


                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Form::label('Minimum Theres hold Amount', __('Minimum Theres hold Amount'), ['class' => 'form-label']) }}
                                            {{ Form::text('holdamt', $referralProgram ? $referralProgram->hold_amount : '', ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}

                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            {{ Form::label('GuideLines', __('GuideLines'), ['class' => 'form-label']) }}

                                            {{ Form::textarea('guideline', $referralProgram ? $referralProgram->guideline : '', ['class' => 'summernote-simple form-control', 'placeholder' => __('Enter GuideLines')]) }}

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button class="btn btn-print-invoice btn-primary m-r-10"
                                    type="submit">{{ __('Save Changes') }}</button>
                            </div>

                            {{ Form::close() }}
                        </div>


                        {{--  End for all settings tab --}}
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('script-page')
        <script>
            $(document).on('click', '.menu-btn', function() {
                var target = $(this).data('target');

                $('.menu-section').addClass('d-none'); // Hide all sections
                $('#' + target).removeClass('d-none'); // Show the targeted section
            });

            $(document).ready(function() {
                $('.menu-btn').click(function(e) {
                    e.preventDefault();
                    $('.menu-btn').removeClass('active');
                    $(this).addClass('active');

                    // Add this line to remove active class from non-active menu items
                    $('.menu-btn').not(this).removeClass('active');
                });
            });
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var copyLinkButtons = document.querySelectorAll('.cp_link');
                copyLinkButtons.forEach(function(button) {
                    button.addEventListener('click', function(event) {
                        event.preventDefault();
                        var link = this.getAttribute('data-link');

                        // Create a temporary input element
                        var input = document.createElement('input');
                        input.setAttribute('value', link);
                        document.body.appendChild(input);

                        // Select and copy the link
                        input.select();
                        document.execCommand('copy');

                        // Remove the temporary input element
                        document.body.removeChild(input);
                    });
                });
            });


            $(document).ready(function() {
                toggleDiv($("#referral_status").prop('checked'));
                $("#referral_status").change(function() {
                    var isChecked = $(this).prop('checked');
                    toggleDiv(isChecked);
                });
            });

            function toggleDiv(isChecked) {
                if (isChecked) {
                    $("#targetDiv").removeClass('disabled').prop('disabled', false);
                } else {
                    $("#targetDiv").addClass('disabled').prop('disabled', true);
                }
            }
        </script>
    @endpush
@endsection
