@php
    $user = json_decode($users->details);
@endphp

<div class="row">
    @if (isset($user->status))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Status') }}</b></div>
            <p class="text-muted mb-4">{{ $user->status }}</p>
        </div>
    @endif

    @if (isset($user->country))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Country') }} </b></div>
            <p class="text-muted mb-4">{{ $user->country }}</p>
        </div>
    @endif


    @if (isset($user->countryCode))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Country Code') }} </b></div>
            <p class="text-muted mb-4">{{ $user->countryCode }}</p>
        </div>
    @endif


    @if (isset($user->region))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Region') }}</b></div>
            <p class="mt-1">{{ $user->region }}</p>
        </div>
    @endif


    @if (isset($user->regionName))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Region Name') }}</b></div>
            <p class="mt-1">{{ $user->regionName }}</p>
        </div>
    @endif


    @if (isset($user->city))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('City') }}</b></div>
            <p class="mt-1">{{ $user->city }}</p>
        </div>
    @endif


    @if (isset($user->zip))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Zip') }}</b></div>
            <p class="mt-1">{{ $user->zip }}</p>
        </div>
    @endif


    @if (isset($user->lat))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Latitude') }}</b></div>
            <p class="mt-1">{{ $user->lat }}</p>
        </div>
    @endif


    @if (isset($user->lon))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Longitude') }}</b></div>
            <p class="mt-1">{{ $user->lon }}</p>
        </div>
    @endif


    @if (isset($user->timezone))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Timezone') }}</b></div>
            <p class="mt-1">{{ $user->timezone }}</p>
        </div>
    @endif


    @if (isset($user->isp))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Isp') }}</b></div>
            <p class="mt-1">{{ $user->isp }}</p>
        </div>
    @endif


    @if (isset($user->org))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Org') }}</b></div>
            <p class="mt-1">{{ $user->org }}</p>
        </div>
    @endif


    @if (isset($user->as))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('As') }}</b></div>
            <p class="mt-1">{{ $user->as }}</p>
        </div>
    @endif


    @if (isset($user->query))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Query') }}</b></div>
            <p class="mt-1">{{ $user->query }}</p>
        </div>
    @endif


    @if (isset($user->browser_name))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Browser Name') }}</b></div>
            <p class="mt-1">{{ $user->browser_name }}</p>
        </div>
    @endif


    @if (isset($user->os_name))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Os Name') }}</b></div>
            <p class="mt-1">{{ $user->os_name }}</p>
        </div>
    @endif


    @if (isset($user->browser_language))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Browser Language') }}</b></div>
            <p class="mt-1">{{ $user->browser_language }}</p>
        </div>
    @endif


    @if (isset($user->device_type))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Device Type') }}</b></div>
            <p class="mt-1">{{ $user->device_type }}</p>
        </div>
    @endif


    @if (isset($user->referrer_host))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Referrer Host') }}</b></div>
            <p class="mt-1">{{ $user->referrer_host }}</p>
        </div>
    @endif


    @if (isset($user->referrer_path))
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{ __('Referrer Path') }}</b></div>
            <p class="mt-1">{{ $user->referrer_path }}</p>
        </div>
    @endif


</div>
