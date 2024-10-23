@extends('layouts.admin')
@section('page-title')
    {{__('Debit Note')}}
@endsection
@push('css-page')
@endpush
@push('script-page')
@endpush
@section('breadcrumb')
    <h6 class="h2 d-inline-block mb-0">{{__('Debit Note')}}</h6>
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
        <ol class="breadcrumb breadcrumb-links">
            <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{__('Debit Note')}}</li>
        </ol>
    </nav>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h2 class="h3 mb-0">Manage Debit Note</h2>
                            </div>
                            <div class="col-auto">
                                <span class="create-btn">
                                        <a href="#" data-url="{{ route('debit-note.create') }}" data-ajax-popup="true" data-title="{{__('Create New Debit Note')}}" class="btn btn-outline-primary btn-sm">
                                            <i class="ti ti-plus"></i>  {{__('Create')}}
                                        </a>
                                    </span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive py-4">
                        <table class="table table-flush" id="datatable-basic">
                            <thead class="thead-light">
                            <tr>
                                <th>Bill</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>Bill</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                            </tfoot>
                            <tbody>
                            <tr>
                                <td>BIL001</td>
                                <td>John</td>
                                <td>18-04-2020</td>
                                <td>$200</td>
                                <td>Test</td>

                                <td class="table-actions">
                                    <a href="#!" class="table-action" data-toggle="tooltip" data-original-title="Edit">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    <a href="#!" class="table-action table-action-delete" data-toggle="tooltip" data-original-title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

