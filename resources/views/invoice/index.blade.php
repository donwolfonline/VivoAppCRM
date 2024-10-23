@extends('layouts.admin')
@php
 $profile = \App\Models\Utility::get_file('uploads/avatar/');
 @endphp

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
        <a href="{{ route('invoice.grid') }}" class="btn btn-sm btn-primary btn-icon m-1">
            <i class="ti ti-layout-grid" data-bs-toggle="tooltip" data-bs-original-title="{{__('Grid View')}}"></i>
        </a>

        <a href="#" data-size="lg" data-url="{{ route('invoice.create') }}" data-bs-toggle="modal"
        data-bs-target="#exampleModal" data-bs-whatever="{{ __('Create Invoice') }}" class="btn btn-sm btn-primary btn-icon m-1">
            <i class="ti ti-plus"  data-bs-toggle="tooltip" data-bs-original-title="{{__('Create')}}"></i>
        </a>

    @endif
@endsection
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
@section('content')
        <div class="col-xl-12">
            <div class=" {{isset($_GET['status'])?'show':''}}" >
                <div class="card card-body">
                    {{ Form::open(array('url' => 'invoice','method'=>'get')) }}
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
                            {{Form::date('start_date',isset($_GET['start_date'])?$_GET['start_date']: new \DateTime() ,array('class'=>'form-control'))}}
                        </div>
                        <div class="col-auto">
                            {{Form::date('end_date',isset($_GET['end_date'])?$_GET['end_date']: new \DateTime() ,array('class'=>'form-control'))}}
                        </div>
                        <div class="action-btn bg-info ms-2">
                            <div class="col-auto">
                                <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-bs-toggle="tooltip"
                                data-title="{{__('Apply')}}"><i class="ti ti-search text-white" ></i></button>
                            </div>
                        </div>
                        <div class="action-btn bg-danger ms-2">
                            <div class="col-auto">
                                <a href="{{route('invoice.index')}}" data-toggle="tooltip" data-title="{{__('Reset')}}"
                                class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash text-white"></i></a>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>


    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <!-- <h5></h5> -->
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th scope="col">{{__('Invoice')}}</th>
                                <th scope="col">{{__('Issue Date')}}</th>
                                <th scope="col">{{__('Due Date')}}</th>
                                <th scope="col">{{__('Total')}}</th>
                                <th scope="col">{{__('Due')}}</th>
                                <th scope="col">{{__('Status')}}</th>
                                @if(\Auth::user()->type=='company' || \Auth::user()->type=='client')
                                    <th scope="col" class="text-right">{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <th scope="row">
                                        <div class="media align-items-center">
                                            <div>
                                                <div class="user-group1">
                                                    @if(\Auth::user()->type!='client')
                                                        <img alt="" @if(!empty($invoice->clients) && !empty($invoice->clients->avatar)) src="{{$profile.'/'.$invoice->clients->avatar}}" @else  avatar="{{!empty($invoice->clients)?$invoice->clients->name:''}}" @endif data-bs-toggle="tooltip" data-bs-original-title="{{!empty($invoice->clients)?$invoice->clients->name:''}}" class="avatar  rounded-circle">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="media-body ms-2 pt-2">

                                                <a href="{{route('invoice.show',\Crypt::encrypt($invoice->id))}}" class="name h6 mb-0 text-sm text-primary">{{\Auth::user()->invoiceNumberFormat($invoice->invoice_id)}}</a><br>
                                                <span class="text-capitalize badge bg-{{$invoice->type=='product' ? 'success':'danger'}} p-1 px-2 rounded" data-bs-toggle="tooltip" data-bs-original-title="{{__('Type')}}">
                                                    {{ $invoice->type }}
                                                </span>
                                            </div>
                                        </div>
                                    </th>

                                    <td>{{\Auth::user()->dateFormat($invoice->issue_date)}}</td>
                                    <td>{{\Auth::user()->dateFormat($invoice->due_date)}}</td>
                                    <td>{{\Auth::user()->priceFormat($invoice->getTotal())}}</td>
                                    <td>{{\Auth::user()->priceFormat($invoice->getDue())}}</td>
                                    <td>
                                        @if($invoice->status == 0)
                                            <span class="badge fix_badges bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 1)
                                            <span class="badge fix_badges bg-info p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 2)
                                            <span class="badge fix_badges bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 3)
                                            <span class="badge fix_badges bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 4)
                                            <span class="badge fix_badges bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 5)
                                            <span class="badge fix_badges bg-success p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @endif
                                    </td>
                                    @if(\Auth::user()->type=='company' || \Auth::user()->type=='client')
                                        <td class="text-right">
                                            <div class="actions ml-3">
                                                @if(\Auth::user()->type=='company' || \Auth::user()->type=='client')
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="{{route('invoice.show',\Crypt::encrypt($invoice->id))}}" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        data-bs-whatever="{{__('Edit Invoice')}}"> <span class="text-white"> <i
                                                                class="ti ti-eye"  data-bs-toggle="tooltip" data-bs-original-title="{{__('View')}}"></i></span></a>
                                                    </div>
                                                @endif


                                                @if(\Auth::user()->type=='company')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal" data-url="{{ route('invoice.edit',$invoice->id) }}"
                                                    data-bs-whatever="{{__('Edit Invoice')}}"> <span class="text-white"> <i
                                                            class="ti ti-edit"  data-bs-toggle="tooltip" data-bs-original-title="{{__('Edit')}}"></i></span></a>
                                                </div>

                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id]]) !!}
                                                    <a href="#!" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                                        <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>



                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

