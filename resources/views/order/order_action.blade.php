{{Form::open(array('url'=>'order/changeAction','method'=>'post'))}}
<div class="card-body p-0">
    <table class="table mb-0 dataTable no-footer">
        <tr role="row">
            <th>{{__('Order Id')}}</th>
            <td>{{ !empty($order->order_id)?$order->order_id:'' }}</td>
        </tr>
        <tr>
            <th>{{__('Plan Name')}}</th>
            <td>{{ !empty($order->plan_name)?$order->plan_name:'' }}</td>
        </tr>
        <tr>
            <th>{{__('Price')}}</th>
            <td>{{ !empty($order->price)?$order->price:'' }}</td>
        </tr>
        <tr>
            <th>{{__('Payment Type')}}</th>
            <td>{{ !empty($order->payment_type)?$order->payment_type:'' }}</td>
         </tr>
        <tr>
            <th>{{__('Payment Status')}}</th>
             <td>
                @if($order->payment_status == 'succeeded' || $order->payment_status == 'success')
                    <div class="badge fix_badge bg-success p-2 px-3 rounded">{{ucfirst('success')}}</div>
                @elseif($order->payment_status == 'Approve')   
                <div class="badge fix_badge bg-success p-2 px-3 rounded">{{ucfirst('Approve')}}</div>
                @elseif($order->payment_status == 'Pending')
                    <div class="badge fix_badge bg-warning p-2 px-3 rounded">{{ $order->payment_status }}</div>
                @else
                    <div class="badge fix_badge bg-danger p-2 px-3 rounded">{{ucfirst($order->payment_status)}}</div>
                @endif
            </td>
        </tr>
        <tr>
            <th>{{__('Bank Detail')}}</th>
            <td>{!! $admin_payment_settings['bank_details'] !!}</td>
        </tr>
        <tr>
            <th>{{__('payment Receipt')}}</th>
            <td>
                <a href="{{ asset('storage/payment_recipt/'.$order->receipt) }}" class="btn btn-primary btn-sm" download><i class="ti ti-download"></i></a>
            </td>
    
        </tr>
        <input type="hidden" value="{{ $order->id }}" name="order_id">
    </table>
</div>
<div class="modal-footer pr-0">
    {{-- <input type="submit" class="btn btn-primary" value="success" {{($order->payment_status=='success')?'disabled':''}} name="payment_status">
    <input type="submit" class="btn btn-danger " value="fail" {{($order->payment_status=='fail')?'disabled':''}} name="payment_status"> --}}
    <a href="{{ route('order.approve', [$order->id, 1]) }}" class="btn btn-success">{{ __('Approval') }}</a>
    <a href="{{ route('order.reject', [$order->id, 0]) }}" class="btn btn-danger">{{ __('Reject') }}</a>
</div>
{{Form::close()}}