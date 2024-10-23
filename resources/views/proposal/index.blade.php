@extends('layouts.admin')
@section('page-title')
    {{__('Proposal')}}
@endsection
@push('css-page')
@endpush
@push('script-page')
@endpush
@section('breadcrumb')
    <h6 class="h2 d-inline-block mb-0">{{__('Proposal')}}</h6>
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
        <ol class="breadcrumb breadcrumb-links">
            <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{__('Proposal')}}</li>
        </ol>
    </nav>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h2 class="h3 mb-0">Filter</h2>
                            </div>
                            <div class="col">
                                <label class="form-control-label" for="exampleFormControlSelect1">Status</label>
                                <select class="form-control font-style" required="required" id="owner" name="owner">
                                    <option value="6">All</option>
                                    <option value="6">Waiting</option>
                                    <option value="7">Accepted</option>
                                    <option value="7">Declined</option>
                                </select>
                            </div>
                            <div class="col">
                                <div class="row input-daterange datepicker align-items-center">
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="form-control-label">Issue date</label>
                                            <input class="form-control" placeholder="Start date" type="text" value="06/18/2018">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="form-control-label">Open Till</label>
                                            <input class="form-control" placeholder="End date" type="text" value="06/22/2018">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="button" data-toggle="modal" data-target="#add_task" class="btn btn-outline-primary btn-sm">
                                    <span class="btn-inner--icon"><i class="fas fa-check"></i></span>
                                    <span class="btn-inner--text">Apply</span>
                                </button>
                            </div>
                            <div class="col-auto">
                                <button type="button" data-toggle="modal" data-target="#add_task" class="btn btn-outline-danger btn-sm">
                                    <span class="btn-inner--icon"><i class="fa fa-user-times"></i></span>
                                    <span class="btn-inner--text">Reset</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h2 class="h3 mb-0">Manage Proposal</h2>
                            </div>
                            <div class="col-auto">
                                <span class="create-btn">
                                        <a href="{{ route('proposal.create') }}" class="btn btn-outline-primary btn-sm">
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
                                <th>#</th>
                                <th>Client</th>
                                <th>Total</th>
                                <th>Issue Date</th>
                                <th>Open Till</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Client</th>
                                <th>Total</th>
                                <th>Issue Date</th>
                                <th>Open Till</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </tfoot>
                            <tbody>
                            <tr>
                                <td>PRO001</td>
                                <td>John</td>
                                <td>$100</td>
                                <td>19-12-2020</td>
                                <td>19-12-2020</td>
                                <td>Waiting</td>
                                <td class="table-actions">
                                    <a href="#!" class="table-action" data-toggle="tooltip" data-original-title="Edit product">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    <a href="#!" class="table-action table-action-delete" data-toggle="tooltip" data-original-title="Delete product">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="#!" class="table-action table-action-delete" data-toggle="tooltip" data-original-title="View">
                                        <i class="fas fa-eye"></i>
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

