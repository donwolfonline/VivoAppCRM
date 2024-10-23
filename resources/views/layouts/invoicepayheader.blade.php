@php
    $users=\Auth::user();
     if(isset($users)){ 
    $currantLang = $users->currentLanguage();
     }
     $languages=\App\Models\Utility::languages();
    $footer_text=isset(\App\Models\Utility::settings()['footer_text']) ? \App\Models\Utility::settings()['footer_text'] : '';
    $header_text = (!empty(\App\Models\Utility::settings()['company_name'])) ? \App\Models\Utility::settings()['company_name'] : env('APP_NAME');
    $setting = App\Models\Utility::colorset();
    $SITE_RTL = isset($site_setting['SITE_RTL']) ? $site_setting['SITE_RTL'] : '';
    $color = isset($site_setting['color']) ? $site_setting['color'] : 'theme-3';
      
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{$SITE_RTL == 'on'?'rtl':''}}">

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('partials.admin.head')

@if ($site_setting['cust_darklayout'] == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}"  id="style">
@else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="style">
@endif

<!-- <body class="application application-offset"> -->
<body class="{{ $color }}">
<div class="container">
<div class="main-content position-relative">
    <nav class="navbar navbar-main navbar-expand-lg navbar-border n-top-header">
    <div class="container align-items-lg-center">
       <h4>{{$header_text}}</h4>
    </div>
    </nav>
    <div class="page-content">
        @include('partials.admin.invoice_content')
        
    </div>
</div>
</div>

{{-- For Toastr Body start --}}
<div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
    <div id="liveToast" class="toast text-white  fade" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
{{-- For Toastr Body End --}}

@include('partials.admin.footer')

@if(Session::has('success'))
    <script>
        toastrs('{{__('Success')}}', '{!! session('success') !!}', 'success');
    </script>
    {{ Session::forget('success') }}
@endif
@if(Session::has('error'))
    <script>
        toastrs('{{__('Error')}}', '{!! session('error') !!}', 'error');
    </script>
    {{ Session::forget('error') }}
@endif


@php
$settings = \App\Models\Utility::settings();
@endphp
​    @if ($settings['enable_cookie'] == 'on')
    @include('layouts.cookie_consent')
@endif


</body>
</html>




