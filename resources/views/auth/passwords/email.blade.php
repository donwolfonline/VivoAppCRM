@extends('layouts.auth')

@php
$footer_text = isset(\App\Models\Utility::settings()['footer_text']) ? \App\Models\Utility::settings()['footer_text'] : '';
@endphp
@section('page-title')
    {{__('Forgot Password')}}
@endsection
@section('title')
    {{__('Forgot Password')}}
@endsection
@section('language')
    @foreach(Utility::languages() as $code => $language)
    <a href="{{ route('password.request',$code) }}" tabindex="0" class="dropdown-item {{ $code == $lang ? 'active':'' }}">
        <span>{{ ucFirst($language)}}</span>
    </a>
    @endforeach
@endsection
@section('content')
    <div class="card-body">
        <div>
            <h2 class="mb-3 f-w-600">{{ __('Forgot Password') }}</h2>
        </div>
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        <div class="custom-login-form">
            {{Form::open(array('route'=>'password.email','method'=>'post','id'=>'loginForm'))}}
                <div class="">
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Email') }}</label>
                        {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter Your Email')))}}
                        @error('email')
                        <span class="error invalid-email text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                    </div>
                    <div class="d-grid">
                        {{Form::submit(__('Reset Password'),array('class'=>'btn btn-primary btn-block mt-2','id'=>'saveBtn'))}}
                    </div>
                    {{ Form::close() }}
                    @if(Utility::getValByName('SIGNUP') == 'on')
                        <!-- <p class="my-4 text-center">{{ __('Not registered?') }}
                                <a href="{{ route('register',$lang) }}" class="my-4 text-primary">{{ __('Create account') }}</a>
                        </p> -->
                    @endif
                    <p class="my-4 text-center">{{__('Back to ')}}
                        <a href="{{route('login',$lang)}}" class="my-4 text-primary">{{ __('Login') }}</a>
                    </p>
                </div>
        </div>
    </div>
@endsection
