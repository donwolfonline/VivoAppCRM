@php
$users=\Auth::user();
//$profile=asset(Storage::url('uploads/avatar/'));
$logo=\App\Models\Utility::get_file('uploads/logo/');
$profile=\App\Models\Utility::get_file('uploads/avatar/');

$currantLang = $users->currentLanguage();
$languages=Utility::languages();

if(\Auth::user()->type == 'employee')
{
$userTask = App\Models\ProjectTask::where('assign_to', \Auth::user()->id)->where('time_tracking', 1)->first();
}
else
{
$userTask = App\Models\ProjectTask::where('time_tracking', 1)->first();
}
$unseenCounter=App\Models\ChMessage::where('to_id', Auth::user()->id)->where('seen', 0)->count();
@endphp
<nav class="navbar navbar-main navbar-expand-lg navbar-border n-top-header" id="navbar-main">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-main-collapse"
            aria-controls="navbar-main-collapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-user d-lg-none ml-auto">
            <ul class="navbar-nav flex-row align-items-center navbar_msg_responsive">
                <li class="nav-item">
                    <a href="#" class="nav-link nav-link-icon sidenav-toggler" data-action="sidenav-pin"
                        data-target="#sidenav-main"><i class="fas fa-bars"></i></a>
                </li>
                <li class="ml-2 main_massege_section">
                    <a href="#" class="nav-link nav-link-icon sidenav-toggler" data-action="sidenav-pin"
                    data-target="#sidenav-main"><i class="fas fa-bars"></i></a>
                    <a class="nav-link pr-lg-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <span class="avatar avatar-sm rounded-circle">
                            <img alt="Image placeholder" @if(!empty($users->avatar))
                            src="{{$profile.'/'.$users->avatar}}" @else avatar="{{$users->name}}" @endif>
                        </span>
                    </a>
                    <div class="row{{!empty($userTask)?" mt-3":""}}">
                        <div class="col-auto tracking-clock">
                            @if(empty($userTask))
                            <a href="{{route('project.all.task.kanban')}}" data-toggle="tooltip"
                                data-original-title="{{__('No time tracking running')}}">
                                <i class="fas fa-clock"></i>
                            </a>
                            @else
                            <a href="{{route('project.all.task.kanban')}}" data-toggle="tooltip">
                                <i class="fas fa-clock"></i>
                            </a>
                            @endif
                        </div>
                        <div class="col-auto">
                            <div class="timer-counter"></div>
                        </div>
                        <div class="col-auto">
                            <p class="start-task"></p>
                        </div>

                    </div>
                    @if(\Auth::user()->type=='company' || \Auth::user()->type=='employee')
                    <div>
                        <a href="{{ url('chats') }}" class="pt-2">
                            <span><i class="fas fa-comment" style="font-size: 21px"></i></span>
                            <span class="badge badge-danger badge-circle badge-btn custom_messanger_counter">
                                {{$unseenCounter}}
                            </span>
                        </a>
                    </div>
                    @endif
                </li>
                <li class="nav-item dropdown dropdown-animate responsive_none">
                    <a class="nav-link pr-lg-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <span class="avatar avatar-sm rounded-circle">
                            <img alt="Image placeholder" @if(!empty($users->avatar))
                            src="{{$profile.'/'.$users->avatar}}" @else avatar="{{$users->name}}" @endif>
                        </span>
                    </a>
                    
                </li>
            </ul>
        </div>
        <div class="collapse navbar-collapse navbar-collapse-fade" id="navbar-main-collapse">
            <ul class="navbar-nav align-items-center d-none d-lg-flex">
                <li class="nav-item">
                    <a href="#" class="nav-link nav-link-icon sidenav-toggler" data-action="sidenav-pin"
                        data-target="#sidenav-main"><i class="fas fa-bars"></i></a>
                </li>

                <li class="nav-item dropdown dropdown-animate">
                    <a class="nav-link pr-lg-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <div class="media media-pill align-items-center">
                            <span class="avatar rounded-circle">
                                <img alt="Image placeholder" @if(!empty($users->avatar))
                                src="{{$profile.'/'.$users->avatar}}" @else avatar="{{$users->name}}" @endif>
                            </span>
                            <div class="ml-2 d-none d-lg-block">
                                <span class="mb-0 text-sm text-capitalize font-weight-bold">{{$users->name}}</span>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right dropdown-menu-arrow">
                        <h6 class="dropdown-header px-0">{{__('Hi')}}, {{$users->name}}</h6>
                        <a href="{{route('profile')}}" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>{{__('My profile')}}</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('frm-logout').submit();"
                            class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>{{__('Logout')}}</span>
                        </a>
                        <form id="frm-logout" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
                <li class="ml-2">
                    <div class="row{{!empty($userTask)?" mt-3":""}}">
                        <div class="col-auto tracking-clock">
                            @if(empty($userTask))
                            <a href="{{route('project.all.task.kanban')}}" data-toggle="tooltip"
                                data-original-title="{{__('No time tracking running')}}">
                                <i class="fas fa-clock"></i>
                            </a>
                            @else
                            <a href="{{route('project.all.task.kanban')}}" data-toggle="tooltip">
                                <i class="fas fa-clock"></i>
                            </a>
                            @endif
                        </div>
                        <div class="col-auto">
                            <div class="timer-counter"></div>
                        </div>
                        <div class="col-auto">
                            <p class="start-task"></p>
                        </div>

                    </div>
                </li>
                @if(\Auth::user()->type=='company' || \Auth::user()->type=='employee')
                <li>
                    <div>
                        <a href="{{ url('chats') }}" class="pt-2">
                            <span><i class="fas fa-comment" style="font-size: 21px"></i></span>
                            <span class="badge badge-danger badge-circle badge-btn custom_messanger_counter">
                                {{$unseenCounter}}
                            </span>
                        </a>
                    </div>
                </li>
                @endif
            </ul>
            <ul class="navbar-nav ml-lg-auto align-items-lg-center">
                <nav aria-label="breadcrumb" class="breadcrumb-font">
                    <ol class="breadcrumb breadcrumb-links">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </ul>
        </div>
    </div>
</nav>