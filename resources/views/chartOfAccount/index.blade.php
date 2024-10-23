@extends('layouts.admin')
@section('page-title')
    {{__('Manage Chart of Accounts')}}
@endsection
@push('script-page')
    <script>
        $(document).on('change', '#type', function () {
            var type = $(this).val();
            $.ajax({
                url: '{{route('charofAccount.subType')}}',
                type: 'POST',
                data: {
                    "type": type, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#sub_type').empty();
                    $.each(data, function (key, value) {
                        $('#sub_type').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        });

    </script>
@endpush
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Manage Chart of Accounts')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Chart of account')}}</li>
@endsection

@section('action-btn')
    @if(\Auth::user()->type=='company')
        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
        data-bs-target="#exampleModal" data-url="{{ route('chart-of-account.create') }}"
        data-bs-whatever="{{__('Create New Account')}}"> <span class="text-white"> 
            <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
        </a>
    @endif
@endsection
@section('filter')

@endsection
@section('content')
        @foreach($chartAccounts as $type=>$accounts)      
            <div class="col-xl-12">
                <div class="card">
                <div class="card-header">
                        <h6>{{ $type }}</h6>
                    </div>
                       
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th> {{__('Code')}}</th>
                                            <th> {{__('Name')}}</th>
                                            <th> {{__('Type')}}</th>
                                            <th> {{__('Balance')}}</th>
                                            <th> {{__('Status')}}</th>
                                            <th> {{__('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($accounts as $account)

                                        <tr>
                                            <td>{{ $account->code }}</td>
                                            <td><a href="{{route('report.ledger')}}?account={{$account->id}}">{{ $account->name }}</a></td>
                                            <td>{{!empty($account->subType)?$account->subType->name:'-'}}</td>
                                            <td>
                                                @if(!empty($account->balance()) && $account->balance()['netAmount']<0)
                                                    {{__('Dr').'. '.\Auth::user()->priceFormat(abs($account->balance()['netAmount']))}}
                                                @elseif(!empty($account->balance()) && $account->balance()['netAmount']>0)
                                                    {{__('Cr').'. '.\Auth::user()->priceFormat($account->balance()['netAmount'])}}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($account->is_enabled==1)
                                                    <span class="badge bg-success p-2 px-3 rounded">{{__('Enabled')}}</span>
                                                @else
                                                    <span class="badge bg-danger p-2 px-3 rounded">{{__('Disabled')}}</span>
                                                @endif
                                            </td>
                                            <td class="Action">
                                            
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="{{route('report.ledger')}}?account={{$account->id}}"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        data-bs-whatever="{{__('View Account')}}"> <span class="text-white"> <i
                                                                class="ti ti-eye" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}"></i></span></a>
                                                    </div>


                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal" data-url="{{ route('chart-of-account.edit',$account->id) }}"
                                                        data-bs-whatever="{{__('Edit Account')}}"> <span class="text-white"> <i
                                                                class="ti ti-edit" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                                    </div>

                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['chart-of-account.destroy', $account->id]]) !!}
                                                        <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>

                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                </div>
            </div>
        @endforeach
@endsection
