@extends('layouts.admin')
@section('page-title')
    {{__('Manage Budgets')}}
@endsection

@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Budget Planner')}}</h5>
</div>
   
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Budget Planner')}}</li>
@endsection


@section('action-btn')

    <a href="{{ route('budget.create') }}" class="btn btn-sm btn-primary btn-icon m-1" 
    data-bs-whatever="{{__('Create Budget Plannner')}}" data-bs-toggle="tooltip" 
    data-bs-original-title="{{__('Create ')}}"> <span class="text-white"> 
        <i class="ti ti-plus text-white"></i></span>
    </a>

@endsection

@section('content')
 

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <!-- <h5></h5> -->
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th> {{__('Name')}}</th>
                                <th> {{__('Year')}}</th>
                                <th> {{__('Budget Period')}}</th>
                                <th> {{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($budgets as $budget)
                                <tr>
                                    <td class="font-style">{{ $budget->name }}</td>
                                    <td class="font-style">{{ $budget->from }}</td>
                                    <td class="font-style">{{ __(\App\Models\Budget::$period[$budget->period]) }}</td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('budget.edit',Crypt::encrypt($budget->id)) }}"
                                                     class="mx-3 btn btn-sm d-inline-flex align-items-center" 
                                                data-bs-whatever="{{__('Edit Budget Planner')}}" data-bs-toggle="tooltip"
                                                data-bs-original-title="{{__('Edit')}}"> <span class="text-white"> <i
                                                        class="ti ti-edit"></i></span></a>
                                            </div>

                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('budget.show',\Crypt::encrypt($budget->id)) }}"
                                                     class="mx-3 btn btn-sm d-inline-flex align-items-center" 
                                                data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip" 
                                                data-bs-original-title="{{__('View')}}"> <span class="text-white"> <i
                                                        class="ti ti-eye"></i></span></a>
                                            </div>


                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['budget.destroy', $budget->id]]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                                </a>
                                                {!! Form::close() !!}

                                            </div>
                                         </span>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
