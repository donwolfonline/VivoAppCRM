@extends('layouts.auth')
@section('page-title')
    {{ __('Verification') }}
@endsection
@php
    $footer_text = isset(\App\Models\Utility::settings()['footer_text']) ? \App\Models\Utility::settings()['footer_text'] : '';
    \App\Models\Utility::setCaptchaConfig();

    if (empty($lang)) {
        $lang = Utility::getValByName('default_language');
    }

    \App::setLocale($lang);
    $setting = \App\Models\Utility::settings();

@endphp
@push('custom-scripts')
@if (isset($setting['recaptcha_module']) && $setting['recaptcha_module'] == 'yes')
    @if (isset($setting['google_recaptcha_version']) && $setting['google_recaptcha_version'] == 'v2-checkbox')
        {!! NoCaptcha::renderJs() !!}
    @else

        <script src="https://www.google.com/recaptcha/api.js?render={{ $setting['google_recaptcha_key'] }}"></script>

        <script>
            $(document).ready(function() {
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ $setting['google_recaptcha_key'] }}', {
                        action: 'submit'
                    }).then(function(token) {
                        $('#g-recaptcha-response').val(token);

                    });
                });
            });
        </script>
    @endif
@endif
@endpush
@php
    use App\Models\Utility;
    $languages = App\Models\Utility::languages();
@endphp


@section('language')
    @foreach (Utility::languages() as $code => $language)
        <a href="{{ route('verification.notice', $code) }}" tabindex="0" class="dropdown-item {{ $code == $lang ? 'active' : '' }}">
            <span>{{ ucFirst($language) }}</span>
        </a>
    @endforeach
@endsection

@section('content')
    <div class="card-body">
        <div>
            <h2 class="mb-3 f-w-600">{{ __('Email Verification') }}</h2>
        </div>
        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600 text-primary">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif
        <div class="mb-4 text-sm text-gray-600">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>
        <div class="custom-login-form">
            <div class="mt-4 flex items-center justify-between">
                <div class="row">
                    <div class="col-auto">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm"> {{ __('Resend Verification Email') }}
                            </button>
                        </form>
                    </div>
                    <div class="col-auto">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm"> {{ __('Logout') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
