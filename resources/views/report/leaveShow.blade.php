
<div class="project-details" style="margin-top: 15px;">
    <div class="row">
        @foreach($leaves as $leave)
            <div class="col text-center">
                <div class="">{{$leave->title}}</div>
                <div class="font-weight-bold">{{$leave->total}}</div>
            </div>
        @endforeach
    </div>
    <div class="row mt-5">
        <table class="table table-flush" id="datatable-basic">
            <thead class="thead-light">
            <tr>
                <th>{{__('Leave Type')}}</th>
                <th>{{__('Leave Date')}}</th>
                <th>{{__('Leave Days')}}</th>
                <th>{{__('Leave Reason')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($leaveData as $leave)
                @php
                    $startDate               = new \DateTime($leave->start_date);
                   $endDate                 = new \DateTime($leave->end_date);
                   $total_leave_days        = $startDate->diff($endDate)->days;
                @endphp
                <tr>
                    <td>{{!empty($leave->leaveType)?$leave->leaveType->title:''}}</td>
                    <td>{{$leave->start_date.' to '.$leave->end_date}}</td>
                    <td>{{$total_leave_days}}</td>
                    <td>{{$leave->leave_reason}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
