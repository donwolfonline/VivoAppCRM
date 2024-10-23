@extends('layouts.admin')
@php  $profile=\App\Models\Utility::get_file('uploads/avatar/');
@endphp
@push('script-page')
    <script>
        $(document).on('change', 'select[name=client]', function () {
            var client_id = $(this).val();
            getClientProject(client_id);
        });

        function getClientProject(client_id) {
            $.ajax({
                url: '{{route('invoice.client.project')}}',
                type: 'POST',
                data: {
                    "client_id": client_id, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#project').empty();
                    $.each(data, function (key, value) {
                        $('#project').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        }

        $(document).on('click', '.type', function () {
            var type = $(this).val();
            if (type == 'Project') {
                $('.project-field').removeClass('d-none')
                $('.project-field').addClass('d-block');
            } else {
                $('.project-field').addClass('d-none')
                $('.project-field').removeClass('d-block');
            }
        });


    </script>
@endpush
@section('page-title')
    {{__('Invoice')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Invoice')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Invoice')}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company')

        <a href="{{route('invoice.export')}}" class="btn btn-sm btn-primary btn-icon m-1" data-title="{{__('Export invoice CSV file')}}" data-toggle="tooltip">
            <i class="ti ti-file-export"  data-bs-toggle="tooltip" data-bs-original-title="{{__('Export')}}"></i>
        </a>

        <a href="{{ route('invoice.index') }}" class="btn btn-sm btn-primary btn-icon m-1">
            <i class="ti ti-list" data-bs-toggle="tooltip" data-bs-original-title="{{__('List View')}}"></i>
        </a>
        


        <a href="#" data-size="lg" data-url="{{ route('invoice.create') }}" data-toggle="tooltip" data-bs-toggle="modal"
        data-bs-target="#exampleModal" data-bs-whatever="{{ __('Create Invoice') }}"
        class="btn btn-sm btn-primary btn-icon m-1">
            <i class="ti ti-plus"  data-bs-toggle="tooltip" data-bs-original-title="{{__('Create')}}"></i>
        </a>

    @endif
@endsection

@section('content')
  
        <div class="col-12">
            <div class=" {{isset($_GET['status'])?'show':''}}" >
                <div class="card card-body">
                    {{ Form::open(array('route' => array('invoice.grid'),'method'=>'get')) }}
                    <div class="row filter-css">
                        <div class="col-md-2">
                            <select class="form-control" data-toggle="select" name="status">
                                <option value="">{{__('Select Status')}}</option>
                                @foreach($status as $k => $val)
                                    <option value="{{$k}}" {{isset($_GET['status']) && $_GET['status'] == $k?'selected':''}}> {{$val}} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            {{Form::date('start_date',isset($_GET['start_date'])?$_GET['start_date']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="col-auto">
                            {{Form::date('end_date',isset($_GET['end_date'])?$_GET['end_date']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="action-btn bg-info ms-2">
                        <div class="col-auto">
                            <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" 
                            data-title="{{__('Apply')}}"><i class="ti ti-search text-white" ></i></button>
                        </div>
                    </div>
                    <div class="action-btn bg-danger ms-2">
                        <div class="col-auto">
                            <a href="{{route('invoice.index')}}" data-toggle="tooltip" data-title="{{__('Reset')}}" 
                            class="mx-3 btn btn-sm d-inline-flex align-items-center"><i class="ti ti-trash text-white"></i></a>
                        </div>
                    </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

    <div class="row">
        @foreach($invoices as $invoice)
            <div class="col-lg-4">
                <div class="card hover-shadow-lg">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-10">
                                <h6 class="mb-0">
                                    <a href="{{route('invoice.show',\Crypt::encrypt($invoice->id))}}">{{\Auth::user()->invoiceNumberFormat($invoice->invoice_id)}}</a>
                                </h6>
                            </div>
                            <div class="col-2 text-right">
                                <div class="actions">
                                    <div class="dropdown">
                                        <a href="#" class="action-item" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if(\Auth::user()->type=='company')
                                                <a href="#" data-size="lg" data-url="{{ route('invoice.edit',$invoice->id) }}" 
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                    data-bs-whatever="{{__('Edit Invoice')}}" class="dropdown-item" >
                                                    <i class="ti ti-edit"></i> {{__('Edit')}}
                                                   
                                                </a>

                                                {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id]]) !!}
                                                    <a href="#!" class="show_confirm dropdown-item">
                                                        <i class="ti ti-trash"></i> {{ __('Delete') }}
                                                    </a>
                                                {!! Form::close() !!}

                                            @endif
                                            @if(\Auth::user()->type=='company' || \Auth::user()->type=='client')
                                                <a href="{{route('invoice.show',\Crypt::encrypt($invoice->id))}}" class="dropdown-item" data-toggle="tooltip" data-original-title="{{__('View')}}">
                                                   <i class="ti ti-eye"></i> {{__('View')}}
                                                </a>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="p-3 border border-dashed">

                            @if($invoice->status == 0)
                                <span class="badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                            @elseif($invoice->status == 1)
                                <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                            @elseif($invoice->status == 2)
                                <span class="badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                            @elseif($invoice->status == 3)
                                <span class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                            @elseif($invoice->status == 4)
                                <span class="badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                            @elseif($invoice->status == 5)
                                <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                            @endif

                            <div class="row align-items-center mt-3">
                                <div class="col-6">
                                    <h6 class="mb-0">{{\Auth::user()->priceFormat($invoice->getTotal())}}</h6>
                                    <span class="text-sm text-muted">{{__('Total Amount')}}</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="mb-0">{{\Auth::user()->priceFormat($invoice->getDue())}}</h6>
                                    <span class="text-sm text-muted">{{__('Due Amount')}}</span>
                                </div>
                            </div>
                            <div class="row align-items-center mt-3">
                                <div class="col-6">
                                    <h6 class="mb-0">{{\Auth::user()->dateFormat($invoice->issue_date)}}</h6>
                                    <span class="text-sm text-muted">{{__('Issue Date')}}</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="mb-0">{{\Auth::user()->dateFormat($invoice->due_date)}}</h6>
                                    <span class="text-sm text-muted">{{__('Due Date')}}</span>
                                </div>
                            </div>
                        </div>
                        @if(\Auth::user()->type != 'client')
                            @php $client=$invoice->clients @endphp

                            <div class="user-group1 pt-3">
                                <img @if(!empty($client->avatar)) src="{{$profile.'/'.$client->avatar}}" @else avatar="{{!empty($invoice->clients)?$invoice->clients->name:''}}" @endif class="avatar rounded-circle avatar-custom" data-toggle="tooltip" data-original-title="{{__('Client')}}">
                                <div class="media-body pl-3">
                                    <div class="text-sm my-0">{{!empty($invoice->clients)?$invoice->clients->name:''}}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

