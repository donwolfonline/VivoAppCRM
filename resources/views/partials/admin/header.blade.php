@php
    $users = \Auth::user();
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    $logo = \App\Models\Utility::get_file('uploads/avatar/');

    // $logo = asset(Storage::url('uploads/logo/'));
    $currantLang = $users->currentLanguage();
    if ($currantLang == null) {
        $currantLang = 'en';
    }
    $languages = Utility::languages();
    $LangName = \App\Models\Languages::where('code', $currantLang)->first();
    // dd($LangName);
    if (\Auth::user()->type == 'employee' && \Auth::user()->type != 'super admin') {
        $userTask = App\Models\ProjectTask::where('assign_to', \Auth::user()->id)
            ->where('time_tracking', 1)
            ->first();
    } elseif (\Auth::user()->type != 'super admin') {
        $userTask = App\Models\ProjectTask::where('time_tracking', 1)
            ->where('created_by', \Auth::user()->id)
            ->first();
    }
    $unseenCounter = App\Models\ChMessage::where('to_id', Auth::user()->id)
        ->where('seen', 0)
        ->count();
@endphp
@if (!empty($userTask))
    @php
        $lastTime = App\Models\ProjectTaskTimer::where('task_id', $userTask->id)
            ->orderBy('id', 'desc')
            ->first();
    @endphp
    <script>
        TrackerTimer("{{ $lastTime->start_time }}");
        $('.start-task').html("{{ $userTask->title }}");
    </script>
@endif
@if (isset($settings['cust_theme_bg']) && $settings['cust_theme_bg'] == 'on')
    <header class="dash-header transprent-bg">
    @else
        <header class="dash-header">
@endif
<div class="header-wrapper">
    <div class="me-auto dash-mob-drp">
        <ul class="list-unstyled">
            <li class="dash-h-item mob-hamburger">
                <a href="#!" class="dash-head-link" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner">
                            </div>
                        </div>
                    </div>
                </a>
            </li>

            <li class="dropdown dash-h-item drp-company">
                <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">

                    <img class="theme-avtar"
                        src="{{ !empty(\Auth::user()->avatar) ? $logo . \Auth::user()->avatar : $logo . '/avatar-1.jpg' }}"
                        class="header-avtar" width="50">

                    <span class="hide-mob ms-2">{{ $users->name }}</span>
                    <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                </a>
                <div class="dropdown-menu dash-h-dropdown">

                    <a href="{{ route('profile') }}" class="dropdown-item">
                        <i class="ti ti-user"></i>
                        <span>{{ __('Profile') }}</span>
                    </a>

                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('frm-logout').submit();"
                        class="dropdown-item">
                        <i class="ti ti-power"></i>
                        <span>{{ __('Logout') }}</span>
                    </a>
                    <form id="frm-logout" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </li>

        </ul>
    </div>
    <div class="ms-auto">
        <ul class="list-unstyled">
            @impersonating($guard = null)
                <li class="dropdown dash-h-item drp-company">
                    <a class="btn btn-danger btn-sm me-3" href="{{ route('exit.company') }}"><i class="ti ti-ban"></i>
                        {{ __('Exit Admin Login') }}
                    </a>
                </li>
            @endImpersonating
            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                <li class="dash-h-item {{ !empty($userTask) ? 'mt-3' : '' }}">

                    <div class="col-auto">
                        <p class="start-task"></p>
                    </div>
                    @if (empty($userTask))
                        <a class="dash-head-link me-0" href="{{ route('project.all.task.kanban') }}">
                            <i class="ti ti-subtask"></i>
                            <span class="sr-only"></span>
                        </a>
                    @else
                        <a class="dash-head-link me-0" style= "margin-top: -17px;"
                            href="{{ route('project.all.task.kanban') }}">
                            <i class="ti ti-subtask"></i>
                            <span class="sr-only"></span>
                        </a>
                    @endif
                    <div class="col-auto" style= "margin-top: -17px;">
                        <div class="timer-counter"></div>
                    </div>
                </li>
            @endif


            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
                <li class="dropdown dash-h-item drp-notification">
                    <a class="dash-head-link arrow-none me-0" href="{{ url('chats') }}" aria-haspopup="false"
                        aria-expanded="false">
                        <i class="ti ti-brand-hipchat"></i>
                        <span
                            class="bg-danger dash-h-badge message-toggle-msg message-counter custom_messanger_counter beep">
                            {{ $unseenCounter }}<span class="sr-only"></span></span>
                    </a>
                </li>
            @endif
            <li class="dropdown dash-h-item drp-language">
                <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    <i class="ti ti-world nocolor"></i>
                      <span class="drp-text hide-mob">{{ Str::upper($LangName->fullName) }}</span>
                    <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                </a>
                <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                    @foreach ($languages as $code => $lang)
                        <a href="{{ route('change.language', $code) }}"
                            class="dropdown-item {{ $currantLang == $code ? 'text-primary' : '' }}">
                            <span>{{ Str::upper($lang) }}</span>
                        </a>
                    @endforeach

                    @if (\Auth::user()->type == 'super admin')
                        <div class="dropdown-divider m-0"></div>
                        <a href="#" data-size="md" data-url="{{ route('create.language') }}"
                            data-bs-toggle="modal" data-bs-target="#exampleModal"
                            data-bs-whatever="{{ __('Create New Language') }}" class="dropdown-item text-primary">
                            {{ __('Create Language') }}
                        </a>
                        <div class="dropdown-divider m-0"></div>
                        <a href="{{ route('manage.language', [$currantLang]) }}" class="dropdown-item text-primary">
                            <span> {{ __('Manage Language') }}</span>
                        </a>
                    @endif

                </div>
            </li>
        </ul>
    </div>
</div>
</header>
