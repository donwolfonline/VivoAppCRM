<div class="form-body">
    <div class="row">
        <div class="col-md-6 ">
            <div class="form-group">
                <label><b>{{__('Zoom Meeting Title')}}</b></label>
                <p> {{!empty($meeting->title)?$meeting->title:''}} </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label><b>{{__('Zoom Meeting ID')}}</b></label>
                <p> {{!empty($meeting->meeting_id)?$meeting->meeting_id:''}} </p>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12 ">
            <div class="form-group">
                <label><b>{{__('Project Name')}}</b></label>
                <p> {{ !empty($meeting->projectName)?$meeting->projectName->title:'' }}</p>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-6 ">
            <div class="form-group">
                <label><b>{{__('User Name')}}</b></label>
                <p> {{ !empty($meeting->projectUsers)?$meeting->projectUsers->name:'' }}</p>
            </div>
        </div>
        <div class="col-md-6 ">
            <div class="form-group">
                <label><b>{{__('Client Name')}}</b></label>
                <p> {{ !empty($meeting->projectClient)?$meeting->projectClient->name:'' }}</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label><b>{{__('Date')}}</b></label>
                <p>{{\Auth::user()->dateFormat($meeting->start_date)}}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label><b>{{__('Time')}}</b></label>
                <p>{{\Auth::user()->timeFormat($meeting->start_date)}}</p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label><b>{{__('Duration')}}</b></label>
                <p> {{$meeting->duration }} Minutes</p>
            </div>
        </div>
    </div>
</div>


