<div class="form-body">
    <div class="row">
        <div class="col-md-12 ">
            <div class="form-group">
                <label><b>{{__('Meeting Title')}}</b></label>
                <p> {{$meeting->title}} </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="font-12" for="">{{__('Department')}}</label><br>
                <div class="bootstrap-tagsinput">
                    @foreach($dep as $department)
                        <span class="tag badge bg-primary">{{$department}}</span>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="font-12" for="">{{__('Designation')}}</label><br>
                <div class="bootstrap-tagsinput">
                    @foreach($des as $designation)
                        <span class="tag badge bg-primary">{{$designation}}</span>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label><b>{{__('Date')}}</b></label>
                <p>{{\Auth::user()->dateFormat($meeting->date)}}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label><b>{{__('Time')}}</b></label>
                <p>{{\Auth::user()->timeFormat($meeting->time)}}</p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 ">
            <div class="form-group">
                <label><b>{{__('Note')}}</b></label>
                <p>{{$meeting->notes}}</p>
            </div>
        </div>
    </div>
    @if(\Auth::user()->type=='company')
    
        <div class="row">
                <div class="col-md-12 text-right">
                    <span class="create-btn">
                        <!-- <a href="#" data-url="{{ route('meeting.edit',$meeting->id) }}" data-ajax-popup="true" data-title="{{__('Edit Meeting')}}" class="btn btn-sm btn-primary rounded-pill">
                            <i class="fa fa-pencil"></i>  {{__('Edit')}}
                        </a> -->

                        <a href="#" class="btn btn-sm btn-primary rounded-pill m-1" data-bs-toggle="modal"
                            data-bs-target="#exampleOverModal" data-url="{{ route('meeting.edit',$meeting->id) }}"
                            data-bs-whatever="{{__('Edit Meeting')}}" >
                                <i class="text-white"></i>{{__('Edit')}}
                        </a>
                    </span>
                </div>
        </div>
    @endif

</div>

