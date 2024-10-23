@extends('layouts.admin')
<?php
$settings = \App\Models\Utility::settings(1);
?>
@push('scripts')
@endpush
@section('page-title')
    {{ __('Referral') }}
@endsection
@section('title')
    {{ __('Referral Program') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Referral Program') }}</li>
@endsection

@push('pre-purpose-css-page')
    <link rel="stylesheet"
        href="{{ Module::asset('LandingPage:Resources/assets/js/plugins/summernote/summernote-bs4.css') }}">
@endpush

@push('script-page')
    <script src="{{ Module::asset('LandingPage:Resources/assets/js/plugins/summernote/summernote-bs4.js') }}"></script>
    <script type="text/javascript">
        summernote()
    </script>
@endpush

@section('content')



@if (Auth::user()->type == 'company')



        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-xl-3">
                        <div class="card sticky-top" style="top:30px">
                            <div class="list-group list-group-flush" id="useradd-sidenav">

                                <a href="#guideline" data-target="guideline"
                                    class="list-group-item list-group-item-action border-0 menu-btn active">{{ __('GuideLine') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>

                                <a href="#ref_transaction" data-target="ref_transaction"
                                    class="list-group-item list-group-item-action border-0 menu-btn">{{ __('Referral Transaction') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>

                                <a href="#payout" data-target="payout"
                                    class="list-group-item list-group-item-action border-0 menu-btn">{{ __('Payout') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-9">
                        {{--  Guideline --}}
                        <div class="card menu-section" id="guideline">
                            {{ Form::model(null, ['route' => ['landingpage.store'], 'method' => 'POST']) }}
                            @csrf

                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <h5 class="mb-2">{{ __('GuideLine') }}</h5>
                                        </div>

                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">


                                            <div class="form-control p-3 border-2">
                                                <h4><b>{{ __('Refer ') .(Auth::user()->name) .(' and Earn ').('$').  (!empty($referralProgram->hold_amount) ? strip_tags($referralProgram->hold_amount) : '' ).(' perpaid signup !')}} </b>  </h4>

                                                    {{ !empty($referralProgram['guideline']) ? strip_tags($referralProgram['guideline']) : '' }}



                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-control p-3 border-2">


                                                        <h4 class="text-center">{{ __('Share Your Link') }}</h4>
                                                        <div class="d-flex justify-content-between">
                                                            <a href="#" class="btn btn-sm btn-light-primary w-100 cp_link"
                                                               data-link="{{ route('register', ['ref_id' => \Auth::user()->referral_code]) }}"
                                                               data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                                               data-bs-original-title="Click to copy business link">
                                                                {{ route('register', ['ref' => \Auth::user()->referral_code]) }}

                                                                <i class="ti ti-copy"></i>

                                                                {{-- <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                                     stroke-linecap="round" stroke-linejoin="round"
                                                                     class="feather feather-copy ms-1">
                                                                    <rect x="9" y="9" width="13" height="13" rx="2"
                                                                          ry="2"></rect>
                                                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 20v1"></path>
                                                                </svg> --}}
                                                            </a>
                                                        </div>





                                            </div>
                                        </div>


                                    </div>
                                </div>


                            {{ Form::close() }}
                        </div>

                        <div class="card menu-section d-none" id="ref_transaction">

                                {{Form::model(null, array('route' => array('landingpage.store'), 'method' => 'POST')) }}
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
                                                <th scope="col">{{__('#')}}</th>
                                                <th scope="col">{{__('COMPANY NAME')}}</th>
                                                <th scope="col">{{__('PLAN NAME')}}</th>
                                                <th scope="col">{{__('PLAN PRICE')}}</th>
                                                <th scope="col">{{__('COMMISSION (%)')}}</th>
                                                <th scope="col">{{__('COMMISSION AMOUNT')}}</th>

                                            </tr>
                                        </thead>
                                        <tbody>



                                            @foreach ($transaction as $transaction)
                                            <tr>

                                                <td class="budget">{{ $transaction->id }}</td>
                                                    <td>{{ $transaction->company_name }} </td>
                                                <td>{{ $transaction->plane_name }}</td>
                                                <td>
                                                    {{ $transaction->plan_price }}
                                                </td>
                                                <td>{{ $transaction->commission }}</td>
                                                <td>{{ $transaction->commission_amount}}</td>

                                            </tr>
                                        @endforeach


                                        </tbody>
                                    </table>

                                </div>



                    {{ Form::close() }}
                            </div>
                        </div>

                        {{-- payout --}}

                        <div class="menu-section d-none" id="payout">
                            <div class="card">

                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-lg-10 col-md-10 col-sm-10">
                                            <h5>{{ __('Payout') }}</h5>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-2 text-end">
                                            <a href="#" class="btn btn-sm btn-primary btn-icon m-1"
                                                data-bs-toggle="modal"data-bs-target="#bonus" data-size="lg"
                                                data-bs-whatever="Amount PayOut">
                                                <span class="text-white">
                                                    <i class="ti ti-arrow-forward-up text-end" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Amount PayOut') }}"></i>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="d-flex border p-3">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-report-money"></i>
                                                </div>
                                                <div style="margin-left: 3%">
                                                    <small>{{ __('Total') }}</small>
                                                    <h5>{{ __('Commission Amount') }}</h5>
                                                </div>
                                                <h4 class="pt-3" style="margin-left: auto">$ {{ $totalCommission ?? '' }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="d-flex border p-3">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-report-money"></i>
                                                </div>
                                                <div style="margin-left: 3%">
                                                    <small>{{ __('Paid') }}</small>
                                                    <h5>{{ __('Commission Amount') }}</h5>
                                                </div>
                                                <h4 class="pt-3" style="margin-left: auto"> $ {{ $totalpaidCommission ?? '' }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                                <div class="card">
                                    <div class="card-header">
                                        <div class="row align-items-center">
                                            <div class="col-6">
                                                <h5 class="mb-2">{{ __('Payout History') }}</h5>
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
                                                        <th scope="col">{{ __('STATUS ') }}</th>
                                                        <th scope="col">{{ __('REQUEST AMOUNT') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($paidCommission as $transaction)
                                                        <tr>

                                                            <td class="budget">{{ $transaction->id }} </td>
                                                            <td>{{ $transaction->company_name }}</td>
                                                            <td>
                                                                {{ $transaction->date }}
                                                            </td>
                                                            <td>
                                                            @if ($transaction->status == "reject")
                                                            <span
                                                                class="status_badge badge bg-danger p-2 px-3 rounded">{{$transaction->status}}</span>
                                                        @elseif($transaction->status == "")
                                                            <span
                                                                class="status_badge badge bg-warning p-2 px-3 rounded">Pending..</span>
                                                        @elseif($transaction->status == "accept")
                                                            <span
                                                                class="status_badge badge bg-primary p-2 px-3 rounded">{{$transaction->status}}</span>
                                                        @endif
                                                            </td>
                                                            <td>{{ $transaction->amount}}</td>

                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>

                                </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>


    <div class="modal fade " id="bonus" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog moda">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="myLargeModalLabel">{{ __('Send Request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{route('payout.store')}}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group" id="site-name-div">
                            <label class="form-label">{{ __('Request Amount') }}</label>
                            <input type="number" class="form-control" placeholder="{{ __('Enter Amount') }}"
                                name="amount" id="amount">

                        </div>
                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn  btn-light"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button class="btn btn-primary me-2">{{ __('Send') }}</button>
                    </div>
                </form>
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
</script>
    @endpush


@endsection
