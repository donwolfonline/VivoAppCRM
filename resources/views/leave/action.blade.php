{{Form::open(array('url'=>'leave/changeAction','method'=>'post'))}}
<div class="card-body p-0">
    <table class="table mb-0 dataTable no-footer">
        <tr role="row">
            <th>{{__('Employee')}}</th>
            <td>{{ !empty($employee->name)?$employee->name:'' }}</td>
        </tr>
        <tr>
            <th>{{__('Leave Type ')}}</th>
            <td>{{ !empty($leave->leaveType)?$leave->leaveType->title:'' }}</td>
        </tr>
        <tr>
            <th>{{__('Appplied On')}}</th>
            <td>{{\Auth::user()->dateFormat( $leave->applied_on) }}</td>
        </tr>
        <tr>
            <th>{{__('Start Date')}}</th>
            <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
        </tr>
        <tr>
            <th>{{__('End Date')}}</th>
            <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
        </tr>
        <tr>
            <th>{{__('Leave Reason')}}</th>
            <td>{{ !empty($leave->leave_reason)?$leave->leave_reason:'' }}</td>
        </tr>
        <tr>
            <th>{{__('Status')}}</th>
            <td>{{ !empty($leave->status)?$leave->status:'' }}</td>
        </tr>
        <input type="hidden" value="{{ $leave->id }}" name="leave_id">
    </table>
</div>
<div class="modal-footer pr-0">
    <input type="submit" class="btn btn-primary" value="Approve" {{($leave->status=='Approve')?'disabled':''}} name="status">
    <input type="submit" class="btn btn-danger " value="Reject" {{($leave->status=='Reject')?'disabled':''}} name="status">
</div>
{{Form::close()}}
