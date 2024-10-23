@extends('layouts.auth')
@section('page-title')
    {{ __('Copylink') }}
@endsection
@section('content')
    <div class="card-body">
        <div>
            <h2 class="mb-3 f-w-600">{{ __('Password') }} <span class="text-primary">{{ __('required!') }}</span></h2>
        </div>
        <div class="custom-login-form">
            <form method="POST" action="{{ route('project.link', [\Illuminate\Support\Facades\Crypt::encrypt($projectID)]) }}">
                @csrf
                <div class="form-group mb-2">
                    <label class="form-control-label">{{ __('Password') }}</label>
                    <div class="input-group input-group-merge">

                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password"
                            required autocomplete="new-password">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <a href="#" data-toggle="password-text" data-target="#password">
                                    <i class="fas fa-eye-slash" id="togglePassword"></i>
                                </a>
                            </span>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-inner--text">{{ __('Save') }}</span>

                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('custom-scripts')

    <script src="{{ asset('assets/custom/libs/jquery/dist/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#form_data").submit(function(e) {
                $("#login_button").attr("disabled", true);
                return true;
            });
        });
    </script>
    @if (\App\Models\Utility::getValByName('recaptcha_module') == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush
{{-- </x-auth-card>
</x-guest-layout> --}}

<script>    
const togglePassword = document.querySelector("#togglePassword");
const password = document.querySelector("#password");
togglePassword.addEventListener("click", function () {
// toggle the type attribute
const type = password.getAttribute("type") === "password" ? "text" : "password";
password.setAttribute("type", type);

// toggle the icon
this.classList.toggle("fa-eye");
this.classList.toggle("fa-eye-slash");
});
</script>