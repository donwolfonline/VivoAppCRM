<div class="row">
    <div class="col-md-4">
        <div class="mb-4">
            <b>{{__('Status')}}</b>
            <p>
            @if($task->status)
                <div class="badge bg-success p-2 px-3 rounded">{{__(\App\DealTask::$status[$task->status])}}</div>
            @else
                <div class="badge bg-warning p-2 px-3 rounded">{{__(\App\DealTask::$status[$task->status])}}</div>
            @endif
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-4">
            <b>{{__('Priority')}}</b>
            <p>{{__(\App\DealTask::$priorities[$task->priority])}}</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-4">
            <b>{{__('Deal Name')}}</b>
            <p>{{$deal->name}}</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-4">
            <b>{{__('Date')}}</b>
            <p>{{Auth::user()->dateFormat($task->date)}}</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-4">
            <b>{{__('Time')}}</b>
            <p>{{Auth::user()->timeFormat($task->time)}}</p>
        </div>
    </div>
    <div class="col-md-12">
        <div class="mb-0">
            <b>{{__('Asigned')}}</b>
            <p class="mt-2">
                @foreach($deal->users as $user)
                    <a href="#" class="btn btn-sm mr-1 p-0 rounded-circle">
                        <img alt="image" data-toggle="tooltip" data-original-title="{{$user->name}}" title="{{$user->name}}" @if($user->avatar) src="{{asset(Storage::url('uploads/avatar')).'/'.$user->avatar}}" @else src="{{asset(Storage::url('uploads/avatar')).'/avatar.png'}}" @endif class="rounded-circle profile-widget-picture" width="25">
                    </a>
                @endforeach
            </p>
        </div>
    </div>
</div>
