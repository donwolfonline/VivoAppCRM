@php
    $logo = asset(Storage::url('logo/'));
    $company_logo = Utility::getValByName('company_logo');
    $favicon = Utility::getValByName('company_favicon');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="CRMGo SaaS - Projects, Accounting, Leads, Deals & HRM Tool">
    <meta name="author" content=" WorkDo">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ \App\Models\Utility::get_file('uploads/logo/favicon.png') }}" type="image"
        sizes="16x16">
    <title>{{ __('Form') }} &dash;
        {{ Utility::getValByName('header_text') ? Utility::getValByName('header_text') : config('app.name', 'LeadGo') }}
        {{ Utility::getValByName('header_text') ? Utility::getValByName('header_text') : config('app.name', 'CRMGo') }}
    </title>
    <link rel="stylesheet" href="{{ asset('custom_assets/libs/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('css/site-light.css') }}" id="stylesheet"> -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}" type="text/css">

</head>


<body class="application application-offset">
    <div class="container-fluid container-application">
        <div class="main-content position-relative">
            <div class="page-content">
                <div class="min-vh-100 py-5 d-flex align-items-center">
                    <div class="w-100">
                        <div class="row justify-content-center">
                            <div class="col-sm-8 col-lg-5">
                                <div class="row justify-content-center mb-3">
                                    <a class="navbar-brand" href="#">
                                        <img src="{{ asset(Storage::url('uploads/logo/logo-dark.png')) }}"
                                            class="auth-logo">
                                    </a>
                                </div>
                                <div class="card shadow zindex-100 mb-0">
                                    @if ($form->is_active == 1)
                                        {{ Form::open(['route' => ['form.view.store'], 'method' => 'post']) }}
                                        <div class="card-body px-md-5 py-5">
                                            <div class="mb-4">
                                                <h6 class="h3">{{ $form->name }}</h6>
                                            </div>
                                            <input type="hidden" value="{{ $code }}" name="code">
                                            @if ($objFields && $objFields->count() > 0)
                                                @foreach ($objFields as $objField)
                                                    @if ($objField->type == 'text')
                                                        <div class="form-group">
                                                            {{ Form::label('field-' . $objField->id, __($objField->name), ['class' => 'form-control-label']) }}
                                                            {{ Form::text('field[' . $objField->id . ']', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'field-' . $objField->id]) }}
                                                        </div>
                                                    @elseif($objField->type == 'email')
                                                        <div class="form-group">
                                                            {{ Form::label('field-' . $objField->id, __($objField->name), ['class' => 'form-control-label']) }}
                                                            {{ Form::email('field[' . $objField->id . ']', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'field-' . $objField->id]) }}
                                                        </div>
                                                    @elseif($objField->type == 'number')
                                                        <div class="form-group">
                                                            {{ Form::label('field-' . $objField->id, __($objField->name), ['class' => 'form-control-label']) }}
                                                            {{ Form::number('field[' . $objField->id . ']', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'field-' . $objField->id]) }}
                                                        </div>
                                                    @elseif($objField->type == 'date')
                                                        <div class="form-group">
                                                            {{ Form::label('field-' . $objField->id, __($objField->name), ['class' => 'form-control-label']) }}
                                                            {{ Form::date('field[' . $objField->id . ']', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'field-' . $objField->id]) }}
                                                        </div>
                                                    @elseif($objField->type == 'textarea')
                                                        <div class="form-group">
                                                            {{ Form::label('field-' . $objField->id, __($objField->name), ['class' => 'form-control-label']) }}
                                                            {{ Form::textarea('field[' . $objField->id . ']', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'field-' . $objField->id]) }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                                <div class="mt-4">
                                                    {{ Form::submit(__('Submit'), ['class' => 'btn  btn-primary ']) }}
                                                </div>
                                            @endif
                                        </div>

                                        {{ Form::close() }}
                                    @else
                                        <div class="page-title">
                                            <h5>{{ __('Form is not active.') }}</h5>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('public/custom_assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/dash.js') }}"></script>
    <script src="{{ asset('public/custom_assets/libs/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('js/letter.avatar.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <!-- <script src="{{ asset('assets/js/site.core.js') }}"></script>
<script src="{{ asset('assets/js/site.js') }}"></script>
<script src="{{ asset('custom_assets/libs/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script> -->
    <script>
        function toastrs(title, message, type) {
            var f = document.getElementById('liveToast');
            var a = new bootstrap.Toast(f).show();
            if (type == 'success') {
                $('#liveToast').addClass('bg-primary');
            } else {
                $('#liveToast').addClass('bg-danger');
            }
            $('#liveToast .toast-body').html(message);
        }
    </script>
    @if (Session::has('success'))
        <script>
            toastrs('{{ __('Success') }}', '{!! session('success') !!}', 'success');
        </script>
        {{ Session::forget('success') }}
    @endif
    @if (Session::has('error'))
        <script>
            toastrs('{{ __('Error') }}', '{!! session('error') !!}', 'error');
        </script>
        {{ Session::forget('error') }}
    @endif
</body>

</html>
