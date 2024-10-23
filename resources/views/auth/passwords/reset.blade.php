@extends('layouts.auth')

@php
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
@section('page-title')
    {{__('Password reset')}}
@endsection
@section('title')
    {{__('Password reset')}}
@endsection
@section('language')
    @foreach(Utility::languages() as $code => $language)
    <a href="{{ route('password.reset',$code) }}" tabindex="0" class="dropdown-item {{ $code == $lang ? 'active':'' }}">
        <span>{{ ucFirst($language)}}</span>
    </a>
    @endforeach
@endsection

@section('content')
    <div class="card-body">
        <div>
            <h2 class="mb-3 f-w-600">{{ __('Reset Password') }}</h2>
        </div>
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        <div class="custom-login-form">
            {{Form::open(array('route'=>'password.update','method'=>'post','id'=>'form_data'))}}
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <div class="">
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">{{ __('Email') }}</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <span class="error invalid-email text-danger" role="alert">
                                <small>{{ $message }}</small>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password"
                            value="{{ old('password') }}" required autocomplete="password" autofocus>
                        @error('password')
                            <span class="error invalid-password text-danger" role="alert">
                                <small>{{ $message }}</small>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                        <input id="password-confirm" type="password" class="form-control"
                            name="password_confirmation" value="{{ old('password') }}" required
                            autocomplete="password" autofocus>
                        @error('password_confirmation')
                            <span class="invalid-password_confirmation text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit"
                            class="btn btn-primary btn-submit btn-block mt-2">{{ __('Reset Password') }}</button>
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </div>

@endsection
