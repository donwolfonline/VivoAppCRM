@extends('layouts.auth')
@section('page-title')
    {{__('Login')}}
@endsection

@php
	$footer_text=isset(\App\Models\Utility::settings()['footer_text']) ? \App\Models\Utility::settings()['footer_text'] : '';
	\App\Models\Utility::setCaptchaConfig();
    $setting = \App\Models\Utility::settings();
	// $SITE_RTL = Cookie::get('SITE_RTL');
    // if ($SITE_RTL == '') {
    //     $SITE_RTL == 'off';
    // }
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
@section('language')
	@foreach(Utility::languages() as $code => $language)
		<a href="{{ route('login',$code) }}" tabindex="0" class="dropdown-item {{ $code == $lang ? 'active':'' }}">
			<span>{{ ucFirst($language)}}</span>
		</a>
	@endforeach
@endsection
@section('content')

<!-- [ auth-signup ] start -->
		<div class="card-body">
			<div>
				<h2 class="mb-3 f-w-600">{{ __('Login') }}</h2>
			</div>
            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
			<div class="custom-login-form">
				<form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate="">
				@csrf
					<div class="form-group mb-3">
						<label class="form-label">{{ __('Email') }}</label>
						<input id="email" type="email" class="form-control  @error('email') is-invalid @enderror"
							name="email" placeholder="{{ __('Enter your email') }}"
							required autofocus>
						@error('email')
							<span class="error invalid-email text-danger" role="alert">
								<small>{{ $message }}</small>
							</span>
						@enderror
					</div>
					<div class="form-group mb-3 pss-field">
						<label class="form-label">{{ __('Password') }}</label>
						<input id="password" type="password" class="form-control  @error('password') is-invalid @enderror" name="password" placeholder="{{ __('Password') }}" required>
						@error('password')
							<span class="error invalid-password text-danger" role="alert">
								<small>{{ $message }}</small>
							</span>
						@enderror
					</div>
					<div class="form-group mb-4">
						<div class="d-flex flex-wrap align-items-center justify-content-between">
							@if (Route::has('password.request'))
								<span>
									<a href="{{ route('password.request', $lang) }}" tabindex="0">{{ __('Forgot Your Password?') }}</a>
								</span>
							@endif
						</div>
					</div>

                    @if ($setting['recaptcha_module'] == 'yes')
                        @if (isset($setting['google_recaptcha_version']) && $setting['google_recaptcha_version'] == 'v2-checkbox')
                            <div class="form-group mb-4">
                                {!! NoCaptcha::display($setting['cust_darklayout'] == 'on' ? ['data-theme' => 'dark'] : []) !!}
                                @error('g-recaptcha-response')
                                    <span class="small text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        @else
                            <div class="form-group mb-4">
                                <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" class="form-control">
                                @error('g-recaptcha-response')
                                    <span class="error small text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        @endif
                    @endif

					<div class="d-grid">
						<button class="btn btn-primary mt-2" type="submit">
							{{ __('Login') }}
						</button>
					</div>
				</form>

				@if(Utility::getValByName('SIGNUP')=='on')
					<p class="my-4 text-center">{{ __("Don't have an account?") }}
						<a href="{{route('register',$lang)}}" tabindex="0">{{__('Register')}}</a>
					</p>
				@endif
			</div>
		</div>
<!-- [ auth-signup ] end -->

@endsection
