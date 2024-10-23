@extends('layouts.admin')
@section('page-title')
    {{__('Proposal Create')}}
@endsection
@push('css-page')
@endpush
@push('script-page')
@endpush
@section('breadcrumb')
    <h6 class="h2 d-inline-block mb-0">{{__('Proposal Create')}}</h6>
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
        <ol class="breadcrumb breadcrumb-links">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="{{route('proposal.index')}}">{{__('Proposal')}}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{__('Create')}}</li>
        </ol>
    </nav>
@endsection
@section('content')
    <div class="container-fluid">
        {{ Form::open(array('url' => 'proposal')) }}
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('subject', __('Subject')) }}
                                    <input class="form-control" placeholder="Subject" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('related', __('Related')) }}
                                    <div class="input-group">
                                        <select class="form-control font-style" required="required" id="owner" name="owner">
                                            <option value="6">Client</option>
                                            <option value="7">Lead</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('client', __('Client / Lead')) }}
                                    <div class="input-group">
                                        <select class="form-control font-style" required="required" id="owner" name="owner">
                                            <option value="6">client 1</option>
                                            <option value="7">client 2</option>
                                            <option value="7">client 3</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('issue_date', __('Issue Date')) }}
                                    <input class="form-control datepicker" placeholder="Select date" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('open_till', __('Open Till')) }}
                                    <input class="form-control datepicker" placeholder="Select date" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('category', __('Category')) }}
                                <div class="input-group">
                                    <select class="form-control font-style" required="required" id="owner" name="owner">
                                        <option value="6">Category 1</option>
                                        <option value="7">Category 2</option>
                                        <option value="7">Category 3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('proposal_number', __('Proposal Number')) }}
                                    <input type="text" class="form-control" value="PRO001" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox mt-4">
                                    <input class="custom-control-input" type="checkbox" name="discount_apply" id="discount_apply">
                                    <label class="custom-control-label" for="discount_apply">{{__('Discount Apply')}}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header border-0">
                        <div class="row">
                            <div class="col-6">
                                <h3 class="mb-0">Item</h3>
                            </div>
                            <div class="col-6 text-right">
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <span class="btn-inner--icon"><i class="ti ti-plus"></i></span>
                                    <span class="btn-inner--text">Add Item</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- Light table -->
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush table-striped">
                            <thead class="thead-light">
                            <tr>
                                <th width="20%">Items</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Tax</th>
                                <th>Discount</th>
                                <th>Discription</th>
                                <th width="10%">Amount</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="table-user">
                                    <div class="input-group">
                                        <select class="form-control font-style" required="required" id="owner" name="owner">
                                            <option value="6">Item 1</option>
                                            <option value="7">Item 2</option>
                                            <option value="7">Item 3</option>
                                        </select>
                                    </div>
                                </td>
                                <td><input type="text" class="form-control"></td>
                                <td><input type="text" class="form-control"></td>
                                <td><input type="text" class="form-control"></td>
                                <td><input type="text" class="form-control"></td>
                                <td><input type="text" class="form-control"></td>
                                <td> $1000</td>
                                <td class="table-actions">
                                    <a href="#!" class="table-action table-action-delete" data-toggle="tooltip" data-original-title="Delete product">
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

        {{ Form::close() }}
    </div>
@endsection

